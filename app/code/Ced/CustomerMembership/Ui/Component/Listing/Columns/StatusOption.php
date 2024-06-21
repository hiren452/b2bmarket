<?php
namespace Ced\CustomerMembership\Ui\Component\Listing\Columns;

class StatusOption implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Enabled')], ['value' => 0, 'label' => __('Disabled')]];
    }
}
