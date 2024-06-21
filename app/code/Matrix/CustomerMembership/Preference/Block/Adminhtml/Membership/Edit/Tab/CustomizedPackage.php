<?php

namespace Matrix\CustomerMembership\Preference\Block\Adminhtml\Membership\Edit\Tab;

use Ced\CustomerMembership\Block\Adminhtml\Membership\Edit\Tab\Package;
use Ced\CustomerMembership\Model\System\Config\Source\Groups;
use Ced\CustomerMembership\Model\System\Config\Source\Months;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class CustomizedPackage extends Package
{
    private $membershipResource;
    private $membershipModel;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Groups $groups,
        Months $months,
        \Ced\CustomerMembership\Model\ResourceModel\Membership $membershipResource,
        \Ced\CustomerMembership\Model\MembershipFactory $membershipModel,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $groups, $months, $data);
        $this->membershipResource = $membershipResource;
        $this->membershipModel = $membershipModel;
    }

    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Membership Information')]);
        $model = $this->membershipModel->create();
        $this->membershipResource->load($model, $this->getRequest()->getParam('id'), 'id');

        if ($model->getPlanName() == \Ced\CustomWork\Observer\Assignmembership::seller_free_membership) {
            $fieldset->addField(
                'plan_name',
                'text',
                [
                    'name' => 'plan_name',
                    'label' => __('Plan Name'),
                    'title' => __('Plan Name'),
                    'required' => true,
                    'class'     => 'validate-text',
                    'readonly' => true,
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
                    'values'=>['1'=>'Enable','0'=>'Disable'],
                    'readonly' => true,
                ]
            );
        } else {
            $fieldset->addField(
                'plan_name',
                'text',
                [
                    'name' => 'plan_name',
                    'label' => __('Plan Name'),
                    'title' => __('Plan Name'),
                    'required' => true,
                    'class'     => 'validate-text'
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
                    'values'=>['1'=>'Enable','0'=>'Disable'],
                ]
            );
        }

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
            'rfq_limit',
            'text',
            [
                'name' => 'rfq_limit',
                'label' => __('RFQ Limit'),
                'title' => __('RFQ Limit'),
                'required' => true,
                'class'     => 'validate-no validate-zero-or-greater',
            ]
        );

        $fieldset->addField(
            'noncatrfq_limit',
            'text',
            [
                'name' => 'noncatrfq_limit',
                'label' => __('Non Catalog RFQ Limit'),
                'title' => __('Non Catalog RFQ Limit'),
                'required' => true,
                'class'     => 'validate-no validate-zero-or-greater',
            ]
        );
        $fieldset->addField(
            'noncatrfq_fee',
            'text',
            [
                'name' => 'noncatrfq_fee',
                'label' => __('Non Catalog RFQ Fee'),
                'title' => __('Non Catalog RFQ Fee'),
                'required' => true,
                'class' => 'validate-number'
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
        return $this;
    }
}
