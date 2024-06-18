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

namespace Webkul\Otp\Model\Customer\Plugin;

use Webkul\Otp\Helper\Customer as CustomerHelper;
use Webkul\Otp\Helper\Data as OtpHelper;

class PhoneNumberToEmailConverter
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
     * Check and converts username from phonenumber into email address
     *
     * @param  \Magento\Customer\Api\AccountManagementInterface $subject
     * @param  string                                           $email
     * @param  string                                           $password
     * @return array
     */
    public function beforeAuthenticate(
        \Magento\Customer\Api\AccountManagementInterface $subject,
        $email,
        $password
    ) {
        if ($this->otpHelper->isModuleEnable()
            && CustomerHelper::USERNAME_EMAIL !== $this->customerHelper->getCurrentUsernameType()
        ) {
            $email = $this->customerHelper->isEmail($email)
            ? $email
            : (
                ($customer = $this->customerHelper->getCustomerDataByPhoneNumber($email, $password)['customer'])
                ? $customer->getEmail()
                : $email
            );
        }

        return [$email, $password];
    }
}
