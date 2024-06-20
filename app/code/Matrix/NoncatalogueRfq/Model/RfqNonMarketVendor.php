<?php
namespace Matrix\NoncatalogueRfq\Model;

use Magento\Framework\Model\AbstractModel;

class RfqNonMarketVendor extends AbstractModel
{

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'matrix_noncatalog_rfq_vendor_nonmarket';

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Matrix\NoncatalogueRfq\Model\ResourceModel\RfqNonMarketVendor');
    }
}
