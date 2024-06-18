<?php

namespace Matrix\CsAuction\Observer;

use Ced\Auction\Model\ResourceModel\Auction;
use Ced\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use OX\Auction\Helper\Data as AuctionHelper;

class AuctionFeeProductOrder implements ObserverInterface
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var CollectionFactory
     */
    protected $auctionCollectionFactory;

    /**
     * @var Auction
     */
    private $auctionResource;

    /**
     * @var AuctionHelper
     */
    private $auctionHelper;

    /**
     * @var StateInterface
     */
    private $inlineTranslate;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * AuctionFeeProductOrder constructor.
     *
     * @param Order $order
     * @param CollectionFactory $auctionCollectionFactory
     * @param Auction $auctionResource
     * @param AuctionHelper $auctionHelper
     * @param StateInterface $inlineTranslate
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Order $order,
        CollectionFactory $auctionCollectionFactory,
        Auction $auctionResource,
        AuctionHelper $auctionHelper,
        StateInterface $inlineTranslate,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->order = $order;
        $this->auctionCollectionFactory = $auctionCollectionFactory;
        $this->auctionResource = $auctionResource;
        $this->auctionHelper = $auctionHelper;
        $this->inlineTranslate = $inlineTranslate;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @throws AlreadyExistsException
     */
    public function execute(Observer $observer)
    {
        $order_id = $observer->getEvent()->getData('order_ids');
        $order = $this->order->load($order_id[0]);

        $items = $order->getAllItems();
        foreach ($items as $item) {
            $productId = $item->getProductId();

            $auction = $this->auctionCollectionFactory->create()
                ->addFieldToFilter('v_product_id', $productId)
                ->getLastItem();

            if (!empty($auction->getData())) {
                $transactionId = $order->getPayment()->getTransactionId();
                $auction->setData('transaction_id', $order->getId());
                $auction->setData('is_paid', 1);
                $this->sendPaymentSuccessMail($auction);
                $this->auctionResource->save($auction);
            }
        }
    }

    /**
     * @param $auction
     * @throws LocalizedException
     */
    public function sendPaymentSuccessMail($auction)
    {
        try {
            $templateId = 51;
            $templateVars = [];
            $vendor = $this->auctionHelper->getVendor($auction->getVendorId());
            $vendorDetails['name'] = $vendor->getName();
            $vendorDetails['email'] = $vendor->getEmail();
            $templateVars['customer_name'] = $vendorDetails['name'];
            $to = $vendorDetails['email'];
            $this->inlineTranslate->suspend();
            $sender = [
                'name' => $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE),
                'email' => $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE)
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
            $this->inlineTranslate->resume();
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
