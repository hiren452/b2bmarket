define([
'jquery',
'mage/translate',
'select2'
], function ($, $t, select2) {
    'use strict';

    $.widget('mage.privateform',{
        options: {
            nonBuyerFormHtml : ''
        },

        _create: function() {
            var self = this;
            $('.select2').select2();

            $('.add_more').click(function () {
               var html = '<div style="border-top:1px dotted #ddd; margin:5px 0;">';
               html += self.getNonBuyerHtml();
               html += '<div class="remove" style="text-align:right">';
               html += '<a href="javascript:void(0);" class="button btn btn-success btn-right btn-remove">';
               html += $t('Remove');
               html += '</a></div></div>';
               $('.add_more_buyer').append(html);
            });

            $(document).on('click', '.btn-remove', function () {
                $(this).parent().parent().remove();
            });

            $('#save_butn').click(function() {
                $('#form-validate').submit()
            });

            $('#form-validate').mage('validation', {});
        },

        getNonBuyerHtml: function () {
            if (this.options.nonBuyerFormHtml != ''){
                return this.options.nonBuyerFormHtml;
            }
                var completeDiv = $('.add-nonbuyer-form').last().clone();
                completeDiv.find('input[type=text]').each(function () {
                    $(this).attr('value', '');
                });
                this.options.nonBuyerFormHtml = completeDiv.html();
            return completeDiv.html();
        }
    });

    return $.mage.privateform;

});
