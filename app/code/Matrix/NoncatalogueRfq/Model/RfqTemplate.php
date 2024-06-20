<?php

namespace Matrix\NoncatalogueRfq\Model;

use Magento\Framework\Model\AbstractModel;

class RfqTemplate extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Matrix\NoncatalogueRfq\Model\ResourceModel\RfqTemplate');
    }
}
