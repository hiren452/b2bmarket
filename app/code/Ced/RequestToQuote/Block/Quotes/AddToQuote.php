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
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RequestToQuote\Block\Quotes;

use Ced\RequestToQuote\Helper\Data as Helper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class AddToQuote extends Template
{

    /**
     * @var string
     */
    public $_template = 'Ced_RequestToQuote::quotes/addtoquote.phtml';

    /**
     * @var ProductFactory
     */
    public $productFactory;

    /**
     * @var Helper
     */
    public $helper;

    /**
     * @var Session
     */
    public $session;

    /**
     * @var CurrencyFactory
     */
    private $currencyCode;

    /**
     * AddToQuote constructor.
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param Helper $helper
     * @param Session $session
     * @param array $data
     * @param CurrencyFactory $currencyCode
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        Helper $helper,
        Session $session,
        CurrencyFactory $currencyCode,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->helper = $helper;
        $this->session = $session;
        $this->currencyCode = $currencyCode;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->productFactory->create()->load($this->getProductId());
    }

    /**
     * @return array
     */
    public function getAllowedCustomerGroups()
    {
        $allowedCustomerGroups = [];
        $value = $this->helper->getConfigValue('requesttoquote_configuration/active/custgroups');
        if ($value) {
            $allowedCustomerGroups = explode(',', $value);
        }
        return $allowedCustomerGroups;
    }

    /**
     * @return mixed
     */
    public function getAddtoCartCustomers()
    {
        return $this->helper->getConfigValue('requesttoquote_configuration/active/hidepriceandcart');
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->session->isLoggedIn();
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->session->getCustomer();
    }

    /**
     * @return int
     */
    public function getVendorId()
    {
        return 0;
    }

    public function getCurrentCurrencySymbol()
    {
        $currentCurrencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $currencySymbol = $this->currencyCode->create()->load($currentCurrencyCode)->getCurrencySymbol();
        return $currencySymbol;
    }
}
