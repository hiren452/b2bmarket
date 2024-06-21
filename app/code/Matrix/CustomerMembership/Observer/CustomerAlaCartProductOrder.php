<?php

namespace Matrix\CustomerMembership\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerAlaCartProductOrder implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    protected $customerAlaCartFactory;

    protected $subscriptionFactory;

    public function __construct(
        \Magento\Sales\Model\Order $order,
        \Matrix\CustomerMembership\Model\CustomerAlaCartFactory $customerAlaCartFactory,
        \Ced\CustomerMembership\Model\SubscriptionFactory $subscriptionFactory
    ) {
        $this->order = $order;
        $this->customerAlaCartFactory = $customerAlaCartFactory;
        $this->subscriptionFactory = $subscriptionFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order_id = $observer->getEvent()->getData('order_ids');
        $order    = $this->order->load($order_id[0]);

        $items = $order->getAllItems();
        foreach ($items as $item):
            $productId = $item->getProductId();

            $alaCart = $this->customerAlaCartFactory->create()->getCollection()->addFieldToFilter('v_product_id', $productId)->getLastItem();

            if (!empty($alaCart->getData())) {

                //$transactionId = $order->getPayment()->getTransactionId();

                $subscription = $this->subscriptionFactory->create()->getCollection()
                                    ->addFieldToFilter('customer_id', $alaCart->getData('customer_id'))
                                    ->addFieldToFilter('membership_id', $alaCart->getData('membership_id'))
                                    ->addFieldToFilter('status', 'running')
                                    ->getLastItem();

                if (!empty($subscription->getData())) {
                    $rfq_limit       = $subscription->getRfqLimit() + $alaCart->getData('rfq_qty');
                    $noncatrfq_limit = $subscription->getNoncatrfqLimit() + $alaCart->getData('non_catalog_rfq_qty');

                    $model = $this->subscriptionFactory->create()->load($subscription->getId());
                    $model->setRfqLimit($rfq_limit);
                    $model->setNoncatrfqLimit($noncatrfq_limit);
                    $model->save();

                    $alaCart->setOrderId($order->getIncrementId())->save();
                }
            }
        endforeach;
    }
}
