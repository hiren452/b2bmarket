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
 * @package     Ced_CsRfq
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsRfq\Helper;

use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session as CustomerSession;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currency
     * @param \Magento\Directory\Model\Currency $currencyModel
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currency,
        \Magento\Directory\Model\Currency $currencyModel,
        ProductRepository $productRepository,
        CustomerSession $customerSession
    ) {
        $this->vsettingsFactory = $vsettingsFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->vendorFactory = $vendorFactory;
        $this->_storeManager = $storeManager;
        $this->currency = $currency;
        $this->currencyModel = $currencyModel;
        $this->productRepository = $productRepository;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isVendorRfqEnable()
    {
        if ($this->getConfigValue('ced_csmarketplace/general/csrfq_enable')) {
            return true;
        }
        return false;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $vendorId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSeller($vendorId)
    {
        $vendor = $this->vendorFactory->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', $vendorId)
                ->getFirstItem();
        if ($vendor->getEntityId()) {
            return $vendor->getPublicName();
        }
        return "Admin";
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode()
    {

        $code = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        return $this->currency->create()->load($code)->getCurrencySymbol();
    }

    /**
     * @param $price
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function priceFormat($price)
    {
        if ($price) {
            $price = $this->currencyModel->format(
                $price,
                [
                    'symbol' => $this->getCurrencyCode(),
                    'precision'=> 2
                ],
                false
            );
        }
        return $price;
    }
    /**
     * Retrieve product collection by product ID.
     *
     * @param int $productId
     * @return \Magento\Catalog\Model\Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductById($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * Check if customer is logged in.
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }
    /**
    * Get customer group ID.
    *
    * @return int
    */
    public function getCustomerGroupId()
    {
        if ($this->isLoggedIn()) {
            return $this->customerSession->getCustomer()->getGroupId();
        }
        return 0; // Default group ID for not logged in users
    }
}
