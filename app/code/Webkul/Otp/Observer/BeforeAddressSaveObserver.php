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

use Magento\Framework\Event\ObserverInterface;
use Webkul\Otp\Helper\Customer as CustomerHelper;
use Webkul\Otp\Helper\Data as OtpHelper;

class BeforeAddressSaveObserver implements ObserverInterface
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
     * Execute
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->otpHelper->isModuleEnable()) {
            $customerAddress = $observer->getCustomerAddress();
            $customer = $customerAddress->getCustomer();
            if ($this->isDefaultBilling($customerAddress)) {
                $countryId = $customerAddress->getCountryId();
                $telephone = $customerAddress->getTelephone();
                $telephoneWithCallingCode = $this->customerHelper->getTelephoneWithCallingCode($countryId, $telephone);
                $oldDefaultPhonenumber = $customer->getData(
                    \Webkul\Otp\Setup\Patch\Data\AddDefaultPhoneNumberAttribute::ATTRIBUTE_CODE
                );
                if ($oldDefaultPhonenumber !== $telephoneWithCallingCode) {
                    $customer->setData(
                        \Webkul\Otp\Setup\Patch\Data\AddDefaultPhoneNumberAttribute::ATTRIBUTE_CODE,
                        $telephoneWithCallingCode
                    );
                    $customer->save();
                }
            }
        }
        return $this;
    }

    /**
     * Check whether specified billing address is default for its customer
     *
     * @param  \Magento\Customer\Model\Address $address
     * @return bool
     */
    private function isDefaultBilling($address)
    {
        return $address->getId() && $address->getId() == $address->getCustomer()->getDefaultBilling()
        || $address->getIsPrimaryBilling()
        || $address->getIsDefaultBilling();
    }
}
