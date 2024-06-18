/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

/*jshint jquery:true*/
define(
    [
    'ko',
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Webkul_Otp/js/model/customer',
    'Magento_Customer/js/model/customer'
    ], function (ko, $, $t, alert, customer, customerModel) {
        'use strict';
        return function (options) {
            var ajaxRequest;
            var wkCheckoutConfig = JSON.parse(localStorage.getItem('wkCheckoutConfig'));
            var ajaxValidate;
            var isValidationFinished = ko.observable(false);
            var modalPopup = $("#otp_modal").modal(
                {
                    buttons: [{
                        text: options.resendText,
                        class: 'otp_resend',
                        click: function () {
                            $('.wk-otp-loading-mask').removeClass('wk-otp-display-none');
                            if ($('#review-order-form').valid()) {
                                sendOtpAjax(1);
                            }
                        }
                    }],
                    modalClass: 'otp_modal_popup',
                    clickableOverlay: false,
                    type: 'popup',
                    title: 'OTP Verification',
                }
            );
            // Setting Customer Data
            customer.setCustomerData(wkCheckoutConfig.customerData);

            $('.action.submit.primary').on(
                'click', function (e) {
                    if ($('#review-order-form').valid()) {
                        $('.otp_response').removeClass('success');
                        $('.otp_response').removeClass('error');
                        $('.wk-otp-loading-mask').removeClass('wk-otp-display-none');
                        e.preventDefault();
                        sendOtpAjax();
                    }
                }
            );

            /**
             * Function to prevent typing alphabets and special character in otp input box
             */
            $('.otp_modal_popup').on(
                'keydown', "#user_otp", function (e) {
                    // Allow: backspace, delete, tab, escape and .
                    if ($.inArray(e.keyCode, [46, 8, 9, 27, 110]) !== -1 
                        // Allow: Ctrl+A, Command+A
                        || (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) 
                        // Allow: home, end, left, right, down, up
                        || (e.keyCode >= 35 && e.keyCode <= 40)
                    ) {
                        // let it happen, don't do anything
                        return;
                    }
                    if (e.keyCode === 13) {
                        $('button.submit_otp').trigger('click');
                    }
                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                }
            );

            $('button.submit_otp').on(
                'click', function () {
                    $('.validate_error').remove();
                    var userOtp = $('#user_otp').val();
                    if (userOtp != "" && $.isNumeric(userOtp) && userOtp > 0) {
                        $('.wk-otp-loading-mask').removeClass('wk-otp-display-none');
                        validateOtp();
                    }
                }
            );


            /**
             *  Function to send Otp via ajax
             */
            function sendOtpAjax(resendFlag = 0)
            {
                if (ajaxRequest && ajaxRequest.readyState != 4) {
                    ajaxRequest.abort();
                }
                var email = customer.getCustomerEmail();
                var name = customer.getCustomerFirstname();
                var mobile = customer.getCustomerTelephoneFromAddress();
                var region = customer.getCustomerCountryIdFromAddress();
                var formKey = $('input[name="form_key"]').val();
                ajaxRequest = $.ajax(
                    {
                        url: options.otpAction,
                        data: {
                            'email': email,
                            'checkout': true,
                            'resend': resendFlag,
                            'form_key': formKey,
                            'mobile': mobile,
                            'region': region,
                            'name': name
                        },
                        async: true,
                        type: 'POST',
                        showLoader: true,
                    }
                ).done(
                    function (result) {
                        if (result.error) {
                            $('.otp_expire_message').addClass('wk-otp-display-none');
                            $('.otp_action').hide();
                            $('.validate_error').remove();
                            $('.wk-otp-loading-mask').addClass('wk-otp-display-none');
                            var errorMessage = "<div class='validate_error'><span>" + result.message + "</span></div>";
                            $('.otp_popup').append(errorMessage);
                            $('button.otp_resend').addClass('wk-otp-display-none');
                        } else {
                            $('.otp_expire_message').removeClass('wk-otp-display-none');
                            $('.otp_action').show();
                            $('.validate_error').remove();
                            $('.wk-otp-loading-mask').addClass('wk-otp-display-none');
                            $('.otp_response').addClass('success').html(result.message);
                            $('#user_otp_checkout').val('');
                            $('#otp_modal').removeClass('wk-otp-display-none');
                            $('#otp_modal').removeClass('hide');
                            $('button.otp_resend').removeClass('wk-otp-display-none');
                        }
                    }
                ).fail(
                    function (jqXHR) {
                        $('.otp_expire_message').addClass('wk-otp-display-none');
                        $('.otp_action').hide();
                        $('.wk-otp-loading-mask').addClass('wk-otp-display-none');
                        $('.otp_response').addClass('error').html(jqXHR.responseText);
                        $('button.otp_resend').addClass('wk-otp-display-none');
                    }
                ).always(
                    function () {
                        modalPopup.modal('openModal');
                    }
                );
            }

            /**
             * Function to validate the Otp entered by the user
             */
            function validateOtp()
            {
                if (ajaxValidate && ajaxValidate.readyState != 4) {
                    ajaxValidate.abort();
                }
                var email = customer.getCustomerEmail();
                var userOtp = $('#user_otp').val();
                var formKey = $('input[name="form_key"]').val();
                ajaxValidate = jQuery.ajax(
                    {
                        url: options.otpValidateAction,
                        data: {
                            'email': email,
                            'user_otp': userOtp,
                            'form_key': formKey
                        },
                        async: true,
                        type: 'POST',
                        showLoader: true,
                    }
                ).done(
                    function (result) {
                        if (result.error) {
                            $('.validate_error').remove();
                            $('.wk-otp-loading-mask').addClass('wk-otp-display-none');
                            var errorMessage = "<div class='validate_error'><span>" + result.message + "</span></div>";
                            $('.otp_popup').append(errorMessage);
                        } else {
                            $('.wk-otp-loading-mask').addClass('wk-otp-display-none');
                            modalPopup.modal('closeModal');
                            $('#review-order-form').submit();
                        }
                    }
                ).fail(
                    function (jqXHR) {
                        $('.wk-otp-loading-mask').addClass('wk-otp-display-none');
                        $('.otp_response').addClass('error').html(jqXHR.responseText);
                    }
                );
            }
        }
    }
);