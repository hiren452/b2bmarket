    define([
    'jquery',
    'uiComponent',    
    'ko',
    'mage/url',
    'owl.carousel/owl.carousel.min'
    ], function($,Component, ko, url) {
 
        var title = 'Non-Catalog Product';              
        var productList = ko.observableArray();                 
        var product = function  product(data){			
              var self =  this;	
              this.item =   data;              
              var uploadsFils =  typeof(data.uploads) !== 'undefined' ? data.uploads:[];
              this.item.uploadsFiles = ko.observable($.parseJSON(uploadsFils));	        
        };     
        return Component.extend({	
             defaults: {
                template: 'Matrix_NoncatalogueRfq/productList',
                data: [],
             },
             title: title,
             product: product,
             productList: productList,
             initialize: function(){                 
                 this._super();		
                 var self =  this;                 
                 /**
                  * define Product class 
                  */
                 // function to populate Product list with Prodect objects
                 var populateProducts = function (data) {
                 var self =  this;	
                 if(data.totalRecords>0)
                  {
					 $.each(data.items, function( index, item ) {						 
						 productList([new product(item)]);						 
					 });					 
				  }                                
                 };                                  
                 populateProducts(self.data);                 
                 ko.bindingHandlers.my_products = {
                   init: function(element, valueAccessor, allBindingsAccessor, viewModel) {                  
                  },
                 update: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                    }
                    
                };                
                $("#owl-rfqproducts").owlCarousel({
				margin: 0,
				nav: true,				
				dots: true,
				responsive: {
					0: {
						items:2
					},
					768: {
						items:3
					},
					992: {
						items:4
					},
					1200: {
						items:6
					}
				  }
			  });
                return this;
               },
             removeProduct : function(product) {
                var self =  this;                
                if(confirm('Are you sure')){
                     productList.remove(product);
                 }                 
             },                       
            addProductForm: function(){
                this.name =  ko.observable(true);
                this.price =  ko.observable(true);
            },
			showProduct: function(element){
				if (element.nodeType === 1) {
                    $(element).fadeOut(function() { $(element).remove(); });
                  }
			}
      });

    });
