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

namespace Webkul\Otp\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Webkul\Otp\Helper\Customer as CustomerHelper;
use Webkul\Otp\Helper\Data as OtpHelper;

/**
 * Validate phone number for customer registration.
 */
class DefaultPhoneNumber extends AbstractBackend
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
     * @var RequestInterface
     */
    private $request;

    /**
     * @param CustomerHelper $customerHelper
     * @param OtpHelper $otpHelper
     * @param RequestInterface $request
     */
    public function __construct(
        CustomerHelper $customerHelper,
        OtpHelper $otpHelper,
        RequestInterface $request
    ) {
        $this->customerHelper = $customerHelper;
        $this->otpHelper = $otpHelper;
        $this->request = $request;
    }

    /**
     * Validates Customer
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @throws LocalizedException
     * @return bool
     */
    public function validate($customer)
    {
        // Temporarily returning true as per the original code comment
        return true;

        if ($this->otpHelper->isModuleEnable() &&
            CustomerHelper::USERNAME_EMAIL !== $this->customerHelper->getCurrentUsernameType()
        ) {
            $requestPost = $this->request->getPostValue('customer');
            if (!empty($requestPost['default_phone_number'])) {
                $phoneNumber = $requestPost['default_phone_number'];
            } else {
                $phoneNumber = $customer->loadByEmail($customer->getEmail())
                    ->getData($this->getAttribute()->getAttributeCode());
            }
            $result = $this->customerHelper->validatePhonenumber($phoneNumber, $customer->getId());
            if ($result['errors']) {
                throw new LocalizedException(
                    $result['messages'][CustomerHelper::PHONENUMBER_INVALID_FORMAT] ??
                    $result['messages'][CustomerHelper::PHONENUMBER_ALREADY_EXISTS] ??
                    __('Invalid phone number.')
                );
            }
        }
        return true;
    }
}
