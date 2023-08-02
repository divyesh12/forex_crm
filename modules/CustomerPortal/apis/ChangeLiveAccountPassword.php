<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once('modules/ServiceProviders/ServiceProviders.php');

class CustomerPortal_ChangeLiveAccountPassword extends CustomerPortal_FetchRecord {

    protected $recordValues = false;
    protected $mode = 'edit';
    protected $translate_module = 'CustomerPortal_Client';

    protected function isNewRecordRequest(CustomerPortal_API_Request $request) {
        $recordid = $request->get('recordId');
        return (preg_match("/([0-9]+)x0/", $recordid));
    }

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        global $current_user;
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;
        if ($current_user) {
            $module = $request->get('module');
            $sub_operation = $request->get('sub_operation');

            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }
            //Check configuration added by sandeep 20-02-2020
            $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
            CustomerPortal_Utils::checkConfiguration($contactId, $current_user, $module, array(), $portal_language);
            //End

            if (in_array($module, array('LiveAccount'))) {
                $recordId = $request->get('recordId');

                if (!empty($recordId)) {
                    //Stop edit record if edit is disabled
                    if (!CustomerPortal_Utils::isModuleRecordEditable($module)) {
                        throw new Exception(vtranslate('CAB_MSG_RECORD_CANNOT_BE_EDITED', $module, $portal_language), 1412);
                        exit;
                    }
                } else {
                    if (!CustomerPortal_Utils::isModuleRecordCreatable($module)) {
                        throw new Exception(vtranslate('CAB_MSG_MODULE_RECORD_CANNOT_BE_CREATED', $module, $portal_language), 1412);
                        exit;
                    }
                }
                $valuesJSONString = $request->get('values', '', false);
                $values = "";

                if (!empty($valuesJSONString) && is_string($valuesJSONString)) {
                    $values = Zend_Json::decode($valuesJSONString);
                } else {
                    $values = $valuesJSONString; // Either empty or already decoded.
                }

                try {
                    if (vtws_recordExists($recordId)) {
                        // Retrieve or Initalize
                        if (!empty($recordId) && !$this->isNewRecordRequest($request)) {
                            $this->recordValues = vtws_retrieve($recordId, $current_user);
                        } else {
                            $this->recordValues = array();
                            // set assigned user to default assignee
                            //$this->recordValues['assigned_user_id'] = CustomerPortal_Settings_Utils::getDefaultAssignee();
                            $this->recordValues['assigned_user_id'] = '19x'.getRecordOwnerId($this->getActiveCustomer()->id)['Users']; //contact's assignee will assign to sub module record
                        }

                        // Set the modified values
                        if (!empty($values)) {
                            foreach ($values as $name => $value) {
                                $this->recordValues[$name] = $value;
                            }
                        }
                        // Setting missing mandatory fields for record.
                        $describe = vtws_describe($module, $current_user);
                        $mandatoryFields = CustomerPortal_Utils:: getMandatoryFields($describe);
                        foreach ($mandatoryFields as $fieldName => $type) {
                            if (!isset($this->recordValues[$fieldName])) {
                                if ($type['name'] == 'reference') {
                                    $crmId = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $module);
                                    $wsId = vtws_getWebserviceEntityId($type['refersTo'][0], $crmId);
                                    $this->recordValues[$fieldName] = $wsId;
                                } else {
                                    $this->recordValues[$fieldName] = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $module);
                                }
                            }
                        }

                        // Update or Create
                        if (isset($this->recordValues['id'])) {
                            foreach ($mandatoryFields as $fieldName => $type) {
                                if (!isset($this->recordValues[$fieldName]) || empty($this->recordValues[$fieldName])) {
                                    if ($type['name'] !== 'reference') {
                                        $this->recordValues[$fieldName] = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $module);
                                    }
                                }
                            }

                            $liveAccountId = explode('x', $this->recordValues['id']);
                            $liveAccountId = $liveAccountId[1];
                            $LiveAccountModel = LiveAccount_Record_Model::getInstanceById($liveAccountId);
                            $currentPassword = $LiveAccountModel->get('password');
                            $currentInvestorPassword = $LiveAccountModel->get('investor_password');

                            //Check Sub operation
                            if ($sub_operation == 'TradingPassword') {
                                //Check cabinet password
                                $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
                                // $contact = vtws_retrieve($contactId, $current_user);
                                // $ok = CustomerPortal_API_Abstract::authenticatePortalUser($contact['email'], $this->recordValues['current_password'], true);
                                // if (!$ok) {
                                //     throw new Exception(vtranslate('CAB_MSG_INCORRECT_TRADING_PASS', $module, $portal_language), 1416);
                                // }

                                if ($this->recordValues['current_password'] != $currentPassword) {
                                    throw new Exception(vtranslate('CAB_MSG_INCORRECT_TRADING_PASS', $module, $portal_language), 1416);
                                }

                                //End
                                //ChangePassword to meta trader
                                $provider = ServiceProvidersManager::getActiveInstanceByProvider($this->recordValues['live_metatrader_type']);
                                if(empty($provider))
                                    throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'Payments', $portal_language), 1416);

