<?php

namespace Matrix\CsAuction\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Matrix\CsAuction\Model\ResourceModel\PrivateAuction as ResourceModel;

class PrivateAuction extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'ced_privateauction_privateauction';

    protected $_cacheTag = 'ced_privateauction_privateauction';

    protected $_eventPrefix = 'ced_privateauction';

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }

    /*public function getId(){
        return $this->getId();
    }
    public function setId($value){
        return $this->setId($value);
    }

    public function getAuctionId(){
        return $this->getAuctionId();
    }
    public function setAuctionId($value){
        return $this->setAuctionId($value);
    }

    public function getVendorId(){
        return $this->getVendorId();
    }
    public function setVendorId($value){
        return $this->setVendorId($value);
    }

    public function getCustomerIds(){
        return $this->getCustomerIds();
    }
    public function setCustomerIds($value){
        return $this->setCustomerIds($value);
    }

    public function getCustomerEmails(){
        return $this->getCustomerEmails();
    }
    public function setCustomerEmails($value){
        return $this->setCustomerEmails($value);
    }*/
}
