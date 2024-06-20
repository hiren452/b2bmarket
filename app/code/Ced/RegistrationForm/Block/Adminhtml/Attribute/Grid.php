<?php
namespace Ced\RegistrationForm\Block\Adminhtml\Attribute;

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Grid extends \Magento\Eav\Block\Adminhtml\Attribute\Grid\AbstractGrid
{

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $_attributesFactory;

    /**
     * @var \Ced\RegistrationForm\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $regFormCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributesFactory
     * @param \Ced\RegistrationForm\Model\ResourceModel\Attribute\CollectionFactory $regFormCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributesFactory,
        \Ced\RegistrationForm\Model\ResourceModel\Attribute\CollectionFactory $regFormCollectionFactory,
        array $data = []
    ) {
        $this->_attributesFactory = $attributesFactory;
        $this->regFormCollectionFactory = $regFormCollectionFactory;
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
        $this->setId('customerAttributeGrid');
        $this->setDefaultSort('sort_order');
    }

    /**
     * Prepare customer attributes grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $regAttributes = $this->regFormCollectionFactory->create()->getColumnValues('attribute_code');
        /** @var $collection \Magento\Customer\Model\ResourceModel\Attribute\Collection */
        $collection = $this->_attributesFactory->create();
        if (is_array($regAttributes) && count($regAttributes)) {
            $collection->addFieldToFilter('attribute_code', ['in' => $regAttributes]);
        } else {
            $collection->addFieldToFilter('attribute_code', ['in' => []]);
        }

        $collection->addSystemHiddenFilter()->addExcludeHiddenFrontendFilter();
        $this->setCollection($collection);
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
                            'base' => 'regform/registration/edit'
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
            'regform/registration/edit',
            [
                'attribute_id' => $row->getAttributeId()
            ]
        );
    }
}
