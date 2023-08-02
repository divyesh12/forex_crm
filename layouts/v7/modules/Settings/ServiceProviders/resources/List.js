/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Settings_Vtiger_List_Js("Settings_ServiceProviders_List_Js", {
    /**
     * Function to trigger edit and add new configuration for SMS server
     */
    triggerEdit: function (event, url) {
        event.stopPropagation();
        var instance = Vtiger_List_Js.getInstance();
        instance.EditRecord(url);
    },
    /**
     * Function to trigger delete SMS provider Configuration
     */
    triggerDelete: function (event, url) {
        event.stopPropagation();
        var instance = Vtiger_List_Js.getInstance();
        instance.DeleteRecord(url);
    }

}, {
    /**
     * Function to show the SMS Provider configuration details for edit and add new
     */
    EditRecord: function (url) {
        var thisInstance = this;
        app.request.get({url: url}).then(
                function (err, data) {
                    if (err) {
                        app.helper.showErrorNotification(err);
                        return;
                    }
                    var callback = function (data) {
                        var form = jQuery('#smsConfig');
                        thisInstance.registerProviderTypeChangeEvent(form);
                        thisInstance.registerPhoneFormatPop(form);
                        thisInstance.registerSaveConfiguration(form);
                        thisInstance.registerCryptoImageValidation(form);
                    }
                    var params = {};
                    params.cb = callback;
                    app.helper.showModal(data, params);
                });
    },
    registerPhoneFormatPop: function (form) {
        form.find('#phoneFormatWarningPop').popover();
    },
    /**
     * Function to register change event for SMS server Provider Type
     */
    registerProviderTypeChangeEvent: function (form) {
        var thisInstance = this;
        form.find('.providerType').change(function (e) {console.log('provider change');
//            alert('dfggfg')
            var currentTarget = jQuery(e.currentTarget);
            var selectedProviderName = currentTarget.val();
            var params = form.serializeFormData();

            params['module'] = app.getModuleName();
            params['parent'] = app.getParentModuleName();
            params['view'] = 'EditAjax';
            params['provider'] = selectedProviderName;
            app.helper.showProgress();
            app.request.get({data: params}).then(function (err, data) {
                app.helper.hideProgress();
                jQuery('#provider').html(data);
                if (jQuery(data).find('select').hasClass('select2')) {
                    vtUtils.applyFieldElementsView(jQuery('#provider'));
                }
                thisInstance.registerCryptoImageValidation(form);
            });

        });
    },
    /**
     * Function to save the SMS Server Configuration Details from edit and Add new configuration 
     */
  /*  registerSaveConfiguration: function (form) {
        var thisInstance = this;
        jQuery('#smsConfig').vtValidate({
            submitHandler: function () {
                var fileData = jQuery('input[type=file]')[0].files[0];console.log(fileData);
//                form.append('files', fileData);
                var params = form.serializeFormData();console.log(params);
                params['module'] = app.getModuleName();
                params['parent'] = app.getParentModuleName();
                params['action'] = 'SaveAjax';
                
                app.helper.showProgress();
                app.request.post({data: params, files: fileData}).then(
                        function (err, data) {
                            if (err == null) {
                                app.helper.hideProgress();
                                app.helper.hideModal();
                                var successMessage = app.vtranslate(data.message);
                                app.helper.showSuccessNotification({"message": successMessage});
                                thisInstance.loadListViewRecords();
                            } else {
                                app.helper.hideProgress();
                                app.helper.showErrorNotification(err);
                                return false;
                            }

//                            app.helper.hideProgress();
//                            if (data) {
//                                app.helper.hideModal();
//                            }

                        });
                return false;
            }
        });
    },*/
    registerSaveConfiguration: function (form) {
        var thisInstance = this;
        jQuery('#smsConfig').vtValidate({
            submitHandler: function () {
                let myform = document.getElementById("smsConfig");
                let formData = new FormData(myform);
                var moduleName = app.getModuleName();
                var parentModule = app.getParentModuleName();
                var action = 'SaveAjax';
                
                formData.append('module', moduleName);
                formData.append('parent', parentModule);
                formData.append('action', action);
                if($('#file').length > 0)
                {
                    if($('#file')[0].files[0])
                    {
                        formData.append('file', $('#file')[0].files[0]);
                    }
                    else
                    {
                        formData.append('file', $('#file').data('value'));
                    }
                }
                app.helper.showProgress();
                $.ajax({
                       url : 'index.php',
                       type : 'POST',
                       data : formData,
                       processData: false,
                       contentType: false,
                       success : function(data) {
                           if (data.success == true) {
                                app.helper.hideProgress();
                                app.helper.hideModal();
                                var successMessage = app.vtranslate(data.result.message);
                                app.helper.showSuccessNotification({"message": successMessage});
                                thisInstance.loadListViewRecords();
                            } else {
                                app.helper.hideProgress();
                                app.helper.showErrorNotification({'message' : app.vtranslate(data.error.message)});
                                return false;
                            }
                       }
                });
            }
        });
    },
    /**
     * Function to delete Configuration for SMS Provider
     */
    DeleteRecord: function (url) {
        var thisInstance = this;
        var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
        app.helper.showConfirmationBox({'message': message}).then(
                function (e) {
                    app.request.post({url: url}).then(
                            function (err, data) {
                                app.helper.showSuccessNotification(app.vtranslate('JS_RECORD_DELETED_SUCCESSFULLY'));
                                thisInstance.loadListViewRecords();
                            });
                });
    },
    registerCryptoImageValidation: function (form) {
        form.find('#file').change(function(e) {
            var file = this.files[0];
            var allowedExtension = ['jpg','jpeg','png'];
              var extension = file.name.substr( (file.name.lastIndexOf('.') +1) );
            //Check file extension
            if(jQuery.inArray(extension, allowedExtension) == -1) {
                app.helper.showErrorNotification({'message' : app.vtranslate('JS_LBL_FILE_EXTENSION_NOT_ALLOW_CRYPTO_IMG_VALIDATION')});
                form.find('#file').val('');
                return false;
            }
            
            var image_width, image_height;
            var img = new Image();
            img.src = URL.createObjectURL(file);
            img.onload = function() {
              image_width = this.width;
              image_height = this.height;
              
              var err = false;
              var errMessage = "";
              
              //Check image resolution
              if (image_width < 100 || image_height < 100) {
                err = true;
                errMessage = "JS_LBL_FILE_RESOLUTION_CRYPTO_IMG_VALIDATION";
              }
              if (image_width > 300 || image_height > 300) {
                err = true;
                errMessage = "JS_LBL_FILE_RESOLUTION_EXCEED_CRYPTO_IMG_VALIDATION";
              }
              //Check image size
              if (file.size > (1024 * 1024 * 5)) {
                err = true;
                errMessage = "JS_LBL_FILE_SIZE_CRYPTO_IMG_VALIDATION";
              }
              
              
              if(err)
              {
                app.helper.showErrorNotification({'message' : app.vtranslate(errMessage)});
                form.find('#file').val('');
                return false;
              }
            };
        });
    },
    /**
     * Function to register all the events
     */
    registerEvents: function () {
        this.initializePaginationEvents();
    }
})

