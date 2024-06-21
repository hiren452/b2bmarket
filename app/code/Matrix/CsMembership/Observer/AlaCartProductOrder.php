<?php

namespace Matrix\CsMembership\Observer;

use Magento\Framework\Event\ObserverInterface;

class AlaCartProductOrder implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    protected $alaCartFactory;

    protected $subscriptionFactory;

    protected $customerSubscriptionFactory;

    public function __construct(
        \Magento\Sales\Model\Order $order,
        \Matrix\CsMembership\Model\AlaCartFactory $alaCartFactory,
        \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory,
        \Ced\CustomerMembership\Model\SubscriptionFactory $customerSubscriptionFactory
    ) {
        $this->order = $order;
        $this->alaCartFactory = $alaCartFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->customerSubscriptionFactory = $customerSubscriptionFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order_id = $observer->getEvent()->getData('order_ids');
        $order    = $this->order->load($order_id[0]);

        $items = $order->getAllItems();
        foreach ($items as $item):
            $productId = $item->getProductId();

            $alaCart = $this->alaCartFactory->create()->getCollection()->addFieldToFilter('v_product_id', $productId)->getLastItem();

            if (!empty($alaCart->getData())) {

                //$transactionId = $order->getPayment()->getTransactionId();

                //=== update rfq limit for buyer subscription
                $customerSubscription = $this->customerSubscriptionFactory->create()->getCollection()
                                    ->addFieldToFilter('customer_id', $order->getCustomerId())
                                    //->addFieldToFilter('subscription_id', $alaCart->getData('subscription_id'))
                                    ->addFieldToFilter('status', 'running')
                                    ->getLastItem();

                if (!empty($customerSubscription->getData())) {
                    $rfq_limit = $customerSubscription->getRfqLimit() + $alaCart->getData('rfq_qty');
                    $noncatrfq_limit = $customerSubscription->getNoncatrfqLimit() + $alaCart->getData('non_catalog_rfq_qty');

                    $model = $this->customerSubscriptionFactory->create()->load($customerSubscription->getId());
                    $model->setRfqLimit($rfq_limit);
                    $model->setNoncatrfqLimit($noncatrfq_limit);
                    $model->save();
                }

                $subscription = $this->subscriptionFactory->create()->getCollection()
                                    ->addFieldToFilter('vendor_id', $alaCart->getData('vendor_id'))
                                    ->addFieldToFilter('subscription_id', $alaCart->getData('subscription_id'))
                                    ->addFieldToFilter('status', \Ced\CsMembership\Model\Status::STATUS_RUNNING)
                                    ->getLastItem();

                if (!empty($subscription->getData())) {
                    $product_limit = $subscription->getProductLimit() + $alaCart->getData('product_qty');
                    $auction_limit = $subscription->getAuctionLimit() + $alaCart->getData('auction_qty');
                    $subscription->setProductLimit($product_limit);
                    $subscription->setAuctionLimit($auction_limit);
                    $subscription->save();

                    $alaCart->setOrderId($order->getIncrementId())->save();
                }
            }

        endforeach;
    }
}
