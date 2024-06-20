<?php
namespace Matrix\NoncatalogueRfq\Model\ResourceModel\RfqPo;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Matrix\NoncatalogueRfq\Model\RfqPo',
            'Matrix\NoncatalogueRfq\Model\ResourceModel\RfqPo'
        );
    }
}
