<?php

namespace Matrix\CustomerMembership\Model\ResourceModel;

class CustomerAlaCart extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('ced_customermembership_alacart', 'id');
    }
}
