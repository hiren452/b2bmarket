<?php
namespace Matrix\NoncatalogueRfq\Model;

use Magento\Framework\Model\AbstractModel;

class RfqPo extends AbstractModel
{

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'matrix_noncatalog_po';

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Matrix\NoncatalogueRfq\Model\ResourceModel\RfqPo');
    }
}
