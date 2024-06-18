<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CsAuction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsAuction\Block\Product\View\Type;

use Ced\Auction\Helper\ConfigData;
use Ced\Auction\Helper\SendEmail;
use Ced\Auction\Model;
use Ced\Auction\Model\BidDetails;
use Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use OX\Auction\Helper\Data as HelperData;

class SimpleProductAuction extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ced\Auction\Model\Auction
     */
    public $auction;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    public $_bidCollection;

    public $dateTime;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * SimpleProductAuction constructor.
     *
     * @param Context           $context
     * @param Session           $customerSession
     * @param Auction           $auction
     * @param DateTime          $dateTime
     * @param CollectionFactory $bidDetails
     * @param UrlInterface      $urlInterface
     * @param array             $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Model\Auction $auction,
        Model\ResourceModel\Auction\CollectionFactory $auctionCollection,
        DateTime $dateTime,
        CollectionFactory $bidDetails,
        UrlInterface $urlInterface,
        ProductRepository $product,
        TimezoneInterface $timezoneInterface,
        BidDetails $bidInfo,
        ConfigData $configHelper,
        SendEmail $emailHelper,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ResponseFactory $result,
        \Ced\Auction\Model\ResourceModel\Winner $winnerResource,
        \Ced\Auction\Model\Winner $winner,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        HelperData $helperData,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->auction = $auction;
        $this->dateTime = $dateTime;
        $this->bidDetails = $bidDetails;
        $this->urlInterface = $urlInterface;
        $this->product = $product;
        $this->auctionCollection = $auctionCollection;
        $this->timezoneInterface = $timezoneInterface;
        $this->bidInfo = $bidInfo;
        $this->configHelper = $configHelper;
        $this->emailHelper = $emailHelper;
        $this->resultRedirectFactory = $result;
        $this->redirect = $redirect;
        $this->winnerResource = $winnerResource;
        $this->winner = $winner;
        $this->vproduct = $vproducts;
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * @return boolean|Auction
     */
    public function getAuctionDetails()
    {

        $auctionRunning = $this->auctionCollection->create()
            ->addFieldToFilter('product_id', $this->getRequest()->getParam('id'))
            ->addFieldToFilter('status', ['processing','not started'])->getFirstItem();
        $vendorId = $auctionRunning->getVendorId();
        $auctionclosed = $auctionRunning->getData();

        // if (count($auctionclosed) == 0) {

        //     return false;
        // }

        /*$extendedTime = $auctionRunning->getExtendedTime();*/
        $endTime = $auctionRunning->getEndDatetime();

        $date = $this->dateTime->gmtDate();
        $currenttime = $this->timezoneInterface
            ->date(new \DateTime($date))
            ->format('Y-m-d H:i:s');

        if ($endTime <= $currenttime) {
            $reserverPrice = $auctionRunning->getReservePrice();
            if(!empty($reserverPrice)) {
                $bidExist = $this->bidDetails->create()->addFieldToFilter('product_id', $auctionRunning->getProductId())
                    ->addFieldToFilter('status', 'bidding');
                if (count($bidExist->getData()) != false) {
                    $bid = $this->bidDetails->create()->addFieldToFilter('product_id', $auctionRunning->getProductId())
                        ->addFieldToFilter('status', 'bidding')
                        ->setOrder('bid_price', 'ASC')->getLastItem();
                    if($bid->getBidPrice() < $reserverPrice) {
                        $auctionRunning->setData('status', 'terminated');
                        $auctionRunning->save();
                        $this->sendTerminationMail($auctionRunning, $bid);
                    } else {
                        $this->getWinnerDetails($auctionRunning);
                    }
                } else {
                    $this->getWinnerDetails($auctionRunning);
                }
            } else {
                $this->getWinnerDetails($auctionRunning);
            }
        }

        return $auctionRunning;
    }

    /**
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @return TimezoneInterface
     */
    public function getTimeZone()
    {
        return $this->timezoneInterface;
    }

    public function getDateTimeCurrent()
    {
        return $this->timezoneInterface->date()
            ->format('Y-m-d H:i:s');
    }

    /**
     * @return \Ced\Auction\Model\ResourceModel\BidDetails\Collection
     */
    public function getBidDetails()
    {
        $this->_bidCollection = $this->bidDetails->create()
            ->addFieldToFilter('product_id', $this->getRequest()->getParam('id'))
            ->addFieldToFilter('status', 'bidding')
            ->setOrder('bid_price', 'ASC');
        return $this->_bidCollection;
    }

    /**
     * getting customer data
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * getting url
     *
     * @return string
     */
    public function getLoginUrl()
    {
        $url = $this->urlInterface->getCurrentUrl();

        $login_url = $this->urlInterface
            ->getUrl(
                'customer/account/login',
                ['referer' => base64_encode($url)]
            );
        return $login_url;

        /*return $this->urlInterface->getUrl();*/
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductData()
    {
        $productId = $this->getRequest()->getParam('id');
        return $this->product->getById($productId);
    }

    /**
     * @return Model\ResourceModel\Auction\Collection
     */
    public function getallAuctionProducts()
    {
        $auctionRunning = $this->auctionCollection->create()->addFieldToFilter('status', 'processing');
        return $auctionRunning;
    }

    public function getAuctionEnable()
    {
        return $this->configHelper->getConfigData('auction_entry_1/standard/standard_enable');
    }

    public function getNextBid()
    {
        return json_decode(
            $this->configHelper->getConfigData('auction_entry_1/standard/increment_price'),
            true
        );
    }

    public function getIncremntAuction()
    {
        return $this->configHelper->getConfigData('auction_entry_1/standard/increment_enable');
    }

    public function getDefaultNextBid()
    {
        return $this->configHelper->getConfigData('auction_entry_1/standard/increment_default_price');
    }
    public function sendTerminationMail($auction, $bid)
    {
        $toDetails = [];
        $templateVars = [];
        $templateVars['subject'] = 'Auction Terminated';
        $templateVars['title'] = 'Auction is Terminated';
        $templateVars['product_name'] = $auction->getProductName();
        $templateVars['auction_status'] = $auction->getStatus();
        $templateVars['starting_date'] = $this->timezoneInterface->date($auction->getData('start_time_utc'))->format('Y-m-d H:i');
        $templateVars['ending_date'] = $this->timezoneInterface->date($auction->getData('end_time_utc'))->format('Y-m-d H:i');
        $templateVars['last_bid'] = $bid->getBidPrice();
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
    }

    public function getWinnerDetails($auction)
    {

        if (count($auction->getData()) != false) {
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
