/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("CurrencyConverter_Edit_Js", {}, {
    
    registerLoadEvent: function (container) {
        
        if(jQuery('input[name="is_currency_value_auto"]').is(':checked')){
            jQuery('#CurrencyConverter_editView_fieldName_conversion_rate').attr('readonly',true);
            jQuery('#CurrencyConverter_editView_fieldName_conversion_rate').css('background', '#AEB6BF');
        }
        jQuery('input[name="is_currency_value_auto"]').click(function() {
            if(this.checked){
                jQuery('#CurrencyConverter_editView_fieldName_conversion_rate').attr('readonly',true);
                jQuery('#CurrencyConverter_editView_fieldName_conversion_rate').css('background', '#AEB6BF');
            }
            if(!this.checked){
                jQuery('#CurrencyConverter_editView_fieldName_conversion_rate').attr('readonly',false);
                jQuery('#CurrencyConverter_editView_fieldName_conversion_rate').css('background', 'none');
            }
        }); 
       // jQuery('input[name="is_currency_value_auto"]').trigger('click');
    },
    /*End*/
    registerBasicEvents: function (container) {
        this._super(container);
        this.registerLoadEvent(container);
    }
})