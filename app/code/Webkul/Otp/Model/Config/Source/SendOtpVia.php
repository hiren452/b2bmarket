<?php
namespace Webkul\Otp\Model\Config\Source;

class SendOtpVia implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * ToOptionArray()
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'mobile', 'label' => __('Mobile')],
            ['value' => 'email', 'label' => __('Email')],
            ['value' => 'both', 'label' => __('Both')]
        ];
    }
}
