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

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Message\ManagerInterface;
use Webkul\Otp\Helper\Customer as CustomerHelper;
use Webkul\Otp\Helper\Data as OtpHelper;

class ValidatePhonenumberForgotPassword
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var OtpHelper
     */
    private $otpHelper;

    /**
     * @param Escaper          $escaper
     * @param ManagerInterface $messageManager
     * @param RedirectFactory  $resultRedirectFactory
     * @param CustomerHelper   $customerHelper
     * @param OtpHelper        $otpHelper
     */
    public function __construct(
        Escaper $escaper,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory,
        CustomerHelper $customerHelper,
        OtpHelper $otpHelper
    ) {
        $this->escaper = $escaper;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->customerHelper = $customerHelper;
        $this->otpHelper = $otpHelper;
    }

    /**
     * Plugin for execute()
     *
     * @param  \Magento\Customer\Controller\Account\ForgotPasswordPost $subject
     * @param  \Closure                                                $proceed
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function aroundExecute(
        \Magento\Customer\Controller\Account\ForgotPasswordPost $subject,
        \Closure $proceed
    ) {
        $request = $subject->getRequest();
        $response = $subject->getResponse();
        $resultRedirect = $this->resultRedirectFactory->create();
        $isModuleEnabled = $this->otpHelper->isModuleEnable();
        $isModuleEnabledAtForgotPassword = $this->otpHelper->isModuleEnabledAtForgotPassword();
        $currentUsernameType = $this->customerHelper->getCurrentUsernameType();
        if ($isModuleEnabled && $isModuleEnabledAtForgotPassword && $request->isPost()
            && CustomerHelper::USERNAME_EMAIL !== $currentUsernameType
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
            if (!$this->customerHelper->isEmail($username)) {
                $resultRedirect->setPath('*/*/forgotpassword');
                if ($this->customerHelper->isPhoneNumberFormatValid($username)) {
                    $this->messageManager->addSuccessMessage($this->getSuccessMessage($username));
                } else {
                    $this->messageManager->addErrorMessage(
                        __('Please enter a valid phone number (Ex: +918888888888).')
                    );
                }
                return $resultRedirect;
            }
        }
        return $proceed();
    }

    /**
     * Retrieve success message
     *
     * @param  string $phonenumber
     * @return \Magento\Framework\Phrase
     */
    private function getSuccessMessage($phonenumber)
    {
        return __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $this->escaper->escapeHtml($phonenumber)
        );
    }
}
