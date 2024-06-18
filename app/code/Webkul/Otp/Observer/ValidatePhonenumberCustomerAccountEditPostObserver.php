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

namespace Webkul\Otp\Observer;

use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Webkul\Otp\Helper\Customer as CustomerHelper;
use Webkul\Otp\Helper\Data as OtpHelper;

class ValidatePhonenumberCustomerAccountEditPostObserver implements ObserverInterface
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
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var UrlInterface
     */
    private $urlModel;

    /**
     * @param CustomerHelper          $customerHelper
     * @param OtpHelper               $otpHelper
     * @param ManagerInterface        $messageManager
     * @param RedirectInterface       $redirect
     * @param ActionFlag              $actionFlag
     * @param SessionManagerInterface $session
     * @param UrlInterface            $urlModel
     */
    public function __construct(
        CustomerHelper $customerHelper,
        OtpHelper $otpHelper,
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        ActionFlag $actionFlag,
        SessionManagerInterface $session,
        UrlInterface $urlModel
    ) {
        $this->otpHelper = $otpHelper;
        $this->customerHelper = $customerHelper;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
        $this->session = $session;
        $this->urlModel = $urlModel;
    }

    /**
     * Execute
     *
     * @param  Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if ($this->otpHelper->isModuleEnable()
            && CustomerHelper::USERNAME_EMAIL !== $this->customerHelper->getCurrentUsernameType()
        ) {
            $controller = $observer->getControllerAction();
            $request = $controller->getRequest();
            $callingCode = $request->getPostValue('callingcode');
            $phonenumber = $request->getPostValue('phonenumber');
            $phonenumberWithCallingCode = $callingCode && preg_match('/^\d+$/', $phonenumber)
                ? $this->customerHelper->getTelephoneWithCallingCode('+' . $callingCode, $phonenumber)
                : null;
            $result = $this->customerHelper->validatePhonenumber($phonenumberWithCallingCode);
            if ($result['errors']) {
                $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->session->setCustomerFormData($request->getPostValue());
                $response = $controller->getResponse();
                $defaultUrl = $this->urlModel->getUrl('*/*/edit', ['_secure' => true]);
                $response->setRedirect($this->redirect->error($defaultUrl));
                $this->messageManager->addErrorMessage(
                    isset($result['messages'][CustomerHelper::PHONENUMBER_INVALID_FORMAT])
                        ? __('Please enter a valid phone number (Ex: 88888888888).')
                        : $result['messages'][CustomerHelper::PHONENUMBER_ALREADY_EXISTS]
                );
            }
        }
        return $this;
    }
}
