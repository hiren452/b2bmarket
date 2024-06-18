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
 * @category    Ced
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RequestToQuote\Model;

use Magento\Framework\Model\AbstractModel;

class Po extends AbstractModel
{
    const PO_STATUS_PENDING = '0';
    const PO_STATUS_CONFIRMED = '1';
    const PO_STATUS_DECLINED = '2';
    const PO_STATUS_ORDERED = '3';

    /**
    * Model event prefix
    *
    * @var string
    */
    protected $_eventPrefix = 'ced_rfq_po';

    /**
    * Define resource model
    */
    protected function _construct()
    {
        $this->_init('Ced\RequestToQuote\Model\ResourceModel\Po');
    }

    public static function getStatusArray()
    {
        return [
            self::PO_STATUS_PENDING    => __('Pending'),
            self::PO_STATUS_CONFIRMED    => __('Confirmed'),
            self::PO_STATUS_DECLINED    => __('Declined'),
            self::PO_STATUS_ORDERED    => __('Ordered')
        ];
    }
}
