<?php

namespace Matrix\CustomerMembership\Model\ResourceModel\CustomerAlaCart;

use Matrix\CustomerMembership\Model\CustomerAlaCart as Model;
use Matrix\CustomerMembership\Model\ResourceModel\CustomerAlaCart as ResourceModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
