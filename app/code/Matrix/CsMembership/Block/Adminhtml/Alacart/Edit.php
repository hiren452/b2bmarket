<?php

namespace Matrix\CsMembership\Block\Adminhtml\Alacart;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    public $_coreRegistry;
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Matrix_CsMembership';
        $this->_controller = 'adminhtml_alacart';

        parent::_construct();

        $this->removeButton('delete');
        $this->removeButton('reset');

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']]
                ]
            ],
            -100
        );
    }

    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('checkmodule_checkmodel')->getId()) {
            return __("Edit Rating '%1'", $this->escapeHtml($this->_coreRegistry->registry('checkmodule_checkmodel')->getTitle()));
        } else {
            return __('New Rating');
        }
    }
}
