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

class QuoteDetail extends AbstractModel
{
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'ced_rfq_quote_item';

    /**
    * Define resource model
    */
    protected function _construct()
    {
        $this->_init('Ced\RequestToQuote\Model\ResourceModel\QuoteDetail');

    }
}
