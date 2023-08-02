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

class ServiceProviders_VirtualPayMobPay_Provider extends ServiceProviders_AbstractPaymentGatways_Model {

	protected $module = 'Payments';
	protected $translate_module = 'CustomerPortal_Client'; // Common label file
	private static $REQUIRED_PARAMETERS = array(
		array('name' => 'mid', 'label' => 'MID', 'type' => 'text', 'mandatory' => true),
		array('name' => 'api_key', 'label' => 'API Key', 'type' => 'text', 'mandatory' => true),
		array('name' => 'private_key', 'label' => 'Private Key', 'type' => 'text', 'mandatory' => true),
		array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
		array('name' => 'test_url', 'label' => 'Test URL', 'type' => 'text', 'mandatory' => true),
		array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),
                array('name' => 'bank_currency', 'label' => 'CAB_LBL_BANK_CURRENCY', 'type' => 'text', 'block' => self::TRANSFER_DETAILS_BLOCK, 'mandatory' => true),
                array('name' => 'currency_conversion', 'label' => 'Currency Conversion Tool', 'type' => 'picklist','picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
	);
        private static $VIRTUAL_MOB_PAY_ALLOWED_COUNTRY = array(
            'Kenya' => array('country_code' => 'KE','currency' => 'KES'),
            'Uganda' => array('country_code' => 'UG','currency' => 'UGX'),
            'Tanzania' => array('country_code' => 'TZ','currency' => 'TZS'),
            'Ghana' => array('country_code' => 'GH','currency' => 'GHS'),
        );
        
