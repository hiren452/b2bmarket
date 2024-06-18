<?php

namespace Matrix\CsAuction\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class PrivateAuction extends AbstractDb
{

    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('ced_auction_private_auctionlist', 'id');
    }
}
