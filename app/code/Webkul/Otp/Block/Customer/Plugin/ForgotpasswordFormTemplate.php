<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Otp\Block\Customer\Plugin;

use Webkul\Otp\Helper\Data as OtpHelper;

class ForgotpasswordFormTemplate
{
    /**
     * @var OtpHelper
     */
    private $otpHelper;

    /**
     * @param OtpHelper $otpHelper
     */
    public function __construct(OtpHelper $otpHelper)
    {
        $this->otpHelper = $otpHelper;
    }

    /**
     * Set template based on module configuration
     *
     * @param  \Magento\Customer\Block\Account\Forgotpassword $subject
     * @param  string                                         $result
     * @return string
     */
    public function afterGetTemplate(
        \Magento\Customer\Block\Account\Forgotpassword $subject,
        $result
    ) {
        return $this->otpHelper->isModuleEnable() && $this->otpHelper->isModuleEnabledAtForgotPassword()
            ? 'Webkul_Otp::form/forgotpassword.phtml' : $result;
    }
}
