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
 * @package   Ced_Auction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Auction\Controller\StartBid;

use Ced\Auction\Helper;
use Ced\Auction\Model\Auction;
use Ced\Auction\Model\BidDetails;
use Ced\Auction\Model\ResourceModel;
use Ced\Auction\Model\Winner;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;

class Start extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    public $resultJsonFactory;
    public $timezoneInterface;

    /**
     * @var Session
     */
    public $customerSesion;

    /**
     * Start constructor.
     *
     * @param Context                                    $context
     * @param Session                                    $session
     * @param PageFactory                                $resultPageFactory
     * @param BidDetails                                 $bidDetails
     * @param Auction                                    $auction
     * @param ResourceModel\BidDetails\CollectionFactory $resourceBidDetails
     * @param Winner                                     $winner
     * @param ResourceModel\BidDetails                   $bidResourceModel
     * @param ResourceModel\Winner                       $winnerResource
     * @param SendEmail                                  $helper
     */
    public function __construct(
        Context $context,
        Session $session,
        PageFactory $resultPageFactory,
        BidDetails $bidDetails,
        Auction $auction,
        ResourceModel\Auction\CollectionFactory $auctionCollection,
        ResourceModel\BidDetails\CollectionFactory $resourceBidDetails,
        Winner $winner,
        ResourceModel\BidDetails $bidResourceModel,
        ResourceModel\Winner $winnerResource,
        Helper\SendEmail $emailHelper,
        Helper\ConfigData $configHelper,
        DateTime $datetime,
        ResourceModel\Winner\CollectionFactory $winnerCollection,
        Data $data,
        TimezoneInterface $timezoneInterface,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSesion = $session;
        $this->bidDetails = $bidDetails;
        $this->auction = $auction;
        $this->auctionCollection = $auctionCollection;
        $this->resourceBidDetails = $resourceBidDetails;
        $this->bidResourceModel = $bidResourceModel;
        $this->winner = $winner;
        $this->winnerResource = $winnerResource;
        $this->datetime = $datetime;
        $this->emailHelper = $emailHelper;
        $this->configHelper = $configHelper;
        $this->winnerCollection = $winnerCollection;
        $this->data = $data;
        $this->timezoneInterface = $timezoneInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {

        $resultJson = $this->resultJsonFactory->create();
        $data = $this->getRequest()->getPost();
        if ($data['remove']) {
            return $resultJson->setData('success');
        }
        $call = $this->setError();
        if (isset($call)) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());

            return $resultRedirect;

        }

        $productId = $this->getRequest()->getParam('productId');

        $this->bidDetails->addData($this->getBidData());
        $this->bidResourceModel->save($this->bidDetails);
        $this->messageManager->addSuccessMessage(__('You Successfully Placed The Bid'));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;

    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBidData()
    {
        $date = $this->datetime->gmtDate();
        $currenttime = $this->timezoneInterface
            ->date(new \DateTime($date))
            ->format('Y-m-d H:i:s');

        $data = [];
        $currency_code = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $data['product_id'] = $this->getRequest()->getParam('productId');
        $data['customer_id'] = $this->customerSesion->getCustomerId();
        $data['customer_name'] = $this->customerSesion->getCustomerData()->getFirstname();
        $data['bid_price'] = $this->getRequest()->getParam('bidprice');
        $data['bid_date'] = $currenttime;
        $data['status'] = 'bidding';
        $data['currency'] = $currency_code;
        $productId = $this->getRequest()->getParam('productId');
        $bidId = $this->resourceBidDetails->create()
            ->addFieldToFilter('customer_id', $this->customerSesion->getCustomerId())
            ->addFieldToFilter('product_id', $productId);
        if ($bidId->count() == 0) {
            $data['winner'] = 1;
        } else {
            $bid = $bidId->getData();
            $data['winner'] = $bid[0]['winner'] + 1;
        }

        if ($this->getAuctionProduct()->getMaxPrice() != null && $this->getAuctionProduct()->getMaxPrice() != 0 && $this->customerSesion->getCustomerData() != null) {

            if ($this->getRequest()->getParam('bidprice') >= $this->getAuctionProduct()->getMaxPrice()) {

                $winnerData = [];
                $winnerData['product_id'] = $this->getRequest()->getParam('productId');
                $winnerData['customer_id'] = $this->customerSesion->getCustomerId();
                $winnerData['customer_name'] = $this->customerSesion->getCustomerData()->getFirstname();
                $winnerData['bid_date'] = $currenttime;
                $winnerData['status'] = 'not purchased';
                $winnerData['currency'] = $currency_code;
                $winnerData['auction_price'] = $this->getAuctionProduct()->getMaxPrice();
                $winnerData['winning_price'] = $this->getRequest()->getParam('bidprice');
                $winnerData['add_to_cart'] = false;

                $data['status'] = 'won';
                $this->_eventManager->dispatch('auction_winner', ['winner_data' => $winnerData, 'winner' => $this->winner]);
                $this->winnerResource->save($this->winner);

                $allWinnerData = $this->resourceBidDetails->create()->addFieldToFilter('product_id', $this->getRequest()->getParam('productId'))
                    ->addFieldToFilter('status', 'bidding');
                $allWinnerData->setDataToAll('status', 'biddingClosed');
                $allWinnerData->save();

                $this->messageManager->addSuccessMessage(__('You Won This Bid'));
                $this->closeAuction();

                $enableMail = $this->configHelper->getConfigData('auction_entry_1/standard/email_winner');
                if ($enableMail) {
                    $this->emailHelper->sendMailtoWinner(
                        $this->customerSesion->getCustomerId(),
                        $this->getRequest()->getParam('productId')
                    );
                }

                $enableAdminMail = $this->configHelper->getConfigData('auction_entry_1/standard/email_admin_auctionclosed');
                if($enableAdminMail) {
                    $this->emailHelper->sendAuctionClosedMailtoAdmin(
                        $this->customerSesion->getCustomerId(),
                        $this->getRequest()->getParam('productId')
                    );
                }
            }
        }
        return $data;
    }

    /**
     * @return Auction
     */
    public function getAuctionProduct()
    {
        $auction = $this->auctionCollection->create()
            ->addFieldToFilter('product_id', $this->getRequest()->getParam('productId'))
            ->addFieldToFilter('status', 'processing')->getFirstItem();
        return $auction;
    }

    public function closeAuction()
    {
        $date = $this->datetime->gmtDate();
        $currenttime = $this->timezoneInterface
            ->date(new \DateTime($date))
            ->format('Y-m-d H:i:s');

        $auction = $this->auctionCollection->create()
            ->addFieldToFilter('product_id', $this->getRequest()->getParam('productId'))
            ->addFieldToFilter('status', 'processing');

        $auction->setDataToAll('status', 'closed');
        $auction->setDataToAll('product_sold', $currenttime);
        $auction->save();
        return $auction;
    }

    /**
     * @return \Magento\Framework\Message\ManagerInterface
     */
    public function setError()
    {
        $params = $this->getRequest()->getParams();

        $biddetails = $this->resourceBidDetails->create()
            ->addFieldToFilter('product_id', $params['productId'])
            ->addFieldToFilter('status', 'bidding')
            ->setOrder('bid_price', 'ASC')->getLastItem();

        $currentBid = $biddetails->getBidPrice();

        $defaultNextBid = json_decode($this->configHelper->getConfigData('auction_entry_1/standard/increment_default_price'), true);
        $nextbid = json_decode($this->configHelper->getConfigData('auction_entry_1/standard/increment_price'), true);

        $nextBidAmount = [];
        $nextAmount = 0;

        $lastBidder = $this->resourceBidDetails->create()
            ->addFieldToFilter('product_id', $params['productId'])
            ->addFieldToFilter('status', 'bidding')
            ->setOrder('bid_price', 'ASC')
            ->getLastItem()->getCustomerId();
        $currentBidder = $this->customerSesion->getCustomerId();

        // if ($lastBidder == $currentBidder) {
        //     return $this->messageManager
        //         ->addErrorMessage('you cannot place the bid as you have already placed the highest bid');
        // }

        if ($nextbid != 0 || $defaultNextBid != 0) {
            foreach ($nextbid as $bid) {

                if ($currentBid != 0 && $currentBid >= $bid['pricefrom'] && $currentBid <= $bid['priceto']) {
                    array_push($nextBidAmount, ['pricefrom' => $bid['pricefrom'], 'priceto' => $bid['priceto'], 'incrementedprice' => $bid['incrementedprice']]);
                }
                if ($currentBid != 0 && count($nextBidAmount) != 0) {
                    $nextAmount = $currentBid + $nextBidAmount[0]['incrementedprice'];
                }
                if ($currentBid != 0 && count($nextBidAmount) == 0) {
                    if ($defaultNextBid) {
                        $nextAmount = $currentBid + $defaultNextBid;
                    }
                }
            }
        }

        $enableIncrement = json_decode(
            $this->configHelper
                ->getConfigData('auction_entry_1/standard/increment_enable'),
            true
        );

        if (!preg_match('/^[0-9]\d*(\.\d+)?$/', $params['bidprice'])) {
            return $this->messageManager
                ->addErrorMessage('Bid must be in decimal');
        }

        if ($enableIncrement) {
            if ($params['bidprice'] < $nextAmount) {
                return $this->messageManager
                    ->addErrorMessage('Bidding Price must be greater than Next Bidding Amount');
            } elseif ($params['bidprice'] < $params['startAmount']) {
                return $this->messageManager
                    ->addErrorMessage('Bidding Price must be greater than Starting Bid Amount');
            }
        } elseif ($params['purchasable'] == 'yes') {

            if ($params['bidprice'] >= $params['maxprice']) {
                return $this->messageManager
                    ->addErrorMessage("You can't place the bid equal to or greater than the product's price.You can add the product directly to cart");
            }
        } elseif ($params['bidprice'] <= $currentBid) {
            return $this->messageManager
                ->addErrorMessage('Bidding Price must be greater than Current Bid Price');
        } elseif ($params['bidprice'] < $params['startAmount']) {
            return $this->messageManager
                ->addErrorMessage('Bidding Price must be greater than Starting Bid Amount');
        }

        if (count($biddetails->getData()) != false) {
            $enableMail = json_decode($this->configHelper->getConfigData('auction_entry_1/standard/email_winner'), true);
            if ($enableMail) {
                $this->emailHelper->outBidMail(
                    $biddetails->getCustomerId(),
                    $biddetails->getProductId(),
                    $params['bidprice'],
                    $currentBid
                );

            }
        }
    }
}
