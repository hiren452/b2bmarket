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
 * @package     Ced_CsMultistepregistration
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultistepreg\Block\Adminhtml\Rewrites\CsVattribute;

class Grid extends \Magento\Eav\Block\Adminhtml\Attribute\Grid\AbstractGrid
{

    protected $_gridFactory;

    protected $_status;

    protected $backendHelper;

    protected $_resource;

    protected $storeManager;

    protected $vendor;

    protected $attributeCollecton;

    const VAR_NAME_FILTER = 'vendor_attribute';

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollecton
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollecton,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->storeManager = $storeManager;
        $this->attributeCollection = $attributeCollecton;
        $this->vendor = $vendor;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('attributeGrid');
        $this->setDefaultSort('Asc');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('vendor_attribute');
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return $this->storeManager->getStore($storeId);
    }

    /**
     * @return $this|\Magento\Eav\Block\Adminhtml\Attribute\Grid\AbstractGrid
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareCollection()
    {

        $store = $this->_getStore();
        $this->setStoreId($store->getId());

        $collection = $this->attributeCollection->create();

        $typeId = $this->vendor->getEntityTypeId();
        $collection = $collection->addFieldToFilter('entity_type_id', ['eq'=>$typeId]);

        $tableName = $this->_resource->getTableName('ced_csmarketplace_vendor_form_attribute');

        $collection->getSelect()->joinLeft(['vform'=>$tableName], 'main_table.attribute_id = vform.attribute_id && vform.store_id =' . $this->getStoreId(), ['is_visible'=> 'vform.is_visible', 'use_in_registration'=>'vform.use_in_registration', 'use_in_left_profile'=>'vform.use_in_left_profile','use_in_invoice'=> 'vform.use_in_invoice','registration_step_no'=>'vform.registration_step_no']);

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this|\Magento\Eav\Block\Adminhtml\Attribute\Grid\AbstractGrid
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->getColumn('attribute_code')->setFilterIndex('main_table.attribute_code');
        $this->addColumnAfter(
            'is_visible',
            [
            'header'=>__('Use in Edit Form'),
            'sortable'=>true,
            'index'=>'is_visible',
            'type' => 'options',
            'options' => [
                '1' => __('Yes'),
                '0' => __('No'),
            ],
            'align' => 'center',
            ],
            'frontend_label'
        );

        $this->addColumnAfter(
            'use_in_registration',
            [
            'header'=>__('Use in Registration Form'),
            'sortable'=>true,
            'index'=>'use_in_registration',
            'type' => 'options',
            'options' => [
                '1' => __('Yes'),
                '0' => __('No'),
            ],
            'align' => 'center',
            ],
            'is_visible'
        );

        $this->addColumnAfter(
            'use_in_invoice',
            [
            'header'=>__('Use in Seller Invoice'),
            'sortable'=>true,
            'index'=>'use_in_invoice',
            'type' => 'options',
            'options' => [
                '1' => __('Yes'),
                '0' => __('No'),
            ],
            'align' => 'center',
            ],
            'use_in_invoice'
        );

        $this->addColumnAfter(
            'use_in_left_profile',
            [
            'header'=>__('Use in Left Profile'),
            'sortable'=>true,
            'index'=>'use_in_left_profile',
            'type' => 'options',
            'options' => [
                '1' => __('Yes'),
                '0' => __('No'),
            ],
            'align' => 'center',
            ],
            'use_in_registration'
        );
        $this->addColumn(
            'registration_step_no',
            [
                'header' => __('Step Number'),
                'sortable' => true,
                'index' => 'registration_step_no',
                'header_css_class' => 'col-attr-code',
                'column_css_class' => 'col-attr-code'
            ]
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_secure'=>true, '_current'=>true]);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['attribute_id' => $row->getAttributeId()]);
    }
}
