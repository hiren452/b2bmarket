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

class Quote extends AbstractModel
{
    const QUOTE_STATUS_PENDING = '0';
    const QUOTE_STATUS_PROCESSING = '1';
    const QUOTE_STATUS_APPROVED = '2';
    const QUOTE_STATUS_CANCELLED = '3';
    const QUOTE_STATUS_PO_CREATED = '4';
    const QUOTE_STATUS_PARTIAL_PO = '5';
    const QUOTE_STATUS_ORDERED = '6';
    const QUOTE_STATUS_COMPLETE = '7';
    const QUOTE_STATUS_PARTIAL_COMPLETE = '8';

    /**
    * Model event prefix
    *
    * @var string
    */
    protected $_eventPrefix = 'ced_rfq_quote';

    /**
    * Define resource model
    */
    protected function _construct()
    {
        $this->_init('Ced\RequestToQuote\Model\ResourceModel\Quote');

    }

    public static function getStatusArray()
    {
        return [
            self::QUOTE_STATUS_PENDING    => __('Pending'),
            self::QUOTE_STATUS_PROCESSING    => __('Processing'),
            self::QUOTE_STATUS_APPROVED    => __('Approved'),
            self::QUOTE_STATUS_CANCELLED    => __('Cancelled'),
            self::QUOTE_STATUS_PO_CREATED    => __('PO Created'),
            self::QUOTE_STATUS_PARTIAL_PO    => __('Partial PO'),
            self::QUOTE_STATUS_ORDERED    => __('Ordered'),
            self::QUOTE_STATUS_COMPLETE   => __('Complete'),
            self::QUOTE_STATUS_PARTIAL_COMPLETE   => __('Partial Complete')
        ];
    }
}
