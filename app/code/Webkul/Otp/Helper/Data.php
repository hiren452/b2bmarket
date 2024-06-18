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

namespace Webkul\Otp\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\CustomerAddressDataProvider;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Url;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Model\Config;
use Twilio\Rest\Client;

/**
 * Otp data helper
 */
class Data extends AbstractHelper
{
    public const XML_PATH_MODULE_ENABLE = 'otp/enable/otp_enable';
    public const XML_PATH_ONESTEPCHECKOUT_ENABLE = 'opc/general_settings/active';
    public const XML_PATH_MODULE_ENABLE_REGISTRATION = 'otp/enable/otp_enable_registration';
    public const XML_PATH_MODULE_ENABLE_LOGIN = 'otp/enable/otp_enable_login';
    public const XML_PATH_REGISTRATION_EMAIL = 'otp/emailsettings/otp_notification';
    public const XML_PATH_MODULE_ENABLE_CHECKOUT = 'otp/enable/otp_enable_checkout';
    public const XML_PATH_ALLOWED_PAYMENT_METHODS = 'otp/enable/allowed_payment_methods';
    public const XML_PATH_CHECKOUT_EMAIL = 'otp/emailsettings/otp_checkout_notification';
    public const XML_PATH_MODULE_ENABLED_FORGOT_PASSWORD = 'otp/enable/forgot_password';
    public const TWILLO_AUTH_ID = 'otp/twiliosettings/authId';
    public const TWILLO_TOKEN = 'otp/twiliosettings/token';
    public const TWILIO_OTP_MESSAGE = 'otp/twiliosettings/message';
    public const TWILIO_ENABLED = 'otp/twiliosettings/twillo_auth_enabled';
    public const TWILIO_SEND_OTP_EMAIL_ENABLED = 'otp/twiliosettings/send_otp_email_enabled';
    public const SENDER_NUMBER = 'otp/twiliosettings/number';
    public const OTP_EXPIRY = 'otp/enable/expiry';
    public const SEND_OTP_VIA = 'otp/twiliosettings/send_otp_via';
    public const MARKETPLACE_LANDING_PAGE = 'marketplace/landingpage_settings/pageLayout';

    /**
     * @var \Webkul\BookingSmsNotification\Encryption\EncryptorInterface
     */
    private $enc;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentHelper;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Model\Address\CustomerAddressDataProvider
     */
    private $customerAddressData;

    /**
     * @var \Magento\Framework\Url
     */
    private $urlModel;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * Countries cache;
     *
     * @var array
     */
    private $countries;

    /**
     * @param \Magento\Payment\Helper\Data                       $paymentHelper
     * @param \Magento\Framework\Encryption\EncryptorInterface   $enc
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem\Driver\Http          $driver
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param Config                                             $paymentModelConfig
     * @param CustomerRepositoryInterface                        $customerRepository
     * @param CustomerAddressDataProvider                        $customerAddressData
     * @param Url                                                $urlModel
     * @param AssetRepository                                    $assetRepository
     * @param JsonHelper                                         $jsonHelper
     * @param \Magento\Framework\Module\Manager                  $moduleManager
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\Encryption\EncryptorInterface $enc,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem\Driver\Http $driver,
        \Magento\Customer\Model\Session $customerSession,
        Config $paymentModelConfig,
        CustomerRepositoryInterface $customerRepository,
        CustomerAddressDataProvider $customerAddressData,
        Url $urlModel,
        AssetRepository $assetRepository,
        JsonHelper $jsonHelper,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->enc = $enc;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->driver = $driver;
        $this->customerSession = $customerSession;
        $this->_paymentModelConfig = $paymentModelConfig;
        $this->customerRepository = $customerRepository;
        $this->customerAddressData = $customerAddressData;
        $this->urlModel = $urlModel;
        $this->assetRepository = $assetRepository;
        $this->jsonHelper = $jsonHelper;
        $this->_moduleManager = $moduleManager;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    /**
     * Return store configuration value.
     *
     * @param  string $path
     * @param  int    $storeId
     * @return mixed
     */
    public function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * GetPaymentMethods
     *
     * @param  none
     * @return array
     */
    public function getPaymentMethods()
    {
        return $this->paymentHelper->getPaymentMethods();
    }

