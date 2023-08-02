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

include_once 'include/Webservices/Create.php';

class Mobile_WS_DibortCreateContact extends Mobile_WS_FetchRecordWithGrouping {

    protected $recordValues = false;
    protected $liveAccRecordValues = false;
    protected $allowedContactFields = array('firstname','lastname','birthday','country_name','mailingstreet','otherstreet','mailingzip',
        'mailingstate','mailingcity','mobile','portal','country_code','portal_language','portal_timezone','portal_timeformate','portal_dateformate');
    protected $allowedLiveAccFields = array('live_metatrader_type', 'live_label_account_type', 'live_currency_code', 'leverage');

    // Avoid retrieve and return the value obtained after Create or Update
    protected function processRetrieve(Mobile_API_Request $request) {
        return $this->recordValues;
    }

    function process(Mobile_API_Request $request) {
        include_once 'modules/ThirdParty/language/en_us.lang.php';
        include_once 'modules/ThirdParty/Mobile.Config.php';
        global $current_user; // Required for vtws_update API
        $current_user = $this->getActiveUser();
        $usersWSId = Mobile_WS_Utils::getEntityModuleWSId('Users');
        $response = new Mobile_API_Response();
        $clientAPISource = $Module_Mobile_Configuration['API_SOURCE'];
        
        $module = 'Contacts';
        if (empty($module) || $module !== 'Contacts') {
            $response->setError(1501, "Module not supported!");
            return $response;
        }
        
        $valuesJSONString = $request->get('values');

        $values = array();
        if (!empty($valuesJSONString) && is_string($valuesJSONString)) {
            $values = Zend_Json::decode($valuesJSONString);
        } else {
            $values = $valuesJSONString; // Either empty or already decoded.
        }
        
        if (empty($values)) {
            $response->setError(1501, "Values cannot be empty!");
            return $response;
        }
        /*Custom code*/
        $postData = $validationMessage = array();
        $validationError = false;
        
//        pr($values);
        /*Validations Code Start*/
        if (preg_match('/[^a-zA-Z ]/', $values['firstname']) || preg_match('/[^a-zA-Z ]/', $values['lastname']) || preg_match('/[^a-zA-Z ]/', $values['mailingcity']) || preg_match('/[^a-zA-Z ]/', $values['mailingstate']))
        {
            $validationError = true;
            $validationMessage = array('code' => "LBL_CHARACTERS_ONLY_VALIDATION", "message" => $mod_strings["LBL_CHARACTERS_ONLY_VALIDATION"]);
        }
        elseif (preg_match('/[^0-9]/', $values['mobile']))
        {
            $validationError = true;
            $validationMessage = array('code' => "LBL_INTEGER_ONLY_VALIDATION", "message" => $mod_strings["LBL_INTEGER_ONLY_VALIDATION"]);
        }
        
        if($validationError)
        {
            $response->setError($validationMessage['code'], $validationMessage['message']);
            return $response;
        }
        /*Validations Code End*/
        
        if(isset($values['country_name']) && !empty($values['country_name']))
        {
            $country_code = $this->get_countryCode($values['country_name']);
        }
        $portal = 1;
        $postData = array(
            'portal' => $portal,
            'country_code' => isset($country_code) && !empty($country_code) ? $country_code : '',
            'isconvertedfromlead' => 0, // if is converted from lead  0  then only create contact
            'assigned_user_id' => $usersWSId.'x'.$current_user->id,
            "portal_language" => "en_us",
            "portal_timezone" => "UTC",
            "portal_timeformate" => "24",
            "portal_dateformate" => "yyyy-mm-dd"
        );
        
        $values = array_merge($values, $postData);
        try {
            
            $duplicateContId = iscontactDuplicate($values['email']);
            if($duplicateContId)
            {
                $duplicateContactEntityId = vtws_getWebserviceEntityId('Contacts', $duplicateContId);
                $request->set('record', $duplicateContactEntityId);
                $response = parent::process($request);
                
                $liveAccExist = isAccountExistInSpecialGroup($duplicateContId);
                if(!$liveAccExist)
                {
                    $liveResponse = $this->createLiveAccount($values, $duplicateContactEntityId);
                }
                else
                {
                    $entityData = Vtiger_Record_Model::getInstanceById($liveAccExist, 'LiveAccount');
                    $liveExistData = $entityData->getData();
                    $liveResponse = array('success' => true, 'record' => $liveExistData);
                }
                $modifiedResult = $response->getResult();
                $modifiedResult['live_details'] = ($liveResponse['success'] == true) ? $liveResponse['record'] : $liveResponse['error'];
                $response->setResult($modifiedResult);
                return $response;
            }
            // Retrieve or Initalize
            $this->recordValues = array();
            // Set the modified values
            foreach ($values as $name => $value) {
                if(isset($this->recordValues['id']) && !in_array($name, $this->allowedContactFields)){continue;}
                $this->recordValues[$name] = $value;
            }
            // Create
            // to save Source of Record while Creating
            $this->recordValues['source'] = $clientAPISource;
            $this->recordValues = vtws_create($module, $this->recordValues, $current_user);
            
            // Update the record id
            $request->set('record', $this->recordValues['id']);
            // Gather response with full details
            $response = parent::process($request);
            
            /*Create Liveaccount*/
            if(!empty($this->recordValues['id']) && !isAccountExistInSpecialGroup($this->recordValues['id']))
            {
                $liveResponse = $this->createLiveAccount($values, $this->recordValues['id']);
                $modifiedResult = $response->getResult();
                $modifiedResult['live_details'] = ($liveResponse['success'] == true) ? $liveResponse['record'] : $liveResponse['error'];
                $response->setResult($modifiedResult);
            }
        } catch (DuplicateException $e) {
            $duplicateRecords = $e->getDuplicateRecordIds();
            $duplicateRecordArr = array();
            foreach($duplicateRecords as $k => $duplicateRecord)
            {
                $contactEntityId = vtws_getWebserviceEntityId('Contacts', $duplicateRecord);
                $duplicateRecordArr[] = $contactEntityId;
            }
            $response->setError($duplicateRecordArr, $e->getMessage());
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        return $response;
    }

    function get_countryCode($country_name)
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
    
    function createLiveAccount($values = array(), $contactId = '')
    {
        include 'modules/ThirdParty/language/en_us.lang.php';
        include 'modules/ThirdParty/Mobile.Config.php';
        global $current_user; // Required for vtws_update API
        $isAllowSeries = $isAllowGroupSeries = false;
        $liveAccountMethod = configvar('live_account_no_method');
        if($liveAccountMethod == 'common_series')
        {
            $isAllowSeries = true;
        }
        else if($liveAccountMethod == 'group_series')
        {
            $isAllowGroupSeries = true;
        }
        $current_user = $this->getActiveUser();
        $usersWSId = Mobile_WS_Utils::getEntityModuleWSId('Users');
        $clientAPISource = $Module_Mobile_Configuration['API_SOURCE'];
        $response = new Mobile_API_Response();
        
        $module = 'LiveAccount';
        $accountNo = '';
        $this->liveAccRecordValues = array();
        $metatraderType = $values['live_metatrader_type'];
        $labelAccountType = $values['live_label_account_type'];
        $currency = $values['live_currency_code'];
        $city = $values['mailingcity'];
        $state = $values['mailingstate'];
        $countryname = $values['country_name'];
        $address1 = $city = $values['mailingstreet'];
        $mailingzip = $city = $values['mailingzip'];
        $mobile = $values['mobile'];
        $leverage = $values['leverage'];
        $contactName = $values['firstname'] . ' ' . $values['lastname'];
        $email = $values['email'];
        $comment = 'Create ' . $metatraderType . ' Account';
        $account_mapping_data = getLiveAccountType($metatraderType, $labelAccountType, $currency);
        $accountType = $account_mapping_data['live_account_type'];
        $phonepassword = strtotime("now");
        $password = DemoAccount_Record_Model::RandomString(8);
        $investorPassword = DemoAccount_Record_Model::RandomString(8);

        try {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatraderType);
            
                $error = false;
                if($isAllowSeries || $isAllowGroupSeries)
                {
                    if ($isAllowSeries && !$isAllowGroupSeries && !empty($provider)) {
                        $start_range = (int) $provider->parameters['liveacc_start_range'];
                        $end_range = (int) $provider->parameters['liveacc_end_range'];
                    } elseif (!$isAllowSeries && $isAllowGroupSeries) {
                        $group_series_data = getLiveAccountSeriesBaseOnAccountType($metatrader_type, $account_type, $label_account_type, $currency);
                        $start_range = (int) $group_series_data['start_range'];
                        $end_range = (int) $group_series_data['end_range'];
                    }
                    $max_accountNo = LiveAccount_Record_Model::getMetaTradeUpcommingSeqNo($module, $metatraderType, $accountType, $labelAccountType, $currency);

                    if (!$max_accountNo) {
                        $error = true;
                        $error_label = 'SET_SERIES_TYPE_ERROR';
                    } else if ($isAllowSeries && ((!$start_range && !$end_range) || (!$end_range) || (!$start_range))) {
                        $error = true;
                        $error_label = 'COMMON_SERIES_ERROR';
                    } else if ($isAllowGroupSeries && ((!$start_range && !$end_range) || (!$end_range) || (!$start_range))) {
                        $error = true;
                        $error_label = 'GROUP_SERIES_ERROR';
                    } else if ($max_accountNo > $end_range && isset($end_range)) {
                        $error = true;
                        $error_label = 'ACCOUNT_LIMIT_ERROR';
                    } else if (isset($end_range) && !in_array($max_accountNo, range($start_range, $end_range))) {
                        $error = true;
                        $error_label = 'ACCOUNT_RANGE_LIMIT_ERROR';
                    }
                    $accountNo = $max_accountNo;
                }
                
                if (!$error && empty($account_mapping_data))
                {
                    $error = true;
                    $error_label = 'ACCOUNT_MAPPING_ISSUE';
                }
                else if(!$error)
                {
                    $create_user_result = $provider->createAccount($city, $state, $countryname, $address1, $mailingzip, $mobile, $comment, $accountNo, $password, $investorPassword, $phonepassword, str_replace(":", "\\", $accountType), $leverage, $contactName, $email, $labelAccountType, $currency);

                    $create_user_code = $create_user_result->Code;
                    $create_user_message = $create_user_result->Message;
                    if ($create_user_message == 'Ok' && $create_user_code == 200)
                    {
                        $accountNumber = $create_user_result->Data->login;
                        // Set the modified values
                        foreach ($values as $name => $value) {
                            if(!in_array($name, $this->allowedLiveAccFields)){continue;}
                            $this->liveAccRecordValues[$name] = $value;
                        }
                        $this->liveAccRecordValues['record_status'] = 'Approved';
                        $this->liveAccRecordValues['account_no'] = $accountNumber;
                        $this->liveAccRecordValues['source'] = $clientAPISource;
                        $this->liveAccRecordValues['assigned_user_id'] = $usersWSId.'x'.$current_user->id;
                        $this->liveAccRecordValues['contactid'] = $contactId;
                        $this->liveAccRecordValues = vtws_create($module, $this->liveAccRecordValues, $current_user);
                        if(isset($this->liveAccRecordValues['id']) && !empty($this->liveAccRecordValues['id']))
                        {
                            $response = array('success' => true,'record' => $this->liveAccRecordValues);
                        }
                        else
                        {
                            $response = array('success' => false, 'error' => array('code' => "LBL_ACCOUNT_CREATION_ERROR", "message" => $app_strings["LBL_ACCOUNT_CREATION_ERROR"]));
                        }
                    }
                    elseif ($create_user_code == 201)
                    {
                        $response = array('success' => false, 'error' => array('code' => "LBL_ACCOUNT_CREATION_LIMIT", "message" => $app_strings["LBL_ACCOUNT_CREATION_LIMIT"]));
                    }
                    else
                    {
                        $response = array('success' => false, 'error' => array('code' => $create_user_message, "message" => $create_user_message));
                    }
                }
                else
                {
                    $errorMsg = vtranslate($error_label, $module);
                    $response = array('success' => false, 'error' => array('code' => $error_label, "message" => $errorMsg));
                }
        } catch (Exception $e) {
            $response = array('success' => false, 'error' => array('code' => $e->getCode(), "message" => $e->getMessage()));
        }
        return $response;
    }
    
}
