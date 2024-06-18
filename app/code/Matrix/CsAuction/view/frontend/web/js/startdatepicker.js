require([
    'jquery',
    'moment',
    'jquery/ui',
], function ($) {
    return function (config) {
        let timezone = config.timezone;
            $('.ui-datepicker-current').live('click', function (id) {
                $.datepicker._curInst.input.datepicker('setDate', timezone);
            });
        /*@Note: to hide upon clicking the Now button*/
        $(document).on('click', '.ui-datepicker-current', function () {
            $('.ui-datepicker-close').click();
        })
    }
});

