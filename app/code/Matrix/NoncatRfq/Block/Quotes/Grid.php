<?php
namespace Matrix\NoncatRfq\Block\Quotes;

use Ced\CsMarketplace\Model\Session;
use Matrix\NoncatalogueRfq\Helper\Data;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
    * @var Data
    */
    protected $helper;

    protected $_marketPlaceVendor;

    /**
    * @var \Magento\Framework\Session\Generic
    */
    protected $session;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Registry $registry,
        \Matrix\NoncatalogueRfq\Model\ResourceModel\NoncatalogRfq\CollectionFactory $quoteCollection,
        \Ced\RequestToQuote\Model\Source\QuoteStatus $quoteStatus,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        Session $customerSession,
        Data $helper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->quoteCollection = $quoteCollection;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
        $this->quoteStatus = $quoteStatus;
        $this->_marketPlaceVendor = $vendor;
        $this->helper = $helper;
        $this->session = $customerSession->getCustomerSession();
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

        $vendorId = $this->getVendorId(); //$this->registry->registry('vendor')['entity_id'];
        $cedMktvendor =$this->_marketPlaceVendor->create()->load($vendorId);
        $industry = $cedMktvendor->getIndustry();

        $catalog_category_entity_table = 'catalog_category_entity';
        $catalog_category_entity_varchar_table = "catalog_category_entity_varchar";

        $collection = $this->quoteCollection->create();
        $noncatrfq_vendor_tbl = 'matrix_noncatalog_rfq_vendor';
        if(isset($industry) && $industry>0) {
            $collection->getSelect()
            ->joinInner(
                $noncatrfq_vendor_tbl,
                'main_table.rfq_id = ' . $noncatrfq_vendor_tbl . '.rfq_id  AND
			' . $noncatrfq_vendor_tbl . '.vendor_id = ' . $vendorId . ' OR
			( main_table.rfq_type=1 AND main_table.category_ids = ' . $industry . ' )',
                ['is_emailsend ']
            )
            ->joinInner($catalog_category_entity_table, 'main_table.category_ids = ' . $catalog_category_entity_table . '.entity_id', ['entity_id'])
            ->joinInner($catalog_category_entity_varchar_table, $catalog_category_entity_table . '.entity_id = ' . $catalog_category_entity_varchar_table . '.entity_id AND ' . $catalog_category_entity_varchar_table . '.attribute_id =45 AND ' . $catalog_category_entity_varchar_table . '.store_id=0', ['category_name' => 'value'])
            ->group('main_table.rfq_id');
        } else {
            $collection->getSelect()->joinInner(
                $noncatrfq_vendor_tbl,
                'main_table.rfq_id = ' . $noncatrfq_vendor_tbl . '.rfq_id  AND ' . $noncatrfq_vendor_tbl . '.vendor_id = ' . $vendorId,
                ['is_emailsend ']
            )->group('main_table.rfq_id');
        }
        $collection->setOrder('main_table.rfq_id', 'desc');

        /*if(isset($industry) && $industry>0){
            $collection->getSelect()
            ->joinInner($noncatrfq_vendor_tbl,
            'main_table.rfq_id = '.$noncatrfq_vendor_tbl.'.rfq_id  AND
            '.$noncatrfq_vendor_tbl.'.vendor_id = '.$vendorId.' OR
            ( main_table.rfq_type=1 AND main_table.category_ids = '.$industry.' )',['is_emailsend '])
            ->joinInner($catalog_category_entity_table, 'main_table.category_ids = '.$catalog_category_entity_table.'.entity_id',['entity_id'])
            ->joinInner($catalog_category_entity_varchar_table, $catalog_category_entity_table.'.entity_id = '.$catalog_category_entity_varchar_table.'.entity_id AND '.$catalog_category_entity_varchar_table.'.attribute_id =45 AND '.$catalog_category_entity_varchar_table.'.store_id=0', ['category_name' => 'value'])
            ->group('main_table.rfq_id');
            $this->setCollection($collection);
        }else {
            $this->setCollection(null);
        }*/

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    private function getVendorId()
    {
        return $this->session->getVendorId();
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'rfq_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'rfq_id',
                ]
        );

        /*$this->addColumn(
    			'category_ids',
    			[
    			'header' => __('Category ID'),
    			'type' => 'string',
    			'index' => 'category_ids',
    			]
    	);*/

        $this->addColumn(
            'quote_increment_id',
            [
                'header' => __('Quote Increment Id'),
                'index' => 'quote_increment_id',
            ]
        );
        $this->addColumn(
            'category_name',
            [
                 'header' => __('Category'),
                 'type' => 'string',
                 'index' => 'category_name',
                 ]
        );
        $this->addColumn(
            'name ',
            [
                'header' => __('Customer Name'),
                'index' => 'name',
                'renderer'  => 'Matrix\NoncatRfq\Block\Quotes\Edit\Tab\Renderer\BuyerName',
                ]
        );

        $this->addColumn(
            'company_name ',
            [
                'header' => __('Company Name'),
                'index' => 'company_name',
                'renderer'  => 'Matrix\NoncatRfq\Block\Quotes\Edit\Tab\Renderer\BuyerCompanyname',
                ]
        );

        $arrRfqTypes = $this->helper->getRFQuoteTypes();
        $arrRfqTypes[$this->helper::NONCATALOG_RFQ_TYPE_PUBLIC] = __('PUBLIC');
        $arrRfqTypes[$this->helper::NONCATALOG_RFQ_TYPE_PRIVATE] = __('PRIVATE');

        $this->addColumn(
            'rfq_type',
            [
                 'header' => __('RFQ Type'),
                 'index' => 'rfq_type',
                 'type' => 'options',
                 'options' => $arrRfqTypes,
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

        return $this->getUrl('*/*/view', ['id' => $row->getRfqId()]);

    }
}
