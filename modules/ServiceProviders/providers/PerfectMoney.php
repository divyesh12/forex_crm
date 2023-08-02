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

class ServiceProviders_PerfectMoney_Provider extends ServiceProviders_AbstractPaymentGatways_Model
{

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file
    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'payee_account', 'label' => 'Payee Account', 'type' => 'text', 'mandatory' => true),
        array('name' => 'payee_name', 'label' => 'Payee Name', 'type' => 'text', 'mandatory' => true),
        array('name' => 'alternate_passphrase', 'label' => 'Alternate Passphrase', 'type' => 'text', 'mandatory' => true),
        array('name' => 'form_action_url', 'label' => 'Form Action URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        // array('name' => 'test_url', 'label' => 'Test URL', 'type' => 'text', 'mandatory' => true),
        // array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),
    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
    );
    private static $WITHDRAW_FORM_PARAMETERS = array(
        // array('name' => 'wallet', 'label' => 'CAB_LBL_WALLET', 'type' => 'text', 'required' => true),
    );

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName()
    {
        return 'PerfectMoney'; // don't take name with any space or special charctor
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
            $returnUrl = $PORTAL_URL . "payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            $cancelUrl = $PORTAL_URL . "payments/fail?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";

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
                                
                $payee_account = $provider->parameters['payee_account'];
                $payee_name = $provider->parameters['payee_name'];
                $form_action_url = $provider->parameters['form_action_url'];                
                $base_url = $provider->parameters['base_url'];
                $description = $provider->parameters['description'];
                $display_currency = $request['payment_currency'];

                if ($provider->parameters['test_mode'] == 'Yes') {
                    $amount_value = $amount_value;
                } else {
                    $b2binpay_action_url = $base_url;
                    $amount_value = $amount_value;
                }

                $perfectMoneyFormParam = array(
                    'PAYEE_ACCOUNT' => $payee_account,
                    'PAYEE_NAME' => $payee_name,
                    'PAYMENT_ID' => $order_id,
                    'PAYMENT_AMOUNT' => $amount_value,
                    'PAYMENT_UNITS' => $display_currency,
                    'STATUS_URL' => '', //$callBackUrl
                    'PAYMENT_URL' => $returnUrl,
                    'PAYMENT_URL_METHOD' => 'GET',
                    'NOPAYMENT_URL' => $cancelUrl,
                    'NOPAYMENT_URL_METHOD' => 'GET',
                    'BAGGAGE_FIELDS' => 'orid pm',
                    'orid' => $order_id,
                    'pm' => $request['payment_from'],
                );

                $result_form = "<form id='payment_form' name='perfectmoney' action='" . $form_action_url . "' method='post'>";
                foreach ($perfectMoneyFormParam as $key => $value) {
                    $result_form .= "<input type='hidden' name='$key' value='$value'/>";
                }
                $result_form .= "</form>";

                if (PaymentProvidersHelper::createPaymentLog($order_id, $this->getName(), $request['payment_from'], $request, 'Created', 'Form Generation')) {
                    $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Form', 'redirect_url' => $form_action_url, 'order_id' => $order_id, 'result_form' => $result_form));
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
        global $log;$log->debug('Entering into paymentResponseVerification');$log->debug($status);$log->debug($payment_response);
        $paymentStatus = false;
        $logStatus = "Failed";
        $logResponse = array("request" => $payment_response, "response" => array("log_message" => ""));
        $errorMsg = "";
        $paymentGatewayName = $this->getName();
        if (PaymentProvidersHelper::getPaymentRecord($order_id))
        {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($paymentGatewayName);
            $alternatePassphrase = $provider->parameters['alternate_passphrase'];$log->debug('$alternatePassphrase='.$alternatePassphrase);
            $md5AlternatePassphrase = strtoupper(md5($alternatePassphrase));

            $concatedString = $payment_response['PAYMENT_ID'].':'.$payment_response['PAYEE_ACCOUNT'].':'.$payment_response['PAYMENT_AMOUNT'].':'.$payment_response['PAYMENT_UNITS'].':'.$payment_response['PAYMENT_BATCH_NUM'].':'.$payment_response['PAYER_ACCOUNT'].':'.$md5AlternatePassphrase.':'.$payment_response['TIMESTAMPGMT'];
            $hashString = strtoupper(md5($concatedString));$log->debug('$hashString='.$hashString);$log->debug('v2hash='.$payment_response['V2_HASH']);

            if($hashString === $payment_response['V2_HASH'])
            {$log->debug('hashing match, request authenticated..');
                if(isset($payment_response['PAYMENT_BATCH_NUM']) && $payment_response['PAYMENT_BATCH_NUM'] != 0 && $payment_response['PAYMENT_BATCH_NUM'] != '0' && $payment_response['PAYMENT_BATCH_NUM'] != '')
                {$log->debug('transaction success');
                    $paymentStatus = true;
                }
            }
        }
        else
        {
            $paymentStatus = false;
            $errorMsg = vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language);
            $res = array('success' => false, 'payment_status' => "Failed", 'message' => $errorMsg);
            return $res;
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
            $errorMsg = vtranslate('CAB_MSG_CANCELLED_BY_USER', $this->module, $portal_language);
            $res = array('success' => true, 'payment_status' => $status, 'message' => $errorMsg);
        }
        /*Create log*/
        PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $paymentGatewayName, $logResponse, $logStatus, "PerfectMoney callback");
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
}
