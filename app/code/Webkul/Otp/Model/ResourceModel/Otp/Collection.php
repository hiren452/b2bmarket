<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    webkul <info@Webkul.com>
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Otp\Model\ResourceModel\Otp;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Otp Model ResoucrceModel Collection Class
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Webkul\Otp\Model\Otp::class,
            \Webkul\Otp\Model\ResourceModel\Otp::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}
