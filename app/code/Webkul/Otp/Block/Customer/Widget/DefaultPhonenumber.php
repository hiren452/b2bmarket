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

namespace Webkul\Otp\Block\Customer\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Framework\View\Element\Template\Context;
use Webkul\Otp\Helper\Customer as CustomerHelper;
use Webkul\Otp\Helper\Data as OtpHelper;

class DefaultPhonenumber extends \Magento\Customer\Block\Widget\AbstractWidget
{
    /**
     * Guessed calling code of default phone number for caching
     *
     * @var string
     */
    private $callingCode;

    /**
     * Cached default phone number
     *
     * @var string
     */
    private $defaultPhonenumber;

    /**
     * Guessed phone number from default phone number
     *
     * @var string
     */
    private $phonenumber;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var OtpHelper
     */
    private $otpHelper;

    /**
     * @param Context                   $context
     * @param AddressHelper             $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param OtpHelper                 $otpHelper
     * @param CustomerHelper            $customerHelper
     * @param array                     $data
     */
    public function __construct(
        Context $context,
        AddressHelper $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        OtpHelper $otpHelper,
        CustomerHelper $customerHelper,
        array $data = []
    ) {
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
        $this->customerHelper = $customerHelper;
        $this->otpHelper = $otpHelper;
        $this->_isScopePrivate = true;
    }

    /**
     * Sets the template
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Webkul_Otp::widget/defaultphonenumber.phtml');
    }

    /**
     * Get Countries with calling code
     *
     * @param  boolean $refresh
     * @return array
     */
    public function getCountries($refresh = false): array
    {
        return $this->otpHelper->getCountries($refresh);
    }

    /**
     * Class name getter
     *
     * @return string
     */
    public function getClassName()
    {
        if (!$this->hasData('class_name')) {
            $this->setData('class_name', 'customer-defaultphonenumber');
        }
        return $this->getData('class_name');
    }

    /**
     * Container class name getter
     *
     * @return string
     */
    public function getContainerClassName()
    {
        $class = $this->getClassName();
        $class .= '-callingcode';
        $class .= '-phonenumber';
        return $class;
    }

    /**
     * Get is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        $isVisible = $this->_getAttribute('default_phone_number')
            ? (bool)$this->_getAttribute('default_phone_number')->isVisible()
            : false;
        $isRequired = $this->isRequired();
        return $isVisible && $isRequired;
    }

    /**
     * Get is required.
     *
     * @return bool
     */
    public function isRequired()
    {
        $isModuleEnabled = $this->otpHelper->isModuleEnable();
        $isMobileOtpEnabled = $this->otpHelper->getOtpEnabledConfigMessage();
        return $isModuleEnabled && $isMobileOtpEnabled;
    }

    /**
     * Retrieve store attribute label
     *
     * @param string $attributeCode
     *
     * @return string
     */
    public function getStoreLabel($attributeCode)
    {
        $attribute = $this->_getAttribute($attributeCode);
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * Get string with frontend validation classes for attribute
     *
     * @param  string $attributeCode
     * @return string
     */
    public function getAttributeValidationClass($attributeCode)
    {
        $attributeMetadata = $this->_getAttribute($attributeCode);
        return $attributeMetadata ? $attributeMetadata->getFrontendClass() : '';
    }

    /**
     * Get customer default phone number
     *
     * @return string|null
     */
    public function getDefaultPhonenumber(): ?string
    {
        if (!$this->defaultPhonenumber) {
            $customerData = $this->getObject();
            if ($customerData) {
                $this->defaultPhonenumber = $this->customerHelper->getPhonenumberByCustomerId($customerData->getId());
            }
        }
        return $this->defaultPhonenumber;
    }

    /**
     * Guess phone number from default phone number
     *
     * @return string
     */
    public function guessPhonenumber(): ?string
    {
        if (!$this->phonenumber) {
            $defaultPhonenumber = $this->getDefaultPhonenumber();
            $guessedCallingCode = $this->guessCallingCode();
            if ($defaultPhonenumber && $guessedCallingCode) {
                $this->phonenumber = substr($defaultPhonenumber, strlen($guessedCallingCode) + 1);
            }
        }
        return $this->phonenumber;
    }

    /**
     * Guess calling code from phone number
     *
     * @return string|null
     */
    public function guessCallingCode(): ?string
    {
        if (!$this->callingCode) {
            $defaultPhonenumber = $this->getDefaultPhonenumber();
            $countries = $this->getCountries();
            if ($defaultPhonenumber && $countries && !$this->callingCode) {
                $matchedCallingCode = null;
                $matchedCallingCodeLength = 0;
                foreach ($countries as $country) {
                    $callingCode = $country['callingCode'];
                    $callingCodeLength = strlen($callingCode);
                    if ($callingCode && preg_match("/^\+$callingCode/", $defaultPhonenumber)
                        && $callingCodeLength > $matchedCallingCodeLength
                    ) {
                        $matchedCallingCode = $callingCode;
                        $matchedCallingCodeLength = $callingCodeLength;
                    }
                }
                $this->callingCode = $matchedCallingCode;
            }
        }
        return $this->callingCode;
    }
}
