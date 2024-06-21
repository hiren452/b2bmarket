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
class NewPlan extends \Magento\Framework\View\Element\Template
{

    protected $_customerSession;

    protected $_filtercollection;
    protected $_requestCollection;
    protected $currencyFactory;
    protected $collectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Ced\CustomerMembership\Model\ResourceModel\Membership\Collection $collectionFactory,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->currencyFactory = $currencyFactory;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }
    public function getCurrencyCode()
    {
        $code =  $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $currency = $this->currencyFactory->create()->load($code);
        return $currency->getCurrencySymbol();
    }
    public function getMembershipPlan()
    {
        return $this->collectionFactory->getCollection()
                                ->addFieldToFilter('status', '1')
                                ->addFieldToFilter('website', $this->_storeManager->getStore()->getWebsiteId());
    }
}
