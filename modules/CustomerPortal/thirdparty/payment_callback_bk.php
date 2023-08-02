<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

include_once '../include.inc';
include_once('modules/ServiceProviders/PaymentProvidersHelper.php');
require_once('modules/ServiceProviders/ServiceProviders.php');
global $log,$adb;
$module = 'Payments';
$portal_language = 'en-us';
$notAllowedPSP = array('FairPay', 'PayTechno', 'Credit Card', 'FasaPay', 'VaultsPay');
$log->debug('Entering into payment_callback...');
$log->debug($_REQUEST);
if (isset($_REQUEST) && !empty($_REQUEST)) {
    if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'B2binpay')
    {
        $callback_payload = json_decode(file_get_contents('php://input'), true);
        $payment_response = $_REQUEST;
        $payment_response['callback'] = file_get_contents('php://input');
        $paymentMethod = $_REQUEST['pm'];
        $order_id = $_REQUEST['order_id'];
        // $status = $_REQUEST['status'];

        $getPaymentRecord = "SELECT vtiger_payments.*,vtiger_crmentity.smownerid FROM vtiger_payments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_payments.paymentsid WHERE vtiger_crmentity.deleted=0 AND vtiger_payments.payment_from = ? AND vtiger_payments.order_id = ? AND vtiger_payments.payment_status=?";
        $resultGetPayment = $adb->pquery($getPaymentRecord, array($paymentMethod, $order_id, 'Pending'));
        $num_rows = $adb->num_rows($resultGetPayment);

        if ($num_rows > 0) {
            $record_id = $adb->query_result($resultGetPayment, 0, 'paymentsid');
            $payment_operation = $adb->query_result($resultGetPayment, 0, 'payment_operation');
            $amount = $adb->query_result($resultGetPayment, 0, 'amount');
            $contactid = $adb->query_result($resultGetPayment, 0, 'contactid');
            $payment_currency = $adb->query_result($resultGetPayment, 0, 'payment_currency');
            $payment_process = $adb->query_result($resultGetPayment, 0, 'payment_process');
            $payment_type = $adb->query_result($resultGetPayment, 0, 'payment_type');
            $payment_from = $adb->query_result($resultGetPayment, 0, 'payment_from');
            $payment_to = $adb->query_result($resultGetPayment, 0, 'payment_to');
            $assigned_user_id = $adb->query_result($resultGetPayment, 0, 'smownerid');

            if ($record_id) {
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($paymentMethod);
                if (empty($provider)) {
                    $payment_response['message'] = "Error : Callback Response. Payment provider not found.";
                    createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, 'Cancelled');
                    $log->debug($payment_response['message']);
                    exit;
                }
                
                /*Verify Signature*/
                $callback_sign = $callback_payload['meta']['sign'];$log->debug('$callback_sign='.$callback_sign);
                $callback_time = $callback_payload['meta']['time'];$log->debug('$callback_time='.$callback_time);

                # retrieve transfer and deposit attributes
                $included_transfer = array_filter(
                    $callback_payload['included'],
                    function ($item) {
                        return $item['type'] === 'transfer';
                    }
                );
                $included_transfer = array_pop($included_transfer)['attributes'];$log->debug('$included_transfer=');$log->debug($included_transfer);
                $deposit = $callback_payload['data']['attributes'];$log->debug('$deposit=');$log->debug($deposit);

                $signStatus = $included_transfer['status'];$log->debug('$signStatus='.$signStatus);
                $orderAmount = $included_transfer['amount'];$log->debug('$orderAmount='.$orderAmount);
                $tracking_id = $deposit['tracking_id'];$log->debug('$tracking_id='.$tracking_id);
                $status = $deposit['status'];

                # prepare data for hash check
                $apiUsername = $provider->getParameter('b2binpay_key');$log->debug('$apiUsername='.$apiUsername);
                $apiPassword = $provider->getParameter('b2binpay_secret');$log->debug('$apiPassword='.$apiPassword);
                $message = $signStatus . $orderAmount . $tracking_id . $callback_time;
                $hash_secret = hash('sha256',  $apiUsername . $apiPassword, true);
                $hash_hmac_result = hash_hmac('sha256', $message, $hash_secret);$log->debug('$hash_hmac_result='.$hash_hmac_result);

                # print result
                if ($hash_hmac_result === $callback_sign) {
                    $log->debug('Signature Verified');
                } else {
                    $log->debug('Invalid Signature');
                }
                /*Verify Signature*/
                $log->debug('$status='.$status);
                if ($status == 2) {$log->debug('in if status');
                    $status = 'Success';
                    $payment_response['message'] = vtranslate('CAB_MSG_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $module, $portal_language);
                    $res = array('success' => true, 'payment_status' => 'Confirmed', 'message' => $payment_response);
                    
                    if (isset($provider->parameters['auto_confirm']) && $provider->parameters['auto_confirm'] == 'No') {
                        $res['message'] = vtranslate('CAB_MSG_AUTO_CONFIRM_NO_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $module, $portal_language);
                        $res['payment_status'] = 'PaymentSuccess';
                    } else {
                        $res['payment_status'] = $res['payment_status'];
                    }
                    $postData = array(
                        'record_id' => '39x' . $record_id,
                        'payment_operation' => $payment_operation,
                        'payment_status' => $res['payment_status'],
                        'assigned_user_id' => '19x'.$assigned_user_id,
                        'amount' => $amount,
                        'contactid' => '12x' . $contactid,
                        'payment_currency' => $payment_currency,
                        'payment_process' => $payment_process,
                        'payment_type' => $payment_type,
                        'payment_from' => $payment_from,
                        'payment_to' => $payment_to,
                    );

                    $record = '39x' . $record_id;
                    $recordRes = UpdateRecord($postData, $module, $record);
                    if ($recordRes['success'] == 1 && (!isset($recordRes['error']))) {
                        if ($recordRes['result']['record']['id'] == $record) {
                            $payment_response['message'] = "Success : Callback Response. Payment is paid.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, 'Success');
                            $log->debug($payment_response['message']);
                        } else {
                            $payment_response['message'] = "Error : Callback Response. Payment record not found.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, 'Failed');
                            $log->debug($payment_response['message']);
                        }
                    } else {
                        $payment_response['message'] = $recordRes['error']['code'] . ' - ' . $recordRes['error']['message'];
                        $payment_response['post_data'] = $postData;
                        createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, 'Failed');
                        $log->debug($payment_response['message']);
                    }
                    // http_response_code(200);
                } else if ($status == -3 || $status == -2 || $status == -1) {$log->debug('in else if status');
                    $status = 'Failed';
                    $postData = array(
                        'record_id' => '39x' . $record_id,
                        'payment_operation' => $payment_operation,
                        'payment_status' => $status,
                        'assigned_user_id' => '19x'.$assigned_user_id,
                        'amount' => $amount,
                        'contactid' => '12x' . $contactid,
                        'payment_currency' => $payment_currency,
                        'payment_process' => $payment_process,
                        'payment_type' => $payment_type,
                        'payment_from' => $payment_from,
                        'payment_to' => $payment_to,
                    );
                    $record = '39x' . $record_id;
                    $recordRes = UpdateRecord($postData, $module, $record);
                    if ($recordRes['success'] == 1 && (!isset($recordRes['error']))) {
                        if ($recordRes['result']['record']['id'] == $record) {
                            $payment_response['message'] = "Failed : Callback Response. Payment failed from payment gateway side.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, 'Failed');
                            $log->debug($payment_response['message']);
                        } else {
                            $payment_response['message'] = "Error : Callback Response. Payment record not found.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, 'Failed');
                            $log->debug($payment_response['message']);
                        }
                    } else {
                        $payment_response['message'] = $recordRes['error']['code'] . ' - ' . $recordRes['error']['message'];
                        $payment_response['post_data'] = $postData;
                        createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, 'Failed');
                        $log->debug($payment_response['message']);
                    }
                    // http_response_code(200);
                } else {
                   createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, 'Pending'); 
                }
            } else {
                $payment_response['message'] = "Error : Callback Response. Payment record not found.";
                createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, 'Cancelled');
                $log->debug($payment_response['message']);
                exit;
            }
        } else {
            $payment_response['message'] = "Error : Callback Response. Payment record not found.";
            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, 'Cancelled');
            $log->debug($payment_response['message']);
            exit;
        }
    }
    else if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'FasaPay')
    {
        $fasapayIpAddresses = array('139.162.19.188', '139.162.53.190', '2400:8901::f03c:92ff:fe3e:6458', '2400:8901::f03c:92ff:fe7b:a89e');
        $log->debug('Entering into Fasapay...');$log->debug($_REQUEST);
        
        $log->debug('Remote address=');
        $log->debug($_SERVER['REMOTE_ADDR']);
        
        $requestBody['pm'] = $_REQUEST['pm'];
        $requestBody['order_id'] = $_REQUEST['order_id'];
        $payment_response = $_REQUEST;
        $paymentMethod = $_REQUEST['pm'];
        $order_id = $_REQUEST['order_id'];
        $status = $requestBody['status'];
        
        $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_response['pm']);
        $securityWord = $provider->parameters['security_word'];
        
        /*Cross check hash*/
        $fpHashAllFromResponse = $payment_response['fp_hash_all'];
        $fpHashList = $payment_response['fp_hash_list'];
        $fpHashListArr = explode('|', $fpHashList);

        $stringToHash = '';
        foreach($fpHashListArr as $key => $hashField)
        {
            if($hashField == 'SCI_SECURITY_WORD')
            {
                $stringToHash .= $securityWord . '|';
            }
            else
            {
                $stringToHash .= $payment_response[$hashField] . '|';
            }
        }
        $stringToHashAll = trim($stringToHash, '|');
        $fpHashAll = hash('sha256', $stringToHashAll);$log->debug('$fpHashAll='.$fpHashAll);
        
        if($fpHashAll == $fpHashAllFromResponse)
        {$log->debug('FP Hash matched');
            $getPaymentRecord = "SELECT vtiger_payments.*,vtiger_crmentity.smownerid FROM vtiger_payments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_payments.paymentsid WHERE vtiger_crmentity.deleted=0 AND vtiger_payments.payment_from = ? AND vtiger_payments.order_id = ?";
            $resultGetPayment = $adb->pquery($getPaymentRecord, array($paymentMethod, $order_id));
            $num_rows = $adb->num_rows($resultGetPayment);

            if ($num_rows > 0)
            {
                $callbackResponse = 'Fasapay Status Form Callback';
                createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, $callbackResponse);
                $log->debug('Fasapay Status Form Callback stored into database');
            }
        }
        else
        {
            $log->debug('Fasapay Status Form Callback not stored into database due to hash not matched!');
        }
        /*Cross check hash*/
    }
    else if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'FairPay')
    {
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $requestBody['pm'] = $_REQUEST['pm'];
        $requestBody['order_id'] = $_REQUEST['order_id'];
        $payment_response = $requestBody;
        $paymentMethod = $_REQUEST['pm'];
        $order_id = $_REQUEST['order_id'];
        $status = $requestBody['status'];

        $getPaymentRecord = "SELECT vtiger_payments.*,vtiger_crmentity.smownerid FROM vtiger_payments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_payments.paymentsid WHERE vtiger_crmentity.deleted=0 AND vtiger_payments.payment_from = ? AND vtiger_payments.order_id = ? AND vtiger_payments.payment_status=?";
        $resultGetPayment = $adb->pquery($getPaymentRecord, array($paymentMethod, $order_id, 'Pending'));
        $num_rows = $adb->num_rows($resultGetPayment);

        if ($num_rows > 0) {
            $record_id = $adb->query_result($resultGetPayment, 0, 'paymentsid');
            $payment_operation = $adb->query_result($resultGetPayment, 0, 'payment_operation');
            $amount = $adb->query_result($resultGetPayment, 0, 'amount');
            $contactid = $adb->query_result($resultGetPayment, 0, 'contactid');
            $payment_currency = $adb->query_result($resultGetPayment, 0, 'payment_currency');
            $payment_process = $adb->query_result($resultGetPayment, 0, 'payment_process');
            $payment_type = $adb->query_result($resultGetPayment, 0, 'payment_type');
            $payment_from = $adb->query_result($resultGetPayment, 0, 'payment_from');
            $payment_to = $adb->query_result($resultGetPayment, 0, 'payment_to');
            $assigned_user_id = $adb->query_result($resultGetPayment, 0, 'smownerid');

            if ($record_id) {
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($paymentMethod);
                if (empty($provider)) {
                    $payment_response['message'] = "Error : Callback Response. Payment provider not found.";
                    createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                    $log->debug($payment_response['message']);
                    exit;
                }
                if ($status == 'PAID') {
                    $status = 'Success';
                    $payment_response['message'] = vtranslate('CAB_MSG_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $module, $portal_language);
                    $res = array('success' => true, 'payment_status' => 'Confirmed', 'message' => $payment_response);
                    
                    if (isset($provider->parameters['auto_confirm']) && $provider->parameters['auto_confirm'] == 'No') {
                        $res['message'] = vtranslate('CAB_MSG_AUTO_CONFIRM_NO_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $module, $portal_language);
                        $res['payment_status'] = 'PaymentSuccess';
                    } else {
                        $res['payment_status'] = $res['payment_status'];
                    }
                    $postData = array(
                        'record_id' => '39x' . $record_id,
                        'payment_operation' => $payment_operation,
                        'payment_status' => $res['payment_status'],
                        'assigned_user_id' => '19x'.$assigned_user_id,
                        'amount' => $amount,
                        'contactid' => '12x' . $contactid,
                        'payment_currency' => $payment_currency,
                        'payment_process' => $payment_process,
                        'payment_type' => $payment_type,
                        'payment_from' => $payment_from,
                        'payment_to' => $payment_to,
                    );

                    $record = '39x' . $record_id;
                    $recordRes = UpdateRecord($postData, $module, $record);
                    if ($recordRes['success'] == 1 && (!isset($recordRes['error']))) {
                        if ($recordRes['result']['record']['id'] == $record) {
                            $payment_response['message'] = "Success : Callback Response. Payment is paid.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                            $log->debug($payment_response['message']);
                            echo "Payment Success";
                            exit;
                        } else {
                            $payment_response['message'] = "Error : Callback Response. Payment record not found.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                            $log->debug($payment_response['message']);
                            exit;
                        }
                    } else {
                        $payment_response['message'] = $recordRes['error']['code'] . ' - ' . $recordRes['error']['message'];
                        $payment_response['post_data'] = $postData;
                        createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                        $log->debug($payment_response['message']);
                        exit;
                    }
                } else if ($status == 'FAILED' || $status == 'EXPIRED') {
                    $status = 'Failed';
                    $postData = array(
                        'record_id' => '39x' . $record_id,
                        'payment_operation' => $payment_operation,
                        'payment_status' => $status,
                        'assigned_user_id' => '19x'.$assigned_user_id,
                        'amount' => $amount,
                        'contactid' => '12x' . $contactid,
                        'payment_currency' => $payment_currency,
                        'payment_process' => $payment_process,
                        'payment_type' => $payment_type,
                        'payment_from' => $payment_from,
                        'payment_to' => $payment_to,
                        'failure_reason' => $requestBody['message'],
                    );
                    $record = '39x' . $record_id;
                    $recordRes = UpdateRecord($postData, $module, $record);
                    if ($recordRes['success'] == 1 && (!isset($recordRes['error']))) {
                        if ($recordRes['result']['record']['id'] == $record) {
                            $payment_response['message'] = "Failed : Callback Response. Payment failed from payment gateway side.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                            $log->debug($payment_response['message']);
                            echo "Payment Failed.";
                            exit;
                        } else {
                            $payment_response['message'] = "Error : Callback Response. Payment record not found.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                            $log->debug($payment_response['message']);
                            exit;
                        }
                    } else {
                        $payment_response['message'] = $recordRes['error']['code'] . ' - ' . $recordRes['error']['message'];
                        $payment_response['post_data'] = $postData;
                        createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                        $log->debug($payment_response['message']);
                        exit;
                    }
                } else if ($status == 'REJECTED') {
                    $status = 'Rejected';
                    $postData = array(
                        'record_id' => '39x' . $record_id,
                        'payment_operation' => $payment_operation,
                        'payment_status' => $status,
                        'assigned_user_id' => '19x'.$assigned_user_id,
                        'amount' => $amount,
                        'contactid' => '12x' . $contactid,
                        'payment_currency' => $payment_currency,
                        'payment_process' => $payment_process,
                        'payment_type' => $payment_type,
                        'payment_from' => $payment_from,
                        'payment_to' => $payment_to,
                        'failure_reason' => $requestBody['message'],
                    );
                    $record = '39x' . $record_id;
                    $recordRes = UpdateRecord($postData, $module, $record);
                    if ($recordRes['success'] == 1 && (!isset($recordRes['error']))) {
                        if ($recordRes['result']['record']['id'] == $record) {
                            $payment_response['message'] = "Failed : Callback Response. Payment Rejected from payment gateway side.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                            $log->debug($payment_response['message']);
                            echo "Payment Failed.";
                            exit;
                        } else {
                            $payment_response['message'] = "Error : Callback Response. Payment record not found.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                            $log->debug($payment_response['message']);
                            exit;
                        }
                    } else {
                        $payment_response['message'] = $recordRes['error']['code'] . ' - ' . $recordRes['error']['message'];
                        $payment_response['post_data'] = $postData;
                        createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                        $log->debug($payment_response['message']);
                        exit;
                    }
                } else {
                    $payment_response['message'] = "Error : Callback Response. Payment is Pending.";
                    createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                    $log->debug($payment_response['message']);
                }
            } else {
                $payment_response['message'] = "Error : Callback Response. Payment record not found.";
                createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                $log->debug($payment_response['message']);
                exit;
            }
        } else {
            $payment_response['message'] = "Error : Callback Response. Payment record not found.";
            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
            $log->debug($payment_response['message']);
            exit;
        }
    } 
    else if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'AwePay')
    {
        $requestBody['request'] = $_REQUEST;
        $requestBody['pm'] = $_REQUEST['pm'];
        $requestBody['order_id'] = $_REQUEST['order_id'];
        $payment_response = $requestBody;
        $paymentMethod = $_REQUEST['pm'];
        $order_id = $_REQUEST['order_id'];

        $status = $requestBody['request']['response'];
        $tid = $requestBody['request']['tid'];
        
        $getPaymentRecord = "SELECT vtiger_payments.*,vtiger_crmentity.smownerid FROM vtiger_payments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_payments.paymentsid WHERE vtiger_crmentity.deleted=0 AND vtiger_payments.payment_from = ? AND vtiger_payments.order_id = ? AND vtiger_payments.payment_status=?";
        $resultGetPayment = $adb->pquery($getPaymentRecord, array($paymentMethod, $tid, 'Pending'));
        $num_rows = $adb->num_rows($resultGetPayment);

        if ($num_rows > 0) {
            $record_id = $adb->query_result($resultGetPayment, 0, 'paymentsid');
            $payment_operation = $adb->query_result($resultGetPayment, 0, 'payment_operation');
            $amount = $adb->query_result($resultGetPayment, 0, 'amount');
            $contactid = $adb->query_result($resultGetPayment, 0, 'contactid');
            $payment_currency = $adb->query_result($resultGetPayment, 0, 'payment_currency');
            $payment_process = $adb->query_result($resultGetPayment, 0, 'payment_process');
            $payment_type = $adb->query_result($resultGetPayment, 0, 'payment_type');
            $payment_from = $adb->query_result($resultGetPayment, 0, 'payment_from');
            $payment_to = $adb->query_result($resultGetPayment, 0, 'payment_to');
            $assigned_user_id = $adb->query_result($resultGetPayment, 0, 'smownerid');

            if ($record_id) {
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($paymentMethod);
                if (empty($provider)) {
                    $payment_response['message'] = "Error : Callback Response. Payment provider not found.";
                    createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                    $log->debug($payment_response['message']);
                    exit;
                }
                if (strtolower($status) == 'approved') {
                    $status = 'Success';
                    $payment_response['message'] = vtranslate('CAB_MSG_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $module, $portal_language);
                    $res = array('success' => true, 'payment_status' => 'Confirmed', 'message' => $payment_response);
                    
                    if (isset($provider->parameters['auto_confirm']) && $provider->parameters['auto_confirm'] == 'No') {
                        $res['message'] = vtranslate('CAB_MSG_AUTO_CONFIRM_NO_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $module, $portal_language);
                        $res['payment_status'] = 'PaymentSuccess';
                    } else {
                        $res['payment_status'] = $res['payment_status'];
                    }
                    $postData = array(
                        'record_id' => '39x' . $record_id,
                        'payment_operation' => $payment_operation,
                        'payment_status' => $res['payment_status'],
                        'assigned_user_id' => '19x'.$assigned_user_id,
                        'amount' => $amount,
                        'contactid' => '12x' . $contactid,
                        'payment_currency' => $payment_currency,
                        'payment_process' => $payment_process,
                        'payment_type' => $payment_type,
                        'payment_from' => $payment_from,
                        'payment_to' => $payment_to,
                    );

                    $record = '39x' . $record_id;
                    $recordRes = UpdateRecord($postData, $module, $record);
                    if ($recordRes['success'] == 1 && (!isset($recordRes['error']))) {
                        if ($recordRes['result']['record']['id'] == $record) {
                            $payment_response['message'] = "Success : Callback Response. Payment is paid.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                            $log->debug($payment_response['message']);
                            echo "Payment Success";
                            exit;
                        } else {
                            $payment_response['message'] = "Error : Callback Response. Payment record not found.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                            $log->debug($payment_response['message']);
                            exit;
                        }
                    } else {
                        $payment_response['message'] = $recordRes['error']['code'] . ' - ' . $recordRes['error']['message'];
                        $payment_response['post_data'] = $postData;
                        createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                        $log->debug($payment_response['message']);
                        exit;
                    }
                } else if (strtolower($status) == 'declined' || strtolower($status) == 'payg_error' || strtolower($status) == 'failed') {
                    $status = 'Failed';
                    $postData = array(
                        'record_id' => '39x' . $record_id,
                        'payment_operation' => $payment_operation,
                        'payment_status' => $status,
                        'assigned_user_id' => '19x'.$assigned_user_id,
                        'amount' => $amount,
                        'contactid' => '12x' . $contactid,
                        'payment_currency' => $payment_currency,
                        'payment_process' => $payment_process,
                        'payment_type' => $payment_type,
                        'payment_from' => $payment_from,
                        'payment_to' => $payment_to,
                        'failure_reason' => $requestBody['request']['comment'],
                    );
                    $record = '39x' . $record_id;
                    $recordRes = UpdateRecord($postData, $module, $record);
                    if ($recordRes['success'] == 1 && (!isset($recordRes['error']))) {
                        if ($recordRes['result']['record']['id'] == $record) {
                            $payment_response['message'] = "Failed : Callback Response. Payment failed from payment gateway side.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                            $log->debug($payment_response['message']);
                            echo "Payment Failed.";
                            exit;
                        } else {
                            $payment_response['message'] = "Error : Callback Response. Payment record not found.";
                            $payment_response['post_data'] = $postData;
                            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                            $log->debug($payment_response['message']);
                            exit;
                        }
                    } else {
                        $payment_response['message'] = $recordRes['error']['code'] . ' - ' . $recordRes['error']['message'];
                        $payment_response['post_data'] = $postData;
                        createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                        $log->debug($payment_response['message']);
                        exit;
                    }
                } else {
                    $payment_response['message'] = "Error : Callback Response. Payment is Pending.";
                    createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                    $log->debug($payment_response['message']);
                }
            } else {
                $payment_response['message'] = "Error : Callback Response. Payment record not found.";
                createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                $log->debug($payment_response['message']);
                exit;
            }
        } else {
            $payment_response['message'] = "Error : Callback Response. Payment record not found.";
            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
            $log->debug($payment_response['message']);
            exit;
        }
    }
    else if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'Rapyd')
    {
        $requestBody['json'] = json_decode(file_get_contents('php://input'), true);
        $requestBody['request'] = $_REQUEST;
        $payment_response = $requestBody;
        $paymentMethod = $_REQUEST['pm'];

        $order_id = $payment_response['json']['data']['merchant_reference_id'];
        $type = $payment_response['json']['type'];

        createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, 'Callback Response', $type);
        $log->debug($payment_response['message']);
        exit;
    }
    else if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'VaultsPay')
    {
        $requestBody['json'] = json_decode(file_get_contents('php://input'), true);
        $requestBody['request'] = $_REQUEST;
        $paymentMethod = $_REQUEST['pm'];
        $order_id = $_REQUEST['order_id'];
        createPaymentLog($order_id, $paymentMethod, $paymentMethod, $requestBody, 'VaultsPay Callback');
        exit;        
    } 
    else if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'Match2Pay')
    {
        $requestBody['json'] = json_decode(file_get_contents('php://input'), true);
        $requestBody['pm'] = $_REQUEST['pm'];
        $requestBody['order_id'] = $_REQUEST['order_id'];        
        $payment_response = $requestBody;        
        $paymentMethod = $_REQUEST['pm'];
        $order_id = $_REQUEST['order_id'];        
        $module = 'Payments';
        $portal_language = 'en-us';
        $status = $requestBody['json']['status'];
        $paymentId = $requestBody['json']['paymentId'];

        $getPaymentRecord = "SELECT vtiger_payments.*,vtiger_crmentity.smownerid FROM vtiger_payments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_payments.paymentsid WHERE vtiger_crmentity.deleted=0 AND vtiger_payments.payment_from = ? AND vtiger_payments.order_id = ? AND vtiger_payments.payment_status=?";
        $resultGetPayment = $adb->pquery($getPaymentRecord, array($paymentMethod, $order_id, 'Pending'));
        $num_rows = $adb->num_rows($resultGetPayment);

        if ($num_rows > 0) {
            $record_id = $adb->query_result($resultGetPayment, 0, 'paymentsid');
            if ($record_id) {
                $payment_response['record_exist'] = true;
                createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
            } else {
                $payment_response['message'] = "Error : Callback Response. Payment record not found.5";
                createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
                $log->debug($payment_response['message']);
                exit;
            }
        } else {
            $payment_response['message'] = "Error : Callback Response. Payment record not found.6";
            createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
            $log->debug($payment_response['message']);
            exit;
        }
    }
    else
    {
        $payment_response = $_REQUEST;
        $payment_response['message'] = "Error : Callback Response. Request body not found for payment.";
        createPaymentLog($_REQUEST['order_id'], $_REQUEST['pm'], $_REQUEST['pm'], $payment_response);
        $log->debug($payment_response['message']);
        exit;
    }

} else {
    $payment_response['message'] = "Error : Callback Response. Request body not found for payment.";
    createPaymentLog($_REQUEST['order_id'] = '', $_REQUEST['pm'], $_REQUEST['pm'], $payment_response);
    $log->debug($payment_response['message']);
    exit;
}

