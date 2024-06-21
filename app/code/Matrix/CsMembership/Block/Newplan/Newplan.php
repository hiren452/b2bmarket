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
namespace Matrix\CsMembership\Block\Newplan;

use Ced\CustomerMembership\Model\MembershipFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Customer address edit block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewPlan extends \Magento\Framework\View\Element\Template
{
    protected $_customerSession;
    protected $_membershipFactory;
    protected $_currencyFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        MembershipFactory $membershipFactory,
        CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_membershipFactory = $membershipFactory;
        $this->_currencyFactory = $currencyFactory;
        parent::__construct($context, $data);
    }

    public function getCurrencyCode()
    {
        $code = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $currency = $this->_currencyFactory->create()->load($code);
        return $currency->getCurrencySymbol();
    }

    public function getMembershipPlan()
    {
        return $this->_membershipFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', '1')
            ->addFieldToFilter('website', $this->_storeManager->getStore()->getWebsiteId());
    }
}
