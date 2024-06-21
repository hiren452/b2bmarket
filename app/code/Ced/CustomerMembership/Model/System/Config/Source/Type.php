<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CUstomerMembership
 * @author         CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CustomerMembership\Model\System\Config\Source;

class Type
{

    /**
     * Retrieve Option values array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return ['fixed' => __('Fixed'),'percentage' => __('Percentage')];
    }
}
