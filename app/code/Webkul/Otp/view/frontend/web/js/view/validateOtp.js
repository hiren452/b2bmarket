/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Webkul_Otp/js/model/validateOtp'
    ],
    function (Component, additionalValidators, otpValidation) {
        'use strict';
        additionalValidators.registerValidator(otpValidation);
        return Component.extend({});
    }
);
