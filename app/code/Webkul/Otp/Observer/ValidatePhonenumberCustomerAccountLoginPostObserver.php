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

class ValidatePhonenumberCustomerAccountLoginPostObserver implements ObserverInterface
{
    /**
     * @var SessionManagerInterface;
     */
    private $session;

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
     * @var UrlInterface
     */
    private $urlModel;

    /**
     * @param SessionManagerInterface $session
     * @param CustomerHelper          $customerHelper
     * @param OtpHelper               $otpHelper
     * @param ManagerInterface        $messageManager
     * @param RedirectInterface       $redirect
     * @param ActionFlag              $actionFlag
     * @param UrlInterface            $urlModel
     */
    public function __construct(
        SessionManagerInterface $session,
        CustomerHelper $customerHelper,
        OtpHelper $otpHelper,
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        ActionFlag $actionFlag,
        UrlInterface $urlModel
    ) {
        $this->session = $session;
        $this->otpHelper = $otpHelper;
        $this->customerHelper = $customerHelper;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
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
            $loginParams = $request->getPostValue('login');
            $login = is_array($loginParams) && array_key_exists('username', $loginParams)
                ? $loginParams['username']
                : null;
            if ($this->customerHelper->isEmail($login)) {
                return $this;
            }
            $result = $this->customerHelper->validatePhonenumber($login);
            if (($result['errors'] && isset($result['messages'][CustomerHelper::PHONENUMBER_INVALID_FORMAT]))
                || !$result['errors']
            ) {
                $this->session->setUsername($login);
                $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $response = $controller->getResponse();
                $defaultUrl = $this->urlModel->getUrl('*/*/login', ['_secure' => true]);
                $response->setRedirect($this->redirect->error($defaultUrl));
                $this->messageManager->addErrorMessage(
                    $result['messages'][CustomerHelper::PHONENUMBER_INVALID_FORMAT] ??
                    __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    )
                );
            }
        }
        return $this;
    }
}
