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
global $log;


$requestBody = json_decode(file_get_contents('php://input'), true);

if (isset($_REQUEST) && !empty($_REQUEST)) {
	if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'FairPay') {        
		$requestBody['pm'] = $_REQUEST['pm'];
		$requestBody['order_id'] = $_REQUEST['order_id'];
	    $payment_response = $requestBody;
	    $paymentMethod = $_REQUEST['pm'];
	    $module = 'Payments';
	    $order_id = $_REQUEST['order_id'];
	    $portal_language = 'en-us';
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
	                        $payment_response['message'] = "Error : Callback Response. Payment record not found.1";
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
	            } else if ($status == 'FAILED') {
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
	                        createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
	                        $log->debug($payment_response['message']);
	                        echo "Payment Failed.";
	                        exit;
	                    } else {
	                        $payment_response['message'] = "Error : Callback Response. Payment record not found.2";
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
	                        $payment_response['message'] = "Error : Callback Response. Payment record not found.3";
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
	            } else if ($status == 'EXPIRED') {
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
	                        $payment_response['message'] = "Failed : Callback Response. Payment Expired from payment gateway side.";
	                        $payment_response['post_data'] = $postData;
	                        createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response);
	                        $log->debug($payment_response['message']);
	                        echo "Payment Failed.";
	                        exit;
	                    } else {
	                        $payment_response['message'] = "Error : Callback Response. Payment record not found.4";
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
} else {
    $payment_response['message'] = "Error : Callback Response. Request body not found for payment.";
    createPaymentLog($order_id = '', $_REQUEST['pm'], $_REQUEST['pm'], $payment_response);
    $log->debug($payment_response['message']);
    exit;
}

$payment_response['data'] = $_REQUEST;
$payment_response['message'] = "Log : Request body record";
createPaymentLog($order_id = '', $_REQUEST['pm'], $_REQUEST['pm'], $payment_response);
$log->debug($payment_response['message']);
exit;


function createPaymentLog($order_id, $provider_type, $provider_title, $request) {
    global $adb;
    $req = json_encode($request);
    $date = date('Y-m-d h:i:s');
    $query = "INSERT INTO `vtiger_payment_logs` (`order_id`, `provider_type`, `provider_title`,`data`, `status`, `event`, `createdtime`) VALUES ('$order_id','$provider_type','$provider_title','$req','Created','Callback Response','$date')";
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
