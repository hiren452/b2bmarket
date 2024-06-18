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
* @category    Ced
* @package     Ced_CsRfq
* @author      CedCommerce Core Team <connect@cedcommerce.com>
* @copyright   Copyright CedCommerce (http://cedcommerce.com/)
* @license      http://cedcommerce.com/license-agreement.txt
*/

namespace Ced\CsRfq\Block;

class Po extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'Ced_CsRfq::quotes/grid.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'po';
        $this->_blockGroup = 'Ced_CsRfq';
        $this->_headerText = __('Manage Po');
        parent::_construct();
        $this->removeButton('add');
        //$this->setData('area','adminhtml');
    }

    /**
     * @return mixed
     */
    protected function _prepareLayout()
    {
        $this->buttonList->remove('add_new');
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Ced\CsRfq\Block\Po\Grid', 'vendor.rfq.po.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     * @return mixed
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
