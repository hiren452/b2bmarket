<?php
namespace Matrix\NoncatalogueRfq\Model;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    public function getData()
    {
        return [];
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        return;
    }
}
