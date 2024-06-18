<?php

namespace Matrix\NoncatalogRfqfrom\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Matrix\RfqEntity\Model\RfqEntityFactory;
use Matrix\RfqEntity\Setup\RfqEntitySetupFactory;

class Save extends \Magento\Backend\App\Action
{
    protected $_moduleDataSetups;
    protected $_rfqentitySetupFactory;
    protected $rfqentityFactory;
    protected $_eavConfig;
    protected $attribute;
    protected $noncatalogRfqfromAttributeFactory;
    protected $resultRedirectFactory;

    public function __construct(
        Action\Context $context,
        ModuleDataSetupInterface $moduleDataSetup,
        RfqEntitySetupFactory $rfqentitySetupFactory,
        RfqEntityFactory $rfqentityFactory,
        AttributeSetFactory $attributeSetFactory,
        EavSetup $eavSetup,
        Config $eavConfig,
        Registry $registry,
        Attribute $attribute,
        \Matrix\NoncatalogRfqfrom\Model\AttributeFactory $noncatalogRfqfromAttributeFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->_moduleDataSetups = $moduleDataSetup;
        $this->_rfqentitySetupFactory = $rfqentitySetupFactory;
        $this->rfqentityFactory = $rfqentityFactory;
        $this->_eavConfig = $eavConfig;
        $this->attribute = $attribute;
        $this->noncatalogRfqfromAttributeFactory = $noncatalogRfqfromAttributeFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
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
                try {
                    $code = $data['attribute_code'];
                    $name = $data['frontend_label'];
                    $data['frontend_input'] = '';
                    $input = $data['frontend_input'];
                    if ($input == "yes/no") {
                        $input = 'select';
                        $smodel = 'Magento\Eav\Model\Entity\Attribute\Source\Boolean';
                        $values = "";
                    } elseif ($input == 'select') {
                        $smodel = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
                        $values = $data['options'];
                        $data['is_required'] = 0;
                    } elseif ($input == 'multiselect') {
                        $smodel = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
                        $values = $data['options'];
                    } elseif ($input == 'image' || $input == 'file') {
                        $data['is_required'] = 0;
                        $smodel = '';
                        $values = $data['options'];
                    }

                    $eavmodel = $this->attribute->load($id);
                    $model = $this->noncatalogRfqfromAttributeFactory->create()->load($id, 'attribute_id');
                    $preOptions = $model->getValues();
                    $preOptions = explode(',', $preOptions);
                    $postValues = $data['options'];
                    $postValues = explode(',', $postValues);
                    $x = array_diff($postValues, $preOptions);
                    $optionValues = array_diff($postValues, $preOptions);
                    $optionValues = implode(',', $optionValues);
                    $optionValues = $this->getOptionData($optionValues);
                    $eavmodel->setData('frontend_label', $name);
                    $eavmodel->setData('is_required', $data['is_required']);
                    $eavmodel->setData('is_unique', $data['is_unique']);
                    $eavmodel->setData('option', ['value' => $optionValues]);
                    $used_in_forms[] = null;
                    $eavmodel->setData("used_in_forms", $used_in_forms)
                        ->setData("is_used_for_customer_segment", false)
                        ->setData("is_system", 0)
                        ->setData("is_user_defined", 1)
                        ->setData("is_visible", 1)
                        ->setData("sort_order", $data['sortorder'])
                        ->setData("position", 1);
                    $eavmodel->save();

                    try {
                        $model = $this->noncatalogRfqfromAttributeFactory->create()->load($model['entity_id']);
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                    }

                    $model->setData('attribute_label', $name);
                    $model->setData('show_in_order', 0);
                    $model->setData('status', $data['status']);
                    $model->setData('values', $data['options']);
                    $model->setData('sortorder', $data['sortorder']);
                    $model->setData('is_dependent', $data['is_dependent']);
                    $model->setData('dependable_attribute', $data['dependable_attribute']);
                    $model->save();
                    $this->messageManager->addSuccess(__('Attribute Updated Successfully'));
                    return $resultRedirect->setPath('*/*/attribute');
                } catch (LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while updating the Non-catalog RFQ attribute.'));
                }
            } else {
                try {
                    $code = $data['attribute_code'];
                    $name = $data['frontend_label'];
                    $input = $data['frontend_input'];
                    $optionValues = $this->getOptionData($data['options']);
                    if ($input == "yes/no") {
                        $input = 'select';
                        $smodel = 'Magento\Eav\Model\Entity\Attribute\Source\Boolean';
                        $values = "yes,no";
                    } elseif ($input == 'select') {
                        $smodel = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
                        $values = $data['options'];
                    } elseif ($input == 'multiselect') {
                        $smodel = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
                        $values = $data['options'];
                    } elseif ($input == 'is_time') {
                        $input = 'text';
                        $smodel = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
                        $values = $data['options'];
                    } else {
                        $smodel = '';
                        $values = $data['options'];
                    }

                    if (preg_match('/[\'^A-Z£$%&*()}{@#~?><>,|=+¬-]/', $code)) {
                        $this->messageManager->addError(__('The attribute code is not matching its criteria.'));
                        return $resultRedirect->setPath('*/*/attribute');
                    }

                    $rfqSetup = $this->_rfqentitySetupFactory->create(['setup' => $this->_moduleDataSetups]);
                    $rfqSetup->addAttribute(
                        \Matrix\RfqEntity\Model\RfqEntity::ENTITY,
                        $code,
                        [
                            'label' => $name,
                            'type' => 'varchar',
                            'input' => $input,
                            'required' => $data['is_required'],
                            'unique' => $data['is_unique'],
                            'source' => $smodel,
                            'system' => 0,
                            'position' => $data['sortorder'],
                            'visible' => 1,
                            'default' => null,
                            'user_defined' => false,
                            'sort_order' => $data['sortorder'],
                            'option' => ['values' => explode(',', $data['options'])],
                        ]
                    );

                    $attributeCodes = $rfqSetup->getAttribute(\Matrix\RfqEntity\Model\RfqEntity::ENTITY, $code);
                    $attributeCodes = $rfqSetup->getEavConfig()->getAttribute(\Matrix\RfqEntity\Model\RfqEntity::ENTITY, $code);
                    $used_in_forms[] = null;
                    $attributeCodes->setData("used_in_forms", $used_in_forms)
                        ->setData("is_used_for_customer_segment", false)
                        ->setData("is_system", 0)
                        ->setData("is_user_defined", 1)
                        ->setData("is_visible", 1);
                    $attributeCodes->save();

                    $eavmodel = $this->attribute->load($code, 'attribute_code');
                    $model = $this->noncatalogRfqfromAttributeFactory->create();
                    $model->setData('attribute_label', $name);
                    $model->setData('attribute_code', $code);
                    $model->setData('attribute_id', $eavmodel->getAttributeId());
                    $model->setData('show_in_order', 0);
                    $model->setData('status', $data['status']);
                    $model->setData('values', $values);
                    $model->setData('sortorder', $data['sortorder']);
                    $model->setData('is_dependent', $data['is_dependent']);
                    $model->setData('dependable_attribute', $data['dependable_attribute']);
                    if ($data['frontend_input'] == 'is_time') {
                        $model->setData('is_time', 1);
                    }
                    $model->save();

                    $this->messageManager->addSuccess(__('Attribute Created Successfully'));
                    return $resultRedirect->setPath('*/*/attribute');
                } catch (LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while creating the Non-catalog RFQ attribute.'));
                }
            }
            return $resultRedirect->setPath('*/*/attribute', ['attribute_id' => $this->getRequest()->getParam('attribute_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    public function getOptionData($data)
    {
        $data = rtrim($data, ',');
        $optionValue = explode(',', rtrim($data));
        foreach ($optionValue as $key => $value) {
            $value = trim($value);
            $options['field' . $key][0] = $value;
        }
        return $options;
    }
}
