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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultistepreg\Model\Rewrite\CsMarketplace\Vendor;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

class Attribute extends \Magento\Eav\Model\Entity\Attribute
{
    /**
     * Prefix of vendor attribute events names
     *
     * @var string
     */
    protected $_eventPrefix = 'csmarektplace_vendor_attribute';

    /**
     * Current scope (store Id)
     *
     * @var int
     */
    protected $_storeId;

    protected $_setup;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor\Form
     */
    protected $vendorForm;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    protected $setCollection;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    protected $vendor;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $attributeCollection;

    /**
     * @var \Ced\CsMarketplace\Setup\CsMarketplaceSetup
     */
    protected $csMarketplaceSetup;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Attribute constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $setCollection
     * @param \Ced\CsMarketplace\Model\Vendor\Form $vendorForm
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Ced\CsMarketplace\Setup\CsMarketplaceSetup $csMarketplaceSetup
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $attributeCollection
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        DateTimeFormatterInterface $dateTimeFormatter,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $setCollection,
        \Ced\CsMarketplace\Model\Vendor\Form $vendorForm,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ced\CsMarketplace\Setup\CsMarketplaceSetup $csMarketplaceSetup,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $attributeCollection,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $eavConfig,
            $eavTypeFactory,
            $storeManager,
            $resourceHelper,
            $universalFactory,
            $optionDataFactory,
            $dataObjectProcessor,
            $dataObjectHelper,
            $localeDate,
            $reservedAttributeList,
            $localeResolver,
            $dateTimeFormatter,
            null,
            null,
            $data
        );
        $this->vendor = $vendor;
        $this->setCollection = $setCollection;
        $this->storeManager = $storeManager;
        $this->vendorForm = $vendorForm;
        $this->resourceConnection = $resourceConnection;
        $this->csMarketplaceSetup = $csMarketplaceSetup;
        $this->attributeCollection = $attributeCollection;
        $this->setEntityTypeId($this->vendor->getEntityTypeId());
        $setIds = $this->setCollection->setEntityTypeFilter($this->getEntityTypeId())->getAllIds();
        $this->setAttributeSetIds($setIds);

    }

    /**
     * @param $store
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setStore($store)
    {
        $this->setStoreId($this->storeManager->getStore($store)->getId());
        return $this;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof \Magento\Store\Model\StoreManagerInterface) {
            $storeId = $storeId->getId();
        }
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Return current store id
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            $this->setStoreId($this->storeManager->getStore(null)->getId());
        }
        return $this->_storeId;
    }

    /**
     * Retrieve default store id
     * @return int
     */
    public function getDefaultStoreId()
    {
        return \Magento\Catalog\Model\AbstractModel::DEFAULT_STORE_ID;
    }

    /**
     * Load vendor's attributes into the object
     * @param int $entityId
     * @param null $field
     * @return $this|\Magento\Eav\Model\Entity\Attribute
     */
    public function load($entityId, $field = null)
    {

        parent::load($entityId, $field);
        if ($this && $this->getId()) {
            $joinFields = $this->_vendorForm($this);
            if (count($joinFields) > 0) {
                foreach ($joinFields as $joinField) {
                    $this->setData('is_visible', $joinField->getIsVisible());
                    $this->setData('position', $joinField->getSortOrder());
                    $this->setData('use_in_registration', $joinField->getData('use_in_registration'));
                    $this->setData('position_in_registration', $joinField->getData('position_in_registration'));
                    $this->setData('use_in_left_profile', $joinField->getData('use_in_left_profile'));
                    $this->setData('fontawesome_class_for_left_profile', $joinField->getData('fontawesome_class_for_left_profile'));
                    $this->setData('position_in_left_profile', $joinField->getData('position_in_left_profile'));
                    $this->setData('use_in_invoice', $joinField->getData('use_in_invoice'));
                    $this->setData('registration_step_no', $joinField->getData('registration_step_no'));
                }
            }
        }

        return $this;
    }

    public function _vendorForm($attribute)
    {
        $store = $this->getStoreId();
        $fields = $this->vendorForm->getCollection()
            ->addFieldToFilter('attribute_id', ['eq' => $attribute->getAttributeId()])
            ->addFieldToFilter('attribute_code', ['eq' => $attribute->getAttributeCode()]);

        if (count($fields) == 0) {
            $data[] = [
                'attribute_id' => $attribute->getId(),
                'attribute_code' => $attribute->getAttributeCode(),
                'is_visible' => 0,
                'sort_order' => 0,
                'store_id' => $store,
                'use_in_registration' => 0,
                'position_in_registration' => 0,
                'use_in_left_profile' => 0,
                'fontawesome_class_for_left_profile' => 'fa fa-circle-thin',
                'position_in_left_profile' => 0,
                'use_in_invoice' => 0,
            ];
            $this->vendorForm->insertMultiple($data);
            return $this->_vendorForm($attribute);
        }
        return $fields;
    }

    /**
     * Retrive Vendor attribute collection
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection()
    {
        $collection = parent::getCollection();
        $typeId = $this->vendor->getEntityTypeId();
        $collection = $collection->addFieldToFilter('entity_type_id', ['eq' => $typeId]);
        $labelTableName = $this->resourceConnection->getTableName('eav_attribute_label');

        $tableName = $this->resourceConnection->getTableName('ced_csmarketplace_vendor_form_attribute');
        if ($this->getStoreId()) {
            $availableStoreWiseIds = $this->getStoreWiseIds($this->getStoreId());
            $collection->getSelect()->join(['vform' => $tableName], 'main_table.attribute_id=vform.attribute_id', ['is_visible' => 'vform.is_visible', 'registration_step_no' => 'vform.registration_step_no', 'sort_order' => 'vform.sort_order', 'store_id' => 'vform.store_id', 'use_in_registration' => 'vform.use_in_registration', 'use_in_left_profile' => 'vform.use_in_left_profile', 'position_in_registration' => 'vform.position_in_registration', 'position_in_left_profile' => 'vform.position_in_left_profile', 'fontawesome_class_for_left_profile' => 'vform.fontawesome_class_for_left_profile', 'use_in_invoice' => 'vform.use_in_invoice']);
            $collection->getSelect()->where('(vform.attribute_id IN ("' . $availableStoreWiseIds . '") AND vform.store_id=' . $this->getStoreId() . ') OR (vform.attribute_id NOT IN ("' . $availableStoreWiseIds . '") AND vform.store_id=0)');
            $collection->getSelect()->group('vform.attribute_id');
            $collection->getSelect()->joinLeft(['vlabel' => $labelTableName], 'main_table.attribute_id=vlabel.attribute_id && vlabel.store_id=' . $this->getStoreId(), ['store_label' => 'vlabel.value']);
        } else {
            $collection->getSelect()->join(['vform' => $tableName], 'main_table.attribute_id=vform.attribute_id && vform.store_id=0', ['is_visible' => 'vform.is_visible', 'registration_step_no' => 'vform.registration_step_no', 'sort_order' => 'vform.sort_order', 'store_id' => 'vform.store_id', 'use_in_registration' => 'vform.use_in_registration', 'use_in_left_profile' => 'vform.use_in_left_profile', 'position_in_registration' => 'vform.position_in_registration', 'position_in_left_profile' => 'vform.position_in_left_profile', 'fontawesome_class_for_left_profile' => 'vform.fontawesome_class_for_left_profile', 'use_in_invoice' => 'vform.use_in_invoice']);
            $collection->getSelect()->joinLeft(['vlabel' => $labelTableName], 'main_table.attribute_id=vlabel.attribute_id && vlabel.store_id=0', ['store_label' => 'vlabel.value']);
        }

        return $collection;
    }

    /**
     * @param int $storeId
     * @return array|string
     */
    public function getStoreWiseIds($storeId = 0)
    {
        if ($storeId) {
            $allowed = [];
            foreach ($this->vendorForm->getCollection()
                         ->addFieldToFilter('store_id', ['eq' => $storeId])
                     as $attribute) {
                $allowed[] = $attribute->getAttributeId();
            }
            return implode(',', $allowed);
        }
        return [];
    }

    /**
     * @param $group
     */
    public function addToGroup($group)
    {
        if ($group) {
            $setIds = $this->setCollection
                ->setEntityTypeFilter($this->getEntityTypeId())->getAllIds();
            $setId = isset($setIds[0]) ? $setIds[0] : $this->getEntityTypeId();
            $installer = $this->csMarketplaceSetup;

            if (!in_array($group, $this->getGroupOptions($setId, true))) {
                $installer->addAttributeGroup(
                    'csmarketplace_vendor',
                    $setId,
                    $group
                );
            }
            $installer->addAttributeToGroup(
                'csmarketplace_vendor',
                $setId,
                $group, //Group Name
                $this->getAttributeId()
            );
        }
    }

    /**
     * @param $setId
     * @param bool $flag
     * @return array
     */
    protected function getGroupOptions($setId, $flag = false)
    {
        $groupCollection = $this->attributeCollection->create()
            ->setAttributeSetFilter($setId);

        $groupCollection->setSortOrder()->load();
        $options = [];
        if ($flag) {
            foreach ($groupCollection as $group) {
                $options[] = $group->getAttributeGroupName();
            }
        } else {
            foreach ($groupCollection as $group) {
                $options[$group->getId()] = $group->getAttributeGroupName();
            }
        }
        return $options;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute
     * @throws \Exception
     */
    public function delete()
    {
        if ($this->getId()) {
            $joinFields = $this->_vendorForm($this);
            if (count($joinFields) > 0) {
                foreach ($joinFields as $joinField) {
                    $joinField->delete();
                }
            }
        }
        return parent::delete();
    }

    /**
     * Processing vendor attribute after save data
     * @return $this|\Magento\Eav\Model\Entity\Attribute
     */
    public function afterSave()
    {
        parent::afterSave();
        if ($this->getId()) {
            $joinFields = $this->_vendorForm($this);
            if (count($joinFields) > 0) {
                foreach ($joinFields as $joinField) {
                    $joinField->setData('is_visible', $this->getData('is_visible'));
                    $joinField->setData('sort_order', $this->getData('position'));
                    $joinField->setData('use_in_registration', $this->getData('use_in_registration'));
                    $joinField->setData('position_in_registration', $this->getData('position_in_registration'));
                    $joinField->setData('use_in_left_profile', $this->getData('use_in_left_profile'));
                    $joinField->setData('fontawesome_class_for_left_profile', $this->getData('fontawesome_class_for_left_profile'));
                    $joinField->setData('position_in_left_profile', $this->getData('position_in_left_profile'));
                    $joinField->setData('use_in_invoice', $this->getData('use_in_invoice'));
                    $joinField->setData('registration_step_no', $this->getData('registration_step_no'));
                    $joinField->save();
                }
            }
        }
        return $this;
    }
}