	private static $DEPOSIT_FORM_PARAMETERS = array(
		array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'required' => true, 'mandatory' => true),
                array('name' => 'conversion_rate', 'label' => 'CAB_LBL_CONVERSION_RATE', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
                array('name' => 'bank_amount', 'label' => 'CAB_LBL_BANK_AMOUNT', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
                array('name' => 'bank_currency', 'label' => 'CAB_LBL_BANK_CURRENCY', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
		array('name' => 'country', 'label' => 'CAB_LBL_COUNTRY', 'type' => 'dropdown_depended', 'picklist' => array(
			array("isAllow" => "true", "value" => "", "label" => "Select an option"),
			array("isAllow" => "true", "value" => "Ghana", "label" => "Ghana"),
			array("isAllow" => "true", "value" => "Kenya", "label" => "Kenya"),
			array("isAllow" => "true", "value" => "Tanzania", "label" => "Tanzania"),
			array("isAllow" => "true", "value" => "Uganda", "label" => "Uganda"),
			), 'required' => true, 'dependency' => 'virtualpay_country'),
		array('name' => 'city', 'label' => 'CAB_LBL_CITY', 'type' => 'text', 'required' => true, 'allow' => 'character', 'mandatory' => true),
	);
	private static $WITHDRAW_FORM_PARAMETERS = array(
		array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'required' => true, 'mandatory' => true)
	);

	/**
	* Function to get provider name
	* @return <String> provider name
	*/
	public function getName() {
		return 'VirtualPayMobPay'; // don't take name with any space or special charctor
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

		// $order_id = PaymentProvidersHelper::generateUUID(); //Generated the unique order id from database

		$order_id = $this->generateRandomString();

		if (!$order_id) {
			return array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_GENERATING_ORDER_ID', $this->module, $portal_language));
		}

		if (!empty($request)) {
			//Get response

			// $returnUrl = $PORTAL_URL . "api/payment?result=paymentcallback&orid=" . $order_id . "&pm=" . $request['payment_from'];
			$returnUrl = $PORTAL_URL . "#/payments/paymentcallback?orid=" . $order_id . "&pm=" . $request['payment_from'];
			$callBackUrl = $site_URL . 'modules/CustomerPortal/thirdparty/payment_callback.php?pm=' . $request['payment_from'] . '&order_id=' . $order_id;

			if ($request['is_mobile_request']) {
				$returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
				$cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
			}
			if ($request['payment_operation'] == 'Deposit') {
				session_start();

				$provider = ServiceProvidersManager::getActiveInstanceByProvider($request['payment_from']);
//				 $amount_value = number_format($request['net_amount'], $provider->parameters['allowed_decimal'], '.', '');
				$amount_value = $request['net_amount'];

				$mid = $provider->parameters['mid'];
				$api_key = $provider->parameters['api_key'];
				$private_key = $provider->parameters['private_key'];
				$test_url = $provider->parameters['test_url'];
				$base_url = $provider->parameters['base_url'];
				$description = $provider->parameters['description'];
				$display_currency =  $request['payment_currency'];
				$email = $request['email'];
				$country = $request['country'];
				$city = $request['city'];

				if ($provider->parameters['test_mode'] == 'Yes') {
					$virtualpay_action_url = $test_url;
				} else {
					$virtualpay_action_url = $base_url;
				}

				$contact_data = PaymentProvidersHelper::getContactDetails($request['contactid']);
				$firstName = '';
				$lastName = '';
				$mobile = '';
				if ($contact_data != false && !empty($contact_data)) {
					$firstName = $contact_data['firstname'];
					$lastName = $contact_data['lastname'];
					$mobile = $contact_data['mobile'];
				}
                                
                                /*Virtual Mob pay condition*/
                                if(array_key_exists($country, self::$VIRTUAL_MOB_PAY_ALLOWED_COUNTRY))
                                {
                                    $display_currency = self::$VIRTUAL_MOB_PAY_ALLOWED_COUNTRY[$country]['currency'];
                                    $country = self::$VIRTUAL_MOB_PAY_ALLOWED_COUNTRY[$country]['country_code'];
                                    
                                    if(strtolower($display_currency) != 'usd')
                                    {
                                        $conversionDetail = getCurrencySupportConvertor($amount_value,'USD',$display_currency,'Deposit');
                                        $amount_value = $conversionDetail['converted_amount'];
                                        $amount_value = str_replace(',','',$amount_value);
                                    }
                                }
                                /*Virtual Mob pay condition*/
                                
                                
				$virtualPayFormParam = array(
					'MID' => $mid,
					'API_KEY' => $api_key,
					'PRIVATE_KEY' => $private_key,
					'REDIRECT_URL' => $returnUrl,
					'NOTIFICATION_URL' => $callBackUrl,
					'FIRST_NAME' => $firstName,
					'LAST_NAME' => $lastName,
					'REQUESTID' => $order_id,
					'MOBILE' => $mobile,
					'EMAIL' => $email,
					'ID' => '',
					'CITY' => $city,
					'COUNTRY' => $country,
					'AMOUNT' => $amount_value,
					'CURRENCY' => $display_currency,
					'DESCRIPTION' => $description,
					'POSTAL CODE' => '',
					'STATE CODE' => '',
					'PAYMENT_TYPE' => 'Mobile',/*Mobile Or Card*/
				);

				$result_form = "<form id='payment_form' name='virtualpay' action='" . $virtualpay_action_url . "' method='post'>";
				foreach ($virtualPayFormParam as $key => $value) {
					$result_form .= "<input type='hidden' name='$key' value='$value'/>";
				}
				$result_form .= "</form>";

				if ($virtualPayFormParam) {
					if (PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Created', 'Form Generation')) {
						$res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Form', 'redirect_url' => $virtualpay_action_url, 'order_id' => $order_id, 'result_form' => $result_form));
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
		global $log,$adb;
		$log->debug('Entering into paymentResponseVerification');
		$log->debug($payment_response);
		if (PaymentProvidersHelper::getPaymentRecord($order_id)) {
			//Verify payment response from stored callback
			$query = "SELECT * FROM vtiger_payment_logs WHERE order_id = ? AND provider_type = ? AND event = ? ORDER BY id DESC LIMIT 0,1";
			$callbackResult = $adb->pquery($query, array($order_id, $payment_response['pm'], 'VirtualPayMobPay callback'));
			$noOfCallback = $adb->num_rows($callbackResult);
			if($noOfCallback > 0)
			{
				$callbackResponseJson = $adb->query_result($callbackResult, 0, 'data');
				$callbackResponse = json_decode($callbackResponseJson, true);
				$transactionStatus = $callbackResponse['Body']['stkCallback']['ResultCode'];
			}
			
			if ($transactionStatus == '0') {
				$status = 'Success';
			} else {
				$status = 'Failed';
				$payment_response['message'] = $payment_response['responsedescription'];
			}

			if ($status == 'Success') {
				$payment_response['message'] = vtranslate('CAB_MSG_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $this->module, $portal_language);
				$res = array('success' => true, 'payment_status' => 'Confirmed', 'message' => $payment_response['message']);
			} else if ($status == 'Failed') {
				/*
				If user click on back to website button or do any other action to cancel transaction then we are considering transaction as a pending
				After sometime we will run cron and update transaction accordingly status available in thirdparty.
				*/
				if (!isset($payment_response['message'])) {
					$status = 'Pending';
					$payment_response['message'] = vtranslate('CAB_MSG_PAYMENT_IN_PENDING', $this->module, $portal_language);
				}
				$res = array('success' => true, 'payment_status' => $status, 'message' => $payment_response['message']);
			}
			PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $payment_response['pm'], $payment_response, $status, $payment_response['message']);
		} else {
			$status == 'Failed';
			$res = array('success' => false, 'payment_status' => $status, 'message' => vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language));
		}
		return $res;
	}

	public function generateRandomString($length = 9) {
		$time = time();
    	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString . '-' . $time;
	}

	public function getCurrencyFromCountry($country = '') {
            $currency = '';
            if(!empty($country))
            {
                $currency = self::$VIRTUAL_MOB_PAY_ALLOWED_COUNTRY[$country]['currency'];
            }
	    return $currency;
	}

	public function getPendingRecordDurationQuery() {
		$query = " AND vtiger_crmentity.createdtime BETWEEN DATE_SUB(CONVERT_TZ(NOW(), @@session.time_zone, '+00:00'),INTERVAL 24 HOUR) AND DATE_SUB(CONVERT_TZ(NOW(), @@session.time_zone, '+00:00'),INTERVAL 2 MINUTE)";
		return $query;
	}

	public function getPendingRecordHandlerQuery() {
		$duration = "24 HOUR";
		$testMode = $this->getParameter('test_mode');
		if(strtolower($testMode) == 'yes')
		{
			$duration = "5 MINUTE";
		}
		$query = " AND vtiger_crmentity.createdtime <= DATE_SUB(CONVERT_TZ(NOW(), @@session.time_zone, '+00:00'),INTERVAL $duration)";
		return $query;
	}

	public function getPaymentCurrentStatus($paymentData = array())
	{
		global $adb,$log;$log->debug('Entering into getPaymentCurrentStatus..');$log->debug($paymentData);
		$status = "";
		$paymentStatusResponse = $callbackData = array();
		$orderId = $paymentData['order_id'];
		$testMode = $this->getParameter('test_mode');
		$username = $this->getParameter('mid');
		$password = $this->getParameter('api_key');
		$actionUrl = "https://evirtualpay.com:7443/validate/mobile";
		if(strtolower($testMode) == 'yes')
		{
			$actionUrl = "https://uat.evirtualpay.com:8443/validate/mobile";
		}
		if (!empty($orderId)) {
			$thirdPartyPayStatus = array('0' => 'success', '1032' => 'failed', '01' => 'cancelled');
			$authorization = base64_encode($username.':'.$password);
			$headers = [
				"Authorization: Basic $authorization",
				"Content-Type: application/json"
			];
			$params = array(
				'requestID' => $orderId,
				'merchantID' => $username
			);
			$callbackData = $this->sendCurlRequest($actionUrl, 'POST', '', $headers, json_encode($params));$log->debug('$callbackData=');$log->debug($callbackData);
			$thirdPartyResponseStatus = $callbackData['responseCode'];
			$paymentStatusResponse['data'] = $callbackData;
			$paymentStatusResponse['status'] = $thirdPartyPayStatus[$thirdPartyResponseStatus];
		}
		return $paymentStatusResponse;
	}

	public function sendCurlRequest(string $url, string $method, string $path, array $headers, $params)
    {
        global $log;
        $ch = curl_init();

        if(strtolower($method) == 'get')
        {
            curl_setopt_array($ch, [
                //CURLOPT_URL => "https://" . $node . ".b2binpay.com/" . $path,
                CURLOPT_URL => $url . $path,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 50,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
            ]);
        }
        else
        {
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
        }
        $response = curl_exec($ch);$log->debug('$response===');$log->debug($response);

        curl_close($ch);

        return (false === $response) ? false : json_decode($response, true);
    }

	public function paymentCallbackHandler($callbackHandlerData = array()) {
		global $log;
		$log->debug('Entering into virtualmobpay paymentCallbackHandler...');
		$callbackResponse = '';
		$callbackResponseJson = file_get_contents('php://input');$log->debug($callbackResponseJson);
		if(!empty($callbackResponseJson))
		{
			$callbackResponse = json_decode($callbackResponseJson, true);$log->debug($callbackResponse);
		}
		$callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['payment_method'], $callbackHandlerData['payment_method'], $callbackResponse);
		return $callbackLogStatus;
	}
}

?>