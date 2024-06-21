<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Block\Adminhtml\Assign;

/**
 * Class Grid (for membership grid)
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \\Ced\CsMarketplace\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Group
     */
    protected $_group;

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Status
     */
    protected $_status;

    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Ced\CsMembership\Model\MembershipFactory $membershipFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Group $group
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Status $status
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\CsMembership\Model\MembershipFactory $membershipFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Ced\CsMarketplace\Model\System\Config\Source\Group $group,
        \Ced\CsMarketplace\Model\System\Config\Source\Status $status,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        array $data = []
    ) {
        $this->_membershipFactory = $membershipFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->_group = $group;
        $this->_status = $status;
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Function for default settings
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('assignGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_filter');
    }

    /**
     * Function for preparing collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_membershipFactory->create()->getCollection();
        $this->setCollection($collection);
        return  parent::_prepareCollection();
    }

    /**
     * Add column filter to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * Prepare columns
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', [
                'header'    => __('Package Id #'),
                'align'     =>'right',
                'index'     => 'id',
                "width" => "50px",
                'type'      => 'text'
            ]);

        $this->addColumn("name", [
                "header" => __("Package Name"),
                "index" => "name",
                ]);

        $store = $this->_getStore();
        $this->addColumn(
            'price',
            [
                'header' => __('Package Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        $this->addColumn(
            'special_price',
            [
                'header' => __('Package Special Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'special_price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * Function for getting store
     *
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Get grid row url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * Get row url
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/edit',
            ['id' => $row->getId()]
        );
    }
}