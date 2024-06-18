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
    'Webkul_Otp/js/model/customer',
    'uiComponent',
    'mage/template',
    'Webkul_Otp/product/view/otpValidation',
    'mage/translate',
    'mage/cookies',
    'loader',
    'mage/mage',
    'domReady!',
    ], function ($, customer, Component, mageTemplate, otpValidation, $t) {
        'use strict';
        return Component.extend(
            {

                initialize: function (options, element) {
                    var paypalOtpText,
                    paypalBtnsDiv,
                    otpModalPopupContainer,
                    guestDetailsContainer,
                    guestDetailsContainerForm,
                    guestDetailsContainerTelephone,
                    guestDetailsContainerEmailAddress,
                    guestDetailsContainerValidationError,
                    guestDetailsContainerSubmitBtn,
                    otpContainer,
                    otpContainerForm,
                    otpContainerResponseMessage,
                    otpContainerInput,
                    otpContainerValidationError,
                    otpContainerSubmitBtn,
                    modalPopup,
                    ajaxRequest,
                    ajaxValidate,
                    otpLoader,
                    modalClass = 'paypalOtpModal',
                    otpResendBtnClass = 'otpResendBtn',
                    otpModalPopupTemplate;

                    customer.setCustomerData(options.customerData);
                    otpModalPopupTemplate = mageTemplate("#otpModalPopupTemplate")({});

                    $(element).append(otpModalPopupTemplate);
                    paypalOtpText = $(element).find('.paypalOtpBtn');
                    paypalBtnsDiv = paypalOtpText.siblings('.paypaldiv');
                    otpModalPopupContainer = paypalOtpText.siblings('.otpModalContainer');

                    // Guest Details Containers Jquery Dom Objects
                    guestDetailsContainer = otpModalPopupContainer.find('.guestDetailsContainer');
                    guestDetailsContainerForm = guestDetailsContainer.find('.guestDetailsContainer-form');
                    guestDetailsContainerTelephone = guestDetailsContainer.find('.guestDetailsContainer-telephone');
                    guestDetailsContainerEmailAddress = guestDetailsContainer.find('.guestDetailsContainer-emailAddress');
                    guestDetailsContainerValidationError = guestDetailsContainer.find('.guestDetailsContainer-validationError');
                    guestDetailsContainerSubmitBtn = otpModalPopupContainer.find(".guestDetailsContainer-submitBtn");

                    // Otp Containers Jquery Dom OBjects
                    otpContainer = otpModalPopupContainer.find('.otpContainer');
                    otpContainerForm = otpContainer.find('.otpContainer-form');
                    otpContainerResponseMessage = otpContainer.find('.otpContainer-responseMessage');
                    otpContainerInput = otpContainer.find('.otpContainer-input');
                    otpContainerValidationError = otpContainer.find('.otpContainer-validationError');
                    otpContainerSubmitBtn = otpContainer.find('.otpContainer-submitBtn');
                    otpContainerSubmitBtn.html(options.submitButtonText);

                    otpContainerForm.otpValidation({});
                    guestDetailsContainer.otpValidation({});

                    guestDetailsContainerSubmitBtn.html(options.submitButtonText);
                    otpContainer.find('.otpContainer-expireMessage').html(options.otpTimeToExpireMessage);
                    guestDetailsContainerTelephone.attr('placeholder', options.telephoneInputPlaceholder);
                    otpContainerInput.attr('placeholder', options.otpInputPlaceholder);

                    // Loader
                    otpLoader = $(element).find('.otp-loader').loader(
                        {
                            icon: options.loaderUrl,
                        }
                    );

                    // Popup
                    modalPopup = otpModalPopupContainer.modal(
                        {
                            buttons: [{
                                text: options.resendText,
                                class: otpResendBtnClass,
                                click: function (event) {
                                    sendOtpAjax(1, getEmailMobileObj(false));
                                }
                            }],
                            opened: function () {
                                otpModalPopupContainer.removeClass('display-none');
                            },
                            closed: function () {
                                otpModalPopupContainer.addClass('display-none');
                                guestDetailsContainerTelephone.val('');
                                guestDetailsContainerEmailAddress.val('');
                                otpContainerInput.val('');
                                otpContainer.addClass('display-none');
                                guestDetailsContainerForm.validation().validation('clearError');
                                guestDetailsContainerForm.find('.mage-error').removeClass('mage-error')
                                otpContainerForm.validation().validation('clearError');
                                otpContainerForm.find('.mage-error').removeClass('mage-error');
                            },
                            modalClass: modalClass,
                            clickableOverlay: false,
                            type: 'popup',
                            title: 'OTP Verification',
                        }
                    );

                    // Paypal text click event
                    paypalOtpText.on(
                        'click', function (event) {
                            $('body').trigger('click');
                            event.preventDefault();
                            otpModalPopupContainer
                            .parents('.' + modalClass)
                            .find('.' + otpResendBtnClass)
                            .addClass('display-none');
                    
                            var emailMobileObj = getEmailMobileObj();
                            if (emailMobileObj !== false) {
                                sendOtpAjax(0, emailMobileObj);
                            } else {
                                guestDetailsContainer.removeClass('display-none');
                                modalPopup.modal('openModal');
                            }
                        }
                    );

                    // Guest Details Submit button event
                    guestDetailsContainerSubmitBtn.click(
                        function (event) {
                            event.preventDefault();
                            var validation = guestDetailsContainerForm.validation();
                            if (!validation || !guestDetailsContainerForm.validation('isValid')) {
                                return false;
                            }
                            var emailMobileObj = getEmailMobileObj(false);
                            if (emailMobileObj !== false) {
                                sendOtpAjax(0, emailMobileObj);
                            } else {
                                guestDetailsContainer.removeClass('display-none');
                                guestDetailsContainerValidationError.removeClass('display-none');
                            }
                        }
                    );

                    // OTP Submit button event
                    otpContainerSubmitBtn.click(
                        function (event) {
                            event.preventDefault();
                            var validation = otpContainerForm.validation();
                            if (!validation || !otpContainerForm.validation('isValid')) {
                                return false;
                            }
                            var otp = otpContainerInput.val();
                            if (otp && $.isNumeric(otp) && otp > 0) {
                                if (guestDetailsContainerEmailAddress.val()) {
                                    validateOtp(guestDetailsContainerEmailAddress.val());
                                } else {
                                    validateOtp();
                                }
                            } else {
                                otpContainerValidationError
                                .removeClass('display-none')
                                .html(options.validateNumberError);
                            }
                        }
                    );

                    // OTP Keydown event
                    otpContainerInput.keydown(
                        function (e) {
                            // Allow: backspace, delete, tab, escape, enter and .
                            if ($.inArray(e.keyCode, [46, 8, 9, 13, 27, 110]) !== -1 
                                // Allow: Ctrl+A, Command+A
                                || (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) 
                                // Allow: home, end, left, right, down, up
                                || (e.keyCode >= 35 && e.keyCode <= 40)
                            ) {
                                return;
                            }
                            // Ensure that it is a number and stop the keypress
                            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                                e.preventDefault();
                            }
                        }
                    );

                    /**
                     * Can send Ajax Request
                     */
                    function canSendOtp(email, mobile)
                    {
                        if (options.isMobileOtpEnabled != "0" 
                            || options.isMobileOtpEnabled != " " 
                            || !options.isMobileOtpEnabled
                        ) {
                            if (options.isSendOtpEmailEnabled == "0" 
                                || options.isSendOtpEmailEnabled == " " 
                                || !options.isSendOtpEmailEnabled
                            ) {
                                guestDetailsContainerEmailAddress
                                    .removeAttr('data-validate')
                                    .closest('.addon')
                                    .addClass('display-none');
                                guestDetailsContainerTelephone
                                    .attr(
                                        'data-validate',
                                        '{required: true, "wk-otp-telephone": true}'
                                    )
                                    .closest('.addon')
                                    .removeClass('display-none')
                                    .find('label')
                                    .addClass('wk-otp-required');

                                return mobile ? true : false;
                            } else {
                                guestDetailsContainerEmailAddress
                                .attr(
                                    'data-validate',
                                    '{required: true, "validate-email": true}'
                                )
                                .closest('.addon')
                                .removeClass('display-none')
                                .find('label')
                                .addClass('wk-otp-required');
                                guestDetailsContainerTelephone
                                .attr(
                                    'data-validate',
                                    '{"wk-otp-telephone": true}'
                                )
                                .closest('.addon')
                                .removeClass('display-none')
                                .find('label')
                                .removeClass('wk-otp-required');

                                return email ? true : false;
                            }
                        } else {
                            guestDetailsContainerEmailAddress
                            .attr(
                                'data-validate',
                                '{required: true, "validate-email": true}'
                            )
                            .closest('.addon')
                            .removeClass('display-none')
                            .find('label')
                            .addClass('wk-otp-required');
                            guestDetailsContainerTelephone
                            .removeAttr('data-validate')
                            .closest('.addon')
                            .addClass('display-none');

                            return email ? true : false;
                        }
                    }

                    /**
                     * Get email and/or mobile
                     * 
                     * @returns {bool|object}
                     */
                    function getEmailMobileObj(preferCustomerFetch = true)
                    {
                        var email = customer.getCustomerEmail(),
                        mobile = customer.getCustomerTelephoneFromAddress(),
                        guestDetailsContainerEmailVal = guestDetailsContainerEmailAddress.val(),
                        guestDetailsContainerTelephoneVal = guestDetailsContainerTelephone.val();
                        email = email && preferCustomerFetch 
                        ? email
                        : (guestDetailsContainerEmailVal ? guestDetailsContainerEmailVal : email);
                        mobile = mobile && preferCustomerFetch
                        ? mobile
                        : (guestDetailsContainerTelephoneVal ? guestDetailsContainerTelephoneVal : mobile);
                        if (!canSendOtp(email, mobile)) {
                            return false;
                        }
                        return {
                            email: email,
                            mobile: mobile,
                        };

                    }

                    /**
                     * Function to Send the Otp to the user
                     */
                    function sendOtpAjax(resendFlag = 0, postData = {})
                    {
                        if (ajaxRequest && ajaxRequest.readyState != 4) {
                            ajaxRequest.abort();
                        }

                        var countryId = customer.getCustomerCountryIdFromAddress();
                        if (countryId) {
                            postData.region = countryId;
                        }
                        postData.name = customer.getCustomerFirstname() ? customer.getCustomerFirstname() : "Guest";
                        postData.form_key = $.mage.cookies.get('form_key');
                        postData.checkout = true;
                        postData.resend = resendFlag;

                        otpLoader.loader('show');
                        ajaxRequest = jQuery.ajax(
                            {
                                url: options.otpAction,
                                data: postData,
                                async: true,
                                showLoader: true,
                                type: 'POST',
                            }
                        ).done(
                            function (result) {
                                if (result.error) {
                                    guestDetailsContainerValidationError
                                    .html(result.message)
                                    .removeClass('display-none');
                                    guestDetailsContainer.removeClass('display-none');
                                    otpContainer.addClass('display-none');
                                } else if (!result.error && result.message) {
                                    otpContainerResponseMessage
                                    .addClass('success')
                                    .removeClass('error')
                                    .html(result.message);
                                    otpModalPopupContainer
                                    .parents('.' + modalClass)
                                    .find('.' + otpResendBtnClass)
                                    .removeClass('display-none');
                                    guestDetailsContainer.addClass('display-none');
                                    otpContainer.removeClass('display-none');
                                    otpContainerValidationError.addClass('display-none');
                                }
                            }
                        ).fail(
                            function () {
                                guestDetailsContainerValidationError
                                .html($t('Unable to send OTP. Please try again later.'))
                                .removeClass('display-none');
                                otpContainer.addClass('display-none');
                                guestDetailsContainer.removeClass('display-none');
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
                    function validateOtp(mailGuest = '')
                    {
                        if (ajaxValidate && ajaxValidate.readyState != 4) {
                            ajaxValidate.abort();
                        }
                        var email = mailGuest ? mailGuest : customer.getCustomerEmail(),
                        otp = otpContainerInput.val(),
                        formKey = $.mage.cookies.get('form_key');

                        otpLoader.loader('show');
                        ajaxValidate = jQuery.ajax(
                            {
                                url: options.otpValidateAction,
                                data: {
                                    'email': email,
                                    'user_otp': otp,
                                    'form_key': formKey
                                },
                                showLoader: true,
                                async: true,
                                type: 'POST',
                            }
                        ).done(
                            function (result) {
                                if (result.error && result.message) {
                                    otpContainerValidationError
                                    .removeClass('display-none')
                                    .html(result.message);
                                } else {
                                    otpContainerValidationError.addClass('display-none');
                                    paypalOtpText.addClass('display-none');
                                    paypalBtnsDiv.removeClass('disablePaypalDiv');
                                    otpContainerResponseMessage
                                    .addClass('success')
                                    .html(result.message);
                                    modalPopup.modal('closeModal');
                                }
                            }
                        ).fail(
                            function () {
                                otpContainerResponseMessage
                                .addClass('error')
                                .removeClass('success')
                                .html($t('Unable to validate OTP. Please try again later.'));
                                otpContainerValidationError
                                .addClass('display-none')
                                .html('');
                            }
                        ).always(
                            function () {
                                otpLoader.loader('hide');
                            }
                        );
                    }
                },
            }
        );
    }
);