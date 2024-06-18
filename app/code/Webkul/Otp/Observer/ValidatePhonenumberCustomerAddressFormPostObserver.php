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

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Webkul\Otp\Helper\Customer as CustomerHelper;
use Webkul\Otp\Helper\Data as OtpHelper;

class ValidatePhonenumberCustomerAddressFormPostObserver implements ObserverInterface
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
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param CustomerHelper              $customerHelper
     * @param OtpHelper                   $otpHelper
     * @param ManagerInterface            $messageManager
     * @param RedirectInterface           $redirect
     * @param ActionFlag                  $actionFlag
     * @param SessionManagerInterface     $session
     * @param UrlInterface                $urlModel
     * @param AddressRepositoryInterface  $addressRepository
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerHelper $customerHelper,
        OtpHelper $otpHelper,
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        ActionFlag $actionFlag,
        SessionManagerInterface $session,
        UrlInterface $urlModel,
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->otpHelper = $otpHelper;
        $this->customerHelper = $customerHelper;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
        $this->session = $session;
        $this->urlModel = $urlModel;
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Execute
     *
     * @param  Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $controller = $observer->getControllerAction();
        $request = $controller->getRequest();
        if ($this->otpHelper->isModuleEnable() && $request->isPost()) {
            $addressId = $request->getParam('id', null);
            $defaultBilling = $request->getPostValue('default_billing');
            $currentCustomerId = $this->session->getCustomerId();
            try {
                $currentCustomer = $this->customerRepository->getById($currentCustomerId);
                $address = $addressId
                    ? $this->addressRepository->getById($addressId)
                    : (($addressId = $currentCustomer->getDefaultBilling())
                        ? $this->addressRepository->getById($addressId)
                        : null);
                $defaultBilling = $defaultBilling ?: ($address ? $address->isDefaultBilling() : $defaultBilling);
                if (!$defaultBilling || ($address && $currentCustomerId != $address->getCustomerId())) {
                    return $this;
                }
            } catch (\Exception $exception) {
                return $this;
            }
            $telephone = $request->getPostValue('telephone');
            $countryId = $request->getPostValue('country_id');
            $phoneNumber = $telephone
                ? $this->customerHelper->getTelephoneWithCallingCode($countryId, $telephone)
                : null;
            $result = $this->customerHelper->validatePhonenumber($phoneNumber, $currentCustomerId);
            if ($result['errors']) {
                $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->session->setAddressFormData($request->getPostValue());
                $response = $controller->getResponse();
                $defaultUrl = $this->urlModel->getUrl('*/*/edit', ['_secure' => true]);
                $response->setRedirect($this->redirect->error($defaultUrl));
                $this->messageManager->addErrorMessage(
                    $result['messages'][CustomerHelper::PHONENUMBER_INVALID_FORMAT]
                        ?? $result['messages'][CustomerHelper::PHONENUMBER_ALREADY_EXISTS]
                );
            }
        }
        return $this;
    }
}
