<?php
namespace Ced\CsRfq\Block;

class Po extends \Magento\Backend\Block\Widget\Container
{
    protected $_template = 'Ced_CsRfq::quotes/grid.phtml';

    protected function _construct()
    {
        $this->_controller = 'po';
        $this->_blockGroup = 'Matrix_NoncatRfq';
        $this->_headerText = __('Manage Non-Caralog Po');

        parent::_construct();
        $this->removeButton('add');
        //$this->setData('area','adminhtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Matrix\NoncatRfq\Block\Po\Grid', 'vendor.noncat-rfq.po.grid')
        );

        return parent::_prepareLayout();
        $this->buttonList->remove('add_new');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

}
