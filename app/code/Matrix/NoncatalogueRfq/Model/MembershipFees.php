<?php
namespace Matrix\NoncatalogueRfq\Model;

use Magento\Framework\Model\AbstractModel;

class MembershipFees extends AbstractModel
{

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'matrix_rfq_membershipfeesplan';

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Matrix\NoncatalogueRfq\Model\ResourceModel\MembershipFees');
    }
}
