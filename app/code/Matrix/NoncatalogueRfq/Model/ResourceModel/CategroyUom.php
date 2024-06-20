<?php
namespace Matrix\NoncatalogueRfq\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CategroyUom extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {

        $this->_init('matrix_category_to_uom', 'id');
    }
}
