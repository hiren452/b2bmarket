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

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Encryption\EncryptorInterface as CustomerPasswordEncryptor;
use Webkul\Otp\Helper\Data as OtpHelper;

class Customer extends AbstractHelper
{
    public const USERNAME_EMAIL = 'email';
    public const USERNAME_PHONE_NUMBER = 'phonenumber';
    public const USERNAME_BOTH = 'both';

    public const CUSTOMER_LOGIN = 'customer_login';
    public const CUSTOMER_FORGOT_PASSWORD = 'customer_forgotpassword';
    public const CUSTOMER_REGISTRATION = 'customer_registration';

    public const PHONENUMBER_ALREADY_EXISTS = 'phonenumber_already_exists';
    public const PHONENUMBER_INVALID_FORMAT = 'phonenumber_invalid_format';
    public const PHONENUMBER_VALIDATION_SUCCESS = 'phonenumber_validation_success';

    /**
     * @var OtpHelper
     */
    private $otpHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerPasswordEncryptor
     */
    private $customerPasswordEncryptor;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @param OtpHelper                                   $otpHelper
     * @param CustomerRepositoryInterface                 $customerRepository
     * @param CustomerRegistry                            $customerRegistry
     * @param CustomerPasswordEncryptor                   $customerPasswordEncryptor
     * @param AccountConfirmation                         $accountConfirmation
     * @param AuthenticationInterface                     $authentication
     * @param CustomerSession                             $customerSession
     * @param AddressRegistry                             $addressRegistry
     * @param AddressRepositoryInterface                  $addressRepository
     * @param CustomerCollectionFactory                   $customerCollectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        OtpHelper $otpHelper,
        CustomerRepositoryInterface $customerRepository,
        CustomerRegistry $customerRegistry,
        CustomerPasswordEncryptor $customerPasswordEncryptor,
        AccountConfirmation $accountConfirmation,
        AuthenticationInterface $authentication,
        CustomerSession $customerSession,
        AddressRegistry $addressRegistry,
        AddressRepositoryInterface $addressRepository,
        CustomerCollectionFactory $customerCollectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->otpHelper = $otpHelper;
        $this->customerRepository = $customerRepository;
        $this->customerRegistry = $customerRegistry;
        $this->customerPasswordEncryptor = $customerPasswordEncryptor;
        $this->accountConfirmation = $accountConfirmation;
        $this->authentication = $authentication;
        $this->customerSession = $customerSession;
        $this->addressRegistry = $addressRegistry;
        $this->addressRepository = $addressRepository;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Get customer data by username and password
     *
     * @param  string $username
     * @param  string $password
     * @return array
     */
    public function getCustomerDataByCredentials($username, $password): array
    {
        try {
            if ($this->isEmail($username)) {

                $medium = 'email';
                $isCustomerValid = $this->validateCustomer($username, $password);

            } else {
                $medium = 'telephone';
                list($customer, $isCustomerValid) = $this->getCustomerDataByPhoneNumber($username, $password);

            }
        } catch (\Exception $exception) {
            return [];
        }
        // if ($isCustomerValid == "" || $isCustomerValid == false) {
        //     // throw new \Magento\Framework\Exception\CouldNotDeleteException(__("Prices have been changed!"))
        // }
        if ($isCustomerValid) {
            if ($this->isEmail($username)) {
                $customer = $this->customerRegistry->retrieveByEmail($username);
            }
            $customerAddress = $customer->getDefaultBillingAddress()
            ?: $customer->getDefaultShippingAddress()
            ?: $customer->getAddresses()[0] ?? null;
            return [
                'medium' => $medium,
                'customer' => $customer,
                'customerAddress' => $customerAddress,
            ];
        }
        return [];
    }

    /**
     * Validate Email
     *
     * @param  string $email
     * @return boolean
     */
    public function isEmail($email): bool
    {
        $validator = new \Magento\Framework\Validator\EmailAddress();
        return $validator->isValid($email);
    }

