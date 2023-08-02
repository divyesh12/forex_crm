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
$callbackPayMethodList = array('B2binpay' ,'FairPay', 'VirtualPay', 'ZotaPay', 'Match2Pay', 'VirtualPayMobPay', 'Help2Pay', 'NowPay');
$payMethodListWithFields = array(
    'B2binpay' => array('status' => 'status', 'order_id' => 'tracking_id', 'pm' => 'pm'),
    'FairPay' => array('status' => 'status', 'order_id' => 'order_id', 'pm' => 'pm'),
    'VirtualPay' => array('status' => 'status', 'order_id' => 'order_id', 'pm' => 'pm'),
    'ZotaPay' => array('status' => 'status', 'order_id' => 'order_id', 'pm' => 'pm'),
    'Match2Pay' => array('status' => 'status', 'order_id' => 'order_id', 'pm' => 'pm'),
    'VirtualPayMobPay' => array('status' => 'status', 'order_id' => 'order_id', 'pm' => 'pm'),
    'Help2Pay' => array('status' => 'status', 'order_id' => 'order_id', 'pm' => 'pm'),
    'NowPay' => array('status' => 'status', 'order_id' => 'order_id', 'pm' => 'pm')
    );
$onlyCallbackStorePayMethodList = array();
$module = 'Payments';

if (isset($_REQUEST) && !empty($_REQUEST))
{
    $serviceTypeList = ServiceProvidersManager::getActiveProviderList();
    $paymentTypeTitle = isset($_REQUEST['pm']) && !empty($_REQUEST['pm']) ? $_REQUEST['pm'] : '';
    if(in_array($paymentTypeTitle, $serviceTypeList) && in_array($paymentTypeTitle, $callbackPayMethodList))
    {
        $provider = ServiceProvidersManager::getActiveInstanceByProvider($paymentTypeTitle);
        if(!empty($provider))
        {
            $ackOfCallback = false;
            $callbackHandlerData = array();
            $paymentResponse = $_REQUEST;
            $portalLanguage = 'en-us';
            $orderIdFieldName = $payMethodListWithFields[$paymentTypeTitle]['order_id'];
            $statusFieldName = $payMethodListWithFields[$paymentTypeTitle]['status'];
            $paymentMethodFieldName = $payMethodListWithFields[$paymentTypeTitle]['pm'];
            $paymentProviderName = $provider->getName();

            $callbackHandlerData['provider_title'] = $paymentTitle = isset($_REQUEST[$paymentMethodFieldName]) && !empty($_REQUEST[$paymentMethodFieldName]) ? $_REQUEST[$paymentMethodFieldName] : '';
            $callbackHandlerData['order_id'] = $orderId = isset($_REQUEST[$orderIdFieldName]) && !empty($_REQUEST[$orderIdFieldName]) ? $_REQUEST[$orderIdFieldName] : '';
            $callbackHandlerData['status'] = $status = isset($_REQUEST[$statusFieldName]) && !empty($_REQUEST[$statusFieldName]) ? $_REQUEST[$statusFieldName] : '';
            $callbackHandlerData['provider_name'] = !empty($paymentProviderName) ? $paymentProviderName : $paymentTitle;
            
            $getPaymentRecord = "SELECT vtiger_payments.*,vtiger_crmentity.smownerid FROM vtiger_payments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_payments.paymentsid WHERE vtiger_crmentity.deleted=0 AND vtiger_payments.payment_from = ? AND vtiger_payments.order_id = ? AND vtiger_payments.payment_status=?";
            $resultGetPayment = $adb->pquery($getPaymentRecord, array($paymentTitle, $orderId, 'Pending'));
            $num_rows = $adb->num_rows($resultGetPayment);
            if ($num_rows > 0)
            {
                $callbackHandlerData['record_id'] = $adb->query_result($resultGetPayment, 0, 'paymentsid');
                $ackOfCallback = $provider->paymentCallbackHandler($callbackHandlerData);
            }
            else
            {
                $log->debug('Payment record not found-' . $orderId);
            }
            return $ackOfCallback;
        }
        else
        {
            $log->debug('Provider is inactive or unavailable.');
        }
    }
    else if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'VirtualPay')
    {
        $requestBody['json'] = json_decode(file_get_contents('php://input'), true);
        $requestBody['request'] = $_REQUEST;
        $payment_response = $requestBody;
        $paymentMethod = $_REQUEST['pm'];
        $order_id = $_REQUEST['order_id'];
        createPaymentLog($order_id, $paymentMethod, $paymentMethod, $payment_response, 'Created', 'VirtualPay Callback Response');
        $log->debug($payment_response['message']);
        exit;
    }
    else
    {
        $log->debug('Provider is not registered for callback.');
    }
}
else
{
    $paymentResponse['message'] = "Error : Callback Response. Request body not found for payment.";
    createPaymentLog('', $paymentTypeTitle, $paymentTypeTitle, $paymentResponse);
    $log->debug($paymentResponse['message']);
    exit;
}