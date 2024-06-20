<?php
namespace Matrix\NoncatalogueRfq\Model\ResourceModel\NoncatalogRfq;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Matrix\NoncatalogueRfq\Model\NoncatalogRfq',
            'Matrix\NoncatalogueRfq\Model\ResourceModel\NoncatalogRfq'
        );
    }
}
