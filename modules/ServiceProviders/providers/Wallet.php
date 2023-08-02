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

class ServiceProviders_Wallet_Provider extends ServiceProviders_AbstractPaymentGatways_Model {

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file
    private static $REQUIRED_PARAMETERS = array(
    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
    );
    private static $WITHDRAW_FORM_PARAMETERS = array(
    );

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName() {
        return 'Wallet'; // don't take name with any space or special charctor
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
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getDepositFormParams() {
        return self::$DEPOSIT_FORM_PARAMETERS;
    }

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getWithdrawFormParams() {
        return self::$WITHDRAW_FORM_PARAMETERS;
    }

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getFormParams() {
        return self::$FORM_PARAMETERS;
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
//                print_r($request);
        if (!empty($paymentInput)) {
            foreach ($paymentInput as $field) {
                $name = $field['name'];
//                echo $name ;
                //echo $field['type'];
//                echo '<pre>';

                if ($field['required'] && !array_key_exists($name, $request) && $field['type'] != "file") {
                    $res = array('success' => false, 'message' => $field['name'] . vtranslate('CAB_MSG_FIELD_IS_REQUIRED', $this->module, $portal_language));
                    break;
                }
//                
                if ($field['required'] && (!array_key_exists($name, $request) && !array_key_exists($name, $FILES)) && $field['type'] == "file") {
                    $res = array('success' => false, 'message' => $field['name'] . vtranslate('CAB_MSG_FIELD_IS_REQUIRED', $this->module, $portal_language));
                    break;
                }
//                if (($field['required'] && (!array_key_exists($name, $request) || !array_key_exists($name, $FILES) ))) {                
//                }
//                if(($field['required'] && $field['type'] == 'file' && !array_key_exists($name, $FILES))){
//                    $res = array('success' => false, 'message' => $field['name'] . ' field is required');
//                    break;
//                    
//                }
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

//    public function getVerifyTransferDetails($request) {
//        $paymentInput = array();
//        if ($request['payment_operation'] == 'Deposit') {
//            $paymentInput = self::getDepositFormParams();
//        } else if ($request['payment_operation'] == 'Withdrawal') {
//            $paymentInput = self::getWithdrawFormParams();
//        } else {
//            $res = array('success' => false, 'message' => 'Payment operation does not match');
//        }
//        $res = array('success' => true);
//        if (!empty($paymentInput)) {
//            for ($i = 0; $i < count($paymentInput); $i++) {
//                $name = $paymentInput[$i]['name'];
//                if (!array_key_exists($name, $request) || !$paymentInput[$i]['required']) {
//                    $res = array('success' => false, 'message' => ucfirst($paymentInput[$i]['name']) . ' field is required');
//                    break;
//                }
//            }
//        }
//        return $res;
//    }    

    public function paymentProcess($request, $portal_language) {
        global $PORTAL_URL, $site_URL;
        $order_id = PaymentProvidersHelper::generateUUID(); //Generated the unique order id from database

        if (!$order_id) {
            return array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_GENERATING_ORDER_ID', $this->module, $portal_language));
        }


        if (!empty($request)) {
            $returnUrl = $PORTAL_URL . "#/payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            $cancelUrl = $PORTAL_URL . "#/payments/fail?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";

            if ($request['is_mobile_request']) {
                $returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
                $cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            }

            if ($request['payment_operation'] == 'Deposit') {
                if (PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Created', 'Request')) {
                    $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Manual', 'redirect_url' => $returnUrl, 'order_id' => $order_id));
                } else {
                    $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module, $portal_language));
                }
            } else if ($request['payment_operation'] == 'Withdrawal') {
                $returnUrl = $PORTAL_URL . "#/payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
                $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Manual', 'redirect_url' => $returnUrl, 'order_id' => $order_id, 'message' => vtranslate('CAB_MSG_YOUR_WITH_REQUEST_HAS_BEEN_SENT', $this->module)));
            } else {
                $res = array('success' => false, 'message' => vtranslate('CAB_MSG_PAYMENT_OPER_DOES_NOT_MATCH', $this->module, $portal_language));
            }
        } else {
            $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_REQUEST', $this->module, $portal_language));
        }
        return $res;
    }

    //Verify the payment response and insert to payment log table
    public function paymentResponseVerification($status, $payment_response, $order_id, $portal_language) {
        
    }

}

?>