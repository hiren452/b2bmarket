<?php
namespace OX\Auction\Block\Auction;

use Ced\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Matrix\CsAuction\Model\ResourceModel\PrivateAuction\CollectionFactory as PrivateCollection;

class Invitedauction extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    protected $collection = null;

    /** @var PrivateCollection */
    protected $privateBuyer;

    /** @var Pager */
    protected $pager;

    /**
     * Index constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CollectionFactory $auctionCollection,
        PrivateCollection $privateBuyer,
        Pager $pager,
        array $data = []
    ) {
        $this->auctionCollection = $auctionCollection;
        $this->customerSession = $customerSession;
        $this->privateBuyer = $privateBuyer;
        $this->pager = $pager;
        parent::__construct($context, $data);
    }

    public function getCustomerId()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        return $customerId;

    }

    public function getAuction($customerId, $pagesize)
    {
        $to = [];
        $privateBuyer = $this->privateBuyer->create()->addFieldToSelect('auction_id')->addFieldToFilter('customer_ids', ['like' => '%' . $customerId . '%'])->setPageSize($pagesize);
        if(!empty($privateBuyer)) {

            foreach($privateBuyer as $auctionId) {
                $auctionCollectionRecord = $this->auctionCollection->create()->addFieldToFilter('id', $auctionId->getAuctionId());
                if(!empty($auctionCollectionRecord->getData())) {
                    $to[] = $auctionCollectionRecord->getData();
                }
            }
            if(!empty($to)) {
                $to = call_user_func_array('array_merge', $to);
            }

        }
        return $to;
    }

    public function getCount($customerId)
    {
        $to = [];
        $privateBuyer = $this->privateBuyer->create()->addFieldToSelect('auction_id')->addFieldToFilter('customer_ids', ['like' => '%' . $customerId . '%']);
        if(!empty($privateBuyer)) {

            foreach($privateBuyer as $auctionId) {
                $auctionCollectionRecord = $this->auctionCollection->create()->addFieldToFilter('id', $auctionId->getAuctionId());
                if(!empty($auctionCollectionRecord->getData())) {
                    $to[] = $auctionCollectionRecord->getData();
                }
            }
            if(!empty($to)) {
                $to = call_user_func_array('array_merge', $to);
            }

        }
        return count($to);
    }

    public function getPagerHtml()
    {
        $limit = $this->getLimit();
        $collection = $this->getAuction($this->getCustomerId());
        $this->pager->setLimit($limit);
        $this->pager->setCollection($collection);
        return $this->pager->toHtml();
    }

    public function getLimit()
    {
        return 10;
    }

    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
