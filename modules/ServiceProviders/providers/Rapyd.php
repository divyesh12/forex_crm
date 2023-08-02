<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
include_once('modules/ServiceProviders/PaymentProvidersHelper.php');

class ServiceProviders_Rapyd_Provider extends ServiceProviders_AbstractPaymentGatways_Model {

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file
    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'secret_key', 'label' => 'Secret Key', 'type' => 'text', 'mandatory' => true),
        array('name' => 'access_key', 'label' => 'Access Key', 'type' => 'text', 'mandatory' => true),
        array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        array('name' => 'test_url', 'label' => 'Test URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),
    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
        array('name' => 'country', 'label' => 'CAB_LBL_COUNTRY', 'type' => 'dropdown_depended', 'picklist' => array(
            array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
            array("isAllow" => "true", "value" => "SG", "label" => "SINGAPORE"),
            array("isAllow" => "true", "value" => "GB", "label" => "UNITED KINGDOM"),
            array("isAllow" => "true", "value" => "US", "label" => "UNITED STATES"),
            array("isAllow" => "true", "value" => "VN", "label" => "VIETNAM"),
        ), 'required' => true, 'dependency' => 'paytechno_payment_method'),
        array('name' => 'bank_currency', 'label' => 'CAB_LBL_CURRENCY', 'type' => 'dropdown_depended', 'picklist' => array(
            array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
            array("isAllow" => "true", "value" => "SGD", "label" => "SGD"),
            array("isAllow" => "true", "value" => "GBP", "label" => "GBP"),
            array("isAllow" => "true", "value" => "USD", "label" => "USD"),
            array("isAllow" => "true", "value" => "VND", "label" => "VND"),
        )),
    );
    private static $WITHDRAW_FORM_PARAMETERS = array(
        array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'required' => true, 'mandatory' => true)
    );

   
    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName() {
        return 'Rapyd'; // don't take name with any space or special charctor
    }

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getRequiredParams() {
        return array_merge(self::$REQUIRED_PARAMETERS, self::DEFAULT_REQUIRED_PARAMETERS);
        //return self::$REQUIRED_PARAMETERS;
    }

    /**
     * Function to get deposit parameters
     * @return <array> required parameters list
     */
    public function getDepositFormParams() {
        return self::$DEPOSIT_FORM_PARAMETERS;
    }

    /**
     * Function to get withdrawal parameters
     * @return <array> required parameters list
     */
    public function getWithdrawFormParams() {
        return self::$WITHDRAW_FORM_PARAMETERS;
    }

    /**
     * Function to set non-auth parameter.
     * @param <String> $key
     * @param <String> $value
     */
    public function setParameter($key, $value) {
        $this->parameters[$key] = $value;
    }

    /**
     * Function to get parameter value
     * @param <String> $key
     * @param <String> $defaultValue
     * @return <String> value/$default value
     */
    public function getParameter($key, $defaultValue = false) {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }
        return $defaultValue;
    }

    /**
     * Function to prepare parameters
     * @return <Array> parameters
     */
    public function prepareParameters() {
        foreach (self::$REQUIRED_PARAMETERS as $key => $fieldInfo) {
            $params[$fieldInfo['name']] = $this->getParameter($fieldInfo['name']);
        }
        return $params;
    }

    public function paymentProcess($request, $portal_language) {
        global $PORTAL_URL, $site_URL,$log;

        $order_id = PaymentProvidersHelper::generateUUID(); //Generated the unique order id from database

        if (!$order_id) {
            return array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_GENERATING_ORDER_ID', $this->module, $portal_language));
        }

        if (!empty($request)) {
            //Get response
            // $returnUrl = $PORTAL_URL . "#/payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";            
            // $cancelUrl = $PORTAL_URL . "#/payments/fail?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";

            $returnUrl = $PORTAL_URL . "payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";            
            $cancelUrl = $PORTAL_URL . "payments/fail?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";

            //For call back handling which hook form third part
            $callBackUrl = $site_URL . 'modules/CustomerPortal/thirdparty/payment_callback.php?pm=' . $request['payment_from'] . '&order_id=' . $order_id;
            
            if ($request['is_mobile_request']) {
                $returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?restatus=success&orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
                $cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?restatus=fail&orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            }
            if ($request['payment_operation'] == 'Deposit') {
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($request['payment_from']);
                $amount_value = number_format($request['net_amount'], $provider->parameters['allowed_decimal'], '.', '');

                $secretKey = $provider->parameters['secret_key'];
                $accessKey = $provider->parameters['access_key'];
                $test_url = $provider->parameters['test_url'];
                $base_url = $provider->parameters['base_url'];
                $description = $provider->parameters['description'];
                $display_currency =  $request['payment_currency'];
                $country = $request['country'];
                $bankCurrency = $request['bank_currency'];
                
                if ($provider->parameters['test_mode'] == 'Yes') {
                    $action_url = $test_url;
                } else {
                    $action_url = $base_url;
                }

                if (strtolower($bank_currency) == 'usd') {
                    $bank_currency = '';
                }

                $body = [
                    "amount" => $amount_value,
                    "complete_checkout_url" => $returnUrl,
                    "cancel_checkout_url" => $cancelUrl,
                    "country" => $country,
                    "currency" => $display_currency,
                    "requested_currency" => $bankCurrency,
                    "merchant_reference_id" => $order_id,
                ];

                $method = 'post';
                $path = '/v1/checkout';
                $salt = $this->generate_string();
                $timestamp = time();
                
                $bodyString = !is_null($body) ? json_encode($body, JSON_UNESCAPED_SLASHES) : '';
                $sigString = "$method$path$salt$timestamp$accessKey$secretKey$bodyString";
                $hashSigString = hash_hmac("sha256", $sigString, $secretKey);
                $signature = base64_encode($hashSigString);

                $headers = [
                    "content-type: application/json",
                    "access_key: " . $accessKey,
                    "salt: " . $salt,
                    "timestamp: " . $timestamp,
                    "signature: " . $signature,
                ];

                $secureJsonData = $this->sendCurlRequest($action_url, $path, 'POST', $headers, $bodyString);
                $responseStatus = $secureJsonData['status']['status'];

                if (strtolower($responseStatus) == 'success') {
                    $redirectUrl = $secureJsonData['data']['redirect_url'];
                    if (PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Created', 'Form Generation')) {
                        $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Redirect', 'redirect_url' => $redirectUrl, 'order_id' => $order_id));
                    } else {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module));
                    }
                } else if (strtolower($responseStatus) == 'error') {
                    $request['error_message'] = $secureJsonData['status']['message'];
                    if (!PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Created', 'Token Creaton')) {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module));
                    } else {
                        $res = array('success' => false, 'message' => $request['error_message']);
                    }
                } else {
                    $res = array('success' => false, 'message' => $secureJsonData['status']['message']);
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

    public function getVerifyTransferDetails($request, $FILES, $portal_language) {
        $paymentInput = array();
        if ($request['payment_operation'] == 'Deposit') {
            $paymentInput = self::getDepositFormParams();
        } else if ($request['payment_operation'] == 'Withdrawal') {
            $paymentInput = self::getWithdrawFormParams();
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
    public function paymentResponseVerification($status, $payment_response, $order_id, $portal_language) {
        global $log;
        $paymentStatus = false;
        $logStatus = "Failed";
        $logResponse = array("request" => $payment_response, "response" => array("log_message" => ""));
        $errorMsg = "";
      
        if (PaymentProvidersHelper::getPaymentRecord($order_id)) {
            if (strtolower($status) == 'success') {
                $paymentStatus = true;
            }
        } else {
            $paymentStatus = false;
            $errorMsg = vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language);
        }
            /*Response handling*/
        if ($paymentStatus) {
            $msg = vtranslate('CAB_MSG_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $this->module, $portal_language);
            
            $res = array('success' => true, 'payment_status' => 'Confirmed', 'message' => $msg );
            $logStatus = "Success";
        } else {
            $status = "Failed";
            $payment_response['errorMessage'] = vtranslate('CAB_MSG_CANCELLED_BY_USER', $this->module);
            $errorMsg = isset($payment_response['errorMessage']) && !empty($payment_response['errorMessage']) ? $payment_response['errorMessage'] : 'Error while payment processing!';
            $res = array('success' => true, 'payment_status' => $status, 'message' => $errorMsg);
        }
        /*Create log*/
        PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $payment_response['pm'], $logResponse, $logStatus, "Rapyd Redirect Response");
        return $res;
    }

    public function sendCurlRequest(string $url, string $path, string $method, array $headers, $params)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url . $path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($ch);

        curl_close($ch);

        return (false === $response) ? false : json_decode($response, true);
    }

    public function generate_string($length = 12) {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($permitted_chars), 0, $length);
    }

    public function getPendingRecordDurationQuery() {
        $duration = "15 MINUTE";
        $testMode = $this->getParameter('test_mode');
        if(strtolower($testMode) == 'yes')
        {
            $duration = "5 MINUTE";
        }
        $query = " AND vtiger_crmentity.createdtime < DATE_SUB(NOW(),INTERVAL $duration)";
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
        global $adb, $log;
        $status = "";
        $paymentStatusResponse = array();
        $orderId = $paymentData['order_id'];
        if (!empty($orderId)) {
            $thirdPartyPayStatus = array('clo' => 'success', 'err' => 'failed', 'exp' => 'failed');            
            $getPaymentCallbackRecord = "SELECT data FROM vtiger_payment_logs WHERE order_id = ? AND provider_type = ? AND  event = ? ORDER BY id DESC";
            $resultGetPaymentCallback = $adb->pquery($getPaymentCallbackRecord, array($orderId, $paymentData['payment_from'], 'Callback Response'));
            $noOfPendingRecord = $adb->num_rows($resultGetPaymentCallback);              
            if ($noOfPendingRecord > 0) {
                  for ($i = 0; $i < $noOfPendingRecord; $i++) {
                        $callbackJsonData = $adb->query_result($resultGetPaymentCallback, $i, 'data');
                        $callbackJsonData = html_entity_decode($callbackJsonData);
                        $callbackData = json_decode($callbackJsonData, true);

                        $order_id = $callbackData['json']['data']['merchant_reference_id'];
                        $type = $callbackData['json']['type'];
                        $status = $callbackData['json']['data']['status'];
                        $paymentStatusResponse['data'] = $callbackData;

                        if ($type != 'PAYMENT_SUCCEEDED') {
                              if ($type == 'PAYMENT_COMPLETED' && $status == 'CLO') {
                                    $paymentStatusResponse['status'] = $thirdPartyPayStatus[strtolower($status)];
                                    return $paymentStatusResponse;
                              } else if ($type == 'PAYMENT_FAILED' && $status == 'ERR') {
                                    $paymentStatusResponse['status'] = $thirdPartyPayStatus[strtolower($status)];
                                    return $paymentStatusResponse;
                              } else if ($type == 'PAYMENT_EXPIRED' && $status == 'EXP') {
                                    $paymentStatusResponse['status'] = $thirdPartyPayStatus[strtolower($status)];
                                    return $paymentStatusResponse;
                              } else {
                                    $paymentStatusResponse['status'] = 'pending';
                                    return $paymentStatusResponse;
                              }
                        } 
                  }
            }
        }
        return $paymentStatusResponse;
    }

    public function paymentCallbackHandler($callbackHandlerData = array())
    {
        global $log;
        $log->debug('Entering into Rapyd paymentCallbackHandler...');
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
                $type = $payment_response['json']['type'];
                $callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['provider_name'], $callbackHandlerData['provider_title'], $callbackResponse, $type);
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

?>