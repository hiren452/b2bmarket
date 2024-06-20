<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category    Ced
 * @package     Ced_RegistrationForm
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Ced\RegistrationForm\Controller\Adminhtml\Registration;

use Ced\RegistrationForm\Model\AttributeFactory;
use Magento\Backend\App\Action;
use Magento\Customer\Model\AttributeMetadataDataProviderFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class Save extends Action
{
    protected $moduleDataSetup;
    protected $customerSetupFactory;
    protected $eavConfig;
    protected $attributeSetFactory;
    protected $attributeMetaData;
    protected $eavSetupFactory;
    protected $attributeFactory;

    public function __construct(
        Action\Context $context,
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        AttributeMetadataDataProviderFactory $attributeMetaData,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        AttributeFactory $attributeFactory
    ) {
        parent::__construct($context);
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeMetaData = $attributeMetaData;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeFactory = $attributeFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!isset($data['is_required'])) {
            $data['is_required'] = 0;
        }
        if (!isset($data['dependable_attribute'])) {
            $data['dependable_attribute'] = '';
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('attribute_id');
            if ($id) {
                $this->updateAttribute($id, $data);
            } else {
                $this->createAttribute($data);
            }
            return $resultRedirect->setPath('*/*/attribute');
        }
        return $resultRedirect->setPath('*/*/');
    }

    protected function updateAttribute($id, $data)
    {
        try {
            $eavAttribute = $this->eavConfig->getAttribute('customer', $id);
            $model = $this->attributeFactory->create()->load($id, 'attribute_id');

            // Your existing logic for updating the attribute
            $optionValues = $this->getOptionData($data['options']);
            $eavAttribute->setData('frontend_label', $data['frontend_label'])
                ->setData('is_required', $data['is_required'])
                ->setData('is_unique', $data['is_unique'])
                ->setData('option', ['value' => $optionValues])
                ->setData('used_in_forms', $this->getUsedInForms($data));

            $eavAttribute->save();

            $model->setData([
                'attribute_label' => $data['frontend_label'],
                'show_in_registration_form' => $data['show_in_registration_form'],
                'show_in_customer_account' => $data['show_in_customer_account'],
                'show_in_order' => $data['show_in_order'],
                'status' => $data['status'],
                'values' => $data['options'],
                'sortorder' => $data['sortorder'],
                'is_dependent' => $data['is_dependent'],
                'dependable_attribute' => $data['dependable_attribute']
            ])->save();

            $this->messageManager->addSuccessMessage(__('Attribute Updated Successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while updating the customer attribute.'));
        }
    }

    protected function createAttribute($data)
    {
        try {
            $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

            // Your existing logic for creating the attribute
            $optionValues = $this->getOptionData($data['options']);
            $customerSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                $data['attribute_code'],
                [
                    'label' => $data['frontend_label'],
                    'type' => 'varchar',
                    'input' => $data['frontend_input'],
                    'required' => $data['is_required'],
                    'unique' => $data['is_unique'],
                    'source' => $this->getSourceModel($data['frontend_input']),
                    'system' => 0,
                    'position' => $data['sortorder'],
                    'visible' => 1,
                    'user_defined' => false,
                    'sort_order' => $data['sortorder'],
                    'option' => ['values' => explode(',', $data['options'])]
                ]
            );

            $attribute = $this->eavConfig->getAttribute(\Magento\Customer\Model\Customer::ENTITY, $data['attribute_code']);
            $attribute->setData('used_in_forms', $this->getUsedInForms($data))->save();

            $model = $this->attributeFactory->create();
            $model->setData([
                'attribute_label' => $data['frontend_label'],
                'attribute_code' => $data['attribute_code'],
                'attribute_id' => $attribute->getId(),
                'show_in_registration_form' => $data['show_in_registration_form'],
                'show_in_customer_account' => $data['show_in_customer_account'],
                'show_in_order' => $data['show_in_order'],
                'status' => $data['status'],
                'values' => $data['options'],
                'sortorder' => $data['sortorder'],
                'is_dependent' => $data['is_dependent'],
                'dependable_attribute' => $data['dependable_attribute']
            ])->save();

            $this->messageManager->addSuccessMessage(__('Attribute Created Successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while creating the customer attribute.'));
        }
    }

    protected function getSourceModel($inputType)
    {
        switch ($inputType) {
            case 'yes/no':
                return 'Magento\Eav\Model\Entity\Attribute\Source\Boolean';
            case 'select':
            case 'multiselect':
                return 'Magento\Eav\Model\Entity\Attribute\Source\Table';
            default:
                return '';
        }
    }

    protected function getUsedInForms($data)
    {
        $usedInForms = ["adminhtml_customer", "checkout_register"];
        if ($data['show_in_registration_form']) {
            $usedInForms[] = "customer_account_create";
        }
        if ($data['show_in_customer_account']) {
            $usedInForms[] = "show_in_customer_account";
        }
        $usedInForms[] = "customer_account_edit";
        $usedInForms[] = "adminhtml_checkout";

        return $usedInForms;
    }

    protected function getOptionData($data)
    {
        $data = rtrim($data, ',');
        $optionValue = explode(',', $data);
        $options = [];
        foreach ($optionValue as $key => $value) {
            $value = trim($value);
            $options['field' . $key][0] = $value;
        }
        return $options;
    }
}
