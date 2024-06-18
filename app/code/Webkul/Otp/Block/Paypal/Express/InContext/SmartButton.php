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

declare (strict_types = 1);

namespace Webkul\Otp\Block\Paypal\Express\InContext;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Paypal\Block\Express\InContext\SmartButton as PaypalInContextSmartButton;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\SmartButtonConfig;
use Webkul\Otp\Helper\Data as OtpHelper;

/**
 * Class Button
 */
class SmartButton extends PaypalInContextSmartButton
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var OtpHelper
     */
    private $otpHelper;

    /**
     * @param Context             $context
     * @param ConfigFactory       $configFactory
     * @param SerializerInterface $serializer
     * @param SmartButtonConfig   $smartButtonConfig
     * @param UrlInterface        $urlBuilder
     * @param OtpHelper           $otpHelper
     * @param array               $data
     */

    public function __construct(
        Context $context,
        ConfigFactory $configFactory,
        SerializerInterface $serializer,
        SmartButtonConfig $smartButtonConfig,
        UrlInterface $urlBuilder,
        OtpHelper $otpHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $configFactory,
            $serializer,
            $smartButtonConfig,
            $urlBuilder,
            $data
        );

        $this->serializer = $serializer;
        $this->otpHelper = $otpHelper;
    }

    /**
     * Get Block Template
     *
     * @return string
     */
    public function getTemplate(): string
    {
        $isModuleEnable = $this->otpHelper->isModuleEnable();
        $enableAtCheckout = $this->otpHelper->isEnableAtCheckout();
        $allowedPayments = explode(',', $this->otpHelper->getAllowedPaymentMethods());
        $isAllowedPaypalExpress = in_array('paypal_express', $allowedPayments);
        if ($isModuleEnable && $enableAtCheckout && $isAllowedPaypalExpress) {
            return 'Webkul_Otp::productPagePaypalButton.phtml';
        }
        return parent::getTemplate();
    }

    /**
     * Returns string to initialize paypalotp js component
     *
     * @return string
     */
    public function getPaypalOtpJsInitParams(): string
    {
        $otpModalConfig = $this->otpHelper->getOtpModalConfig();
        $paypalOtpConfig = [
            'paypalotp' => $otpModalConfig,
        ];
        return $this->serializer->serialize($paypalOtpConfig);
    }
}