    /**
     * Return template id.
     *
     * @param  string $xmlPath
     * @return string
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * Function to return status of Module
     *
     * @return bool
     */
    public function isModuleEnable()
    {
        return $this->getConfigValue(self::XML_PATH_MODULE_ENABLE, $this->getStore()->getStoreId());
    }

    /**
     * Function to return status of Module
     *
     * @return bool
     */
    public function isOneStepCheckoutEnable()
    {
        return $this->getConfigValue(self::XML_PATH_ONESTEPCHECKOUT_ENABLE, $this->getStore()->getStoreId());
    }

    /**
     * Function to return status of otp validation at Registration
     *
     * @return bool
     */
    public function isEnableAtRegistration()
    {
        return $this->getConfigValue(self::XML_PATH_MODULE_ENABLE_REGISTRATION, $this->getStore()->getStoreId());
    }

    /**
     * Function to return status of otp validation at Login
     *
     * @return bool
     */
    public function isEnableAtLogin()
    {
        return $this->getConfigValue(self::XML_PATH_MODULE_ENABLE_LOGIN, $this->getStore()->getStoreId());
    }

    /**
     * Function to return status of otp validation at checkout
     *
     * @return bool
     */
    public function isEnableAtCheckout()
    {
        return $this->getConfigValue(self::XML_PATH_MODULE_ENABLE_CHECKOUT, $this->getStore()->getStoreId());
    }

    /**
     * Function to return status of otp validation at forgot password
     *
     * @return bool
     */
    public function isModuleEnabledAtForgotPassword()
    {
        return $this->getConfigValue(self::XML_PATH_MODULE_ENABLED_FORGOT_PASSWORD, $this->getStore()->getStoreId());
    }

    /**
     * Function to get allowed payment methods from Configuration
     *
     * @return string
     */
    public function getAllowedPaymentMethods()
    {
        return $this->getConfigValue(self::XML_PATH_ALLOWED_PAYMENT_METHODS, $this->getStore()->getStoreId());
    }

    /**
     * Return store.
     *
     * @return object
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Get countries with calling codes and name
     *
     * @param  boolean $refresh
     * @return array
     */
    public function getCountries($refresh = false): array
    {
        $logger = (new \Zend_Log())->addWriter(new \Zend_Log_Writer_Stream(BP . '/var/log/data' . date('my') . '.log'));

        if (empty($this->countries) || $refresh) {
            try {
                $url = 'restcountries.com/v2/all?fields=name,callingCodes';
                $countriesData = $this->driver->fileGetContents($url);
                $countriesData = $this->jsonHelper->jsonDecode($countriesData);
                $logger->info("countriesData1 : " . json_encode($countriesData));
                $logger->info("countries1 : " . json_encode($this->countries));
            } catch (\Exception $exception) {
                $countriesData = [];
            }
            $countries = array_map(
                function ($country) {
                    return [
                        'name' => $country['name'],
                        'callingCode' => str_replace(" ", "", $country['callingCodes'][0]),
                    ];
                },
                $countriesData
            );
            $logger->info("countriesarraymap : " . json_encode($countries));
            $logger->info("countries2 : " . json_encode($this->countries));
            $this->countries = $countries
            ? array_filter(
                $countries,
                function ($country) {
                    return $country['callingCode'] !== "";
                }
            )
            : $this->countries;
        }
        $logger->info("countries3 : " . json_encode($this->countries));
        return $this->countries;
    }

    /**
     * Get calling code by country code
     *
     * @param  string $countryCode
     * @return string
     */
    public function getCallingCodeByCountryCode($countryCode): string
    {
        try {
            $url = "restcountries.com/v2/alpha/$countryCode?fields=callingCodes";
            $callingCodeJson = $this->driver->fileGetContents($url);
            $callingCodeArray = $this->jsonHelper->jsonDecode($callingCodeJson);
        } catch (\Exception $exception) {
            $callingCodeArray = "";
        }
        return isset($callingCodeArray['callingCodes']) ? $callingCodeArray['callingCodes'][0] : "";
    }

