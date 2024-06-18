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
 * @package   Ced_CsAuction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsAuction\Block\AddAuction;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_collectionFactory;
    protected $_productFactory;
    protected $_vproduct;
    protected $_type;
    protected $pageLayoutBuilder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Framework\Module\Manager $moduleManager,
        \Ced\CsMarketplace\Model\ResourceModel\Vproducts\Collection $vproduct,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $product,
        \Ced\Auction\Model\ResourceModel\Auction\CollectionFactory $auction,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        $this->_websiteFactory = $websiteFactory;
        $this->_collectionFactory = $setsFactory;
        $this->_productFactory = $productFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->_visibility = $visibility;
        $this->moduleManager = $moduleManager;
        $this->vproduct = $vproduct;
        $this->auctionCollection = $auction;
        $this->product = $product;
        $this->session = $session;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendorGrid');
        $this->setDefaultSort('post_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }

    protected function _prepareCollection()
    {
        $productId = [];
        $auctions = $this->auctionCollection->create()
            ->addFieldToFilter('status', ['processing','disapprove','not started']);

        $product = $this->vproduct
            ->addFieldToFilter('vendor_id', $this->session->getVendorId())
            ->addFieldToFilter('check_status', \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS);
        $vendorProductId = [];
        if($product->getData()!= null) {
            foreach ($product as $products) {
                array_push($vendorProductId, $products['product_id']);
            }
        }

        if ($auctions->getData() != null) {
            foreach ($auctions as $auction) {
                array_push($productId, $auction['product_id']);
            }

            $collection = $this->product->create()
               //->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
                ->addAttributeToFilter('visibility', ['neq'=>\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE]);
            // ->addAttributeToFilter('entity_id',['in' => $vendorProductId])
            //->addAttributeToFilter('entity_id',['nin'=> $productId]);

            $this->setCollection($collection);
            parent::_prepareCollection();
            return $this;
        }

        $collection = $this->product->create()
            //->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->addAttributeToFilter('visibility', ['neq'=>\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE]);
        /// ->addAttributeToFilter('entity_id',['in' => $vendorProductId]);

        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', ['header' => __('Product Id'), 'index' => 'entity_id','type' => 'number']);

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product Name'),
                'index' => 'name',
                'renderer' => 'Ced\CsAuction\Block\AddAuction\Grid\Renderer\ProductName',
                'filter'=> false
            ]
        );

        $this->addColumn(
            'type_id',
            [
                'header' => __('Type'),
                'index' => 'type_id',
                'type' => 'options',
                'options' =>  $this->_type->getOptionArray(),
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header'=> __('SKU'),
                'index' => 'sku',
            ]
        );

        $this->addColumn(
            'price',
            [
                'header'=> __('Price'),
                'renderer' => 'Ced\CsAuction\Block\AddAuction\Grid\Renderer\ProductPrice',
                'filter'=> false
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareFilterButtons()
    {
        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Reset Filter'),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()',
                    'class' => 'action-reset action-tertiary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-reset'])
        );
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Search'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'action-secondary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-apply'])
        );
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
    public function getRowUrl($row)
    {

        return $this->getUrl('csauction/addauction/auctionform', ['product_id'=>$row->getId()]);
    }

    protected function _productStatusFilter($collection, $column)
    {
        if(!strlen($column->getFilter()->getValue())) {
            return $this;
        }
        if($column->getFilter()->getValue()==\Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS . \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            $this->getCollection()
                ->addFieldToFilter('check_status', ['eq' =>\Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS]);
            //->addFieldToFilter('status', array('eq' =>\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED));
        } elseif($column->getFilter()->getValue()==\Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS . \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
            $this->getCollection()
                ->addFieldToFilter('check_status', ['eq' =>\Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS]);
            //->addAttributeToFilter('status', array('eq' =>\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED));
        } else {
            $this->getCollection()->addFieldToFilter('check_status', ['eq' =>$column->getFilter()->getValue()]);
        }
        return $this;
    }

}
