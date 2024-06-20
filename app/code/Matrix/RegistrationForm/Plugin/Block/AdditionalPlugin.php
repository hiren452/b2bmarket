<?php

namespace Matrix\RegistrationForm\Plugin\Block;

use Ced\RegistrationForm\Block\Additional;
use Ced\RegistrationForm\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as EavCollectionFactory;

class AdditionalPlugin
{

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var EavCollectionFactory
     */
    private $eavCollectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        EavCollectionFactory $eavCollectionFactory
    ) {

        $this->collectionFactory = $collectionFactory;
        $this->eavCollectionFactory = $eavCollectionFactory;
    }
    public function aroundAttrCollection(Additional $subject, callable $proceed)
    {
        $arr = [];
        $model =  $this->collectionFactory->create()->getData();
        foreach ($model as $attributesValues) {
            $arr[] = $attributesValues['attribute_code'];
        }
        $attribute = $this->eavCollectionFactory->create()->addFieldToFilter('entity_type_id', 1);
        $attribute->addFieldToFilter('attribute_code', [
            ['in' => $arr]
        ]);
        return $attribute;
    }
}
