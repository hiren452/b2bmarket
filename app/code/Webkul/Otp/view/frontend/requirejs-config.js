/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
var config = {
    deps: [
            'Webkul_Otp/js/validation',
            'Webkul_Otp/js/lib/knockout/bindings/mage-init-update',
    ],
    map: {
        '*': {
            verifyOtp: 'Webkul_Otp/js/verifyOtp',
            verifyCheckoutOtp: 'Webkul_Otp/js/verifyCheckoutOtp',
            verifyMultishippingOtp: 'Webkul_Otp/js/multishipping/verifyOtp',
            'Magento_Paypal/template/payment/paypal-express-in-context.html': 'Webkul_Otp/template/payment/paypal-express-in-context.html',
            'Magento_Paypal/js/in-context/express-checkout-wrapper': 'Webkul_Otp/js/in-context/express-checkout-wrapper',
            paypalotp: 'Webkul_Otp/js/paypalotp',
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/authentication': {
                'Webkul_Otp/js/view/authentication-mixin': true
            },
        }
    }
};
