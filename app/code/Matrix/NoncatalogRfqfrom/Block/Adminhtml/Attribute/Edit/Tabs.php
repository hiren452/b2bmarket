<?php
namespace  Matrix\NoncatalogRfqfrom\Block\Adminhtml\Attribute\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('noncatalogrfq_form');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Non-Catalog RFQ Form Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'information',
            [
                'label'     => __('General'),
                'content'   => $this->getLayout()->createBlock('Matrix\NoncatalogRfqfrom\Block\Adminhtml\Attribute\Edit\Tab\Main')->toHtml(),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
