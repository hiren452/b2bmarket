<?php

namespace Ced\RegistrationForm\Block\Adminhtml\Attribute\Edit\Tab;

use Ced\RegistrationForm\Model\AttributeFactory;
use Ced\RegistrationForm\Model\Config\Source\FieldType;
use Ced\RegistrationForm\Model\Source\Dependable;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Main extends Generic implements TabInterface
{
    protected $fieldType;
    protected $systemStore;
    protected $wysiwygConfig;
    protected $countryCollectionFactory;
    protected $inputTypeFactory;
    protected $attributeFactory;
    protected $dependableSource;

    /**
     * Main constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param FieldType $fieldType
     * @param CountryCollectionFactory $countryCollectionFactory
     * @param InputtypeFactory $inputTypeFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param WysiwygConfig $wysiwygConfig
     * @param AttributeFactory $attributeFactory
     * @param Dependable $dependableSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        FieldType $fieldType,
        CountryCollectionFactory $countryCollectionFactory,
        InputtypeFactory $inputTypeFactory,
        \Magento\Store\Model\System\Store $systemStore,
        WysiwygConfig $wysiwygConfig,
        AttributeFactory $attributeFactory,
        Dependable $dependableSource,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->fieldType = $fieldType;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->inputTypeFactory = $inputTypeFactory;
        $this->systemStore = $systemStore;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->attributeFactory = $attributeFactory;
        $this->dependableSource = $dependableSource;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('regform_data');
        $form = $this->_formFactory->create();
        $isElementDisabled = false;
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('regform_data', ['legend' => __(' Information')]);

        if ($model->getId()) {
            $fieldset->addField('attribute_id', 'hidden', ['name' => 'attribute_id']);
        }

        $fieldset->addField(
            'frontend_label',
            'text',
            [
                'name' => 'frontend_label',
                'label' => __('Attribute Name'),
                'title' => __('Attribute Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name' => 'attribute_code',
                'label' => __('Attribute Code'),
                'title' => __('Attribute Code'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'after_element_html' => 'Please create attribute code in small letters also the special characters are not allowed'
            ]
        );

        $fieldset->addField(
            'frontend_input',
            'select',
            [
                'label' => __('Type'),
                'title' => __('Type'),
                'name' => 'frontend_input',
                'required' => true,
                'placeholder' => '--Please Select--',
                'values' => $this->fieldType->toOptionArray(),
                'after_element_html' => ''
            ]
        );

        $fieldset->addField(
            'options',
            'text',
            [
                'name' => 'options',
                'after_element_html' => '<span id="addafter">add options separated by comma</span>'
            ]
        );

        $fieldset->addField(
            'sortorder',
            'text',
            [
                'name' => 'sortorder',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_dependent',
            'select',
            [
                'label' => __('Is Dependent'),
                'title' => __('Is Dependent'),
                'name' => 'is_dependent',
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'dependable_attribute',
            'select',
            [
                'name' => 'dependable_attribute',
                'label' => __('Dependable Attributes'),
                'title' => __('Dependable Attributes'),
                'value' => 'text',
                'values' => $this->dependableSource->toOptionArray()
            ]
        );

        $fieldset->addField(
            'is_unique',
            'select',
            [
                'label' => __('Unique Value'),
                'title' => __('Unique Value'),
                'name' => 'is_unique',
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_required',
            'select',
            [
                'label' => __('Required Value'),
                'title' => __('Required Value'),
                'name' => 'is_required',
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'show_in_registration_form',
            'select',
            [
                'label' => __('Show in Registration Form'),
                'title' => __('Show in Registration Form'),
                'name' => 'show_in_registration_form',
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'show_in_customer_account',
            'select',
            [
                'label' => __('Show in Customer Account'),
                'title' => __('Show in Customer Account'),
                'name' => 'show_in_customer_account',
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'show_in_order',
            'select',
            [
                'label' => __('Show in Order'),
                'title' => __('Show in Order'),
                'name' => 'show_in_order',
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => ['1' => __('enable'), '0' => __('disable')],
                'disabled' => $isElementDisabled
            ]
        );

        $values = $model->getData();
        if (isset($values['attribute_id'])) {
            $attribute = $this->attributeFactory->create()->load($values['attribute_id'], 'attribute_id');
            $table = $attribute->getData();

            if ($values['source_model'] == "Magento\Eav\Model\Entity\Attribute\Source\Boolean") {
                $values['frontend_input'] = 'yes/no';
            }

            if (isset($table['is_time']) && $table['is_time'] == 1) {
                $values['frontend_input'] = 'is_time';
            }

            $values = array_merge($values, $table);
            $values['options'] = $values['values'] ?? '';
        }

        $form->setValues($values);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Information');
    }

    public function getTabTitle()
    {
        return __('Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getCountryOptions()
    {
        $options = [];
        foreach ($this->countryCollectionFactory->create()->loadByStore()->toOptionArray() as $option) {
            $options[$option['value']]=$option['label'];
        }
        return $options;
    }
}
