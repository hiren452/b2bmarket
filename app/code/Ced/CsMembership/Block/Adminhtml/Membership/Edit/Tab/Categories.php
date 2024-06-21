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

namespace Ced\CsMembership\Block\Adminhtml\Membership\Edit\Tab;

use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

class Categories extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_filtercollection;

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type
     */
    protected $_type;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var
     */
    protected $registerInterface;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    public $marketplaceSession;

    /**
     * @var \Ced\CsMembership\Model\MembershipFactory
     */
    protected $membershipFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public $_vendorFactory;

    /**
     * Categories constructor.
     * @param \Magento\Framework\Registry $registerInterface
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\Store $store
     * @param \Ced\CsMembership\Model\MembershipFactory $membershipFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param \Ced\CsMarketplace\Model\Session $customerSession
     * @param UrlFactory $urlFactory
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Magento\Framework\Registry $registerInterface,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type,
        \Magento\Framework\Module\Manager $moduleManager,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\Store $store,
        \Ced\CsMembership\Model\MembershipFactory $membershipFactory,
        \Ced\CsMarketplace\Helper\Data $marketplaceHelper,
        \Ced\CsMarketplace\Helper\Vproducts\Category $vproductsCategory,
        \Magento\Catalog\Model\Category $catalogCategory,
        \Magento\Store\Model\GroupFactory $groupFactory,
        \Ced\CsMembership\Model\Vproducts $membershipVproducts,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        \Ced\CsMarketplace\Model\Session $customerSession,
        UrlFactory $urlFactory
    ) {
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

        $this->vproductsFactory = $vproductsFactory;
        $this->storeManager = $context->getStoreManager();
        $this->productFactory = $productFactory;
        $this->store = $store;
        $this->marketplaceSession = $customerSession;
        $this->membershipFactory = $membershipFactory;
        $this->moduleManager = $moduleManager;
        $this->_type = $type;
        $this->_coreRegistry = $registerInterface;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->vproductsCategory = $vproductsCategory;
        $this->catalogCategory = $catalogCategory;
        $this->groupFactory = $groupFactory;
        $this->membershipVproducts = $membershipVproducts;

        $this->setTemplate('csmembership/categories.phtml');
        $vendorId = $this->getVendorId();

        $collection = $this->vproductsFactory->create()->getVendorProducts('', $vendorId, 0);

        if (count($collection) > 0) {
            $products = [];
            $statusarray = [];
            foreach ($collection as $data) {
                array_push($products, $data->getProductId());
                $statusarray[$data->getProductId()] = $data->getCheckStatus();
            }
            $currentStore = $this->storeManager->getStore(null)->getId();
            $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            $productcollection = $this->productFactory->create()->getCollection();

            $storeId = 0;
            if ($this->getRequest()->getParam('store')) {
                $websiteId = $this->store->load($this->getRequest()->getParam('store'))->getWebsiteId();
                if ($websiteId) {
                    if (in_array($websiteId, $this->vproductsFactory->create()->getAllowedWebsiteIds())) {
                        $storeId = $this->getRequest()->getParam('store');
                    }
                }
            }

            $productcollection->addAttributeToSelect('*')->addAttributeToFilter('entity_id', ['in' => $products])->addAttributeToSort('entity_id', 'DESC');

            if ($storeId) {
                $productcollection->addStoreFilter($storeId);
                $productcollection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $storeId);
                $productcollection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $storeId);
                $productcollection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $storeId);
                $productcollection->joinAttribute('thumbnail', 'catalog_product/thumbnail', 'entity_id', null, 'left', $storeId);
            }

            if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
                $productcollection->joinField(
                    'qty',
                    'cataloginventory_stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left'
                );
            }
            $productcollection->joinField('check_status', 'ced_csmarketplace_vendor_products', 'check_status', 'product_id=entity_id', null, 'left');

            $params = $this->marketplaceSession->getData('product_filter');

            if (isset($params) && is_array($params) && count($params) > 0) {
                foreach ($params as $field => $value) {
                    if ($field == 'store' || $field == 'store_switcher' || $field == "__SID") {
                        continue;
                    }
                    if (is_array($value)) {
                        if (isset($value['from']) && urldecode($value['from']) != "") {
                            $from = urldecode($value['from']);
                            $productcollection->addAttributeToFilter($field, ['gteq' => $from]);
                        }
                        if (isset($value['to']) && urldecode($value['to']) != "") {
                            $to = urldecode($value['to']);
                            $productcollection->addAttributeToFilter($field, ['lteq' => $to]);
                        }
                    } elseif (urldecode($value) != "") {
                        $productcollection->addAttributeToFilter($field, ["like" => '%' . urldecode($value) . '%']);
                    }
                }
            }

            $this->storeManager->setCurrentStore($currentStore);
            $productcollection->setStoreId($storeId);
            if ($productcollection->getSize() > 0) {
                $this->_filtercollection = $productcollection;
                $this->setVproducts($this->_filtercollection);
            }
        }
    }

    /**
     * prepare product list layout
     * @return Ced_CsMarketplace_Block_Vproducts
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->_filtercollection) {
            if ($this->_filtercollection->getSize() > 0) {
                if ($this->getRequest()->getActionName() == 'index') {
                    $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'custom.pager');
                    $pager->setAvailableLimit([5 => 5, 10 => 10, 20 => 20, 'all' => 'all']);
                    $pager->setCollection($this->_filtercollection);
                    $this->setChild('pager', $pager);
                }
            }
        }
        return $this;
    }

    /**
     * Get pager HTML
     *
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * get Edit product url
     *
     */
    public function getEditUrl($product)
    {
        return $this->getUrl('*/*/edit', ['_nosid' => true, 'id' => $product->getId(), 'type' => $product->getTypeId(), 'store' => $this->getRequest()->getParam('store', 0)]);
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->_type->toOptionArray(false, true);
    }

    /**
     * get Product Type url
     *
     */
    public function getProductTypeUrl()
    {
        return $this->getUrl('*/*/new/', ['_nosid' => true]);
    }

    /**
     * get Delete url
     *
     */
    public function getDeleteUrl($product)
    {
        return $this->getUrl('*/*/delete', ['_nosid' => true, 'id' => $product->getId()]);
    }

    /**
     * back Link url
     *
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * get Category IDs
     *
     */
    public function getCategoryIds()
    {
        $catId_json = $this->getProduct();
        $category_array = array_unique(explode(',', $catId_json));
        if ($this->_coreRegistry->registry("csmembership_category")) {
            $cat_array = array_unique(explode(',', $this->_coreRegistry->registry("csmembership_category")));
            return $cat_array;
        }
        return $category_array;
    }

    /**
     * Forms string out of getCategoryIds()
     *
     * @return string
     */
    public function getIdsString()
    {
        return implode(',', $this->getCategoryIds());
    }

    /**
     * Retrieve currently edited product
     *
     */
    public function getProduct()
    {
        if ($this->getRequest()->getParam('id')) {
            return $this->membershipFactory->create()->load($this->getRequest()->getParam('id'))->getData('category_ids');
        }
    }
}
