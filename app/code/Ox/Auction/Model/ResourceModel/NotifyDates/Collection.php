<?php

namespace OX\Auction\Model\ResourceModel\NotifyDates;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use OX\Auction\Model\NotifyDates as Model;
use OX\Auction\Model\ResourceModel\NotifyDates as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Collection Constructor
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
