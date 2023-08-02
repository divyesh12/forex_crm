/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Edit_Js("Contacts_Edit_Js", {}, {
    //Will have the mapping of address fields based on the modules
    addressFieldsMapping: {'Accounts':
                {'mailingstreet': 'bill_street',
                    'otherstreet': 'ship_street',
                    'mailingpobox': 'bill_pobox',
                    'otherpobox': 'ship_pobox',
                    'mailingcity': 'bill_city',
                    'othercity': 'ship_city',
                    'mailingstate': 'bill_state',
                    'otherstate': 'ship_state',
                    'mailingzip': 'bill_code',
                    'otherzip': 'ship_code',
                    'mailingcountry': 'bill_country',
                    'othercountry': 'ship_country'
                }
    },
    //Address field mapping within module
    addressFieldsMappingInModule: {
        'otherstreet': 'mailingstreet',
        'otherpobox': 'mailingpobox',
        'othercity': 'mailingcity',
        'otherstate': 'mailingstate',
        'otherzip': 'mailingzip',
        'othercountry': 'mailingcountry'
    },
    /* Function which will register event for Reference Fields Selection
     */
    registerReferenceSelectionEvent: function (container) {
        var thisInstance = this;

        jQuery('input[name="account_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
            thisInstance.referenceSelectionEventHandler(data, container);
        });
    },
    /**
     * Reference Fields Selection Event Handler
     * On Confirmation It will copy the address details
     */
    referenceSelectionEventHandler: function (data, container) {
        var thisInstance = this;
        var message = app.vtranslate('OVERWRITE_EXISTING_MSG1') + app.vtranslate('SINGLE_' + data['source_module']) + ' (' + data['selectedName'] + ') ' + app.vtranslate('OVERWRITE_EXISTING_MSG2');
        app.helper.showConfirmationBox({'message': message}).then(function (e) {
            thisInstance.copyAddressDetails(data, container);
        },
                function (error, err) {});
    },
    /**
     * Function which will copy the address details - without Confirmation
     */
    copyAddressDetails: function (data, container) {
        var thisInstance = this;
        var sourceModule = data['source_module'];
        thisInstance.getRecordDetails(data).then(
                function (response) {
                    thisInstance.mapAddressDetails(thisInstance.addressFieldsMapping[sourceModule], response['data'], container);
                },
                function (error, err) {

                });
    },
    /**
     * Function which will map the address details of the selected record
     */
    mapAddressDetails: function (addressDetails, result, container) {
        for (var key in addressDetails) {
            if (container.find('[name="' + key + '"]').length == 0) {
                var create = container.append("<input type='hidden' name='" + key + "'>");
            }
            container.find('[name="' + key + '"]').val(result[addressDetails[key]]);
            container.find('[name="' + key + '"]').trigger('change');
        }
    },
    /**
     * Function to swap array
     * @param Array that need to be swapped
     */
    swapObject: function (objectToSwap) {
        var swappedArray = {};
        var newKey, newValue;
        for (var key in objectToSwap) {
            newKey = objectToSwap[key];
            newValue = key;
            swappedArray[newKey] = newValue;
        }
        return swappedArray;
    },
    /**
     * Function to copy address between fields
     * @param strings which accepts value as either odd or even
     */
    copyAddress: function (swapMode, container) {
        var thisInstance = this;
        var addressMapping = this.addressFieldsMappingInModule;
        if (swapMode == "false") {
            for (var key in addressMapping) {
                var fromElement = container.find('[name="' + key + '"]');
                var toElement = container.find('[name="' + addressMapping[key] + '"]');
                toElement.val(fromElement.val());
                if ((jQuery("#massEditContainer").length) && (toElement.val() != "") && (typeof (toElement.attr('data-validation-engine')) == "undefined")) {
                    toElement.attr('data-validation-engine', toElement.data('invalidValidationEngine'));
                }
            }
        } else if (swapMode) {
            var swappedArray = thisInstance.swapObject(addressMapping);
            for (var key in swappedArray) {
                var fromElement = container.find('[name="' + key + '"]');
                var toElement = container.find('[name="' + swappedArray[key] + '"]');
                toElement.val(fromElement.val());
                if ((jQuery("#massEditContainer").length) && (toElement.val() != "") && (typeof (toElement.attr('data-validation-engine')) == "undefined")) {
                    toElement.attr('data-validation-engine', toElement.data('invalidValidationEngine'));
                }
            }
        }
    },
    /**
     * Function to register event for copying address between two fileds
     */
    registerEventForCopyingAddress: function (container) {
        var thisInstance = this;
        var swapMode;
        jQuery('[name="copyAddress"]').on('click', function (e) {
            var element = jQuery(e.currentTarget);
            var target = element.data('target');
            if (target == "other") {
                swapMode = "false";
            } else if (target == "mailing") {
                swapMode = "true";
            }
            thisInstance.copyAddress(swapMode, container);
        })
    },
    /**
     * Function to check for Portal User
     */
    checkForPortalUser: function (form) {
        var element = jQuery('[name="portal"]', form);
        var response = element.is(':checked');
        var primaryEmailField = jQuery('[name="email"]');
        var primaryEmailValue = primaryEmailField.val();
        if (response) {
            if (primaryEmailField.length == 0) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_PRIMARY_EMAIL_FIELD_DOES_NOT_EXISTS')});
                return false;
            }
            if (primaryEmailValue == "") {
                app.helper.showErrorNotification({message: app.vtranslate('JS_PLEASE_ENTER_PRIMARY_EMAIL_VALUE_TO_ENABLE_PORTAL_USER')});
                return false;
            }
        }
        return true;
    },
    /**
     * Function to register recordpresave event
     */
    registerRecordPreSaveEvent: function (form) {
        var thisInstance = this;
        if (typeof form == 'undefined') {
            form = this.getForm();
        }

        app.event.on(Vtiger_Edit_Js.recordPresaveEvent, function (e) {
            var result = thisInstance.checkForPortalUser(form);
            if (!result) {
                e.preventDefault();
            }
        });

    },
    /*Add By Divyesh Chothani
     * Date:- 10-12-2019
     * Comment:- Custom validation
     * */
    registerCustomEvent: function (container) {
        var thisInstance = this;
        jQuery('span.createReferenceRecord').remove();
        var recordId = jQuery('input[name="record"]').val();
        var view = app.view();

        var is_allow_update_parent_ib = jQuery('input[id="is_allow_update_parent_ib"]').val();
        var parent_affiliate_code = jQuery('input[name="parent_affiliate_code"]').val();
        if (is_allow_update_parent_ib != 1 && parent_affiliate_code && recordId) {
            jQuery('[name="parent_affiliate_code"]').prop('disabled', true);
        }

        container.find('[name="is_login_varified"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        container.find('[name="is_first_time_deposit"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
//        jQuery('#Contacts_editView_fieldName_is_document_verified').change(function () {
//            jQuery('#Contacts_editView_fieldName_is_document_verified').prop('disabled', true);
//        });

        jQuery('td#fieldLabel_affiliate_code').hide();
        jQuery('td#fieldValue_affiliate_code').hide();
//        jQuery('td#fieldLabel_parent_affiliate_code').hide();
//        jQuery('td#fieldValue_parent_affiliate_code').hide();
        jQuery('td#fieldLabel_ib_hierarchy').hide();
        jQuery('td#fieldValue_ib_hierarchy').hide();
        jQuery('td#fieldLabel_ib_depth').hide();
        jQuery('td#fieldValue_ib_depth').hide();
        jQuery('td#fieldLabel_kyc_form_status').hide();
        jQuery('td#fieldValue_kyc_form_status').hide();

        var record_status = container.find('[name="record_status"]').val();
        if (jQuery('#Contacts_editView_fieldName_is_document_verified').prop("checked")) {
            container.find('[name="is_document_verified"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        }
        /*if (record_status == 'Approved') {
            // container.find('[name="record_status"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
            jQuery('input[name=child_ibcommissionprofileid_display]').toggleClass('required', record_status);
            jQuery('td#fieldLabel_child_ibcommissionprofileid').append('<span class="redColor">*</span>');
            jQuery("td#fieldLabel_child_ibcommissionprofileid span.redColor").css('color', 'red');

            jQuery('td#fieldLabel_affiliate_code').show();
            jQuery('td#fieldValue_affiliate_code').show();
//            jQuery('td#fieldLabel_parent_affiliate_code').show();
//            jQuery('td#fieldValue_parent_affiliate_code').show();
        }*/

        container.find('[name="portal_language"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        container.find('[name="portal_timezone"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        container.find('[name="portal_timeformate"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        container.find('[name="portal_dateformate"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');

        // container.find('[name="portal"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');

        container.find('[name="affiliate_code"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        container.find('[name="ib_hierarchy"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        container.find('[name="ib_depth"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
//        jQuery("#Contacts_editView_fieldName_status_reason").addClass('disabled');
        jQuery("#Contacts_editView_fieldName_affiliate_code").attr('readonly', 'readonly');


        jQuery('select[name="record_status"]').on('change', function (e) {
            var record_status = jQuery(this).val();
       
            if (record_status == 'Approved') {
                jQuery('td#fieldLabel_status_reason span.redColor').detach();
                jQuery("#Contacts_editView_fieldName_status_reason").val('');
                jQuery('input[name=status_reason]').removeClass('required');
                jQuery('input[name=child_ibcommissionprofileid_display]').toggleClass('required', record_status);
                jQuery('td#fieldLabel_child_ibcommissionprofileid').append('<span class="redColor">*</span>');
                jQuery("td#fieldLabel_child_ibcommissionprofileid span.redColor").css('color', 'red');
                jQuery("#child_ibcommissionprofileid_display").removeAttr('disabled', 'disabled');
                jQuery("#child_ibcommissionprofileid_display").parent('div.input-group').css('pointer-events', 'auto');
            } else if (record_status == 'Disapproved') {
                jQuery('input[name=status_reason]').toggleClass('required', record_status);
                jQuery('td#fieldLabel_status_reason').append('<span class="redColor">*</span>');
                jQuery("td#fieldLabel_status_reason span.redColor").css('color', 'red');
                jQuery("#Contacts_editView_fieldName_status_reason").removeClass('disabled');

                jQuery('input[name=child_ibcommissionprofileid]').val('');
                jQuery("td#fieldLabel_child_ibcommissionprofileid span.redColor").css('color', '#f7f7f9');
                jQuery('input[name=child_ibcommissionprofileid_display]').removeClass('required');
                jQuery("#child_ibcommissionprofileid_display").addClass('disabled');
                jQuery("#child_ibcommissionprofileid_display").val('');
                jQuery('td#fieldLabel_child_ibcommissionprofileid span.redColor').detach();
                jQuery('#child_ibcommissionprofileid_display').removeClass('input-error');
                jQuery("#child_ibcommissionprofileid_display").parent('div.input-group').css('pointer-events', 'none');
            } else {
                jQuery("td#fieldLabel_child_ibcommissionprofileid span.redColor").css('color', '#f7f7f9');
                jQuery('input[name=child_ibcommissionprofileid_display]').removeClass('required');
                jQuery("#child_ibcommissionprofileid_display").addClass('disabled');
                jQuery("#child_ibcommissionprofileid_display").val('');
                jQuery('input[name=child_ibcommissionprofileid]').val('');
                jQuery('td#fieldLabel_child_ibcommissionprofileid span.redColor').detach();
//                jQuery('#child_ibcommissionprofileid_display').removeClass('input-error');
//                jQuery("#child_ibcommissionprofileid_display").parent('div.input-group').css('pointer-events', 'none');

                jQuery("td#fieldLabel_status_reason span.redColor").css('color', '#f7f7f9');
                jQuery('input[name=status_reason]').removeClass('required');
//                jQuery("#Contacts_editView_fieldName_status_reason").attr('disabled', 'disabled');
                jQuery("#Contacts_editView_fieldName_status_reason").val('');
                jQuery('td#fieldLabel_status_reason span.redColor').detach();
                jQuery('#Contacts_editView_fieldName_status_reason').removeClass('input-error');

            }
        });



        jQuery('#Contacts_editView_fieldName_plain_password').css('background-color', '#EBEDEF');
        jQuery('#Contacts_editView_fieldName_plain_password').attr('readonly', true);
        //jQuery('#fieldValue_plain_password').css('pointer-events', 'none');

        jQuery('#Contacts_editView_fieldName_is_agree').css({'background-color':'#EBEDEF','pointer-events':'none'});
        jQuery('#Contacts_editView_fieldName_agree_ip').css('background-color', '#EBEDEF');
        jQuery('#Contacts_editView_fieldName_agree_ip').attr('readonly', true);
        jQuery('#Contacts_editView_fieldName_login_ip_address').attr('disabled', true);
        jQuery('#Contacts_editView_fieldName_login_ip_address').addClass('disabled');
        jQuery('select[name="record_status"]').find('option[value=""]').attr('disabled',true).addClass('disabled');
//        jQuery('select[name="record_status"]').find('option[value=""]');
        
        jQuery('select[name="record_status"]').trigger('change');
        
        jQuery('button#plain_password_copy').on('click', function (e) {
            var clipboardText = "";
            clipboardText = container.find('[name="plain_password"]').val();
            thisInstance.copyToClipboard(clipboardText);
        });
    },
    copyToClipboard: function (text) {
        var textArea = document.createElement("input");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            var successful = document.execCommand('copy');
            var msg = successful ? 'successful' : 'unsuccessful';
        } catch (err) {
        }
        document.body.removeChild(textArea);
    },
    /*
     * * Add By Divyesh Chothani
     * Date:-05-02-2020 
     * Function to Validate and Save Event 
     * @returns {undefined}
     */
    registerValidation: function () {
        var thisInstance = this;
        var form = jQuery('#EditView');
        var params = {
            submitHandler: function (form) {
                var form = jQuery(form);
                if (form.data('submit') === 'true' && form.data('performCheck') === 'true') {
                    return true;
                } else {
                    if (this.numberOfInvalids() <= 0) {
                        var formData = form.serializeFormData();
                        thisInstance.checkParentAffiliateCode({
                            'parent_affiliate_code': formData.parent_affiliate_code,
                            'affiliate_code': formData.affiliate_code,
                            'contact_id': formData.record,
                        }).then(
                                function (data) {
                                    form.data('submit', 'true');
                                    form.data('performCheck', 'true');
                                    form.submit();
                                },
                                function (data, err) {
                                    var error_message = data['message'];
                                    var params = {};
                                    params.position = {
                                        my: 'bottom left',
                                        at: 'top left',
                                        container: form
                                    };
                                    vtUtils.showValidationMessage(form.find('input[name="parent_affiliate_code"]'), error_message, params);
                                    form.find('a#parent_ib_tree').addClass('disabled');
                                    return false;
                                }
                        );
                    } else {
                        jQuery('[name="EditView"]').find('.saveButton').removeAttr('disabled');
                        form.removeData('submit');
                    }
                }
            }
        };
        form.vtValidate(params);
    },
    checkParentAffiliateCode: function (details) {
        var aDeferred = jQuery.Deferred();
        var params = {
            'module': 'Contacts',
            'action': 'EditAjax',
            'mode': 'checkParentAffiliateCode',
            'parent_affiliate_code': details.parent_affiliate_code,
            'affiliate_code': details.affiliate_code, /* Added By Reena 12_03_2020 */
            'contact_id': details.contact_id,
        };
        app.request.post({'data': params}).then(
                function (err, data) {
                    if (err === null) {
                        var result = data['success'];
                        if (result == true) {
                            aDeferred.reject(data);
                        } else {
                            aDeferred.resolve(data);
                        }
                    }
                });
        return aDeferred.promise();
    },
    /*@ Add by:- Divyesh
     *@ Date:- 04-02-2020
     *@ Comment:-Get liveAccount Based on contacts 
     * */
    getPopUpParams: function (container) {
        var params = this._super(container);
        var sourceFieldElement = jQuery('input[class="sourceField"]', container);
        if (!sourceFieldElement.length) {
            sourceFieldElement = jQuery('input.sourceField', container);
        }
        if (sourceFieldElement.attr('name') == 'parent_ibcommissionprofileid' || sourceFieldElement.attr('name') == 'child_ibcommissionprofileid') {
            params['search_key'] = 'ibcomm_status';
            params['search_value'] = 'Published';
            params['operator'] = 'e';
        }
        return params;
    },
    registerParentTreeButtonEvent: function (container) {
        var thisInstance = this;
        jQuery('#parent_ib_tree').on('click', function (e) {
            var currentAffiliateCode = jQuery('input[name="parent_affiliate_code"]').val();
            var url = 'ib_parent_hierarchy.php?parent_affiliate_code=' + window.btoa(currentAffiliateCode);
            window.open(url,'_blank');
        });
        jQuery('input[name="parent_affiliate_code"]').on('keyup', function (e) {
            var currentParentAffiliateCode = jQuery(this).val();
            if(currentParentAffiliateCode === '')
            {
                jQuery('#parent_ib_tree').addClass('disabled');
            }
            else
            {
                jQuery('#parent_ib_tree').removeClass('disabled');
            }
        });
        jQuery('input[name="parent_affiliate_code"]').on('change', function (e) {
            thisInstance.toggleMaxCommFields(container);
        });
        jQuery('input[name="parent_affiliate_code"]').trigger('keyup');
    },
    toggleMaxCommFields: function (container) {
        var currentParentCode = jQuery('input[name="parent_affiliate_code"]', container).val();
        var isAllowMaxCommDist = jQuery('#is_allow_max_ib_comm', container).val();
        if(currentParentCode === '' && isAllowMaxCommDist === '1')
        {
            jQuery('#fieldLabel_is_dist_max_comm', container).removeClass('hide');
            jQuery('#fieldValue_is_dist_max_comm', container).removeClass('hide');
            jQuery('#fieldValue_is_dist_max_comm', container).find('select').prop('disabled',false);
            jQuery('#fieldLabel_comm_amount_per_lot', container).removeClass('hide');
            jQuery('#fieldValue_comm_amount_per_lot', container).removeClass('hide');
            jQuery('#fieldValue_comm_amount_per_lot', container).find('input').prop('disabled',false);
        }
        else
        {
            jQuery('#fieldLabel_is_dist_max_comm', container).addClass('hide');
            jQuery('#fieldValue_is_dist_max_comm', container).addClass('hide');
            jQuery('#fieldValue_is_dist_max_comm', container).find('select').prop('disabled',true);
            jQuery('#fieldLabel_comm_amount_per_lot', container).addClass('hide');
            jQuery('#fieldValue_comm_amount_per_lot', container).addClass('hide');
            jQuery('#fieldValue_comm_amount_per_lot', container).find('input').prop('disabled',true);
        }
    },
    registerIBCommDistEvent: function (container) {
        var thisInstance = this;
        thisInstance.toggleMaxCommFields(container);
        
        /*Distribute Max Comm. field on change event*/
        jQuery('select[name="is_dist_max_comm"]', container).on('change', function (e) {
            var isDisMaxCommAllowed = jQuery(this).val();
            if(isDisMaxCommAllowed === 'Yes')
            {
                container.find('[name="parent_affiliate_code"]').parents('td').addClass('disabled');
                container.find('input[name="comm_amount_per_lot"]').parents('td').removeClass('disabled');
                
                jQuery('input[name="comm_amount_per_lot"]', container).addClass('required');
                jQuery('td#fieldLabel_comm_amount_per_lot', container).append('<span class="redColor">*</span>');
                jQuery('td#fieldLabel_comm_amount_per_lot span.redColor', container).css('color', 'red');
                container.find('input[name="comm_amount_per_lot"]').prop('data-rule-currency','true');
                container.find('input[name="comm_amount_per_lot"]').prop('min','0.1');
                if(jQuery('input[name="comm_amount_per_lot"]', container).val() === '')
                {
                    container.find('input[name="comm_amount_per_lot"]').focus();
                }
            }
            else
            {
                container.find('[name="parent_affiliate_code"]').parents('td').removeClass('disabled');
                container.find('input[name="comm_amount_per_lot"]').parents('td').addClass('disabled');
                
                container.find('input[name="comm_amount_per_lot"]').removeClass('required');
                container.find('input[name="comm_amount_per_lot"]').removeAttr('data-rule-currency');
                container.find('input[name="comm_amount_per_lot"]').removeAttr('min');
                jQuery('td#fieldLabel_comm_amount_per_lot span.redColor', container).remove();
            }
        });
        
        jQuery('select[name="is_dist_max_comm"]', container).trigger('change');
    },
    /*End*/
    registerBasicEvents: function (container) {
        this._super(container);
        this.registerEventForCopyingAddress(container);
        this.registerRecordPreSaveEvent(container);
        this.registerReferenceSelectionEvent(container);
        this.registerCustomEvent(container);
        this.registerParentTreeButtonEvent(container);
        this.registerIBCommDistEvent(container);
        this.registerValidation();
    }
})