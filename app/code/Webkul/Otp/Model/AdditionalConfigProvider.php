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
namespace Webkul\Otp\Model;

class AdditionalConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @param \Webkul\Otp\Helper\Data $helper
     */
    public function __construct(
        \Webkul\Otp\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Function to set additonal parameter in window coonfig provider on checkout page.
     *
     * @return array value of config validation enable at checkout
     */
    public function getConfig()
    {
        $output['is_onestepcheckout_enabled'] = $this->_helper->isOneStepCheckoutEnable();
        $output['is_module_enabled'] = $this->_helper->isModuleEnable();
        $output['validateNumberError'] = __("Please enter a valid number.");
        $output['opt_validation_enabled'] = $this->_helper->isEnableAtCheckout();
        $output['allowed_payment_methods'] = $this->_helper->getAllowedPaymentMethods();
        return $output;
    }
}
