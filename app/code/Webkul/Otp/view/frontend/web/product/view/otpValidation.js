/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define(
            [
            'jquery',
            'jquery-ui-modules/widget',
            'mage/validation/validation'
            ], factory
        );
    } else {
        factory(jQuery);
    }
}(function ($) {
    'use strict';
        var otpValidation = {
            options: {
                radioCheckboxClosest: 'ul, ol',
                fieldFormClasses: 'guestDetailsContainer-form otpContainer-form',
                fieldWrapper: 'addon',

                /**
                 * @param {*} error
                 * @param {HTMLElement} element
                 */
                errorPlacement: function (error, element) {

                    var formClasses = this.fieldFormClasses.split(' ');
                    formClasses.forEach(
                        function (formClass) {
                            if ($(element).parents('form').hasClass(formClass)) {
                                element.closest('.' + this.fieldWrapper).after(error);
                                return;
                            }
                        }, this
                    );

                },
            }
    };

    $.widget('mage.otpValidation', $.mage.validation, otpValidation);

    return $.mage.otpValidation;
}));