    /**
     * Make Twilio Client
     *
     * @return array|Twilio\Rest\Client
     */
    public function makeTwilloClient()
    {
        try {
            $sid = $this->scopeConfig->getValue(self::TWILLO_AUTH_ID, $this->storeScope);
            $token = $this->scopeConfig->getValue(self::TWILLO_TOKEN, $this->storeScope);
            $client = new Client($this->enc->decrypt($sid), $this->enc->decrypt($token));
            return $client;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $message = substr($message, (strpos($message, ":") ?: -2) + 2);
            $result = ['error' => true, 'message' => $message];
            return $result;
        }
    }

    /**
     * Send notification message
     *
     * @param  object $client
     * @param  string $reciver
     * @param  string $message
     * @return array
     */
    public function sendMessage($client, $reciver, $message = null)
    {
        if (empty($message)) {
            $message = $this->scopeConfig->getValue(self::TWILLO_MESSAGE, $this->storeScope);
        }
        try {
            $messageInstance = $client->messages->create(
                $reciver,
                ['from' => $this->scopeConfig->getValue(self::SENDER_NUMBER, $this->storeScope), 'body' => $message]
            );
            if (!empty($messageInstance->errorCode) || !empty($messageInstance->errorMessage)
                || (!empty($messageInstance->status) && in_array($messageInstance->status, ['failed', 'undeliveried']))
            ) {
                return ['error' => true, 'message' => $messageInstance->errorMessage];
            }

            return [
                'error' => false,
                'message' => !empty($messageInstance->status)
                ? ("Status: " . $messageInstance->status)
                : "Message has been sent successfully." .
                "But Unable to verify status with Twilio Server",
            ];
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $message = substr($message, (strpos($message, ":") ?: -2) + 2);
            $result = ['error' => true, 'message' => $message];
            return $result;
        }
    }

    /**
     * Otp Config Message
     *
     * @return string
     */
    public function getTwilioConfigMessage()
    {
        return $this->scopeConfig->getValue(self::TWILIO_OTP_MESSAGE, $this->storeScope);
    }

    /**
     * Enable Otp config
     *
     * @return bool
     */
    public function getOtpEnabledConfigMessage()
    {
        return (bool) $this->scopeConfig->getValue(
            self::TWILIO_ENABLED,
            $this->storeScope
        );
    }

    /**
     * Get send_opt_email_enabled config parameter
     *
     * @return bool
     */
    public function getTwilioSendOtpEmailEnabled()
    {
        return (bool) $this->scopeConfig->getValue(
            self::TWILIO_SEND_OTP_EMAIL_ENABLED,
            $this->storeScope
        );
    }

