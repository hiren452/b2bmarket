define([
    'jquery',
    'underscore',
    'uiRegistry',    
    'Magento_Ui/js/form/element/ui-select',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function ($, _, uiRegistry, select, modal, url) {
    'use strict';
    
    return select.extend({
		/**
         * On value change handler.
         *
         * @param {String} value
         */         
         initConfig:function (config) {
			 this._super();
			 if(this.selectedvalues){			 
			 var selectedOptionString =  this.selectedvalues;
			 var self = this;
		
			 if(selectedOptionString !=""){
				 
				 var selectedArr = selectedOptionString.split(',');
				 if(selectedArr.length){
					 $.each(selectedArr, function(index, value){						 
						 self.ajaxgetUom(value);
						 self.value.push(value);
					 });
				 }
			 }
		   } 		  
		 return;
		},
		
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value)
        {
			 
			 $('#umo').empty();
			 $('#umo').append('<option value="">Select</option>'); 
            if (value != 'undefined')
            {
              //Do your Ajx stuff here
              var ajaxUrl = url.build('noncatalogrequesttoquote/ajax/index');              
              $.ajax({
                url: ajaxUrl,
                showLoader: true,
                data: {form_key: window.FORM_KEY, 'cat_ids':value},
                type: "GET",
                dataType : 'json',
                success: function(result) {
					if(result.options.length){						
						var options = result.options;
						$.each(options, function( index, option ) {
							var optionValue = option.value;
							var optionText =  option.label;							
							$('#umo').append('<option value="'+optionValue+'">'+optionText+'</option>'); 
						
						});
						
					}
                 }
             });
              
            }
            return this._super();
        },
        ajaxgetUom: function(catId){
			var  ajaxUrl = url.build('noncatalogrequesttoquote/ajax/index');              
              $.ajax({
                url: ajaxUrl,
                showLoader: true,
                data: {form_key: window.FORM_KEY, 'cat_ids':catId},
                type: "GET",
                dataType : 'json',
                success: function(result) {
					if(result.options.length){						
						var options = result.options;
						$.each(options, function( index, option ) {
							var optionValue = option.value;
							var optionText =  option.label;
							$('#umo').append('<option value="'+optionValue+'">'+optionText+'</option>'); 						
						});						
					}
                 }
             });
		}
       
    });
});
