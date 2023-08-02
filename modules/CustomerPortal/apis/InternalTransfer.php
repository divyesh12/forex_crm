<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_InternalTransfer extends CustomerPortal_API_Abstract {

    protected $recordValues = false;
    protected $mode = 'edit';
    protected $translate_module = 'CustomerPortal_Client';

    function process(CustomerPortal_API_Request $request) {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;
        $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
        $contact = vtws_retrieve($contactId, $current_user); //Getting user full details from contact_details table

        if ($current_user) {
            $module = $request->get('module');
            $sub_operation = $request->get('sub_operation');
            /*
             * It will check module in vtiger_customerportal_tabs table. Need to add module if not added
             */
            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module), 1412);
                exit;
            }

            //It will help to convert from values JSONstring to array
            $valuesJSONString = $request->get('values', '', false);
            $values = "";

            if (!empty($valuesJSONString) && is_string($valuesJSONString)) {
                $values = Zend_Json::decode($valuesJSONString);
            } else {
                $values = $valuesJSONString; // Either empty or already decoded.
            }
            //END   

            /* Validation for emoji text input Start */
            foreach ($values as $key => $value) {
                $checkInput = CustomerPortal_Utils::hasEmoji($value);
                if ($checkInput) {
                    throw new Exception(vtranslate('CAB_LBL_PLEASE_ENTER_VALID_CHARACTER', $this->translate_module), 1413);
                    exit;
                }
            }
            /* Validation for emoji text input End */

            if (isset($values) && !empty($values)) {
                //Get input data from values
                $payment_operation = $values['payment_operation'];
                $payment_from = $values['payment_from'];
                $payment_type = $values['payment_type'];
                $payment_currency = $values['payment_currency'];
                $payment_to = $values['payment_to'];
                $amount = $values['amount'];
                //End                
                $internaltransfer_parameters = array();
                $payment_process = '';

                //Verify configuration for module or operation is enabled or not                
                CustomerPortal_Utils::checkConfiguration($contactId, $current_user, $module, $values, $portal_language);                
                //End

                if ($payment_operation == 'InternalTransfer') {
                    
                    $values['contactid'] = $this->getActiveCustomer()->id;
                    $values['net_amount'] = $amount;
                    $values['wallet_id'] = $contact['contact_no'];
                    
                    CustomerPortal_Utils::verifyInputData($values, array(), $module, $portal_language);
                    
                    //Process on Submit from cabinet or mobile side
                    if ($sub_operation == 'Submit') {
                            $response->addToResult('payment_type', $values['payment_type']);
                            $response->addToResult('payment_from', $values['payment_from']);
                            $response->addToResult('payment_currency', $values['payment_currency']);
                            $response->addToResult('payment_to', $values['payment_to']);
                            $response->addToResult('amount', $values['amount']);                            
                            $response->addToResult('net_amount', $values['net_amount']);
                            $response->addToResult('wallet_id', $values['wallet_id']);
                            $response->addToResult('contactid', $values['contactid']);
                            $response->addToResult('comment', $values['comment']);
//                        $response->addToResult('paymentdescribe', $values);
//                        $response->addToResult('message', 'Submitted');
                    } else if ($sub_operation == 'Confirm') {
                        $payment_status = 'Pending';
                        if ($values['payment_type'] == 'A2A') {
                            $payment_process = 'Account Withdrawal';
                        }
                        if ($values['payment_type'] == 'E2E') {
                            $payment_process = 'Wallet Withdrawal';
                            $values['payment_from'] = $values['wallet_id'];
                        }

                        $this->recordValues = array(
                            'payment_operation' => $payment_operation,
                            'amount' => $amount,
                            'contactid' => $contactId,
                            'payment_amount' => $amount,
                            'payment_currency' => $payment_currency,
                            'payment_process' => $payment_process,
                            'payment_status' => $payment_status,
                            'payment_type' => $values['payment_type'],
                            'payment_from' => $values['payment_from'],
                            'payment_to' => $values['payment_to'],
                            'request_from' => 'CustomerPortal',
                            'comment' => $values['comment'],
                        );
                        // set assigned user to default assignee                                                
                        //$this->recordValues['assigned_user_id'] = CustomerPortal_Settings_Utils::getDefaultAssignee();
                        $this->recordValues['assigned_user_id'] = '19x' . getRecordOwnerId($this->getActiveCustomer()->id)['Users']; //contact's assignee will assign to sub module record

                        //Setting source to customer portal
                        $this->recordValues['source'] = $current_user->column_fields['source']; //'CUSTOMER PORTAL';
                        $this->recordValues = vtws_create($module, $this->recordValues, $current_user);
                        if (!empty($this->recordValues)) {
                            $record_id = $this->recordValues['id'];
                            $response->addToResult('payment_type', $values['payment_type']);
                            if($this->recordValues['payment_status'] == 'Failed'){
                                throw new Exception(vtranslate($this->recordValues['failure_reason'], $module), 1413);
                                exit;
                                //$response->addToResult('message', vtranslate($this->recordValues['failure_reason'], $module));
                            }else{
                                $response->addToResult('message', CustomerPortal_Utils::setMessage($module, $this->recordValues['payment_status'], $payment_operation, $portal_language));
                            }                            
                        }
                    } else {
                        throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module), 1413);
                        exit;
                    }
                } else {
                    throw new Exception(vtranslate('CAB_MSG_PAYMENT_OPER_DOES_NOT_MATCH', $this->translate_module), 1413);                    
                    exit;
                }
            } else {
                throw new Exception(vtranslate('CAB_MSG_VALUES_PARAM_SHOULD_NOT_BE_EMPTY', $this->translate_module), 1413);                
                exit;
            }
        }
        return $response;
    }

}
