/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("LeverageHistory_Edit_Js", {
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

    /*@ Add by:- Divyesh Chothani
     *@ Date:- 14-11-2019
     *@ Comment:- return validation messege
     * */
    registerLoadEvent: function (container) {
        var urlParams = new URLSearchParams(window.location.search);
        var error = urlParams.get('error');
        if (error == 'MQT_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_META_TRADER_ERROR')});
        } else if (error == 'LEVERAGE_UPDATE_ISSUE') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_LEVERAGE_UPDATE_ISSUE')});
        } else if (error == 'PENDING_REQUEST_EXIST') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_PENDING_REQUEST_EXIST')});
        } else {
            if(error != null){
                app.helper.showErrorNotification({message: app.vtranslate(error)});
            }
        }

    },

    /*@ Add by:- Reena Hingol
     *@ Date:- 14-11-2019
     *@ Comment:-Get Autoselect Current Leverage from LiveAccount while QuickCreate
     * */
    registorQuickLeverageEvent: function (container) {

        var data = new Object;
        var form = container.closest('form');
        data.source_module = app.getModuleName();
        data.record = form.find("[name='liveaccountid']").val();
        data.selectedName = form.find("[name='liveaccountid_display']").val();
        this.referenceSelectionEventHandler(data, container);

//            var formId = container.closest('form').attr('id');
//
//            if (formId == 'QuickCreate') {
//                  var data = new Object;
//                  // var container = jQuery("[name='QuickCreate']");
//                  var form = container.closest('form');
//                  data.source_module = app.getModuleName();
//                  data.record = form.find("[name='liveaccountid']").val();
//                  data.selectedName = form.find("[name='liveaccountid_display']").val();
//                  this.referenceSelectionEventHandler(data, container);
//
//            } else {
//                  var data = new Object;
//                  var container = jQuery("[name='edit']");
//                  data.source_module = app.getModuleName();
//                  data.record = container.find("[name='liveaccountid']").val();
//                  alert(container.find("[name='liveaccountid']").val())
//                  data.selectedName = container.find("[name='liveaccountid_display']").val();
//                  this.referenceSelectionEventHandler(data, container);
//            }
    },
    /*@ Add by:- Reena Hingol
     *@ Date:- 14-11-2019
     *@ Comment:-Get Auto select Current Leverage from LiveAccountid for full form
     * */
    registerReferenceSelectionEvent: function (container) {
        var thisInstance = this;
        jQuery('input[name="liveaccountid"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
            thisInstance.referenceSelectionEventHandler(data, container);
        });
    },
    /*@ Add by:- Reena Hingol
     *@ Date:- 14-11-2019
     *@ Comment:-Get Auto select Current Leverage from LiveAccount for full form
     * */
    referenceSelectionEventHandler: function (data, container) {
        var self = this;
        var source_module = data['source_module'];
        //var selectedvalue = data['selectedName'];
        var recordId = data['record'];
        if(recordId)
        {
            var params = {
                'module': 'LeverageHistory',
                'action': 'GetRecordData',
                'mode': 'GetLiveAccountData',
                'source_module': source_module,
                'record_id': recordId,
            }
            app.request.post({data: params}).then(function (error, data) {
                var current_leverage = data['leverage'];
                var contact_name = data['contact_name'];
                var contactid = data['contactid'];
                var metaType = data['meta_type'];
                var leverageEnable = data['leverage_enable'];
                jQuery('[name="old_leverage"]', container).val(current_leverage);

                if(leverageEnable === 'false')
                {
                    var errorMessage = app.vtranslate('JS_ACCOUNT_NOT_SUPPORT_LEVERAGE');
                    app.helper.showErrorNotification({"message": errorMessage});
                    var form = container.closest('form');
                    var parent = jQuery("input[name='liveaccountid']").closest('.input-group');
                    parent.find('.clearReferenceSelection').trigger('click');
    //                form.find("[name='liveaccountid']").val('').trigger('change');
    //                form.find("[name='liveaccountid_display']").val('').trigger('change');
                    return;
                }

                /*@ Add by:- Reena
                 *@ Date:- 15-11-2019
                 *@ Comment:-Validation for old and new leverage not same and New leverage value is blank
                 * */
                var new_leverage = jQuery('[name="leverage"]', container).val();
                if (current_leverage != "" && (Number(current_leverage) == Number(new_leverage))) {
                    var errorMessage = app.vtranslate('JS_SAME_LEVERAGE_ERROR_MESSAGE');
                    var select_option_label = app.vtranslate('JS_SELECT_OPTION');
                    app.helper.showErrorNotification({"message": errorMessage});
                    jQuery('[name="leverage"]', container).select2('val','');
                    //jQuery('[name="leverage"]', container).trigger('change');
                    //jQuery('div#s2id_leverage').find("#select2-chosen-2").html(select_option_label);
                }
                /*END*/
                if (contactid) {
                    container.find("[name='contactid']").val(contactid);
                    container.find("[name='contactid_display']").val(contact_name);
                }
            });
        }
    },
    /**
     * @Add by: Reena Hingol
     * @Date: 11-10-2019
     * @comment: for make Mendatory field reject reason on Disapproved status
     */
    registerStatusChangeEvent: function (container) {
//        var thisInstance = this;
        jQuery("#LeverageHistory_editView_fieldName_old_leverage").attr('readonly', true);
        jQuery('#LeverageHistory_editView_fieldName_status_reason').attr('disabled', 'disabled');
        jQuery('select[name="record_status"]').on('change', function (e) {
            var record_status = jQuery(this).val();
            if (record_status == 'Disapproved') {
                jQuery('input[name=status_reason]').toggleClass('required', record_status);
                jQuery('td#fieldLabel_status_reason').append('<span class="redColor">*</span>');
                jQuery("td#fieldLabel_status_reason span.redColor").css('color', 'red');
                jQuery("#LeverageHistory_editView_fieldName_status_reason").removeAttr('disabled', 'disabled');
            } else {
                jQuery("td#fieldLabel_status_reason span.redColor").css('color', '#f7f7f9');
                jQuery('input[name=status_reason]').removeClass('required');
                jQuery("#LeverageHistory_editView_fieldName_status_reason").attr('disabled', 'disabled');
                jQuery("#LeverageHistory_editView_fieldName_status_reason").val('');
                jQuery('td#fieldLabel_status_reason span.redColor').detach();
                jQuery('#LeverageHistory_editView_fieldName_status_reason').removeClass('input-error');

            }

        });
    },
    /*@ Add by:- Reena
     *@ Date:- 13-11-2019
     *@ Comment:-Get liveAccount Based on contacts 
     * */
    getPopUpParams: function (container) {
        var params = this._super(container);
        var sourceFieldElement = jQuery('input[class="sourceField"]', container);
        if (!sourceFieldElement.length) {
            sourceFieldElement = jQuery('input.sourceField', container);
        }
        if (sourceFieldElement.attr('name') == 'liveaccountid') {
            var form = container.closest('form');
            var parentIdElement = form.find('[name="contactid"]');
            var currencyCodeElement = form.find('[name="payment_currency"]');
            var closestContainer = parentIdElement.closest('td');
            var related_parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
            if (parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
                params['column1'] = 'contactid';
                params['value1'] = parentIdElement.val();
                params['column2'] = 'record_status';
                params['value2'] = 'Approved';
                params['related_parent_module'] = related_parent_module;
            }
        }
        return params;
    },
    /*@ Add by:- Reena
     *@ Date:- 14-11-2019
     *@ Comment:-Validation for old and new leverage not same and New leverage value is blank
     * */
    registerEventLeverageSame: function (container) {
        var thisInstance = this;
        var form = container.closest('form').attr('id');
        var record_status = container.find('[name="record_status"]').val();
        if(record_status == 'Pending'){
            jQuery('td#fieldValue_contactid').css('pointer-events', 'none');
            jQuery('td#fieldValue_liveaccountid').css('pointer-events', 'none');
        }
       
        jQuery('select[name="leverage"]').on('change', function (e) {
//            var current_leverage = jQuery("#LeverageHistory_editView_fieldName_old_leverage").val();
            var current_leverage = jQuery('[name="old_leverage"]', container).val();
            var new_leverage = jQuery('[name="leverage"]', container).val();
            if (Number(current_leverage) == Number(new_leverage)) {
                var errorMessage = app.vtranslate('JS_SAME_LEVERAGE_ERROR_MESSAGE');
                var select_option_label = app.vtranslate('JS_SELECT_OPTION');
                app.helper.showErrorNotification({"message": errorMessage});
                jQuery('[name="leverage"]', container).select2('val','');
                
                //jQuery('[name="leverage"]', container).val('').trigger("liszt:updated");
                //jQuery('[name="leverage"]', container).trigger('change');
                // jQuery('div#s2id_leverage').find("#select2-chosen-2").html(select_option_label);
                // jQuery('div#s2id_leverage').find("#select2-chosen-10").html(select_option_label);//Add for QuickCreate blank Option value by Reena 151119

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
    /*END*/
    registerBasicEvents: function (container) {
        this._super(container);
        this.registorQuickLeverageEvent(container);
        this.registerStatusChangeEvent(container);
        this.registerReferenceSelectionEvent(container);
        this.registerEventLeverageSame(container);//Added for old and new leverage not same 141119
        this.registerLoadEvent(container);
        this.registerCancel();
    }
});