<?php
namespace Matrix\NoncatalogueRfq\Observer;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Customer\Model\Session;
//use Ced\RequestToQuote\Model\PoFactory;
use Magento\Framework\Event\ObserverInterface;
use Matrix\NoncatalogueRfq\Helper\Data as Helper;
use Matrix\NoncatalogueRfq\Model\RfqPoFactory;

class OrderPlaceAfter implements ObserverInterface
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
     * @var PoFactory
     */
    protected $poFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * OrderPlaceAfter constructor.
     * @param Session $customerSession
     * @param CustomerCart $cart
     * @param PoFactory $poFactory
     * @param Helper $helper
     */
    public function __construct(
        Session $customerSession,
        CustomerCart $cart,
        RfqPoFactory $poFactory,
        \Psr\Log\LoggerInterface $logger,
        Helper $helper
    ) {
        $this->session = $customerSession;
        $this->cart = $cart;
        $this->poFactory = $poFactory;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->info("Order OrderPlaceAfter called");
        $module_enable = $this->helper->getConfigValue('noncatalogrfq_configuration/active/enable');
        if ((int)$module_enable) {
            $poIncid = '';
            foreach ($this->cart->getQuote()->getAllItems() as $item) {
                if ($item->getMatrixPoId()) {
                    $poIncid = $item->getMatrixPoId();
                    break;
                }
            }
            if ($poIncid) {
                $po = $this->poFactory->create()->load($poIncid);
                if ($po && $po->getId()) {
                    $this->session->setData('matrix_po_id', $po->getPoIncrementId());
                    $this->logger->info("Order OrderPlaceAfter matrix_po_id =" . $po->getPoIncrementId());
                }
            }
        }
    }
}
