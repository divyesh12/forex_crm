/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("IBCommissionProfileItems_Edit_Js", {}, {

    /**
     * Function to register event for setting up picklistdependency
     * for a module if exist on change of picklist value
     */
    registerEventForPicklistDependencySetup: function (container) {
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
            if (i != sourcePicklists.length - 1)
                sourcePickListNames += '[name="' + sourcePicklists[i] + '"],';
            else
                sourcePickListNames += '[name="' + sourcePicklists[i] + '"]';
        }
        var sourcePickListElements = container.find(sourcePickListNames);
        sourcePickListElements.on('change', function (e) {
            var currentElement = jQuery(e.currentTarget);
            var sourcePicklistname = currentElement.attr('name');
            var configuredDependencyObject = picklistDependencyMapping[sourcePicklistname];
            var selectedValue = currentElement.val();
            var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
            var picklistmap = configuredDependencyObject["__DEFAULT__"];
            if (typeof targetObjectForSelectedSourceValue == 'undefined') {
                targetObjectForSelectedSourceValue = picklistmap;
            }

            jQuery.each(picklistmap, function (targetPickListName, targetPickListValues) {
                var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
                if (typeof targetPickListMap == "undefined") {
                    targetPickListMap = targetPickListValues;
                }
                //var targetPickList = jQuery('[name="' + targetPickListName + '"]', container);
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
                    var targetPickListSelectedValue = targetPickListMap[0]; // to automatically select picklist if only one picklistmap is present.
                }
                if ((targetPickListName == 'group_id' || targetPickListName == 'assigned_user_id') && jQuery("[name=" + sourcePicklistname + "]").val() == '') {
                    return false;
                }
                targetPickList.html(targetOptions).val(targetPickListSelectedValue).trigger("change");
            })
        });
        //To Trigger the change on load
        sourcePickListElements.trigger('change');
    },

    /** 
     * Function to register Basic Events
     * @returns {undefined}
     */
    registerBasicEvents: function (container) {
//        app.event.on('post.editView.load', function (event, container) {
//        });
        this._super(container);
        this.registerEventForPicklistDependencySetup(container);
    },
})