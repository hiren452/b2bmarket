<?php
namespace Matrix\NoncatalogRfqfrom\Block\Adminhtml\Attribute;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    protected $_coreRegistry = null;

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'attribute_id';
        $this->_blockGroup = 'Matrix_NoncatalogRfqfrom';
        $this->_controller = 'adminhtml_attribute';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Attribute'));
        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
                'class' => 'back'
            ],
            -1
        );
        $this->buttonList->update('delete', 'label', __('Delete Attribute'));
    }
    public function getBackUrl()
    {
        return $this->getUrl('*/*/attribute');
    }

    public function getHeaderText()
    {
        $register_form_reg = $this->_coreRegistry->registry('noncatalogrfqform_data');
        if ($register_form_reg->getId()) {
            return __("Edit Post '%1'", $this->escapeHtml($register_form_reg->getTitle()));
        } else {
            return __('New Post');
        }
    }

    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'content');
                }
            };
        ";
        return parent::_prepareLayout();
    }
}
