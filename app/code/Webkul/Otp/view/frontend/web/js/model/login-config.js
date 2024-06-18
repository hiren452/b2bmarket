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
    'Webkul_Otp/js/action/post',
    'mage/cookies',
    ], function ($, sendPost) {
        'use strict';

        return {
            requestUrl: 'otp/customer/loginconfig',
        
            /**
             * Get Otp Login Component Config
             *
             * @returns {$.Deffered}
             */
            getConfig: function () {
                return sendPost(
                    {'form_key': $.mage.cookies.get('form_key')},
                    this.requestUrl,
                    false
                );
            }
        };
    }
);
