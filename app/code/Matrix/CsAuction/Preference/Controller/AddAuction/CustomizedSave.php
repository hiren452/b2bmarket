<?php

namespace Matrix\CsAuction\Preference\Controller\AddAuction;

use Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlFactory;

class CustomizedSave extends \Ced\CsAuction\Controller\AddAuction\Save
{
    /**
     * @var \Ced\CsMembership\Helper\Data
     */
    private $membershipHelper;
    /**
     * @var \Ced\Auction\Model\AuctionFactory
     */
    private $auctionFactory;
    /**
     * @var \Ced\Auction\Model\ResourceModel\Auction
     */
    private $auctionResource;
    /**
     * @var \Ced\Auction\Model\ResourceModel\Auction\CollectionFactory
     */
    private $auctionCollectionFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    public function __construct(
        \Ced\Auction\Model\Auction $auction,
        DateTime $datetime,
        TimezoneInterface $timezoneInterface,
        CollectionFactory $biddetails,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Ced\CsMembership\Helper\Data $membershipHelper,
        \Ced\Auction\Model\AuctionFactory $auctionFactory,
        \Ced\Auction\Model\ResourceModel\Auction $auctionResource,
        \Ced\Auction\Model\ResourceModel\Auction\CollectionFactory $auctionCollectionFactory,
        TimezoneInterface $timezone,
    ) {
        parent::__construct(
            $auction,
            $datetime,
            $timezoneInterface,
            $biddetails,
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
        $this->membershipHelper = $membershipHelper;
        $this->auctionFactory = $auctionFactory;
        $this->auctionResource = $auctionResource;
        $this->auctionCollectionFactory = $auctionCollectionFactory;
        $this->timezone = $timezone;
    }

    public function execute()
    {
        $postdata = $this->getRequest()->getPostValue();
        if($postdata && $this->getRequest()->getParam('product_id')) {

            if($this->checkAuctionLimit()) {
                return $this->_redirect('csauction/auctionlist/index');
            }

            $resultRedirect = $this->resultRedirectFactory->create();

            $date = $this->datetime->gmtDate();
            $stratDateGmt = new \DateTime($postdata['start_datetime']);
            $stratDateGmt->setTimezone(new \DateTimeZone('GMT'));
            $startdateUTCprevious = $stratDateGmt->format('Y-m-d H:i:s');
            $startdateUTC =  $this->getNextQuarter($startdateUTCprevious);
            $endDateGmt = new \DateTime($postdata['end_datetime']);
            $endDateGmt->setTimezone(new \DateTimeZone('GMT'));
            $enddateUTCprevious = $endDateGmt->format('Y-m-d H:i:s');
            $enddateUTC =  $this->getNextQuarter($enddateUTCprevious);
            $startdateParts = explode(' ', $postdata['start_datetime'], 3);

            $startDateTimePrevious = $startdateParts[0] . ' ' . $startdateParts[1];
            $startDateTime =  $this->getNextQuarter($startDateTimePrevious);
            $starttimezoneOffset = $startdateParts[2];
            $enddateParts = explode(' ', $postdata['end_datetime']);
            $endDateTimePrevious = $enddateParts[0] . ' ' . $enddateParts[1];
            $endtimezoneOffset = $enddateParts[2];
            $endDateTime =  $this->getNextQuarter($endDateTimePrevious);

            $currenttime = $this->timezoneInterface
                ->date(new \DateTime($date))
                ->format('Y-m-d H:i:s');
            $extended = $this->timezoneInterface
                ->date(new \DateTime($date))
                ->modify('-1 minutes')
                ->format('Y-m-d H:i:s');
            $endtime = $this->timezoneInterface
                ->date(new \DateTime($endDateTime))
                ->format('Y-m-d H:i:s');
            if(isset($postdata['start_datetime']) && strtotime($startDateTime) < strtotime($extended)) {
                $this->messageManager
                    ->addErrorMessage('Bidding start time must be greater than current time');
                return $resultRedirect->setPath(
                    'csauction/addauction/auctionform',
                    ['product_id' => $this->getRequest()->getParam('product_id')]
                );
            }
            if(isset($postdata['start_datetime'])  && strtotime($endtime) < strtotime($startDateTime)) {
                $this->messageManager
                    ->addErrorMessage('Bidding end time must be greater than bidding start time');
                return $resultRedirect->setPath(
                    'csauction/addauction/auctionform',
                    ['product_id' => $this->getRequest()->getParam('product_id')]
                );
            }

            if(isset($postdata['start_datetime']) && strtotime($startDateTime) > strtotime($currenttime)) {
                $postdata['status'] = 'not started';
            }
            if(isset($postdata['start_datetime']) && strtotime($startDateTime) <= strtotime($currenttime)) {
                $postdata['status'] = 'processing';
            }

            if($postdata['Sell_Product'] == 'yes') {
                if($postdata['Starting_Price'] > $postdata['Price']) {
                    $this->messageManager->addErrorMessage('Starting bid must be less than original product price');
                    return $resultRedirect->setPath('csauction/addauction/auctionform', ['product_id' => $this->getRequest()->getParam('product_id')]);
                }
                if($postdata['Max_Price'] > $postdata['Price']) {
                    $this->messageManager->addErrorMessage('Maximum bid must be less than original product price');
                    return $resultRedirect->setPath('csauction/addauction/auctionform', ['product_id' => $this->getRequest()->getParam('product_id')]);
                }
                if($postdata['Max_Price'] == null) {
                    $postdata['Max_Price'] = $postdata['Price'];
                }
            }

            if(!isset($postdata['auction_type'])) {
                $types = $this->membershipHelper->getAuctionTypes();

                if($types == 2) {
                    $postdata['auction_type'] = 0;
                } else {
                    $postdata['auction_type'] = $types[0]['value'];
                }
            }

            $this->auction->setProductName($postdata['Product_Name']);
            $this->auction->setStartingPrice($postdata['Starting_Price']);
            $this->auction->setMaxPrice($postdata['Max_Price']);
            if (isset($postdata['start_datetime'])) {
                $this->auction->setStartDatetime($startDateTime);
                $this->auction->setData('start_time_utc', $startdateUTC);
            }
            if(isset($postdata['end_datetime'])) {
                $this->auction->setEndDatetime($endDateTime);
                $this->auction->setData('end_time_utc', $enddateUTC);
                $this->auction->setTempEnddate($endDateTime);
            }

            $this->auction->setProductId($this->getRequest()->getParam('product_id'));
            $this->auction->setVendorId($this->customerSession->getVendorId());
            $this->auction->setStatus($postdata['status']);
            $this->auction->setSellproduct($postdata['Sell_Product']);
            if (isset($postdata['start_datetime'])) {
                $this->auction->setTempStartdate($startDateTime);
            }
            $this->auction->setAuctionType($postdata['auction_type']);
            $this->auction->setIsBuyNow($postdata['is_buy_now']);
            $this->auction->setReservePrice($postdata['reserve_price']);
            $auctionsavedData = $this->auctionResource->save($this->auction);

            $this->messageManager->addSuccessMessage(__('Auction has been save successfully'));

            $last_auction = $this->auctionCollectionFactory->create()
                ->addFieldToFilter('product_id', $this->getRequest()->getParam('product_id'))
                ->addFieldToFilter('vendor_id', $this->customerSession->getVendorId())
                ->addFieldToSelect('*')->getLastItem();
            $this->_eventManager->dispatch('ox_auction_product_create', ['auction' => $last_auction]);
            if($this->getRequest()->getParam('go_to_invite')) {
                return $this->_redirect('csauction/auctionlist/privateauction', ['id'=>$last_auction->getId()]);
            }
            return $this->_redirect('csauction/auctionlist/index');
            return $this->_redirect('csauction/auctionlist/pay', ['id'=>$last_auction->getId()]);
        }

        // auction edit

        if($postdata && $this->getRequest()->getParam('id')) {

            $resultRedirect = $this->resultRedirectFactory->create();
            $date = $this->datetime->gmtDate();
            if(isset($postdata['start_datetime'])) {
                $startdateParts = explode(' ', $postdata['start_datetime'], 3);
                $startDateTimePrevious = $startdateParts[0] . ' ' . $startdateParts[1];
                $startDateTime =  $this->getNextQuarter($startDateTimePrevious);
                $starttimezoneOffset = $startdateParts[2];
                $timestamp = strtotime($postdata['start_datetime']);
                $startdateutc = new DateTime("@" . $timestamp);
                $startdateUTCprevious = $startdateutc->format('Y-m-d H:i:s');
                $startdateUTC =  $this->getNextQuarter($startdateUTCprevious);

            }
            if(isset($postdata['end_datetime'])) {
                $enddateParts = explode(' ', $postdata['end_datetime'], 3);
                $endDateTime = $enddateParts[0] . ' ' . $enddateParts[1];
                $endtimezoneOffset = $enddateParts[2];
                $timestamp = strtotime($postdata['end_datetime']);
                $enddateutc = new DateTime("@" . $timestamp);
                $enddateUTCprevious = $enddateutc->format('Y-m-d H:i:s');
                $enddateUTC =  $this->getNextQuarter($enddateUTCprevious);
            }
            $currenttime = $this->timezoneInterface
                ->date(new \DateTime($date))
                ->format('Y-m-d H:i:s');

            $bid =$this->biddetailsCollection->create()
                ->addFieldToFilter('product_id', $this->getRequest()->getParam('product_id'))
                ->addFieldToFilter('status', 'bidding');

            if(count($bid)> 0) {

                if (isset($postdata['start_datetime']) && strtotime($startDateTime) != strtotime($postdata['Temp_Startdate'])) {
                    $this->messageManager->addErrorMessage('Bid has been made so start datetime could not be change');
                    return $resultRedirect->setPath('csauction/addauction/auctionform', ['id' => $this->getRequest()->getParam('id')]);
                }
            }

            if($postdata['Max_Price'] != null && $postdata['Max_Price'] != 0) {
                if($postdata['Starting_Price'] >= $postdata['Max_Price']) {
                    $this->messageManager->addErrorMessage('Starting bid must be less than maximum bid');
                    return $resultRedirect->setPath('csauction/addauction/auctionform', ['id' => $this->getRequest()->getParam('id')]);
                }
            }

            if($postdata['Sell_Product'] == 'yes') {

                if ($postdata['Starting_Price'] > $postdata['Price']) {
                    $this->messageManager->addErrorMessage('Starting bid must be less than original product price');
                    return $resultRedirect->setPath('csauction/addauction/auctionform', ['id' => $this->getRequest()->getParam('id')]);
                }
                if ($postdata['Max_Price'] > $postdata['Price']) {
                    $this->messageManager->addErrorMessage('Starting bid must be less than original product price');
                    return $resultRedirect->setPath('csauction/addauction/auctionform', ['id' => $this->getRequest()->getParam('id')]);
                }
                if ($postdata['Max_Price'] == null) {
                    $postdata['Max_Price'] = $postdata['Price'];
                }
            }

            if(isset($postdata['End_Datetime']) && isset($startDateTime) && strtotime($endDateTime) < strtotime($startDateTime)) {
                $this->messageManager->addErrorMessage('Bidding end time must be greater than bidding start time');
                return $resultRedirect->setPath('csauction/addauction/auctionform', ['id' => $this->getRequest()->getParam('id')]);
            }
            if(isset($postdata['start_datetime']) && strtotime($startDateTime) > strtotime($currenttime)) {
                $data['status'] = 'not started';
            }

            if(isset($postdata['start_datetime']) && strtotime($startDateTime) <= strtotime($currenttime)) {
                $postdata['status'] = 'processing';
            }

            if(!isset($postdata['auction_type'])) {
                $types = $this->membershipHelper->getAuctionTypes();

                if($types == 2) {
                    $postdata['auction_type'] = 0;
                } else {
                    $postdata['auction_type'] = $types[0]['value'];
                }
            }

            $this->auction->load($this->getRequest()->getParam('id'));
            $this->auction->setMaxPrice($postdata['Max_Price']);
            if(isset($postdata['start_datetime'])) {
                $this->auction->setStartDatetime($startDateTime);
                $this->auction->setData('start_time_utc', $startdateUTC);
            }
            if(isset($postdata['End_Datetime'])) {
                $this->auction->setEndDatetime($endDateTime);
                $this->auction->setData('end_time_utc', $enddateUTC);
                $this->auction->setTempEnddate($endDateTime);
            }
            $this->auction->setSellproduct($postdata['Sell_Product']);
            if(isset($postdata['start_datetime'])) {
                $this->auction->setTempStartdate($startDateTime);
            }
            $this->auction->setAuctionType($postdata['auction_type']);
            $this->auction->setIsBuyNow($postdata['is_buy_now']);
            $this->auction->setReservePrice($postdata['reserve_price']);
            $this->auctionResource->save($this->auction);

            $this->messageManager->addSuccessMessage(__('Auction has been save successfully'));

            $this->_eventManager->dispatch('ox_auction_product_create', ['auction' => $this->auction]);

            if($this->getRequest()->getParam('go_to_invite')) {

                return $this->_redirect('csauction/auctionlist/privateauction', ['id'=>$this->getRequest()->getParam('id')]);
            }

            return $this->_redirect('csauction/auctionlist/pay', ['id'=>$this->getRequest()->getParam('id')]);

        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving auction'));
            return $this->_redirect('csauction/auctionlist/index');
        }
    }

    public function checkAuctionLimit()
    {
        $auctionCollection = $this->auctionCollectionFactory->create();
        $auctionCollection->addFieldToFilter('vendor_id', $this->customerSession->getVendorId());
        $count = $auctionCollection->getSize();

        $existing_subscription = $this->membershipHelper->getExistingSubcription($this->customerSession->getVendorId());

        if(empty($existing_subscription)) {
            $this->messageManager->addErrorMessage(__('Please take a subscription first.'));
            return true;
        }

        if(isset($existing_subscription[0]['auction_limit'])) {
            $auction_limit = $existing_subscription[0]['auction_limit'];

            if($count >= $auction_limit) {
                $this->messageManager->addErrorMessage(__('Auction Creation limit has Exceeded.'));
                return true;
            }
        }

        return false;
    }

    public function getNextQuarter($date)
    {

        $minutes = date('i', strtotime($date));
        $roundedMinutes = (ceil($minutes / 15) * 15) % 60;
        if($minutes > 45) {
            $hour = date('H', strtotime($date))+1;
        } else {
            $hour = date('H', strtotime($date));
        }
        $seconds = 00;
        $YEAR = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $day = date('d', strtotime($date));
        $data = mktime($hour, $roundedMinutes, $seconds, $month, $day, $YEAR);
        return date("Y-m-d G:i:s", $data);
    }
}
