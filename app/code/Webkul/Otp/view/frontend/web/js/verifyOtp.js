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
        'jquery',
        'mage/translate',
        'mage/cookies',
        'loader'      
    ],
    function ($,$t) {
        'use strict';
        return function (config, element) {

            var otpModalPopup = $(element),
                resendOtp,
                ajaxRequest,
                ajaxValidate,
                otpPopup = otpModalPopup.find('.otp_popup'),
                otpResponse = otpPopup.find('.otp_response'),
                otpExpireMessage = otpPopup.find('.otp_expire_message'),
                otpAction = otpPopup.find('.otp_action'),
                customerRegisterForm = $('#form-validate'),
                otpLoader = otpModalPopup.siblings('.otpLoader').loader(
                    {
                        icon: config.loaderUrl,
                    }
                ),
                validateError = otpPopup.find('.validate_error'),
                customerRegisterFormSubmitBtn = $('.action.submit.primary'),
                userOtp = otpAction.find('.user_otp'),
                submitOtp = otpAction.find('.submit_otp'),
                otpResendClass = 'otp_resend',
                otpModalPopupClass = 'otp_modal_popup',
                modalPopup = otpModalPopup.modal(
                    {
                        buttons: [{
                            text: config.resendText,
                            class: otpResendClass,
                            click: function () {
                                otpLoader.loader('show');
                                if (customerRegisterForm.valid()) {
                                    sendOtpAjax(1);
                                }
                            }
                        }],
                        modalClass: otpModalPopupClass,
                        clickableOverlay: false,
                        type: 'popup',
                        title: config.modalTitle
                    }
                );

            /**
             * Function to prevent typing alphabets and special character in otp input box
             */
            userOtp.keydown(
                function (e) {
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
                    // On Enter key validate OTP
                    if (e.keyCode === 13) {
                        submitOtp.trigger('click');
                    }
                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                }
            );

            customerRegisterFormSubmitBtn.on(
                'click', function (event) {
                    if (customerRegisterForm.valid()) {
                        otpResponse.removeClass('success');
                        otpResponse.removeClass('error');
                        otpLoader.loader('show');
                        event.preventDefault();
                        sendOtpAjax();
                    }
                }
            );

            resendOtp = otpModalPopup
                .closest('.' + otpModalPopupClass)
                .find('button.otp_resend');

            submitOtp.on(
                'click', function () {
                    validateError.addClass('display-none');
                    var otp = userOtp.val();
                    if (otp != "" && $.isNumeric(otp) && otp > 0) {
                        otpLoader.loader('show');
                        validateOtp();
                    } else {
                        validateError
                        .html("<span>"+config.validateNumberError+"</span>")
                        .removeClass('display-none');
                    }
                }
            );

            /**
             * Function to Send the Otp to the user
             */
            function sendOtpAjax(resendFlag = 0)
            {
                if (ajaxRequest && ajaxRequest.readyState != 4) {
                    ajaxRequest.abort();
                }
                var email = $('#email_address').val();
                var name = $('#firstname').val();
                var formKey = $.mage.cookies.get('form_key');
                if (config.isMobileOtpEnabled != "0" && config.isMobileOtpEnabled != " " && config.isMobileOtpEnabled) {
                    var countryCode = $('#country_codes').val(),
                        mobile = countryCode + $('#mobile').val();
                }

                ajaxRequest = jQuery.ajax(
                    {
                        url: config.otpAction,
                        data: {
                            'email': email,
                            'name': name,
                            'resend': resendFlag,
                            'form_key': formKey,
                            'mobile': mobile
                        },
                        showLoader: true,
                        async: true,
                        type: 'POST',
                    }
                ).done(
                    function (result) {
                        otpLoader.loader('hide');
                        if (result.error) {
                            $('html, body').animate({ scrollTop: 0 }, "slow"); // scroll to page top
                            validateError
                            .removeClass('display-none')
                            .html("<span>" + result.message + "</span>");
                            otpAction.hide();
                            otpExpireMessage.addClass('wk-otp-display-none');
                            resendOtp.addClass('wk-otp-display-none');
                        } else {
                            otpAction.show();
                            otpExpireMessage.removeClass('wk-otp-display-none');
                            validateError.addClass('display-none')
                            otpResponse
                            .addClass('success')
                            .html(result.message);
                            resendOtp.removeClass('wk-otp-display-none');
                            userOtp.val('');
                        }
                    }
                ).fail(
                    function (jqXHR, textResponse, errorThrown) {
                        otpAction.hide();
                        resendOtp.addClass('wk-otp-display-none');
                        otpExpireMessage.addClass('wk-otp-display-none');
                        validateError
                        .removeClass('display-none')
                        .html("<span>" + jqXHR.responseText + "</span>");
                    }
                ).always(
                    function () {
                        otpLoader.loader('hide');
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
                var email = $('#email_address').val(),
                    otp = userOtp.val(),
                    formKey = $.mage.cookies.get('form_key');
                ajaxValidate = jQuery.ajax(
                    {
                        url: config.otpValidateAction,
                        showLoader: true,
                        data: {
                            'email': email,
                            'user_otp': otp,
                            'form_key': formKey
                        },
                        async: true,
                        type: 'POST',
                    }
                ).done(
                    function (result) {
                        if (result.error) {
                            validateError
                            .removeClass('display-none')
                            .html("<span>" + result.message + "</span>");
                        } else {
                            modalPopup.modal('closeModal');
                            customerRegisterForm.submit();
                        }
                    }
                ).fail(
                    function (jqXHR, textResponse, errorThrown) {
                        validateError
                        .removeClass('display-none')
                        .html("<span>" + result.message + "</span>");
                    }
                ).always(
                    function () {
                        otpLoader.loader('hide');
                    }
                )
            }
        }
    }
);