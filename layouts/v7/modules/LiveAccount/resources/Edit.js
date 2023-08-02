/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("LiveAccount_Edit_Js", {
}, {

    /**
     * Register Quick Create Save Event
     * @param {type} form
     * @returns {undefined}
     */
    quickCreateSave: function (form, invokeParams) {
        var params = {
            submitHandler: function (form) {
                // to Prevent submit if already submitted
                jQuery("button[name='saveButton']").attr("disabled", "disabled");
                app.helper.showProgress();
                if (this.numberOfInvalids() > 0) {
                    return false;
                }
                var formData = jQuery(form).serialize();
                app.helper.showProgress();
                app.request.post({data: formData}).then(function (err, data) {
                    app.helper.hideProgress();
                    if (err === null) {
                        jQuery('.vt-notification').remove();
                        app.event.trigger("post.QuickCreateForm.save", data, jQuery(form).serializeFormData());
                        app.helper.hideModal();
                        var message = typeof formData.record !== 'undefined' ? app.vtranslate('JS_RECORD_UPDATED') : app.vtranslate('JS_RECORD_CREATED');
                        app.helper.showSuccessNotification({"message": message}, {delay: 4000});
                        invokeParams.callbackFunction(data, err);
                        //To unregister onbefore unload event registered for quickcreate
                        window.onbeforeunload = null;
                    } else {
                        app.event.trigger('post.save.failed', err);
                        jQuery("button[name='saveButton']").removeAttr('disabled');
                    }
                });
            },
            validationMeta: quickcreate_uimeta
        };
        form.vtValidate(params);
    },

    loadEvent: function (container) {
        var thisInstance = this;
        var urlParams = new URLSearchParams(window.location.search);
        var error = urlParams.get('error');
        if (error == 'MQT_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_META_TRADER_ERROR')});
        } else if (error == 'ACCOUNT_LIMIT_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_ACCOUNT_CREATION_LIMIT')});
        } else if (error == 'LEVERAGE_UPDATE_ISSUE') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_LEVERAGE_UPDATE_ISSUE')});
        } else if (error == 'ACCOUNT_TYPE_UPDATE_ISSUE') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_ACCOUNT_TYPE_UPDATE_ISSUE')});
        } else if (error == 'ACCOUNT_MAPPING_ISSUE') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_ACCOUNT_MAPPING_ISSUE')});
        } else if (error == 'COMMON_SERIES_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_SET_COMMON_SERIES_FROM_PROVIDER')});
        } else if (error == 'GROUP_SERIES_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_SET_GROUP_SERIES_FROM_LIVEACCOUNT_MAPPING')});
        } else if (error == 'SET_SERIES_TYPE_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_SET_SERIES_TYPE_ERROR')});
        } else if (error == 'ACCOUNT_RANGE_LIMIT_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_ACCOUNT_RANGE_LIMIT_ERROR')});
        } else {
            if(error != null){
                app.helper.showErrorNotification({message: app.vtranslate(error)});
            }
        }

        var container = jQuery('#EditView');
        var recordId = jQuery('input[name="record"]').val();
        jQuery('.createReferenceRecord').hide();
        var contactid = jQuery("input[name='contactid']").val();
        if (contactid) {
            container.find('[name="contactid"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
            jQuery('#QuickCreate').find('[name="contactid"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        }


        jQuery("td#fieldLabel_cabinet_label_account_type").addClass('hide');
        jQuery("td#fieldValue_cabinet_label_account_type").addClass('hide');

        jQuery("td#fieldLabel_investor_password").addClass('hide');
        jQuery("td#fieldValue_investor_password").addClass('hide');

        jQuery("td#fieldLabel_password").addClass('hide');
        jQuery("td#fieldValue_password").addClass('hide');

        jQuery("td#fieldLabel_current_balance").addClass('hide');
        jQuery("td#fieldValue_current_balance").addClass('hide');

        jQuery("td#fieldLabel_free_margin").addClass('hide');
        jQuery("td#fieldValue_free_margin").addClass('hide');

        jQuery("td#fieldLabel_equity").addClass('hide');
        jQuery("td#fieldValue_equity").addClass('hide');
        
        jQuery("td#fieldLabel_username").addClass('hide');
        jQuery("td#fieldValue_username").addClass('hide');
        //  container.find('[name="metatrader_type"]').closest("tr").hide();

        jQuery("#leadid_display").attr("disabled", "disabled");
        container.find('[name="leadid"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        jQuery("#LiveAccount_editView_fieldName_account_no").attr("readonly", true);
        jQuery("#LiveAccount_editView_fieldName_currency").attr('readonly', true);
        jQuery("#LiveAccount_editView_fieldName_password").attr('readonly', true);
        jQuery("#LiveAccount_editView_fieldName_investor_password").attr('readonly', true);


//          jQuery("td#fieldLabel_live_currency_code").addClass('hide');
//          jQuery("td#fieldValue_live_currency_code").addClass('hide');


//            if (recordId != '') {
////                  jQuery('.cursorPointer').hide();
////                  jQuery('.clearReferenceSelection').hide();
//                  container.find('[name="live_metatrader_type"]').parent('td').css('pointer-events', 'none');
//                  container.find('[name="live_metatrader_type"]').css('background-color', '#DEDEDE');
//                  container.find('[name="live_label_account_type"]').parent('td').css('pointer-events', 'none');
//                  container.find('[name="live_label_account_type"]').css('background-color', '#DEDEDE');
//                  container.find('[name="live_currency_code"]').parent('td').css('pointer-events', 'none');
//                  container.find('[name="live_currency_code"]').css('background-color', '#DEDEDE');
//                  container.find('[name="live_account_type"]').parent('td').css('pointer-events', 'none');
//                  container.find('[name="live_account_type"]').css('background-color', '#DEDEDE');
//                  container.find('[name="balance"]').parent('td').css('pointer-events', 'none');
//                  container.find('[name="balance"]').css('background-color', '#DEDEDE');
////                  container.find('[name="leverage"]').parent('td').css('pointer-events', 'none');
////                  container.find('[name="leverage"]').css('background-color', '#DEDEDE');
//            }

        var record_status = container.find('[name="record_status"]').val();
        if (record_status == 'Approved') {
            container.find('[name="record_status"]').parent('td').css('pointer-events', 'none');
            container.find('[name="record_status"]').css('background-color', '#DEDEDE');
            container.find('[name="leverage"]').parent('td').css('pointer-events', 'auto');
            container.find('[name="leverage"]').css('background-color', '#DEDEDE');
            container.find('[name="live_metatrader_type"]').parent('td').css('pointer-events', 'none');
            container.find('[name="live_metatrader_type"]').css('background-color', '#DEDEDE');
            // container.find('[name="live_label_account_type"]').parent('td').css('pointer-events', 'none');
            // container.find('[name="live_label_account_type"]').css('background-color', '#DEDEDE');
            container.find('[name="live_currency_code"]').parent('td').css('pointer-events', 'none');
            container.find('[name="live_currency_code"]').css('background-color', '#DEDEDE');
            // container.find('[name="live_account_type"]').parent('td').css('pointer-events', 'none');
            // container.find('[name="live_account_type"]').css('background-color', '#DEDEDE');
            container.find('[name="balance"]').parent('td').css('pointer-events', 'none');
            container.find('[name="balance"]').css('background-color', '#DEDEDE');
        }

        jQuery('select[name="live_metatrader_type"]').on('change', function (e) {
            var metaType = jQuery(this).val();
            if(metaType)
            {
                thisInstance.hideLeverageForVertex(metaType);
            }
        });
    },

    hideLeverageForVertex: function (metaType) {
        var aDeferred = jQuery.Deferred();
        app.helper.showProgress();
        var param = {
            'module' : 'LiveAccount',
            'action' : 'GetData',
            'mode' : 'getProviderType',
            'meta_type': metaType
        }
        app.request.post({"data":param}).then(function(err,data){
            app.helper.hideProgress();
            leverageEnable = data.leverage_enable;
            if(leverageEnable !== "" && leverageEnable !== undefined && leverageEnable !== null)
            {
                if(leverageEnable == "false")
                {
                    jQuery('select[name="leverage"]').parents('td').prev().addClass('hide');
                    jQuery('select[name="leverage"]').parents('td').addClass('hide');
                }
                else
                {
                    jQuery('select[name="leverage"]').parents('td').prev().removeClass('hide');
                    jQuery('select[name="leverage"]').parents('td').removeClass('hide');
                }
            }
        });
        aDeferred.resolve();
        return aDeferred.promise();
    },

    registerStatusChangeEvent: function (container) {
        jQuery('#LiveAccount_editView_fieldName_status_reason').attr('disabled', 'disabled');
        jQuery('select[name="record_status"]').on('change', function (e) {
            var record_status = jQuery(this).val();
            if (record_status == 'Disapproved') {
                jQuery('input[name=status_reason]').toggleClass('required', record_status);
                jQuery('td#fieldLabel_status_reason').append('<span class="redColor">*</span>');
                jQuery("td#fieldLabel_status_reason span.redColor").css('color', 'red');
                jQuery("#LiveAccount_editView_fieldName_status_reason").removeAttr('disabled', 'disabled');
            } else {
                jQuery("td#fieldLabel_status_reason span.redColor").css('color', '#f7f7f9');
                jQuery('input[name=status_reason]').removeClass('required');
                jQuery("#LiveAccount_editView_fieldName_status_reason").attr('disabled', 'disabled');
                jQuery("#LiveAccount_editView_fieldName_status_reason").val('');
                jQuery('td#fieldLabel_status_reason span.redColor').detach();
                jQuery('#LiveAccount_editView_fieldName_status_reason').removeClass('input-error');

            }

        });
    },

    registerCancel: function () {
        jQuery('.cancelLink').click(function (e) {
            var currentElement = jQuery(e.target);
            if(currentElement.parents('form#QuickCreate').length > 0)
            {
                app.helper.hideModal();
            }
            else
            {
                window.history.back();
            }
            return false;
        });
    },
    registerBasicEvents: function (container) {
        this._super(container);
        this.loadEvent(container);
        this.registerStatusChangeEvent(container);
        this.registerCancel();
        jQuery('select[name="live_metatrader_type"]').trigger('change');
    }
});