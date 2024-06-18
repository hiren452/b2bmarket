<?php

namespace OX\Auction\Observer;

use Ced\CsMembership\Helper\Data;
use Exception;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use OX\Auction\Helper\Data as HelperData;
use OX\Auction\Model\NotifyDatesFactory;
use OX\Auction\Model\ResourceModel\NotifyDates;

class AuctionProduct implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var Data
     */
    protected $membershipHelper;
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var ProductFactory
     */
    protected $productFactory;
    /** @var HelperData */
    protected $helperData;
    /** @var NotifyDatesFactory */
    protected $notifyModelFactory;
    /** @var NotifyDates */
    protected $notifyResourceModel;

    /** @var TimezoneInterface */
    protected $timezone;

    /**
     * AuctionProduct constructor.
     *
     * @param Session $session
     * @param IndexerRegistry $indexerRegistry
     * @param ManagerInterface $messageManager
     * @param ProductFactory $productFactory
     * @param Data $membershipHelper
     * @param HelperData $helperData
     * @param NotifyDatesFactory $notifyDatesFactory
     * @param NotifyDates $notifyResourceModel
     * @param TimezoneInterface $timezoneInterface
     */
    public function __construct(
        Session $session,
        IndexerRegistry $indexerRegistry,
        ManagerInterface $messageManager,
        ProductFactory $productFactory,
        Data $membershipHelper,
        HelperData $helperData,
        NotifyDatesFactory $notifyDatesFactory,
        NotifyDates $notifyResourceModel,
        TimezoneInterface $timezoneInterface
    ) {
        $this->notifyModelFactory = $notifyDatesFactory;
        $this->notifyResourceModel = $notifyResourceModel;
        $this->helperData = $helperData;
        $this->session = $session;
        $this->membershipHelper = $membershipHelper;
        $this->indexerRegistry = $indexerRegistry;
        $this->messageManager = $messageManager;
        $this->productFactory = $productFactory;
        $this->timezone = $timezoneInterface;
    }

    /**
     * The method is used to create auction product
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $this->sendNotifyEmails($observer->getAuction());
            if ($this->getSession()->getVendorId()) {
                $existingSubscription = $this->membershipHelper->getExistingSubcription(
                    $this->getSession()->getVendorId()
                );

                $auction = $observer->getData('auction');
                // if ($auction->getId() && $auction->getAuctionType() == 1) {
                //     if (empty($existingSubscription)) {
                //         $this->messageManager->addErrorMessage(__('Please take a subscription first.'));
                //     }

                //     if (isset($existingSubscription[0]['auction_fee'])) {
                //         $price = $existingSubscription[0]['auction_fee'];

                //         //phpcs:disable
                //         $sku = 'auctionfee' . mt_rand();
                //         //phpcs:enable

                //         $name = __('Auction Fee');
                //         $product = $this->productFactory->create();
                //         $product
                //             ->setTypeId(Type::TYPE_VIRTUAL)
                //             ->setAttributeSetId($this->productFactory->create()->getDefaultAttributeSetId())
                //             ->setSku($sku)
                //             ->setWebsiteIds([1])
                //             ->setStatus(Status::STATUS_ENABLED)
                //             ->setVisibility(Visibility::VISIBILITY_IN_SEARCH)
                //             ->setStockData([
                //                 'manage_stock' => 0,
                //                 'is_in_stock' => 1,
                //                 'qty' => 1,
                //                 'min_sale_qty' => 1,
                //                 'max_sale_qty' => 1
                //             ])
                //             ->setName($name)
                //             ->setShortDescription($name)
                //             ->setDescription($name)
                //             ->setPrice($price)
                //             ->setTaxClassId(2)// Taxable Goods by default
                //             ->setWeight(10)
                //             ->setUrlKey("auction-fee-" . $auction->getId());

                //         $product->save();
                //         $this->reindexByProductsIds($product->getId());
                //         $productId = $this->productFactory->create()->getIdBySku($sku);
                //         /* save the virtual product id for future use */
                //         $auction->setData('v_product_id', $productId);
                //         $auction->save();
                //     }
                // }
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
    }

    /**
     * Send Email
     *
     * @param mixed $auctionModel
     * @return void
     * @throws NoSuchEntityException
     */
    public function sendNotifyEmails($auctionModel)
    {
        $templateVars = [];
        $templateVars['product_name'] = $auctionModel->getProductName();
        $templateVars['auction_status'] = $auctionModel->getStatus();
        $templateVars['starting_date'] = $this->timezone->date($auctionModel->getData('start_time_utc'))->format('Y-m-d H:i');
        $templateVars['ending_date'] = $this->timezone->date($auctionModel->getData('end_time_utc'))->format('Y-m-d H:i');
        $templateVars['product_url'] = $this->helperData
            ->getProductDetail($auctionModel->getProductId())->getProductUrl();
        $vendor = $this->helperData->getVendor($auctionModel->getVendorId());
        $toDetails = [['name' => $vendor->getName(), 'email' => $vendor->getEmail()],
            ['name' => 'Admin', 'email' => $this->helperData->getAdminEmailId()]];
        $emailTemplate = 'auction_entry_1_standard_auction_creation_notify';
        try {
            foreach ($toDetails as $toDetail) {
                $templateVars['recipient_name'] = $toDetail['name'];
                $this->helperData->sendEmail($templateVars, $emailTemplate, $toDetail['email']);
            }
            if ($auctionModel->getStatus() == 'not started') {
                $this->saveFutureNotifyDates($auctionModel);
            }
        } catch (Exception $e) {
            $this->helperData->logger->error($e->getMessage());
        }
    }

    /**vendor
     * Save Future Dates for Send Email notification for buyers
     *
     * @param object $auction
     */
    public function saveFutureNotifyDates($auction)
    {
        $IncrementedDate = $this->timezone->date()->setTimezone(new \DateTimeZone('UTC'))->modify('+1 hours');
        $startDate = $this->timezone->date($auction->getData('start_time_utc'))->setTimezone(new \DateTimeZone('UTC'));
        while ($IncrementedDate < $startDate) {
            $notifyModel = $this->notifyModelFactory->create();
            $notifyModel->setAuctionId($auction->getId());
            $notifyModel->setNotifyDate($IncrementedDate->format('Y-m-d'));
            $this->notifyResourceModel->save($notifyModel);
            $IncrementedDate = $this->timezone->date($IncrementedDate)->setTimezone(new \DateTimeZone('UTC'))->modify('+24 hours');
        }
    }

    /**
     * The method is used to get session
     *
     * @return Session
     */
    protected function getSession(): Session
    {
        return $this->session;
    }

    /**
     * The product is used to reindex th single product
     *
     * @param int $productId
     */
    public function reindexByProductsIds(int $productId): void
    {
        $indexLists = [
            'catalog_product_category',
            'catalog_product_attribute',
            'cataloginventory_stock',
            'inventory'
        ];

        foreach ($indexLists as $indexList) {
            $categoryIndexer = $this->indexerRegistry->get($indexList);
            $categoryIndexer->reindexRow($productId);
        }
    }
}
