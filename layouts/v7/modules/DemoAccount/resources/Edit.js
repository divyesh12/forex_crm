/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("DemoAccount_Edit_Js", {
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

    registorCustomEvent: function (container) {
        var thisInstance = this;
        var container = jQuery('#EditView');
        var recordId = jQuery('input[name="record"]').val();

        var urlParams = new URLSearchParams(window.location.search);
        var error = urlParams.get('error');
        if (error == 'MQT_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_META_TRADER_ERROR')});
        } else if (error == 'MQT_UPDATE_BALANCE_ISSUE') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_UPDATE_BALANCE_ISSUE')});
        } else if (error == 'ACCOUNT_LIMIT_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_ACCOUNT_CREATION_LIMIT')});
        } else if (error == 'ACCOUNT_CONTACT_RELATION_LIMIT_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_ACCOUNT_CONTACT_RELATION_LIMIT')});
        } else if (error == 'ACCOUNT_MAPPING_ISSUE') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_ACCOUNT_MAPPING_ISSUE')});
        } else if (error == 'COMMON_SERIES_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_SET_COMMON_SERIES_FROM_PROVIDER')});
        } else if (error == 'GROUP_SERIES_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_SET_GROUP_SERIES_FROM_DEMOACCOUNT_MAPPING')});
        } else if (error == 'SET_SERIES_TYPE_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_SET_SERIES_TYPE_ERROR')});
        } else if (error == 'ACCOUNT_RANGE_LIMIT_ERROR') {
            app.helper.showErrorNotification({message: app.vtranslate('JS_ACCOUNT_RANGE_LIMIT_ERROR')});
        } else {
            if(error != null){
                app.helper.showErrorNotification({message: app.vtranslate(error)});
            }
        }

        jQuery('.createReferenceRecord').hide();
        jQuery("#leadid_display").attr("disabled", "disabled");
        jQuery("#DemoAccount_editView_fieldName_account_no").attr("readonly", true);

        jQuery("td#fieldLabel_is_account_disable").hide();
        jQuery("td#fieldValue_is_account_disable").hide();
        jQuery("td#fieldLabel_account_expriry_date").hide();
        jQuery("td#fieldValue_account_expriry_date").hide();

        jQuery("td#fieldLabel_username").addClass('hide');
        jQuery("td#fieldValue_username").addClass('hide');
        
        container.find('[name="password"]').closest("tr").hide();
        container.find('[name="account_expriry_date"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        container.find('[name="leadid"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        container.find('[name="account_no"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        container.find('[name="currency"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        container.find('[name="password"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        container.find('[name="investor_password"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');

        var is_account_disable = container.find('[name="is_account_disable"]').is(":checked");
        if (is_account_disable) {
            container.find('[name="is_account_disable"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        } else {
            container.find('[name="is_account_disable"]').parents('td').addClass('fieldValue').css('pointer-events', 'auto');
        }

        var contactid = parseInt(jQuery("input[name='contactid']").val());
        if (contactid) {
            container.find('[name="contactid"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
            jQuery('#QuickCreate').find('[name="contactid"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        }

        if (recordId != '') {
            container.find('[name="metatrader_type"]').parent('td').css('pointer-events', 'none');
            container.find('[name="metatrader_type"]').css('background-color', '#DEDEDE');
            container.find('[name="demo_label_account_type"]').parent('td').css('pointer-events', 'none');
            container.find('[name="demo_label_account_type"]').css('background-color', '#DEDEDE');
            container.find('[name="demo_currency_code"]').parent('td').css('pointer-events', 'none');
            container.find('[name="demo_currency_code"]').css('background-color', '#DEDEDE');
            container.find('[name="demo_account_type"]').parent('td').css('pointer-events', 'none');
            container.find('[name="demo_account_type"]').css('background-color', '#DEDEDE');
            container.find('[name="leverage"]').parent('td').css('pointer-events', 'none');
            container.find('[name="leverage"]').css('background-color', '#DEDEDE');
            container.find('[name="balance"]').parent('td').css('pointer-events', 'none');
            container.find('[name="balance"]').css('background-color', '#DEDEDE');
        }
        
        jQuery('select[name="metatrader_type"]').on('change', function (e) {
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
        this.registorCustomEvent(container);
        this.registerCancel();
        jQuery('select[name="metatrader_type"]').trigger('change');
    }
});