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
require 'lib/razorpay-php/Razorpay.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class ServiceProviders_RazorPay_Provider extends ServiceProviders_AbstractPaymentGatways_Model
{

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file
    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'key_id', 'label' => 'Key ID', 'type' => 'text', 'mandatory' => true),
        array('name' => 'key_secret', 'label' => 'Secret Key', 'type' => 'text', 'mandatory' => true),
        array('name' => 'display_currency', 'label' => 'Base Currency', 'type' => 'text', 'mandatory' => true),
    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
//        array('name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true)
    );
    private static $WITHDRAW_FORM_PARAMETERS = array(
//        array('name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true)
    );

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName()
    {
        return 'RazorPay'; // don't take name with any space or special charctor
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

            $returnUrl = $PORTAL_URL . "#/payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            $cancelUrl = $PORTAL_URL . "#/payments/fail?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";

            if ($request['is_mobile_request']) {
                $returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
                $cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            }
            if ($request['payment_operation'] == 'Deposit') {
                session_start();

                // Create the Razorpay Order
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($request['payment_from']);
                //$amount_value = number_format($request['net_amount'], $provider->parameters['allowed_decimal'], '.', ''); //For 2, its round up the value

                $keyId = $provider->parameters['key_id'];
                $keySecret = $provider->parameters['key_secret'];
                $description = $provider->parameters['description'];

                $display_currency = $provider->parameters['display_currency'];

                $api = new Api($keyId, $keySecret);

                // We create an razorpay order using orders api
                // Docs: https://docs.razorpay.com/docs/orders

                if ($request['payment_currency'] !== $display_currency) {
                    $operation_type = 'Deposit';
                    $conversion_rate = PaymentProvidersHelper::getCurrencyRate($request['payment_currency'], $display_currency, $operation_type);
                    if ($conversion_rate) {
                        //Use custom currency convertor for it
                        $displayAmount = $conversion_rate * $request['net_amount'];
                    } else {
                        $displayAmount = 1 * $request['net_amount'];
                    }
                }

                $orderData = [
                    'receipt' => $order_id,
                    'amount' => round($displayAmount * 100), //2000 * 100, // 2000 rupees in paise
                    'currency' => $display_currency, //$request['payment_currency'],
                    'payment_capture' => 1, // auto capture
                ];

                $razorpayOrder = $api->order->create($orderData);

                $razorpayOrderId = $razorpayOrder['id'];

                $_SESSION['razorpay_order_id'] = $razorpayOrderId;

                $displayAmount = $amount = $orderData['amount'] / 100;

                $checkout = 'automatic'; //'manual';
                //Get contact details
                $contact_data = PaymentProvidersHelper::getContactDetails($request['contactid']);
                $name = '';
                $email = '';
                $mobile = '';
                if ($contact_data != false && !empty($contact_data)) {
                    $name = $contact_data['firstname'] . ' ' . $contact_data['lastname'];
                    $email = $contact_data['email'];
                    $mobile = $contact_data['mobile'];
                }
                //End
                $data = [
                    "key" => $keyId,
                    "amount" => $amount,
                    "name" => $name,
                    "description" => $description,
                    "image" => $site_URL . "test/logo/logo.png", //https://s29.postimg.org/r6dj1g85z/daft_punk.jpg
                    "prefill" => [
                        "name" => $name,
                        "email" => $email,
                        "contact" => $mobile,
                    ],
                    "modal" => [
                        // We should prevent closing of the form when esc key is pressed.
                        "escape" => false,
                    ],
                    "notes" => [
                        //"address" => "Hello World",
                        //"merchant_order_id" => "12312321",
                    ],
                    "theme" => [
                        "color" => "#F37254",
                    ],
                    "order_id" => $razorpayOrderId,
                    "callback_url" => $returnUrl,
                    //"callback_fail_url" => $cancelUrl
                    //"redirect" => true
                ];

                if ($request['payment_currency'] !== $display_currency) {
                    $data['display_currency'] = $display_currency;
                    $data['display_amount'] = $displayAmount;
                }

                if (!empty($data)) {
                    $redirectUrl = ''; ///Added at cabinet side in js
                    $request['order_data'] = $data;
                    if (PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Created', 'Order Creation')) {
                        $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Popup', 'redirect_url' => $redirectUrl, 'order_id' => $order_id, 'order_data' => $data, 'service_provider_type' => $this->getName()));
                    } else {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module, $portal_language));
                    }
                } else {
                    $res = array('success' => false, 'message' => $response->error->message);
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
    public function paymentResponseVerification($status, $payment_response, $order_id, $portal_language)
    {
        if (PaymentProvidersHelper::getPaymentRecord($order_id)) {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_response['pm']);
            //Verify payment response
            session_start();
            $error = "Payment Failed";
            if ($status === 'Success') {
                if (empty($payment_response['razorpay_payment_id']) === false) {

                    $keyId = $provider->parameters['key_id'];
                    $keySecret = $provider->parameters['key_secret'];

                    $api = new Api($keyId, $keySecret);
                    try {
                        // Please note that the razorpay order ID must
                        // come from a trusted source (session here, but
                        // could be database or something else)
                        $attributes = array(
                            'razorpay_order_id' => $payment_response['razorpay_order_id'],
                            'razorpay_payment_id' => $payment_response['razorpay_payment_id'],
                            'razorpay_signature' => $payment_response['razorpay_signature'],
                        );
                        $api->utility->verifyPaymentSignature($attributes);
                    } catch (SignatureVerificationError $e) {
                        $status = 'Failed';
                        $payment_response['message'] = 'Razorpay Error : ' . $e->getMessage();
                    }
                }
            }
//End
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
