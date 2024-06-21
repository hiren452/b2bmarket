define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';
    return function () {
        let allowedExtensions = ['txt', 'pdf'];
        $.validator.addMethod(
            "multistepregvalidationrule",
            function (value) {
                let resField = value;
                let extension = resField.substr(resField.lastIndexOf('.') + 1).toLowerCase();
                return !(resField.length > 0 && allowedExtensions.indexOf(extension) === -1);
            },
            $.mage.__('Invalid file Format. Only ' + allowedExtensions.join(', ') + ' are allowed.')
        );
    }
});
