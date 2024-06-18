<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Otp\Plugin;

use Webkul\Otp\Helper\Customer as CustomerHelper;
use Webkul\Otp\Helper\Data as OtpHelper;

class CheckoutProcessor
{
    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var OtpHelper
     */
    private $otpHelper;

    /**
     * @param CustomerHelper $customerHelper
     * @param OtpHelper      $otpHelper
     */
    public function __construct(
        CustomerHelper $customerHelper,
        OtpHelper $otpHelper
    ) {
        $this->customerHelper = $customerHelper;
        $this->otpHelper = $otpHelper;
    }

    /**
     * Checkout LayoutProcessor after process plugin.
     *
     * @param  \Magento\Checkout\Block\Checkout\LayoutProcessor $processor
     * @param  array                                            $jsLayout
     * @return array
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $processor, $jsLayout)
    {
        if ($this->otpHelper->isModuleEnable()) {
            $otpModalConfig = $this->otpHelper->getOtpModalConfig();
            $usernameType = $this->customerHelper->getCurrentUsernameType();
            $loginConfig['usernameFieldConfig']
                = $this->customerHelper->getLoginUsernameFieldConfigByType($usernameType);
            $loginConfig['otpModalComponent'] = ['Webkul_Otp/js/login' => $otpModalConfig];
            $shippingConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress'];
            $shippingConfig['children']['customer-email']['component']
                    = 'Webkul_Otp/js/view/form/element/email-phonenumber';
            $shippingConfig['children']['customer-email']['config'] = $loginConfig;
        }
        return $jsLayout;
    }
}
