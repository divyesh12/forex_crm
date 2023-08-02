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

class CustomerPortal_PaymentCallBack extends CustomerPortal_API_Abstract {

    protected $recordValues = false;
    protected $mode = 'edit';
    protected $translate_module = 'CustomerPortal_Client';

    function process(CustomerPortal_API_Request $request) {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;
        if ($current_user) {
            $module = $request->get('module');
            $sub_operation = $request->get('sub_operation');
            $recordId = $request->get('record_id');
            $order_id = $request->get('order_id');
            $payment_from = $request->get('payment_from');
            $status = $request->get('status');
            /*
             * It will check module in vtiger_customerportal_tabs table. Need to add module if not added
             */
            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }
            if ($sub_operation != 'Deposit') {
                throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module, $portal_language), 1412);
                exit;
            }

            $fields = implode(',', CustomerPortal_Utils::getActiveFields($module));
            $sql = sprintf('SELECT %s FROM %s WHERE id=\'%s\';', $fields, $module, $recordId);
            $result = vtws_query($sql, $this->getActiveUser());
            if (empty($result)) {
                throw new Exception(vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', $this->translate_module, $portal_language), 1414);
                exit;
            }

            //Check Provider exist or not and deposit supporting or not
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_from);
            if (empty($provider))
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'Payments', $portal_language), 1416);
            $providerParameters = array();
            $deposit_supported = $provider->parameters['deposit_supported'];
            if ($deposit_supported == 'Yes') {
                $providerParameters = $provider->parameters;
            } else {
                throw new Exception(vtranslate('CAB_MSG_DEPOSIT_DOES_NOT_SUPPORTS_THE', $module, $portal_language) . $payment_from . vtranslate('CAB_MSG_PAYMENT_GATEWAY', $module, $portal_language), 1414);
                exit;
            }
            //End
            //It will help to convert from values JSONstring to array
            $payment_responseJSONString = $request->get('payment_response', '', false);
            $payment_response = "";
            if (!empty($payment_responseJSONString) && is_string($payment_responseJSONString)) {
                $payment_response = Zend_Json::decode($payment_responseJSONString);
            } else {
                $payment_response = $payment_responseJSONString; // Either empty or already decoded.
            }
            //END            
            //Check configuration added by sandeep 20-02-2020
            $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
            CustomerPortal_Utils::checkConfiguration($contactId, $current_user, $module, array(), $portal_language);
            //End

            if (isset($payment_response) && !empty($payment_response)) {
                //Update record setting 
                $this->recordValues = array();
                $this->recordValues['id'] = $recordId;

                //Setting missing mandatory fields for record update, it will get column from database table which not get from request.
                $describe = vtws_describe($module, $current_user);
                $mandatoryFields = CustomerPortal_Utils:: getMandatoryFields($describe);
                foreach ($mandatoryFields as $fieldName => $type) {
                    if (!isset($this->recordValues[$fieldName])) {
                        if (array_key_exists($fieldName, $result[0])) {
                            $this->recordValues[$fieldName] = $result[0][$fieldName];
                        }
                    }
                }
                //End
                if ($status == 'Success') {
                    $res = $provider->paymentResponseVerification($status, $payment_response, $order_id, $portal_language);
                    if (!empty($res) && $res['success']) {
                        //Check the payment gateway is auto confirm or not
                        if (isset($provider->parameters['auto_confirm']) && $provider->parameters['auto_confirm'] == 'No') {
                            //Not Auto confirm
                            $res['message'] = vtranslate('CAB_MSG_AUTO_CONFIRM_NO_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $module, $portal_language);
                            $this->recordValues['payment_status'] = 'PaymentSuccess';
                        } else {
                            //Auto confirm
                            $this->recordValues['payment_status'] = $res['payment_status'];
                        }
                        if ($res['payment_status'] == 'Pending') {
                            $response->setResult(array('payment_status' => $res['payment_status'], 'message' => $res['message']));
                        } else {
                            $updatedStatus = vtws_update($this->recordValues, $current_user);
                            if ($updatedStatus['id'] == $recordId) {
                                if ($updatedStatus['payment_status'] != 'Failed') {
                                    $response->setResult(array('payment_status' => $updatedStatus['payment_status'], 'message' => $res['message']));
                                } else {
                                    throw new Exception(vtranslate($updatedStatus['failure_reason'], $module, $portal_language), 1414);
                                    exit;
                                }
                            } else {
                                throw new Exception(vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', $this->translate_module, $portal_language), 1414);
                                exit;
                                //$response->setError("RECORD_NOT_FOUND", "Record does not exist");
                            }
                        }
                    } else {
                        throw new Exception(vtranslate($res['message'], $module, $portal_language), 1414);
                        exit;
                    }
                } else if ($status == 'Failed') {
                    $res = $provider->paymentResponseVerification($status, $payment_response, $order_id, $portal_language);
                    if (!empty($res) && $res['success']) {
                        $this->recordValues['payment_status'] = $res['payment_status'];
                        $this->recordValues['failure_reason'] = $res['message'];
                        $updatedStatus = vtws_update($this->recordValues, $current_user);
                        if ($updatedStatus['id'] == $recordId) {
                            $response->setResult(array('payment_status' => $updatedStatus['payment_status'], 'message' => vtranslate($updatedStatus['failure_reason'], $module, $portal_language)));
                        } else {
                            throw new Exception(vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', $this->translate_module, $portal_language), 1414);
                            exit;
                        }
                    } else {
                        throw new Exception(vtranslate($res['message'], $module, $portal_language), 1414);
                        exit;
                    }
                } else if ($status == 'Pending') {
                    $res = $provider->paymentResponseVerification($status, $payment_response, $order_id, $portal_language);

                    if (!empty($res) && $res['success']) {
                        //Check the payment gateway is auto confirm or not
                        if (isset($provider->parameters['auto_confirm']) && $provider->parameters['auto_confirm'] == 'No') {
                            //Not Auto confirm
                            $res['message'] = vtranslate('CAB_MSG_AUTO_CONFIRM_NO_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $module, $portal_language);
                            $this->recordValues['payment_status'] = 'PaymentSuccess';
                        } else {
                            //Auto confirm
                            $this->recordValues['payment_status'] = $res['payment_status'];
                        }
                        if ($res['payment_status'] == 'Pending') {
                            $response->setResult(array('payment_status' => $res['payment_status'], 'message' => $res['message']));
                        } else {
                            $updatedStatus = vtws_update($this->recordValues, $current_user);
                            if ($updatedStatus['id'] == $recordId) {
                                if ($updatedStatus['payment_status'] != 'Failed') {
                                    $response->setResult(array('payment_status' => $updatedStatus['payment_status'], 'message' => $res['message']));
                                } else {
                                    throw new Exception(vtranslate($updatedStatus['failure_reason'], $module, $portal_language), 1414);
                                    exit;
                                }
                            } else {
                                throw new Exception(vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', $this->translate_module, $portal_language), 1414);
                                exit;
                            }
                        }
                    } else if(!$res['success']) {
                        $this->recordValues['payment_status'] = $res['payment_status'];
                        $this->recordValues['failure_reason'] = $res['message'];
                        $updatedStatus = vtws_update($this->recordValues, $current_user);
                        if ($updatedStatus['id'] == $recordId) {
                            $response->setResult(array('payment_status' => $updatedStatus['payment_status'], 'message' => vtranslate($updatedStatus['failure_reason'], $module, $portal_language)));
                        } else {
                            throw new Exception(vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', $this->translate_module, $portal_language), 1414);
                            exit;
                        }
                    } else {
                        throw new Exception(vtranslate($res['message'], $module, $portal_language), 1414);
                        exit;
                    }
                } else {
                    throw new Exception($status . vtranslate('CAB_MSG_STATUS_DOES_NOT_EXIST', $module, $portal_language), 1414);
                    exit;
                }
            } else {
                throw new Exception(vtranslate('CAB_PAYMENT_RESPONSE_SHOULD_NOT_BE_EMPTY', $module, $portal_language), 1413);
                exit;
            }
        } else {
            throw new Exception(vtranslate('CAB_CURRENT_USER_DOES_NOT_EXIST', $module, $portal_language), 1412);
            exit;
        }
        return $response;
    }

}
