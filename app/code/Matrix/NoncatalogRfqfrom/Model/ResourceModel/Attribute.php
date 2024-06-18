<?php
namespace Matrix\NoncatalogRfqfrom\Model\ResourceModel;

class Attribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('matrix_noncatalogrfqattrib', 'entity_id');
    }
}
