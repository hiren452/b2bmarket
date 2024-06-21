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
 * @category  Ced
 * @package   Ced_CsMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Helper;

/**
 * Class Marketplace (Helper for adding common functions used in various files)
 */
class Marketplace extends \Ced\CsMarketplace\Helper\Data
{
    /**
     * @var Data
     */
    protected $membershipHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Marketplace constructor.
     * @param Data $membershipHelper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $moduleRegistry
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ValueInterface $value
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Catalog\Model\Product\ActionFactory $actionFactory
     * @param \Magento\Indexer\Model\ProcessorFactory $processorFactory
     * @param \Magento\Catalog\Model\Product\Website $website
     * @param \Ced\CsMarketplace\Model\Vshop $vshop
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Ced\CsMarketplace\Model\NotificationFactory $notificationFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\Stdlib\StringUtils $stringUtils
     * @param \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Ced\CsMembership\Helper\Data $membershipHelper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Component\ComponentRegistrarInterface $moduleRegistry,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ValueInterface $value,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Catalog\Model\Product\ActionFactory $actionFactory,
        \Magento\Indexer\Model\ProcessorFactory $processorFactory,
        \Magento\Catalog\Model\Product\Website $website,
        \Ced\CsMarketplace\Model\Vshop $vshop,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Ced\CsMarketplace\Model\NotificationFactory $notificationFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory,
        \Magento\Store\Model\Store $store,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct(
            $filterManager,
            $moduleRegistry,
            $cacheTypeList,
            $cacheFrontendPool,
            $request,
            $productMetadata,
            $storeManager,
            $value,
            $transaction,
            $requestInterface,
            $state,
            $websiteFactory,
            $actionFactory,
            $processorFactory,
            $website,
            $vshop,
            $resourceConnection,
            $deploymentConfig,
            $notificationFactory,
            $vproductsFactory,
            $vendorFactory,
            $stringUtils,
            $vpaymentFactory,
            $store,
            $context
        );

        $this->membershipHelper = $membershipHelper;
    }

    /**
     * Function for getting Config value of current store
     *
     * @param string $path ,
     */
    public function getStoreConfig($path, $storeId = null)
    {

        $store = $this->_storeManager->getStore($storeId);

        $patharray = [];
        $patharray = explode('/', $path);
        $key = end($patharray);
        if ($key == 'category') {

            $subcriptionLimit = $this->membershipHelper->getAllowedCategory($path, $store);

            $subcriptionLimit = array_unique($subcriptionLimit);
            $subcriptionLimit = array_filter($subcriptionLimit);
            $subcriptionLimit = implode(',', $subcriptionLimit);
            return $subcriptionLimit;
        }

        return $this->_scopeConfigManager->getValue($path, 'store', $store->getCode());
    }

    /**
     * Get Product limit
     *
     * @return integer
     */
    public function getVendorProductLimit()
    {
        $storeId = $this->_storeManager->getStore(null)->getStoreId();
        $subcriptionLimit = $this->membershipHelper->getLimit($storeId);
        $defaultLimit = $this->_scopeConfigManager->getValue('ced_vproducts/general/limit');
        return $subcriptionLimit + $defaultLimit;
    }
}
