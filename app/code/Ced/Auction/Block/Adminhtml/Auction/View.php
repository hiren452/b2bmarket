<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 24/2/19
 * Time: 2:06 PM
 */

namespace Ced\Auction\Block\Adminhtml\Auction;

class View extends \Magento\Backend\Block\Widget
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('auction_view_bid');
    }

    public function getHeaderText()
    {
        return __('View Bids');
    }
}
