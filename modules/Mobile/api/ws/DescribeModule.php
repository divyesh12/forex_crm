<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
include_once 'include/Webservices/DescribeObject.php';
include_once 'modules/CustomerPortal/helpers/Utils.php';

class Mobile_WS_DescribeModule extends Mobile_WS_Controller {
    
    function process(Mobile_API_Request $request) {
       
        $current_user = $this->getActiveUser();
        $response = new Mobile_API_Response();
        $module = $request->get('module');
        $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($module);

        $describeInfo = vtws_describe($module, $current_user);
        // Get active fields with read, write permissions
        $activeFields = CustomerPortal_Utils::getActiveFields($module, true);
        $activeFieldKeys = array_keys($activeFields);

        foreach ($describeInfo['fields'] as $key => $value) {
            if (!in_array($value['name'], $activeFieldKeys)) {
                unset($describeInfo['fields'][$key]);
            } else {
                // Handling UTF-8 charecters in Picklist values
                $value['default'] = decode_html($value['default']);
                if ($value['type']['name'] === 'picklist' || $value['type']['name'] === 'metricpicklist') {
                    $pickList = $value['type']['picklistValues'];

                    foreach ($pickList as $pickListKey => $pickListValue) {
                        $pickListValue['label'] = decode_html(vtranslate($pickListValue['value'], $module, $portal_language));
                        $pickListValue['value'] = decode_html($pickListValue['value']);
                        $pickList[$pickListKey] = $pickListValue;
                    }
                    $value['type']['picklistValues'] = $pickList;
                    $value['type']['dependencies'] = $picklistDependencyDatasource[$value['name']];
                } else if ($value['type']['name'] === 'time') {
                    $value['default'] = Vtiger_Time_UIType::getTimeValueWithSeconds($value['default']);
                }
                //$value['label'] = decode_html(vtranslate($value['label'], $module, $portal_language));                    
                //Changed by Sandeep Thakkar 25-05-2020 for getting field label from vtiger_field table for multi lang
                $label = CustomerPortal_Utils::getFieldLabel($module, $value['name']);                    
                $value['label'] = decode_html(vtranslate($label, $module, $portal_language));
                //End
                
                if ($activeFields[$value['name']]) {
                    $value['editable'] = true;
                } else {
                    $value['editable'] = false;
                }
                $describeInfo['fields'][$key] = $value;

                $position = array_search($value['name'], $activeFieldKeys);
                $fieldList[$position] = $describeInfo['fields'][$key];
            }
        }

        if ($fieldList) {
            unset($describeInfo['fields']);
            $describeInfo['fields'] = $fieldList;
        }

        $response = new Mobile_API_Response();
        $response->setResult(array('describeModule' => $describeInfo));
        return $response;
    }
}