    /**
     * Validate Phone number format
     *
     * @param  string $phoneNumber
     * @return boolean
     */
    public function isPhoneNumberFormatValid($phoneNumber): bool
    {
        return (bool) empty($phoneNumber) ? false : preg_match("/^\+\d{9,}$/", $phoneNumber);
    }

    /**
     * Validate phone number and check if a customer already exists with phone number
     *
     * @param  string $phoneNumber
     * @param  int    $customerIdToExclude
     * @return array
     */
    public function validatePhonenumber($phoneNumber, $customerIdToExclude = null): array
    {
        $result['errors'] = false;
        $result['messages'][self::PHONENUMBER_VALIDATION_SUCCESS] = __('Phonenumber validation successfull');
        if (!$this->isPhoneNumberFormatValid($phoneNumber)) {
            $result['errors'] = true;
            unset($result['messages']);
            $result['messages'][self::PHONENUMBER_INVALID_FORMAT]
            = __('Please enter a valid phone number (Ex: +918888888888).');
        }
        list($existingCustomer) = $this->getCustomerDataByPhoneNumber($phoneNumber, '', $customerIdToExclude);
        if ($existingCustomer) {
            if (!$result['errors']) {
                unset($result['messages']);
            }
            $result['errors'] = true;
            $result['messages'][self::PHONENUMBER_ALREADY_EXISTS] = __('Phonenumber already exist.');
        }
        return $result;
    }

    /**
     * Check if accounts confirmation is required in config
     *
     * @param  CustomerInterface $customer
     * @return boolean
     */
    private function isConfirmationRequired($customer): bool
    {
        return $this->accountConfirmation->isConfirmationRequired(
            $customer->getWebsiteId(),
            $customer->getId(),
            $customer->getEmail()
        );
    }

