<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
include_once 'include/Webservices/Retrieve.php';
include_once dirname(__FILE__) . '/FetchRecord.php';
include_once 'include/Webservices/DescribeObject.php';
include 'modules/Mobile/api/ws/ListModuleRecords.php';

class Mobile_WS_FetchRecordWithGrouping extends Mobile_WS_FetchRecord {

    private $_cachedDescribeInfo = false;
    private $_cachedDescribeFieldInfo = false;

    protected function cacheDescribeInfo($describeInfo) {
        $this->_cachedDescribeInfo = $describeInfo;
        $this->_cachedDescribeFieldInfo = array();
        if (!empty($describeInfo['fields'])) {
            foreach ($describeInfo['fields'] as $describeFieldInfo) {
                $this->_cachedDescribeFieldInfo[$describeFieldInfo['name']] = $describeFieldInfo;
            }
        }
    }

    protected function cachedDescribeInfo() {
        return $this->_cachedDescribeInfo;
    }

    protected function cachedDescribeFieldInfo($fieldname) {
        if ($this->_cachedDescribeFieldInfo !== false) {
            if (isset($this->_cachedDescribeFieldInfo[$fieldname])) {
                return $this->_cachedDescribeFieldInfo[$fieldname];
            }
        }
        return false;
    }

    protected function cachedEntityFieldnames($module) {
        $describeInfo = $this->cachedDescribeInfo();
        $labelFields = $describeInfo['labelFields'];
        switch ($module) {
            case 'HelpDesk': $labelFields = 'ticket_title';
                break;
            case 'Documents': $labelFields = 'notes_title';
                break;
        }
        return explode(',', $labelFields);
    }

    protected function isTemplateRecordRequest(Mobile_API_Request $request) {
        $recordid = $request->get('record');
        return (preg_match("/([0-9]+)x0/", $recordid));
    }

    protected function processRetrieve(Mobile_API_Request $request) {
        $recordid = $request->get('record');

        // Create a template record for use 
        if ($this->isTemplateRecordRequest($request)) {
            $current_user = $this->getActiveUser();

            $module = $this->detectModuleName($recordid);
            $describeInfo = vtws_describe($module, $current_user);
            Mobile_WS_Utils::fixDescribeFieldInfo($module, $describeInfo);

            $this->cacheDescribeInfo($describeInfo);

            $templateRecord = array();
            foreach ($describeInfo['fields'] as $describeField) {
                $templateFieldValue = '';
                if (isset($describeField['type']) && isset($describeField['type']['defaultValue'])) {
                    $templateFieldValue = $describeField['type']['defaultValue'];
                } else if (isset($describeField['default'])) {
                    $templateFieldValue = $describeField['default'];
                }
                $templateRecord[$describeField['name']] = $templateFieldValue;
            }
            if (isset($templateRecord['assigned_user_id'])) {
                $templateRecord['assigned_user_id'] = sprintf("%sx%s", Mobile_WS_Utils::getEntityModuleWSId('Users'), $current_user->id);
            }
            // Reset the record id
            $templateRecord['id'] = $recordid;

            return $templateRecord;
        }

        // Or else delgate the action to parent
        return parent::processRetrieve($request);
    }

    function process(Mobile_API_Request $request) {
        $response = parent::process($request);
        return $this->processWithGrouping($request, $response);
    }

    protected function processWithGrouping(Mobile_API_Request $request, $response) {
        $isTemplateRecord = $this->isTemplateRecordRequest($request);
        $result = $response->getResult();

        $resultRecord = $result['record'];
        $module = $this->detectModuleName($resultRecord['id']);

        $modifiedRecord = $this->transformRecordWithGrouping($resultRecord, $module, $isTemplateRecord);
        $response->setResult(array('record' => $modifiedRecord));

        return $response;
    }

