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

namespace Webkul\Otp\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\Otp\Api\Data\OtpInterface;
use Webkul\Otp\Api\OtpRepositoryInterface;

class Index extends Action
{
    public const XML_PATH_OTP_EMAIL = 'otp/emailsettings/otp_notification';
    public const XML_PATH_OTP_EMAIL_CHECKOUT = 'otp/emailsettings/otp_checkout_notification';

    /**
     * @var \Webkul\Otp\Helper\FormKey\Validator $formKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJson;

    /**
     * @var OtpRepositoryInterface
     */
    private $otpRepositoryInterface;

    /**
     * @var OtpInterface
     */
    private $otpInterface;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @param \Magento\Framework\Translate\Inline\StateInterface               $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder                $transportBuilder
     * @param \Webkul\Otp\Helper\FormKey\Validator                             $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface               $scopeConfig
     * @param \Magento\Framework\Controller\Result\JsonFactory                 $resultJson
     * @param \Magento\Store\Model\StoreManagerInterface                       $storeManager
     * @param \Magento\Customer\Model\CustomerFactory                          $customerFactory
     * @param \Magento\Customer\Model\Session                                  $customerSession
     * @param \Webkul\Otp\Helper\Data                                          $helper
     * @param OtpRepositoryInterface                                           $otpRepositoryInterface
     * @param OtpInterface                                                     $otpInterface
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                      $date
     * @param \Magento\Framework\Json\Helper\Data                              $jsonHelper
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param Context                                                          $context
     */
    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface$inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder$transportBuilder,
        \Webkul\Otp\Helper\FormKey\Validator$formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface$scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory$resultJson,
        \Magento\Store\Model\StoreManagerInterface$storeManager,
        \Magento\Customer\Model\CustomerFactory$customerFactory,
        \Magento\Customer\Model\Session$customerSession,
        \Webkul\Otp\Helper\Data$helper,
        OtpRepositoryInterface $otpRepositoryInterface,
        OtpInterface $otpInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime$date,
        \Magento\Framework\Json\Helper\Data$jsonHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory$customerCollectionFactory,
        Context $context
    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->formKeyValidator = $formKeyValidator;
        $this->scopeConfig = $scopeConfig;
        $this->resultJson = $resultJson;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->otpRepositoryInterface = $otpRepositoryInterface;
        $this->otpInterface = $otpInterface;
        $this->date = $date;
        $this->jsonHelper = $jsonHelper;
        $this->customerCollectionFactory = $customerCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Function execute for Controller
     *
     * @return Json result
     */
    public function execute()
    {
        if ($this->formKeyValidator->validate($this->getRequest()) && $this->helper->isModuleEnable()) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $requestData = $this->getRequest()->getParams()
            ?: $this->jsonHelper->jsonDecode($this->getRequest()->getContent());

            $name = $requestData['name'] ?? null;
            $email = $requestData['email'] ?? null;
            $resend = $requestData['resend'] ?? null;
            $mobile = $requestData['mobile'] ?? null;
            $regionId = $requestData['region'] ?? null;
            $shouldCheckExistingAccount = isset($requestData['shouldCheckExistingAccount'])
            ? (int) $requestData['shouldCheckExistingAccount'] : 1;
            if (!$this->isCheckout() && $shouldCheckExistingAccount) {
                $accountExists = $this->checkExistingAccount($email);
                if ($accountExists) {
                    $result = [
                        'error' => true,
                        'message' => __("An account already exits with this email."),
                        'errorCode' => "account_exist",
                    ];
                    return $this->resultJson->create()->setData($result);
                }

                $checkIfPhoneNumberExist = $this->customerCollectionFactory->create()
                    ->addAttributeToFilter('default_phone_number', ['eq' => $mobile]);

                if (!empty($checkIfPhoneNumberExist->getData())) {
                    $result = [
                        'error' => true,
                        'message' => __("Phone number already exist"),

                    ];
                    return $this->resultJson->create()->setData($result);
                }
            }
            $password = rand(100000, 999999);
            $collection = $this->otpRepositoryInterface->getByEmail($email);
            $date = $this->date->gmtDate();
            if ($mobile!=null) {
                $mobile = str_replace(" ", "", $mobile);
            }
            $callingCode = empty($regionId)
            ? ''
            : ('+' . $this->helper->getCallingCodeByCountryCode($regionId));
            $mobile = !empty($mobile) && !empty($callingCode) &&
            substr($mobile, 0, 1) !== '+'
            ? $callingCode . $mobile
            : $mobile;
            if (!$this->customerSession->getCustomer()->getGroupId()) {
                if (empty($email)) {
                    $email = $this->customerSession->getCustomer()->getEmail();
                }
                if (empty($mobile)) {
                    $regionId = $this->customerSession->getCustomer()->getPrimaryBillingAddress()->getCountryId();
                    $callingCode = '+' . $this->helper->getCallingCodeByCountryCode($regionId);
                    $mobile = $callingCode . $this->customerSession
                        ->getCustomer()->getPrimaryBillingAddress()
                        ->getTelephone();
                }
            }
            if (is_array($collection->getData())) {
                $collection->setEmail($email);
                $collection->setOtp($password);
                $collection->setCreatedAt($date);
                $collection->save($collection);
            } else {
                $this->otpInterface->setEmail($email);
                $this->otpInterface->setOtp($password);
                $this->otpRepositoryInterface->save($this->otpInterface);
            }

            $isMobileOtpEnabled = $this->helper->getOtpEnabledConfigMessage();
            $isTwilioSendOtpEmailEnabled = $this->helper->getTwilioSendOtpEmailEnabled();
            $sendOtpVia = $this->helper->sendOtpVia();

            if ($sendOtpVia == 'mobile' && $isMobileOtpEnabled) {
                $response = $this->sendOTPToPhone($mobile, $password);
                $otpMedium = 'Mobile Number';
            } elseif ($sendOtpVia == 'email' && $isMobileOtpEnabled) {
                $response = $this->sendOTPToEmail($email, $name, $password);
                $otpMedium = 'Email ID';
            } elseif ($sendOtpVia == 'both' && $isMobileOtpEnabled) {
                if (!empty($mobile)) {
                    $response = $this->sendOTPToPhone($mobile, $password);
                }
                if (!empty($email)) {
                    $response = $this->sendOTPToEmail($email, $name, $password);
                }

                if (!empty($mobile) && !empty($email)) {
                    $otpMedium = 'Mobile Number & Email ID';
                } elseif (empty($mobile)) {
                    $otpMedium = 'Email ID & Mobile number not updated on account.';
                } elseif (!empty($email) && !empty($email)) {
                    $otpMedium = 'Mobile number & Email Id not updated on account.';
                }
            }
            if (!$isMobileOtpEnabled) {

                $response = $this->sendOTPToEmail($email, $name, $password);
                $otpMedium = 'Email ID';
            }

            if ($response['error']) {
                $errorMessage = $response['message'] ?? "";

                if ($otpMedium == "Email ID") {
                    if ($errorMessage == 'Unable to send mail') {
                        $result = ['error' => true, 'message' => __("Unable to send mail"), 'errorCode' => "exception"];
                        return $this->resultJson->create()->setData($result);
                    }
                }

                if ($otpMedium == "Mobile Number") {
                    if (str_contains($errorMessage, 'Authentication Error')) {
                        $result = ['error' => true, 'message' => __("Twilio Authentication Failed"), 'errorCode' => "exception"];
                        return $this->resultJson->create()->setData($result);
                    }
                    if (str_contains($errorMessage, "SMS-capable inbound phone number")) {
                        $result = ['error' => true, 'message' => __("Please verify your number from the twilio console."), 'errorCode' => "exception"];
                        return $this->resultJson->create()->setData($result);
                    }
                }
                if (strpos($errorMessage, 'Unable to send mail') === false) {

                    $result = ['otpMedium'=>$otpMedium,
                    'rerrorMessage'=>$response['message'],
                    'error' => true,
                    'message' =>__("Unable to send mail or verify your twilio sender number"),
                    'errorCode' => "exception"];
                    return $this->resultJson->create()->setData($result);
                } elseif (strpos($errorMessage, 'unverified numbers') === false) {
                    $result = ['error' => true, 'message' => __(
                        "The phone number %1 is not verified.
                    Please verify your number from the twilio console.",
                        $mobile
                    ), 'errorCode' => "exception"];
                    return $this->resultJson->create()->setData($result);
                } else {

                    $result = ['error' => true, 'message' => __(
                        "Unable to send OTP.
                    Please try again later."
                    ), 'errorCode' => "exception"];
                    return $this->resultJson->create()->setData($result);
                }
            } else {
                $successMessage = $resend
                ? __("A new OTP has been sent to your registered %1. Please enter the OTP.", $otpMedium)
                : __("Please Enter the OTP sent to your registered %1", $otpMedium);
                $result = ['error' => false, 'message' => $successMessage];
                return $this->resultJson->create()->setData($result);
            }
        } else {
            $this->messageManager->addError(__("Something Went Wrong."));
            $result = ['error' => true, 'message' => __("Something Went Wrong."), 'errorCode' => "exception"];
            return $this->resultJson->create()->setData($result);
        }
    }

    /**
     * Function to send One time password on email
     *
     * @param  string  $email
     * @param  string  $name
     * @param  integer $password
     * @return array
     */
    private function sendOTPToEmail($email, $name, $password)
    {
        $emailTempVariables = [];
        $senderInfo = [];
        $receiverInfo = [];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
        $senderName = $this->scopeConfig->getValue('trans_email/ident_support/name', $storeScope);
        $emailTempVariables['password'] = $password;
        $senderInfo['email'] = $senderEmail;
        $senderInfo['name'] = $senderName;
        $receiverInfo['email'] = $email;

        if (!empty($name)) {
            $receiverInfo['name'] = $name;
            $emailTempVariables['name'] = $name;
        } else {
            $receiverInfo['name'] = "Buyer";
            $emailTempVariables['name'] = "Buyer";
        }
        $emailTempVariables['time_to_expire'] = $this->helper->getOtpTimeToExpireString();
        try {
            if ($this->isCheckout()) {
                $this->template = $this->getTemplateId(self::XML_PATH_OTP_EMAIL_CHECKOUT);
            } else {
                $this->template = $this->getTemplateId(self::XML_PATH_OTP_EMAIL);
            }
            $this->inlineTranslation->suspend();
            $this->generateTemplate($emailTempVariables, $senderInfo, $receiverInfo);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            $result = ['error' => false, 'message' => 'Successfully sent'];
        } catch (\Exception$e) {
            $message = $e->getMessage();
            $message = substr($message, (strpos($message, ":") ?: -2) + 2);
            $result = ['error' => true, 'message' => "Unable to send mail"];
        }

        return $result;
    }

    /**
     * Function to send One time password on Mobile
     *
     * @param  string  $mobile
     * @param  integer $password
     * @return array
     */
    private function sendOTPToPhone($mobile, $password)
    {
        try {
            $reciever = $mobile;
            $message = $this->helper->getTwilioConfigMessage();
            if (!empty($message)) {
                $message = str_replace('{otp}', $password, $message);
                $client = $this->helper->makeTwilloClient();
                $result = $this->helper->sendMessage($client, $reciever, $message);
            }
        } catch (\Exception$e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
        }

        return $result;
    }

    /**
     * Function to check for checkout process
     *
     * @return bool
     */
    private function isCheckout()
    {
        return $this->getRequest()->getParam('checkout');
    }

    /**
     * [generateTemplate description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    private function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->transportBuilder
            ->setTemplateIdentifier($this->template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }

    /**
     * Return template id.
     *
     * @param string $xmlPath
     *
     * @return mixed
     */
    private function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    private function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Function to check if an already exists with email provided by customer
     *
     * @param string $email email
     *
     * @return bool
     */
    private function checkExistingAccount($email)
    {
        $accountExists = $this->customerFactory->create()->getCollection()
            ->addFieldToFilter('email', ['eq' => $email])
            ->getSize();
        if ($accountExists > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return store.
     *
     * @return object
     */
    private function getStore()
    {
        return $this->storeManager->getStore();
    }
}
