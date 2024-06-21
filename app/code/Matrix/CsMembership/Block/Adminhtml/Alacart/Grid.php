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

namespace Matrix\CsMembership\Block\Adminhtml\Alacart;

use Ced\CsMarketplace\Model\System\Config\Source\Status;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Matrix\CsMembership\Model\AlaCartPriceFactory;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $alaCartPriceFactory;

    protected $_status;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param AlaCartPriceFactory $alaCartPriceFactory
     * @param Status $status
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        AlaCartPriceFactory $alaCartPriceFactory,
        Status $status,
        array $data = []
    ) {
        $this->alaCartPriceFactory = $alaCartPriceFactory;
        $this->_status = $status;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('alacartGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->alaCartPriceFactory->create()->getCollection();
        $filter     = $this->getRequest()->getParams();
        if (isset($filter['id']) && $filter['id']!='') {
            $collection->addFieldToFilter('id', $this->getRequest()->getParam('id'));
        }

        $this->setCollection($collection);
        return  parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        /*$this->addColumn('id', [
                'header'    => __('ID'),
                'align'     =>'right',
                'index'     => 'id',
                "width" => "50px",
                'type'    => 'number'
            ]
        );*/

        $this->addColumn("name", [
                "header" => __("Title"),
                "index" => "name",
                ]);

        /*$this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => [1 => 'Yes', 0 => 'No']
            ]
        );*/

        $this->addColumn(
            'commission',
            [
                'header' => __('Prices'),
                'index' => 'commission',
                'renderer' => 'Matrix\CsMembership\Block\Adminhtml\Alacart\Renderer\Prices'
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
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
