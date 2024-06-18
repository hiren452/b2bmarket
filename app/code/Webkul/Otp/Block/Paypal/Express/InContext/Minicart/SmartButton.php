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

declare(strict_types=1);

namespace Webkul\Otp\Block\Paypal\Express\InContext\Minicart;

use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;
use Magento\Paypal\Block\Express\InContext\Minicart\SmartButton as PaypalInContextMinicartSmartButton;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\SmartButtonConfig;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;
use Webkul\Otp\Helper\Data as OtpHelper;

class SmartButton extends PaypalInContextMinicartSmartButton
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
     * @param Context                $context
     * @param ConfigFactory          $configFactory
     * @param Session                $session
     * @param MethodInterface        $payment
     * @param SerializerInterface    $serializer
     * @param SmartButtonConfig      $smartButtonConfig
     * @param UrlInterface           $urlBuilder
     * @param QuoteIdToMaskedQuoteId $quoteIdToMaskedQuoteId
     * @param OtpHelper              $otpHelper
     * @param array                  $data
     */
    public function __construct(
        Context $context,
        ConfigFactory $configFactory,
        Session $session,
        MethodInterface $payment,
        SerializerInterface $serializer,
        SmartButtonConfig $smartButtonConfig,
        UrlInterface $urlBuilder,
        QuoteIdToMaskedQuoteId $quoteIdToMaskedQuoteId,
        OtpHelper $otpHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $configFactory,
            $session,
            $payment,
            $serializer,
            $smartButtonConfig,
            $urlBuilder,
            $quoteIdToMaskedQuoteId,
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
            return 'Webkul_Otp::cartPagePaypalButton.phtml';
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
            'paypalotp' => $otpModalConfig
        ];
        return $this->serializer->serialize($paypalOtpConfig);
    }
}
