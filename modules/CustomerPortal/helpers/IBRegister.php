<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_IBRegister {

    protected $translate_module = 'CustomerPortal_Client';
    
    function __construct() {
            $this->pagelimit = 10;
    }
        
    static function clientCreateProcess($request, $current_user, $activecustomer)
    {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $module = 'Contacts';
        $recordId = $request->get('recordId');
        $valuesJSONString = $request->get('values', '', false);
        $values = "";

        if (!empty($valuesJSONString) && is_string($valuesJSONString)) {
            $values = Zend_Json::decode($valuesJSONString);
        } else {
            $values = $valuesJSONString; // Either empty or already decoded.
        }
        
        $errorFlag = false;
        $emailPattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        if (preg_match('/[^a-zA-Z ]/', $values['firstname']) || preg_match('/[^a-zA-Z ]/', $values['lastname']) || preg_match('/[^a-zA-Z ]/', $values['country_name']))
        {
            $response->setError("CAB_MSG_CHARACTERS_ONLY_VALIDATION", vtranslate('CAB_MSG_CHARACTERS_ONLY_VALIDATION', 'CustomerPortal_Client', $activecustomer->portal_language));
            $errorFlag = true;
        }
        else if (preg_match('/[^0-9]/', $values['mobile']))
        {
            $response->setError("CAB_MSG_INTEGER_ONLY_VALIDATION", vtranslate('CAB_MSG_INTEGER_ONLY_VALIDATION', 'CustomerPortal_Client', $activecustomer->portal_language));
            $errorFlag = true;
        }
        else if (!preg_match($emailPattern, $values['email']))
        {
            $response->setError("CAB_MSG_EMAIL_VALIDATION", vtranslate('CAB_MSG_EMAIL_VALIDATION', 'CustomerPortal_Client', $activecustomer->portal_language));
            $errorFlag = true;
        }
        
        if($errorFlag)
        {
            return $response;
        }
        
        $currentCustomerId = $activecustomer->id;
        $countryCode = CustomerPortal_IBRegister::getCountryCode($values['country_name']);
        $values['parent_affiliate_code'] = getAffiliateCodeFromId($currentCustomerId);
        $values['portal'] = 1;
        $values['leadsource'] = 'IB Registration Form';
        $values['country_code'] = $countryCode;
        $values['withdraw_allow'] = 1;
        // Retrieve or Initalize
        $recordValues = array();
        // set assigned user to default assignee
        $recordValues['assigned_user_id'] = '19x' . getRecordOwnerId($currentCustomerId)['Users']; //contact's assignee will assign to sub module record

        // Set the modified values
        if (!empty($values)) {
            foreach ($values as $name => $value) {
                $recordValues[$name] = $value;
            }
        }

        // Setting missing mandatory fields for record.
        $describe = vtws_describe($module, $current_user);
        $mandatoryFields = CustomerPortal_Utils:: getMandatoryFields($describe);
        foreach ($mandatoryFields as $fieldName => $type) {
            if (!isset($recordValues[$fieldName])) {
                if ($type['name'] == 'reference') {
                    $crmId = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $module);
                    $wsId = vtws_getWebserviceEntityId($type['refersTo'][0], $crmId);
                    $recordValues[$fieldName] = $wsId;
                } else {
                    $recordValues[$fieldName] = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $module);
                }
            }
        }
        $recordValues['source'] = $current_user->column_fields['source']; //'CUSTOMER PORTAL';
        $recordValues = vtws_create($module, $recordValues, $current_user);

        $fields = implode(',', CustomerPortal_Utils::getActiveFields($module));
        $sql = sprintf('SELECT %s FROM %s WHERE id=\'%s\';', $fields, $module, $recordValues['id']);
        $result = vtws_query($sql, $current_user);
        $response->setResult(array('record' => $result[0]));
        return $response;
    }

    static function clientListProcess($request, $current_user, $activecustomer)
    {
        global $adb;
        $ibObj = new CustomerPortal_IBRegister;
        $pageLimit = $ibObj->pagelimit;
        $recordId = $request->get('recordId');
        $currentCustomerId = $activecustomer->id;
        $orderBy = $request->get('orderBy');
        $order = $request->get('order');
        $page = $request->get('page');
        $documentVerify = $request->get('document_verify');
        $filter = htmlspecialchars_decode($request->get('filter'));
        
        $isDocumentVerify = 0;
        if($documentVerify === 'true')
        {
            $isDocumentVerify = 1;
        }
        
        $contactId = vtws_getWebserviceEntityId('Contacts', $currentCustomerId);
        $contact = vtws_retrieve($contactId, $current_user);
        $contact = CustomerPortal_Utils::resolveRecordValues($contact);
        $ib_hierarchy = $contact['ib_hierarchy'];
        $affiliate_code = $contact['affiliate_code'];

        $params = array();
        if (empty($orderBy)) {
            $orderBy = ' ORDER BY level';
            $order = 'ASC';
        }
        $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), $pageLimit);
        $ib_hierarchy = $contact['ib_hierarchy'];
        if (empty($filter)) {
            $filter = " c.ib_hierarchy LIKE '$ib_hierarchy" . "%'";
        }
        $where = " AND t.is_document_verified = ? ";
        array_push($params, $isDocumentVerify);
        
        $sql = "SELECT * FROM (SELECT c.contactid, c.email, c.firstname, c.lastname, c.affiliate_code, c.parent_affiliate_code, c.ib_hierarchy, c.record_status, findIBLevel(REPLACE(c.ib_hierarchy, '" . $ib_hierarchy . "', '')) as level, c.is_document_verified FROM `vtiger_contactdetails` as c INNER JOIN `vtiger_crmentity` as ce ON c.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0 AND " . $filter . ") AS t WHERE t.level = '1'" . $where . $limitClause;
        $sqlResult = $adb->pquery($sql, $params);
        $numRow = $adb->num_rows($sqlResult);
        $rows = array();
        for ($i = 0; $i < $numRow; $i++) {
            $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
            if ($adb->query_result($sqlResult, $i, 'record_status') == 'Approved')
                $rows[$i]['affiliate_code'] = $adb->query_result($sqlResult, $i, 'affiliate_code');
            else
                $rows[$i]['affiliate_code'] = '';
            $contactid = $adb->query_result($sqlResult, $i, 'contactid');
            $rows[$i]['contactid'] = vtws_getWebserviceEntityId('Contacts', $contactid);
            $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');
        }
        $response = new CustomerPortal_API_Response();
        $response->addToResult('records', $rows);
        return $response;
    }
    
    static function documentUploadProcess($request)
    {
        $values = $request->get('values');
        $file = $request->get('file');
        $data = array(
            '_operation' => 'SaveRecord',
            'module' => 'Documents',
            'values' => $values,
            'doc_upload_by_ib' => true,
            'file' => $file,
        );
        $result = CustomerPortal_IBRegister::apiProcess($data);
        return $result;
    }
    
    static function documentListProcess($request)
    {
        $ibContactId = $request->get('ib_contact_id');
        $data = array(
            '_operation' => 'FetchRecords',
            'module' => 'Documents',
            'moduleLabel' => 'Documents',
            'filter' => "document_type='KYC'",
            'response_type' => "List",
            'ib_contact_id' => $ibContactId,
        );
        $result = CustomerPortal_IBRegister::apiProcess($data);
        return $result;
    }
    
    static function apiProcess($data)
    {
        $clientRequestValues = $data;
        if (get_magic_quotes_gpc()) {
            $clientRequestValues = $this->stripslashes_recursive($clientRequestValues);
        }
        $clientRequestValuesRaw = array();
        return CustomerPortal_API_EntryPoint::process(new CustomerPortal_API_Request($clientRequestValues, $clientRequestValuesRaw));
    }
    
    static function getCountryCode($country_name)
    {
        $db = PearDatabase::getInstance();
        $query = "SELECT targetvalues FROM vtiger_picklist_dependency WHERE tabid = 7 and sourcevalue =  '" . $country_name . "'";
        $result = $db->pquery($query);
        $num_rows = $db->num_rows($result);
        if ($db->num_rows($result)) {
            $county_code = (string) trim(str_replace('&quot;', '', $db->query_result($result, 0, 'targetvalues')), '[]');
            return $county_code;
        }
        return false;
    }
    
    static function checkIbApproved($contactId = '')
    {
        if(!empty($contactId))
        {
            $db = PearDatabase::getInstance();
            $query = "SELECT record_status FROM vtiger_contactdetails WHERE contactid = ?";
            $result = $db->pquery($query, array($contactId));
            $num_rows = $db->num_rows($result);
            if ($num_rows > 0)
            {
                $ibStatus = $db->query_result($result, 0, 'record_status');
                if($ibStatus === 'Approved')
                {
                    return true;
                }
            }
        }
        return false;
    }
}
