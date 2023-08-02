/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/* Percentage validation for text field input */
jQuery.validator.addMethod("percentageValidation", function (value, element, params) {console.log('percentageValidation');
        try {
            if (value) {console.log(value);
                var form = jQuery(element).closest('form');
                var RegExpression = /^100$|^\d{0,2}(\.\d{1,4})? *%?$/;                
                var ibCommissionValueElement = form.find('[name="ib_commission_value"]');
                var ib_commission_value = ibCommissionValueElement.val();
                var ibCommissionTypeElement = form.find('[name="ib_commission_type"]');
                var ib_commission_type = ibCommissionTypeElement.val();
                if (ib_commission_type == 'Percentage') {
                    if (ib_commission_value.match(RegExpression)) {
                        return true;
                    }                    
                }
                else {
                    return true;
                }
            }            
        } catch (err) {
            return false;
        }
    }, jQuery.validator.format(app.vtranslate('JS_IBCOMM_PERCENTAGE_VALIDATION'))
);
/* Percentage validation for text field input */