    /**
     * Validate Customer
     *
     * @param  CustomerInterface|string $customer
     * @param  string                   $password
     * @return bool
     */
    private function validateCustomer($customer, $password): bool
    {
        try {
            $customer = (!is_numeric($customer) && is_string($customer))
            ? $customer = $this->customerRepository->get($customer)
            : $customer;
            $customerId = $customer->getId();
            $currentCustomer = $this->customerRegistry->retrieve($customerId);
            if ($password != "") {
                if (!$this->isCustomerPasswordMatches($customerId, $password)
                    || $currentCustomer->isCustomerLocked()
                    || ($customer->getConfirmation() && $this->isConfirmationRequired($customer))
                ) {
                    return false;
                }
            }
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * Validate customer password
     *
     * @param  int    $customerId
     * @param  string $password
     * @return boolean
     */
    private function isCustomerPasswordMatches($customerId, $password): bool
    {
        $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);
        $hash = $customerSecure->getPasswordHash() ?? '';
        return $this->customerPasswordEncryptor->validateHash($password, $hash);
    }

    /**
     * Process Customer Authentication Failure
     *
     * @param  string $email
     * @return boolean
     */
    public function processAuthenticationFailure($email): bool
    {
        try {
            $customer = $this->customerRepository->get($email);
            $customerId = $customer->getId();
            $this->authentication->processAuthenticationFailure($customerId);
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * Get Username Login Field Configuration by type
     *
     * @param  string $fieldType
     * @return array
     */
    public function getLoginUsernameFieldConfigByType($fieldType)
    {
        switch ($fieldType) {
            case self::USERNAME_EMAIL:
                return [
                    'label' => 'Email',
                    'title' => 'Email',
                    'alt' => 'email',
                    'dataValidate' => "{required: true, 'validate-email': true}",
                    'note' => 'If you have an account sign in with your email address.',
                    'type' => 'email',
                ];
            case self::USERNAME_PHONE_NUMBER:
                return [
                    'label' => __("Phone Number "),
                    'title' => 'Phone Number',
                    'alt' => 'phone number',
                    'dataValidate' => "{required: true, 'wk-otp-telephone': true}",
                    'note' => 'If you have an account sign in with your phone number.',
                    'type' => 'text',
                ];
            case self::USERNAME_BOTH:
                return [
                    'label' => 'Email or Phone Number',
                    'title' => 'Email or Phone Number',
                    'alt' => 'email or phone number',
                    'dataValidate' => "{required: true, 'wk-otp-email-telephone': true}",
                    'note' => 'If you have an account sign in with your email or phone number.',
                    'type' => 'text',
                ];
        }
    }

    /**
     * Get phone number with calling code
     *
     * @param  string $country
     * @param  string $telephone
     * @return string|null
     */
    public function getTelephoneWithCallingCode($country, $telephone): ?string
    {
        $country = is_numeric($country) ? ('+' . (int) $country) : $country;
        $callingCode = strpos($country, "+") === 0
        ? $country
        : ('+' . $this->otpHelper->getCallingCodeByCountryCode($country));
        $telephone = str_replace(" ", "", $telephone);
        return strpos($telephone, $callingCode) === false ? $callingCode . $telephone : $telephone;
    }

    /**
     * Returns customer data by phonenumber
     *
     * @param  string   $phoneNumber
     * @param  string   $password
     * @param  int|null $customerIdToExclude
     * @return array
     */
    public function getCustomerDataByPhoneNumber($phoneNumber, $password = '', $customerIdToExclude = null): array
    {
        $phoneNumberCustomers = $this->customerCollectionFactory->create()
            ->addAttributeToFilter('default_phone_number', ['eq' => $phoneNumber]);
        if ($customerIdToExclude = $customerIdToExclude ?: $this->customerSession->getCustomerId()) {
            $phoneNumberCustomers->addFieldToFilter('entity_id', ['neq' => $customerIdToExclude]);
        }
        $phoneNumberCustomers = $phoneNumberCustomers->getItems();
        foreach ($phoneNumberCustomers as $phoneNumberCustomer) {
            $isCustomerValid = $this->validateCustomer($phoneNumberCustomer, $password);
            if (!$password || $isCustomerValid) {
                $customer = $phoneNumberCustomer;
                break;
            }
        }
        if (empty($customer)) {
            $defaultBillingCustomers = $this->customerCollectionFactory
                ->create()
                ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing')
                ->addAttributeToFilter('billing_telephone', ['eq' => $phoneNumber]);
            if ($customerIdToExclude) {
                $defaultBillingCustomers->addFieldToFilter('entity_id', ['neq' => $customerIdToExclude]);
            }
            $defaultBillingCustomers = $defaultBillingCustomers->getItems();
            foreach ($defaultBillingCustomers as $defaultBillingCustomer) {
                $isCustomerValid = $this->validateCustomer($defaultBillingCustomer, $password);
                if (!$password || $isCustomerValid) {
                    $customer = $defaultBillingCustomer;
                    break;
                }
            }
        }
        return [
            0 => $customer ?? null,
            1 => $isCustomerValid ?? false,
            'customer' => $customer ?? null,
            'isCustomerValid' => $isCustomerValid ?? false,
        ];
    }

    /**
     * Get current Username type
     *
     * @return string
     */
    public function getCurrentUsernameType(): string
    {
        $isModuleEnabled = $this->otpHelper->isModuleEnable();
        $isMobileOtpEnabled = $this->otpHelper->getOtpEnabledConfigMessage();
        $isSendOtpEmailEnabled = $this->otpHelper->getTwilioSendOtpEmailEnabled();
        $sendOtpVia = $this->otpHelper->sendOtpVia();
        if ($isModuleEnabled) {
            if ($isMobileOtpEnabled) {
                if ($sendOtpVia == 'mobile') {
                    return self::USERNAME_PHONE_NUMBER;
                } elseif ($sendOtpVia == 'email') {
                    return self::USERNAME_EMAIL;
                } else {
                    return self::USERNAME_BOTH;
                }
            }
            return self::USERNAME_EMAIL;
        }
        return self::USERNAME_EMAIL;
    }

    /**
     * Get Phone number by customer id
     *
     * @param  int $customerId
     * @return string|null
     */
    public function getPhonenumberByCustomerId($customerId): ?string
    {
        $customers = $this->customerCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['eq' => $customerId])
            ->addAttributeToSelect('default_phone_number')->getItems();
        foreach ($customers as $customer) {
            return $customer->getDefaultPhoneNumber();
        }
    }
}
