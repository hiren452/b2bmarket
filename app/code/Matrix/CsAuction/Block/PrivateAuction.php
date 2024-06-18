<?php

namespace Matrix\CsAuction\Block;

use Magento\Framework\View\Element\Template;

class PrivateAuction extends Template
{

    protected $_registry;

    protected $auctionFactory;

    protected $customerFactory;

    protected $privateAuctionFactory;
    public $auctionCollection;
    public $session;
    public $formKey;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Ced\Auction\Model\ResourceModel\Auction\CollectionFactory $auction,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Matrix\CsAuction\Model\PrivateAuctionFactory $privateAuctionFactory,
        array $data = []
    ) {
        $this->_registry         = $registry;
        $this->auctionCollection = $auction;
        $this->session           = $session;
        $this->customerFactory   = $customerFactory;
        $this->privateAuctionFactory = $privateAuctionFactory;
        $this->formKey = $formKey;

        parent::__construct($context, $data);
    }

    public function getAuctions()
    {
        $collection = $this->auctionCollection->create()->addFieldToFilter('vendor_id', $this->session->getVendorId());

        return $collection;
    }

    public function getCustomerCollection()
    {
        return $this->customerFactory->create();
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getPrivateAuctionDetails($auction_id)
    {
        $model = $this->privateAuctionFactory->create();
        $model->load($auction_id, 'auction_id');

        return $model->getData();
    }
}
