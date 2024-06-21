<?php

namespace Ced\CustomerMembership\Model\System\Config\Source;

class Months implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' =>  '30', 'label' => __('1')],
            ['value' => '60', 'label' => __('2')],
            ['value' => '90', 'label' => __('3')],
            ['value' =>  '120', 'label' => __('4')],
            ['value' => '150', 'label' => __('5')],
            ['value' =>  '180', 'label' => __('6')],
            ['value' =>  '210', 'label' => __('7')],
            ['value' => '240', 'label' => __('8')],
            ['value' => '270', 'label' => __('9')],
            ['value' =>  '300', 'label' => __('10')],
            ['value' => '330', 'label' => __('11')],
            ['value' => '360', 'label' => __('12')],
        ];
    }
}
