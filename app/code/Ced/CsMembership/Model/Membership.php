<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CsMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Model;

/**
 * Class Membership (for membership model)
 */
class Membership extends \Magento\Framework\Model\AbstractModel
{
    const PRODUCT_EDIT = 'edit';
    const PRODUCT_NEW = 'new';
    const DEFAULT_SORT_BY = 'name';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ced\CsMembership\Model\ResourceModel\Membership');
    }

    /**
     * Get Attribute Used For Sort By Array
     *
     * @return array
     */
    public function getAttributeUsedForSortByArray()
    {
        $options = [
            self::DEFAULT_SORT_BY => __('Name')
        ];
        return $options;
    }
}
