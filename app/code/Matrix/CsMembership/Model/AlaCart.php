<?php

namespace Matrix\CsMembership\Model;

class AlaCart extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Matrix\CsMembership\Model\ResourceModel\AlaCart');
    }
}
