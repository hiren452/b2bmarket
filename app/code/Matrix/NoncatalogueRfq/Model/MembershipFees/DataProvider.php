<?php
namespace Matrix\NoncatalogueRfq\Model\MembershipFees;

use Matrix\NoncatalogueRfq\Model\ResourceModel\MembershipFees\CollectionFactory;

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
        CollectionFactory $rfqmembershipfeesCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $rfqmembershipfeesCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        // echo $this->collection->getSelect();

        $items = $this->collection->getItems();
        $this->loadedData = [];
        foreach ($items as $rfqFees) {
            //$this->loadedData[$item->getId()]['rfqmembershipfees'] = $item->getData();
            $this->loadedData[$rfqFees->getId()] = $rfqFees->getData();

        }
        return $this->loadedData;
    }
}
