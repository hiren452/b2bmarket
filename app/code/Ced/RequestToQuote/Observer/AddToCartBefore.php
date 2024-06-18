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
 * @category    Ced
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\RequestToQuote\Observer;

use Ced\RequestToQuote\Helper\Data;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddToCartBefore
 * @package Ced\RequestToQuote\Observer
 */
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
        $module_enable = $this->helper->isEnable();
        if ((int)$module_enable) {
            $allItems = $this->cart->getQuote()->getAllItems();
            $poItemExistFlag = false;
            foreach ($allItems as $item) {
                if ($item->getCedPoId()) {
                    $poItemExistFlag = true;
                    break;
                }
            }
            if($poItemExistFlag) {
                $observer->getRequest()->setParam('product', false);
                $this->_messageManager->addErrorMessage(__('You have proposal item(s) in your shopping cart. So you cannot add item into shopping cart.'));
                return $this;
            }
        }
        return $this;
    }
}
