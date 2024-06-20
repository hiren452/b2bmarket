define(["jquery","Magento_Ui/js/modal/modal", "Magento_Ui/js/modal/confirm"], function ($,modal, confirmation) {
    'use strict';
    return function () {
        $(".reply-negotiation").click(function(){
            var negotiotion = $(this).data("negotiotion");
            $('#negotiated_qty').html(negotiotion.custom.negotiated_qty);
            $('#target_qty').html(negotiotion.custom.target_qty);
            $('#target_price').html(negotiotion.custom.target_price);
            $('#negotiated_price').html(negotiotion.custom.negotiation_price);
            $('#lead_time').html(negotiotion.custom.lead_time);
            $('#negotiation_leadtime').html(negotiotion.custom.negotiation_leadtime);
            $("#negotiate_id").val(negotiotion.id);
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: false,
                title:'Send Your Negotiation',
                buttons: [{
                    text: 'Close',
                    class: 'supplier-modal1',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };
            var popup = modal(options, $('#negotiate-popup'));
            $("#negotiate-popup").modal("openModal");
        });
    };
});
