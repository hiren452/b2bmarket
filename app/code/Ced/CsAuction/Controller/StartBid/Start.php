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

namespace Ced\CsAuction\Controller\StartBid;

use Ced\Auction\Helper;
use Ced\Auction\Model\Auction;
use Ced\Auction\Model\AuctionFactory;
use Ced\Auction\Model\BidDetails;
use Ced\Auction\Model\ResourceModel;
use Ced\Auction\Model\Winner;
use Ced\CsMarketplace\Model\Vproducts;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;

class Start extends \Ced\Auction\Controller\StartBid\Start
{
    private $vproduct;
    private $auctionFactory;
    private $helper;
    private $storeManager;
    private $resourceBidDetails;
    private $bidDetails;
    private $messageManager;
    private $resultFactory;
    private $customerSession;
    private $resultJsonFactory;
    private $auctionCollection;
    private $winnerResource;
    private $winner;
    private $emailHelper;
    private $configHelper;
    private $datetime;
    private $timezoneInterface;
    private $resourceConnection;

    public function __construct(
        AuctionFactory $auctionFactory,
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
        Vproducts $vproducts,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Helper\Data $helper,
        ResourceConnection $resourceConnection
    ) {
        $this->vproduct = $vproducts;
        $this->auctionFactory = $auctionFactory;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context, $session, $resultPageFactory, $bidDetails, $auction, $auctionCollection, $resourceBidDetails, $winner, $bidResourceModel, $winnerResource, $emailHelper, $configHelper, $datetime, $winnerCollection, $data, $timezoneInterface, $resultJsonFactory, $storeManager);
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
        $email = $this->customerSession->getCustomer()->getEmail();
        $connection = $this->resourceConnection->getConnection();
        $attributeIdSql = "SELECT `entity_id` FROM `ced_csmarketplace_vendor_varchar` WHERE `value` ='" . $email . "'";
        $vendorId = $connection->fetchOne($attributeIdSql, ['email' => $email]);

        if (!empty($vendorId)) {
            $auctionRunning = $this->auctionCollection->create()
                ->addFieldToFilter('product_id', $this->getRequest()->getParam('productId'))
                ->addFieldToFilter('vendor_id', $vendorId)
                ->addFieldToFilter('status', 'processing')->getFirstItem();
            if (!empty($auctionRunning->getData())) {
                $this->messageManager
                    ->addErrorMessage('You may not bid on your own products in an auction');
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }
        }

        if ($data['remove']) {
            if (!empty($data['auction']) && !empty($data['status'])) {
                $auction = $this->auctionFactory->create()->load($data['auction']);
                if ($data['status'] == 'not started') {
                    $status = 'processing';
                    $auction->setStatus($status);
                    $auction->save();
                } else {
                    $this->helper->closeAuction();
                }
                return $resultJson->setData('success');
            }
        }

        $call = $this->setError();
        if (isset($call)) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $productId = $this->getRequest()->getParam('productId');
        $bidId = $this->resourceBidDetails->create()
            ->addFieldToFilter('customer_id', $this->customerSession->getCustomerId())
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status', 'bidding')
            ->getFirstItem()->getId();
        $this->bidDetails->load($bidId);
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
        $data['auction_id'] = $this->getAuctionProduct()->getId();
        if($this->vproduct->getVendorIdByProduct($this->getRequest()->getParam('productId'))) {
            $data['vendor_id'] = $this->vproduct->getVendorIdByProduct($this->getRequest()->getParam('productId'));
        }

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
        if($this->getAuctionProduct()->getMaxPrice()!= null && $this->getAuctionProduct()->getMaxPrice() != 0) {
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
                //$id = $this->vproduct->getVendorIdByProduct($this->getRequest()->getParam('productId'));

                if ($this->vproduct->getVendorIdByProduct($this->getRequest()->getParam('productId'))) {
                    $winnerData['vendor_id'] = $this->vproduct->getVendorIdByProduct($this->getRequest()->getParam('productId'));
                }

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

                /*if ($this->customerSesion->getVendorId()) {
                    $this->emailHelper->sendWinningMailtoVendor(
                        $this->customerSesion->getVendorId(),
                        $this->getRequest()->getParam('productId'));
                }*/

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

}
