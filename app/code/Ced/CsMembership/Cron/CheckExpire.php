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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Cron;

/**
 * Class CheckExpire (Check membership is expired or not)
 */
class CheckExpire
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Ced\CsMembership\Model\SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var \Ced\CsMembership\Model\MembershipFactory
     */
    protected $membershipFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Ced\CsMembership\Helper\Data
     */
    protected $membershipHelper;

    /**
     * CheckExpire constructor.
     * @param \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory
     * @param \Ced\CsMembership\Model\MembershipFactory $membershipFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\CsMembership\Helper\Data $membershipHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory,
        \Ced\CsMembership\Model\MembershipFactory $membershipFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsMembership\Helper\Data $membershipHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->membershipFactory = $membershipFactory;
        $this->productFactory = $productFactory;
        $this->membershipHelper = $membershipHelper;
    }

    /**
     * Execute action
     *
     * @throws \Exception
     */
    public function execute()
    {

        $cur_time = date('Y-m-d');
        $collection = $this->subscriptionFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', \Ced\CsMembership\Model\Status::STATUS_RUNNING)
            ->addFieldToFilter('end_date', ['to' => date("Y-m-d")]);
        foreach ($collection as $subcription) {
            $model = $this->subscriptionFactory->create();
            $model->load($subcription->getId());
            $model->setStatus(\Ced\CsMembership\Model\Status::STATUS_EXPIRED);
            $model->save();
            $qtyModel = $this->membershipFactory->create()->load($subcription->getSubcriptionId());
            $prvqty = $qtyModel->getQty();
            $newqty = $prvqty + 1;
            $qtyModel->setQty($newqty);
            $qtyModel->save();
            $product = $this->productFactory->create();
            $product->setStockData(['qty' => $newqty, 'is_in_stock' => 1]);
            $product->setQuantityAndStockStatus(['qty' => $newqty, 'is_in_stock' => 1]);
            $product->save();
            try {
                $this->membershipHelper->sendExpireMail($subcription);
            } catch (\Exception $e) {
                $this->_logger->addDebug($e->getMessage());
            }
        }
    }
}
