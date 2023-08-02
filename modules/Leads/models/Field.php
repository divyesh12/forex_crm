<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Added By :- Reena 
 * Date :- 17-12-2019
 * Comment:-Leads Field Model Class 
 * */
class Leads_Field_Model extends Vtiger_Field_Model {

    /**
     * Function returns special validator for fields
     * @return <Array>
     */
    function getValidator() {
        $validator = array();
        $fieldName = $this->getName();

        switch ($fieldName) {
            case 'firstname' :
            case 'lastname' :
            case 'nationality':
            case 'city':
            case 'state':
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
            case 'pobox':
            case 'code':
                $funcName = array('name' => 'zipcodeValidationWithLength10');
                array_push($validator, $funcName);
                break;
            case 'lane':
            case 'address2':
                $funcName = array('name' => 'addressValidationWithLength200');
                array_push($validator, $funcName);
                break;
            case 'date_of_birth':
                $funcName = array('name' => 'birthDateValidation');
                array_push($validator, $funcName);
                break;
            default : $validator = parent::getValidator();
                break;
        }
        return $validator;
    }

}
