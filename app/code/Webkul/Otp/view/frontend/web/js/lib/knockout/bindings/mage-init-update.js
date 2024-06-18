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
    'ko',
    'underscore',
    'mage/apply/main'
    ], function (ko, _, mage) {
        'use strict';

        ko.bindingHandlers.mageInitUpdate = {
            /**
             * Initializes components assigned to HTML elements.
             *
             * @param {HTMLElement} el
             * @param {Function} valueAccessor
             */
            init: function (el, valueAccessor) {
                var data = valueAccessor();

                _.each(
                    data, function (config, component) {
                        mage.applyFor(el, config, component);
                    }
                );
            },

            /**
             * Updates components assigned to HTML elements.
             *
             * @param {HTMLElement} el
             * @param {Function} valueAccessor
             */
            update: function (el, valueAccessor) {
                var data = valueAccessor();
                data = ko.unwrap(data);
                if (typeof data === 'string') {
                    data = JSON.parse(data);
                }
                _.each(
                    data, function (config, component) {
                        mage.applyFor(el, config, component);
                    }
                );
            }
        };
    }
);