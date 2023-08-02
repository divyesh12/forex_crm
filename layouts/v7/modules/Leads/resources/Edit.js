/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Edit_Js("Leads_Edit_Js", {}, {
    
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
            'module': 'Leads',
            'action': 'EditAjax',
            'mode': 'checkParentAffiliateCode',
            'parent_affiliate_code': details.parent_affiliate_code,
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
            }
        );
        return aDeferred.promise();
    },

    registerBasicEvents: function (container) {
        this._super(container);
        this.registerValidation();
    }
})