<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/* * Add By Reena
 * 17-12-2019
 * Contacts Field Model Class
 */

class Contacts_Field_Model extends Vtiger_Field_Model {

    /**
     * Function returns special validator for fields
     * @return <Array>
     */
    function getValidator() {
        $view = $_REQUEST['view'];
        if ($view == 'ConvertLead') {
            return array();
        }
        $validator = array();
        $fieldName = $this->getName();

        switch ($fieldName) {
            case 'firstname' :
            case 'lastname' :
            case 'nationality':
            case 'mailingcity':
            case 'mailingstate':
                $funcName = array('name' => 'onlyAllowCharacterWithLenth25');
                array_push($validator, $funcName);
                break;
            case 'mobile':
                $funcName = array('name' => 'mobileNumberValidationWithLength8_14');
                array_push($validator, $funcName);
                break;
//                  case 'email':
//                  case 'secondaryemail':
//                        $funcName = array('name' => 'emailIdValidationWithLength100');
//                        array_push($validator, $funcName);
//                        break;
//                  case 'skypeid':
//                        $funcName = array('name' => 'skypeidValidationWithLength35');
//                        array_push($validator, $funcName);
//                        break;
            case 'mailingzip':
            case 'mailingpobox':
                $funcName = array('name' => 'zipcodeValidationWithLength10');
                array_push($validator, $funcName);
                break;
            case 'mailingstreet':
            case 'otherstreet':
                $funcName = array('name' => 'addressValidationWithLength200');
                array_push($validator, $funcName);
                break;
            case 'birthday':
                $funcName = array('name' => 'birthDateValidation');
                array_push($validator, $funcName);
                break;
            default : $validator = parent::getValidator();
                break;
        }
        return $validator;
    }

    /**
     * Customize the display value for detail view.
     */
//    public function getDisplayValue($value, $record = false, $recordInstance = false) {
//        global $form_security_key, $encrypt_method;
//        if ($recordInstance) {
//            //add field name for decrypted value
//            if ($this->getName() == 'portal_password') {
//                $decryptedString = string_Encrypt_Decrypt($value, 'D', $form_security_key, $encrypt_method);
//                return $decryptedString;
//            }
//        }
//        return parent::getDisplayValue($value, $record, $recordInstance);
//    }
}
