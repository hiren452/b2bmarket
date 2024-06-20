var config = {
    "shim": {
        "extjs/ext-tree": [
            "prototype"
        ],
        "extjs/ext-tree-checkbox": [
            "extjs/ext-tree",
            "extjs/defaults"
        ]
    },
    map: {
             "*": {
                   noUrlValidation: "Matrix_NoncatalogueRfq/js/validation",
                   noEmailValidation: "Matrix_NoncatalogueRfq/js/noemail-validation",
                   
          }
    }
        
    /*"map": {
        '*' : {
               
               'myfileuploader': 'Matrix_NoncatalogueRfq::js/jquery/jquery.fileupload'               
             }
    },"shim": {
        'myfileuploader': ['jquery']
    }*/
};
