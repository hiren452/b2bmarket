<?php
namespace Matrix\NoncatalogRfqfrom\Block\Adminhtml\Attribute;

class Grid extends \Magento\Eav\Block\Adminhtml\Attribute\Grid\AbstractGrid
{

    /**
     * @var \Matrix\RfqEntity\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $_attributesFactory;

    /**
     * @var \Matrix\NoncatalogRfqfrom\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $rfqFormCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Matrix\RfqEntity\Model\ResourceModel\Attribute\\CollectionFactory $attributesFactory
     * @param \Matrix\NoncatalogRfqfrom\Model\ResourceModel\Attribute\CollectionFactory $rfqFormCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Matrix\RfqEntity\Model\ResourceModel\Attribute\CollectionFactory $attributesFactory,
        \Matrix\NoncatalogRfqfrom\Model\ResourceModel\Attribute\CollectionFactory $rfqFormCollectionFactory,
        array $data = []
    ) {
        $this->_attributesFactory = $attributesFactory;
        $this->rfqFormCollectionFactory = $rfqFormCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize grid, set grid Id
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        //$this->setId('customerAttributeGrid');
        $this->setId('noncatalogrfqAttributeGrid');
        $this->setDefaultSort('sort_order');
    }

    /**
     * Prepare customer attributes grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $regAttributes = $this->rfqFormCollectionFactory->create()->getColumnValues('attribute_code');
        /** @var $collection \Magento\Customer\Model\ResourceModel\Attribute\Collection */
        $collection = $this->_attributesFactory->create();

        if (is_array($regAttributes) && count($regAttributes)) {
            $collection->addFieldToFilter('attribute_code', ['in' => $regAttributes]);
        }
        //$collection->addSystemHiddenFilter()->addExcludeHiddenFrontendFilter();
        $this->setCollection($collection);
        //echo $collection->getSelect();
        return parent::_prepareCollection();
    }

    /**
     * Prepare customer attributes grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('is_user_defined');
        $this->addColumn(
            'attribute_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'attribute_id',

            ]
        );
        $this->addColumn(
            'frontend_label',
            [
                'header' => __('Name'),
                'index' => 'frontend_label',

            ]
        );
        $this->addColumn(
            'attribute_code',
            [
                'header' => __('Attribute Code'),
                'index' => 'attribute_code',

            ]
        );
        $this->addColumn(
            'is_unique',
            [
                'header' => __('Unique'),
                'index' => 'is_unique',
                'type' => 'options',
                'options' => ['1' => __('Yes'), '0' => __('No')],
            ]
        );
        $this->addColumn(
            'is_required',
            [
                'header' => __('Required'),
                'sortable' => true,
                'index' => 'is_required',
                'type' => 'options',
                'options' => ['1' => __('Yes'), '0' => __('No')],

            ]
        );
        /*$this->addColumn(
            'status',
            [
                'header' => __('status'),
                'sortable' => true,
                'index' => 'status',
                'type' => 'options',
                'options' => ['1' => __('Enable'), '0' => __('Disable')],

            ]
        );*/
        $this->addColumn(
            'edit',
            [
                'header' => __('Edit'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => 'noncatrfqformfields/index/edit'
                        ],
                        'field' => 'attribute_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',

            ]
        );
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            'noncatrfqformfields/index/edit',
            [
                'attribute_id' => $row->getAttributeId()
            ]
        );
    }

}
