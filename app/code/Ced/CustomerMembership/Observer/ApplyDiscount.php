<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CustomerMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CustomerMembership\Observer;

use Ced\CustomerMembership\Model\MembershipFactory;
use Ced\CustomerMembership\Model\SubscriptionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class ApplyDiscount implements ObserverInterface
{

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var MembershipFactory
     */
    protected $_membershipFactory;

    /**
     * @var SubscriptionFactory
     */
    protected $_subscriptionFactory;

    public function __construct(
        CustomerSession $customerSession,
        MembershipFactory $membershipFactory,
        SubscriptionFactory $subscriptionFactory,
        ManagerInterface $messageManager
    ) {
        $this->_customerSession = $customerSession;
        $this->_membershipFactory = $membershipFactory;
        $this->_subscriptionFactory = $subscriptionFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Execute observer method
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customerid = $this->_customerSession->getCustomerId();
        $loggedin = $this->_customerSession->isLoggedIn();
        $subscribedplan = $this->_subscriptionFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerid)
            ->addFieldToFilter('status', 'running');

        if ($loggedin && count($subscribedplan->getData()) > 0) {
            try {
                $quote = $observer->getEvent()->getQuote();
                $quoteId = $quote->getId();
                $total = $quote->getBaseSubtotal();
                $discountAmount = $this->getDiscountPrice($total, $quote);

                if ($quoteId && $discountAmount > 0) {
                    $canAddItems = $quote->isVirtual() ? 'billing' : 'shipping';

                    foreach ($quote->getAllAddresses() as $address) {
                        $address->setBaseSubtotal(0);
                        $quote->setSubtotal((float) $quote->getSubtotal() + $address->getSubtotal());
                        $quote->setBaseSubtotal((float) $quote->getBaseSubtotal() + $address->getBaseSubtotal());
                        $quote->setSubtotalWithDiscount((float) $quote->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount());
                        $quote->setBaseSubtotalWithDiscount((float) $quote->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount());
                        $quote->setGrandTotal((float) $quote->getGrandTotal() + $address->getGrandTotal());
                        $quote->setBaseGrandTotal((float) $quote->getBaseGrandTotal() + $address->getBaseGrandTotal());
                        $quote->save();

                        if ($address->getAddressType() == $canAddItems) {
                            $address->setSubtotalWithDiscount((float) $address->getSubtotalWithDiscount() - $discountAmount);
                            $address->setGrandTotal((float) $address->getGrandTotal() - $discountAmount);
                            $address->setBaseSubtotalWithDiscount((float) $address->getBaseSubtotalWithDiscount() - $discountAmount);
                            $address->setBaseGrandTotal((float) $address->getBaseGrandTotal() - $discountAmount);

                            if ($address->getDiscountDescription()) {
                                $address->setDiscountAmount(-($address->getDiscountAmount() - $discountAmount));
                                $address->setDiscountDescription($address->getDiscountDescription() . ', Custom Discount');
                                $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount() - $discountAmount));
                            } else {
                                $address->setDiscountAmount(-($discountAmount));
                                $address->setDiscountDescription('Membership Discount');
                                $address->setBaseDiscountAmount(-($discountAmount));
                            }
                            $address->save();
                        }
                    }

                    foreach ($quote->getAllItems() as $item) {
                        if ($total > 0) {
                            $rat = $item->getPriceInclTax() / $total;
                            $ratdisc = $discountAmount * $rat;
                            $item->setDiscountAmount($discountAmount / (count($quote->getAllItems())));
                            $item->setBaseDiscountAmount($discountAmount / (count($quote->getAllItems())))->save();
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
    }

    /**
     * Get discount price based on subscription and quote
     *
     * @param float $total
     * @param \Magento\Quote\Model\Quote $quote
     * @return float
     */
    public function getDiscountPrice($total, $quote)
    {
        $productIds = $this->_membershipFactory->create()->getCollection()->getColumnValues('product_id');

        foreach ($quote->getAllItems() as $item) {
            if (in_array($item->getProductId(), $productIds)) {
                return 0; // No discount if membership product found in quote
            }
        }

        if ($this->_customerSession->isLoggedIn() && $total > 0) {
            $customerId = $this->_customerSession->getCustomerId();
            $subscribedPlan = $this->_subscriptionFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('status', 'running')
                ->getLastItem();

            if ($subscribedPlan && $subscribedPlan->getId()) {
                $discountAmount = ($subscribedPlan->getOrderDiscount() * $total) / 100;
                return $discountAmount;
            }
        }

        return 0;
    }
}
