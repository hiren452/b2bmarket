<?php
namespace Matrix\NoncatRfq\Block;

class VendorQuote extends \Magento\Backend\Block\Widget\Container
{
    protected $_template = 'Matrix_NoncatRfq::vendorquote/grid.phtml';

    protected function _construct()
    {
        $this->_controller = 'Rfq';
        $this->_blockGroup = 'Matrix_NoncatRfq';
        $this->_headerText = __('Manage Non-Catalog Quotes');

        parent::_construct();
        $this->removeButton('add');
        //$this->setData('area','adminhtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Matrix\NoncatRfq\Block\Quotes\Grid', 'vendor.noncat-rfq.quotes.grid')
        );

        return parent::_prepareLayout();
        $this->buttonList->remove('add_new');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

}
