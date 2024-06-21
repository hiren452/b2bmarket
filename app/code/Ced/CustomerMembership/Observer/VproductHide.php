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

use Magento\Framework\Event\ObserverInterface;

class VproductHide implements ObserverInterface
{

    public function __construct(
        \Ced\CustomerMembership\Helper\Data $helper,
        \Ced\CustomerMembership\Model\ResourceModel\Membership\CollectionFactory $collectionFactory
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->helper=$helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product=$observer->getEvent()->getProduct();
        $productId=$product->getId();
        $checkMembershipProduct  =$this->_collectionFactory->create()->addFieldToFilter('product_id', $productId);
        if (!$checkMembershipProduct->getFirstItem()->getId()) {
            if (!$this->helper->subscriptionStatus()) {
                $salableObject = $observer->getEvent()->getSalable();
                $salableObject->setIsSalable(false);
            }
        }
    }
}
