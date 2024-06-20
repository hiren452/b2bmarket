<?php
namespace Matrix\NoncatalogueRfq\Model\ResourceModel\RfqPodetail;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Matrix\NoncatalogueRfq\Model\RfqPodetail',
            'Matrix\NoncatalogueRfq\Model\ResourceModel\RfqPodetail'
        );
    }
}
