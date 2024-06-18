<?php
namespace Matrix\NoncatalogRfqfrom\Model\ResourceModel\Attribute;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Matrix\NoncatalogRfqfrom\Model\Attribute', 'Matrix\NoncatalogRfqfrom\Model\ResourceModel\Attribute');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
