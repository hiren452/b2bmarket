<?php
namespace Matrix\NoncatalogueRfq\Observer;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Customer\Model\Session;

use Magento\Framework\Event\ObserverInterface;
use Matrix\NoncatalogueRfq\Helper\Data as Helper;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;
use Matrix\NoncatalogueRfq\Model\RfqPoFactory;

/**
 * Class OrderPlaceAfter
 */
class RfqFeesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var NonCatalogRfqFactory
     */
    protected $nonCatalogRfqFactory;

    /**
     * @var PoFactory
     */
    protected $poFactory;

    /**
     * @var Helper
     */
    protected $helper;

    protected $_logger;

    /**
     * OrderPlaceAfter constructor.
     * @param Session $customerSession
     * @param CustomerCart $cart
     * @param NoncatalogRfqFactory $noncatalogRfqFactory
     * @param PoFactory $poFactory
     * @param Helper $helper
     */
    public function __construct(
        Session $customerSession,
        CustomerCart $cart,
        NoncatalogRfqFactory $noncatalogRfqFactory,
        RfqPoFactory $poFactory,
        \Matrix\NoncatalogueRfq\Logger\Logger $logger,
        Helper $helper
    ) {
        $this->session = $customerSession;
        $this->cart = $cart;
        $this->nonCatalogRfqFactory = $noncatalogRfqFactory;
        $this->poFactory = $poFactory;
        $this->_logger = $logger;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_logger->warning("call Class Methd <--" . __METHOD__ . '--> \n approx go to  <-- line no ' . __LINE__ . '-->');
        $this->_logger->info("RfqFeesOrderPlaceAfter called");
        //$this->logger->info("RfqFeesOrderPlaceAfter called");
        $module_enable = $this->helper->getConfigValue('noncatalogrfq_configuration/active/enable');
        if ((int)$module_enable) {
            $rfFeescid = '';
            foreach ($this->cart->getQuote()->getAllItems() as $item) {
                if ($item->getMatrixRfqfeesId()) {
                    $rfFeescid = $item->getMatrixRfqfeesId();
                    break;
                }
            }
            if ($rfFeescid>0) {
                $po = $this->poFactory->create()->load($rfFeescid);
                if ($po && $po->getId()) {
                    $this->session->setData('matrix_rfqfees_id', $po->getPoId());
                    $this->_logger->warning("call Class Methd <--" . __METHOD__ . '--> \n approx go to  <-- line no ' . __LINE__ . '-->');
                    $this->_logger->info("Order RfqFeesOrderPlaceAfter matrix_rfqfees_id =" . $po->getPoId());
                }
                /*$noncatalogRfq = $this->nonCatalogRfqFactory->create()->load($rfFeescid);
                $this->logger->info("Order RfqFeesOrderPlaceAfter RFQ DATA ",$noncatalogRfq->getData());
                if ($noncatalogRfq) {
                    $this->session->setData('matrix_rfqfees_id', $noncatalogRfq->getRfqId());
                    $this->logger->info("Order RfqFeesOrderPlaceAfter matrix_rfqfees_id =".$noncatalogRfq->getMatrixRfqfeesId());
                }*/
            }
        }
    }
}
