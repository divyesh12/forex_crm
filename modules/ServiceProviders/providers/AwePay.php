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
include_once('lib/jose-php-lib/vendor/autoload.php');

class ServiceProviders_AwePay_Provider extends ServiceProviders_AbstractPaymentGatways_Model
{

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file
    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'secret_key', 'label' => 'Secret Key', 'type' => 'text', 'mandatory' => true),
        array('name' => 't_sid', 'label' => 'Test SID', 'type' => 'text', 'mandatory' => true),
        array('name' => 'sid', 'label' => 'Base SID', 'type' => 'text', 'mandatory' => true),
        array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),        
        array('name' => 'bank_currency', 'label' => 'CAB_LBL_BANK_CURRENCY', 'type' => 'text', 'block' => self::TRANSFER_DETAILS_BLOCK, 'mandatory' => true),
        array('name' => 'currency_conversion', 'label' => 'Currency Conversion Tool', 'type' => 'picklist','picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
        array('name' => 'bank_currency', 'label' => 'CAB_LBL_BANK_CURRENCY', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
        array('name' => 'conversion_rate', 'label' => 'CAB_LBL_CONVERSION_RATE', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
        array('name' => 'bank_amount', 'label' => 'CAB_LBL_BANK_AMOUNT', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
    );
    private static $WITHDRAW_FORM_PARAMETERS = array(
    );

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName()
    {
        return 'AwePay'; // don't take name with any space or special charctor
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
                
                $secret_key = $provider->parameters['secret_key'];
                $actionUrl = $provider->parameters['base_url'];
                $description = $provider->parameters['description'];
                $display_currency = $request['payment_currency'];

                $contact_data = PaymentProvidersHelper::getContactDetails($request['contactid']);
                $firstname = $contact_data['firstname'];
                $lastname = $contact_data['lastname'];
                
                if ($provider->parameters['test_mode'] == 'Yes') {
                    $sid = $provider->parameters['t_sid'];
                } else {
                    $sid = $provider->parameters['sid'];
                }
                $bank_currency = $provider->parameters['bank_currency'];
           
                $operation_type = 'Deposit';
                if (strtolower($bank_currency) != 'usd') {
                    $conversion_rate = PaymentProvidersHelper::getCurrencyRate($display_currency, $bank_currency, $operation_type);
                    if ($conversion_rate) {
                        $displayAmount = round($conversion_rate * $request['net_amount'], 2);
                    } else {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_CONVERSION_RATE_NOT_FOUND', $this->module, $portal_language));
                        return $res;
                    }                        
                }
               
                $payload = array(
                    'sid' => $sid,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'tid' => $order_id,
                    'payby' => 'p2p',
                    'tx_action' => 'PAYMENT',
                    'currency' => $bank_currency,
                    'bank_code' => '',
                    'no_ship_address' => '1',
                    'hide_descriptor' => '1',
                    'successurl' => $returnUrl,
                    'failureurl' => $cancelUrl,
                    'postbackurl' => $callBackUrl,
                    'item_quantity[]' => 1,
                    'item_name[]' => 'Deposit',
                    'item_no' => 1,
                    'item_desc' => $description,
                    'item_amount_unit[]' => $displayAmount
                );

                $secretplain_text = "357A2E0F54470FCE1946E461F6BC2C4FE2AA7BA3B06D92D10B17741C7D752AC0";
                $json_data = json_encode($payload);
                $secret = hex2bin($secretplain_text);
            
                $jwe = new JOSE_JWE($json_data);
                $algorithm = 'dir';
                $encrypt_method = 'A128CBC-HS256';
            
                $jwe = $jwe->encrypt($secret, $algorithm, $encrypt_method);
                $token = $jwe->toString();
            
                // $jwe_decoded = JOSE_JWT::decode($jwe->toString());
                // $token = $jwe->toString();
                // $url_with_token = "https://secure.awepay.com/txHandler.php?token=" . $token;
            
                $result_form = "<form id='payment_form' name='awepay' action='" . $actionUrl . "' method='get'>";
                    $result_form .= "<input type='hidden' name='token' value='$token'/>";
                $result_form .= "</form>";

                if (PaymentProvidersHelper::createPaymentLog($order_id, $this->getName(), $request['payment_from'], $request, 'Created', 'Form Generation')) {
                    $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Form', 'redirect_url' => $actionUrl, 'order_id' => $order_id, 'result_form' => $result_form));
                } else {
                    $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module, $portal_language));
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
        // if (PaymentProvidersHelper::getPaymentRecord($order_id)) {
        if (!empty($order_id)) {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_response['pm']);
            $rucode = $provider->parameters['secret_key'];
            $status = $this->decrypt($payment_response['status'], $rucode);

            if (isset($status['error']) &&  strtolower($status['error']['response']) == 'declined') {
                $payment_response['message'] = $status['error']['msg'];
                $res = array('success' => false, 'payment_status' => 'Failed', 'message' => $payment_response['message']);
            } else {
                $payment_response['message'] = vtranslate('CAB_MSG_DEPOSIT_REQUEST_HAS_BEEN_SENT', $this->module, $portal_language);
                $res = array('success' => true, 'payment_status' => 'Pending', 'message' => $payment_response['message']);
            }
            
            // if (PaymentProvidersHelper::createPaymentLog($order_id, $this->getName(), $payment_response['pm'], $payment_response, $status, $payment_response['message'])) {

            // }
        } else {
            $status == 'Failed';
            $res = array('success' => false, 'payment_status' => $status, 'message' => vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language));
        }
        return $res;
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

    public function decrypt($string, $key) {
        $result = "";
        $string = base64_decode($string);

        for($i=0; $i<strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)-ord($keychar));
            $result.=$char;
        }

        parse_str($result,$result);

        return $result;
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
        global $adb,$log;
        $status = "";
        $paymentStatusResponse = array();
        $orderId = $paymentData['order_id'];
        if (!empty($orderId)) {
            $thirdPartyPayStatus = array('approved' => 'success', 'declined' => 'failed', 'payg_error' => 'failed');
            
            $getPaymentCallbackRecord = "SELECT data FROM vtiger_payment_logs WHERE order_id = ? AND provider_type = ? AND status = ? AND event = ? ORDER BY id DESC LIMIT 1";
            $resultGetPaymentCallback = $adb->pquery($getPaymentCallbackRecord, array($orderId, $paymentData['payment_from'], 'Created', 'Callback Response'));

            $callbackJsonData = $adb->query_result($resultGetPaymentCallback, 0, 'data');
            $callbackJsonData = html_entity_decode($callbackJsonData);
            $callbackData = json_decode($callbackJsonData, true);

            $thirdPartyResponseStatus = strtolower($callbackData['request']['response']);
            $paymentStatusResponse['data'] = $callbackData;
            $paymentStatusResponse['status'] = $status = $thirdPartyPayStatus[$thirdPartyResponseStatus];
        }
        return $paymentStatusResponse;
    }
    
    public function paymentCallbackHandler($callbackHandlerData = array())
    {
        global $log;
        $log->debug('Entering into Awepay paymentCallbackHandler...');
        try
        {
            $callbackResponse = $_REQUEST;$log->debug($callbackResponseJson);

            if(!empty($callbackResponse))
            {
                $status = strtolower($callbackResponse['response']);
                $paymentStatus = '';
                $recordModel = Vtiger_Record_Model::getInstanceById($callbackHandlerData['record_id'], 'Payments');
                $failedReason = "Payment failed by fairpay callback";
                switch (strtolower($status)) {
                    case 'approved':
                        $paymentStatus = 'Success';
                        $autoConfirm = $this->getParameter('auto_confirm');
                        if ($autoConfirm == 'No')
                        {
                            $paymentStatus = 'PaymentSuccess';
                        }
                        break;
                    case 'declined':
                        $paymentStatus = 'Failed';
                        $recordModel->set('failure_reason', $failedReason);
                        break;
                    case 'payg_error':
                        $paymentStatus = 'Failed';
                        break;
                    case 'failed':
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
