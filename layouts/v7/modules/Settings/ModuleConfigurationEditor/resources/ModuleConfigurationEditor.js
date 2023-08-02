/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/*Add By:- Divyesh Chothani
 * Date:- 02-01-2020
 * Comment:- Validate fields
 * */
jQuery.Class('Settings_ModuleConfigurationEditor_Js', {}, {

    registerChangeEvent: function () {

        /*DemoAccount Validation*/
//        jQuery('select[name="demoaccount_common_series_range"]').on('change', function () {
//            var common_series_range_value = jQuery(this).val();
//            var group_series_range_value = jQuery('select[name="demoaccount_group_series_range"]').val();
//
//            if (common_series_range_value == 'true') {
//                jQuery('select[name="demoaccount_group_series_range"]').val('false');
//            }
//            if (common_series_range_value == 'false') {
//                jQuery('select[name="demoaccount_group_series_range"]').val('true');
//            }
//            if (common_series_range_value == 'false' && group_series_range_value == 'false') {
//                jQuery('select[name="demoaccount_common_series_range"]').val('false');
//                jQuery('select[name="demoaccount_group_series_range"]').val('false');
//            }
//        });
//
//        jQuery('select[name="demoaccount_group_series_range"]').on('change', function () {
//            var group_series_range_value = jQuery(this).val();
//            var common_series_range_value = jQuery('select[name="demoaccount_common_series_range"]').val();
//            if (group_series_range_value == 'true') {
//                jQuery('select[name="demoaccount_common_series_range"]').val('false');
//            }
//            if (group_series_range_value == 'false') {
//                jQuery('select[name="demoaccount_common_series_range"]').val('true');
//            }
//            if (common_series_range_value == 'false' && group_series_range_value == 'false') {
//                jQuery('select[name="demoaccount_common_series_range"]').val('false');
//                jQuery('select[name="demoaccount_group_series_range"]').val('false');
//            }
//        });
        /*DemoAccount Validation*/


        /*LiveAccount Validation*/
//        jQuery('select[name="liveaccount_common_series_range"]').on('change', function () {
//            var common_series_range_value = jQuery(this).val();
//            var group_series_range_value = jQuery('select[name="liveaccount_group_series_range"]').val();
//
//            if (common_series_range_value == 'true') {
//                jQuery('select[name="liveaccount_group_series_range"]').val('false');
//            }
//            if (common_series_range_value == 'false') {
//                jQuery('select[name="liveaccount_group_series_range"]').val('true');
//            }
//            if (common_series_range_value == 'false' && group_series_range_value == 'false') {
//                jQuery('select[name="liveaccount_common_series_range"]').val('false');
//                jQuery('select[name="liveaccount_group_series_range"]').val('false');
//            }
//        });
//
//        jQuery('select[name="liveaccount_group_series_range"]').on('change', function () {
//            var group_series_range_value = jQuery(this).val();
//            var common_series_range_value = jQuery('select[name="liveaccount_common_series_range"]').val();
//            if (group_series_range_value == 'true') {
//                jQuery('select[name="liveaccount_common_series_range"]').val('false');
//            }
//            if (group_series_range_value == 'false') {
//                jQuery('select[name="liveaccount_common_series_range"]').val('true');
//            }
//            if (common_series_range_value == 'false' && group_series_range_value == 'false') {
//                jQuery('select[name="liveaccount_common_series_range"]').val('false');
//                jQuery('select[name="liveaccount_group_series_range"]').val('false');
//            }
//        });
        /*LiveAccount Validation*/

        /*Ewallet Validation*/
        jQuery('select[name="ewallet_module_enabled"]').on('change', function () {
            var ewallet_module_enabled = jQuery(this).val();

            if (ewallet_module_enabled == 'false') {
                jQuery('select[name="ewallet_to_tradingaccount"]').val('false');
                jQuery('select[name="tradingaccount_to_ewallet"]').val('false');
                jQuery('select[name="ewallet_to_ewallet"]').val('false');
                jQuery('select[name="ewallet_deposit_without_kyc_verification"]').val('false');
                jQuery('select[name="ewallet_to_ewallet_auto_approved"]').val('false');
                jQuery('select[name="ewallet_to_tradingaccount_auto_approved"]').val('false');
                jQuery('select[name="tradingaccount_to_ewallet_auto_approved"]').val('false');
            } else if (ewallet_module_enabled == 'true') {
                jQuery('select[name="ewallet_to_tradingaccount"]').val('true');
                jQuery('select[name="tradingaccount_to_ewallet"]').val('true');
                jQuery('select[name="ewallet_to_ewallet"]').val('true');
                jQuery('select[name="ewallet_deposit_without_kyc_verification"]').val('true');
                jQuery('select[name="ewallet_to_ewallet_auto_approved"]').val('true');
                jQuery('select[name="ewallet_to_tradingaccount_auto_approved"]').val('true');
                jQuery('select[name="tradingaccount_to_ewallet_auto_approved"]').val('true');
            }
        });
        /*Ewallet Validation*/
        /*KYC Form*/
        jQuery('select[name="is_doc_limit_enable"]').on('change', function () {
            var kycQuestEnable = jQuery(this).val();console.log(kycQuestEnable);
            if (kycQuestEnable == 'true')
            {
                jQuery('input[name="doc_approved_limit_for_id_proof"]').removeAttr('disabled');
                jQuery('input[name="doc_approved_limit_for_residence_proof"]').removeAttr('disabled');
            }
            else if(kycQuestEnable == 'false')
            {
                jQuery('input[name="doc_approved_limit_for_id_proof"]').val('0').attr('disabled', 'disabled');
                jQuery('input[name="doc_approved_limit_for_residence_proof"]').val('0').attr('disabled', 'disabled');
            }
        });
        jQuery('select[name="is_doc_limit_enable"]').trigger('change');
        /*KYC Form*/
    },

    /**
     * Function to Validate and Save Event 
     * @returns {undefined}
     */
    registerValidation: function () {
        var thisInstance = this;
        var form = jQuery('#module_configrution_editor');
        var params = {
            submitHandler: function (form) {
                var form = jQuery(form);
                jQuery('.saveButton').attr('disabled', true);
                /*var liveaccount_group_series_range = jQuery('select[name="liveaccount_group_series_range"]').val();
                var liveaccount_common_series_range = jQuery('select[name="liveaccount_common_series_range"]').val();
                var demoaccount_group_series_range = jQuery('select[name="demoaccount_group_series_range"]').val();
                var demoaccount_common_series_range = jQuery('select[name="demoaccount_common_series_range"]').val();

                if (liveaccount_group_series_range == liveaccount_common_series_range) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_LIVE_SET_SERIES_TYPE')});
                    jQuery('.saveButton').removeAttr('disabled');
                    return false;
                } else if (demoaccount_group_series_range == demoaccount_common_series_range) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_DEMO_SET_SERIES_TYPE')});
                    jQuery('.saveButton').removeAttr('disabled');
                    return false;
                } else {*/
                    this.form.submit();
                    return true;
                /*}*/
            }
        };
        form.vtValidate(params);
    },
    registerCancel: function () {
        jQuery('.cancelLink').click(function () {
            window.history.back();
            return false;
        });
    },

    registerCabinetBannerDimensionsValidation : function() {
        var allowedDimensionsForHeader = {
            'width' : 480,
            'height' : 60
        };
        var allowedDimensionsForFooter = {
            'width' : 350,
            'height' : 52
        };
        var allowedDimensionsForMobile = {
            'width' : 480,
            'height' : 100
        };
        
        var module_configrution_editor = jQuery('form#module_configrution_editor');
        
        var cabinet_header_banner_path = module_configrution_editor.find('#cabinet_header_banner_path');
        cabinet_header_banner_path.on('change', function() {
            var _URL = window.URL || window.webkitURL;
            var image, file = this.files[0];
            if(file && typeof Image === 'function') {
                image = new Image();
                image.onload = function() {
                    var width = this.width;
                    var height = this.height;
                    if(width != allowedDimensionsForHeader.width || height != allowedDimensionsForHeader.height) {
                        app.helper.showErrorNotification({
                            'message' : app.vtranslate('JS_CABINET_HEADER_BANNER_IMAGE_DIMENSIONS_WRONG')
                        });
                        cabinet_header_banner_path.val(null); //this will empty file input
                    }
                };
                image.src = _URL.createObjectURL(file);
            }
        });

        var cabinet_footer_banner_path = module_configrution_editor.find('#cabinet_footer_banner_path');
        cabinet_footer_banner_path.on('change', function() {
            var _URL = window.URL || window.webkitURL;
            var image, file = this.files[0];
            if(file && typeof Image === 'function') {
                image = new Image();
                image.onload = function() {
                    var width = this.width;
                    var height = this.height;
                    if(width != allowedDimensionsForFooter.width || height != allowedDimensionsForFooter.height) {
                        app.helper.showErrorNotification({
                            'message' : app.vtranslate('JS_CABINET_FOOTER_BANNER_IMAGE_DIMENSIONS_WRONG')
                        });
                        cabinet_footer_banner_path.val(null); //this will empty file input
                    }
                };
                image.src = _URL.createObjectURL(file);
            }
        });

        var mobile_header_banner_path = module_configrution_editor.find('#mobile_header_banner_path');
        mobile_header_banner_path.on('change', function() {
            var _URL = window.URL || window.webkitURL;
            var image, file = this.files[0];
            if(file && typeof Image === 'function') {
                image = new Image();
                image.onload = function() {
                    var width = this.width;
                    var height = this.height;
                    if(width != allowedDimensionsForMobile.width || height != allowedDimensionsForMobile.height) {
                        app.helper.showErrorNotification({
                            'message' : app.vtranslate('JS_MOBILE_BANNER_IMAGE_DIMENSIONS_WRONG')
                        });
                        mobile_header_banner_path.val(null); //this will empty file input
                    }
                };
                image.src = _URL.createObjectURL(file);
            }
        });
    },

    registerEvents: function (e) {
        var thisInstance = this;
        // this._super();
        this.registerChangeEvent();
        this.registerValidation();
        this.registerCancel();
        this.registerCabinetBannerDimensionsValidation();
    }

});
jQuery(document).ready(function () {
    var settingModuleConfigurationEditorInstance = new Settings_ModuleConfigurationEditor_Js();
    settingModuleConfigurationEditorInstance.registerEvents();
})
