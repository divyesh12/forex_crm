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
 * Add By :-Reena Hingol
 * Date :-05-02-2020
 * added file IBCommissionProfileItems Field Model Class
 * */

class IBCommissionProfileItems_Field_Model extends Vtiger_Field_Model {

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
            /* Add By :-Reena Hingol
             * Date:- 05-02-2020
             * Comment :- IB commission profile level retrieval from settings
             */
            if ($fieldName == 'ibcommission_level') {
                $max_ib_level = configvar('max_ib_level');
                for ($i = 0; $i <= $max_ib_level; $i++) {
                    $fieldPickListValues[$i] = $i;
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
            /*             *
             * Add By :-Reena Hingol
             * Date:- 05-02-2020
             * Comment :- IB commission profile level retrieval from settings
             * */
            if ($fieldName == 'ibcommission_level') {
                $max_ib_level = configvar('max_ib_level');
                for ($i = 0; $i <= $max_ib_level; $i++) {
                    $fieldPickListValues[$i] = $i;
                }
            } else {
                foreach ($picklistValues as $value) {
                    $fieldPickListValues[$value] = vtranslate($value, $this->getModuleName());
                }
            }
            return $fieldPickListValues;
        }
        return null;
    }

    /**
     * Function returns special validator for fields
     * @return <Array>
     */
    function getValidator() {
        $validator = array();
        $fieldName = $this->getName();

        switch ($fieldName) {
            case 'ib_commission_value':
                $funcName = array('name' => 'percentageValidation');
                array_push($validator, $funcName);
                break;
            default : $validator = parent::getValidator();
                break;
        }
        return $validator;
    }

}
