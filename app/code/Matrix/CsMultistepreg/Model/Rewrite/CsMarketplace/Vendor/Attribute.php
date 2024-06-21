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

namespace Matrix\CsMultistepreg\Model\Rewrite\CsMarketplace\Vendor;

use Ced\CsMarketplace\Model\Vendor;
use Ced\CsMarketplace\Model\Vendor\Form;
use Ced\CsMarketplace\Setup\CsMarketplaceSetup;
use Magento\Catalog\Model\Product\ReservedAttributeList;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;

class Attribute extends \Ced\CsMultistepreg\Model\Rewrite\CsMarketplace\Vendor\Attribute
{
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Config $eavConfig,
        TypeFactory $eavTypeFactory,
        StoreManagerInterface $storeManager,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        AttributeOptionInterfaceFactory $optionDataFactory,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        TimezoneInterface $localeDate,
        ReservedAttributeList $reservedAttributeList,
        ResolverInterface $localeResolver,
        DateTimeFormatterInterface $dateTimeFormatter,
        Vendor $vendor,
        Collection $setCollection,
        Form $vendorForm,
        ResourceConnection $resourceConnection,
        CsMarketplaceSetup $csMarketplaceSetup,
        CollectionFactory $attributeCollection,
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
            $vendor,
            $setCollection,
            $vendorForm,
            $resourceConnection,
            $csMarketplaceSetup,
            $attributeCollection,
            $data
        );
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
                    $this->setData('fontawesome_class_for_left_profile', $joinField->
                    getData('fontawesome_class_for_left_profile'));
                    $this->setData('position_in_left_profile', $joinField->getData('position_in_left_profile'));
                    $this->setData('use_in_invoice', $joinField->getData('use_in_invoice'));
                    $this->setData('registration_step_no', $joinField->getData('registration_step_no'));
                    $this->setData('description', $joinField->getData('description'));
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
                'description' => '',
            ];
            $this->vendorForm->insertMultiple($data);
            return $this->_vendorForm($attribute);
        }
        return $fields;
    }

    /**
     * Retrive Vendor attribute collection
     * @return AbstractCollection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCollection()
    {
        $collection = $this->getResourceCollection();
        $typeId = $this->vendor->getEntityTypeId();
        $collection = $collection->addFieldToFilter('entity_type_id', ['eq' => $typeId]);
        $labelTableName = $this->resourceConnection->getTableName('eav_attribute_label');
        $tableName = $this->resourceConnection->getTableName('ced_csmarketplace_vendor_form_attribute');
        if ($this->getStoreId()) {
            $availableStoreWiseIds = $this->getStoreWiseIds($this->getStoreId());
            $collection->getSelect()->join(
                ['vform' => $tableName],
                'main_table.attribute_id=vform.attribute_id',
                ['is_visible' => 'vform.is_visible', 'registration_step_no' => 'vform.registration_step_no',
                    'sort_order' => 'vform.sort_order', 'store_id' => 'vform.store_id',
                    'use_in_registration' => 'vform.use_in_registration', 'use_in_left_profile' => 'vform.use_in_left_profile',
                    'position_in_registration' => 'vform.position_in_registration',
                    'position_in_left_profile' => 'vform.position_in_left_profile',
                    'fontawesome_class_for_left_profile' => 'vform.fontawesome_class_for_left_profile',
                    'use_in_invoice' => 'vform.use_in_invoice', 'description' => 'vform.description']
            );
            $collection->getSelect()->where('(vform.attribute_id IN ("' . $availableStoreWiseIds . '")
             AND vform.store_id=' . $this->getStoreId() . ') OR (vform.attribute_id NOT IN
             ("' . $availableStoreWiseIds . '") AND vform.store_id=0)');
            $collection->getSelect()->group('vform.attribute_id');
            $collection->getSelect()->joinLeft(
                ['vlabel' => $labelTableName],
                'main_table.attribute_id=vlabel.attribute_id && vlabel.store_id=' . $this->getStoreId(),
                ['store_label' => 'vlabel.value']
            );
        } else {
            $collection->getSelect()->join(
                ['vform' => $tableName],
                'main_table.attribute_id=vform.attribute_id && vform.store_id=0',
                ['is_visible' => 'vform.is_visible',
                    'registration_step_no' => 'vform.registration_step_no', 'sort_order' => 'vform.sort_order',
                    'store_id' => 'vform.store_id', 'use_in_registration' => 'vform.use_in_registration',
                    'use_in_left_profile' => 'vform.use_in_left_profile',
                    'position_in_registration' => 'vform.position_in_registration',
                    'position_in_left_profile' => 'vform.position_in_left_profile',
                    'fontawesome_class_for_left_profile' => 'vform.fontawesome_class_for_left_profile',
                    'use_in_invoice' => 'vform.use_in_invoice', 'description' => 'vform.description']
            );
            $collection->getSelect()->joinLeft(
                ['vlabel' => $labelTableName],
                'main_table.attribute_id=vlabel.attribute_id && vlabel.store_id=0',
                ['store_label' => 'vlabel.value']
            );
        }
        return $collection;
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
                    $joinField->setData('fontawesome_class_for_left_profile', $this->
                    getData('fontawesome_class_for_left_profile'));
                    $joinField->setData('position_in_left_profile', $this->getData('position_in_left_profile'));
                    $joinField->setData('use_in_invoice', $this->getData('use_in_invoice'));
                    $joinField->setData('registration_step_no', $this->getData('registration_step_no'));
                    $joinField->setData('description', $this->getData('description'));
                    $joinField->save();
                }
            }
        }
        return $this;
    }
}
