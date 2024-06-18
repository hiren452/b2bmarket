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

namespace Ced\CsAuction\Block\AuctionList;

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
        \Ced\CsMarketplace\Model\Vproducts $vproduct,
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
        $this->_vproduct = $vproduct;
        $this->auctionCollection = $auction;
        $this->session = $session;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendorproductGrid');
        $this->setDefaultSort('post_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }

    protected function _prepareCollection()
    {

        $collection = $this->auctionCollection->create()->addFieldToFilter('vendor_id', $this->session->getVendorId());
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

        $this->addColumn('product_id', ['header' => __('Product Id'), 'index' => 'product_id','type' => 'number']);

        $this->addColumn('product_name', ['header' => __('Name'), 'index' => 'product_name']);

        $this->addColumn(
            'starting_price',
            [
                'header' => __('Start Price'),
                'type'  => 'currency',
                'index' => 'starting_price',
            ]
        );

        $this->addColumn(
            'max_price',
            [
                'header'=> __('Max Price'),
                'type'  => 'currency',
                'index' => 'max_price',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
            ]
        );

        $this->addColumn(
            'start_datetime',
            [
                'header' => __('Start Datetime'),
                'index' => 'start_datetime',
            ]
        );

        $this->addColumn(
            'end_datetime',
            [
                'header' => __('End Datetime'),
                'index' => 'end_datetime',
            ]
        );

        $this->addColumn(
            'sellproduct',
            [
                'header' => __('Sell Product'),
                'index' => 'sellproduct',
            ]
        );

        $this->addColumn(
            'edits',
            [
                'header' => __('Edit'),
                'caption' => __('Edit'),
                'renderer' => 'Ced\CsAuction\Block\AddAuction\Renderer\Edit',
                'sortable' => false,
                'filter' => false
            ]
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setTemplate('Magento_Backend::widget/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('product_id');
        $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('csauction/auctionlist/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        return $this;
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

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}
