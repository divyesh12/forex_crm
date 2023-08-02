/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Settings_Vtiger_CompanyDetails_Js",{},{
    
    init : function() {
       this.addComponents();
    },
   
    addComponents : function (){
      this.addModuleSpecificComponent('Index', app.module, app.getParentModuleName());
    },
    
	registerUpdateDetailsClickEvent : function() {
		jQuery('#updateCompanyDetails').on('click',function(e){
			jQuery('#CompanyDetailsContainer').addClass('hide');
			jQuery('#updateCompanyDetailsForm').removeClass('hide');
            jQuery('#updateCompanyDetails').addClass('hide');
		});
	},
	
	registerSaveCompanyDetailsEvent : function() {
		var thisInstance = this;
		var form = jQuery('#updateCompanyDetailsForm');
		var params = {
			submitHandler : function(form) {
				var form = jQuery(form);
				var result = thisInstance.checkValidation();
				var Favicon_result = thisInstance.checkValidation_ForFavicon();
            	var Banner_result = thisInstance.checkValidation_ForBanner();
					
	            if ((Favicon_result == false)) {
	                return Favicon_result;
	                e.preventDefault();
	            }
	            if ((Banner_result == false)) {
	                return Banner_result;
	                e.preventDefault();
	            }
				if ((result == false)) {
	                return result;
	                e.preventDefault();
	            }
				else {
					return true;
				}
			}
		};
		form.vtValidate(params);
	},
	
	registerCancelClickEvent : function () {
		jQuery('.cancelLink').on('click',function() {
			jQuery('#CompanyDetailsContainer').removeClass('hide');
			jQuery('#updateCompanyDetailsForm').addClass('hide');
            jQuery('#updateCompanyDetails').removeClass('hide');
		});
	},

	WidthHeight_logoName: function () {
        var thisInstance = this;
        jQuery('#logoFile').on('change', function (e) {
            //Get reference of FileUpload.
            var fileUpload = jQuery("#logoFile")[0];

            //Check whether HTML5 is supported.
            if (typeof (fileUpload.files) != "undefined") {
                //Initiate the FileReader object.
                var reader = new FileReader();
                //Read the contents of Image File.
                reader.readAsDataURL(fileUpload.files[0]);
                reader.onload = function (e) {
                    //Initiate the JavaScript Image object.
                    var image = new Image();
                    //Set the Base64 string return from FileReader as source.
                    image.src = e.target.result;
                    image.onload = function () {
                        //Determine the Height and Width.
                        var height = this.height;
                        var width = this.width;
                        jQuery("#logoNameWidth").val(width);
                        jQuery("#logoNameHeight").val(height);
                    };
                }
            }
        });
    },

    WidthHeight_logoNameCabinet: function () {
        var thisInstance = this;
        jQuery('#logoFileCabinet').on('change', function (e) {
            //Get reference of FileUpload.
            var fileUpload = jQuery("#logoFileCabinet")[0];

            //Check whether HTML5 is supported.
            if (typeof (fileUpload.files) != "undefined") {
                //Initiate the FileReader object.
                var reader = new FileReader();
                //Read the contents of Image File.
                reader.readAsDataURL(fileUpload.files[0]);
                reader.onload = function (e) {
                    //Initiate the JavaScript Image object.
                    var image = new Image();
                    //Set the Base64 string return from FileReader as source.
                    image.src = e.target.result;
                    image.onload = function () {
                        //Determine the Height and Width.
                        var height = this.height;
                        var width = this.width;
                        jQuery("#logoNameWidthCabinet").val(width);
                        jQuery("#logoNameHeightCabinet").val(height);
                    };
                }
            }
        });
    },
	
	checkValidation : function() {
		var imageObj = jQuery('#logoFile');
		var imageName = imageObj.val();
		if(imageName != '') {
			var image_arr = new Array();
			image_arr = imageName.split(".");
			var image_arr_last_index = image_arr.length - 1;
			if(image_arr_last_index < 0) {
				app.helper.showErrorNotification({'message' : app.vtranslate('LBL_WRONG_IMAGE_TYPE')});
				imageObj.val('');
				return false;
			}
			var image_extensions = JSON.parse(jQuery('#supportedImageFormats').val());
			var image_ext = image_arr[image_arr_last_index].toLowerCase();
			if(image_extensions.indexOf(image_ext) != '-1') {
				var size = imageObj[0].files[0].size;
				if (size < 1024000) {
					return true;
				} else {
					app.helper.showErrorNotification({'message' : app.vtranslate('LBL_MAXIMUM_SIZE_EXCEEDS')});
					return false;
				}
			} else {
				app.helper.showErrorNotification({'message' : app.vtranslate('LBL_WRONG_IMAGE_TYPE')});
				imageObj.val('');
				return false;
			}
	
		}
	},

	checkValidation_ForBanner: function () {
        var imageObj = jQuery('#bannerFile');
        var imageName = imageObj.val();
        if (imageName != '') {
            var image_arr = new Array();
            image_arr = imageName.split(".");
            var image_arr_last_index = image_arr.length - 1;
            if (image_arr_last_index < 0) {
                imageObj.validationEngine('showPrompt', app.vtranslate('LBL_WRONG_IMAGE_TYPE'), 'error', 'topLeft', true);
                imageObj.val('');
                return false;
            }
            var image_extensions = JSON.parse(jQuery('#bannerSupportedFormats').val());
            var image_ext = image_arr[image_arr_last_index].toLowerCase();
            if (image_extensions.indexOf(image_ext) != '-1') {
                var size = imageObj[0].files[0].size;
                if (size < 5120000) {
                    return true;
                } else {
                    imageObj.validationEngine('showPrompt', app.vtranslate('LBL_MAXIMUM_SIZE_EXCEEDS_BANNERIMAGE'), 'error', 'topLeft', true);
                    return false;
                }
            } else {
                imageObj.validationEngine('showPrompt', app.vtranslate('LBL_WRONG_IMAGE_TYPE'), 'error', 'topLeft', true);
                imageObj.val('');
                return false;
            }

        }
    },

    WidthHeight_faviconName: function () {
        var thisInstance = this;
        jQuery('#faviconFile').on('change', function (e) {
            //Get reference of FileUpload.
            var fileUpload = jQuery("#faviconFile")[0];
            //Check whether HTML5 is supported.
            if (typeof (fileUpload.files) != "undefined") {
                //Initiate the FileReader object.
                var reader = new FileReader();
                //Read the contents of Image File.
                reader.readAsDataURL(fileUpload.files[0]);
                reader.onload = function (e) {
                    //Initiate the JavaScript Image object.
                    var image = new Image();
                    //Set the Base64 string return from FileReader as source.
                    image.src = e.target.result;
                    image.onload = function () {
                        //Determine the Height and Width.
                        var height = this.height;
                        var width = this.width;
                        jQuery("#faviconNameWidth").val(width);
                        jQuery("#faviconHeight").val(height);
                    };
                }
            }
        });
    },

    WidthHeight_faviconNameCabinet: function () {
        var thisInstance = this;
        jQuery('#faviconFileCabinet').on('change', function (e) {
            //Get reference of FileUpload.
            var fileUpload = jQuery("#faviconFileCabinet")[0];
            //Check whether HTML5 is supported.
            if (typeof (fileUpload.files) != "undefined") {
                //Initiate the FileReader object.
                var reader = new FileReader();
                //Read the contents of Image File.
                reader.readAsDataURL(fileUpload.files[0]);
                reader.onload = function (e) {
                    //Initiate the JavaScript Image object.
                    var image = new Image();
                    //Set the Base64 string return from FileReader as source.
                    image.src = e.target.result;
                    image.onload = function () {
                        //Determine the Height and Width.
                        var height = this.height;
                        var width = this.width;
                        jQuery("#faviconNameWidthCabinet").val(width);
                        jQuery("#faviconNameHeightCabinet").val(height);
                    };
                }
            }
        });
    },

    checkValidation_ForFavicon: function () {
        var imageObj = jQuery('#faviconFile');
        var imageName = imageObj.val();
        var faviconWidth = jQuery("#faviconNameWidth").val();
        var faviconHeight = jQuery("#faviconNameWidth").val();

        if (imageName != '') {
            var image_arr = new Array();
            image_arr = imageName.split(".");
            var image_arr_last_index = image_arr.length - 1;
            if (image_arr_last_index < 0) {
                imageObj.validationEngine('showPrompt', app.vtranslate('LBL_WRONG_IMAGE_TYPE'), 'error', 'topLeft', true);
                imageObj.val('');
                return false;
            }
            var image_extensions = JSON.parse('ico');
            var image_ext = image_arr[image_arr_last_index].toLowerCase();

            if (image_extensions.indexOf(image_ext) != '-1') {
                if (faviconWidth < 16 || faviconHeight < 16) {
                    imageObj.validationEngine('showPrompt', app.vtranslate('LBL_MAXIMUM_FAVICON_PIXEL_SIZE'), 'error', 'topLeft', true);
                    return false;
                }
                var size = imageObj[0].files[0].size;
                if (size < 500000) {
                    return true;
                } else {
                    imageObj.validationEngine('showPrompt', app.vtranslate('LBL_MAXIMUM_SIZE_EXCEEDS_FAVICON'), 'error', 'topLeft', true);
                    return false;
                }
            } else {
                imageObj.validationEngine('showPrompt', app.vtranslate('LBL_WRONG_IMAGE_TYPE'), 'error', 'topLeft', true);
                imageObj.val('');
                return false;
            }

        }
    },
    
    registerCompanyLogoDimensionsValidation : function() {
        //200*50 logo with padding would be nice
        var allowedDimensions = {
            'width' : 200,
            'width1' : 150,
            'height' : 50,
            'height1' : 40
        };
        var updateCompanyDetailsForm = jQuery('form#updateCompanyDetailsForm');
        var logoFile = updateCompanyDetailsForm.find('#logoFile');
        logoFile.on('change', function() {
            //http://stackoverflow.com/a/13572209
            var _URL = window.URL || window.webkitURL;
            var image, file = this.files[0];
            if(file && typeof Image === 'function') {
                image = new Image();
                image.onload = function() {
                    var width = this.width;
                    var height = this.height;
                    if((width !== allowedDimensions.width || height !== allowedDimensions.height) && (width !== allowedDimensions.width1 || height !== allowedDimensions.height1) ) {
                        app.helper.showErrorNotification({
                            'message' : app.vtranslate('JS_LOGO_IMAGE_DIMENSIONS_WRONG')
                        });
                        logoFile.val(null); //this will empty file input
                    }
                };
                image.src = _URL.createObjectURL(file);
            }
        });
    },  

    registerCompanyLogoCabinetDimensionsValidation : function() {
        //200*50 logo with padding would be nice
        var allowedDimensions = {
            'width' : 200,
            'width1' : 150,
            'height' : 50,
            'height1' : 40
        };
        var updateCompanyDetailsForm = jQuery('form#updateCompanyDetailsForm');
        var logoFileCabinet = updateCompanyDetailsForm.find('#logoFileCabinet');
        logoFileCabinet.on('change', function() {
            //http://stackoverflow.com/a/13572209
            var _URL = window.URL || window.webkitURL;
            var image, file = this.files[0];
            if(file && typeof Image === 'function') {
                image = new Image();
                image.onload = function() {
                    var width = this.width;
                    var height = this.height;
                    if((width !== allowedDimensions.width || height !== allowedDimensions.height) && (width !== allowedDimensions.width1 || height !== allowedDimensions.height1) ) {
                        app.helper.showErrorNotification({
                            'message' : app.vtranslate('JS_LOGO_IMAGE_DIMENSIONS_WRONG')
                        });
                        logoFileCabinet.val(null); //this will empty file input
                    }
                };
                image.src = _URL.createObjectURL(file);
            }
        });
    },    

    registerCompanyFaviconDimensionsValidation : function() {
        var allowedDimensions = {
            'width' : 16,
            'height' : 16
        };
        var updateCompanyDetailsForm = jQuery('form#updateCompanyDetailsForm');
        var faviconFile = updateCompanyDetailsForm.find('#faviconFile');
        faviconFile.on('change', function() {
            var _URL = window.URL || window.webkitURL;
            var image, file = this.files[0];

        	var imageName = faviconFile.val();
            var image_arr = new Array();
            image_arr = imageName.split(".");
            var image_arr_last_index = image_arr.length - 1;

            var image_extensions = JSON.parse(jQuery('#faviconSupportedFormats').val());
            var image_ext = image_arr[image_arr_last_index].toLowerCase();

            if (image_extensions.indexOf(image_ext) != '-1') {

	            if(file && typeof Image === 'function') {
	                image = new Image();
	                image.onload = function() {
	                    var width = this.width;
	                    var height = this.height;
	                    if(width > allowedDimensions.width || height > allowedDimensions.height ) {
	                        app.helper.showErrorNotification({
	                            'message' : app.vtranslate('JS_FAVICON_IMAGE_DIMENSIONS_WRONG')
	                        });
	                        faviconFile.val(null); //this will empty file input
	                    }
	                };
	                image.src = _URL.createObjectURL(file);
	            }

	        } else {
	        	image = new Image();
	        	app.helper.showErrorNotification({
                    'message' : app.vtranslate('JS_FAVICON_IMAGE_TYPE_WRONG')
                });
                faviconFile.val(null);
                image.src = _URL.createObjectURL(file);
            }

        });
    }, 

    registerCompanyFaviconCabinetDimensionsValidation : function() {
        var allowedDimensions = {
            'width' : 16,
            'height' : 16
        };
        var updateCompanyDetailsForm = jQuery('form#updateCompanyDetailsForm');
        var faviconFileCabinet = updateCompanyDetailsForm.find('#faviconFileCabinet');
        faviconFileCabinet.on('change', function() {
            var _URL = window.URL || window.webkitURL;
            var image, file = this.files[0];

            var imageName = faviconFileCabinet.val();
            var image_arr = new Array();
            image_arr = imageName.split(".");
            var image_arr_last_index = image_arr.length - 1;

            var image_extensions = JSON.parse(jQuery('#faviconSupportedFormats').val());
            var image_ext = image_arr[image_arr_last_index].toLowerCase();

            if (image_extensions.indexOf(image_ext) != '-1') {

                if(file && typeof Image === 'function') {
                    image = new Image();
                    image.onload = function() {
                        var width = this.width;
                        var height = this.height;
                        if(width > allowedDimensions.width || height > allowedDimensions.height ) {
                            app.helper.showErrorNotification({
                                'message' : app.vtranslate('JS_FAVICON_IMAGE_DIMENSIONS_WRONG')
                            });
                            faviconFileCabinet.val(null); //this will empty file input
                        }
                    };
                    image.src = _URL.createObjectURL(file);
                }

            } else {
                image = new Image();
                app.helper.showErrorNotification({
                    'message' : app.vtranslate('JS_FAVICON_IMAGE_TYPE_WRONG')
                });
                faviconFileCabinet.val(null);
                image.src = _URL.createObjectURL(file);
            }

        });
    }, 

    registerCompanyBannerDimensionsValidation : function() {
        var updateCompanyDetailsForm = jQuery('form#updateCompanyDetailsForm');
        var bannerFile = updateCompanyDetailsForm.find('#bannerFile');
        bannerFile.on('change', function() {
            var _URL = window.URL || window.webkitURL;
            var image, file = this.files[0];

            var imageName = bannerFile.val();
            var image_arr = new Array();
            image_arr = imageName.split(".");
            var image_arr_last_index = image_arr.length - 1;

            var image_extensions = JSON.parse(jQuery('#bannerSupportedFormats').val());
            var image_ext = image_arr[image_arr_last_index].toLowerCase();

            if (image_extensions.indexOf(image_ext) != '-1') {
            } else {
                image = new Image();
                app.helper.showErrorNotification({
                    'message' : app.vtranslate('JS_BANNER_IMAGE_TYPE_WRONG5')
                });
                bannerFile.val(null);
                image.src = _URL.createObjectURL(file);
            }

        });
    },  

    registerCompanyBannerCabinetDimensionsValidation : function() {
        var updateCompanyDetailsForm = jQuery('form#updateCompanyDetailsForm');
        var bannerFileCabinet = updateCompanyDetailsForm.find('#bannerFileCabinet');
        bannerFileCabinet.on('change', function() {
            var _URL = window.URL || window.webkitURL;
            var image, file = this.files[0];

            var imageName = bannerFileCabinet.val();
            var image_arr = new Array();
            image_arr = imageName.split(".");
            var image_arr_last_index = image_arr.length - 1;

            var image_extensions = JSON.parse(jQuery('#bannerSupportedFormats').val());
            var image_ext = image_arr[image_arr_last_index].toLowerCase();

            if (image_extensions.indexOf(image_ext) != '-1') {
            } else {
                image = new Image();
                app.helper.showErrorNotification({
                    'message' : app.vtranslate('JS_BANNER_IMAGE_TYPE_WRONG5')
                });
                bannerFileCabinet.val(null);
                image.src = _URL.createObjectURL(file);
            }

        });
    },    
	
	registerEvents: function() {
		this.registerUpdateDetailsClickEvent();
		this.registerSaveCompanyDetailsEvent();
		this.WidthHeight_logoName();
        this.WidthHeight_faviconName();
        this.WidthHeight_logoNameCabinet();
        this.WidthHeight_faviconNameCabinet();
		this.registerCancelClickEvent();
		this.registerCompanyLogoDimensionsValidation();
		this.registerCompanyFaviconDimensionsValidation();
        this.registerCompanyBannerDimensionsValidation();
        this.registerCompanyLogoCabinetDimensionsValidation();
        this.registerCompanyFaviconCabinetDimensionsValidation();
        this.registerCompanyBannerCabinetDimensionsValidation();
	}

});
