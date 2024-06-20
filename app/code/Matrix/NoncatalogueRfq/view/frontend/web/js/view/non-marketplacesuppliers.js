define([
    'jquery',
    'uiComponent',
    'uiRegistry',
    'ko',
    'mage/url',
    'Magento_Ui/js/modal/modal'
], function($, Component, registry, ko, url, modal) {
    var title = 'Non-marketplace Suppliers';
    var suppliersList = ko.observableArray();
    var supplier = function
        supplier(
        companyname,
        email,
        phone,
        url,
        address
    ) {
        var self = this;
        this.companyname = ko.observable(companyname);
        this.email = ko.observable(email);
        this.phone = ko.observable(phone);
        this.url = ko.observable(url);
        this.address = ko.observable(address);

        this.formattedName = ko.computed(function() {
            return '<strong>' + this.companyname() + '</strong>';
        }, this);
    };

    return Component.extend({
        initConfig: function(config) {
            this._super();
            var self = this;
            this.maxlimit = this.maxlimit;
            return;
        },
        defaults: {
            template: 'Matrix_NoncatalogueRfq/suppliers/nonmkt-suppliersList'
        },
        title: title,
        supplier: supplier,
        suppliersList: suppliersList,
        initialize: function() {
            this._super();
            var self = this;
            self.suppliersList.subscribe(function(changes) {
                var arrSuplier = new Array();
                self.updateStorege();
                changes.forEach(function(change) {
                    if (change.status === 'added' || change.status === 'deleted') {
                        var data = {
                            "companyname": change.value.companyname(),
                            "email": change.value.email(),
                            "phone": change.value.phone(),
                            "url": change.value.url(),
                            "address": change.value.address(),
                        };
                        arrSuplier.push(JSON.stringify(data));
                    }
                });
            }, null, "arrayChange");
            self.populateList();
            self.updateStorege();
            return this;
        },

        removeSupplier: function(supplier) {
            var self = this;
            if (confirm('Are you sure')) {
                suppliersList.remove(supplier);
            }
        },
        editSupplier: function(supplier, event) {
            var self = this;
            var context = ko.contextFor(event.target);
            var itemIndex = context.$index();
            
            $("#supplierinfoadd-form").trigger("reset"); //Clear The Popup Form
            $("#supplierinfoadd-form input[name=companyname]").val(supplier.companyname());
            $("#supplierinfoadd-form input[name=email]").val(supplier.email());
            $("#supplierinfoadd-form input[name=telephone]").val(supplier.phone());
            $("#supplierinfoadd-form input[name=companyurl]").val(supplier.url());
            $("#supplierinfoadd-form textarea[name=address]").val(supplier.address());
            $("#supplierinfoadd-form input[id=nonmktvendorid]").val(itemIndex);
            $("#supplierinfoadd_submit").html('<span>Update Supplier</span>');
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: false,
                modalClass: 'supplierselect-popupmodal',
                buttons: [{
                    text: $.mage.__('Close'),
                    class: 'supplier-modal1',
                    click: function() {
                        this.closeModal();
                    }
                }]
            };
            var popup = modal(options, $('#mktsupplierselect-popupmodal'));
            $("#mktsupplierselect-popupmodal").modal("openModal");
        },
        updateSupplier: function(data) {
            var self = this;
            var companyName = data.companyname;
            var email = data.email;
            var phone = data.telephone;
            var url = data.companyurl;
            var address = data.address;

            //var oldSupplier = self.suppliersList(0);
            var itemIndex = $("#supplierinfoadd-form input[id=nonmktvendorid]").val();

            let isEmailExist = false;
            let isExistItemIndex = 0;
            $.each(suppliersList(), function(index, value) {
                if( email == value.email()){
                    isEmailExist =  true;
                    isExistItemIndex = index;
                    return false;
                }

            });
            if(isEmailExist && isExistItemIndex!=itemIndex ){
                alert(email +' already exist');
                return false;

            }


            if (itemIndex != '') {
                suppliersList.remove(this.suppliersList()[itemIndex]);
            }
            var updateSupplier = new supplier(companyName, email, phone, url, address);
            suppliersList.splice(itemIndex,0,updateSupplier);
            //suppliersList.push(updateSupplier);
            $("#supplierinfoadd-form input[id=nonmktvendorid]").val(null);
            return true;
        },
        openMoreSupplierPopup: function() {
            $("#vendoremailalert").html('');
            $('#email-error').remove();
            $('#supplierinfoadd_submit').prop('disabled',false);
            var self = this;
            if(this.maxlimit>0){
                if( self.suppliersList().length >= this.maxlimit){
                    alert("No more Non-marketplace Suppliers allowed.");
                    return;
                }
            }

            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: false,
                modalClass: 'supplierselect-popupmodal',
                buttons: [{
                    text: $.mage.__('Close'),
                    class: 'supplier-modal1',
                    click: function() {
                        this.closeModal();
                    }
                }]
            };
            $("#supplierinfoadd_submit").html('<span>Create Supplier</span>');
            var popup = modal(options, $('#mktsupplierselect-popupmodal'));
            $("#supplierinfoadd-form").trigger("reset");
            $("#mktsupplierselect-popupmodal").modal("openModal");
        },
        addSupplier: function(data) {
            var companyName = data.companyname;
            var email = data.email;
            var phone = data.telephone;
            var url = data.companyurl;
            var address = data.address;
            var newSupplier = new supplier(companyName, email, phone, url, address);
            let isEmailExist = false;
            $.each(suppliersList(), function(index, value) {
                if( email == value.email()){
                    isEmailExist =  true;
                    return false;
                }

            });
            if(!isEmailExist){
                suppliersList.push(newSupplier);
                return true;
            }else {
                alert(email +' already exist');
                return false;
            }

        },
        hideSupplier: function(element) {
            if (element.nodeType === 1) {
                $(element).hide().fadeIn();
            }
        },
        showPSupplier: function(element) {
            if (element.nodeType === 1) {
                $(element).fadeOut(function() {
                    $(element).remove();
                });
            }
        },
        shouldShowList: function() {
            var self = this;
            return true;
            /*if(self.suppliersList().length){
				   return true;
			   } else {
				   return false;
			   }*/
        },
        updateStorege: function() {
            var self = this;
            var arrSuplier = new Array();
            $.each(self.suppliersList(), function(index, value) {
                var data = {
                    "companyname": value.companyname(),
                    "email": value.email(),
                    "phone": value.phone(),
                    "url": value.url(),
                    "address": value.address()
                };
                arrSuplier.push(JSON.stringify(data));
            });
        },
        populateList: function() {

            if (this.selectedvalues) {
                var self = this;
                var arrStoreSupplie = self.selectedvalues;
                $.each(arrStoreSupplie, function(index, data) {
                    data.telephone = data.phone;
                    self.addSupplier(data);
                });
            }
        }
    });

});
