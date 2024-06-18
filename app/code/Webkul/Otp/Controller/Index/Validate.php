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
use Webkul\Otp\Api\OtpRepositoryInterface;

class Validate extends Action
{
    /**
     * @var \Webkul\Otp\Helper\FormKey\Validator $formKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var \Webkul\Otp\Model\OtpFactory
     */
    private $otpFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJson;

    /**
     * @var OtpRepositoryInterface
     */
    private $otpRepositoryInterface;

    /**
     * @param \Webkul\Otp\Helper\FormKey\Validator             $formKeyValidator
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJson
     * @param OtpRepositoryInterface                           $otpRepositoryInterface
     * @param \Webkul\Otp\Model\OtpFactory                     $otpFactory
     * @param \Webkul\Otp\Helper\Data                          $helper
     * @param \Psr\Log\LoggerInterface                         $logger
     * @param Context                                          $context
     */
    public function __construct(
        \Webkul\Otp\Helper\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson,
        OtpRepositoryInterface $otpRepositoryInterface,
        \Webkul\Otp\Model\OtpFactory $otpFactory,
        \Webkul\Otp\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger, //log injection
        Context $context
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJson = $resultJson;
        $this->otpRepositoryInterface = $otpRepositoryInterface;
        $this->otpFactory = $otpFactory;
        $this->_logger = $logger;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Execute function for validate the Otp from Customers
     *
     * @param  none
     * @return mixed
     */
    public function execute()
    {
        if ($this->formKeyValidator->validate($this->getRequest())) {
            $email = $this->getRequest()->getParam('email');
            $otp = $this->getRequest()->getParam('user_otp');
            $otpData = $this->otpRepositoryInterface->getByEmail($email);
            if (is_array($otpData->getData())) {
                $otpCreatedTimestamp = strtotime($otpData->getCreatedAt());
                $currentTimestamp = time();
                $timeDiff = $currentTimestamp - $otpCreatedTimestamp;
                $otpExpiryTime = $this->helper->otpExpiry();
                if ($otpExpiryTime >= 60 && $otpExpiryTime <= 300) {
                    $otpExpiryTime = $otpExpiryTime;
                } else {
                    $otpExpiryTime = 60;
                }
                if ($timeDiff >= $otpExpiryTime) {
                    $result = ['error' => true, 'message' => __('OTP expired.Please resend OTP and try again.')];
                    return $this->resultJson->create()->setData($result);
                }
                if ($otpData->getOtp() == $otp) {
                    $this->otpRepositoryInterface->deleteByEmail($email);
                    $result = ['error' => false, 'message' => __('OTP verified.')];
                    return $this->resultJson->create()->setData($result);
                } else {
                    $result = ['error' => true, 'message' => __('You have entered a wrong code. Please try again.')];
                    return $this->resultJson->create()->setData($result);
                }
            }
            $result = ['error' => true, 'message' => __('Something Went Wrong.')];
            return $this->resultJson->create()->setData($result);
        } else {
            $result = ['error' => true, 'message' => 'Something Went Wrong.'];
            return $this->resultJson->create()->setData($result);
        }
    }
}
