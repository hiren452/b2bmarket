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

namespace Webkul\Otp\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Otp\Helper\Customer as CustomerHelper;
use Webkul\Otp\Helper\Data as OtpHelper;

class ConvertPhonenumberToEmailCustomerAccountForgotpasswordPostObserver implements ObserverInterface
{
    /**
     * @var CustomerHelper $customerHelper
     */
    private $customerHelper;

    /**
     * @var OtpHelper $otpHelper
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
     * Converts Phone number into email form forgot password
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $currentUsernameType = $this->customerHelper->getCurrentUsernameType();
        $controller = $observer->getControllerAction();
        $request = $controller->getRequest();
        if ($this->otpHelper->isModuleEnable() && $this->otpHelper->isModuleEnabledAtForgotPassword()
            && $request->isPost() && CustomerHelper::USERNAME_EMAIL !== $currentUsernameType
        ) {
            $username = $request->getPostValue('email');
            $password = $request->getPostValue('password') ?? '';
            $username = $this->customerHelper->isEmail($username)
                ? $username
                : (
                    ($customer = $this->customerHelper->getCustomerDataByPhoneNumber($username, $password)['customer'])
                        ? $customer->getEmail()
                        : $username
                );
            if ($this->customerHelper->isEmail($username)) {
                $request->setPostValue('email', $username);
            }
        }
        return $this;
    }
}
