<?php
namespace Matrix\NoncatalogRfqfrom\Block\Adminhtml\Attribute\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory;
use Matrix\NoncatalogRfqfrom\Model\AttributeFactory;
use Matrix\NoncatalogRfqfrom\Model\Config\Source\FieldType;
use Matrix\NoncatalogRfqfrom\Model\Source\Dependable;

class Main extends Generic implements TabInterface
{
    protected $fieldType;
    protected $_systemStore;
    protected $_wysiwygConfig;
    protected $_status;
    protected $_collectionFactory;
    protected $_inputTypeFactory;
    protected $dependable;
    protected $attributeFactory;

    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param FieldType $fieldType
     * @param CollectionFactory $collectionFactory
     * @param InputtypeFactory $inputTypeFactory
     * @param SystemStore $systemStore
     * @param Config $wysiwygConfig
     * @param Dependable $dependable
     * @param Attribute $attributeFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        FieldType $fieldType,
        CollectionFactory $collectionFactory,
        InputtypeFactory $inputTypeFactory,
        SystemStore $systemStore,
        Config $wysiwygConfig,
        Dependable $dependable,
        AttributeFactory $attributeFactory,
        array $data = []
    ) {
        $this->_inputTypeFactory = $inputTypeFactory;
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_collectionFactory = $collectionFactory;
        $this->fieldType = $fieldType;
        $this->dependable = $dependable;
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('noncatalogrfqform_data');
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
                'after_element_html' =>'Please create attribute code in small letters also the special characters are not allowed  '
            ]
        );

        $script = "";
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
                //'required' => true,
                'after_element_html' =>'<span id="addafter">add options separated by comma for Dropdown and MultiSelect Type Attribute</span>'
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
                'values' => $this->dependable->toOptionArray()
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
                'disabled' => $isElementDisabled,
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __(' Status'),
                'name' => 'status',
                'required' => true,
                'options' => ['1' => __('enable'), '0' => __('disable')],
                'disabled' => $isElementDisabled
            ]
        );

        $values = $model->getData();
        if(isset($values['attribute_id'])) {
            $table = $this->attributeFactory->load($values['attribute_id'], 'attribute_id')->getData();
            if($values['source_model'] == "Magento\Eav\Model\Entity\Attribute\Source\Boolean") {
                $values['frontend_input'] = 'yes/no';
            }
            if(isset($table['is_time']) && $table['is_time'] == 1) {
                $values['frontend_input'] = 'is_time';
            }
            $values = array_merge($values, $table);
            $values['options'] = isset($values['values']) ? $values['values'] : '';
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
        foreach($this->_collectionFactory->create()->loadByStore()->toOptionArray() as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }
}
