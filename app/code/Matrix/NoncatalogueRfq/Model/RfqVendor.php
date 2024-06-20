<?php
namespace Matrix\NoncatalogueRfq\Model;

use Magento\Framework\Model\AbstractModel;

class RfqVendor extends AbstractModel
{

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'matrix_noncatalog_rfq_vendor';

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Matrix\NoncatalogueRfq\Model\ResourceModel\RfqVendor');
    }
}
