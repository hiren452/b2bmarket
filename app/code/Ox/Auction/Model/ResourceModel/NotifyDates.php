<?php

namespace OX\Auction\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class NotifyDates extends AbstractDb
{
    /**
     * ResourceModel Constructor
     */
    protected function _construct()
    {
        $this->_init('auction_notify_dates', 'id');
    }
}
