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

class ServiceProviders_FairPay_Provider extends ServiceProviders_AbstractPaymentGatways_Model
{

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file
    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'api_key', 'label' => 'API Key', 'type' => 'text', 'mandatory' => true),
        array('name' => 'secret_key', 'label' => 'Secret Key', 'type' => 'text', 'mandatory' => true),
        array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        array('name' => 'test_url_token', 'label' => 'Token Test URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'base_url_token', 'label' => 'Token Base URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'test_url_payment_method', 'label' => 'Payment Method Test URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'base_url_payment_method', 'label' => 'Payment Method Base URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'test_url', 'label' => 'Test URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),
    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
        array('name' => 'city', 'label' => 'CAB_LBL_CITY', 'type' => 'text', 'allow' => 'character', 'required' => true),
        array('name' => 'postcode', 'label' => 'CAB_LBL_POST_CODE', 'type' => 'text', 'required' => true),
        array('name' => 'address', 'label' => 'CAB_LBL_ADDRESS', 'type' => 'textarea', 'required' => true),
        array('name' => 'phone', 'label' => 'CAB_LBL_PHONE_NUMBER', 'type' => 'number', 'required' => true),
        array('name' => 'country', 'label' => 'CAB_LBL_COUNTRY', 'type' => 'dropdown_depended', 'picklist' => array(
            array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
            array("isAllow" => "true", "value" => "BR", "label" => "Brazil"),
            array("isAllow" => "true", "value" => "CL", "label" => "Chile"),
            array("isAllow" => "true", "value" => "CR", "label" => "Costa Rica"),
            array("isAllow" => "true", "value" => "EC", "label" => "Ecuador"),
            array("isAllow" => "true", "value" => "SV", "label" => "El Salvador"),
            array("isAllow" => "true", "value" => "MX", "label" => "Mexico"),
            array("isAllow" => "true", "value" => "PA", "label" => "Panama"),
            array("isAllow" => "true", "value" => "PE", "label" => "Peru"),
            array("isAllow" => "true", "value" => "GT", "label" => "Guatemala"),
        ), 'required' => true, 'dependency' => 'fairpay_payment_method'),
        array('name' => 'fairpay_payment_method', 'label' => 'CAB_LBL_PAYMENT_METHOD_NAME', 'type' => 'dropdown_depended', 'picklist' => array(
            array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
            array("isAllow" => "true", "value" => "CL_CASH_1042", "label" => "Lider - CASH"),
            array("isAllow" => "true", "value" => "CL_CASH_1011", "label" => "aCuenta - CASH"),
            array("isAllow" => "true", "value" => "CL_CASH_1043", "label" => "ServiEstado - CASH"),
            array("isAllow" => "true", "value" => "CL_CASH_1044", "label" => "Caja Vecina - CASH"),
            array("isAllow" => "true", "value" => "CL_ONLINE_1033", "label" => "Santander Chile - ONLINE"),
            array("isAllow" => "true", "value" => "CL_ONLINE_1010", "label" => "Banco TBANC - ONLINE"),
            array("isAllow" => "true", "value" => "CL_ONLINE_1004", "label" => "Banco BCI - ONLINE"),
            array("isAllow" => "true", "value" => "CR_CASH_1008", "label" => "Banco Nacional - CASH"),
            array("isAllow" => "true", "value" => "CR_ONLINE_1008", "label" => "Banco Nacional - ONLINE"),
            array("isAllow" => "true", "value" => "CR_ONLINE_1022", "label" => "Grupo Mutual - ONLINE"),
            array("isAllow" => "true", "value" => "CR_CASH_1022", "label" => "Grupo Mutual - CASH"),
            array("isAllow" => "true", "value" => "CR_ONLINE_1021", "label" => "Mucap - ONLINE"),
            array("isAllow" => "true", "value" => "CR_CASH_1021", "label" => "Mucap - CASH"),
            array("isAllow" => "true", "value" => "CR_CASH_1039", "label" => "Teledolar MN - CASH"),
            array("isAllow" => "true", "value" => "CR_ONLINE_1005", "label" => "Banco Cathay - ONLINE"),
            array("isAllow" => "true", "value" => "CR_CASH_1005", "label" => "Banco Cathay - CASH"),
            array("isAllow" => "true", "value" => "MX_ONLINE_1001", "label" => "BBVA Bancomer - ONLINE"),
            array("isAllow" => "true", "value" => "MX_CASH_1001", "label" => "BBVA Bancomer - CASH"),
            array("isAllow" => "true", "value" => "MX_CASH_1023", "label" => "HSBC Mexico - CASH"),
            array("isAllow" => "true", "value" => "MX_CASH_1025", "label" => "OpenPay - CASH"),
            array("isAllow" => "true", "value" => "MX_CASH_1003", "label" => "Banco Azteca - CASH"),
            array("isAllow" => "true", "value" => "MX_ONLINE_1030", "label" => "SPEI MX - ONLINE"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1002", "label" => "BBVA Continental - ONLINE"),
            array("isAllow" => "true", "value" => "PE_CASH_1002", "label" => "BBVA Continental - CASH"),
            array("isAllow" => "true", "value" => "PE_CASH_1038", "label" => "Tambo - CASH"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1012", "label" => "Banco de CrÃ©dito - ONLINE"),
            array("isAllow" => "true", "value" => "PE_CASH_1012", "label" => "Banco de CrÃ©dito - CASH"),
            array("isAllow" => "true", "value" => "PE_CASH_1034", "label" => "Scotiabank Peru - CASH"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1034", "label" => "Scotiabank Peru - ONLINE"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1024", "label" => "Interbank - ONLINE"),
            array("isAllow" => "true", "value" => "PE_CASH_1024", "label" => "Interbank - CASH"),
            array("isAllow" => "true", "value" => "PE_CASH_1016", "label" => "Caja Tacna - CASH"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1016", "label" => "Caja Tacnav"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1015", "label" => "Caja Huancayo - ONLINE"),
            array("isAllow" => "true", "value" => "PE_CASH_1015", "label" => "Caja Huancayo - CASH"),
            array("isAllow" => "true", "value" => "PE_CASH_1040", "label" => "Western Union - CASH"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1014", "label" => "Caja Arequipa - ONLINE"),
            array("isAllow" => "true", "value" => "PE_CASH_1014", "label" => "Caja Arequipa - CASH"),
            array("isAllow" => "true", "value" => "SV_CASH_1028", "label" => "Punto Xpress SLV - CASH"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1017", "label" => "Caja Trujillo - ONLINE"),
            array("isAllow" => "true", "value" => "BR_CASH_1083", "label" => "Boleto - CASH"),
            array("isAllow" => "true", "value" => "BR_CASH_1084", "label" => "Boleto Flash Itau - CASH"),
            array("isAllow" => "true", "value" => "BR_ONLINE_1099", "label" => "Direct PIX - ONLINE"),
            array("isAllow" => "true", "value" => "EC_CASH_1009", "label" => "Banco Pichincha - CASH"),
            array("isAllow" => "true", "value" => "EC_ONLINE_1009", "label" => "Banco Pichincha - ONLINE"),
            array("isAllow" => "true", "value" => "EC_ONLINE_1007", "label" => "Banco Guayaquil - ONLINE"),
            array("isAllow" => "true", "value" => "EC_CASH_1007", "label" => "Banco Guayaquil - CASH"),
            array("isAllow" => "true", "value" => "PA_CASH_1041", "label" => "Western Union - CASH"),
            array("isAllow" => "true", "value" => "CL_CASH_1020", "label" => "Express Lider - CASH"),
            array("isAllow" => "true", "value" => "PE_CASH_1029", "label" => "Banco Ripley - CASH"),
            array("isAllow" => "true", "value" => "PE_CASH_1017", "label" => "Caja Trujillo - CASH"),
            array("isAllow" => "true", "value" => "GT_CASH_1085", "label" => "Banco Industrial - CASH"),
            array("isAllow" => "true", "value" => "GT_ONLINE_1085", "label" => "Banco Industrial - ONLINE"),
        ), 'required' => true, 'dependency' => ''),
        array('name' => 'personalId', 'label' => 'CAB_LBL_PERSONALID', 'type' => 'text', 'class' => 'br_cpf personalid'),
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
        return 'FairPay'; // don't take name with any space or special charctor
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
                $amount_value = $amount_value * 100; // this payment gateway take * 100 passing amount from user
                // $access_token = $_SESSION['fairPayAccessToken'];
                $api_key = $provider->parameters['api_key'];
                $secret_key = $provider->parameters['secret_key'];
                $IP = $_SERVER['REMOTE_ADDR'];
                $test_url = $provider->parameters['test_url'];
                $base_url = $provider->parameters['base_url'];
                $test_url_token = $provider->parameters['test_url_token'];
                $base_url_token = $provider->parameters['base_url_token'];
                $description = $provider->parameters['description'];
                $display_currency = $request['payment_currency'];
                $contact_data = PaymentProvidersHelper::getContactDetails($request['contactid']);
                if (isset($request['personalId']) && !empty($request['personalId'])) {
                    $personalId = $request['personalId'];    
                } else {
                $personalId = $this->generateRandomString();
                }

                if (!$request['is_mobile_request']) {
                    $request['country'] = $this->getActualCountryNameAndLabel('Value', $request['country']);
                    $request['fairpay_payment_method'] = $this->getActualMethodNameAndLabel('Value', $request['fairpay_payment_method'], $request['country']);
                }
                
                $explode = explode("_", $request['fairpay_payment_method']);
                $paymentMethodId = $explode[2];
                $channel = $explode[1];

                if ($provider->parameters['test_mode'] == 'Yes') {
                    $b2binpay_action_url = $test_url;
                    $apiUrlToken = $test_url_token;
                } else {
                    $b2binpay_action_url = $base_url;
                    $apiUrlToken = $base_url_token;
                }

                $auth = base64_encode($api_key . ':' . $secret_key);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $apiUrlToken,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "grant_type=client_credentials",
                    CURLOPT_HTTPHEADER => array(
                        "authorization: Basic " . $auth,
                        "content-type: application/x-www-form-urlencoded",
                    ),
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                $output = json_decode($response);
                $access_token = $output->accessToken;

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $b2binpay_action_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => '{
                        "paymentMethodId": "' . $paymentMethodId . '",
                        "channel": "' . $channel . '",
                        "currency": "' . $display_currency . '",
                        "amount": "' . $amount_value . '",
                        "language": "ES",
                        "orderId": "' . $order_id . '",
                        "description": "' . $description . '",
                        "returnUrl": "' . $returnUrl . '",
                        "cancelUrl": "' . $cancelUrl . '",
                        "notificationUrl": "' . $callBackUrl . '",
                        "customer": {
                            "country": "' . $request['country'] . '",
                            "firstName": "' . $contact_data['firstname'] . '",
                            "lastName": "' . $contact_data['lastname'] . '",
                            "city": "' . $request['city'] . '",
                            "email": "' . $contact_data['email'] . '",
                            "phone": "' . $request['phone'] . '",
                            "postcode": "' . $request['postcode'] . '",
                            "address": "' . $request['address'] . '",
                            "personalId": "' . $personalId . '",
                            "ip": "' . $IP . '"
                        }
                    }',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer ' . $access_token,
                        'Content-Type: application/json',
                    ),
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                $responseAPI = json_decode($response);

                // echo "<pre>";
                // print_r($responseAPI);
                // exit;

                if ($responseAPI->status == 'PENDING' && isset($responseAPI->paymentForm->action)) {
                    $redirectUrl = $responseAPI->paymentForm->action;
                    $request['redirectUrl'] = $redirectUrl;
                    $request['token'] = $access_token;
                    $request['order_data'] = $responseAPI;
                    if (PaymentProvidersHelper::createPaymentLog($order_id, $this->getName(), $request['payment_from'], $request, 'Created', 'Order Creation')) {
                        $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Redirect', 'redirect_url' => $redirectUrl, 'order_id' => $order_id, 'order_data' => $responseAPI));
                    } else {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module, $portal_language));
                    }
                } else if ($responseAPI->status == 400) {
                    $res = array('success' => false, 'message' => $responseAPI->errors[0]->message);
                    // if (PaymentProvidersHelper::createPaymentLog($order_id, $this->getName(), $responseAPI, $request, 'Created', 'Token Creaton')) {
                    //     $res = array('success' => false, 'message' => $responseAPI->errors[0]->message);
                    // } else {
                    //     $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module, $portal_language));
                    // }
                } else {
                    $res = array('success' => false, 'message' => $responseAPI);
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

    public function labelChangeWithFormData($form_data, $values) {
        if ($values['country'] == 'BR' || $values['country'] == 'CL') {
            $formDataUpdated = array();
            foreach ($form_data as $key => $item) {
                if ($item['name'] == 'personalId') {
                    if ($values['country'] == 'BR') {
                        $item['label'] = 'CAB_LBL_CPF';
                    }
                    if ($values['country'] == 'CL') {
                        $item['label'] = 'CAB_LBL_RUT';
                    }
                }
                $formDataUpdated[] = $item;
            }
            return $formDataUpdated;
        } else {
            foreach ($form_data as $key => $item) {
                if ($item['name'] != 'personalId') {
                    $formDataUpdated[] = $item;
                }
            }
            return $formDataUpdated;
        }
        return $form_data;
    }

    public function unsetPersonalIDOnCountryNotSet($values) {
        if ($values['country'] == 'BR' || $values['country'] == 'CL') {
        } else {
            unset($values['personalId']);
        }
        $country = $values['country'];
        if (!empty($values['country'])) {
            $values['country'] = $this->getActualCountryNameAndLabel('Label', $values['country']);
        }
        if (!empty($values['fairpay_payment_method'])) {
            $values['fairpay_payment_method'] = $this->getActualMethodNameAndLabel('Label', $values['fairpay_payment_method'], $country);
        }
        return $values;
    }

    public function getActualCountryNameAndLabel($type, $countryName) {
        $countryArr = $this->getCountryAndPaymentMethodArray();
        $countryArr = $countryArr['countryArr'];
        foreach ($countryArr as $key => $country) {
            if ($type == 'Label') {    
                if ($countryName == $country['value']) {
                    return $country['label'];
                }
            }
            if ($type == 'Value') {
                if ($countryName == $country['label']) {
                    return $country['value'];
                }
            }
        }
    }

    public function getActualMethodNameAndLabel($type, $payMethod, $country) {
        $methodArr = $this->getCountryAndPaymentMethodArray();
        $methodArr = $methodArr['methodArr'];
        foreach ($methodArr as $key => $method) {
            if ($type == 'Label') {    
                if ($payMethod == $method['value']) {
                    return $method['label'];
                }
            }
            if ($type == 'Value') {                
                if ($payMethod == $method['label']) {
                    $explode = explode("_", $method['value']);
                    if ($country == $explode[0]) {
                        return $method['value'];
                    }
                }
            }
        }
    }    

    public function getCountryAndPaymentMethodArray() {
        $depositArray = self::$DEPOSIT_FORM_PARAMETERS;
        foreach ($depositArray as $key => $depArr) {
            if ($depArr['name'] == 'country') {
                $countryArr = $depArr['picklist'];
            }
            if ($depArr['name'] == 'fairpay_payment_method') {
                $methodArr = $depArr['picklist'];
            }
        }
        return array('countryArr' => $countryArr, 'methodArr' => $methodArr);
    }

    public function getCountryName()
    {
        $countryName = array(
            array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
            array("isAllow" => "true", "value" => "BR", "label" => "Brazil"),
            array("isAllow" => "true", "value" => "CL", "label" => "Chile"),
            array("isAllow" => "true", "value" => "CR", "label" => "Costa Rica"),
            array("isAllow" => "true", "value" => "EC", "label" => "Ecuador"),
            array("isAllow" => "true", "value" => "SV", "label" => "El Salvador"),
            array("isAllow" => "true", "value" => "MX", "label" => "Mexico"),
            array("isAllow" => "true", "value" => "PA", "label" => "Panama"),
            array("isAllow" => "true", "value" => "PE", "label" => "Peru"),
            array("isAllow" => "true", "value" => "GT", "label" => "Guatemala"),
        );
        return $countryName;
    }

    public function getPaymentMethods()
    {
        $countryWithPaymentMethod = array(
            array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
            array("isAllow" => "true", "value" => "CL_CASH_1042", "label" => "Lider - CASH"),
            array("isAllow" => "true", "value" => "CL_CASH_1011", "label" => "aCuenta - CASH"),
            array("isAllow" => "true", "value" => "CL_CASH_1043", "label" => "ServiEstado - CASH"),
            array("isAllow" => "true", "value" => "CL_CASH_1044", "label" => "Caja Vecina - CASH"),
            array("isAllow" => "true", "value" => "CL_ONLINE_1033", "label" => "Santander Chile - ONLINE"),
            array("isAllow" => "true", "value" => "CL_ONLINE_1010", "label" => "Banco TBANC - ONLINE"),
            array("isAllow" => "true", "value" => "CL_ONLINE_1004", "label" => "Banco BCI - ONLINE"),
            array("isAllow" => "true", "value" => "CR_CASH_1008", "label" => "Banco Nacional - CASH"),
            array("isAllow" => "true", "value" => "CR_ONLINE_1008", "label" => "Banco Nacional - ONLINE"),
            array("isAllow" => "true", "value" => "CR_ONLINE_1022", "label" => "Grupo Mutual - ONLINE"),
            array("isAllow" => "true", "value" => "CR_CASH_1022", "label" => "Grupo Mutual - CASH"),
            array("isAllow" => "true", "value" => "CR_ONLINE_1021", "label" => "Mucap - ONLINE"),
            array("isAllow" => "true", "value" => "CR_CASH_1021", "label" => "Mucap - CASH"),
            array("isAllow" => "true", "value" => "CR_CASH_1039", "label" => "Teledolar MN - CASH"),
            array("isAllow" => "true", "value" => "CR_ONLINE_1005", "label" => "Banco Cathay - ONLINE"),
            array("isAllow" => "true", "value" => "CR_CASH_1005", "label" => "Banco Cathay - CASH"),
            array("isAllow" => "true", "value" => "MX_ONLINE_1001", "label" => "BBVA Bancomer - ONLINE"),
            array("isAllow" => "true", "value" => "MX_CASH_1001", "label" => "BBVA Bancomer - CASH"),
            array("isAllow" => "true", "value" => "MX_CASH_1023", "label" => "HSBC Mexico - CASH"),
            array("isAllow" => "true", "value" => "MX_CASH_1025", "label" => "OpenPay - CASH"),
            array("isAllow" => "true", "value" => "MX_CASH_1003", "label" => "Banco Azteca - CASH"),
            array("isAllow" => "true", "value" => "MX_ONLINE_1030", "label" => "SPEI MX - ONLINE"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1002", "label" => "BBVA Continental - ONLINE"),
            array("isAllow" => "true", "value" => "PE_CASH_1002", "label" => "BBVA Continental - CASH"),
            array("isAllow" => "true", "value" => "PE_CASH_1038", "label" => "Tambo - CASH"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1012", "label" => "Banco de CrÃ©dito - ONLINE"),
            array("isAllow" => "true", "value" => "PE_CASH_1012", "label" => "Banco de CrÃ©dito - CASH"),
            array("isAllow" => "true", "value" => "PE_CASH_1034", "label" => "Scotiabank Peru - CASH"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1034", "label" => "Scotiabank Peru - ONLINE"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1024", "label" => "Interbank - ONLINE"),
            array("isAllow" => "true", "value" => "PE_CASH_1024", "label" => "Interbank - CASH"),
            array("isAllow" => "true", "value" => "PE_CASH_1016", "label" => "Caja Tacna - CASH"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1016", "label" => "Caja Tacnav"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1015", "label" => "Caja Huancayo - ONLINE"),
            array("isAllow" => "true", "value" => "PE_CASH_1015", "label" => "Caja Huancayo - CASH"),
            array("isAllow" => "true", "value" => "PE_CASH_1040", "label" => "Western Union - CASH"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1014", "label" => "Caja Arequipa - ONLINE"),
            array("isAllow" => "true", "value" => "PE_CASH_1014", "label" => "Caja Arequipa - CASH"),
            array("isAllow" => "true", "value" => "SV_CASH_1028", "label" => "Punto Xpress SLV - CASH"),
            array("isAllow" => "true", "value" => "PE_ONLINE_1017", "label" => "Caja Trujillo - ONLINE"),
            array("isAllow" => "true", "value" => "BR_CASH_1083", "label" => "Boleto - CASH"),
            array("isAllow" => "true", "value" => "BR_CASH_1084", "label" => "Boleto Flash Itau - CASH"),
            array("isAllow" => "true", "value" => "BR_ONLINE_1099", "label" => "Direct PIX - ONLINE"),
            array("isAllow" => "true", "value" => "EC_CASH_1009", "label" => "Banco Pichincha - CASH"),
            array("isAllow" => "true", "value" => "EC_ONLINE_1009", "label" => "Banco Pichincha - ONLINE"),
            array("isAllow" => "true", "value" => "EC_ONLINE_1007", "label" => "Banco Guayaquil - ONLINE"),
            array("isAllow" => "true", "value" => "EC_CASH_1007", "label" => "Banco Guayaquil - CASH"),
            array("isAllow" => "true", "value" => "PA_CASH_1041", "label" => "Western Union - CASH"),
            array("isAllow" => "true", "value" => "CL_CASH_1020", "label" => "Express Lider - CASH"),
            array("isAllow" => "true", "value" => "PE_CASH_1029", "label" => "Banco Ripley - CASH"),
            array("isAllow" => "true", "value" => "PE_CASH_1017", "label" => "Caja Trujillo - CASH"),
            array("isAllow" => "true", "value" => "GT_CASH_1085", "label" => "Banco Industrial - CASH"),
            array("isAllow" => "true", "value" => "GT_ONLINE_1085", "label" => "Banco Industrial - ONLINE"),
        );
        return $countryWithPaymentMethod;
    }

    public function getPaymentMethods16_07_2021($api_key, $secret_key, $apiUrlToken, $apiUrlPaymentMethod)
    {
        session_start();
        $auth = base64_encode($api_key . ':' . $secret_key);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrlToken,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_HTTPHEADER => array(
                "authorization: Basic " . $auth,
                "content-type: application/x-www-form-urlencoded",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $output = json_decode($response);

        if ($err) {
            // return "cURL Error #:" . $err;
        } else {
            $paymentMethods = [];
            $access_token = $output->accessToken;
            $_SESSION['fairPayAccessToken'] = $access_token;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $apiUrlPaymentMethod,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "authorization: Bearer " . $access_token,
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $paymentMethods = json_decode($response, true);
            if ($err) {
                // return "cURL Error #:" . $err;
            } else {
                return $paymentMethods;
            }
        }
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
    
    public function paymentCallbackHandler($callbackHandlerData = array())
    {
        global $log;
        $log->debug('Entering into match2pay paymentCallbackHandler...');
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
                    case 'paid':
                        $paymentStatus = 'Success';
                        $autoConfirm = $this->getParameter('auto_confirm');
                        if ($autoConfirm == 'No')
                        {
                            $paymentStatus = 'PaymentSuccess';
                        }
                        break;
                    case 'failed':
                        $paymentStatus = 'Failed';
                        $recordModel->set('failure_reason', $failedReason);
                        break;
                    case 'rejected':
                        $paymentStatus = 'Rejected';
                        break;
                    case 'expired':
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