    protected function transformRecordWithGrouping($resultRecord, $module, $isTemplateRecord = false) {
        $current_user = $this->getActiveUser();

        $recordId = end(explode('x', $resultRecord['id']));
        if ($module == 'Documents') {
            $documentAttachmentDetails = Mobile_WS_ListModuleRecords::getDocumentAttachmentDetails($recordId);
        }

        $moduleFieldGroups = Mobile_WS_Utils::gatherModuleFieldGroupInfo($module);

        $modifiedResult = array();

        $blocks = array();
        $labelFields = false;
        foreach ($moduleFieldGroups as $blocklabel => $fieldgroups) {
            $fields = array();

            foreach ($fieldgroups as $fieldname => $fieldinfo) {
    
                // Pickup field if its part of the result
                
                if (isset($resultRecord[$fieldname]) && !($fieldname === 'custom_data' && in_array($module,["Payments"]))) {

                    $value = $resultRecord[$fieldname];
                    if ($fieldinfo['uitype'] == 56) {
                        $value = ($resultRecord[$fieldname]) ? 'Yes' : 'No';
                    }
                    $field = array(
                        'name' => $fieldname,
                        'value' => $value, //$resultRecord[$fieldname],
                        'label' => $fieldinfo['label'],
                        'uitype' => $fieldinfo['uitype']
                    );
                    if ($module == 'Documents' && $fieldname == 'filename') {
                        $field['document_url'] = $documentAttachmentDetails['document_url'];
                    }else if($module == 'Documents' && $fieldname == 'filelocationtype'){
                        $filelocationtype = ($resultRecord[$fieldname] == 'I') ? 'Internal' : (
                                 ($resultRecord[$fieldname] == 'E') ? 'External' : '');
                        $field['value'] = $filelocationtype;
                    }

                    // Template record requested send more details if available
                    if ($isTemplateRecord) {
                        $describeFieldInfo = $this->cachedDescribeFieldInfo($fieldname);
                        if ($describeFieldInfo) {
                            foreach ($describeFieldInfo as $k => $v) {
                                if (isset($field[$k]))
                                    continue;
                                $field[$k] = $v;
                            }
                        }
                        // Entity fieldnames
                        $labelFields = $this->cachedEntityFieldnames($module);
                    }
                    // Fix the assigned to uitype
                    if ($field['uitype'] == '53') {
                        $field['type']['defaultValue'] = array('value' => "19x{$current_user->id}", 'label' => $current_user->column_fields['last_name']);
                    } else if ($field['uitype'] == '117') {
                        $field['type']['defaultValue'] = $field['value'];
                    }
                    // Special case handling to pull configured Terms & Conditions given through webservices.
                    else if ($field['name'] == 'terms_conditions' && in_array($module, array('Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder'))) {
                        $field['type']['defaultValue'] = $field['value'];
                    }
                    //Special case handling to set defaultValue for visibility field in calendar.
                    else if ($field['name'] == 'visibility' && in_array($module, array('Calendar', 'Events'))) {
                        $field['type']['defaultValue'] = $field['value'];
                    } else if ($field['type']['name'] != 'reference') {
                        $field['type']['defaultValue'] = $field['default'];
                    }                    
                    $fields[] = $field;
                } 
                
                if ($fieldname == 'custom_data' && $module == 'Payments') {
                    $custom_data = $resultRecord['custom_data'];
                    $customDataArray = Zend_Json::decode(decode_html($custom_data));
                    $payment_details = array();
                    if (!empty($customDataArray)) {
                        foreach ($customDataArray as $key => $value) {
                            $custom_field = array(
                                'name' => $value['name'],
                                'value' => (string) $value['value'],
                                'label' => vtranslate($value['label'], $module),
                                'uitype' => 1,
                                'type' => array("defaultValue" => null)
                            );

                            if ($value['type'] == 'file') {
                                $filename = '';
                                $document_url = '';
                                $documentid = end(explode("x", $value['value']));
                                if ($documentid) {
                                    $documentAttachmentDetails = Mobile_WS_ListModuleRecords::getDocumentAttachmentDetails($documentid);
                                    $filename = $documentAttachmentDetails['filename'];
                                    $document_url = $documentAttachmentDetails['document_url'];
                                }
                                $custom_field['value'] = $filename;
                                $custom_field['document_url'] = $document_url;
                            }
                            $fields[] = $custom_field;
                        }
                    }
                }

            }
            $blocks[] = array('label' => $blocklabel, 'fields' => $fields);   
        }

        $sections = array();
        $moduleFieldGroupKeys = array_keys($moduleFieldGroups);
        foreach ($moduleFieldGroupKeys as $blocklabel) {
            // Eliminate empty blocks
            if (isset($groups[$blocklabel]) && !empty($groups[$blocklabel])) {
                $sections[] = array('label' => $blocklabel, 'count' => count($groups[$blocklabel]));
            }
        }

        $modifiedResult = array('blocks' => $blocks, 'id' => $resultRecord['id']);

        $entityId = end(explode('x',$resultRecord['id']));
        $recordStausFieldArray = array('Contacts'=>'is_document_verified','Documents'=>'record_status','LiveAccount'=>'record_status','LeverageHistory'=>'record_status','Payments'=>'payment_status','HelpDesk'=>'ticketstatus','Campaigns'=>'campaignstatus');
        $modifiedResult['action_status'] = '';
        foreach($recordStausFieldArray AS $fieldModuleName=>$listDefaultFieldName){
            if($module == $fieldModuleName){
                $recordModel = Vtiger_Record_Model::getInstanceById($entityId, $module);
                $status = $recordModel->get($listDefaultFieldName);
                //$modifiedResult[$listDefaultFieldName] = $status;
                $modifiedResult['action_status'] = $status;
            }
        }
        
        if ($labelFields)
            $modifiedResult['labelFields'] = $labelFields;

        if (isset($resultRecord['LineItems'])) {
            $modifiedResult['LineItems'] = $resultRecord['LineItems'];
        }

        return $modifiedResult;
    }

}
