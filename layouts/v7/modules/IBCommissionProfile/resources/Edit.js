/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("IBCommissionProfile_Edit_Js", {}, {

    registerEventForPicklistDependencySetup: function (container) {

        jQuery(document).ready(function () {
            jQuery('body').on('change', '#live_metatrader_type', function () {
                var container = jQuery("[name='BulkIBCommissionForm']");
                var picklistDependcyElemnt = jQuery('[name="picklistDependency"]', container);
                if (picklistDependcyElemnt.length <= 0) {
                    return;
                }
                var picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());

                var sourcePicklists = Object.keys(picklistDependencyMapping);
                if (sourcePicklists.length <= 0) {
                    return;
                }

                var sourcePickListNames = "";
                for (var i = 0; i < sourcePicklists.length; i++) {
                    if (i != sourcePicklists.length - 1) {
                        sourcePickListNames += '[name="' + sourcePicklists[i] + '"],';
                    } else {
                        sourcePickListNames += '[name="' + sourcePicklists[i] + '"]';
                    }
                }

                //    var sourcePickListElements = container.find(sourcePickListNames);
                var currentElement = jQuery(this);
                var sourcePicklistname = currentElement.attr('name');
                var configuredDependencyObject = picklistDependencyMapping[sourcePicklistname];
                var selectedValue = currentElement.val();
                var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
                var picklistmap = configuredDependencyObject["__DEFAULT__"];

                if (typeof targetObjectForSelectedSourceValue == 'undefined') {
                    targetObjectForSelectedSourceValue = picklistmap;
                }

                jQuery.each(picklistmap, function (targetPickListName, targetPickListValues) {
                    if(targetPickListName == '')
                    {
                        return;
                    }
                    var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
                    if (typeof targetPickListMap == "undefined") {
                        targetPickListMap = targetPickListValues;
                    }
                    //  var targetPickList = jQuery('#IBCommissionProfile_Edit_fieldName_' + targetPickListName, container);
                    var targetPickList = jQuery('#' + targetPickListName, container);
                    if (targetPickList.length <= 0) {
                        return;
                    }
                    var listOfAvailableOptions = targetPickList.data('availableOptions');
                    if (typeof listOfAvailableOptions == "undefined") {
                        listOfAvailableOptions = jQuery('option', targetPickList);
                        targetPickList.data('available-options', listOfAvailableOptions);
                    }
                    var targetOptions = new jQuery();
                    var optionSelector = [];
                    optionSelector.push('');
                    for (var i = 0; i < targetPickListMap.length; i++) {
                        optionSelector.push(targetPickListMap[i]);
                    }
                    jQuery.each(listOfAvailableOptions, function (i, e) {
                        var picklistValue = jQuery(e).val();
                        if (jQuery.inArray(picklistValue, optionSelector) != -1) {
                            targetOptions = targetOptions.add(jQuery(e));
                        }
                    })
                    var targetPickListSelectedValue = '';
                    var targetPickListSelectedValue = targetOptions.filter('[selected]').val();
                    if (targetPickListMap.length == 1) {
                        var targetPickListSelectedValue = targetPickListMap[0];                                                                                                                                 // to automatically select picklist if only one picklistmap is present.
                    }
//                    if ((targetPickListName == 'group_id' || targetPickListName == 'assigned_user_id') && jQuery("[name=" + sourcePicklistname + "]").val() == '') {
//                        return false;
//                    }
                    targetPickList.html(targetOptions).val(targetPickListSelectedValue).trigger("change");
                })
            });

            jQuery('body').on('change', '#security', function () {
                var container = jQuery("[name='BulkIBCommissionForm']");
                var picklistDependcyElemnt = jQuery('[name="securitySymbolDependency"]', container);
                if (picklistDependcyElemnt.length <= 0) {
                    return;
                }
                var picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());

                var sourcePicklists = Object.keys(picklistDependencyMapping);
                if (sourcePicklists.length <= 0) {
                    return;
                }

                var currentElement = jQuery(this);
                var sourcePicklistname = 'security';
                var configuredDependencyObject = picklistDependencyMapping[sourcePicklistname];
                var selectedValue = currentElement.val();
                var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
                var picklistmap = configuredDependencyObject["__DEFAULT__"];

                if (typeof targetObjectForSelectedSourceValue == 'undefined') {
                    targetObjectForSelectedSourceValue = picklistmap;
                }

                jQuery.each(picklistmap, function (targetPickListName, targetPickListValues) {
                    if(targetPickListName == '')
                    {
                        return;
                    }
                    var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
                    if (typeof targetPickListMap == "undefined") {
                        targetPickListMap = targetPickListValues;
                    }
                    //  var targetPickList = jQuery('#IBCommissionProfile_Edit_fieldName_' + targetPickListName, container);
                    var targetPickList = jQuery('#' + targetPickListName, container);
                    if (targetPickList.length <= 0) {
                        return;
                    }
                    var listOfAvailableOptions = targetPickList.data('availableOptions');
                    if (typeof listOfAvailableOptions == "undefined") {
                        listOfAvailableOptions = jQuery('option', targetPickList);
                        targetPickList.data('available-options', listOfAvailableOptions);
                    }
                    var targetOptions = new jQuery();
                    var optionSelector = [];
                    optionSelector.push('');
                    for (var i = 0; i < targetPickListMap.length; i++) {
                        optionSelector.push(targetPickListMap[i]);
                    }
                    jQuery.each(listOfAvailableOptions, function (i, e) {
                        var picklistValue = jQuery(e).val();
                        if (jQuery.inArray(picklistValue, optionSelector) != -1) {
                            targetOptions = targetOptions.add(jQuery(e));
                        }
                    })
                    var targetPickListSelectedValue = '';
                    var targetPickListSelectedValue = targetOptions.filter('[selected]').val();
                    if (targetPickListMap.length == 1) {
                        var targetPickListSelectedValue = targetPickListMap[0];                                                                                                                                 // to automatically select picklist if only one picklistmap is present.
                    }
                    targetPickList.html(targetOptions).val(targetPickListSelectedValue).trigger("change");
                })
            });
            //To Trigger the change on load
            jQuery('#live_metatrader_type').trigger('change');
        });

    },

    registorChangeEvent: function (container) {
            jQuery('body').on('change', '#ib_commission_type', function () {
                var commission_type = jQuery(this).val();
                var secondaryModulesContainer = jQuery('#security');
                if (commission_type == 'FIX' || commission_type == 'PIP' || commission_type == 'Percentage') {
                    jQuery('#security').toggleClass('required', commission_type);
                    jQuery('td#fieldLabel_security .muted').children().remove();
                    jQuery('td#fieldLabel_security .muted').append('<span class="redColor">*</span>');
                    jQuery("#security").removeAttr('disabled', 'disabled');
                    
                    if(commission_type == 'PIP')
                    {
                        jQuery("#security").val('').trigger('change');
                        secondaryModulesContainer.select2('destroy');
                        secondaryModulesContainer.removeAttr('multiple');
                        vtUtils.showSelect2ElementView(secondaryModulesContainer,{maximumSelectionSize: 1});
                        jQuery('#fieldLabel_symbol').removeClass('hide');
                        jQuery('#fieldValue_symbol').removeClass('hide');
                    }
                    else
                    {
                        jQuery("#security").val('').trigger('change');
                        secondaryModulesContainer.attr('multiple', 'true');
                        vtUtils.showSelect2ElementView(secondaryModulesContainer,{maximumSelectionSize: ''});
                        jQuery('#fieldLabel_symbol').addClass('hide');
                        jQuery('#fieldValue_symbol').addClass('hide');
                    }
                } else {
                    jQuery("#security").val('').trigger('change');
                    vtUtils.showSelect2ElementView(secondaryModulesContainer,{maximumSelectionSize: ''});
                    jQuery("#security").attr('disabled', 'disabled');
                    jQuery('td#fieldLabel_security .muted').children().remove();
                    jQuery('select[name="security[]"]').removeClass('required');
                    jQuery('#security').removeClass('input-error');
                    jQuery('#fieldLabel_symbol').addClass('hide');
                    jQuery('#fieldValue_symbol').addClass('hide');
                }
            });

            jQuery('body').on('change', 'input[name="ib_commission_value[]"]', function (e) {
                var element = jQuery(e.currentTarget);
                var commType = element.parents('tr').find('input[name="ib_commission_type[]"]').val();
                if(commType.toLowerCase() == 'percentage')
                {
                    var commission_val = jQuery(this).val();
                    if (commission_val)
                    {
                        var RegExpression = /^100$|^\d{0,2}(\.\d{1,4})? *%?$/;
                        if (!commission_val.match(RegExpression))
                        {
                            alert(app.vtranslate('JS_IBCOMM_PERCENTAGE_VALIDATION'));
                            jQuery(this).val('').focus();
                            return false;
                        }
                    }
                }
            });
    },
    /*
     * Function to get Field parent element
     */
    getParentElement: function (element) {
        var parent = element.closest('td');
        // added to support from all views which may not be table format
        if (parent.length === 0) {
            parent = element.closest('.td').length ?
                    element.closest('.td') : element.closest('.fieldValue');
        }
        return parent;
    },
    /**
     * Function which will register event for create of reference record
     * This will allow users to create reference record from edit view of other record
     */
    registerReferenceCreate: function (container) {
        var thisInstance = this;
        container.on('click', '#addIBCommission', function (e) {
            var url = "index.php?module=IBCommissionProfile&view=IBCommissionPopup";
            app.request.get({url: url}).then(
                    function (err, data) {
                        if (err) {
                            app.helper.showErrorNotification(err);
                            return;
                        }
                        var callback = function (data) {
                            var form = jQuery('#BulkIBCommissionForm');
                            var popupHeader = app.vtranslate('JS_IBCOMM_ITEM_ADD');
                            jQuery('#ibcommitem_header').text(popupHeader);
                            thisInstance.registerSaveIBCommissionItems(form, "ADD");
                            app.event.trigger('post.QuickCreateForm.show',form);
                        }
                        var params = {};
                        params.cb = callback;
                        app.helper.showModal(data, params);
                    });
        });
        container.on('click', '#updateIBCommission', function (e) {
            var url = "index.php?module=IBCommissionProfile&view=IBCommissionPopup";
            app.request.get({url: url}).then(
                    function (err, data) {
                        if (err) {
                            app.helper.showErrorNotification(err);
                            return;
                        }
                        var callback = function (data) {
                            var form = jQuery('#BulkIBCommissionForm');
                            var popupHeader = app.vtranslate('JS_IBCOMM_ITEM_UPDATE');
                            jQuery('#ibcommitem_header').text(popupHeader);
                            thisInstance.registerSaveIBCommissionItems(form, "UPDATE");
                        }
                        var params = {};
                        params.cb = callback;
                        app.helper.showModal(data, params);
                    });
        });
    },

    registerPostQuickCreateEvent : function(){
		var thisInstance = this;
		app.event.on("post.QuickCreateForm.show",function(event,form){
            // var BulkIBCommissionFormContainer = jQuery("[name='BulkIBCommissionForm']");
            thisInstance.registerEventForPicklistDependencySetup(form);
            thisInstance.registorChangeEvent(form);
		});
	},

    getColumn: function (value, fieldName, styleClass, fieldType, dataFieldName, isRequired, readonly, addStyle) {
        var readonly = readonly == true ? "readonly" : "";
        var html = ' <td class="fieldValue" >' +
                '<input type="text"  value="' + value + '" name="' + fieldName + '" class="' + styleClass + '" data-fieldtype="' + fieldType + '" data-fieldname="' + dataFieldName + '" data-rule-required="' + isRequired + '"   aria-required="' + isRequired + '" ' + addStyle + ' ' + readonly + '>';
        return html;
    },
    getAllColumns: function (element, id) {
        var thisInstance = this;

        var trHtml = "";
        var styleClass = "inputElement nameField";
        var fieldType = "string";
        trHtml += ' <input type="hidden" name ="ibcommissionprofileitemsid[]" value="' + id + '">';
        trHtml += thisInstance.getColumn(element.live_metatrader_type, "live_metatrader_type[]", styleClass, fieldType, "live_metatrader_type", false, true, 'style="width: 85px;"');
        trHtml += thisInstance.getColumn(element.ibcommission_level, "ibcommission_level[]", styleClass, fieldType, "ibcommission_level", false, true, 'style="width: 95px;"');
        trHtml += thisInstance.getColumn(element.security, "security[]", styleClass, fieldType, "security", false, true, 'style="width: 120px;"');
        trHtml += thisInstance.getColumn(element.symbol, "symbol[]", styleClass, fieldType, "symbol", false, true, 'style="width: 120px;"');
        trHtml += thisInstance.getColumn(element.live_label_account_type, "live_label_account_type[]", styleClass, fieldType, "live_label_account_type", false, true, '');
        trHtml += thisInstance.getColumn(element.live_currency_code, "live_currency_code[]", styleClass, fieldType, "live_currency_code", false, true, 'style="width: 80px;"');
        trHtml += thisInstance.getColumn(element.ib_commission_type, "ib_commission_type[]", styleClass, fieldType, "ib_commission_type", true, true, '');
        trHtml += thisInstance.getColumn(element.ib_commission_value, "ib_commission_value[]", styleClass, fieldType, "ib_commission_value", true, false, 'style="width: 120px;"');
        return trHtml;
    },
    registerSaveIBCommissionItems: function (form, type) {
        var thisInstance = this;
        jQuery('#BulkIBCommissionForm').vtValidate({
            submitHandler: function () {

                var params = form.serializeFormData();
                params['module'] = app.getModuleName();
                params['action'] = 'GetIBCommCombinationAjax';
                app.helper.showProgress();
                app.request.post({data: params}).then(
                        function (err, data) {
                            var html = "";
                            data.forEach(function (element) {
                                var idArr = [element.live_metatrader_type, element.ibcommission_level, element.security, element.symbol, element.live_label_account_type, element.live_currency_code];
                                var id = jQuery.trim(idArr.join("-"));
                                var id = id.replace(/\s/g, '');
                                var id = id.replaceAll('/', '');
                                if (jQuery("#" + id).html() == undefined) {
                                    html += '<tr id="' + id + '" style="background: red;">';

                                    //alert(element.security);
                                    html += thisInstance.getAllColumns(element, "");
                                    html += '</tr >';
                                } else {
                                    if (type = "UPDATE") {
//                                    html += thisInstance.getAllColumns(element);
                                        var recordId = "";
                                        if (jQuery("#" + id + "_id").val() != undefined) {
                                            recordId = jQuery("#" + id + "_id").val();
                                        }
                                        jQuery("#" + id).css("background", "green");
                                        jQuery("#" + id).html(thisInstance.getAllColumns(element, recordId));
//                                        jQuery("#" + id+"_id").val();
                                    }
                                }
                            });

                            app.helper.hideProgress();
                            jQuery('#IBCommissionItemDetails tr:first').after(html);
                            app.helper.hideModal();
                        });
                return false;
            }
        });
    },

    /** 
     * Function to register Basic Events
     * @returns {undefined}
     */
    registerBasicEvents: function (container) {
//        app.event.on('post.editView.load', function (event, container) {
//        });
        this._super(container);
        var BulkIBCommissionFormContainer = jQuery("[name='BulkIBCommissionForm']");
        // this.registerEventForPicklistDependencySetup(BulkIBCommissionFormContainer);
        this.registorChangeEvent(BulkIBCommissionFormContainer);
    },
})