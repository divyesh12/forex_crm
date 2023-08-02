/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/


/**
 * Added By :- Reena 
 * Date :- 17-12-2019
 * Comment:-Leads module field validation
 * */
/*FirstName,LastName,City,State Validation */
jQuery.validator.addMethod("onlyAllowCharacterWithLenth25", function (value, element, params) {
    try {
        if (value) {
            var RegExpression = /^[a-zA-Z\s]*$/;
            var stringlength = value.length;
            if (!value.match(RegExpression)) {
                return this.optional(element) || false;
            } else if (stringlength > 25) {
                return this.optional(element) || false;
            }
        }
        return true;
    } catch (err) {
        return false;
    }
}, function (params, element) {
    var value = jQuery(element).val();
    var RegExpression = /^[a-zA-Z\s]*$/;
    var stringlength = value.length;
    if (!value.match(RegExpression)) {
        return app.vtranslate('JS_CHARACTERS_ONLY_VALIDATION');
    }
    if (stringlength > 25) {
        return app.vtranslate('JS_25LENGTH_VALIDATION');
    }
}
);
/*FirstName,LastName,City,State Validation */

/*Mobile Number or Phone  Number Validation */
jQuery.validator.addMethod("mobileNumberValidationWithLength8_14", function (value, element, params) {
    try {
        if (value) {
            var RegExpression = /[0-9]+$/;
            var stringlength = value.length;
            if (!value.match(RegExpression)) {
                return this.optional(element) || false;
            } else if (stringlength < 8 || stringlength > 14) {
                return this.optional(element) || false;
            }
        }
        return true;
    } catch (err) {
        return false;
    }
}, function (params, element) {
    var value = jQuery(element).val();
    var RegExpression = /[0-9]+$/;
    var stringlength = value.length;
    if (!value.match(RegExpression)) {
        return app.vtranslate('JS_INTEGER_ONLY_VALIDATION');
    } else if (stringlength < 8 || stringlength > 14) {
        return app.vtranslate('JS_8_14_LENGTH_VALIDATION');
    }
}
);
/*Mobile Number or Phone  Number Validation */

/*email id Validation */
jQuery.validator.addMethod("emailIdValidationWithLength100", function (value, element, params) {
    try {
        if (value) {
            var stringlength = value.length;
            if (stringlength > 100) {
                return this.optional(element) || false;
            }
        }
        return true;
    } catch (err) {
        return false;
    }
}, function (params, element) {
    var value = jQuery(element).val();
    var stringlength = value.length;
    if (stringlength > 100) {
        return app.vtranslate('JS_EMAIL_LENGTH_VALIDATION');
    }
}
);
/*email Validation*/

/*skypeid validation*/
jQuery.validator.addMethod("skypeidValidationWithLength35", function (value, element, params) {
    try {
        if (value) {
            var stringlength = value.length;
            if (stringlength > 35) {
                return this.optional(element) || false;
            }
        }
        return true;
    } catch (err) {
        return false;
    }
}, function (params, element) {
    var value = jQuery(element).val();
    var stringlength = value.length;
    if (stringlength > 35) {
        return app.vtranslate('JS_35_SKYPEID_LENGTH_VALIDATION');
    }
}
);
/*skypeid validation*/


/*ZipCode or PO Box validation*/
jQuery.validator.addMethod("zipcodeValidationWithLength10", function (value, element, params) {
    try {
        if (value) {
            var stringlength = value.length;
            if (stringlength > 10) {
                return this.optional(element) || false;
            }
        }
        return true;
    } catch (err) {
        return false;
    }
}, function (params, element) {
    var value = jQuery(element).val();
    var stringlength = value.length;
    if (stringlength > 10) {
        return app.vtranslate('JS_ZIP_LENGTH_VALIDATION');
    }
}
);
/*ZipCode or PO Box validation*/

/*Address validation*/
jQuery.validator.addMethod("addressValidationWithLength200", function (value, element, params) {
    try {
        if (value) {
            var stringlength = value.length;
            if (stringlength > 200) {
                return this.optional(element) || false;
            }
        }
        return true;
    } catch (err) {
        return false;
    }
}, function (params, element) {
    var value = jQuery(element).val();
    var stringlength = value.length;
    if (stringlength > 200) {
        return app.vtranslate('JS_ADDRESS_LENGTH_VALIDATION');
    }
}
);
/*Address validation*/

/* BirthDay Validation */
jQuery.validator.addMethod("birthDateValidation", function (value, element, params) {
    try {
        if (value) {
            var oneDay = 24 * 60 * 60 * 1000;
            var fieldDateInstance = app.helper.getDateInstance(value, app.getDateFormat());
            fieldDateInstance.setHours(0, 0, 0, 0);
            var todayDateInstance = new Date();
            todayDateInstance.setHours(0, 0, 0, 0);
            var comparedDateVal = (todayDateInstance - fieldDateInstance) / oneDay;
            if (comparedDateVal <= 6574) {
                return false;
            }
        }
        return true;
    } catch (err) {
        return false;
    }
}, function (params, element) {
    return app.vtranslate('JS_AGE_VALIDATION');
}
);
/* BirthDay Validation */



