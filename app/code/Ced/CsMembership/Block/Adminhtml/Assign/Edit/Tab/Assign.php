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

namespace Ced\CsMembership\Block\Adminhtml\Assign\Edit\Tab;

/**
 * Class Assign (for membership assignment)
 */
class Assign extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Ced\CsMembership\Helper\Data
     */
    protected $membershipHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * Assign constructor.
     * @param \Ced\CsMembership\Helper\Data $membershipHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Ced\CsMembership\Helper\Data $membershipHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);

        $this->membershipHelper = $membershipHelper;
        $this->vendorFactory = $vendorFactory;

        $vendor_id = $this->getRequest()->getParam('vendor_id', 0);
        $this->setId('vordersGrids_' . $vendor_id);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare Collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $packageId = $this->getRequest()->getParam('id');
        $existingVendorIds = $this->membershipHelper->getSubscribedUsers($packageId);
        $collection = $this->vendorFactory->create()->getCollection();
        if (count($existingVendorIds) > 0) {
            $collection->addFieldToFilter('entity_id', ['nin' => $existingVendorIds]);
        }
        $collection->addAttributeToSelect('*');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare Columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
        $this->addColumn('membership_vendors', [
                'header_css_class' => 'a-center',
                'align' => 'center',
                'index' => 'entity_id',
                'type' => 'checkbox',
                'renderer' => 'Ced\CsMembership\Block\Adminhtml\Assign\Edit\Tab\Renderer\Check'
            ]);

        $this->addColumn("vendor_id", [
                "header" => __("Vendor Id"),
                'header_css_class' => 'a-center',
                'type' => 'text',
                "index" => "entity_id",
                'align' => 'center',
                'name' => 'vendor_id'
            ]);

        $this->addColumn("created_at", [
                "header" => __("Created At"),
                'header_css_class' => 'a-center',
                'type' => 'datetime',
                "index" => "created_at",
                'align' => 'right',
                'width' => '50px'
            ]);

        $this->addColumn("name", [
                "header" => __("Vendor Name"),
                'type' => 'text',
                "index" => "name",
                'align' => 'left',
                'name' => 'name',
                'filter_index' => 'name'
            ]);

        $this->addColumn("email", [
                "header" => __("Vendor Email"),
                "index" => "email",
                'align' => 'left',
                'filter_index' => 'email'
            ]);

        $this->addColumn("shop_url", [
                "header" => __("Vendor Shop Url"),
                "index" => "shop_url",
                'align' => 'left',
                'filter_index' => 'shop_url'
            ]);

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * Get Grid Row Url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/vendorgrid', ['_secure' => true, '_current' => true]);
    }

    /**
     * Remove existing column
     *
     * @param string $columnId
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    public function removeColumn($columnId)
    {
        if (isset($this->_columns[$columnId])) {
            unset($this->_columns[$columnId]);
            if ($this->_lastColumnId == $columnId) {
                $this->_lastColumnId = key($this->_columns);
            }
        }
        return $this;
    }
}
