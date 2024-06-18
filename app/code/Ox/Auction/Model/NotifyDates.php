<?php

namespace OX\Auction\Model;

use Magento\Framework\Model\AbstractModel;
use OX\Auction\Model\ResourceModel\NotifyDates as ResourceModel;

class NotifyDates extends AbstractModel
{
    /**
     * Model Constructor
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
