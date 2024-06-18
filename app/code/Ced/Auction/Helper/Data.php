<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 10/3/19
 * Time: 9:05 PM
 */

namespace Ced\Auction\Helper;

use Ced\Auction\Model;
use Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use OX\Auction\Helper\Data as HelperData;

class Data extends AbstractHelper
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    public function __construct(
        Context $context,
        Model\ResourceModel\Auction\CollectionFactory $auctionCollection,
        ProductRepository $productCollection,
        DateTime $datetime,
        TimezoneInterface $timezoneInterface,
        CollectionFactory $bidDetails,
        \Ced\Auction\Model\Winner $winner,
        ConfigData $configHelper,
        SendEmail $emailHelper,
        Session $customerSession,
        HelperData $helperData,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->customerSession = $customerSession;
        $this->productCollection = $productCollection;
        $this->auctionCollection = $auctionCollection;
        $this->dateTime = $datetime;
        $this->timezoneInterface = $timezoneInterface;
        $this->bidDetails = $bidDetails;
        $this->winner = $winner;
        $this->configHelper = $configHelper;
        $this->emailHelper = $emailHelper;
        $this->helperData = $helperData;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function closeAuction()
    {

        $auctionRunning = $this->auctionCollection->create()->addFieldToFilter('status', 'processing');
        if (count($auctionRunning->getData()) != false) {
            foreach ($auctionRunning as $auction) {
                $endTimeTimeZon = $auction->getData('end_time_utc');
                $date = $this->dateTime->gmtDate();
                $currenttime = $this->timezoneInterface
                    ->date(new \DateTime($date))
                    ->format('Y-m-d H:i:s');
                $endTime = $this->timezoneInterface
                    ->date(new \DateTime($endTimeTimeZon))
                    ->format('Y-m-d H:i:s');

                if ($endTime <= $currenttime) {
                    $reserverPrice = $auction->getReservePrice();
                    if(!empty($reserverPrice)) {
                        $bidExist = $this->bidDetails->create()->addFieldToFilter('product_id', $auction->getProductId())
                            ->addFieldToFilter('status', 'bidding');
                        if (count($bidExist->getData()) != false) {
                            $bid = $this->bidDetails->create()->addFieldToFilter('product_id', $auction->getProductId())
                                ->addFieldToFilter('status', 'bidding')
                                ->setOrder('bid_price', 'ASC')->getLastItem();
                            if($bid->getBidPrice() < $reserverPrice) {
                                $auction->setData('status', 'terminated');
                                $auction->save();
                                $this->sendTerminationMail($auction, $bid);
                            } else {
                                $this->getWinnerDetails($auction);
                            }
                        } else {
                            $auction->setData('status', 'terminated');
                            $auction->save();
                            $bid = null;
                            $this->sendTerminationMail($auction, $bid);
                        }
                    } else {
                        $this->getWinnerDetails($auction);
                    }
                }
            }
        }
        return false;
    }
    public function sendTerminationMail($auction, $bid = null)
    {
        if($bid == null) {
            $bidAmount = 'Not Bidded';
        } else {
            $bidAmount = $bid->getBidPrice();
        }
        $toDetails = [];
        $templateVars = [];
        $templateVars['subject'] = 'Auction Terminated';
        $templateVars['title'] = 'Auction is Terminated';
        $templateVars['product_name'] = $auction->getProductName();
        $templateVars['auction_status'] = $auction->getStatus();
        $templateVars['starting_date'] = $this->timezoneInterface->date($auction->getData('start_time_utc'))->format('Y-m-d H:i');
        $templateVars['ending_date'] = $this->timezoneInterface->date($auction->getData('end_time_utc'))->format('Y-m-d H:i');
        $templateVars['last_bid'] = $bidAmount;
        $vendor = $this->helperData->getVendor($auction->getVendorId());
        $vendorDetails['name'] = $vendor->getName();
        $vendorDetails['email'] = $vendor->getEmail();
        array_push($toDetails, $vendorDetails);
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $adminEmail  = $this->scopeConfig->getValue('auction_entry_1/standard/admin_email', $storeScope);
        $adminDetails['name'] =  'Admin';
        $adminDetails['email'] =  $adminEmail;
        array_push($toDetails, $adminDetails);
        $emailTemplate = 'auction_entry_1_standard_auction_terminated';
        foreach ($toDetails as $toDetail) {
            $templateVars['recipient_name'] = $toDetail['name'];
            $this->helperData->sendEmail($templateVars, $emailTemplate, $toDetail['email']);
        }
        return true;
    }

    public function getWinnerDetails($auction)
    {

        if (count($auction->getData()) != false) {
            $endTimeTimeZon = $auction->getData('end_time_utc');
            $endTime = $this->timezoneInterface
                ->date(new \DateTime($endTimeTimeZon))
                ->format('Y-m-d H:i:s');
            $auction->setData('status', 'closed');
            $auction->setData('product_sold', $endTime);
            $auction->save();
        }
        $bidExist = $this->bidDetails->create()->addFieldToFilter('product_id', $auction->getProductId())
            ->addFieldToFilter('status', 'bidding');

        if (count($bidExist->getData()) != false) {

            $bid = $this->bidDetails->create()->addFieldToFilter('product_id', $auction->getProductId())
                ->addFieldToFilter('status', 'bidding')
                ->setOrder('bid_price', 'ASC')->getLastItem();

            if (count($bid->getData()) != false) {
                $bid->setStatus('won');
                $bid->save();
            }

            $allWinnerData = $this->bidDetails->create()->addFieldToFilter('product_id', $auction->getProductId())
                ->addFieldToFilter('status', 'bidding');

            if (count($allWinnerData->getData()) != false) {
                $allWinnerData->setDataToAll('status', 'biddingClosed');
                $allWinnerData->save();
            }

            $winnerData = [];
            $winnerData['product_id'] = $auction->getProductId();
            $winnerData['customer_id'] = $bid['customer_id'];
            $winnerData['customer_name'] = $bid['customer_name'];
            $winnerData['auction_price'] = $auction->getMaxPrice();
            $winnerData['bid_date'] = $endTime;
            $winnerData['status'] = 'not purchased';
            $winnerData['winning_price'] = $bid['bid_price'];
            $winnerData['add_to_cart'] = false;

            // if ($this->vproductsFactory->create()->getVendorIdByProduct($auction->getProductId())) {
            //     $winnerData['vendor_id'] = $this->vproductsFactory->create()->getVendorIdByProduct($auction->getProductId());
            // }

            $this->winner->setData($winnerData);
            $this->winner->save();
            $enableMail = $this->configHelper->getConfigData('auction_entry_1/standard/email_winner');
            if ($enableMail) {
                $this->emailHelper->sendMailtoWinner(
                    $this->customerSession->getCustomerId(),
                    $auction->getProductId()
                );
            }
        }
    }
}
