<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category    Ced
 * @package     Ced_Customermembership
 * @author       CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Ced\CustomerMembership\Block\Membership;

/**
 * Customer address edit block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends \Magento\Framework\View\Element\Template
{

    protected $_customerSession;

    protected $_filtercollection;
    protected $_requestCollection;
    protected $collectionFactory;
    protected $subscriptionCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CustomerMembership\Model\ResourceModel\Membership\Collection $collectionFactory,
        \Ced\CustomerMembership\Model\ResourceModel\Subscription\Collection $subscriptionCollection,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->subscriptionCollection = $subscriptionCollection;
        parent::__construct($context, $data);
    }

    public function getMembershipPlan()
    {
        return $this->collectionFactory->getCollection()->addFieldToFilter('status', '1');
    }
    public function getSubscribedMembership()
    {
        return $this->subscriptionCollection->getCollection()->addFieldToFilter('customer_id', $this->_customerSession->getCustomerId());
    }
}
