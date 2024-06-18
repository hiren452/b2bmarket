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

use Ced\RequestToQuote\Helper\Data;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class SetForm
 * @package Ced\RequestToQuote\Block\Quotes
 */
class SetForm extends Template
{

    /**
     * SetForm constructor.
     * @param Context $context
     * @param ProductFactory $productloader
     * @param Session $session
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductFactory $productloader,
        Session $session,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_productloader = $productloader;
        $this->_session = $session;
        $this->helper = $helper;
        self::getProduct();
    }

    /**
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        $id = $this->getProductId();
        if ($id) {
            $product = $this->_productloader->create()->load($id);
            return $product;
        }
    }

    /**
     * @param $productId
     * @return string
     */
    public function getVendor($productId)
    {
        return "0";
    }

    /**
     * @return mixed
     */
    public function getProductType()
    {
        $id = $this->getProductId();
        if ($id) {
            $producttype = $this->_productloader->create()->load($id)->getTypeId();
            return $producttype;
        }
    }

    /**
     * @return mixed
     */
    public function getProductName()
    {
        $id = $this->getProductId();
        if ($id) {
            $productName = $this->_productloader->create()->load($id)->getName();
            return $productName;
        }
    }

    /**
     * @return array
     */
    public function getAllowedCustomerGroups()
    {
        $value = $this->helper->getConfigValue('requesttoquote_configuration/active/custgroups');
        $custgroups = explode(',', $value);
        return $custgroups;
    }

    /**
     * @return mixed
     */
    public function getAddtoCartCustomers()
    {
        return $this->helper->getConfigValue('requesttoquote_configuration/active/hidepriceandcart');
    }

    /**
     * @return mixed
     */
    public function getPriceHideCustomers()
    {
        return $this->getAddtoCartCustomers();
    }
}
