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

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;

class SidebarUpdateItemQtytBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * SidebarUpdateItemQtytBefore constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param CustomerCart $cart
     * @param UrlInterface $url
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        CustomerCart $cart,
        UrlInterface $url
    ) {
        $this->_messageManager = $messageManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_session = $customerSession;
        $this->cart = $cart;
        $this->url = $url;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item_id = $observer->getRequest()->getPostValue('item_id');
        $quoteItem = $this->cart->getQuote()->getItemById($item_id);
        if($quoteItem && $quoteItem->getItemId() && $quoteItem->getCedPoId()) {
            if (!empty($this->_session->getData('rfq_price'))) {
                $proids = array_keys($this->_session->getData('rfq_price'));
                $item = $this->_checkoutSession->getQuote()->getItemById($item_id);
                if (in_array($item->getProduct()->getId(), $proids)) {
                    $observer->getRequest()->setParam('item_id', null);
                    $observer->getRequest()->setParam('item_qty', false);
                    $link = '<a href="' . $this->url->getUrl('requesttoquote/customer/editpo', ['poId' => $quoteItem->getCedPoId()]) . '">' . __('Click Here') . '</a>';
                    $this->_messageManager->addError(__('Quantity edit for the quote item is not allowed. ' . $link . ' to remove Proposal Item(s) from cart.'));
                    return $this;
                } elseif ($item->getProduct()->getTypeId() == 'configurable') {
                    $_children = $item->getProduct()->getTypeInstance()->getUsedProducts($item->getProduct());
                    $childProductExistInCartFlag = false;
                    foreach ($_children as $child) {
                        if (in_array($child->getId(), $proids)) {
                            $childProductExistInCartFlag = true;
                            break;
                        }
                    }
                    if ($childProductExistInCartFlag) {
                        $observer->getRequest()->setParam('item_id', null);
                        $observer->getRequest()->setParam('item_qty', false);
                        $link = '<a href="' . $this->url->getUrl('requesttoquote/customer/editpo', ['poId' => $quoteItem->getCedPoId()]) . '">' . __('Click Here') . '</a>';
                        $this->_messageManager->addError(__('Quantity edit for the quote item is not allowed. ' . $link . ' to remove Proposal Item(s) from cart.'));
                        return $this;
                    }
                }
            }
        }
        return $this;
    }
}
