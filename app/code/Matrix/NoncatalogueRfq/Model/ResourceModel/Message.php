<?php

namespace Matrix\NoncatalogueRfq\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Message extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {

        $this->_init('matrix_noncatalog_rfq_message', 'id');
    }
}
