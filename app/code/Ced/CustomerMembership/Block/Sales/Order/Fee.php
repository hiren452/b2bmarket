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
namespace Ced\CustomerMembership\Block\Sales\Order;

use Magento\Customer\Model\Session as CustomerSession;

class Fee extends \Magento\Framework\View\Element\Template
{
    /**
     * Tax configuration model
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    protected $subscriptionCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Ced\CustomerMembership\Model\ResourceModel\Subscription\Collection $subscriptionCollection,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->_config = $taxConfig;
        $this->subscriptionCollection = $subscriptionCollection;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();

        $store = $this->getStore();

        $fee = new \Magento\Framework\DataObject(
            [
                   'code' => 'fee',
                   'strong' => false,
                   'value' => $this->getDiscountPrice(),
                   //'value' => $this->_source->getFee(),
                   'label' => __('Fee'),
               ]
        );

        $parent->addTotal($fee, 'fee');
        // $this->_addTax('grand_total');
        $parent->addTotal($fee, 'fee');

        return $this;
    }
    public function getDiscountPrice()
    {
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getCustomerId();
            $subscribedPlan = $this->subscriptionCollection->addFieldToFilter('customer_id', $customerId)->getLastItem();
            if ($subscribedPlan && $subscribedPlan->getId()) {
                return $subscribedPlan->getOrderDiscount();
            }
        }
        return null;
    }
}
