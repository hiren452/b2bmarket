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
 * @package     Ced_CsRfq
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Model\Config\Source;

class Condition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'all',
                'label' => __('For All Vendor')
            ],
            [
                'value' => 'specific',
                'label' => __('Let Vendor Choose Themselves')
            ]
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'all' => __('For All Vendor'),
            'specific'=> __('Let Vendor Choose Themselves')
        ];
    }
}
