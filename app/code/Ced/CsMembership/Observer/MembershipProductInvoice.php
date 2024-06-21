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
 * Class MembershipProductInvoice (invoice of membership product)
 */
class MembershipProductInvoice implements ObserverInterface
{
    /**
     * @var \Ced\CsMembership\Helper\Data
     */
    protected $menbershipHelper;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Ced\CsMembership\Model\SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var \Magento\Framework\App\MessageInterface
     */
    protected $messageManager;

    /**
     * MembershipProductInvoice constructor.
     * @param \Ced\CsMembership\Helper\Data $menbershipHelper
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Ced\CsMembership\Helper\Data $menbershipHelper,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Psr\Log\LoggerInterface $logger,
        \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->menbershipHelper = $menbershipHelper;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->logger = $logger;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Execute action
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getDataObject()->getOrder();

        try {
            $subscriptionData = $this->subscriptionFactory->create()->getCollection()->addFieldToFilter('order_id', $order->getIncrementId());
            if ($subscriptionData) {
                $model = $this->subscriptionFactory->create()->load($order->getIncrementId(), 'order_id');
                $model->setStatus(\Ced\CsMembership\Model\Status::STATUS_RUNNING);
                $model->save();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
