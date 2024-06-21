<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Ced;
 * @package     Ced_CustomerMembership
 * @author         CedCommerce Core Team <coreteam@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Ced\CustomerMembership\Model\Total;

use Ced\CustomerMembership\Model\MembershipFactory;
use Ced\CustomerMembership\Model\SubscriptionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class Fee extends AbstractTotal
{
    protected $_customerSession;
    protected $_membershipFactory;
    protected $_subscriptionFactory;

    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        CustomerSession $customerSession,
        MembershipFactory $membershipFactory,
        SubscriptionFactory $subscriptionFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_membershipFactory = $membershipFactory;
        $this->_subscriptionFactory = $subscriptionFactory;
        parent::__construct($quoteValidator);
    }

    /**
     * Collect grand total address amount
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $productIds = $this->_membershipFactory->create()->getCollection()->getColumnValues('product_id');

        foreach ($quote->getAllItems() as $item) {
            if (in_array($item->getProductId(), $productIds)) {
                $productIds = $item->getProductId();
                break;
            }
        }

        $isLoggedIn = $this->_customerSession->isLoggedIn();
        $customerId = $this->_customerSession->getCustomerId();

        $subscribedPlanCount = $this->_subscriptionFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', 'running')
            ->count();

        if ($isLoggedIn && $subscribedPlanCount > 0 && !$productIds) {
            $fee = $this->getDiscountPrice($total, $quote);
            $total->addTotalAmount('fee', -$fee);
            $total->addBaseTotalAmount('fee', -$fee);
            $quote->setFee(-$fee);
        }

        return $this;
    }

    /**
     * Fetch details for display in checkout totals.
     *
     * @param Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code' => 'fee',
            'title' => __('Discount'),
            'value' => $this->getDiscountPrice($total, $quote),
        ];
    }

    /**
     * Get label for display in checkout totals.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Discount');
    }

    /**
     * Calculate discount amount based on subscription and quote.
     *
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param Quote $quote
     * @return float
     */
    public function getDiscountPrice(\Magento\Quote\Model\Quote\Address\Total $total, Quote $quote)
    {
        $productIds = $this->_membershipFactory->create()->getCollection()->getColumnValues('product_id');

        foreach ($quote->getAllItems() as $item) {
            if (in_array($item->getProductId(), $productIds)) {
                $productIds = $item->getProductId();
                break;
            }
        }

        if ($this->_customerSession->isLoggedIn() && !$productIds) {
            $subtotal = $total->getBaseSubtotal();

            $subscribedPlan = $this->_subscriptionFactory->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', $this->_customerSession->getCustomerId())
                ->addFieldToFilter('status', 'running')
                ->getLastItem();

            $discountAmount = ($subscribedPlan->getOrderDiscount() * $subtotal) / 100;
            return $discountAmount;
        }

        return 0;
    }
}
