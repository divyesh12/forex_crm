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

class ServiceProviders_BlockChain_Provider extends ServiceProviders_AbstractPaymentGatways_Model {

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file
    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'root_url', 'label' => 'Root URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'receive_root_url', 'label' => 'Receive Root URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'secret_key', 'label' => 'Secret Key', 'type' => 'text', 'mandatory' => true), //User defined
        array('name' => 'xpub', 'label' => 'Bitcoin Public Address', 'type' => 'text', 'mandatory' => true),
        array('name' => 'api_key', 'label' => 'Api Key', 'type' => 'text', 'mandatory' => true),
    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
            //array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'required' => true)
    );
    private static $WITHDRAW_FORM_PARAMETERS = array(
        array('name' => 'btc_address', 'label' => 'CAB_LBL_BTC_ADDRESS', 'type' => 'text', 'required' => true, 'mandatory' => true)
    );

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName() {
        return 'BlockChain'; // don't take name with any space or special charctor
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
        global $PORTAL_URL, $site_URL;

        $order_id = PaymentProvidersHelper::generateUUID(); //Generated the unique order id from database

        if (!$order_id) {
            return array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_GENERATING_ORDER_ID', $this->module, $portal_language));
        }

        if (!empty($request)) {
            $returnUrl = $PORTAL_URL . "#/payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            if ($request['is_mobile_request']) {
                $returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            }
            //$returnUrl = "http://localhost/forex_crm_v2/testblockchain.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            if ($request['payment_operation'] == 'Deposit') {
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($request['payment_from']);
                $amount_value = number_format($request['net_amount'], $provider->parameters['allowed_decimal'], '.', ''); //For 2, its round up the value                                 //
                ////Order creation
                // Must be pass as define because it is passing to rest api                
                //$invoice_id = $order_id;
                $blockchain_receive_root = $provider->parameters['receive_root_url'];
                $root_url = $provider->parameters['root_url'];
                $secret = $provider->parameters['secret_key'];
                $invoice_id = time();
                $callback_url = $site_URL . "webhook.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "&invoice_id=" . $invoice_id . "&secret=" . $secret;
                $xpub = $provider->parameters['xpub'];
                $api_key = $provider->parameters['api_key'];
                $price_in_btc = file_get_contents($root_url . "tobtc?currency=" . $request['payment_currency'] . "&value=" . $request['net_amount']);
                $request_url = $blockchain_receive_root . "v2/receive?key=" . $api_key . "&callback=" . urlencode($callback_url) . "&xpub=" . $xpub;
                $resp = file_get_contents($request_url);
                $response = json_decode($resp);
//                print json_encode(array('input_address' => $response->address));
//                die;
                //End of Order creation    
                if (empty($response->address)) {
                    //$redirectUrl = $order->getRedirectUrl() . '?orid=' . $order_id;                    
                    $redirectUrl = $returnUrl;
                    $request['btc_address'] = $response->address;
                    $request['price_in_btc'] = $price_in_btc;
                    $request['callback_url'] = $callback_url;
                    if (PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Created', 'Order Creation')) {
                        $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'internal_page', 'redirect_url' => $redirectUrl, 'order_id' => $order_id, 'btc_address' => $response->address));
                    } else {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module, $portal_language));
                    }
                } else {
                    $res = array('success' => false, 'message' => $response);
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
            if (PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $payment_response['pm'], $payment_response, $status, $payment_response['message'])) {
                
            }
        } else {
            $status == 'Failed';
            $res = array('success' => false, 'payment_status' => $status, 'message' => vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language));
        }

        return $res;
    }

}

?>