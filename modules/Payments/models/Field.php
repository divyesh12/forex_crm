<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/* * 
 * Add By:- Divyesh Chothani
 * Date:- 06-01-2020
 * Comment:- Payments Field Model Class
 */

class Payments_Field_Model extends Vtiger_Field_Model {

    /**
     * Function returns special validator for fields
     * @return <Array>
     */
    function getValidator() {
        $validator = array();
        $fieldName = $this->getName();

        switch ($fieldName) {
            case 'amount' :
                $funcName = array('name' => 'paymentAmountValidation');
                array_push($validator, $funcName);
                break;
            default : $validator = parent::getValidator();
                break;
        }
        return $validator;
    }

    /**
     * Function to get all the available picklist values for the current field
     * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise.
     */
    public function getPicklistValues() {
        $fieldDataType = $this->getFieldDataType();
        $fieldName = $this->getName();
        $permission = true;

        // for reference fields the field name will be in the format of (referencefieldname;(module)fieldname)
        preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);
        if (count($matches) > 0) {
            list($full, $referenceParentField, $referenceModule, $referenceFieldName) = $matches;
            $fieldName = $referenceFieldName;
        }

        if ($fieldName == 'hdnTaxType' || ($fieldName == 'region_id' && $this->get('displaytype') == 5))
            return null;

        if ($fieldDataType == 'picklist' || $fieldDataType == 'multipicklist' || $fieldDataType == 'metricpicklist' || $fieldDataType == 'timestring') {
            $fieldPickListValues = array();
            $picklistValues = Vtiger_Util_Helper::getPickListValues($fieldName);

            /* Add By Divyesh Chothani
             * Date:- 08-01-2019
             * Commnet :- Ewallet Module Disable Condition and remove all Wallet regarding Payment Operation type For Listing condition
             */
//            foreach ($picklistValues as $value) {
//                $fieldPickListValues[$value] = vtranslate($value, $this->getModuleName());
//            }
            $disable_ewallet_operation = disableEwalletOperation();
            if ($fieldName == 'payment_type') {
                foreach ($picklistValues as $value) {
                    if (!in_array($value, $disable_ewallet_operation)) {
                        $fieldPickListValues[$value] = vtranslate($value, $this->getModuleName());
                    }
                }
            } else {
                foreach ($picklistValues as $value) {
                    $fieldPickListValues[$value] = vtranslate($value, $this->getModuleName());
                }
            }
            /* end */
            return $fieldPickListValues;
        }
        return null;
    }

    /**
     * Function to get all editable  picklist values for the current user
     * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise.
     */
    public function getEditablePicklistValues() {
        $fieldDataType = $this->getFieldDataType();
        $fieldName = $this->getName();
        $permission = true;

        // for reference fields the field name will be in the format of (referencefieldname;(module)fieldname)
        preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);
        if (count($matches) > 0) {
            list($full, $referenceParentField, $referenceModule, $referenceFieldName) = $matches;
            $fieldName = $referenceFieldName;
        }

        if ($fieldName == 'hdnTaxType' || ($fieldName == 'region_id' && $this->get('displaytype') == 5))
            return null;

        if ($fieldDataType == 'picklist' || $fieldDataType == 'multipicklist') {

            $fieldPickListValues = array();
            if ($this->isRoleBased()) {
                $userModel = Users_Record_Model::getCurrentUserModel();
                $picklistValues = Vtiger_Util_Helper::getRoleBasedPicklistValues($fieldName, $userModel->get('roleid'));
            } else {
                $picklistValues = Vtiger_Util_Helper::getPickListValues($fieldName);
            }

            /* Add By Divyesh Chothani
             * Date:- 08-01-2019
             * Commnet :- Ewallet Module Disable Condition and remove all Wallet regarding Payment Operation type for Edit Form condition
             */
//            foreach ($picklistValues as $value) {
//                $fieldPickListValues[$value] = vtranslate($value, $this->getModuleName());
//            }
            $disable_ewallet_operation = disableEwalletOperation();
            if ($fieldName == 'payment_type') {
                foreach ($picklistValues as $value) {
                    if (!in_array($value, $disable_ewallet_operation)) {
                        $fieldPickListValues[$value] = vtranslate($value, $this->getModuleName());
                    }
                }
            } else {
                foreach ($picklistValues as $value) {
                    $fieldPickListValues[$value] = vtranslate($value, $this->getModuleName());
                }
            }
            /* End */
            return $fieldPickListValues;
        }
        return null;
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
