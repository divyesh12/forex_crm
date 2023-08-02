<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

include_once 'include/Webservices/ConvertLead.php';

class Mobile_WS_ConvertLead extends Mobile_WS_FetchRecordWithGrouping {

    protected $recordValues = false;

    // Avoid retrieve and return the value obtained after Create or Update
    protected function processRetrieve(Mobile_API_Request $request) {
        return $this->recordValues;
    }

    function process(Mobile_API_Request $request) {
        global $current_user; // Required for vtws_update API
        $current_user = $this->getActiveUser();

        $module = 'Leads';
        $record = $request->get('record');
        $assignId = '1';
        $transferModule = 'Contacts';
        $valuesJSONString = $request->get('values');
        list($tabId, $recordId) = vtws_getIdComponents($record);

        $values = "";
        if (!empty($valuesJSONString) && is_string($valuesJSONString)) {
            $values = Zend_Json::decode($valuesJSONString);
        } else {
            $values = $valuesJSONString; // Either empty or already decoded.
        }
        $response = new Mobile_API_Response();
        if (empty($values)) {
            $response->setError(1501, "Values cannot be empty!");
            return $response;
        }

        try {
            if (vtws_recordExists($record)) {
                $entityValues = array();
                $entityValues['transferRelatedRecordsTo'] = $transferModule;
                $entityValues['assignedTo'] = vtws_getWebserviceEntityId(vtws_getOwnerType($assignId), $assignId);
                $entityValues['leadId'] = vtws_getWebserviceEntityId($module, $recordId);
//        $entityValues['imageAttachmentId'] = $values['imageAttachmentId'];

                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
                $convertLeadFields = $recordModel->getConvertLeadFields();

                if (vtlib_isModuleActive($module) && vtlib_isModuleActive($transferModule)) {
                    $entityValues['entities'][$transferModule]['create'] = true;
                    $entityValues['entities'][$transferModule]['name'] = $transferModule;

                    // Converting lead should save records source as CRM instead of WEBSERVICE
                    $entityValues['entities'][$transferModule]['source'] = 'Mobile';
                    foreach ($convertLeadFields[$transferModule] as $fieldModel) {
                        $fieldName = $fieldModel->getName();
                        $fieldValue = $values[$fieldName];

                        //Potential Amount Field value converting into DB format
                        if ($fieldModel->getFieldDataType() === 'currency') {
                            if ($fieldModel->get('uitype') == 72) {
                                // Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
                                $fieldValue = Vtiger_Currency_UIType::convertToDBFormat($fieldValue, null, true);
                            } else {
                                $fieldValue = Vtiger_Currency_UIType::convertToDBFormat($fieldValue);
                            }
                        } elseif ($fieldModel->getFieldDataType() === 'date') {
                            $fieldValue = DateTimeField::convertToDBFormat($fieldValue);
                        } elseif ($fieldModel->getFieldDataType() === 'reference' && $fieldValue) {
                            if ($fieldModel->get('uitype') == 77) {
                                $fieldValue = vtws_getWebserviceEntityId(vtws_getOwnerType($fieldValue), $fieldValue);
                            } else {
                                $ids = vtws_getIdComponents($fieldValue);
                                if (count($ids) === 1) {
                                    $fieldValue = vtws_getWebserviceEntityId(getSalesEntityType($fieldValue), $fieldValue);
                                }
                            }
                        }
                        $entityValues['entities'][$transferModule][$fieldName] = $fieldValue;
                    }

                    $result = vtws_convertlead($entityValues, $current_user);
                    $response->setResult($result);
                    return $response;
                } else {
                    $response->setError("MODULE_NOT_FOUND", "Module is inactive!");
                    return $response;
                }
            } else {
                $response->setError("RECORD_NOT_FOUND", "Record does not exist");
                return $response;
            }
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        return $response;
    }

}