                                $change_pass_result = $provider->changePassword($this->recordValues['account_no'], $this->recordValues['password'], '0');
                                if ($change_pass_result->Code == 200 && $change_pass_result->Message == 'Ok') {
                                    
                                } else {
                                    $message = $change_pass_result->Message;
                                    throw new Exception($message, 1416);
                                }
                                //End
                                $this->recordValues = vtws_update($this->recordValues, $current_user);
                                $request->set('action', 'TradingPassword');
                            } else if ($sub_operation == 'TradingInvestorPassword') {
                                //Check cabinet password
                                $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
                                // $contact = vtws_retrieve($contactId, $current_user);
                                // $ok = CustomerPortal_API_Abstract::authenticatePortalUser($contact['email'], $this->recordValues['current_password'], true);
                                // if (!$ok) {
                                //     throw new Exception("Incorrect cabinet password.", 1416);
                                // }

                                if ($this->recordValues['current_password'] != $currentInvestorPassword) {
                                    throw new Exception(vtranslate('CAB_MSG_INCORRECT_INVESTOR_PASS', $module, $portal_language), 1416);
                                }
                                
                                //End
                                //ChangePassword to meta trader
                                $provider = ServiceProvidersManager::getActiveInstanceByProvider($this->recordValues['live_metatrader_type']);
                                if(empty($provider))
                                    throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'Payments', $portal_language), 1416);
                                
                                $change_pass_result = $provider->changePassword($this->recordValues['account_no'], $this->recordValues['investor_password'], '1');
                                if ($change_pass_result->Code == 200 && $change_pass_result->Message == 'Ok') {
                                    
                                } else {
                                    $message = $change_pass_result->Message;
                                    throw new Exception($message, 1416);
                                }
                                //End
                                $this->recordValues = vtws_update($this->recordValues, $current_user);
                                $request->set('action', 'TradingInvestorPassword');
                            }
                        } else {
                            $response->setError("CAB_MSG_RECORD_DOES_NOT_EXIST", vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', $module, $portal_language));
                        }
                        // Update the record id
                        $request->set('recordId', $this->recordValues['id']);
                        $idComponents = explode('x', $this->recordValues['id']);
                        $recordId = $idComponents[1];
                        // Gather response with full details                        
                        $response = parent::process($request);
                    } else {
                        $response->setError(vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', $module, $portal_language));                        
                    }
                } catch (Exception $e) {
                    $response->setError($e->getCode(), $e->getMessage());
                }
            } else {
                $response->setError(1404, vtranslate('CAB_MSG_SAVE_OPER_NOT_SUPPORTS_THIS_MODULE', $module, $portal_language));
            }
            return $response;
        }
    }

}
