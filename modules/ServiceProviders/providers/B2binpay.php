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

class ServiceProviders_B2binpay_Provider extends ServiceProviders_AbstractPaymentGatways_Model
{

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file
    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'b2binpay_key', 'label' => 'B2binpay Key', 'type' => 'text', 'mandatory' => true),
        array('name' => 'b2binpay_secret', 'label' => 'B2binpay Secret', 'type' => 'text', 'mandatory' => true),
        array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        array('name' => 'test_url', 'label' => 'Test URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'parent_wallet', 'label' => 'Parent Wallet ID', 'type' => 'text'),
        array('name' => 'wallet', 'label' => 'Wallet ID', 'type' => 'text', 'required' => true, 'mandatory' => true),
    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
        // array('name' => 'amount', 'label' => 'CAB_LBL_AMOUNT', 'type' => 'text', 'required' => true),
        array('name' => 'crypto_currency', 'label' => 'CAB_LBL_CRYPTOCURRENCY', 'type' => 'dropdown_depended', 'picklist' => array(
            array("isAllow" => "true", "value" => "", "label" => "Select Crypto Currency"),
            array("isAllow" => "true", "value" => "1000", "label" => "BTC"),
            array("isAllow" => "true", "value" => "1002", "label" => "ETH"),
            array("isAllow" => "true", "value" => "2015", "label" => "USDT"),
            array("isAllow" => "true", "value" => "1125", "label" => "BNB"),
            array("isAllow" => "true", "value" => "1019", "label" => "DOGE"),
            array("isAllow" => "true", "value" => "2126", "label" => "SNX"),
            array("isAllow" => "true", "value" => "1026", "label" => "TRX"),
            array("isAllow" => "true", "value" => "1005", "label" => "DASH"),
            array("isAllow" => "true", "value" => "1010", "label" => "XRP"),
            array("isAllow" => "true", "value" => "1021", "label" => "XLM"),
            array("isAllow" => "true", "value" => "1003", "label" => "LTC"),
        ), 'required' => true, 'dependency' => 'paytechno_payment_method'),
    );
    private static $WITHDRAW_FORM_PARAMETERS = array(
        //array('name' => 'wallet', 'label' => 'CAB_LBL_WALLET', 'type' => 'text', 'required' => true),
    );

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName()
    {
        return 'B2binpay'; // don't take name with any space or special charctor
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

        global $PORTAL_URL, $site_URL,$log;

        $order_id = PaymentProvidersHelper::generateUUID(); //Generated the unique order id from database

        if (!$order_id) {
            return array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_GENERATING_ORDER_ID', $this->module, $portal_language));
        }

        if (!empty($request)) {
            //Get response
            $returnUrl = $PORTAL_URL . "#/payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            $cancelUrl = $PORTAL_URL . "#/payments/fail?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";

            //For call back handling which hook form third part
            $callBackUrl = $site_URL . 'modules/CustomerPortal/thirdparty/payment_callback.php?pm=' . $request['payment_from'] . '&order_id=' . $order_id;

            if ($request['is_mobile_request']) {
                $returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
                $cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            }
            if ($request['payment_operation'] == 'Deposit') {
                session_start();
                $responseAPI = array();

                $provider = ServiceProvidersManager::getActiveInstanceByProvider($request['payment_from']);
                $amount_value = number_format($request['net_amount'], $provider->parameters['allowed_decimal'], '.', '');
                $log->debug('$amount_value=');
                $log->debug($amount_value);
                $b2binpay_key = $provider->parameters['b2binpay_key'];
                $b2binpay_secret = $provider->parameters['b2binpay_secret'];
                $test_url = $provider->parameters['test_url'];
                $base_url = $provider->parameters['base_url'];
//                $parent_wallet = $provider->parameters['parent_wallet'];
                $walletId = $provider->parameters['wallet'];

                // $auth = base64_encode($b2binpay_key . ':' . $b2binpay_secret);

                if ($provider->parameters['test_mode'] == 'Yes') {
                    $b2binpay_action_url = $test_url;
                } else {
                    $b2binpay_action_url = $base_url;
                }

                $params = array(
                    'data' => array(
                        'type' => 'auth-token',
                        'attributes' => array(
                            'login' => $b2binpay_key,
                            'password' => $b2binpay_secret
                        ),
                    ),
                );
                $params = json_encode($params);
                $headers = [
                    'Content-Type: application/vnd.api+json'
                ];
                $tokenPath = 'token/';
                $method = 'POST';
                $responseAPI = $this->sendCurlRequest($b2binpay_action_url, $tokenPath, $method, $headers, $params);

                if ($responseAPI['errors']) {
                    $errorMsg = 'Token Error';
                    if ($responseAPI['errors']['detail'] && !empty($responseAPI['errors']['detail'])) {
                        $errorMsg = $responseAPI['errors']['detail'];
                    } 
                    $res = array('success' => false, 'message' => $errorMsg);
                } else {
                    $accessToken = $responseAPI['data']['attributes']['access'];

                    $invoiceParams = array(
                        'data' => array(
                            'type' => 'deposit',
                            'attributes' => array(
                                'label' => 'Deposit',
                                'tracking_id' => $order_id,
                                'confirmations_needed' => '1',
                                'callback_url' => $callBackUrl,
                                'time_limit' => 900,
                                // 'inaccuracy' => 5,
                                'target_amount_requested' => $amount_value,
                            ),
                            'relationships' => array(
                                'currency' => array(
                                    'data' => array(
                                        'type' => 'currency',
                                        'id' => $request['crypto_currency'],
                                    ),
                                ),
                                'wallet' => array(
                                    'data' => array(
                                        'type' => 'wallet',
                                        'id' => $walletId,
                                    ),
                                ),
                            ),
                        ),
                    );
                    $invoiceParams = json_encode($invoiceParams);
                    $headers = [
                        'authorization: Bearer ' . $accessToken, 
                        'Content-Type: application/vnd.api+json'
                    ];
                    $tokenPath = 'deposit/';
                    $method = 'POST';
                    $responseAPI = $this->sendCurlRequest($b2binpay_action_url, $tokenPath, $method, $headers, $invoiceParams);
                }

                if ($responseAPI['data']) {
                    $redirectUrl = $responseAPI['data']['attributes']['payment_page'];
                    $request['redirectUrl'] = $redirectUrl;
                    $request['b2binpay_response'] = $responseAPI['data'];
                    if (PaymentProvidersHelper::createPaymentLog($order_id, $this->getName(), $request['payment_from'], $request, 'Created', 'Bill Creation')) {
                        $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Redirect', 'redirect_url' => $redirectUrl, 'order_id' => $order_id));
                    } else {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module, $portal_language));
                    }
                } else if ($responseAPI['errors']) {
                    $request['responseAPI'] = $responseAPI;
                    $errMsg = $responseAPI['errors'][0]['detail'];
                    $res = array('success' => false, 'message' => $errMsg);
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
        if (PaymentProvidersHelper::getPaymentRecord($order_id)) {
            if ($status == 'Success') {
                $payment_response['message'] = vtranslate('CAB_MSG_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $this->module, $portal_language);
                $res = array('success' => true, 'payment_status' => 'Confirmed', 'message' => $payment_response['message']);
            }
            if ($status == 'Failed') {
                if (!isset($payment_response['message'])) {
                    $status = 'Cancelled';
                    $payment_response['message'] = vtranslate('CAB_MSG_CANCELLED_BY_USER', $this->module, $portal_language);
                }
                $res = array('success' => true, 'payment_status' => $status, 'message' => $payment_response['message']);
            }
            if (PaymentProvidersHelper::createPaymentLog($order_id, $this->getName(), $payment_response['pm'], $payment_response, $status, $payment_response['message'])) {

            }
        } else {
            $status == 'Failed';
            $res = array('success' => false, 'payment_status' => $status, 'message' => vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language));
        }
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

    public function getPaymentCurrentStatus($paymentData = array()) {
        return $paymentStatusResponse = array();
    }

    public function paymentCallbackHandler($callbackHandlerData = array())
    {
        global $log;
        $log->debug('Entering into B2binpay paymentCallbackHandler...');
        try
        {
            $callbackResponse = '';
            $callbackResponseJson = file_get_contents('php://input');$log->debug($callbackResponseJson);
            if(!empty($callbackResponseJson))
            {
                $callbackResponse = json_decode($callbackResponseJson, true);$log->debug($callbackResponse);
            }
            if(!empty($callbackResponse))
            {
                $status = strtolower($callbackResponse['status']);
                $paymentStatus = '';
                $recordModel = Vtiger_Record_Model::getInstanceById($callbackHandlerData['record_id'], 'Payments');
                $failedReason = "Payment failed by fairpay callback";
                switch ($status) {
                    case 2:
                        $paymentStatus = 'Success';
                        $autoConfirm = $this->getParameter('auto_confirm');
                        if ($autoConfirm == 'No')
                        {
                            $paymentStatus = 'PaymentSuccess';
                        }
                        break;
                    case -3:
                        $paymentStatus = 'Failed';
                        $recordModel->set('failure_reason', $failedReason);
                        break;
                    case -2:
                        $paymentStatus = 'Failed';
                        $recordModel->set('failure_reason', $failedReason);
                        break;
                    case -1:
                        $paymentStatus = 'Failed';
                        $recordModel->set('failure_reason', $failedReason);
                        break;
                    default:
                        break;
                }

                if(!empty($paymentStatus) && !empty($recordModel))
                {
                    $recordModel->set('mode', 'edit');
                    $recordModel->set('status', $paymentStatus);
                    $recordUpdateStatus = $recordModel->save();
                    if($recordUpdateStatus)
                    {
                        $callbackResponse['message'] = "Success : Payment record status updated successfully.";
                        $callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['provider_name'], $callbackHandlerData['provider_title'], $callbackResponse, $paymentStatus);
                    }
                    else
                    {
                        $callbackResponse['message'] = "Error : Callback Response. Payment record status not updated.";
                        $callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['provider_name'], $callbackHandlerData['provider_title'], $callbackResponse, 'Failed');
                    }
                }
                else
                {
                    $callbackResponse['message'] = "Error : Callback Response. Payment record not found.";
                    $callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['provider_name'], $callbackHandlerData['provider_title'], $callbackResponse, 'Failed');
                }
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
