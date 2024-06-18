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

use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AfterPlaceOrder
 * @package Ced\RequestToQuote\Observer
 */
class AfterPlaceOrder implements ObserverInterface
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * AfterPlaceOrder constructor.
     * @param Session $customerSession
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Session $customerSession,
        ProductFactory $productFactory
    ) {
        $this->session = $customerSession;
        $this->productFactory = $productFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $prices = $this->session->getRfqPrice();

        foreach($prices as $key => $value) {
            $this->productFactory->create()->load($key)->setPrice($value['new_price'])->save();
        }

    }
}
