/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class('Settings_ModuleBuilder_Js', {}, {

      /**
       * Function which will arrange the selected element choices in order
       */
      ChangeEvent: function () {
            jQuery('#picklist_values').hide();
            jQuery('textarea[name="picklist_values"]').attr('disabled', 'disabled');
            jQuery('#relatedmodule_name').hide();
            jQuery('input[name="relatedmodule_name"]').attr('disabled', 'disabled');
            jQuery('select[name="field_ui_type"]').on('change', function () {
                  var field_ui_type = this.value;
                  if (field_ui_type == 'PickList_15' || field_ui_type == 'MultiSelectComboBox_33') {
                        jQuery('#picklist_values').show();
                        jQuery('textarea[name="picklist_values"]').removeAttr('disabled', 'disabled');
                  } else {
                        jQuery('textarea[name="picklist_values"]').attr('disabled', 'disabled');
                        jQuery('#picklist_values').hide();
                  }
                  if (field_ui_type == 'RelationModule_10') {
                        jQuery('#relatedmodule_name').show();
                        jQuery('input[name="relatedmodule_name"]').removeAttr('disabled', 'disabled');
                  } else {
                        jQuery('input[name="relatedmodule_name"]').attr('disabled', 'disabled');
                        jQuery('#relatedmodule_name').hide();
                  }
            })
            jQuery('input[name="field_name"]').on('change focusout', function () {
                  var field_name = jQuery('input[name="field_name"]').val();
                  var field_name_regex = new RegExp("^[0-9a-z_\s]+$");
                  var field_ui_type = jQuery('select[name="field_ui_type"]').val();
                  if (!field_name_regex.test(field_name) && field_name != '') {
                        app.helper.showErrorNotification({'message': 'Please enter only small letter with underscore in Field Name.'});
                        jQuery('input[name="field_name"]').val('');
                  }
                  if (field_ui_type == 'PickList_15' || field_ui_type == 'MultiSelectComboBox_33') {
                        var postData = {
                              "parent": "Settings",
                              "module": 'ModuleBuilder',
                              "action": "CheckTableExists",
                              "field_name": field_name,
                        }
                        AppConnector.request(postData).then(
                                function (data) {
                                      var result = data['result'][0];
                                      if (result > 0) {
                                            app.helper.showErrorNotification({'message': field_name + 'field already exist in your database.'});
                                            jQuery('input[name="field_name"]').val('');
                                      }
                                },
                                function (error, err) {
                                }
                        );
                  }
            })

            jQuery('input[name="module_name"]').on('change', function () {
                  var module_name = jQuery('input[name="module_name"]').val();
                  var module_name_regex = new RegExp("^[a-zA-Z\s]+$");
                  if (!module_name_regex.test(module_name) && module_name != '') {
                        app.helper.showErrorNotification({'message': 'Please enter only letter in Module Name.'});
                        jQuery('input[name="module_name"]').val('');
                  }
            })

            jQuery('select[name="module_name"]').on('change', function () {
                  var module_name = jQuery('select[name="module_name"]').val();
                  if (module_name != '') {
                        var postData = {
                              "parent": "Settings",
                              "module": 'ModuleBuilder',
                              "action": "GetModuleBlocks",
                              "module_name": module_name,
                        }
                        AppConnector.request(postData).then(
                                function (data) {
                                      var blockname_list = data['result'][0];
                                      jQuery('#block_name').html(blockname_list);
                                },
                                function (error, err) {
                                }
                        );
                  }
            })
      },

      registerEvents: function (e) {
            var thisInstance = this;
            thisInstance.ChangeEvent();
      }
});
jQuery(document).ready(function () {
      var settingModuleBuilderInstance = new Settings_ModuleBuilder_Js();
      settingModuleBuilderInstance.registerEvents();
})
