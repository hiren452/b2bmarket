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
    'mage/storage',
    'mage/translate'
    ], function ($, storage, $t) {
        'use strict';

        /**
         * @param   {Object} request
         * @param   {String} requestUrl
         * @param   {*} isGlobal
         * @returns $.Deffered
         */
        var action = function (request, requestUrl, isGlobal) {
            return storage.post(
                requestUrl,
                JSON.stringify(request),
                isGlobal
            );
        };

        return action;
    }
);
