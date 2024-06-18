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

class EditFormTemplate
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
     * Get Template based on module configuration
     *
     * @param  \Magento\Customer\Block\Form\Edit $subject
     * @param  string                            $result
     * @return string
     */
    public function afterGetTemplate(
        \Magento\Customer\Block\Form\Edit $subject,
        $result
    ) {
        return $this->otpHelper->isModuleEnable() ? 'Webkul_Otp::form/edit.phtml' : $result;
    }
}
