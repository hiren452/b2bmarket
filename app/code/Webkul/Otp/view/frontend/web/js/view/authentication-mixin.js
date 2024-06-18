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
    'jquery',
    'Webkul_Otp/js/model/login-config',
    'ko',
    'uiRegistry',
    ], function ($, loginConfig, ko, registry) {
        'use strict';

        var loginConfigObservable = ko.observable({});

        var mixin = {
            defaults: {
                template: 'Webkul_Otp/authentication'
            },

            /**
             * Initialize Component
             */
            initialize: function () {
                this._super();
                loginConfig
                .getConfig()
                .done(
                    function (response) {
                        if (!response.error) {
                            loginConfigObservable(response.data);
                        }
                    }.bind(this)
                );
            
                return this;
            },

            /**
             * Returns Username Field Label
             * 
             * @returns {String}
             */
            getUsernameLabel: function () {
                return ko.computed(
                    function () {
                        var loginConfigData = loginConfigObservable();
                        if (loginConfigData.hasOwnProperty('usernameFieldConfig')) {
                            return loginConfigData.usernameFieldConfig.label;
                        }

                        return 'Email';
                    }
                );
            },

            /**
             * @returns {String}
             */
            getUsernameType: function () {
                return ko.computed(
                    function () {
                        var loginConfigData = loginConfigObservable();
                        if (loginConfigData.hasOwnProperty('usernameFieldConfig')) {
                            return loginConfigData.usernameFieldConfig.type;
                        }

                        return 'email';
                    }
                );
            },

            /**
             * @returns {String}
             */
            getUsernameDataValidate: function () {
                return ko.computed(
                    function () {
                        var loginConfigData = loginConfigObservable();
                        if (loginConfigData.hasOwnProperty('usernameFieldConfig')) {
                            return loginConfigData.usernameFieldConfig.dataValidate;
                        }

                        return '{required: true, "validate-email": true}';
                    }
                );
            },

            /**
             * Returns Otp modal config
             * 
             * @returns {Object}
             */
            getOtpModalComponent: function () {
                return ko.computed(
                    function () {
                        var loginConfigData = loginConfigObservable();
                        if (loginConfigData.hasOwnProperty('otpModalComponent')) {
                            return JSON.stringify(loginConfigData.otpModalComponent);
                        }

                        return "{}";
                    }
                );
            },

            /**
             * @inheritdoc
             */
            login: function (loginForm) {
                var otpModalConfig = loginConfigObservable().hasOwnProperty('otpModalComponent') ?
                Object.values(loginConfigObservable().otpModalComponent).pop() :
                {},
                isModuleEnabled = otpModalConfig.hasOwnProperty('isModuleEnabled') ? Number(otpModalConfig.isModuleEnabled) : false;
                if (!isModuleEnabled || isModuleEnabled && registry.get('wk_otp_submit_form') == "1") {
                    registry.remove('wk_otp_submit_form');
                    this._super(loginForm);
                }
            }
        };

        return function (target) {
            return target.extend(mixin);
        };
    }
);
