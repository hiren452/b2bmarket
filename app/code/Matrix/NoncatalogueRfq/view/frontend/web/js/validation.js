requirejs([
	'jquery',
	'jquery/ui',
	'jquery/validate',
	'mage/translate'
], function($){
	'use strict';
	$.validator.addMethod(
		"restrictUrl",
		function(value, element) {
			//return value.match(/^http([s]?):\/\*/) ||  value.match(/^www.[0-9a-zA-Z',-]./);
			if(new RegExp("([a-zA-Z0-9]+://)?([a-zA-Z0-9_]+:[a-zA-Z0-9_]+@)?([a-zA-Z0-9.-]+\\.[A-Za-z]{2,4})(:[0-9]+)?(/.*)?").test(value)) {
             return false;
            }else {
				return true;
			}
			
		},
		$.mage.__("No URLs/Email allowed!")
	);
});
