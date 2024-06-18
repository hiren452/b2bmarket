<?php

namespace Matrix\CsAuction\Model\ResourceModel\PrivateAuction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Matrix\CsAuction\Model\PrivateAuction as Model;
use Matrix\CsAuction\Model\ResourceModel\PrivateAuction as ResourceModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'privateauction_collection';
    protected $_eventObject = 'privateauction_collection';

    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
