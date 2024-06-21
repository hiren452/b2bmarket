define(["jquery"], function ($) {
    'use strict';
    return function () {
        $(document).ready(function () {
            if ($("#country_id").val() === "US") {
                $(".img-div").hide();
                $(".dun_bradstreet").show();
            }
            $("#country_id").change(function () {
                if ($(this).val() === "US") {
                    $(".img-div").hide();
                    $(".dun_bradstreet").show();
                }
                if ($(this).val() !== "US") {
                    $(".img-div").show();
                    $(".dun_bradstreet").hide();
                }
            });
            let uploadField = $("#registration_document_upload");

            uploadField.on('change', function () {
                if (this.files[0].size > 2097152) {
                    alert("File is too big,should be less than or equal to 2 MB!");
                    this.value = "";
                }
            });
        });
    };
});
