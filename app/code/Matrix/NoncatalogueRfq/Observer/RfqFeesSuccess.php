<?php
namespace Matrix\NoncatalogueRfq\Observer;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Matrix\NoncatalogueRfq\Helper\Data  as Helper;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;

use Matrix\NoncatalogueRfq\Model\RfqPoFactory;

/**
 * Class Success
 */
class RfqFeesSuccess implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;

    public $quoteFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var NoncatalogRfqFactory
     */
    protected $noncatalogRfqFactory;

    /**
     * @var PoFactory
     */
    protected $poFactory;

    protected $quoteRepository;

    protected $order;

    private $logger;

    /**
     * Success constructor.
     * @param Session $customerSession
     * @param Helper $helper
     * @param NoncatalogRfqFactory $noncatalogRfqFactory
     * @param CustomerCart $cart
     */
    public function __construct(
        Session $customerSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Sales\Model\Order $order,
        NoncatalogRfqFactory $noncatalogRfqFactory,
        RfqPoFactory $poFactory,
        \Psr\Log\LoggerInterface $logger,
        CustomerCart $cart,
        Helper $helper,
        CollectionFactory $quoteFactory
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->session = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->order = $order;
        $this->noncatalogRfqFactory = $noncatalogRfqFactory;
        $this->poFactory = $poFactory;
        $this->cart = $cart;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order_id = $observer->getEvent()->getData('order_ids');
        $orderid = $order_id[0];
        $products = [];
        $matrixRfqfeesId = $this->session->getData('matrix_rfqfees_id');
        $order = $this->order->load($orderid);
        //get quote id from order
        $quoteid = $order->getQuoteId();
        $quoteFactory = $this->quoteFactory->create()->addFieldToFilter('quote_id', $quoteid);
        foreach ($quoteFactory->getItems() as $value) {
            $matrixRfqfeesId = $value->getMatrixRfqfeesId();
        }
        if (isset($matrixRfqfeesId) && isset($quoteid)) {

            try {
                $quoteItems = $this->getQuoteItems($quoteid);
                if (count($quoteItems)) {
                    foreach ($quoteItems as $item) {
                        //$this->logger->info("RFQ Fees Qute Item",$item->getData());
                        $matrix_rfqfees_id = $item->getData('matrix_rfqfees_id');
                        if (isset($matrix_rfqfees_id) && $matrix_rfqfees_id==$matrixRfqfeesId) {
                            $price = $item->getData('price');
                            /*$nonCatRfq = $this->noncatalogRfqFactory->create()->load($matrixRfqfeesId);
                            $nonCatRfq->setData('is_feespaid',1);
                            $nonCatRfq->setData('rfq_fees',$price);
                            $nonCatRfq->save();*/
                            $poload = $this->poFactory->create()->load($matrixRfqfeesId);
                            $poload->setData('is_feespaid', 1);
                            $poload->setData('rfq_fees', $price);
                            $poload->save();

                        }

                    }
                }

            } catch (\Exception $e) {
            }
        }
    }

    public function getQuoteItems($quoteId)
    {
        try {
            $quote = $this->quoteRepository->get($quoteId);
            $items = $quote->getAllItems();
            return $items;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return [];
        }
    }
}
