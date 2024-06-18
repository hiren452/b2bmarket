<?php

namespace Matrix\Auction\Preference\Helper;

use Ced\Auction\Helper\ConfigData;
use Ced\Auction\Helper\SendEmail;
use Ced\Auction\Model\ResourceModel;
use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class CustomizedSendEmail extends SendEmail
{
    const XML_PATH_EMAIL_TEMPLATE_FIELD = 'auction_entry_1/standard/email_winner_template';

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
        ConfigData $configData,
        \OX\Auction\Helper\Data $helperData
    ) {
        parent::__construct(
            $context,
            $transportBuilder,
            $storeManager,
            $stateInterface,
            $logger,
            $winnerCollection,
            $productRepository,
            $urlInterface,
            $currencyHelper,
            $customerFactory,
            $configData
        );
        $this->helperData = $helperData;
    }

    public function sendMailtoWinner($customerId, $productId)
    {
        $product = $this->productRepository->getById($productId);
        $customer = $this->customer->create()->load($customerId);

        $winnerProductExpire = $this->configData->getConfigData('auction_entry_1/standard/winner_product_expire');
        /* Send SMS Coding start */
        $this->_eventManager->dispatch('csmarketplace_buyer_won_auction', [
            'customer' => $customer,
            'product' => $product,
            'winner_product_expire' => $winnerProductExpire
        ]);
        /* Send SMS Coding end */
        $enableMail = json_decode($this->configData->getConfigData('auction_entry_1/standard/email_winner'), true);
        if ($enableMail) {
            $winnerData = $this->winnerCollection->create()->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('status', 'not purchased')->getLastItem();

            if ($winnerData) {
                $customerName = $winnerData->getCustomerName();
                $productName = $product->getName();
                $winningPrice = $this->currencyHelper->currency($winnerData->getWinningPrice(), true, false);
                $winningDate = $winnerData->getBidDate();
                $addtocartLink = $this->urlInterface->getUrl() . 'auction/index/';
            }
            $adminMail = $this->configData->getConfigData('auction_entry_1/standard/admin_email');
            $customerEmail = $customer->getEmail();

            if ($adminMail) {
                try {
                    $this->stateInterface->suspend();
                    $templateOptions = [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId()
                    ];
                    $templateVars = [
                        'store' => $this->storeManager->getStore(),
                        'customer_name' => $customerName,
                        'product_name' => $productName,
                        'winning_price' => $winningPrice,
                        'winning_date' => $winningDate,
                        'addtocart_link' => $addtocartLink,
                        'product_url' => $product->getProductUrl(),
                        'winner_product_expire' => $winnerProductExpire
                    ];
                    $templateId = $this->getTemplateId(self::XML_PATH_EMAIL_TEMPLATE_FIELD);
                    $from = [
                        'name' =>  $this->configData->getConfigData('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                        'email' => $this->configData->getConfigData('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                    ];
                    $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($customerEmail)
                        ->getTransport();
                    $transport->sendMessage();
                    $this->stateInterface->resume();
                } catch (Exception $e) {
                    $this->logger->addError($e);
                }
            }
        }
    }

    public function getTemplateId($xmlPath)
    {
        return $this->configData->getConfigData($xmlPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
        // $templateId = $this->configData->getConfigData(
        //     'auction_entry_1/standard/outbid_mail_template',
        //     \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $templateId = 'auction_entry_1_standard_auction_outbid';
        if ($adminMail) {
            try {
                $templateVars = [];
                // $templateOptions = array(
                //     'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                //     'store' => $this->storeManager->getStore()->getId()
                // );
                $templateVars['store'] = $this->storeManager->getStore();
                $templateVars['subject'] = 'Bid Out';
                $templateVars['customer_name'] = $customerName;
                $templateVars['product_name'] = $productName;
                $templateVars['product_url'] = $product->getProductUrl();
                $templateVars['customer_bidprice'] = $customerBid;
                $templateVars['out_bidprice'] = $outBid;
                $templateVars['product_link'] = $productUrl;
                $this->helperData->sendEmail($templateVars, $templateId, $customerEmail);

                // $templateVars = array(
                //     'store' => $this->storeManager->getStore(),
                //     'customer_name' => $customerName,
                //     'product_name' => $productName,
                //     'product_url' => $product->getProductUrl(),
                //     'customer_bidprice' => $customerBid,
                //     'out_bidprice' => $outBid,
                //     'product_link' => $productUrl
                // );

                // $from = array('email' => $adminMail, 'name' => 'Admin');
                // $to = array($customerEmail);
                // $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                //     ->setTemplateOptions($templateOptions)
                //     ->setTemplateVars($templateVars)
                //     ->setFrom($from)
                //     ->addTo($to)
                //     ->getTransport();
                // $transport->sendMessage();
                // $this->stateInterface->resume();
            } catch (\Exception $e) {
                $this->logger->addError($e);
            }
        }
    }

    /**
     * @param $customerId
     * @param $productId
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
        $templateId = $this->configData->getConfigData(
            'auction_entry_1/standard/admin_notify_closed_mail',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
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
                    'product_url' => $this->productRepository->getById($productId)->getProductUrl(),
                    'winning_price' => $winningPrice,
                    'winning_date' => $winningDate,
                    'auction_price' => $auctionPrice
                ];
                $from = ['email' => $adminMail, 'name' => 'Admin'];
                $to = [$adminMail];
                $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
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
        }
    }
}
