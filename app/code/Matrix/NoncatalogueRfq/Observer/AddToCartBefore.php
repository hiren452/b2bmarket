<?php
namespace Matrix\NoncatalogueRfq\Observer;

use Magento\Checkout\Model\Cart as CustomerCart;

use Magento\Framework\Event\ObserverInterface;

use Matrix\NoncatalogueRfq\Helper\Data;

class AddToCartBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var Data
     */
    protected $helper;

    private $logger;

    /**
     * AddToCartBefore constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param CustomerCart $cart
     * @param Data $helper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        CustomerCart $cart,
        Data $helper
    ) {
        $this->_messageManager = $messageManager;
        $this->_session = $customerSession;
        $this->cart = $cart;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productId = $observer->getRequest()->getPostValue('product');
        if (!$productId) {
            return $this;
        }
        $module_enable = $this->helper->getConfigValue('noncatalogrfq_configuration/active/enable');
        if ((int)$module_enable) {
            $allItems = $this->cart->getQuote()->getAllItems();
            $poItemExistFlag = false;
            $currentproduct = $observer->getRequest()->getPostValue('product');
            foreach ($allItems as $item) {
                if ($item->getMatrixPoId() && $item->getProduct()->getId() == $currentproduct) {
                    $poItemExistFlag = true;
                    break;
                }
            }
            if ($poItemExistFlag) {
                $observer->getRequest()->setParam('product', false);
                $this->_messageManager->addErrorMessage(__('Quantity edit for the proposal item(s) is not allowed.'));
                return $this;
            }
        }
        return $this;
    }
}
