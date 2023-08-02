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

class ServiceProviders_FasaPay_Provider extends ServiceProviders_AbstractPaymentGatways_Model {

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file
    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'fp_acc', 'label' => 'FasaPay Account', 'type' => 'text', 'mandatory' => true),
        array('name' => 'store_name', 'label' => 'Store Name', 'type' => 'text', 'mandatory' => true),
        array('name' => 'security_word', 'label' => 'Security Word', 'type' => 'text', 'mandatory' => true),
        array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        array('name' => 'test_url', 'label' => 'Test URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),
    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
        array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'required' => true, 'mandatory' => true),
        array('name' => 'from_account', 'label' => 'CAB_LBL_FROM_ACCOUNT', 'type' => 'text', 'required' => true, 'mandatory' => true),
    );
    private static $WITHDRAW_FORM_PARAMETERS = array(
        array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'required' => true, 'mandatory' => true)
    );

    private static $FASAPAY_IP_ADDRESS = array('139.162.19.188', '139.162.53.190', '2400:8901::f03c:92ff:fe3e:6458', '2400:8901::f03c:92ff:fe7b:a89e');
    
    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName() {
        return 'FasaPay'; // don't take name with any space or special charctor
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
//            $returnUrl = $PORTAL_URL . "#/payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";            
//            $cancelUrl = $PORTAL_URL . "#/payments/fail?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            $returnUrl = $PORTAL_URL . "payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";            
            $cancelUrl = $PORTAL_URL . "payments/fail?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";

            //For call back handling which hook form third part
            $callBackUrl = $site_URL . 'modules/CustomerPortal/thirdparty/payment_callback.php?pm=' . $request['payment_from'] . '&order_id=' . $order_id;
            
            if ($request['is_mobile_request']) {
                $returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
                $cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            }
            if ($request['payment_operation'] == 'Deposit') {
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($request['payment_from']);
                $amount_value = number_format($request['net_amount'], $provider->parameters['allowed_decimal'], '.', '');

                $storeName = $provider->parameters['store_name'];
                $fp_acc = $provider->parameters['fp_acc'];
                $test_url = $provider->parameters['test_url'];
                $base_url = $provider->parameters['base_url'];
                $description = $provider->parameters['description'];

                if ($provider->parameters['test_mode'] == 'Yes') {
                    $fasa_action_url = $test_url;
                } else {
                    $fasa_action_url = $base_url;
                }
                $fasaPayFormParam = array(
                    'fp_acc' => $fp_acc,
                    'fp_acc_from' => $request['from_account'],
                    'fp_store' => $storeName,
                    'fp_item' => 'Deposit',
                    'fp_amnt' => $amount_value,
                    'fp_currency' => $request['payment_currency'],
                    'fp_fee_mode' => 'FiS',
                    'fp_comments' => $description,
                    'fp_success_url' => $returnUrl, //'http://forexcabinetqa.iconflux.info/#/payments/success?orid=2c4245d3-0fb1-11eb-8884-08002792d5c0&pm=FasaPay',
                    'fp_success_method' => 'GET',
                    'fp_fail_url' => $cancelUrl, //'http://forexcabinetqa.iconflux.info/#/payments/fail?orid=9a64a9ca-0fb3-11eb-943f-141877a7c07b&pm=FasaPay',//$cancelUrl,
                    'fp_fail_method' => 'GET',
                    'fp_status_url' => $callBackUrl, //'http://forexcabinetqa.iconflux.info/#/payments/success?orid=2c4245d3-0fb1-11eb-8884-08002792d5c0&pm=FasaPay', //$returnUrl,
                    'fp_status_method' => 'GET',
                    'track_id' => $order_id,
                    'orid' => $order_id,
                    'fp_sci_link' => 'TRUE',
                );
                $param = '';
                foreach ($fasaPayFormParam as $key => $value) {
                    $param .= $key . '=' . $value . '&';
                }
                $param = trim($param, '&');
                $headers = [
                    "content-type: application/x-www-form-urlencoded",
                ];

                $secureJsonData = $this->sendCurlRequest($fasa_action_url, 'POST', '', $headers, $param);
                $redirectUrl = isset($secureJsonData['fp_sci_link']) && !empty($secureJsonData['fp_sci_link']) ? $secureJsonData['fp_sci_link'] : '';
                if ($redirectUrl) {
                    if (PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Created', 'Form Generation')) {
                        $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Redirect', 'redirect_url' => $redirectUrl, 'order_id' => $order_id));
                    } else {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module));
                    }
                } else {
                    $request['error_message'] = $response->getMessage();
                    if (!PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Created', 'Token Creaton')) {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module));
                    } else {
                        $res = array('success' => false, 'message' => $request['error_message']);
                    }
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
        
        if (PaymentProvidersHelper::getPaymentRecord($order_id))
        {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_response['pm']);
            $storeName = $provider->parameters['store_name'];
            $fpAccount = $provider->parameters['fp_acc'];
            $securityWord = $provider->parameters['security_word'];
                    
            $fpBatchNumber = $payment_response['fp_batchnumber'];
            
            if(!empty($fpBatchNumber))
            {
                $paymentStatus = true;
            }
        }
        else
        {
            $paymentStatus = false;
            $errorMsg = vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language);
        }
            /*Response handling*/
        if($paymentStatus)
        {
            $msg = vtranslate('CAB_MSG_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $this->module, $portal_language);
            
            $res = array('success' => true, 'payment_status' => 'Confirmed', 'message' => $msg );
            $logStatus = "Success";
        }
        else
        {
            $status = "Failed";
            $payment_response['errorMessage'] = vtranslate('CAB_MSG_CANCELLED_BY_USER', $this->module);
            $errorMsg = isset($payment_response['errorMessage']) && !empty($payment_response['errorMessage']) ? $payment_response['errorMessage'] : 'Error while payment processing!';
            $res = array('success' => true, 'payment_status' => $status, 'message' => $errorMsg);
        }
        /*Create log*/
        PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $payment_response['pm'], $logResponse, $logStatus, "Fasapay callback");
        return $res;
    }

    public function sendCurlRequest(string $url, string $method, string $path, array $headers, $params)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            //CURLOPT_URL => "https://" . $node . ".b2binpay.com/" . $path,
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

    public function paymentCallbackHandler($callbackHandlerData = array())
    {
        global $log,$adb;
        $log->debug('Entering into fasapay paymentCallbackHandler...');
        try
        {
            $callbackResponse = $paymentStatus = '';
            $callbackResponseJson = file_get_contents('php://input');$log->debug($callbackResponseJson);
            if(!empty($callbackResponseJson))
            {
                $callbackResponse = json_decode($callbackResponseJson, true);$log->debug($callbackResponse);
            }
            if(!empty($callbackResponse))
            {
                $securityWord = $this->getParameter('security_word');
                /*Cross check hash*/
                $fpHashAllFromResponse = $callbackResponse['fp_hash_all'];
                $fpHashList = $callbackResponse['fp_hash_list'];
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
                        $stringToHash .= $callbackResponse[$hashField] . '|';
                    }
                }
                $stringToHashAll = trim($stringToHash, '|');
                $fpHashAll = hash('sha256', $stringToHashAll);$log->debug('$fpHashAll='.$fpHashAll);

                if($fpHashAll == $fpHashAllFromResponse)
                {$log->debug('FP Hash matched');
                    $getPaymentRecord = "SELECT vtiger_payments.*,vtiger_crmentity.smownerid FROM vtiger_payments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_payments.paymentsid WHERE vtiger_crmentity.deleted=0 AND vtiger_payments.payment_from = ? AND vtiger_payments.order_id = ?";
                    $resultGetPayment = $adb->pquery($getPaymentRecord, array($callbackHandlerData['provider_title'], $callbackHandlerData['order_id']));
                    $num_rows = $adb->num_rows($resultGetPayment);

                    if ($num_rows > 0)
                    {
                        $paymentStatus = "Success";
                        $callbackResponseMsg = 'Fasapay Status Form Callback';
                        $callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['provider_name'], $callbackHandlerData['provider_title'], $callbackResponseMsg, $paymentStatus);
                        $log->debug('Fasapay Status Form Callback stored into database');
                    }
                    else
                    {
                        $log->debug('Fasapay Status Form Callback not stored into database due to record not found in payment module!');
                    }
                }
                else
                {
                    $log->debug('Fasapay Status Form Callback not stored into database due to hash not matched!');
                }
                /*Cross check hash*/
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