<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
include_once 'modules/ServiceProviders/PaymentProvidersHelper.php';

class ServiceProviders_VaultsPay_Provider extends ServiceProviders_AbstractPaymentGatways_Model
{

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file
    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'client_id', 'label' => 'Client ID', 'type' => 'text', 'mandatory' => true),
        array('name' => 'client_secret', 'label' => 'Client Secret', 'type' => 'text', 'mandatory' => true),
        array('name' => 'schema_code', 'label' => 'Schema Code', 'type' => 'text', 'mandatory' => true),
        array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        array('name' => 'test_url', 'label' => 'Test URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),
    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
        array('name' => 'vaultspay_payment_ref_id', 'label' => 'PAYMENT_REF_ID', 'type' => 'hidden'),
    );
    private static $WITHDRAW_FORM_PARAMETERS = array(
    );

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName()
    {
        return 'VaultsPay'; // don't take name with any space or special charctor
    }

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getRequiredParams()
    {
        return array_merge(self::$REQUIRED_PARAMETERS, self::DEFAULT_REQUIRED_PARAMETERS);
        //return self::$REQUIRED_PARAMETERS;
    }

    /**
     * Function to get deposit parameters
     * @return <array> required parameters list
     */
    public function getDepositFormParams()
    {
        return self::$DEPOSIT_FORM_PARAMETERS;
    }

    /**
     * Function to get withdrawal parameters
     * @return <array> required parameters list
     */
    public function getWithdrawFormParams()
    {
        return self::$WITHDRAW_FORM_PARAMETERS;
    }

    /**
     * Function to set non-auth parameter.
     * @param <String> $key
     * @param <String> $value
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Function to get parameter value
     * @param <String> $key
     * @param <String> $defaultValue
     * @return <String> value/$default value
     */
    public function getParameter($key, $defaultValue = false)
    {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }
        return $defaultValue;
    }

    /**
     * Function to prepare parameters
     * @return <Array> parameters
     */
    public function prepareParameters()
    {
        foreach (self::$REQUIRED_PARAMETERS as $key => $fieldInfo) {
            $params[$fieldInfo['name']] = $this->getParameter($fieldInfo['name']);
        }
        return $params;
    }

    public function paymentProcess($request, $portal_language)
    {
        global $PORTAL_URL, $site_URL;

        $order_id = PaymentProvidersHelper::generateUUID(); //Generated the unique order id from database

        if (!$order_id) {
            return array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_GENERATING_ORDER_ID', $this->module, $portal_language));
        }

        if (!empty($request)) {
            //Get response
            $returnUrl = $PORTAL_URL . "#/payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            $cancelUrl = $PORTAL_URL . "#/payments/fail?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";

            $returnUrl = $PORTAL_URL . "#/payments/paymentcallback?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";

            //For call back handling which hook form third part
            $callBackUrl = $site_URL . 'modules/CustomerPortal/thirdparty/payment_callback.php?pm=' . $request['payment_from'] . '&order_id=' . $order_id;

            if ($request['is_mobile_request']) {
                $returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
                $cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            }
            if ($request['payment_operation'] == 'Deposit') {
                session_start();

                $provider = ServiceProvidersManager::getActiveInstanceByProvider($request['payment_from']);
                $amount_value = number_format($request['net_amount'], $provider->parameters['allowed_decimal'], '.', '');
                
                $client_id = $provider->parameters['client_id'];
                $client_secret = $provider->parameters['client_secret'];
                $schemaCode = $provider->parameters['schema_code'];
                $test_url = $provider->parameters['test_url'];
                $base_url = $provider->parameters['base_url'];
                $description = $provider->parameters['description'];
                $display_currency = $request['payment_currency'];

                if ($provider->parameters['test_mode'] == 'Yes') {
                    $actionUrl = $test_url;
                } else {
                    $actionUrl = $base_url;
                }

                $createTokenParam = array('clientId' => $client_id, 'clientSecret' => $client_secret);
                $createTokenParam = json_encode($createTokenParam);
                $tokenUrl = $actionUrl . 'public/external/v1/merchant-auth';
                $output = $this->paymentAPI($tokenUrl, $createTokenParam, $accessToken = '');

                if ($output->code == 200 && $output->message == 'Successful.') {
                    $accessToken = $output->data->access_token;
                    $channelName = $output->data->stores[0]->channelName;
                        
                    $paymentInitializeParam = array(
                        'amount' => $amount_value,
                        'callBackUrl' => $callBackUrl,
                        'redirectUrl' => $returnUrl,
                        'expiryInSeconds' => '7200',
                        'channelName' => $channelName,
                        'schemaCode' => $schemaCode,
                        'clientReference' => $order_id
                    );
                    $paymentInitializeParam = json_encode($paymentInitializeParam);
                    $paymentInitializeUrl = $actionUrl . 'public/external/v1/initialize-merchant-payment';
                    $responseAPI = $this->paymentAPI($paymentInitializeUrl, $paymentInitializeParam, $accessToken);
                } else {
                    $errMessage = $output->message;
                    $res = array('success' => false, 'message' => $errMessage);
                }

                if ($responseAPI->code == 200 && $responseAPI->message == 'Successful.') {
                    $redirectUrl = $responseAPI->data->paymentUrl;
                    $paymentId = $responseAPI->data->paymentId;
                    $request['redirectUrl'] = $redirectUrl;
                    $request['token'] = $access_token;
                    $request['order_data'] = $responseAPI;
                    if (PaymentProvidersHelper::createPaymentLog($order_id, $this->getName(), $request['payment_from'], $request, 'Created', 'Order Creation')) {
                        $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Redirect', 'redirect_url' => $redirectUrl, 'order_id' => $order_id, 'order_data' => $responseAPI));
                    } else {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module, $portal_language));
                    }
                } else {
                    $res = array('success' => false, 'message' => $responseAPI->message);
                }
            } else if ($request['payment_operation'] == 'Withdrawal') {
                $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Manual', 'redirect_url' => $returnUrl, 'order_id' => $order_id, 'message' => 'Withdrawal request has been sent successfully'));
            } else {
                $res = array('success' => false, 'message' => vtranslate('CAB_MSG_PAYMENT_OPER_DOES_NOT_MATCH', $this->module, $portal_language));
            }
        } else {
            $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_REQUEST', $this->module, $portal_language));
        }
        return $res;
    }

    public function getVerifyTransferDetails($request, $FILES, $portal_language)
    {
        $paymentInput = array();
        if ($request['payment_operation'] == 'Deposit') {
            $paymentInput = $this->getDepositFormParams();
        } else if ($request['payment_operation'] == 'Withdrawal') {
            $paymentInput = $this->getWithdrawFormParams();
        } else {
            $res = array('success' => false, 'message' => vtranslate('CAB_MSG_PAYMENT_OPER_DOES_NOT_MATCH', $this->module, $portal_language));
        }
        $res = array('success' => true);
        if (!empty($paymentInput)) {
            foreach ($paymentInput as $field) {
                $name = $field['name'];
                if ($field['required'] && !array_key_exists($name, $request) && $field['type'] != "file") {
                    $res = array('success' => false, 'message' => $field['name'] . vtranslate('CAB_MSG_FIELD_IS_REQUIRED', $this->module, $portal_language));
                    break;
                }

                if ($field['required'] && (!array_key_exists($name, $request) && !array_key_exists($name, $FILES)) && $field['type'] == "file") {
                    $res = array('success' => false, 'message' => $field['name'] . vtranslate('CAB_MSG_FIELD_IS_REQUIRED', $this->module, $portal_language));
                    break;
                }
                if ($field['type'] == 'file' && array_key_exists($name, $FILES)) {
                    $file_type = explode(',', $field['allowed_type']); // array('JPEG', 'JPG', 'PNG', 'PDF');
                    if (!in_array(strtoupper(pathinfo($FILES[$name]['name'])['extension']), $file_type)) {
                        $res = array('success' => false, 'message' => pathinfo($FILES[$name]['name'])['extension'] . vtranslate('CAB_MSG_FILE_TYPE_DOES_NOT_ALLOWED', $this->module, $portal_language));
                        break;
                    } else if ($FILES[$name]['size'] <= 0 || $FILES[$name]['size'] > 5000000) {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_FILE_SIZE_SHOULD_NOT_BE_GREATER_THAN_MB', $this->translate_module, $portal_language));
                        break;
                    }
                }
            }
        }
        return $res;
    }

    //Verify the payment response and insert to payment log table
    public function paymentResponseVerification($status, $payment_response, $order_id, $portal_language)
    {
        global $adb;
        if (PaymentProvidersHelper::getPaymentRecord($order_id)) {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_response['pm']);
            if ($status == 'Pending') {

                $getPaymentRecord = "SELECT vtiger_payments.order_id,vtiger_payments.custom_data FROM vtiger_payments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_payments.paymentsid WHERE vtiger_crmentity.deleted=0 AND vtiger_payments.order_id = ? AND vtiger_payments.payment_status=?";
                $resultGetPayment = $adb->pquery($getPaymentRecord, array($order_id, 'Pending'));
                $num_rows = $adb->num_rows($resultGetPayment);

                if ($num_rows > 0) {
                    $order_id = $adb->query_result($resultGetPayment, 0, 'order_id');
                    $custom_data = $adb->query_result($resultGetPayment, 0, 'custom_data');
                    $custom_data = htmlspecialchars_decode($custom_data);
                    $custom_data = json_decode($custom_data);
                    $vaultsPay_PaymentID = $custom_data[0]->value;

                    $client_id = $provider->parameters['client_id'];
                    $client_secret = $provider->parameters['client_secret'];
                    $test_url = $provider->parameters['test_url'];
                    $base_url = $provider->parameters['base_url'];
                    if ($provider->parameters['test_mode'] == 'Yes') {
                        $actionUrl = $test_url;
                    } else {
                        $actionUrl = $base_url;
                    }
                    
                    $createTokenParam = array('clientId' => $client_id, 'clientSecret' => $client_secret);
                    $createTokenParam = json_encode($createTokenParam);
                    $tokenUrl = $actionUrl . 'public/external/v1/merchant-auth';
                    $output = $this->paymentAPI($tokenUrl, $createTokenParam, $accessToken = '');

                    if ($output->code == 200 && $output->message == 'Successful.') {
                        $accessToken = $output->data->access_token;
                        $checkStatusParam = array('transactionId' => $vaultsPay_PaymentID);
                        $checkStatusParam = json_encode($checkStatusParam);
                        $checkStatusUrl = $actionUrl . 'public/external/v1/get-transaction-details';
                        $output = $this->paymentAPI($checkStatusUrl, $checkStatusParam, $accessToken);
                        
                        if ($output->code == 200 && $output->message == 'Successful.') {
                            $payStatus = $output->data->transactionStatus;
                            if (strtolower($payStatus) == 'success') {
                                $payment_response['message'] = vtranslate('CAB_MSG_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $this->module, $portal_language);
                                $res = array('success' => true, 'payment_status' => 'Confirmed', 'message' => $payment_response['message']);
                            } else {
                                $status = 'Failed';
                                $payment_response['message'] = vtranslate('CAB_MSG_TRANSACTION_HAS_BEEN_FAILED', $this->module, $portal_language);
                                $res = array('success' => false, 'payment_status' => $status, 'message' => $payment_response['message']);
                            }
                        } else {
                            $status = 'Failed';
                            $payment_response['message'] = $output->message;
                            $res = array('success' => false, 'payment_status' => $status, 'message' => $payment_response['message']);
                        } 
                    } else {
                        $status = 'Failed';
                        $payment_response['message'] = $output->message;
                        $res = array('success' => false, 'payment_status' => $status, 'message' => $payment_response['message']);
                    }
                }
            }
            if ($status == 'Failed') {
                if (!isset($payment_response['message'])) {
                    $status = 'Cancelled';
                    $payment_response['message'] = vtranslate('CAB_MSG_CANCELLED_BY_USER', $this->module, $portal_language);
                }
                $res = array('success' => false, 'payment_status' => $status, 'message' => $payment_response['message']);
            }
            if (PaymentProvidersHelper::createPaymentLog($order_id, $this->getName(), $payment_response['pm'], $payment_response, $status, $payment_response['message'])) {

            }
        } else {
            $status == 'Failed';
            $res = array('success' => false, 'payment_status' => $status, 'message' => vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language));
        }
        return $res;
    }

    public function paymentAPI($actionUrl, $params, $token) {        
        if ($token != '') {
            $token = "accessToken: " . $token;
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $actionUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
                $token,
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($response);
        return $output;
    }


    public function generateRandomString($length = 10)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    public function getPendingRecordDurationQuery() {
        $duration = "15 MINUTE";
        $testMode = $this->getParameter('test_mode');
        if(strtolower($testMode) == 'yes')
        {
            $duration = "5 MINUTE";
        }
        $query = " AND vtiger_crmentity.createdtime BETWEEN DATE_SUB(CONVERT_TZ(NOW(), @@session.time_zone, '+00:00'),INTERVAL 1 HOUR) AND DATE_SUB(CONVERT_TZ(NOW(), @@session.time_zone, '+00:00'),INTERVAL $duration)";
        return $query;
    }

    public function getPendingRecordHandlerQuery() {
        $duration = "48 HOUR";
        $testMode = $this->getParameter('test_mode');
        if(strtolower($testMode) == 'yes')
        {
            $duration = "10 MINUTE";
        }
        $query = " AND vtiger_crmentity.createdtime <= DATE_SUB(NOW(),INTERVAL $duration)";
        return $query;
    }

    public function getPaymentCurrentStatus($paymentData = array())
    {
        global $adb,$log;
       $status = "";
        $paymentStatusResponse = $callbackData = array();
        $orderId = $paymentData['order_id'];
        if (!empty($orderId)) {
            $thirdPartyPayStatus = array('success' => 'success', 'failed' => 'failed');

            $paymentLogQuery = "SELECT data FROM vtiger_payment_logs WHERE order_id = ? AND event = ? ORDER BY id DESC LIMIT 0,1";
            $paymentLogQueryResult = $adb->pquery($paymentLogQuery, array($orderId, 'VaultsPay Callback'));
            $noOfRecordlog = $adb->num_rows($paymentLogQueryResult);
            if($noOfRecordlog > 0)
            {
                $callbackJsonData = $adb->query_result($paymentLogQueryResult, 0, 'data');
                $callbackJsonData = html_entity_decode($callbackJsonData);
                $callbackData = json_decode($callbackJsonData, true);
                $status = $callbackData['json']['status'];
            }
            else
            {
                $status = "failed";
            }
            $thirdPartyResponseStatus = strtolower($status);
            $paymentStatusResponse['data'] = $callbackData;
            $paymentStatusResponse['status'] = $thirdPartyPayStatus[$thirdPartyResponseStatus];
        }
        return $paymentStatusResponse;
    }
        
    public function paymentCallbackHandler($callbackHandlerData = array())
    {
        global $log;
        $log->debug('Entering into VaultsPay paymentCallbackHandler...');
        try
        {
            $callbackResponse = '';
            $callbackResponseJson = file_get_contents('php://input');$log->debug($callbackResponseJson);
            if(!empty($callbackResponseJson))
            {
                $callbackResponse = json_decode($callbackResponseJson, true);$log->debug($callbackResponse);
            }
            $callbackResponse['request'] = $_REQUEST;
            if(!empty($callbackResponse))
            {
                $callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['provider_name'], $callbackHandlerData['provider_title'], $callbackResponse, 'VaultsPay Callback');
                $ackOfCallback = json_encode(array('success' => true));
            }
            else
            {
                $ackOfCallback = json_encode(array('success' => false, 'msg' => 'Callback response blank!'));
            }
            return $ackOfCallback;
        }
        catch (Exception $e)
        {
            return json_encode(array('success' => false, 'msg' => $e->getMessage()));
        }
    }
}
