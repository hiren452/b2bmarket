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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class MembershipProductOrder (for membership product order)
 */
class MembershipProductOrder implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Ced\CsMembership\Helper\Data
     */
    protected $membershipHelper;

    /**
     * MembershipProductOrder constructor.
     * @param \Magento\Sales\Model\Order $order
     * @param \Ced\CsMembership\Helper\Data $membershipHelper
     */
    public function __construct(
        \Magento\Sales\Model\Order $order,
        \Ced\CsMembership\Helper\Data $membershipHelper
    ) {
        $this->order = $order;
        $this->membershipHelper = $membershipHelper;
    }

    /**
     * Execute action
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order_id = $observer->getEvent()->getData('order_ids');
        $order = $this->order->load($order_id[0]);
        $this->membershipHelper->setSubscription($order);
    }
}
