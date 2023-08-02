/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("Payments_Edit_Js", {}, {

    /**
     * Function to get popup params
     */
    getPopUpParams: function (container) {
        var params = this._super(container);
        var sourceFieldElement = jQuery('input[class="sourceField"]', container);
        if (!sourceFieldElement.length) {
            sourceFieldElement = jQuery('input.sourceField', container);
        }
//  alert(sourceFieldElement.attr('name'))
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
                params['column3'] = 'live_currency_code';
                params['value3'] = currencyCodeElement.val();
                params['related_parent_module'] = related_parent_module;
            }
        }
        return params;
    },
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
    registerRemoveOptionEvent: function (container) {
        var form = container.closest('form');
        var recordId = form.find('[name="record"]').val();
        var payment_status = form.find('[name="payment_status"]').val();
        var payment_process = form.find('[name="payment_process"]').val();
        var payment_type = form.find('[name="payment_type"]').val();
        var payment_operation = form.find('[name="payment_operation"]').val();
        var failure_reason = form.find('[name="failure_reason"]').val();
        if (failure_reason != '') {
            container.find('[name="failure_reason"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        }
        if (recordId) {
            container.find('[name="payment_process"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
            if (payment_status == 'Failed') {
                jQuery('#field_payment_status option').each(function () {
                    if (jQuery(this).val() != 'InProgress' && $(this).val() != payment_status && jQuery(this).val() != '') {
                        jQuery(this).remove();
                    }
                });
            } else if (payment_status == 'Pending') {
                jQuery('#field_payment_status option').each(function () {
                    if (jQuery(this).val() != 'InProgress' && jQuery(this).val() != 'Rejected' && $(this).val() != payment_status && jQuery(this).val() != '') {
                        jQuery(this).remove();
                    }
                });
            } else if (payment_status == 'PaymentSuccess') {
                jQuery('#field_payment_status option').each(function () {
                    if (jQuery(this).val() != 'Confirmed' && jQuery(this).val() != 'Rejected' && $(this).val() != payment_status && jQuery(this).val() != '') {
                        jQuery(this).remove();
                    }
                });
            }


//                  if (payment_operation == 'Deposit') {
//                        if (payment_type == 'P2A') {
//                              if (payment_status == 'Failed' && payment_process == 'Account') {
//                                    jQuery('#field_payment_status option').each(function () {
//                                          if (jQuery(this).val() != 'InProgress' && $(this).val() != payment_status && $(this).val() != '') {
//                                                jQuery(this).remove();
//                                          }
//                                    });
//                              } else if (payment_status == 'InProgress' && payment_process == 'PSP') {
//                                    jQuery('#field_payment_status option').each(function () {
//                                          if (jQuery(this).val() != 'Confirmed' && jQuery(this).val() != 'Failed' && $(this).val() != payment_status && $(this).val() != '') {
//                                                jQuery(this).remove();
//                                          }
//                                    });
//                              } else if (payment_status == 'Pending' && payment_process == 'PSP') {
//                                    jQuery('#field_payment_status option').each(function () {
//                                          if (jQuery(this).val() != 'InProgress' && jQuery(this).val() != 'Rejected' && $(this).val() != payment_status && $(this).val() != '') {
//                                                jQuery(this).remove();
//                                          }
//                                    });
//                              }
//                        } else if (payment_type == 'P2E') {
//                              if (payment_status == 'Failed' && payment_process == 'Wallet') {
//                                    jQuery('#field_payment_status option').each(function () {
//                                          if (jQuery(this).val() != 'InProgress' && $(this).val() != payment_status && $(this).val() != '') {
//                                                jQuery(this).remove();
//                                          }
//                                    });
//                              } else if (payment_status == 'Pending' && payment_process == 'Wallet') {
//                                    jQuery('#field_payment_status option').each(function () {
//                                          if (jQuery(this).val() != 'InProgress' && jQuery(this).val() != 'Rejected' && $(this).val() != payment_status && $(this).val() != '') {
//                                                jQuery(this).remove();
//                                          }
//                                    });
//                              }
//                        }
//                  } else if (payment_operation == 'Withdrawal') {
//                        if (payment_type == 'A2P') {
//                              if (payment_status == 'Failed' && payment_process == 'PSP') {
//                                    jQuery('#field_payment_status option').each(function () {
//                                          if (jQuery(this).val() != 'InProgress' && $(this).val() != payment_status && $(this).val() != '') {
//                                                jQuery(this).remove();
//                                          }
//                                    });
//                              } else if (payment_status == 'Pending' && payment_process == 'Account') {
//                                    jQuery('#field_payment_status option').each(function () {
//                                          if (jQuery(this).val() != 'InProgress' && jQuery(this).val() != 'Rejected' && $(this).val() != payment_status && $(this).val() != '') {
//                                                jQuery(this).remove();
//                                          }
//                                    });
//                              }
//                        } else if (payment_type == 'E2P') {
//                              if (payment_status == 'Failed' && payment_process == 'Wallet') {
//                                    jQuery('#field_payment_status option').each(function () {
//                                          if (jQuery(this).val() != 'InProgress' && $(this).val() != payment_status && $(this).val() != '') {
//                                                jQuery(this).remove();
//                                          }
//                                    });
//                              } else if (payment_status == 'Pending' && payment_process == 'Wallet') {
//                                    jQuery('#field_payment_status option').each(function () {
//                                          if (jQuery(this).val() != 'InProgress' && jQuery(this).val() != 'Rejected' && $(this).val() != payment_status && $(this).val() != '') {
//                                                jQuery(this).remove();
//                                          }
//                                    });
//                              }
//                        }
//                  }

        } else {
            jQuery('#field_payment_status option').each(function () {
                if (jQuery(this).val() != '' && jQuery(this).val() != 'InProgress' && jQuery(this).val() != 'Pending') {
                    jQuery(this).remove();
                }
            });
            jQuery('select[name="payment_operation"] option').each(function () {
                if (jQuery(this).val() == 'IBCommission') {
                    jQuery(this).remove();
                }
            });
        }
    },
    registerDropdownChangeEvent: function (container) {

        var form = container.closest('form');
        var paymentOperationElement = form.find('[name="payment_operation"]');
        var recordId = form.find('[name="record"]').val();
        if (recordId) {
            container.find('[name="payment_operation"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
            container.find('[name="contactid"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
            // container.find('[name="payment_type"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
            container.find('[id="s2id_field_payment_type"]').css('pointer-events', 'none');
            container.find('[name="payment_currency"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
            container.find('[name="payment_from"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
            container.find('[name="payment_to"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
        }
//            container.find('[name="payment_process"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
//            container.find('[name="payment_process"]').parents('td').addClass('hide');
//            jQuery('#fieldLabel_payment_process').addClass('hide');


        jQuery('select[name="payment_operation"]').on('change', function (e) {
            jQuery('[name="payment_type"]', container).val('').trigger("liszt:updated");
            jQuery('[name="payment_type"]', container).trigger('change');
            jQuery('[name="payment_from"]', container).val('').trigger("liszt:updated");
            jQuery('[name="payment_from"]', container).trigger('change');
            jQuery('[name="payment_to"]', container).val('').trigger("liszt:updated");
            jQuery('[name="payment_to"]', container).trigger('change');
            jQuery('option.P2A').remove();
            jQuery('option.P2E').remove();
            jQuery('option.A2P').remove();
            jQuery('option.E2P').remove();
            jQuery('option.E2E').remove();
            jQuery('option.A2A').remove();
        });
        jQuery('select[name="payment_currency"]').on('change', function (e) {
            jQuery('[name="payment_from"]', container).val('').trigger("liszt:updated");
            jQuery('[name="payment_from"]', container).trigger('change');
            jQuery('[name="payment_to"]', container).val('').trigger("liszt:updated");
            jQuery('[name="payment_to"]', container).trigger('change');
            jQuery('option.P2A').remove();
            jQuery('option.P2E').remove();
            jQuery('option.A2P').remove();
            jQuery('option.E2P').remove();
            jQuery('option.E2E').remove();
            jQuery('option.A2A').remove();
        });
        jQuery('select[name="payment_type"]').on('change', function (e) {

            //alert(paymentOperationElement.val())
            var payment_type = jQuery(this).val();
            var form = container.closest('form');
            // var formId = jQuery('form.recordEditView').attr('id');
            if (paymentOperationElement.val() == 'Deposit') {
                if (payment_type == 'P2A') {

                    jQuery('div#s2id_field_payment_to').show();
                    jQuery('select#field_payment_to').show();
                    jQuery('select#field_payment_to option:selected').attr('disabled', false);
                    jQuery('input#Payments_editView_fieldName_payment_to').remove();
                    var params = {
                        'module': 'Payments',
                        'action': 'GetPaymentsData',
                        'mode': 'OperationPaymentGatewayToAccount',
                        'payment_operation': form.find('[name="payment_operation"]').val(),
                        'payment_type': payment_type,
                        'contactid': form.find('[name="contactid"]').val(),
                        'record_status': 'Approved',
                        'live_currency_code': form.find('[name="payment_currency"]').val(),
                    }
                    app.request.get({data: params}).then(function (error, data) {
                        //  jQuery('option.P2A').remove();
                        jQuery('option.P2E').remove();
                        jQuery('option.A2P').remove();
                        jQuery('option.E2P').remove();
                        jQuery('option.E2E').remove();
                        jQuery('option.A2A').remove();
                        //var targetPickList = jQuery('[name="payment_from"]', container);
                        jQuery('[name="payment_from"]', container).val('').trigger("liszt:updated");
                        jQuery('[name="payment_from"]', container).trigger('change');
                        //var targetPickList1 = jQuery('[name="payment_to"]', container);
                        jQuery('[name="payment_to"]', container).val('').trigger("liszt:updated");
                        jQuery('[name="payment_to"]', container).trigger('change');
                        var FromElement = container.find('[name="payment_from"]');
                        FromElement.append(data['from_value']);
                        var ToElement = container.find('[name="payment_to"]');
                        ToElement.append(data['to_value']);
                    });
                } else if (payment_type == 'P2E') {
                    jQuery('div#s2id_field_payment_to').show();
                    jQuery('select#field_payment_to').show();
                    jQuery('select#field_payment_to option:selected').attr('disabled', false);
                    jQuery('input#Payments_editView_fieldName_payment_to').remove();
                    var params = {
                        'module': 'Payments',
                        'action': 'GetPaymentsData',
                        'mode': 'OperationPaymentGatewayToEWallet',
                        'payment_operation': form.find('[name="payment_operation"]').val(),
                        'payment_type': payment_type,
                        'contactid': form.find('[name="contactid"]').val(),
                        'record_status': 'Approved',
                        'live_currency_code': form.find('[name="payment_currency"]').val(),
                    }
                    app.request.get({data: params}).then(function (error, data) {
                        jQuery('option.P2A').remove();
                        //jQuery('option.P2E').remove();
                        jQuery('option.A2P').remove();
                        jQuery('option.E2P').remove();
                        jQuery('option.E2E').remove();
                        jQuery('option.A2A').remove();
                        //var targetPickList = jQuery('[name="payment_from"]', container);
                        jQuery('[name="payment_from"]', container).val('').trigger("liszt:updated");
                        jQuery('[name="payment_from"]', container).trigger('change');
                        //var targetPickList1 = jQuery('[name="payment_to"]', container);
                        jQuery('[name="payment_to"]', container).val('').trigger("liszt:updated");
                        jQuery('[name="payment_to"]', container).trigger('change');
                        var FromElement = container.find('[name="payment_from"]');
                        FromElement.append(data['from_value']);
                        var ToElement = container.find('[name="payment_to"]');
                        ToElement.append(data['to_value']);
                    });
                }
            } else if (paymentOperationElement.val() == 'Withdrawal') {
                if (payment_type == 'A2P') {
                    jQuery('div#s2id_field_payment_to').show();
                    jQuery('select#field_payment_to').show();
                    jQuery('select#field_payment_to option:selected').attr('disabled', false);
                    jQuery('input#Payments_editView_fieldName_payment_to').remove();
                    var params = {
                        'module': 'Payments',
                        'action': 'GetPaymentsData',
                        'mode': 'OperationAccountToPaymentGateway',
                        'payment_operation': form.find('[name="payment_operation"]').val(),
                        'payment_type': payment_type,
                        'contactid': form.find('[name="contactid"]').val(),
                        'record_status': 'Approved',
                        'live_currency_code': form.find('[name="payment_currency"]').val(),
                    }
                    app.request.get({data: params}).then(function (error, data) {
                        jQuery('option.P2A').remove();
                        jQuery('option.P2E').remove();
                        // jQuery('option.A2P').remove();
                        jQuery('option.E2P').remove();
                        jQuery('option.E2E').remove();
                        jQuery('option.A2A').remove();
                        //var targetPickList = jQuery('[name="payment_from"]', container);
                        jQuery('[name="payment_from"]', container).val('').trigger("liszt:updated");
                        jQuery('[name="payment_from"]', container).trigger('change');
                        //var targetPickList1 = jQuery('[name="payment_to"]', container);
                        jQuery('[name="payment_to"]', container).val('').trigger("liszt:updated");
                        jQuery('[name="payment_to"]', container).trigger('change');
                        var FromElement = container.find('[name="payment_from"]');
                        FromElement.append(data['from_value']);
                        var ToElement = container.find('[name="payment_to"]');
                        ToElement.append(data['to_value']);
                    });
                } else if (payment_type == 'E2P') {
                    jQuery('div#s2id_field_payment_to').show();
                    jQuery('select#field_payment_to').show();
                    jQuery('select#field_payment_to option:selected').attr('disabled', false);
                    jQuery('input#Payments_editView_fieldName_payment_to').remove();
                    var params = {
                        'module': 'Payments',
                        'action': 'GetPaymentsData',
                        'mode': 'OperationEWalletToPaymentGateway',
                        'payment_operation': form.find('[name="payment_operation"]').val(),
                        'payment_type': payment_type,
                        'contactid': form.find('[name="contactid"]').val(),
                        'record_status': 'Approved',
                        'live_currency_code': form.find('[name="payment_currency"]').val(),
                    }
                    app.request.get({data: params}).then(function (error, data) {
                        jQuery('option.P2A').remove();
                        jQuery('option.P2E').remove();
                        jQuery('option.A2P').remove();
                        // jQuery('option.E2P').remove();
                        jQuery('option.E2E').remove();
                        jQuery('option.A2A').remove();
                        //var targetPickList = jQuery('[name="payment_from"]', container);
                        jQuery('[name="payment_from"]', container).val('').trigger("liszt:updated");
                        jQuery('[name="payment_from"]', container).trigger('change');
                        //var targetPickList1 = jQuery('[name="payment_to"]', container);
                        jQuery('[name="payment_to"]', container).val('').trigger("liszt:updated");
                        jQuery('[name="payment_to"]', container).trigger('change');
                        var FromElement = container.find('[name="payment_from"]');
                        FromElement.append(data['from_value']);
                        var ToElement = container.find('[name="payment_to"]');
                        ToElement.append(data['to_value']);
                    });
                }
            } else if (paymentOperationElement.val() == 'InternalTransfer') {
                if (payment_type == 'E2E') {

                    jQuery('div#s2id_field_payment_to').hide();
                    jQuery('select#field_payment_to').hide();
                    jQuery('select#field_payment_to option:selected').attr('disabled', 'disabled');
                    jQuery('td#fieldValue_payment_to').append('<input id="Payments_editView_fieldName_payment_to" type="text" data-fieldname="payment_to" data-fieldtype="string" class="inputElement nameField" name="payment_to" value="" data-rule-required="true" aria-required="true">');
//                              jQuery('td#fieldValue_payment_to').html('<input id="Payments_editView_fieldName_payment_to" type="text" data-fieldname="payment_to" data-fieldtype="string" class="inputElement nameField" name="payment_to" value="" data-rule-required="true" aria-required="true">');
                    var params = {
                        'module': 'Payments',
                        'action': 'GetPaymentsData',
                        'mode': 'OperationEWalletToEWallet',
                        'payment_operation': form.find('[name="payment_operation"]').val(),
                        'payment_type': payment_type,
                        'contactid': form.find('[name="contactid"]').val(),
                        'record_status': 'Approved',
                        'live_currency_code': form.find('[name="payment_currency"]').val(),
                    }
                    app.request.get({data: params}).then(function (error, data) {
                        jQuery('option.P2A').remove();
                        jQuery('option.P2E').remove();
                        jQuery('option.A2P').remove();
                        jQuery('option.E2P').remove();
                        // jQuery('option.E2E').remove();
                        jQuery('option.A2A').remove();
                        //var targetPickList = jQuery('[name="payment_from"]', container);
                        jQuery('[name="payment_from"]', container).val('').trigger("liszt:updated");
                        jQuery('[name="payment_from"]', container).trigger('change');
                        //var targetPickList1 = jQuery('[name="payment_to"]', container);
                        jQuery('[name="payment_to"]', container).val('');
                        //jQuery('[name="payment_to"]', container).trigger('change');

                        var FromElement = container.find('[name="payment_from"]');
                        FromElement.append(data['from_value']);
//                                    var ToElement = container.find('[name="payment_to"]');
//                                    ToElement.append(data['to_value']);
                    });
                } else if (payment_type == 'A2A') {

                    jQuery('div#s2id_field_payment_to').show();
                    jQuery('select#field_payment_to').show();
                    jQuery('select#field_payment_to option:selected').attr('disabled', false);
                    jQuery('input#Payments_editView_fieldName_payment_to').remove();
                    var params = {
                        'module': 'Payments',
                        'action': 'GetPaymentsData',
                        'mode': 'OperationAccountToAccount',
                        'payment_operation': form.find('[name="payment_operation"]').val(),
                        'payment_type': payment_type,
                        'contactid': form.find('[name="contactid"]').val(),
                        'record_status': 'Approved',
                        'live_currency_code': form.find('[name="payment_currency"]').val(),
                    }
                    app.request.get({data: params}).then(function (error, data) {
                        jQuery('option.P2A').remove();
                        jQuery('option.P2E').remove();
                        jQuery('option.A2P').remove();
                        jQuery('option.E2P').remove();
                        jQuery('option.E2E').remove();
                        // jQuery('option.A2A').remove();

                        //var targetPickList = jQuery('[name="payment_from"]', container);
                        jQuery('[name="payment_from"]', container).val('').trigger("liszt:updated");
                        jQuery('[name="payment_from"]', container).trigger('change');
                        //var targetPickList1 = jQuery('[name="payment_to"]', container);
                        jQuery('[name="payment_to"]', container).val('').trigger("liszt:updated");
                        jQuery('[name="payment_to"]', container).trigger('change');
                        var FromElement = container.find('[name="payment_from"]');
                        FromElement.append(data['from_value']);
//                                    var ToElement = container.find('[name="payment_to"]');
//                                    ToElement.append(data['to_value']);
                    });
                }
            }

        });
        jQuery('select[name="payment_from"]').on('change', function (e) {
            var payment_from = jQuery(this).val();
            if (payment_from && paymentOperationElement.val() == 'InternalTransfer' && form.find('[name="payment_type"]').val() == 'A2A') {

                var params = {
                    'module': 'Payments',
                    'action': 'GetPaymentsData',
                    'mode': 'OperationAccountToAccount',
                    'payment_operation': form.find('[name="payment_operation"]').val(),
                    'payment_type': form.find('[name="payment_type"]').val(),
                    'contactid': form.find('[name="contactid"]').val(),
                    'record_status': 'Approved',
                    'live_currency_code': form.find('[name="payment_currency"]').val(),
                    'payment_from': payment_from,
                }
                app.request.get({data: params}).then(function (error, data) {
                    jQuery('option.P2A').remove();
                    jQuery('option.P2E').remove();
                    jQuery('option.A2P').remove();
                    jQuery('option.E2P').remove();
                    jQuery('option.E2E').remove();
                    // jQuery('option.A2A').remove();

                    jQuery('[name="payment_to"]', container).val('').trigger("liszt:updated");
                    jQuery('[name="payment_to"]', container).trigger('change');
                    var ToElement = container.find('[name="payment_to"]');
                    ToElement.html(data['to_value']);
                    form.find('[name="payment_to"]').find('[value="' + payment_from + '"]').remove();
                });
            }
        });
    },
    /*
     * @creator: Divyesh Chothani
     * @comment: Call ajax and append provider html into org server fields
     * @date: 17-10-2019
     */
    registerProviderTypeChangeEvent: function (container) {
        var self = this;
        // jQuery('#goToFullForm').hide();
        jQuery('td#fieldLabel_custom_data').remove();
        jQuery('td#fieldValue_custom_data').remove();
        var form = container.closest('form');
        jQuery(document).on('change', '#field_payment_from', function (e) {
            var paymentOperationElement = form.find('[name="payment_operation"]');
            var payment_operation = paymentOperationElement.val();
            var paymentTypeElement = form.find('[name="payment_type"]');
            var payment_type = paymentTypeElement.val();
            var currentTarget = jQuery(e.currentTarget);
            var selectedProviderName = currentTarget.val();
            if (selectedProviderName != '' && jQuery.trim(payment_operation) == 'Deposit' && (jQuery.trim(payment_type) == 'P2A' || jQuery.trim(payment_type) == 'P2E')) {

                var params = {
                    'module': 'Payments',
                    'action': 'GetHTML',
                    'provider': selectedProviderName,
                    'payment_operation': payment_operation,
                    'payment_type': payment_type,
                }
                app.helper.showProgress();
                app.request.get({data: params}).then(function (error, data) {
                    app.helper.hideProgress();
//                    alert(data)
                    if (data[0] == 1) {
                        jQuery('tr.payment_provider_fields').remove();
                        jQuery('table#lbl_payment_details').append(data[1]);
                        //jQuery('table#lbl_payment_details tr').eq(0).after(data);
                    } else if (data[0] == 0) {
                        jQuery('tr.payment_provider_fields').remove();

//                        var alertMessage = selectedProviderName + ' ' + app.vtranslate('JS_PROVIDER_FIELD_ISSUE');
//                        app.helper.showAlertNotification({"message": alertMessage});
                    }
                });
            }
        });
        jQuery(document).on('change', '#field_payment_to', function (e) {
            var paymentOperationElement = form.find('[name="payment_operation"]');
            var payment_operation = paymentOperationElement.val();
            var paymentTypeElement = form.find('[name="payment_type"]');
            var payment_type = paymentTypeElement.val();
            var currentTarget = jQuery(e.currentTarget);
            var selectedProviderName = currentTarget.val();
            if (selectedProviderName != '' && jQuery.trim(payment_operation) == 'Withdrawal' && (jQuery.trim(payment_type) == 'A2P' || jQuery.trim(payment_type) == 'E2P')) {
                var currentTarget = jQuery(e.currentTarget);
                var selectedProviderName = currentTarget.val();
                var params = {
                    'module': 'Payments',
                    'action': 'GetHTML',
                    'provider': selectedProviderName,
                    'payment_operation': payment_operation,
                    'payment_type': payment_type,
                }
                app.helper.showProgress();
                app.request.get({data: params}).then(function (error, data) {
                    app.helper.hideProgress();
                    if (data[0] == 1) {
                        jQuery('tr.payment_provider_fields').remove();
                        jQuery('table#lbl_payment_details').append(data[1]);
                        //jQuery('table#lbl_payment_details tr').eq(0).after(data);
                    } else if (data[0] == 0) {
                        jQuery('tr.payment_provider_fields').remove();

//                        var alertMessage = selectedProviderName + ' ' + app.vtranslate('JS_PROVIDER_FIELD_ISSUE');
//                        app.helper.showAlertNotification({"message": alertMessage});
                    }
                });
            }
        });
    },
//      registerDropdownChangeEvent: function (container) {
//            var selectEle = container.find('[name="payment_getways"]');
//            selectEle.append('<option value="test">test</option><option value="test1" >test1</option><option value="test2" >test2</option>');
//
//            // var formId = jQuery(this).closest("form").attr('id');
//            var form = container.closest('form');
//            var paymentOperationElement = form.find('[name="payment_operation"]');
//            jQuery('select[name="payment_type"]').on('change', function (e) {
//
//                  //alert(paymentOperationElement.val())
//                  var payment_type = jQuery(this).val();
//                  var formId = jQuery('form.recordEditView').attr('id');
//                  if (paymentOperationElement.val() == 'Deposit') {
//                        if (payment_type == 'P2A') {
//                              jQuery('td#fieldLabel_liveaccountid').show();
//                              jQuery('td#fieldValue_liveaccountid').show();
//                              if (formId == 'EditView') {
//                                    jQuery('td#fieldLabel_liveaccountid').html(app.vtranslate('To'));
//                                    jQuery('td#fieldLabel_payment_getways').html(app.vtranslate('From'));
//                              } else if (formId == 'QuickCreate') {
//                                    //'<label class="muted pull-right">To&nbsp;</label>'
////                                    '<label class="muted pull-right">From&nbsp;</label>'
//                                    jQuery('td#fieldLabel_liveaccountid .muted').html(app.vtranslate('To'));
//                                    jQuery('td#fieldLabel_payment_getways .muted').html(app.vtranslate('From'));
//                              }
//
//                        } else if (payment_type == 'P2E') {
//                              if (formId == 'EditView') {
//                                    jQuery('td#fieldLabel_contactid').html(app.vtranslate('To'));
//                                    jQuery('td#fieldLabel_payment_getways').html(app.vtranslate('From'));
//                              } else if (formId == 'QuickCreate') {
//                                    //'<label class="muted pull-right">To&nbsp;</label>'
////                                    '<label class="muted pull-right">From&nbsp;</label>'
//                                    jQuery('td#fieldLabel_contactid .muted').html(app.vtranslate('To'));
//                                    jQuery('td#fieldLabel_payment_getways .muted').html(app.vtranslate('From'));
//                              }
////                              jQuery('td#fieldLabel_liveaccountid').hide();
////                              jQuery('td#fieldValue_liveaccountid').hide();
////                              jQuery('td#fieldLabel_contactid').html(app.vtranslate('To'));
////                              jQuery('td#fieldLabel_payment_getways').html(app.vtranslate('From'));
//                              //alert(payment_type)
//                        }
//                  }
//
//            });
//      },
    /* *
     * Add By :-Reena Hingol
     * Date:- 07-02-2020
     * Comment :-Payments module validation for commission calculate
     * */
    registerCalculateCommission: function (container) {
        var form = container.closest('form');
        var recordId = form.find('[name="record"]').val();
        if (recordId) {
            jQuery('td#fieldValue_commission').css('pointer-events', 'none');
        }
        //Add for read only payment_amount and commission_valu field
        jQuery('td#fieldValue_payment_amount').css('pointer-events', 'none');
        jQuery('td#fieldValue_commission_value').css('pointer-events', 'none');
        jQuery('input[name="amount"],input[name="commission"]').on('focusout', function (e) {
            var operation = form.find('[name="payment_operation"]').val();
            var amount = form.find('[name="amount"]').val();
            var commission = form.find('[name="commission"]').val();
//            var operation = jQuery('select[name="payment_operation"]').val();
//            var amount = jQuery('input[name="amount"]').val();
//            var commission = jQuery('input[name="commission"]').val();
            if (operation == 'Deposit') {
                var commission_value = (amount * (commission / 100));
                var payment_amount = parseFloat(amount) + parseFloat(commission_value);
            } else if (operation == 'Withdrawal') {
                var commission_value = (amount * (commission / 100));
                var payment_amount = parseFloat(amount) - parseFloat(commission_value);
            } else {
                var commission_value = 0;
                var payment_amount = parseFloat(amount) + parseFloat(commission_value);
            }
            form.find('[name="commission_value"]').val(commission_value)
            form.find('[name="payment_amount"]').val(payment_amount)
        });
    },
    /*END*/
    /* *
     * Add By :-Reena Hingol
     * Date:- 11_03_2020
     * Comment :-Payments module :-while status is Rejected,reject reason should be mandatory and enabled
     * */
    registerStatusChangeEvent: function (container) {
        //jQuery('#Payments_editView_fieldName_reject_reason').attr('disabled', 'disabled');
        jQuery('select[name="payment_status"]').on('change', function (e) {
            var payment_status = jQuery(this).val();
            if (payment_status == 'Rejected') {
                jQuery('input[name=failure_reason]').toggleClass('required', payment_status);
                jQuery('td#fieldLabel_failure_reason').append('<span class="redColor">*</span>');
                jQuery("td#fieldLabel_failure_reason span.redColor").css('color', 'red');
                jQuery("#Payments_editView_fieldName_failure_reason").removeAttr('disabled', 'disabled');
            } else {
                jQuery("td#fieldLabel_failure_reason span.redColor").css('color', '#f7f7f9');
                jQuery('input[name=failure_reason]').removeClass('required');
                jQuery("#Payments_editView_fieldName_failure_reason").attr('disabled', 'disabled');
                jQuery("#Payments_editView_fieldName_failure_reason").val('');
                jQuery('td#fieldLabel_failure_reason span.redColor').detach();
                jQuery('#Payments_editView_fieldName_failure_reason').removeClass('input-error');
            }

        });
    },
    /*
     * * Add By Divyesh Chothani
     * Date:-05-02-2020 
     * Function to Validate and Save Event 
     * @returns {undefined}
     */
    registerValidation: function () {
        var thisInstance = this;
        // var formContainer = container.find('form');
        var editViewForm = this.getForm();
        var formId = editViewForm.attr('id');
        if (formId == 'EditView') {
            var form = jQuery('#EditView');
        } else {
            var form = jQuery('#QuickCreate');
        }

        var params = {
            submitHandler: function (form) {
                var form = jQuery(form);
                if (form.data('submit') === 'true' && form.data('performCheck') === 'true') {
                    return true;
                } else {
                    if (this.numberOfInvalids() <= 0) {
                        var formData = form.serializeFormData();
                        app.helper.showProgress();
                        thisInstance.validationPaymentOperation({
                            'payment_operation': formData.payment_operation,
                            'contactid': formData.contactid,
                            'payment_type': formData.payment_type,
                            'payment_process': formData.payment_process,
                            'payment_currency': formData.payment_currency,
                            'amount': formData.amount,
                            'commission': formData.commission,
                            'commission_value': formData.commission_value,
                            'payment_amount': formData.payment_amount,
                            'payment_status': formData.payment_status,
                            'reject_reason': formData.reject_reason,
                            'transaction_id': formData.transaction_id,
                            'comment': formData.comment,
                            'description': formData.description,
                            'failure_reason': formData.failure_reason,
                            'payment_from': formData.payment_from,
                            'payment_to': formData.payment_to,
                            'assigned_user_id': formData.assigned_user_id,
                            'record': formData.record,
                            'form_type': 'Save',
                        }).then(
                                function (data) {
                                    form.data('submit', 'true');
                                    form.data('performCheck', 'true');
                                    form.submit();
                                },
                                function (data, err) {
                                    app.helper.hideProgress();
                                    var error_message = data['message'];
                                    app.helper.showErrorNotification({message: error_message});
//                                    var params = {};
//                                    params.position = {
//                                        my: 'bottom left',
//                                        at: 'top left',
//                                        container: form
//                                    };
                                    //vtUtils.showValidationMessage(form.find('select[name="payment_operation"]'), error_message, params);
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
    validationPaymentOperation: function (details) {
        var aDeferred = jQuery.Deferred();
        var params = {
            'module': 'Payments',
            'action': 'EditAjax',
            'mode': 'validationPaymentOperation',
            'payment_operation': details.payment_operation,
            'contactid': details.contactid,
            'payment_type': details.payment_type,
            'payment_process': details.payment_process,
            'payment_currency': details.payment_currency,
            'amount': details.amount,
            'commission': details.commission,
            'commission_value': details.commission_value,
            'payment_amount': details.payment_amount,
            'payment_status': details.payment_status,
            'reject_reason': details.reject_reason,
            'transaction_id': details.transaction_id,
            'comment': details.comment,
            'description': details.description,
            'failure_reason': details.failure_reason,
            'payment_from': details.payment_from,
            'payment_to': details.payment_to,
            'assigned_user_id': details.assigned_user_id,
            'record': details.record,
            'form_type': details.form_type,
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

    registerCancel: function (e) {
        jQuery('.cancelLink').click(function () {
            var currentElement = jQuery(e.target);
            if(currentElement.parents('form#QuickCreate').length > 0)
            {
                app.helper.hideModal();
            }
            else
            {
                var urlParams = new URLSearchParams(window.location.search);
                var recordId = urlParams.get('record');
                if (recordId) {
                    window.location.replace("index.php?module=Payments&view=Detail&record=" + recordId + "&app=MARKETING");
                }
            }
            return false;
        });
    },
    /*END*/

    registerBasicEvents: function (container) {
        this._super(container);

        this.registerDropdownChangeEvent(container);
        this.registerRemoveOptionEvent(container);
        this.registerCalculateCommission(container); //Add by Reena 07_02_2020
        // this.registerProviderTypeChangeEvent(container);
        this.registerStatusChangeEvent(container); //Added By Reena 11_03_2020
        this.registerValidation();
        this.registerCancel();
        jQuery(document).ready(function () {
            jQuery('.saveButton',container).removeClass('disabled');
            jQuery('#Payments_editView_fieldName_ip_address').attr('disabled', true);
            jQuery('#Payments_editView_fieldName_order_id').attr('disabled', true);
        });
    }

});