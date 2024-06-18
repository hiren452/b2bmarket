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

namespace Ced\Auction\Helper;

use Ced\Auction\Model\ResourceModel;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SendEmail extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    public $transportBUilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    public $stateInterface;

    public $productRepository;

    public $logger;

    public $currencyHelper;

    public $configData;

    public $customerFactory;

    /**
     * SendEmail constructor.
     *
     * @param Context               $context
     * @param TransportBuilder      $transportBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $stateInterface,
        LoggerInterface $logger,
        ResourceModel\Winner\CollectionFactory $winnerCollection,
        ProductRepository $productRepository,
        UrlInterface $urlInterface,
        Data $currencyHelper,
        CustomerFactory $customerFactory,
        ConfigData $configData
    ) {
        $this->stateInterface = $stateInterface;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->winnerCollection = $winnerCollection;
        $this->productRepository = $productRepository;
        $this->urlInterface = $urlInterface;
        $this->currencyHelper = $currencyHelper;
        $this->customer = $customerFactory;
        $this->configData = $configData;
        parent::__construct($context);
    }

    /**
     * @param  $customerId
     * @param  $productId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendMailtoWinner($customerId, $productId)
    {
        $winnerData = $this->winnerCollection->create()->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status', 'not purchased')->getLastItem();

        if ($winnerData) {
            $customerName = $winnerData->getCustomerName();
            $productName = $this->productRepository->getById($productId)->getName();
            $winningPrice = $this->currencyHelper->currency($winnerData->getWinningPrice(), true, false);
            $winningDate = $winnerData->getBidDate();
            $addtocartLink = $this->urlInterface->getUrl() . 'auction/index/';
        }
        $adminMail = $this->configData->getConfigData('auction_entry_1/standard/admin_email');
        $customerEmail = $this->customer->create()->load($customerId)->getEmail();

        if ($adminMail) {
            try {
                $templateOptions = [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ];

                $templateVars = [
                    'store' => $this->storeManager->getStore(),
                    'customer_name' => $customerName,
                    'product_name' => $productName,
                    'winning_price' => $winningPrice,
                    'winning_date' => $winningDate,
                    'addtocart_link' => $addtocartLink
                ];

                $from = ['email' => $adminMail, 'name' => 'Admin'];
                $transport = $this->transportBuilder->setTemplateIdentifier('winning_mail')
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($customerEmail)
                    ->getTransport();
                $transport->sendMessage();
                $this->stateInterface->resume();
            } catch (\Exception $e) {
                $this->logger->addError($e);
            }
        }
    }

    public function outBidMail($customerId, $productId, $currentbid, $prevoiusbid)
    {
        if($customerId) {
            $customer = $this->customer->create()->load($customerId);
        }

        if($customer) {
            $customerName = $customer->getName();
            $customerEmail = $customer->getEmail();
        }

        if($productId) {
            $product = $this->productRepository->getById($productId);
        }

        if($product) {
            $productName = $product->getName();
            $productUrl = $product->getProductUrl();
        }

        $customerBid = $this->currencyHelper->currency($prevoiusbid, true, false);
        $outBid = $this->currencyHelper->currency($currentbid, true, false);

        $adminMail = $this->configData->getConfigData('auction_entry_1/standard/admin_email');

        if ($adminMail) {
            try {
                $templateOptions = [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ];

                $templateVars = [
                    'store' => $this->storeManager->getStore(),
                    'customer_name' => $customerName,
                    'product_name' => $productName,
                    'customer_bidprice' => $customerBid,
                    'out_bidprice' => $outBid,
                    'product_link' => $productUrl
                ];

                $from = ['email' => $adminMail, 'name' => 'Admin'];
                $transport = $this->transportBuilder->setTemplateIdentifier('outbid_mail')
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($customerEmail)
                    ->getTransport();
                $transport->sendMessage();
                $this->stateInterface->resume();
            } catch (\Exception $e) {
                $this->logger->addError($e);
            }
        }
    }

    /**
     * @param  $customerId
     * @param  $productId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendAuctionClosedMailtoAdmin($customerId, $productId)
    {
        $winnerData = $this->winnerCollection->create()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status', 'not purchased')->getLastItem();

        if ($winnerData) {
            $customerName = $winnerData->getCustomerName();
            $productName = $this->productRepository->getById($productId)->getName();
            $winningPrice = $this->currencyHelper->currency($winnerData->getWinningPrice(), true, false);
            $winningDate = $winnerData->getBidDate();
            $auctionPrice = $this->currencyHelper->currency($winnerData->getAuctionPrice(), true, false);
        }

        $adminMail = $this->configData->getConfigData('auction_entry_1/standard/admin_email');

        if($adminMail) {
            try {

                $templateOptions = [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ];

                $templateVars = [
                    'store' => $this->storeManager->getStore(),
                    'customer_name' => $customerName,
                    'product_name' => $productName,
                    'winning_price' => $winningPrice,
                    'winning_date' => $winningDate,
                    'auction_price' => $auctionPrice
                ];
                $from = ['email' => $adminMail, 'name' => 'Admin'];

                $transport = $this->transportBuilder->setTemplateIdentifier('admin_notify_closed_mail')
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($adminMail)
                    ->getTransport();
                $transport->sendMessage();
                $this->stateInterface->resume();
            } catch (\Exception $e) {
                $this->logger->addError($e);
            }
        }
    }

    /**
     * @param  $customerId
     * @param  $productId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    /*public function sendPurchaseMail($customerId, $productId)
    {
        $winnerData = $this->winnerCollection->create()->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status', 'not purchased')->getLastItem();

        if ($winnerData) {
            $customerName = $winnerData->getCustomerName();
            $productName = $this->productRepository->getById($productId)->getName();
            $winningPrice = $this->currencyHelper->currency($winnerData->getWinningPrice(), true, false);
        }

        try {

            $templateOptions = array(
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()
            );

            $templateVars = array(
                'store' => $this->storeManager->getStore(),
                'customer_name' => $customerName,
                'product_name' => $productName,
                'winning_price' => $winningPrice
            );
            $from = array('email' => "sweetyagarwal1919@gmail.com", 'name' => 'Sweety Agarwal');
            $to = array('sweetyagarwal1919@yahoo.com');
            $transport = $this->transportBuilder->setTemplateIdentifier('purchase_mail')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->stateInterface->resume();
        } catch (\Exception $e) {
            $this->logger->addError($e);
        }
    }*/

    /**
     * @param  $customerId
     * @param  $productId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    /*public function sendPurchaseMailtoAdmin($customerId, $productId)
    {
        $winnerData = $this->winnerCollection->create()->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status', 'not purchased')->getLastItem();

        if ($winnerData) {
            $customerName = $winnerData->getCustomerName();
            $productName = $this->productRepository->getById($productId)->getName();
            $winningPrice = $this->currencyHelper->currency($winnerData->getWinningPrice(), true, false);
            $auctionPrice = $this->currencyHelper->currency($winnerData->getAuctionPrice(), true, false);
        }

        try {

            $templateOptions = array(
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()
            );

            $templateVars = array(
                'store' => $this->storeManager->getStore(),
                'customer_name' => $customerName,
                'product_name' => $productName,
                'winning_price' => $winningPrice,
                'auction_price' => $auctionPrice
            );
            $from = array('email' => "sweetyagarwal1919@gmail.com", 'name' => 'Sweety Agarwal');
            $to = array('sweetyagarwal1919@yahoo.com');
            $transport = $this->transportBuilder->setTemplateIdentifier('admin_notify_purchase_mail')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->stateInterface->resume();
        } catch (\Exception $e) {
            $this->logger->addError($e);
        }
    }*/

    /**
     * @param  $customerId
     * @param  $productId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    /*public function sendProductDeleteMail($customerId, $productId)
    {
        $winnerData = $this->winnerCollection->create()->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status', 'not purchased')->getLastItem();

        if ($winnerData) {
            $customerName = $winnerData->getCustomerName();
            $productName = $this->productRepository->getById($productId)->getName();
            $winningPrice = $this->currencyHelper->currency($winnerData->getWinningPrice(), true, false);
            $auctionPrice = $this->currencyHelper->currency($winnerData->getAuctionPrice(), true, false);
        }

        try {

            $templateOptions = array(
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()
            );

            $templateVars = array(
                'store' => $this->storeManager->getStore(),
                'customer_name' => $customerName,
                'product_name' => $productName,
                'winning_price' => $winningPrice,
                'auction_price' => $auctionPrice
            );
            $from = array('email' => "sweetyagarwal1919@gmail.com", 'name' => 'Sweety Agarwal');
            $to = array('sweetyagarwal1919@yahoo.com');
            $transport = $this->transportBuilder->setTemplateIdentifier('admin_notify_purchase_mail')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->stateInterface->resume();
        } catch (\Exception $e) {
            $this->logger->addError($e);
        }
    }*/

}
