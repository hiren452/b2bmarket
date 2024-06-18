<?php

namespace OX\Auction\Helper;

use Ced\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Ced\CsMarketplace\Model\Vendor;
use Ced\CsMarketplace\Model\VendorFactory;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Matrix\CsAuction\Model\ResourceModel\PrivateAuction\CollectionFactory as PrivateCollection;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    public const IS_SEND_EMAIL_TO_VENDOR_ON_BID = 'auction_entry_1/standard/send_bid_email_to_vendor';
    public const EMAIL_TEMPLATE_FOR_BID = 'auction_entry_1/standard/vendor_bid_email_template';
    public const FROM_ADMIN_EMAIL_ID = 'auction_entry_1/standard/admin_email';
    public const NOTIFY_WINNER_TO_VENDOR_TEMPLATE = 'auction_entry_1/standard/admin_notify_closed_mail';
    /** @var LoggerInterface */
    public $logger;
    /** @var SerializerInterface */
    public $serializer;
    /** @var VendorFactory */
    protected $vendorDetails;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    /** @var CustomerRepositoryInterface */

    protected $customerRepository;
    /** @var ProductRepository */

    protected $productRepository;

    protected $customerSession;

    /** @var CollectionFactory */
    protected $collectionFactory;

    /** @var PrivateCollection */
    protected $privateBuyer;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param VendorFactory $vendorFactory
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepository $productRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        VendorFactory $vendorFactory,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository,
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $collectionFactory,
        PrivateCollection $privateBuyer
    ) {
        $this->serializer = $serializer;
        $this->productRepository = $productRepository;
        $this->vendorDetails = $vendorFactory;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->privateBuyer = $privateBuyer;
        parent::__construct($context);
    }

    /**
     * Get enable/disable status
     *
     * @return mixed
     */
    public function isSendEmailenabledForbid()
    {
        return $this->scopeConfig
            ->getValue(self::IS_SEND_EMAIL_TO_VENDOR_ON_BID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Template for the vendor bid
     *
     * @return mixed
     */
    public function getTemplateForBid()
    {
        return $this->scopeConfig
            ->getValue(self::EMAIL_TEMPLATE_FOR_BID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Vendor Details
     *
     * @param int $vendorId
     * @return Vendor
     */
    public function getVendor($vendorId)
    {
        return $this->vendorDetails->create()->load($vendorId);
    }

    /**
     * Get Winner Template
     *
     * @return mixed
     */
    public function getNotifyWinTempForVendor()
    {
        return $this->scopeConfig
            ->getValue(self::NOTIFY_WINNER_TO_VENDOR_TEMPLATE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Send email
     *
     * @param array $templateVars
     * @param string|int $templateId
     * @param array|string $to
     */
    public function sendEmail($templateVars, $templateId, $to)
    {
        try {
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->scopeConfig
                    ->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE),
                'email' => $this->scopeConfig
                    ->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE)
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($templateVars)
                ->setFromByScope($sender)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Get admin email address
     *
     * @return mixed
     */
    public function getAdminEmailId()
    {
        return $this->scopeConfig
            ->getValue(self::FROM_ADMIN_EMAIL_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Customer Details
     *
     * @param int $customerId
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerDetails($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * Get product Details
     *
     * @param int $productId
     * @return ProductInterface|mixed
     * @throws NoSuchEntityException
     */
    public function getProductDetail($productId)
    {
        return $this->productRepository->getById($productId);
    }

    public function getCustomerId()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        return $customerId;

    }

    public function getAuction($customerId)
    {
        $to = [];
        $privateBuyer = $this->privateBuyer->create()->addFieldToSelect('auction_id')->addFieldToFilter('customer_ids', ['like' => '%' . $customerId . '%']);
        if(!empty($privateBuyer)) {

            foreach($privateBuyer as $auctionId) {
                $auctionCollection = $this->collectionFactory->create()->addFieldToFilter('id', $auctionId->getAuctionId());
                if(!empty($auctionCollection->getData())) {
                    $to[] = $auctionCollection->getData();
                }
            }
            if(!empty($to)) {
                $to = call_user_func_array('array_merge', $to);
            }

        }
        return $to;
    }

    public function getPagerHtml()
    {
        $pagerBlock = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager');
        $pagerBlock->setAvailableLimit($this->getAvailableLimit());
        $pagerBlock->setCollection($this->getAuction($this->getCustomerId()));
        return $pagerBlock->toHtml();
    }

    public function isPagerNeeded($auctionCount)
    {
        $limit = 10;
        if ($auctionCount > $limit) {
            return true;
        }
        return false;
    }

    public function getAuctionDetails($productId)
    {

        $auctionRunning = $this->collectionFactory->create()
        ->addFieldToFilter('product_id', $productId)
        ->addFieldToFilter('status', ['processing','not started'])->getFirstItem();
        return $auctionRunning;
    }

}
