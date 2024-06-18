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

namespace Webkul\Otp\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Webkul\Otp\Helper\Customer as CustomerHelper;
use Webkul\Otp\Helper\Data as OtpHelper;
use Webkul\Otp\Helper\FormKey\Validator as FormKeyValidator;

class LoginConfig extends Action
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var $customerHelper
     */
    private $customerHelper;

    /**
     * @var OtpHelper
     */
    private $otpHelper;

    /**
     * @var JsonResult
     */
    private $jsonHelper;

    /**
     * @param FormKeyValidator  $formKeyValidator
     * @param ResultJsonFactory $resultJsonFactory
     * @param CustomerHelper    $customerHelper
     * @param OtpHelper         $otpHelper
     * @param JsonHelper        $jsonHelper
     * @param Context           $context
     */
    public function __construct(
        FormKeyValidator $formKeyValidator,
        ResultJsonFactory $resultJsonFactory,
        CustomerHelper $customerHelper,
        OtpHelper $otpHelper,
        JsonHelper $jsonHelper,
        Context $context
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerHelper = $customerHelper;
        $this->otpHelper = $otpHelper;
        $this->jsonHelper = $jsonHelper;

        parent::__construct($context);
    }

    /**
     * Returns login configuraiton for checkout loing widget.
     */
    public function execute()
    {
        $response = [
            'error' => true,
            'message' => __('Bad Request.'),
        ];
        if (!$this->getRequest()->getMethod() === 'POST'
            || !$this->getRequest()->isXmlHttpRequest()
            || !$this->formKeyValidator->validate($this->getRequest())
        ) {
            return $this->resultJsonFactory->create()->setData($response);
        }
        $otpModalConfig = $this->otpHelper->getOtpModalConfig();
        $usernameType = $this->customerHelper->getCurrentUsernameType();
        $usernameFieldConfig = $this->customerHelper->getLoginUsernameFieldConfigByType($usernameType);
        $loginConfig['otpModalComponent'] = ['Webkul_Otp/js/login' => $otpModalConfig];
        $loginConfig['usernameFieldConfig'] = $usernameFieldConfig;
        $response = ['error' => false, 'message' => __('Success'), 'data' => $loginConfig];
        return $this->resultJsonFactory->create()->setData($response);
    }
}