function createPaymentLog($order_id, $provider_type, $provider_title, $request, $callbackResponse = 'Callback Response', $status = 'Created') {
    global $adb;
    $req = json_encode($request);
    $date = date('Y-m-d h:i:s');
    $query = "INSERT INTO `vtiger_payment_logs` (`order_id`, `provider_type`, `provider_title`,`data`, `status`, `event`, `createdtime`) VALUES ('$order_id','$provider_type','$provider_title','$req','$status','$callbackResponse','$date')";
    $result = $adb->pquery($query, array());
    if ($result)
        return true;
    else
        return false;
}

function checkLogin() {
    global $accessKey, $site_URL, $frontform_username, $frontform_password;
    $url = $site_URL . 'modules/Mobile/api.php';
    $data = array(
        '_operation' => 'loginAndFetchModules',
        'username' => $frontform_username,
        'password' => $frontform_password,
    );
    $params = '';
    foreach ($data as $key => $value)
        $params .= $key . '=' . $value . '&';
    $params = trim($params, '&');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); //Remote Location URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Return data instead printing directly in Browser
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7); //Timeout after 7 seconds
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
    curl_setopt($ch, CURLOPT_HEADER, 0);

    //We add these 2 lines to create POST request
    curl_setopt($ch, CURLOPT_POST, count($data)); //number of parameters sent
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params); //parameters data

    $result = curl_exec($ch);
    $response = json_decode($result, true);
    return $response;
}

function UpdateRecord($postData, $moduleName, $record) {
    global $site_URL;
    $checkLogin = checkLogin();
    $sessionName = $checkLogin['result']['login']['session'];
    $url = $site_URL . 'modules/Mobile/api.php';
    $postData = json_encode($postData);
    $data = array('_operation' => 'saveRecord', 'record' => $record, '_session' => $sessionName, 'module' => $moduleName, 'values' => $postData);
    $params = '';
    foreach ($data as $key => $value)
        $params .= $key . '=' . $value . '&';
    $params = trim($params, '&');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); //Remote Location URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Return data instead printing directly in Browser
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7); //Timeout after 7 seconds
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
    curl_setopt($ch, CURLOPT_HEADER, 0);

    //We add these 2 lines to create POST request
    curl_setopt($ch, CURLOPT_POST, count($data)); //number of parameters sent
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params); //parameters data

    $result = curl_exec($ch);
    $response = json_decode($result, true);
    return $response;
}
