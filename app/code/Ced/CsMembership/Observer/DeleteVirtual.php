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
 * Class DeleteVirtual (for deleting product)
 */
class DeleteVirtual implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * DeleteVirtual constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->productFactory = $productFactory;
        $this->logger = $logger;
    }

    /**
     * Execute action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product_id = $observer->getEvent()->getProduct();
        if ($product_id) {
            try {
                $this->productFactory->create()->load($product_id)->delete();
            } catch (Exception $e) {
                $this->logger->addError($e->getMessage());
            }
        }
    }
}
