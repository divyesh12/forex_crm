<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/* Add By Divyesh Chothani - 20-09-2019
 * Commnet:- Check duplicate Fisrtname,brandname, mobile number validation
 */
require_once('modules/ServiceProviders/ServiceProviders.php');

Class Payments_EditAjax_Action extends Vtiger_IndexAjax_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('validationPaymentOperation');
        // $this->exposeMethod('checkFirstNameLastNameMobileNo');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    public function validationPaymentOperation(Vtiger_Request $request) {
        global $adb;

        $module = 'Payments';
        $recordId = $request->get('record');
        $action = $request->get('form_type');
        $mode = $request->get('mode');
        $payment_operation = $request->get('payment_operation');
        $contactid = $request->get('contactid');
        $payment_type = $request->get('payment_type');
        $payment_process = $request->get('payment_process');
        $payment_currency = $request->get('payment_currency');
        $amount = $request->get('amount');
        $commission = $request->get('commission');
        $commission_value = $request->get('commission_value');
        $payment_amount = $request->get('payment_amount');
        $payment_status = $request->get('payment_status');
        $reject_reason = $request->get('reject_reason');
        $transaction_id = $request->get('transaction_id');
        $comment = $request->get('comment');
        $description = $request->get('description');
        $failure_reason = $request->get('failure_reason');
        $payment_from = $request->get('payment_from');
        $payment_to = $request->get('payment_to');
        $assigned_user_id = $request->get('assigned_user_id');
        $request_from = $request->get('request_from');


        $response = new Vtiger_Response();

        if ($contactid && isset($contactid)) {
            $Cont_recordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
            $Cont_modelData = $Cont_recordModel->getData();
            // $ewallet_balance = Payments_Record_Model::getWalletBalance($contactid);
            $ewalletBalanceResult = getEwalletBalanceBaseOnCurrency($contactid);
            $ewallet_balance = 0;
            if (array_key_exists($payment_currency, $ewalletBalanceResult)) {
                $ewallet_balance = $ewalletBalanceResult[$payment_currency];
            }
            $ewallet_no = $Cont_modelData['contact_no'];
        }

        if ($action == 'Save') {
            if ($payment_operation == 'Deposit') {
                if ($payment_type == 'P2A' && $payment_status == 'InProgress') {
                    $account_no = $payment_to;
                    $liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($account_no, $contactid);
                    $metatrader_type = $liveAccountDetails['live_metatrader_type'];
                    $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);

                    $isLiveAccountExist = isLiveAccountExist($contactid,$account_no);
                    if(!$isLiveAccountExist){
                        $message = vtranslate('LBL_NOT_FOUND_FROM_CRM', $module);
                        $response->setResult(array('success' => true, 'message' => $message));
                    }else if (empty($provider)) {
                        $message = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
                        $response->setResult(array('success' => true, 'message' => $message));
                    } else {
                        $check_account_exist_result = $provider->checkAccountExist($account_no);
                        if ($ewallet_balance < $amount && $ewallet_no == $payment_from) {
                            $message = vtranslate('LBL_INSUFFICIENT_WALLET_BALANCE', $module);
                            $response->setResult(array('success' => true, 'message' => $message));
                        } else if ($check_account_exist_result->Code != 200) {
                            $message = vtranslate($check_account_exist_result->Message, $module);
                            $response->setResult(array('success' => true, 'message' => $message));
                        } else {
                            $response->setResult(array('success' => false));
                        }
                    }
                } else if ($payment_type == 'P2E' && $payment_status == 'InProgress') {
                    $response->setResult(array('success' => false));
                } else {
                    $response->setResult(array('success' => false));
                }
            } else if ($payment_operation == 'Withdrawal') {
                if ($payment_type == 'A2P' && $payment_status == 'InProgress') {
                    $account_no = $payment_from;
                    $liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($account_no, $contactid);
                    $metatrader_type = $liveAccountDetails['live_metatrader_type'];
                    $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);

                    $isLiveAccountExist = isLiveAccountExist($contactid,$account_no);
                    if(!$isLiveAccountExist){
                        $message = vtranslate('LBL_NOT_FOUND_FROM_CRM', $module);
                        $response->setResult(array('success' => true, 'message' => $message));
                    }else if (empty($provider)) {
                        $message = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
                        $response->setResult(array('success' => true, 'message' => $message));
                    } else {
                        $check_account_exist_result = $provider->checkAccountExist($account_no);
                        $account_balance_result = $provider->getAccountInfo($account_no);
//                        echo "<pre>";
//                        print_r($account_balance_result);
//                        exit;
                        if ($check_account_exist_result->Code != 200) {
                            $message = vtranslate($check_account_exist_result->Message, $module);
                            $response->setResult(array('success' => true, 'message' => $message));
                        } else if ($account_balance_result->Code == 200 && $account_balance_result->Message == 'Ok') {
                            if ($account_balance_result->Data->free_margin < $amount && $account_no == $account_balance_result->Data->login) {
                                $message = vtranslate('LBL_INSUFFICIENT_ACCOUNT_BALANCE', $module);
                                $response->setResult(array('success' => true, 'message' => $message));
                            } else {
                                $response->setResult(array('success' => false));
                            }
                        } else {
                            $message = vtranslate($account_balance_result->Message, $module);
                            $response->setResult(array('success' => true, 'message' => $message));
                        }
                    }
                } else if ($payment_type == 'E2P' && $payment_status == 'InProgress') {
                    if ($ewallet_balance < $amount && $ewallet_no == $payment_from) {
                        $message = vtranslate('LBL_INSUFFICIENT_WALLET_BALANCE', $module);
                        $response->setResult(array('success' => true, 'message' => $message));
                    } else {
                        $response->setResult(array('success' => false));
                    }
                } else {
                    $response->setResult(array('success' => false));
                }
            } else if ($payment_operation == 'InternalTransfer') {
                if ($payment_type == 'A2A' && $payment_status == 'InProgress') {
                    $from_account_no = $payment_from;
                    $from_liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($from_account_no, $contactid);
                    $from_metatrader_type = $from_liveAccountDetails['live_metatrader_type'];

                    $to_account_no = $payment_to;
                    $to_liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($to_account_no, $contactid);
                    $to_metatrader_type = $to_liveAccountDetails['live_metatrader_type'];

                    $from_provider = ServiceProvidersManager::getActiveInstanceByProvider($from_metatrader_type);
                    $to_provider = ServiceProvidersManager::getActiveInstanceByProvider($to_metatrader_type);
                    
                    $isFromLiveAccountExist = isLiveAccountExist($contactid,$from_account_no);
                    $isToLiveAccountExist = isLiveAccountExist($contactid,$to_account_no);
                    if(!$isFromLiveAccountExist || !$isToLiveAccountExist){
                        $message = vtranslate('LBL_NOT_FOUND_FROM_CRM', $module);
                        $response->setResult(array('success' => true, 'message' => $message));
                    }else if (empty($from_provider) || empty($to_provider)) {
                        $message = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
                        $response->setResult(array('success' => true, 'message' => $message));
                    } else {
                        $from_check_account_exist_result = $from_provider->checkAccountExist($from_account_no);
                        //$account_balance_result = $from_provider->getBalance($from_account_no);
                        $account_balance_result = $from_provider->getAccountInfo($from_account_no);
                        $to_check_account_exist_result = $to_provider->checkAccountExist($to_account_no);
                        if ($from_check_account_exist_result->Code != 200) {
                            $message = vtranslate($from_check_account_exist_result->Message, $module);
                            $response->setResult(array('success' => true, 'message' => $message));
                        } else if ($to_check_account_exist_result->Code != 200) {
                            $message = vtranslate($to_check_account_exist_result->Message, $module);
                            $response->setResult(array('success' => true, 'message' => $message));
                        } else if ($account_balance_result->Code == 200 && $account_balance_result->Message == 'Ok') {
                            if ($account_balance_result->Data->free_margin < $amount && $from_account_no == $account_balance_result->Data->login) {
                                //  if ($account_balance_result->Data->free_margin < $amount) {
                                $message = vtranslate('LBL_INSUFFICIENT_ACCOUNT_BALANCE', $module);
                                $response->setResult(array('success' => true, 'message' => $message));
                            } else {
                                $response->setResult(array('success' => false));
                            }
                        } else {
                            $message = vtranslate($account_balance_result->Message, $module);
                            $response->setResult(array('success' => true, 'message' => $message));
                        }
                    }
                } else if ($payment_type == 'E2E' && $payment_status == 'InProgress') {
                    $ewallet_no_exits = Payments_Record_Model::checkWalletNoExist($payment_to);

                    if ($ewallet_balance < $amount && $ewallet_no == $payment_from) {
                        $message = vtranslate('LBL_INSUFFICIENT_WALLET_BALANCE', $module);
                        $response->setResult(array('success' => true, 'message' => $message));
                    } else if (!$ewallet_no_exits) {
                        $message = $payment_to . " " . vtranslate('LBL_WALLET_NO_NOT_EXIST', $module);
                        $response->setResult(array('success' => true, 'message' => $message));
                    } else {
                        $response->setResult(array('success' => false));
                    }
                } else {
                    $response->setResult(array('success' => false));
                }
            } else if ($payment_operation == 'IBCommission') {
                if ($payment_type == 'C2E' && $payment_status == 'Completed' && $payment_process == 'Finish') {
                    $ewallet_no_exits = Payments_Record_Model::checkWalletNoExist($payment_to);
                    if (!$ewallet_no_exits) {
                        $message = $payment_to . " " . vtranslate('LBL_WALLET_NO_NOT_EXIST', $module);
                        $response->setResult(array('success' => true, 'message' => $message));
                    } else {
                        $response->setResult(array('success' => false));
                    }
                } else {
                    $response->setResult(array('success' => false));
                }
            } else {
                $message = "Something wrong!, Please check form parameters.";
                $response->setResult(array('success' => true, 'message' => $message));
            }
        }

        $response->emit();
    }

}
