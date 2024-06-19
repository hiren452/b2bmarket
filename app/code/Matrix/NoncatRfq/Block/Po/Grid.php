<?php

namespace Matrix\NoncatRfq\Block\Po;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Registry $registry,
        \Matrix\NoncatalogueRfq\Model\ResourceModel\RfqPo\CollectionFactory $poCollection,
        \Ced\RequestToQuote\Model\Source\PoStatus $poStatus,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->poCollection = $poCollection;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
        $this->poStatus = $poStatus;
    }

    protected function _construct()
    {

        parent::_construct();
        $this->setId('quotesGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $vendorId = $this->registry->registry('vendor')['entity_id'];
        $collection = $this->poCollection->create()->addFieldToFilter('vendor_id', $vendorId);
        $noncatrfq_rfq_tbl = 'matrix_noncatalog_rfq';
        $collection->getSelect()->joinInner($noncatrfq_rfq_tbl, 'main_table.rfq_id = ' . $noncatrfq_rfq_tbl . '.rfq_id', ['quote_increment_id'])->group('main_table.rfq_id');
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'po_id',
                ]
        );

        $this->addColumn(
            'po_increment_id',
            [
                'header' => __('Po Increment Id'),
                'index' => 'po_increment_id',
            ]
        );

        $this->addColumn(
            'quote_increment_id',
            [
                'header' => __('Quote Increment Id'),
                'index' => 'quote_increment_id',
                'renderer' =>'Matrix\NoncatRfq\Block\Po\Renderer\QuoteId'
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

        /*$this->addColumn(
        		'name',
        		[
        		'header' => __('Quote Increment Id'),
        		'index' => 'quote_id',
        		'renderer' =>'Matrix\NoncatRfq\Block\Po\QuoteId'
        		]
        );*/

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->poStatus->getAllOption(),
                ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Prepare grid filter buttons
     *
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

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    public function getRowUrl($row)
    {

        return $this->getUrl('*/*/viewPo', ['id' => $row->getId()]);

    }
}
