<?php
namespace Matrix\NoncatalogueRfq\Model;

use Magento\Framework\Model\AbstractModel;

class CategroyUom extends AbstractModel
{
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'matrix_category_to_uom';

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Matrix\NoncatalogueRfq\Model\ResourceModel\CategroyUom');
    }
}
