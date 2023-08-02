<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_DescribeModule extends CustomerPortal_API_Abstract {

    protected $translate_module = 'CustomerPortal_Client';
    function process(CustomerPortal_API_Request $request) {
        $current_user = $this->getActiveUser();
        $response = new CustomerPortal_API_Response();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $module = $request->get('module');

            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }
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

                        /*Check Provider active or not and unset inactive provider from picklist*/
                        if($value['name'] == "live_metatrader_type" || $value['name'] == "metatrader_type") {
                            $activeProviders = getActiveAllProviderList();
                            $pickListFinal = array();
                            foreach ($pickList as $pickListKey => $pickListValue) {
                                if(in_array($pickListValue['value'], $activeProviders)) {
                                    $pickListFinal[$pickListKey] = $pickListValue;
                                }
                                $pickList = array_values($pickListFinal);
                            }
                        }
                        /*Check Provider active or not and unset inactive provider from picklist*/
                        
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
                if($current_user->column_fields['source'] == 'MOBILE')
                {
                    $describeInfo['fields'] = array_values($fieldList);
                }
                else
                {
                    $describeInfo['fields'] = $fieldList;
                }
            }

            //Describe giving wrong labelfields for HelpDesk and Documents.
            if ($module == 'Documents') {
                $describeInfo['labelFields'] = 'notes_title';
            }
            if ($module == 'HelpDesk') {
                $describeInfo['labelFields'] = 'ticket_title';
            }

            $describeInfo['label'] = decode_html(vtranslate($describeInfo['label'], $module, $portal_language));
            $response->addToResult('describe', $describeInfo);
        }
        return $response;
    }
}
