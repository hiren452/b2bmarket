define(['jquery'], function ($) {
    'use strict';
    return function (config) {
        $("#form-validate").on("submit", function (event) {
            if ($(this).valid()) {
                if (config.config) {
                    event.preventDefault();
                    let requiredFields = JSON.parse(config.config);
                    let finalResult = {};
                    for (let name in requiredFields) {
                        let fieldName = 'input[name=' + requiredFields[name] + ']';
                        finalResult[name] = true;
                        if (!$(fieldName).val().length) {
                            finalResult[name] = false;
                            $(fieldName).prop('required', true);
                        }
                    }
                    for (let obj in finalResult) {
                        if (!finalResult[obj]) {
                            $(this).valid();
                            return false;
                        }
                    }
                    $("#form-validate").unbind('submit').submit();
                }
            }
        });
    };
});