    /**
     * Get active payment methods
     *
     * @return array
     */
    public function getActivePaymentMethods()
    {
        $payments = $this->_paymentModelConfig->getActiveMethods();
        $methods = [];

        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->scopeConfig
                ->getValue('payment/' . $paymentCode . '/title');
            $methods[$paymentCode] = [
                'label' => $paymentTitle,
                'value' => $paymentCode,
            ];
        }
        return $methods;
    }

    /**
     * Function to get otp expiry time.
     *
     * @return int
     */
    public function otpExpiry()
    {
        return $this->getConfigValue(
            self::OTP_EXPIRY,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * Function to determine guest user
     *
     * @return boolean
     */
    public function isGuestCheckout()
    {
        return !$this->customerSession->isLoggedIn();
    }

    /**
     * Function to get OTP modal configuration
     *
     * @return array
     */
    public function getOtpModalConfig(): array
    {
        $otpAction = $this->urlModel->getUrl('otp');
        $otpValidateAction = $this->urlModel->getUrl('otp/index/validate');
        $otpTimeToExpireString = $this->getOtpTimeToExpireString();
        $otpModalConfig = [
            'isModuleEnabled' => $this->isModuleEnable(),
            'resendText' => __("Resend OTP"),
            'validateNumberError' => __("Please enter a valid number."),
            'otpAction' => $otpAction,
            'otpValidateAction' => $otpValidateAction,
            'submitButtonText' => __("Submit"),
            'otpTimeToExpireString' => __($otpTimeToExpireString),
            'isLoggedIn' => (!$this->isGuestCheckout()),
            'isMobileOtpEnabled' => $this->getOtpEnabledConfigMessage(),
            'isSendOtpEmailEnabled' => $this->getTwilioSendOtpEmailEnabled(),
            'loaderUrl' => $this->assetRepository->createAsset('Webkul_Otp::images/ajax-loader.gif')->getUrl(),
            'customerData' => $this->getCustomerData(),
            'otpTimeToExpireMessage' => __("Your OTP will expire in %1", $otpTimeToExpireString),
            'otpInputPlaceholder' => __('Enter the OTP here'),
            'telephoneInputPlaceholder' => __('Telephone number with country code'),
            'modalTitle' => __('OTP Verification'),
            'validateCustomerCredentialsUrl' => $this->urlModel->getUrl('otp/customer/validatecustomercredentials'),
            'validateCustomerOtpUrl' => $this->urlModel->getUrl('otp/customer/validatecustomerotp'),
        ];

        return $otpModalConfig;
    }

    /**
     * Get relative path of checkout configuration route
     *
     * @return string
     */
    public function getCheckoutConfigurationUrl()
    {
        return $this->urlModel->getUrl('otp/checkout/configdata');
    }

    /**
     * Returns a formatted string representation of OPT expiry time
     *
     * @return string
     */
    public function getOtpTimeToExpireString(): string
    {
        $timeToExpireInSeconds = $this->otpExpiry();
        $timeToExpireInSeconds = $timeToExpireInSeconds < 60 || $timeToExpireInSeconds > 300
        ? 60 : $timeToExpireInSeconds;
        $timeToExpireMinutes = floor(($timeToExpireInSeconds / 60));
        $timeToExpireSeconds = $timeToExpireInSeconds % 60;
        $timeToExpireMinutesString = $timeToExpireMinutes > 0
        ? "$timeToExpireMinutes minute" . ($timeToExpireMinutes > 1 ? 's' : '')
        : '';
        $timeToExpireSecondsString = $timeToExpireSeconds > 0
        ? "$timeToExpireSeconds second" . ($timeToExpireSeconds > 1 ? 's' : '')
        : '';
        $timeToExpireString = join(
            " and ",
            array_filter(
                [$timeToExpireMinutesString, $timeToExpireSecondsString],
                function ($value) {
                    return !empty($value);
                }
            )
        );

        return $timeToExpireString;
    }

    /**
     * Retrieve customer data
     *
     * @param  int $id
     * @return array
     */
    public function getCustomerData($id = null): array
    {
        // @TODO: Move this to Customer helper
        $customerData = [];
        if (empty($id)) {
            $id = $this->customerSession->getCustomerId();
        }
        if (empty($id)) {
            return [];
        }
        /**
 * @var \Magento\Customer\Api\Data\CustomerInterface $customer
*/
        $customer = $this->customerRepository->getById($id);
        $customerData = $customer->__toArray();
        $customerData['addresses'] = $this->customerAddressData->getAddressDataByCustomer($customer);

        return $customerData;
    }

    /**
     * Send the medium of OTP
     *
     * @return string
     */
    public function sendOtpVia()
    {
        return $this->getConfigValue(self::SEND_OTP_VIA, $this->getStore()->getStoreId());
    }

    /**
     * Send the marketplace module enable
     *
     * @return boolean
     */
    public function isMarketplaceModuleEnable()
    {
        return $this->_moduleManager->isEnabled('Webkul_Marketplace');
    }

    /**
     * GetMarketplacePageLayout
     *
     * @return int
     */
    public function getMarketplacePageLayout()
    {
        if ($this->isMarketplaceModuleEnable()) {
            return $this->getConfigValue(self::MARKETPLACE_LANDING_PAGE, $this->getStore()->getStoreId());
        }
    }
}
