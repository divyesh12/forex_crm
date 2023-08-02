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

class ServiceProviders_Match2Pay_Provider extends ServiceProviders_AbstractPaymentGatways_Model {

	protected $module = 'Payments';
	protected $translate_module = 'CustomerPortal_Client'; // Common label file
	private static $REQUIRED_PARAMETERS = array(
		array('name' => 'api_token', 'label' => 'API Token', 'type' => 'text', 'mandatory' => true),
		array('name' => 'api_secret_key', 'label' => 'API Secret Key', 'type' => 'text', 'mandatory' => true),		
		array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
		array('name' => 'test_url', 'label' => 'Test URL', 'type' => 'text', 'mandatory' => true),
		array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),
	);
	private static $DEPOSIT_FORM_PARAMETERS = array(
		array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'required' => true, 'mandatory' => true),
		array('name' => 'crypto_currency', 'label' => 'CAB_LBL_CRYPTOCURRENCY', 'type' => 'dropdown_depended', 'picklist' => array(
            array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
            array("isAllow" => "true", "value" => "BTC-BTC", "label" => "Bitcoin"),
            array("isAllow" => "true", "value" => "USX-USDT TRC20", "label" => "USDT (TRC20)"),
            array("isAllow" => "true", "value" => "USB-BUSD BEP20", "label" => "BUSD (BEP20)"),
			array("isAllow" => "true", "value" => "USB-USDC BEP20", "label" => "USDC (BEP20)"),
			array("isAllow" => "true", "value" => "UCX-USDC TRC20", "label" => "USDC (TRC20)"),
        ), 'required' => true, 'dependency' => 'fairpay_payment_method'),
	);
	private static $WITHDRAW_FORM_PARAMETERS = array(
		array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'placeholder' => "", 'required' => true, 'mandatory' => true),
	);

	/**
	* Function to get provider name
	* @return <String> provider name
	*/
	public function getName() {
		return 'Match2Pay'; // don't take name with any space or special charctor
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
			//Get response

			$returnUrl = $PORTAL_URL . "#/payments/paymentcallback?orid=" . $order_id . "&pm=" . $request['payment_from'];
			$callBackUrl = $site_URL . 'modules/CustomerPortal/thirdparty/payment_callback.php?pm=' . $request['payment_from'] . '&order_id=' . $order_id;

			if ($request['is_mobile_request']) {
				$returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
				$cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
			}
			if ($request['payment_operation'] == 'Deposit') {
				session_start();

				$provider = ServiceProvidersManager::getActiveInstanceByProvider($request['payment_from']);
				// $amount_value = number_format($request['net_amount'], $provider->parameters['allowed_decimal'], '.', '');
				$amount_value = $request['net_amount'];
				$api_token = $provider->parameters['api_token'];
				$api_secret_key = $provider->parameters['api_secret_key'];
				$testURL = $provider->parameters['test_url'];
				$baseURL = $provider->parameters['base_url'];
				$description = $provider->parameters['description'];
				$display_currency =  $request['payment_currency'];
				$email = $request['email'];
				$timestamp = time(); 
				
				$cryptoCurrency = explode('-', $request['crypto_currency']);
				$paymentCurrency = $cryptoCurrency[0];
				$paymentGatewayName = $cryptoCurrency[1];

				$signatureStr = $amount_value . $api_token . $callBackUrl . $display_currency . $paymentCurrency . $paymentGatewayName . $timestamp . $api_secret_key;
				$signature = hash('sha384', $signatureStr);

				if ($provider->parameters['test_mode'] == 'Yes') {
					$actionURL = $testURL;
				} else {
					$actionURL = $baseURL;
				}
			
				$apiURL = $actionURL . '/api/v2/deposit/crypto_agent';
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $apiURL,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => '{
						"amount" : "' . $amount_value . '",
						"currency" : "' . $display_currency . '",
						"paymentGatewayName" : "' . $paymentGatewayName . '",
						"paymentCurrency" : "' . $paymentCurrency . '",
						"callbackUrl" : "' . $callBackUrl . '",
						"apiToken" : "' . $api_token . '",
						"timestamp" : "' . $timestamp . '",
						"signature":"' . $signature . '"
                    }',
                    CURLOPT_HTTPHEADER => array(
                        "content-type: application/json",
                    ),
                ));

                $response = curl_exec($curl);
                curl_close($curl);
                $responseAPI = json_decode($response);

				// pr($responseAPI);


				if (isset($responseAPI->status) && $responseAPI->status == 'NEW' && isset($responseAPI->checkoutUrl)) {
                    $paymentId = $responseAPI->paymentId;
					$redirectUrl = $responseAPI->checkoutUrl;                                        
					$request['redirectUrl'] = $redirectUrl;
					$request['order_data'] = $responseAPI;
					if (PaymentProvidersHelper::createPaymentLog($order_id, $this->getName(), $request['payment_from'], $request, 'Created', 'Order Creation')) {
						$res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Redirect', 'redirect_url' => $redirectUrl, 'order_id' => $order_id, 'order_data' => $responseAPI));
					} else {
						$res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module, $portal_language));
					}
                } else if ($responseAPI->error) {
					$request['error_message'] = $responseAPI->error;
					if (!PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Created', 'Token Creaton')) {
						$res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module));
					} else {
						$res = array('success' => false, 'message' => $request['error_message']);
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
		if (PaymentProvidersHelper::getPaymentRecord($order_id)) {
			$provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_response['pm']);
			//Verify payment response
			session_start();
			if (isset($payment_response['result']) && ($payment_response['result'] == '00' || $payment_response['result'] == '0')) {
				// $mid = $provider->parameters['mid'];
				// $base64Hash = hash('sha256', $order_id.$mid, true);
				// $signature = base64_encode($base64Hash);
				// if ($signature != $payment_response['signature']) {
				// 	$status = 'Failed';
				// }
				$status = 'Success';
			} else {
				$status = 'Failed';
				$payment_response['message'] = $payment_response['responsedescription'];
			}

			if ($status == 'Success') {
				$payment_response['message'] = vtranslate('CAB_MSG_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $this->module, $portal_language);
				$res = array('success' => true, 'payment_status' => 'Confirmed', 'message' => $payment_response['message']);
			}
			if ($status == 'Failed') {
				if (!isset($payment_response['message'])) {
					$status = 'Cancelled';
					$payment_response['message'] = vtranslate('CAB_MSG_CANCELLED_BY_USER', $this->module, $portal_language);
				}
				$res = array('success' => false, 'payment_status' => $status, 'message' => $payment_response['message']);
			}
			if (PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $payment_response['pm'], $payment_response, $status, $payment_response['message'])) {

			}
		} else {
			$status == 'Failed';
			$res = array('success' => false, 'payment_status' => $status, 'message' => vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language));
		}
		return $res;
	}

	public function generateRandomString($length = 9) {
    	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
        
        public function getPendingRecordDurationQuery() {
            $query = " AND vtiger_crmentity.createdtime BETWEEN DATE_SUB(NOW(),INTERVAL 48 HOUR) AND NOW()";
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
            $paymentStatusResponse = $callbackData = array();
            $orderId = $paymentData['order_id'];
            if (!empty($orderId)) {
                $thirdPartyPayStatus = array('done' => 'success', 'failed' => 'failed');
                
                $paymentLogQuery = "SELECT data FROM vtiger_payment_logs WHERE order_id = ? AND event = ? ORDER BY id DESC LIMIT 0,1";
                $paymentLogQueryResult = $adb->pquery($paymentLogQuery, array($orderId, 'Match2Pay Callback Response'));
                $noOfRecordlog = $adb->num_rows($paymentLogQueryResult);
                if($noOfRecordlog > 0)
                {
                    $callbackJsonData = $adb->query_result($paymentLogQueryResult, 0, 'data');
                    $callbackJsonData = html_entity_decode($callbackJsonData);
                    $callbackData = json_decode($callbackJsonData, true);
                    $status = $callbackData['json']['status'];
                }
                
                $thirdPartyResponseStatus = strtolower($status);
                $paymentStatusResponse['data'] = $callbackData;
                $paymentStatusResponse['status'] = $thirdPartyPayStatus[$thirdPartyResponseStatus];
            }
            return $paymentStatusResponse;
        }

        public function paymentCallbackHandler($callbackHandlerData = array()) {
            global $log;
            $log->debug('Entering into match2pay paymentCallbackHandler...');
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