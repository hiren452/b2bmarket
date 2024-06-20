<?php
namespace Ced\RegistrationForm\Block\Adminhtml\Attribute;

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
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
        $this->_blockGroup = 'Ced_RegistrationForm';
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
        $register_form_reg = $this->_coreRegistry->registry('regform_data');
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
