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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Webkul\Otp\Helper\Customer as CustomerHelper;
use Webkul\Otp\Helper\Data as OtpHelper;

class BeforeCustomerSaveObserver implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var OtpHelper
     */
    private $otpHelper;

    /**
     * @param RequestInterface $request
     * @param CustomerHelper   $customerHelper
     * @param OtpHelper        $otpHelper
     */
    public function __construct(
        RequestInterface $request,
        CustomerHelper $customerHelper,
        OtpHelper $otpHelper
    ) {
        $this->otpHelper = $otpHelper;
        $this->customerHelper = $customerHelper;
        $this->request = $request;
    }

    /**
     * Execute
     *
     * @param  Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        if ($this->otpHelper->isModuleEnable()
        ) {
            $callingCode = $this->request->getPostValue('region')
            ?: ($this->request->getPostValue('callingcode')
                ? '+' . $this->request->getPostValue('callingcode')
                : null);

            $phonenumber = $this->request->getPostValue('mobile') ?: $this->request->getPostValue('phonenumber');
            if ($phonenumber == "" && !empty($this->request->getPostValue('customer')['default_phone_number'])) {

                $defaultPhoneNumber = $this->request->getPostValue('customer')['default_phone_number'];

                $customer->setData('default_phone_number', $defaultPhoneNumber);
            } else {

                $phonenumberWithCallingCode = $callingCode && $phonenumber
                ? $this->customerHelper->getTelephoneWithCallingCode($callingCode, $phonenumber)
                : null;

                if ($phonenumberWithCallingCode) {
                    $customer->setDefaultPhoneNumber($phonenumberWithCallingCode);
                }
            }

        }
        return $this;
    }
}
