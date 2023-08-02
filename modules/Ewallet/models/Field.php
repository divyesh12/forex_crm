<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Ewallet_Field_Model extends Vtiger_Field_Model {

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
		if(count($matches) > 0) {
			list($full, $referenceParentField, $referenceModule, $referenceFieldName) = $matches;
			$fieldName = $referenceFieldName;
		}

		if($fieldName == 'hdnTaxType' || ($fieldName == 'region_id' && $this->get('displaytype') == 5)) return null;

        if($fieldDataType == 'picklist' || $fieldDataType == 'multipicklist' || $fieldDataType == 'metricpicklist' || $fieldDataType == 'timestring') {
            $fieldPickListValues = array();
            $picklistValues = Vtiger_Util_Helper::getPickListValues($fieldName);
            
            foreach ($picklistValues as $value) {
//                if (!is_numeric($value)) {
                    $addValue = vtranslate($value, $this->getModuleName());
//                } else {
//                    $addValue = $value;
//			}
                $fieldPickListValues[$value] = $addValue;
            }
            
            return $fieldPickListValues;
		}
		return null;
    }
}
