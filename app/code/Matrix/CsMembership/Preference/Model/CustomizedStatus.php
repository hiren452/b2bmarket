<?php

namespace Matrix\CsMembership\Preference\Model;

use Ced\CsMembership\Model\Status;

class CustomizedStatus extends Status
{
    const STATUS_ENABLED    = 1;
    const STATUS_DISABLED   = 0;
    const STATUS_RUNNING    = 'running';
    const STATUS_EXPIRED    = 'expired';
    /**
     * getOptionArray
     * return array
     */
    public static function getOptionArray()
    {
        return [
            self::STATUS_ENABLED    => __('Yes'),
            self::STATUS_DISABLED   => __('No')
        ];
    }
}
