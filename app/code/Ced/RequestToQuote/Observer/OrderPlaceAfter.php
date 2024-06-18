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

use Ced\RequestToQuote\Helper\Data as Helper;
use Ced\RequestToQuote\Model\PoFactory;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class OrderPlaceAfter
 * @package Ced\RequestToQuote\Observer
 */
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
        PoFactory $poFactory,
        Helper $helper
    ) {
        $this->session = $customerSession;
        $this->cart = $cart;
        $this->poFactory = $poFactory;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $module_enable = $this->helper->getConfigValue('requesttoquote_configuration/active/enable');
        if ((int)$module_enable) {
            $poIncid = '';
            foreach ($this->cart->getQuote()->getAllItems() as $item) {
                if ($item->getCedPoId()) {
                    $poIncid = $item->getCedPoId();
                    break;
                }
            }
            if ($poIncid) {
                $po = $this->poFactory->create()->load($poIncid);
                if ($po && $po->getId()) {
                    $this->session->setData('po_id', $po->getPoIncrementId());
                }
            }
        }
    }
}
