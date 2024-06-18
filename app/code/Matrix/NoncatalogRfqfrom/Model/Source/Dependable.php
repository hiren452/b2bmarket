<?php

namespace Matrix\NoncatalogRfqfrom\Model\Source;

use Magento\Eav\Model\Entity\AttributeFactory as EavAttributeFactory;
use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\Registry;
use Matrix\NoncatalogRfqfrom\Model\AttributeFactory;

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
     * @var EavAttributeFactory
     */
    protected $eavAttributeFactory;

    /**
     * @param Registry $registry
     * @param AttributeFactory $attributeFactory
     * @param EavAttributeFactory $eavAttributeFactory
     */
    public function __construct(
        Registry $registry,
        AttributeFactory $attributeFactory,
        EavAttributeFactory $eavAttributeFactory
    ) {
        $this->_coreRegistry = $registry;
        $this->attributeFactory = $attributeFactory;
        $this->eavAttributeFactory = $eavAttributeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $model = $this->_coreRegistry->registry('noncatalogrfqform_data')->getData();
        if (count($model) > 0) {
            $attributeModel = $this->attributeFactory->create()->load($model['attribute_code'], 'attribute_code');
            if ($attributeModel->getId()) {
                return [
                    ['value' => $attributeModel->getData('dependable_attribute'), 'label' => __($attributeModel->getData('dependable_attribute'))]
                ];
            }
        } else {
            $attributeCollection = $this->eavAttributeFactory->create()->getCollection()
                ->addFieldToFilter('entity_type_id', 1)
                ->addFieldToFilter('source_model', 'Magento\Eav\Model\Entity\Attribute\Source\Boolean');

            $array = [];
            foreach ($attributeCollection as $attribute) {
                $array[] = $attribute->getAttributeCode();
            }

            $attributeModelCollection = $this->attributeFactory->create()->getCollection();
            $removeArray = [];
            foreach ($attributeModelCollection as $attributeModel) {
                if ($attributeModel->getData('dependable_attribute') !== null) {
                    $removeArray[] = $attributeModel->getData('dependable_attribute');
                }
            }

            $options = [];
            $array = array_diff($array, $removeArray);
            foreach ($array as $value) {
                $options[] = ['value' => $value, 'label' => __($value)];
            }
            return $options;
        }

        return [];
    }
}
