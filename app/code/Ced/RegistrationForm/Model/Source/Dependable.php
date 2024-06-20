<?php

namespace Ced\RegistrationForm\Model\Source;

use Ced\RegistrationForm\Model\AttributeFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\Registry;

class Dependable implements ArrayInterface
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var AttributeCollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * Dependable constructor.
     *
     * @param Registry $registry
     * @param AttributeFactory $attributeFactory
     * @param AttributeCollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        Registry $registry,
        AttributeFactory $attributeFactory,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->_coreRegistry = $registry;
        $this->attributeFactory = $attributeFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $model = $this->_coreRegistry->registry('regform_data')->getData();
        if (count($model) > 0) {
            $models = $this->attributeFactory->create()->load($model['attribute_code'], 'attribute_code');
            if ($models != null) {
                return [['value' => $models['dependable_attribute'], 'label' => __($models['dependable_attribute'])]];
            }
        } else {
            $attributeCollection = $this->attributeCollectionFactory->create();
            $attributeCollection->addFieldToFilter('entity_type_id', 1)
                ->addFieldToFilter('source_model', 'Magento\Eav\Model\Entity\Attribute\Source\Boolean');

            $array = [];
            foreach ($attributeCollection->getData() as $value) {
                $array[] = $value['attribute_code'];
            }

            $modelCollection = $this->attributeFactory->create()->getCollection();
            $removeArray = [];
            foreach ($modelCollection->getData() as $val) {
                if ($val['dependable_attribute'] != null) {
                    $removeArray[] = $val['dependable_attribute'];
                }
            }

            $array = array_diff($array, $removeArray);
            $options = [];
            foreach ($array as $value) {
                $options[] = ['value' => $value, 'label' => __($value)];
            }
            return $options;
        }

        return [];
    }
}
