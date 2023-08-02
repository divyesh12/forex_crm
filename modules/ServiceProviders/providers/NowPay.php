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

class ServiceProviders_NowPay_Provider extends ServiceProviders_AbstractPaymentGatways_Model {

	protected $module = 'Payments';
	protected $translate_module = 'CustomerPortal_Client'; // Common label file
	private static $REQUIRED_PARAMETERS = array(
		array('name' => 'api_key', 'label' => 'API Key', 'type' => 'text', 'mandatory' => true),
		array('name' => 'ipn_secret', 'label' => 'IPN Secret', 'type' => 'text', 'mandatory' => true),
		array('name' => 'fixed_rate', 'label' => 'Fixed Rate', 'type' => 'picklist','picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
		array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
		array('name' => 'test_url', 'label' => 'Test URL', 'type' => 'text', 'mandatory' => false),
		array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),
		array('name' => 'bank_currency', 'label' => 'CAB_LBL_BANK_CURRENCY', 'type' => 'text', 'block' => self::TRANSFER_DETAILS_BLOCK, 'mandatory' => true),
		array('name' => 'currency_conversion', 'label' => 'Currency Conversion Tool', 'type' => 'picklist','picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
	);
        
	private static $DEPOSIT_FORM_PARAMETERS = array(
		array('name' => 'pay_currency', 'label' => 'CAB_LBL_PAY_CURRENCY', 'type' => 'dropdown_depended', 'picklist' => array(
            array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
            array("isAllow" => "true", "value" => "btc", "label" => "BTC"),
            array("isAllow" => "true", "value" => "USDTTRC20", "label" => "USDT(TRC20)"),
        ), 'required' => true, 'dependency' => 'payment_method'),
		// array('name' => 'case', 'label' => 'CAB_LBL_CASE', 'type' => 'text', 'required' => false, 'display' => true),
		array('name' => 'conversion_rate', 'label' => 'CAB_LBL_CONVERSION_RATE', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
		array('name' => 'bank_amount', 'label' => 'CAB_LBL_BANK_AMOUNT', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
	);

	private static $WITHDRAW_FORM_PARAMETERS = array(
		array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'required' => true, 'mandatory' => true)
	);

	/**
	* Function to get provider name
	* @return <String> provider name
	*/
	public function getName() {
		return 'NowPay'; // don't take name with any space or special charctor
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
		global $PORTAL_URL, $site_URL, $log;

		$orderId = PaymentProvidersHelper::generateUUID(); //Generated the unique order id from database

		if (!$orderId) {
			return array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_GENERATING_ORDER_ID', $this->module, $portal_language));
		}

		if (!empty($request)) {
			//Get response
			$thirdPartyData = array();
			$returnUrl = $PORTAL_URL . "#/payments/paymentcallback?result=paymentcallback&orid=" . $orderId . "&pm=" . $request['payment_from'];
			$callBackUrl = $site_URL . 'modules/CustomerPortal/thirdparty/payment_callback.php?pm=' . $request['payment_from'] . '&order_id=' . $orderId;

			if ($request['is_mobile_request']) {
				$returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $orderId . "&pm=" . $request['payment_from'] . "";
				$cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $orderId . "&pm=" . $request['payment_from'] . "";
			}
			if ($request['payment_operation'] == 'Deposit') {
				// $amountValue = number_format($request['net_amount'], 2);
				$amountValue = $request['net_amount'];$log->debug('params-');$log->debug($this->parameters);
				$test_url = $this->parameters['test_url'];
				$base_url = $this->parameters['base_url'];
				$apiKey = $this->parameters['api_key'];
				$bankCurrency = $request['payment_currency'];
				$payCurrency = $request['pay_currency'];
				$fixedRate = ($this->parameters['fixed_rate'] == 'Yes') ? true : false;$log->debug('fixedrate-');$log->debug($fixedRate);
				$case = $request['case'];
				
				if ($this->parameters['test_mode'] == 'Yes') {
					$actionUrl = $test_url;
				} else {
					$actionUrl = $base_url;
				}
				$depositActionUrl = $actionUrl . "/v1/payment";
				$minAmountActionUrl = $actionUrl . "/v1/min-amount?currency_from=$bankCurrency&currency_to=$payCurrency";$log->debug($minAmountActionUrl);
				$getConversionActionUrl = $actionUrl . "/v1/estimate?amount=$amountValue&currency_from=$bankCurrency&currency_to=$payCurrency";$log->debug($getConversionActionUrl);

				$params = array(
					'price_amount' => $amountValue,
					'price_currency' => $bankCurrency,
					'pay_currency' => $payCurrency,
					'ipn_callback_url' => $callBackUrl,
					'order_id' => $orderId,
					'is_fixed_rate' => $fixedRate
				);
				if ($this->parameters['test_mode'] == 'Yes' && !empty($case)) {
					$params['case'] = strtolower($case);
				}
				$headers = [
					"x-api-key: $apiKey",
					"Content-Type: application/json"
				];
				/*Get minimum deposit amount*/
				$minDepositResponse = sendCustomCurlRequest($minAmountActionUrl, 'GET', "", $headers, array());
				$minDepositResponseAPI = json_decode($minDepositResponse);$log->debug($minDepositResponseAPI);
				if($minDepositResponseAPI->min_amount)
				{
					if($amountValue < $minDepositResponseAPI->min_amount)
					{
						$res = array('success' => false, 'message' => vtranslate('CAB_MSG_MINIMUM_DEPOSIT_AMOUNT_REQUIRED', $this->module).' '.$minDepositResponseAPI->min_amount);
						return $res;
					}
				}
				else
				{
					$res = array('success' => false, 'message' => $minDepositResponseAPI->message);
					return $res;
				}

				/*Get conversion from thirdparty*/
				$conversionResponse = sendCustomCurlRequest($getConversionActionUrl, 'GET', "", $headers, array());
				$conversionResponseAPI = json_decode($conversionResponse);$log->debug($conversionResponseAPI);
				if($conversionResponseAPI->estimated_amount)
				{
					$conversionRate = ($conversionResponseAPI->estimated_amount/$amountValue);
					$thirdPartyData['bank_amount'] = number_format($conversionResponseAPI->estimated_amount,6);
					$thirdPartyData['conversion_rate'] = number_format($conversionRate,6);
				}
				else
				{
					$res = array('success' => false, 'message' => $conversionResponseAPI->message);
					return $res;
				}

				/*Initiate transaction in thirdparty*/
				$response = sendCustomCurlRequest($depositActionUrl, 'POST', "", $headers, json_encode($params));
				$responseAPI = json_decode($response);$log->debug($responseAPI);
				if($responseAPI->statusCode != '400')
				{
					$thirdPartyData['response'] = $responseAPI;$log->debug('$thirdPartyData=');$log->debug($thirdPartyData);
					if(!empty($responseAPI->pay_address))
					{
						global $site_URL;
						$responseAPIArr = (array) $responseAPI;
						$responseAPIArr['return_url'] = $returnUrl;
						// $responseParams = "";
						// foreach($responseAPIArr as $respKey => $resValue)
						// {
						// 	if(!empty($responseParams))
						// 	$responseParams .= "&";
						// 	$responseParams .= "$respKey=$resValue";
						// }
						$responseParams = json_encode($responseAPIArr);
						$encrptedParams = base64_encode($responseParams);
						$redirectUrl = $site_URL . 'payment/nowpay_customer.php?' . $encrptedParams;$log->debug('$redirectUrl='.$redirectUrl);
						if (PaymentProvidersHelper::createPaymentLog($orderId, self::getName(), $request['payment_from'], $request, 'Created', 'Form Generation'))
						{
							$res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Redirect', 'redirect_url' => $redirectUrl, 'order_id' => $orderId, 'order_data' => $thirdPartyData));
						} 
						else
						{
							$res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module));
						}
					}
					else
					{
						$res = array('success' => false, 'message' => vtranslate('CAB_MSG_PAYMENT_ADDRESS_IS_BLANK', $this->module));
					}
				}
				else
				{
					$request['error_message'] = $responseAPI->message;
					PaymentProvidersHelper::createPaymentLog($orderId, self::getName(), $request['payment_from'], $request, 'Created', 'Token Creation error');
					$res = array('success' => false, 'message' => $request['error_message']);
				}
				
			} else if ($request['payment_operation'] == 'Withdrawal') {
				$res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Manual', 'redirect_url' => $returnUrl, 'order_id' => $orderId, 'message' => 'Withdrawal request has been sent successfully'));
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
		$payment_response['message'] = "Payment is in process!";
		$res = array('success' => true, 'payment_status' => 'Pending', 'message' => $payment_response['message']);
		return $res;
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
			$duration = "10 MINUTE";
		}
		$query = " AND vtiger_crmentity.createdtime <= DATE_SUB(CONVERT_TZ(NOW(), @@session.time_zone, '+00:00'),INTERVAL $duration)";
		return $query;
	}

	public function getPaymentCurrentStatus($paymentData = array())
	{
		global $adb,$log;$log->debug('Entering into getPaymentCurrentStatus..');$log->debug($paymentData);
		$paymentStatusResponse = $callbackData = array();
		$orderId = $paymentData['order_id'];
		$testMode = $this->getParameter('test_mode');
		$apiKey = $this->getParameter('api_key');
		$actionUrl = $this->getParameter('base_url');
		if(strtolower($testMode) == 'yes')
		{
			$actionUrl = $this->getParameter('test_url');
		}
		if (!empty($orderId)) {
			$paymentId = '';
			$paymentLogQuery = "SELECT data FROM vtiger_payment_logs WHERE provider_title = ? AND order_id = ? AND event = ? ORDER BY id DESC LIMIT 0,1";
			$paymentLogQueryResult = $adb->pquery($paymentLogQuery, array($paymentData['payment_from'], $orderId, 'Payment Callback Handler'));
			$noOfRecordlog = $adb->num_rows($paymentLogQueryResult);
			if($noOfRecordlog > 0)
			{
				$callbackJsonData = $adb->query_result($paymentLogQueryResult, 0, 'data');
				$callbackJsonData = html_entity_decode($callbackJsonData);
				$callbackData = json_decode($callbackJsonData, true);
				$paymentId = $callbackData['payment_id'];$log->debug('$paymentId-'.$paymentId);
			}

			if(!empty($paymentId))
			{
				$thirdPartyPayStatus = array('finished' => 'success', 'failed' => 'failed', 'expired' => 'cancelled');
				$actionUrl = $actionUrl . "/v1/payment/$paymentId";
				$headers = [
					"x-api-key: $apiKey",
					"Content-Type: application/json"
				];
				$params = array();$log->debug('$actionUrl=');$log->debug($actionUrl);
				$callbackJsonData = sendCustomCurlRequest($actionUrl, 'GET', "", $headers, $params);
				$callbackData = json_decode($callbackJsonData,TRUE);$log->debug('$callbackJsonData=');$log->debug($callbackJsonData);
				if($callbackData)
				{
					$thirdPartyResponseStatus = $callbackData['payment_status'];
					$paymentStatusResponse['data'] = $callbackData;
					$paymentStatusResponse['status'] = $thirdPartyPayStatus[$thirdPartyResponseStatus];
				}
			}
		}
		return $paymentStatusResponse;
	}

	public function paymentCallbackHandler($callbackHandlerData = array())
	{
		global $log;
		$log->debug('Entering into nowpay paymentCallbackHandler...');
		try
        {
			$callbackResponse = array();
			if (isset($_SERVER['HTTP_X_NOWPAYMENTS_SIG']) && !empty($_SERVER['HTTP_X_NOWPAYMENTS_SIG']))
			{
				$recivedHmac = $_SERVER['HTTP_X_NOWPAYMENTS_SIG'];
				$callbackResponseString = file_get_contents('php://input');$log->debug($callbackResponseString);
				if(!empty($callbackResponseString))
				{
					$callbackResponse = json_decode($callbackResponseString, true);
					$authStatus = $this->check_ipn_request_is_valid($callbackResponse, $recivedHmac);
					if($authStatus)
					{
						$thirdPartyPayStatus = array('finished' => 'success', 'failed' => 'failed', 'expired' => 'cancelled');
						$status = strtolower($thirdPartyPayStatus[$callbackResponse['payment_status']]);$log->debug('callback status-'.$status);
						$paymentStatus = '';						
						$failedReason = "Payment failed by nowpay callback";
						switch ($status) {
							case 'success':
								$paymentStatus = 'InProgress';
								$autoConfirm = $this->getParameter('auto_confirm');
								if ($autoConfirm == 'No')
								{
									$paymentStatus = 'PaymentSuccess';
								}
								break;
							case 'failed':
								$paymentStatus = 'Failed';
								$postData['failure_reason'] = $failedReason;
								break;
							case 'cancelled':
								$paymentStatus = 'Cancelled';
								$postData['failure_reason'] = $failedReason;
								break;
							case 'rejected':
								$paymentStatus = 'Rejected';
								$postData['failure_reason'] = $failedReason;
								break;
							default:
								break;
						}
						if(!empty($paymentStatus))
						{
							$postData['payment_status'] = $paymentStatus;
							$record = '39x' . $callbackHandlerData['record_id'];
                    		$recordUpdateStatus = updateRecord($postData, 'Payments', $record);
							if($recordUpdateStatus['success'])
							{
								$callbackResponse['message'] = "Callback: Success: Payment record status updated successfully.";
								$callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['provider_name'], $callbackHandlerData['provider_title'], $callbackResponse, $paymentStatus, 'Payment Callback Handler');
							}
							else
							{
								$callbackResponse['message'] = "Callback: Error: Callback Response. Payment record status not updated.";
								$callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['provider_name'], $callbackHandlerData['provider_title'], $callbackResponse, 'Failed', 'Payment Callback Handler');
							}
						}
						else
						{
							$callbackResponse['message'] = "Callback: Pending Callback Response.";
							$callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['provider_name'], $callbackHandlerData['provider_title'], $callbackResponse, 'Pending', 'Payment Callback Handler');
						}
						$ackOfCallback = json_encode(array('success' => true));
					}
					else
					{
						$ackOfCallback = json_encode(array('success' => false, 'msg' => 'Authentication failed of IPN callback!'));
					}
				}
				else
				{
					$ackOfCallback = json_encode(array('success' => false, 'msg' => 'Callback response blank!'));
				}
			}
			else
			{
				$ackOfCallback = json_encode(array('success' => false, 'msg' => 'No HMAC signature sent!'));
        	}
            return $ackOfCallback;
		}
		catch (Exception $e)
		{
			return json_encode(array('success' => false, 'msg' => $e->getMessage()));
		}
	}

	function check_ipn_request_is_valid($requestData, $recivedHmac)
    {
		global $log;
		$log->debug('Entering into check_ipn_request_is_valid');
        $auth_ok = false;
		ksort($requestData);
		$sorted_request_json = json_encode($requestData);
		$hmac = hash_hmac("sha512", $sorted_request_json, trim($this->parameters['ipn_secret']));
		$log->debug($recivedHmac);
		$log->debug($hmac);
		if ($hmac == $recivedHmac)
		{
			$auth_ok = true;
		}
		return $auth_ok;
    }
}

?>