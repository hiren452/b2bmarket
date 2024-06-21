<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CustomerMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CustomerMembership\Block\Adminhtml\Membership\Edit\Tab;

use Ced\CustomerMembership\Model\System\Config\Source\Groups;
use Ced\CustomerMembership\Model\System\Config\Source\Months;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Package extends Generic
{
    protected $groups;
    protected $months;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Groups $groups,
        Months $months,
        array $data = []
    ) {
        $this->groups = $groups;
        $this->months = $months;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Membership Information')]);
        $model = $this->getCurrentMembership();

        $fieldset->addField(
            'plan_name',
            'text',
            [
                'name' => 'plan_name',
                'label' => __('Plan Name'),
                'title' => __('Plan Name'),
                'required' => true,
                'class'     => 'validate-text',
            ]
        );

        $fieldset->addField(
            'package_price',
            'text',
            [
               'name' => 'package_price',
               'label' => __('Plan Price'),
               'title' => __('Plan Price'),
               'required' => true,
               'class'     => 'validate-no validate-zero-or-greater',
               ]
        );

        $fieldset->addField(
            'duration',
            'select',
            [
               'name' => 'duration',
               'label' => __('Duration(in Month)'),
               'title' => __('Duration(in Month)'),
               'required' => true,
               'values'=>$this->months->toOptionArray(),
               ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
               'name' => 'status',
               'label' => __('Status'),
               'title' => __('Status'),
               'required' => true,
               'values'=>['1'=>'Enable','2'=>'Disable'],
               ]
        );

        $fieldset->addField(
            'website',
            'select',
            [
               'name' => 'website',
               'label' => __('Choose Website'),
               'title' => __('Choose Website'),
               'required' => true,
               'values'=>$this->getWebSites(),
               ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => true,
                'class'     => 'validate-text',
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getCurrentMembership()
    {
        return $this->getRegistry()->registry('current_membership');
    }

    public function getWebSites()
    {
        $websites = [];
        $websiteCollection = $this->_storeManager->getWebsites();
        foreach ($websiteCollection as $website) {
            array_push($websites, ['value'=> $website->getId(),'label'=> $website->getName()]);
        }
        return $websites;
    }
}
