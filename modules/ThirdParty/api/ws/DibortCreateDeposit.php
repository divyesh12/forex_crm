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


class Mobile_WS_DibortCreateDeposit extends Mobile_WS_FetchRecordWithGrouping {

    protected $recordValues = false;
    protected $allowedUpdateFields = array('payment_from', 'payment_currency', 'payment_to', 'amount', 'contactid');
    protected $allowedPaymentGateways = array('IPAY');

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
        
        $module = 'Payments';
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
        $validationMessage = array();
        $validationError = false;
        $deposit_supported = '';
        if(isset($values['payment_from']) && !empty($values['payment_from']))
        {
            if(in_array($values['payment_from'], $this->allowedPaymentGateways))
            {
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($values['payment_from']);
                if($provider)
                {
                    $deposit_supported = $provider->parameters['deposit_supported'];
                    $providerParameters = $provider->parameters;
                }
                else
                {
                    $validationMessage = array('code' => "MSG_PROVIDER_INACTIVE", "message" => $mod_strings["MSG_PROVIDER_INACTIVE"]);
                    $response->setError($validationMessage['code'], $validationMessage['message']);
                    return $response;
                }
            }
        }
        else
        {
            $validationMessage = array('code' => "MSG_PARAMS_MISSING", "message" => $mod_strings["MSG_PARAMS_MISSING"]);
            $response->setError($validationMessage['code'], $validationMessage['message']);
            return $response;
        }
        // $contactId = explode('x', $contactId);
        // $contactId = $contactId[1];
        /*Validations Code Start*/
        if (strtolower($deposit_supported) != 'yes')
        {
            $validationError = true;
            $validationMessage = array('code' => "MSG_DEPOSIT_DOES_NOT_SUPPORTS", "message" => $mod_strings["MSG_DEPOSIT_DOES_NOT_SUPPORTS"]);
        }
        else if(!empty($values['payment_to']) && !isAccountWithinSpecialGroup($values['payment_to']))
        {
            $validationError = true;
            $validationMessage = array('code' => "LBL_ACCOUNT_VALIDATION", "message" => $mod_strings["LBL_ACCOUNT_VALIDATION"]);
        }
        
        if($validationError)
        {
            $response->setError($validationMessage['code'], $validationMessage['message']);
            return $response;
        }
        /*Validations Code End*/
        
        /*Commission calculation*/
        $amount = $values['amount'];
        $commissionAmount = 0;
        $netAmount = $amount;
        $values['commission'] = 0;
        if ($providerParameters['commission'] != 0 && $providerParameters['commission'] > 0) {
            $commissionAmount = ($amount * $providerParameters['commission']) / 100;
            $netAmount = $amount + $commissionAmount;
        }
        /*Commission calculation*/
        
        try {
            //Initalize
            $this->recordValues = array();
            // Set the modified values
            foreach ($values as $name => $value) {
                if(isset($this->recordValues['id']) && !in_array($name, $this->allowedUpdateFields)){continue;}
                $this->recordValues[$name] = $value;
            }
            // to save Source of Record while Creating
            $this->recordValues['payment_operation'] = 'Deposit';
            $this->recordValues['payment_type'] = 'P2A';
            $this->recordValues['payment_status'] = 'Pending';
            $this->recordValues['payment_process'] = 'PSP';
            $this->recordValues['commission'] = $providerParameters['commission'];
            $this->recordValues['commission_amount'] = $commissionAmount;
            $this->recordValues['net_amount'] = $netAmount;
            $this->recordValues['assigned_user_id'] = $usersWSId.'x'.$current_user->id;
            $this->recordValues['source'] = $clientAPISource;
            $this->recordValues = vtws_create($module, $this->recordValues, $current_user);
            // Update the record id
            $request->set('record', $this->recordValues['id']);
            // Gather response with full details
            $response = parent::process($request);
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
}
