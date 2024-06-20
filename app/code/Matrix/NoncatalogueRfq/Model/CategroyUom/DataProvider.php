<?php
namespace Matrix\NoncatalogueRfq\Model\CategroyUom;

use Matrix\NoncatalogueRfq\Model\ResourceModel\CategroyUom\CollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $contactCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $categroyuomCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $categroyuomCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        $this->loadedData = [];
        /** @var Customer $customer */
        foreach ($items as $categroyuom) {
            $this->loadedData[$categroyuom->getId()]['categroyuom'] = $categroyuom->getData();
        }

        return $this->loadedData;
    }
}
