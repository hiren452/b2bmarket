<?php

namespace Matrix\CustomerMembership\Plugin\Model\System\Config\Source;

class Months
{
    public function afterToOptionArray(
        \Ced\CustomerMembership\Model\System\Config\Source\Months $subject,
        $result
    ) {
        $result[] = ['value' => '7300', 'label' => __('240')];
        return $result;
    }
}
