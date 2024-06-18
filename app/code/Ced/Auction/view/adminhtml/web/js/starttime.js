define(
    ['Magento_Ui/js/form/element/abstract'],function (Abstract) {
        return Abstract.extend(
            {




                /**
                 * Change currently selected color
                 *
                 * @param {String} color
                 */
                selectDate: function () {

                    return this.source.data.sellproduct;
                },

                selectOption: function () {
                    if((this.source.data.sellproduct == 'yes')||this.source.data.sellproduct == undefined) {
                        $('div[data-index="max_price"]').hide();

                    } else if(this.source.data.sellproduct == 'no') {
                        $('div[data-index="max_price"]').show();

                    }
                },
            }
        );
    }
);