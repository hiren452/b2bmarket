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
 * @package     Ced_CsMembership
 * @author         CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Model;

/**
 * Class StatusOption (for membership options)
 */
class StatusOption implements \Magento\Framework\Data\OptionSourceInterface
{
    const STATUS_ENABLED    = 1;
    const STATUS_DISABLED   = 0;
    const STATUS_RUNNING    = 'running';
    const STATUS_EXPIRED    = 'expired';

    /**
     * Get option array
     *
     * @return array[]
     */
    public static function getOptionArray()
    {
        $oprions = [
            ['value' => self::STATUS_ENABLED, 'label' => __('Yes')],
            ['value' => self::STATUS_DISABLED, 'label' => __('No')]
        ];
        return $oprions;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return $this->getOptionArray();
    }
}
