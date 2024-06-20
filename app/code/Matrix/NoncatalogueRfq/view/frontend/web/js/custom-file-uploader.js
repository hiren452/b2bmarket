define([
    'jquery',
    'Magento_Ui/js/form/element/file-uploader'
], function ($, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            fileInputName: 'noncatalogrfqupload'
        },
         /**
         * Initializes file uploader plugin on provided input element.
         *
         * @param {HTMLInputElement} fileInput
         * @returns {FileUploader} Chainable.
         */
        initUploader: function (fileInput) {
			var self =  this;
			if(this.savedFiles){
			var savedFiles =  this.savedFiles;
            if(savedFiles.length){
				$.each(savedFiles, function(index, file){					
					self.addFile(file);
				});
			}
		    }
			return this._super();
		},
        /**
         * Handler of the file upload complete event.
         *
         * @param {Event} e
         * @param {Object} data
         */
        onFileUploaded: function (e, data) {
            this._super(e, data);            
            var response = data.result; // Here the response data are stored             
        },
         /**
         * Adds provided file to the files list.
         *
         * @param {Object} file
         * @returns {FileUploader} Chainable.
         */
        addFile: function (file) {
            file = this.processFile(file);
            this.isMultipleFiles ?
                this.value.push(file) :
                this.value([file]);

            return this;
        },        
    });
});
