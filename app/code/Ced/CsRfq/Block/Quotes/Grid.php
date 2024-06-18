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
 * @package     Ced_CsRfq
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Block\Quotes;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\RequestToQuote\Model\ResourceModel\Quote\CollectionFactory $quoteCollection
     * @param \Ced\RequestToQuote\Model\Source\QuoteStatus $quoteStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Registry $registry,
        \Ced\RequestToQuote\Model\ResourceModel\Quote\CollectionFactory $quoteCollection,
        \Ced\RequestToQuote\Model\Source\QuoteStatus $quoteStatus,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->quoteCollection = $quoteCollection;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
        $this->quoteStatus = $quoteStatus;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quotesGrid');
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
        $vendorId = $this->registry->registry('vendor')['entity_id'];
        $collection = $this->quoteCollection->create()->addFieldToFilter('vendor_id', $vendorId);
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return mixed
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'quote_increment_id',
            [
                'header' => __('Id'),
                'index' => 'quote_increment_id',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'type'=>'date'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->quoteStatus->getAllOption(),
            ]
        );

        $this->addColumn(
            'last_updated_by',
            [
                'header' => __('Last Updated by'),
                'index' => 'last_updated_by',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return void
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
     * @return mixed
     */
    public function getGridUrl()
    {
        return $this->getUrl('rfq/quotes/grid', ['_current' => true]);
    }

    /**
     * @param $row
     * @return mixed
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', ['id' => $row->getQuoteId()]);
    }
}
