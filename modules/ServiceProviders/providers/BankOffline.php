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

class ServiceProviders_BankOffline_Provider extends ServiceProviders_AbstractPaymentGatways_Model {

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file

    public function __construct() {
        //$this->SUPPORTS_CURRENCY_CONVERTOR = true;
    }

    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'bank_currency', 'label' => 'CAB_LBL_BANK_CURRENCY', 'type' => 'text', 'block' => self::TRANSFER_DETAILS_BLOCK, 'mandatory' => true),
        array('name' => 'auto_confirm', 'label' => 'Payment Auto Confirm', 'type' => 'picklist',
            'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        array('name' => 'deposit_supported', 'label' => 'Deposit Supported', 'type' => 'picklist',
            'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        array('name' => 'withdrawal_supported', 'label' => 'Withdrawal Supported', 'type' => 'picklist',
            'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        array('name' => 'commission', 'label' => 'Commission(%)', 'type' => 'text', 'block' => self::TRANSFER_DETAILS_BLOCK),
        array('name' => 'description', 'label' => 'Description', 'type' => 'text', 'mandatory' => true),
        array('name' => 'currencies', 'label' => 'Currencies', 'type' => 'text', 'block' => self::TRANSFER_DETAILS_BLOCK, 'mandatory' => true),
        array('name' => 'deposit_min', 'label' => 'Minimum Deposit', 'type' => 'number', 'block' => self::TRANSFER_DETAILS_BLOCK, 'display_type' => 'Deposit', 'mandatory' => true),
        array('name' => 'deposit_max', 'label' => 'Maximum Deposit', 'type' => 'number', 'block' => self::TRANSFER_DETAILS_BLOCK, 'display_type' => 'Deposit', 'mandatory' => true),
        array('name' => 'withdrawal_min', 'label' => 'Minimum Withdrawal', 'type' => 'number', 'block' => self::TRANSFER_DETAILS_BLOCK, 'display_type' => 'Withdrawal', 'mandatory' => true),
        array('name' => 'withdrawal_max', 'label' => 'Maximum Withdrawal', 'type' => 'number', 'block' => self::TRANSFER_DETAILS_BLOCK, 'display_type' => 'Withdrawal', 'mandatory' => true),
        array('name' => 'allowed_decimal', 'label' => 'Decimal Allowed', 'type' => 'number', 'mandatory' => true),
        array('name' => 'sequence_number', 'label' => 'Sequence Number', 'type' => 'number'),

    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
//        array('name' => 'conversion_rate', 'label' => 'CAB_LBL_CONVERSION_RATE', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
//        array('name' => 'bank_amount', 'label' => 'CAB_LBL_BANK_AMOUNT', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
//        array('name' => 'bank_currency', 'label' => 'CAB_LBL_BANK_CURRENCY', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
//        array('name' => 'transfer_from', 'label' => 'CAB_LBL_TRANSFER_FROM', 'type' => 'text', 'required' => true, 'placeholder' => "CAB_MSG_EG_FROM_BANK_NAME", 'mandatory' => true),
        array('name' => 'transfer_type', 'label' => 'CAB_LBL_TRANSFER_TYPE', 'type' => 'text', 'placeholder' => "CAB_MSG_EG_TRANSFER_TYPE", 'required' => true, 'mandatory' => true),
        //When using is support currency convertor then need to use below three fields        
//        array('name' => 'payment_ref_id', 'label' => 'CAB_LBL_PAYMENT_REF_ID', 'type' => 'text', 'required' => true, 'mandatory' => true),
        array('name' => 'file', 'label' => 'CAB_LBL_DEPOSIT_RECIEPT', 'type' => 'file', 'required' => true, 'allowed_type' => "JPEG,JPG,PNG,PDF", 'size' => '5', 'note' => 'CAB_MSG_FILE_SUPPORTS', 'mandatory' => true),
    );
    private static $WITHDRAW_FORM_PARAMETERS = array(
        array('name' => 'bank_account_holder_name', 'label' => 'CAB_LBL_ACCOUNT_HOLDER_NAME', 'type' => 'text', 'placeholder' => "", 'required' => true, 'mandatory' => true),
        array('name' => 'bank_account_no', 'label' => 'CAB_LBL_ACCOUNT_NO', 'type' => 'number', 'placeholder' => "", 'required' => true, 'mandatory' => true),
        array('name' => 'bank_name', 'label' => 'CAB_LBL_BANK_NAME', 'type' => 'text', 'placeholder' => "", 'required' => true, 'mandatory' => true),
        array('name' => 'bank_address', 'label' => 'CAB_LBL_BANK_ADDRESS', 'type' => 'textarea', 'placeholder' => "", 'required' => true, 'mandatory' => true),
        array('name' => 'bank_IBAN', 'label' => 'CAB_LBL_IBAN', 'type' => 'text', 'placeholder' => "", 'required' => false),
        array('name' => 'bank_swift_IFSC_code', 'label' => 'CAB_LBL_SWIFT_IFSC_CODE', 'type' => 'text', 'placeholder' => "", 'required' => true, 'mandatory' => true),
        array('name' => 'bank_phone_number', 'label' => 'CAB_LBL_PHONE_NUMBER', 'type' => 'number', 'placeholder' => "", 'required' => true, 'mandatory' => true),
        array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'placeholder' => "", 'required' => true, 'mandatory' => true),
        array('name' => 'conversion_rate', 'label' => 'CAB_LBL_CONVERSION_RATE', 'type' => 'hidden', 'required' => false, 'display' => true),
        array('name' => 'bank_amount', 'label' => 'CAB_LBL_BANK_AMOUNT', 'type' => 'hidden', 'required' => false, 'display' => true),
        array('name' => 'bank_currency', 'label' => 'CAB_LBL_BANK_CURRENCY', 'type' => 'hidden', 'required' => false, 'display' => true),
    );

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName() {
        return 'BankOffline'; // don't take name with any space or special charctor
    }

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getRequiredParams() {
        return array_merge(self::$REQUIRED_PARAMETERS);
//        return self::DEFAULT_REQUIRED_PARAMETERS;
    }

    /**
     * Function to get deposit parameters
     * @return <array> required parameters list
     */
    public function getDepositFormParams() {
        return self::$DEPOSIT_FORM_PARAMETERS;
    }

    /**
     * Function to get withdraw parameters
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
            $cancelUrl = $PORTAL_URL . "#/payments/fail?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";

            if ($request['is_mobile_request']) {
                $returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
                $cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            }
            
            if ($request['payment_operation'] == 'Deposit') {
                //$provider = ServiceProvidersManager::getActiveInstanceByProvider($request['payment_from']);
                if (PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Created', 'Request')) {
                    $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Manual', 'redirect_url' => $returnUrl, 'order_id' => $order_id));
                } else {
                    $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module, $portal_language));
                }
            } else if ($request['payment_operation'] == 'Withdrawal') {
                $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Manual', 'redirect_url' => $returnUrl, 'order_id' => $order_id, 'message' => vtranslate('CAB_MSG_YOUR_WITH_REQUEST_HAS_BEEN_SENT', $this->module, $portal_language)));
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

    //Verify the payment response and update the log with status and modified date and time
    //Verify the payment response and insert to payment log table
    public function paymentResponseVerification($status, $payment_response, $order_id, $portal_language) {
        
    }

}

?>