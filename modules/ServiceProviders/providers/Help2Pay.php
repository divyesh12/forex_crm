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

class ServiceProviders_Help2Pay_Provider extends ServiceProviders_AbstractPaymentGatways_Model {

	protected $module = 'Payments';
	protected $translate_module = 'CustomerPortal_Client'; // Common label file
	private static $REQUIRED_PARAMETERS = array(
		array('name' => 'merchant_code', 'label' => 'Merchant', 'type' => 'text', 'mandatory' => true),
		array('name' => 'security_code', 'label' => 'Security Code', 'type' => 'text', 'mandatory' => true),
		array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
		array('name' => 'test_url', 'label' => 'Test URL', 'type' => 'text', 'mandatory' => false),
		array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),
		array('name' => 'bank_currency', 'label' => 'CAB_LBL_BANK_CURRENCY', 'type' => 'text', 'block' => self::TRANSFER_DETAILS_BLOCK, 'mandatory' => true),
		array('name' => 'currency_conversion', 'label' => 'Currency Conversion Tool', 'type' => 'picklist','picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
	);
        
	private static $DEPOSIT_FORM_PARAMETERS = array(
		array('name' => 'conversion_rate', 'label' => 'CAB_LBL_CONVERSION_RATE', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
		array('name' => 'bank_amount', 'label' => 'CAB_LBL_BANK_AMOUNT', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
		// array('name' => 'bank_currency', 'label' => 'CAB_LBL_BANK_CURRENCY', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
		array('name' => 'bank_currency', 'label' => 'CAB_LBL_BANK_CURRENCY', 'type' => 'dropdown_depended', 'picklist' => array(
            array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
            array("isAllow" => "true", "value" => "MYR", "label" => "MYR"),
            array("isAllow" => "true", "value" => "VND", "label" => "VND"),
            array("isAllow" => "true", "value" => "THB", "label" => "THB"),
            array("isAllow" => "true", "value" => "IDR", "label" => "IDR"),
            array("isAllow" => "true", "value" => "PHP", "label" => "PHP"),
        ), 'required' => true, 'dependency' => 'help2pay_bank_code'),
		array('name' => 'help2pay_bank_code', 'label' => 'Bank Code', 'type' => 'dropdown_depended', 'picklist' => array(
            array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
            array("isAllow" => "true", "value" => "MYR_AFF", "label" => "AFF (Affin Bank)"),
			array("isAllow" => "true", "value" => "MYR_ALB", "label" => "ALB (Alliance Bank Malaysia Berhad)"),
			array("isAllow" => "true", "value" => "MYR_AMB", "label" => "AMB (AmBank Group)"),
			array("isAllow" => "true", "value" => "MYR_BIMB", "label" => "BIMB (Bank Islam Malaysia Berhad)"),
			array("isAllow" => "true", "value" => "MYR_BSN", "label" => "BSN (Bank Simpanan Nasional)"),
			array("isAllow" => "true", "value" => "MYR_CIMB", "label" => "CIMB (CIMB Bank Berhad)"),
			array("isAllow" => "true", "value" => "MYR_HLB", "label" => "HLB (Hong Leong Bank Berhad)"),
			array("isAllow" => "true", "value" => "MYR_HSBC", "label" => "HSBC (HSBC Bank (Malaysia) Berhad)"),
			array("isAllow" => "true", "value" => "MYR_MBB", "label" => "MBB (Maybank Berhad)"),
			array("isAllow" => "true", "value" => "MYR_OCBC", "label" => "OCBC (OCBC Bank (Malaysia) Berhad)"),
			array("isAllow" => "true", "value" => "MYR_PBB", "label" => "PBB (Public Bank Berhad)"),
			array("isAllow" => "true", "value" => "MYR_RHB", "label" => "RHB (RHB Banking Group)"),
			array("isAllow" => "true", "value" => "MYR_UOB", "label" => "UOB (United Overseas Bank (Malaysia) Bhd)"),
			array("isAllow" => "true", "value" => "THB_BBL", "label" => "BBL (Bangkok Bank)"),
			array("isAllow" => "true", "value" => "THB_BOA", "label" => "BOA (Bank of Ayudhya (Krungsri))"),
			array("isAllow" => "true", "value" => "THB_CIMBT", "label" => "CIMBT (CIMB Thai)"),
			array("isAllow" => "true", "value" => "THB_KKR", "label" => "KKR (Karsikorn Bank (K-Bank))"),
			array("isAllow" => "true", "value" => "THB_KNK", "label" => "KNK (Kiatnakin Bank)"),
			array("isAllow" => "true", "value" => "THB_KTB", "label" => "KTB (Krung Thai Bank)"),
			array("isAllow" => "true", "value" => "THB_SCB", "label" => "SCB (Siam Commercial Bank)"),
			array("isAllow" => "true", "value" => "THB_TMB", "label" => "TMB (TMBThanachart Bank(TTB))"),
			array("isAllow" => "true", "value" => "THB_PPTP", "label" => "PPTP (Promptpay)"),
			array("isAllow" => "true", "value" => "VND_ACB", "label" => "ACB (Asia Commercial Bank)"),
			array("isAllow" => "true", "value" => "VND_AGB", "label" => "AGB (Agribank)"),
			array("isAllow" => "true", "value" => "VND_BIDV", "label" => "BIDV (Bank for Investment and Development of Vietnam)"),
			array("isAllow" => "true", "value" => "VND_DAB", "label" => "DAB (DongA Bank)"),
			array("isAllow" => "true", "value" => "VND_EXIM", "label" => "EXIM (Eximbank Vietnam)"),
			array("isAllow" => "true", "value" => "VND_HDB", "label" => "HDB (HDB Bank)"),
			array("isAllow" => "true", "value" => "VND_MB", "label" => "MB (Military Commercial Joint Stock Bank)"),
			array("isAllow" => "true", "value" => "VND_MTMB", "label" => "MTMB (Maritime Bank)"),
			array("isAllow" => "true", "value" => "VND_OCB", "label" => "OCB (Orient Commercial Joint Stock Bank)"),
			array("isAllow" => "true", "value" => "VND_SACOM", "label" => "SACOM (Sacombank)"),
			array("isAllow" => "true", "value" => "VND_TCB", "label" => "TCB (Techcombank)"),
			array("isAllow" => "true", "value" => "VND_TPB", "label" => "TPB (Tien Phong Bank)"),
			array("isAllow" => "true", "value" => "VND_VCB", "label" => "VCB (Vietcombank)"),
			array("isAllow" => "true", "value" => "VND_VIB", "label" => "VIB (Vietnam International Bank)"),
			array("isAllow" => "true", "value" => "VND_VPB", "label" => "VPB (VP Bank)"),
			array("isAllow" => "true", "value" => "VND_VTB", "label" => "VTB (Vietinbank)"),
			array("isAllow" => "true", "value" => "IDR_BCA", "label" => "BCA (Bank Central Asia)"),
			array("isAllow" => "true", "value" => "IDR_BDI", "label" => "BDI (Bank Danamon Indonesia)"),
			array("isAllow" => "true", "value" => "IDR_BNI", "label" => "BNI (Bank Negara Indonesia)"),
			array("isAllow" => "true", "value" => "IDR_BRI", "label" => "BRI (Bank Rakyat Indonesia)"),
			array("isAllow" => "true", "value" => "IDR_CIMBN", "label" => "CIMBN (CIMB Niaga)"),
			array("isAllow" => "true", "value" => "IDR_MBBI", "label" => "MBBI (Bank Maybank Indonesia)"),
			array("isAllow" => "true", "value" => "IDR_MDR", "label" => "MDR (Mandiri Bank)"),
			array("isAllow" => "true", "value" => "IDR_PMTB", "label" => "PMTB (Permata Bank)"),
			array("isAllow" => "true", "value" => "IDR_PANIN", "label" => "PANIN (Panin Bank)"),
			array("isAllow" => "true", "value" => "PHP_BDO", "label" => "BDO (Banco de Oro)"),
			array("isAllow" => "true", "value" => "PHP_BPI", "label" => "BPI (Bank of the Philippine Islands)"),
			array("isAllow" => "true", "value" => "PHP_LBP", "label" => "LBP (Land Bank of the Philippines)"),
			array("isAllow" => "true", "value" => "PHP_MTB", "label" => "MTB (MetroBank)"),
			array("isAllow" => "true", "value" => "PHP_RCBC", "label" => "RCBC (Rizal Commercial Banking Corporation)"),
			array("isAllow" => "true", "value" => "PHP_SBC", "label" => "SBC (Security Bank Corporation)"),
        ), 'required' => true, 'dependency' => ''),
	);

	private static $WITHDRAW_FORM_PARAMETERS = array(
		array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'required' => true, 'mandatory' => true)
	);

	/**
	* Function to get provider name
	* @return <String> provider name
	*/
	public function getName() {
		return 'Help2Pay'; // don't take name with any space or special charctor
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

		$order_id = PaymentProvidersHelper::generateUUID(); //Generated the unique order id from database

		if (!$order_id) {
			return array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_GENERATING_ORDER_ID', $this->module, $portal_language));
		}

		if (!empty($request)) {
			//Get response

			$returnUrl = $PORTAL_URL . "api/payment?result=paymentcallback&orid=" . $order_id . "&pm=" . $request['payment_from'];
			$callBackUrl = $site_URL . 'modules/CustomerPortal/thirdparty/payment_callback.php?pm=' . $request['payment_from'] . '&order_id=' . $order_id;

			if ($request['is_mobile_request']) {
				$returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
				$cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
			}
			if ($request['payment_operation'] == 'Deposit') {
				session_start();

				$provider = ServiceProvidersManager::getActiveInstanceByProvider($request['payment_from']);
				$amountValue = $request['net_amount'];
				$merchantKey = $provider->parameters['merchant_code'];
				$test_url = $provider->parameters['test_url'];
				$base_url = $provider->parameters['base_url'];
				
				global $default_timezone;
				date_default_timezone_set('Asia/Kuala_Lumpur');
				$currentDateTime = date('Y-m-d H:i:sA');
				date_default_timezone_set($default_timezone);
				if ($provider->parameters['test_mode'] == 'Yes') {
					$actionUrl = $test_url;
				} else {
					$actionUrl = $base_url;
				}
				$actionUrl = $actionUrl . "MerchantTransfer";
				$contact_data = PaymentProvidersHelper::getContactDetails($request['contactid']);
				$walletID = '';
				if ($contact_data != false && !empty($contact_data)) {
					$walletID = $contact_data['contact_no'];
				}
                                
				/*Help2Pay Mob pay condition*/
				$bankCurrency = $request['bank_currency'];
				list($preCurrency, $bankCode) = explode('_', $request['bank_code']);
				if(strtolower($bankCurrency) != 'usd')
				{
					$conversionDetail = getCurrencySupportConvertor($amountValue,'USD',$bankCurrency,'Deposit');
					$amountValue = $conversionDetail['converted_amount'];$log->debug('$amountValue-');$log->debug($amountValue);
					$amountValue = str_replace(',','',$amountValue);$log->debug('$replaceamountValue-');$log->debug($amountValue);
					$amountValue = number_format($amountValue,2,'.','');$log->debug('$afteramountValue-');$log->debug($amountValue);
					if($bankCurrency == 'IDR' || $bankCurrency == 'VND')
					{
						$amountValue = round($amountValue).'.00';
					}$log->debug('$finalamountValue-');$log->debug($amountValue);
				}
				/*Help2Pay Mob pay condition*/
                $log->debug('$_SERVER-');$log->debug($_SERVER);                
                list($clientIP, $otherIp) = explode(',', $_SERVER['HTTP_CLIENTIP']);
				$help2PayFormParam = array(
					'Merchant' => $merchantKey,
					'Currency' => $bankCurrency,
					'Customer' => $walletID,
					'Reference' => $order_id,
					'Key' => "",
					'Amount' => $amountValue,
					'Note' => "",
					'Datetime' => $currentDateTime,
					'FrontURI' => $returnUrl,
					'BackURI' => $callBackUrl,
					'Language' => 'en-us',
					'Bank' => $bankCode,
					'ClientIP' => $clientIP,
				);

				$help2PayFormParam = $this->generateHashKey($help2PayFormParam);
				unset($help2PayFormParam['date_time']);
				$log->debug('$help2PayFormParam');
				$log->debug($help2PayFormParam);
				$result_form = "<form id='payment_form' name='virtualpay' action='" . $actionUrl . "' method='post'>";
				foreach ($help2PayFormParam as $key => $value) {
					$result_form .= "<input type='hidden' name='$key' value='$value'/>";
				}
				$result_form .= "</form>";

				if (PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Created', 'Form Generation')) {
					$res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Form', 'redirect_url' => $actionUrl, 'order_id' => $order_id, 'result_form' => $result_form));
				} else {
					$res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module));
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

	public function verifySignature($paymentData = array())
	{
		global $log;
		$log->debug('Entering into verifySignature');
		$log->debug($paymentData);
		$isVerify = false;
		if(!empty($paymentData))
		{
			$paymentDataWithKey = $this->generateHashKey($paymentData, '2');
			$log->debug('Signature---');
			$log->debug($paymentDataWithKey['Key']);
			$log->debug($paymentData['Key']);
			if($paymentDataWithKey['Key'] == $paymentData['Key'])
			{
				$isVerify = true;
			}
		}
		return $isVerify;
	}

	//Verify the payment response and insert to payment log table
	public function paymentResponseVerification($status, $payment_response, $order_id, $portal_language) {
		global $log,$adb;
		$log->debug('Entering into paymentResponseVerification');
		$log->debug($payment_response);
		if (PaymentProvidersHelper::getPaymentRecord($order_id)) {
			//Verify payment response from stored callback
			$transactionStatus = $payment_response['Status'];
			/*Verify key */
			$isSignVerified = $this->verifySignature($payment_response);
			if(!$isSignVerified)
			{
				$transactionStatus = "pending";
				$payment_response['errorMessage'] = "Signature not valid!";
			}
			/*Verify key */

			if ($transactionStatus == '000' || $transactionStatus == '006') {
				$status = 'Success';
			} else if ($transactionStatus == '009') {
				$status = 'Pending';
			} else if ($transactionStatus == 'pending') {
				$status = 'SignatureFailed';
			} else {
				$status = 'Failed';
			}

			if ($status == 'Success') {
				$payment_response['message'] = vtranslate('CAB_MSG_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $this->module, $portal_language);
				$res = array('success' => true, 'payment_status' => 'Confirmed', 'message' => $payment_response['message']);
			} else if ($status == 'SignatureFailed') {
				$payment_response['message'] = "Signature not verified!";
				$res = array('success' => true, 'payment_status' => 'Pending', 'message' => $payment_response['message']);
			} else if ($status == 'Pending') {
				$payment_response['message'] = "Payment is in process!";
				$res = array('success' => true, 'payment_status' => 'Pending', 'message' => $payment_response['message']);
			} else {
				$status = "Failed";
				$payment_response['message'] = vtranslate('CAB_MSG_CANCELLED_BY_USER', $this->module);
				$errorMsg = isset($payment_response['message']) && !empty($payment_response['message']) ? $payment_response['message'] : 'Error while payment processing!';
				$res = array('success' => false, 'payment_status' => $status, 'message' => $errorMsg);
			}
			PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $payment_response['pm'], $payment_response, $status, $payment_response['message']);
		} else {
			$status == 'Failed';
			$res = array('success' => false, 'payment_status' => $status, 'message' => vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language));
		}
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
		$merchantCode = $this->getParameter('merchant_code');
		$actionUrl = $this->getParameter('base_url');
		if(strtolower($testMode) == 'yes')
		{
			$actionUrl = $this->getParameter('test_url');
		}
		if (!empty($orderId)) {
			$thirdPartyPayStatus = array('000' => 'success', '006' => 'success', '001' => 'failed', '008' => 'cancelled', '007' => 'failed');
			
			$actionUrl = $actionUrl . "Services/Merchants/$merchantCode/TransferStatus/$orderId";
			$headers = array();
			$params = array();$log->debug('$actionUrl=');$log->debug($actionUrl);
			$callbackXmlData = $this->sendCurlRequest($actionUrl, 'POST', "", $headers, $params);$log->debug('$callbackXmlData=');$log->debug($callbackXmlData);
			$xmlObj = simplexml_load_string($callbackXmlData);
			$callbackJsonData = json_encode($xmlObj);$log->debug('$callbackJsonData=');$log->debug($callbackJsonData);
			$callbackData = json_decode($callbackJsonData,TRUE);
			if($callbackData)
			{
				$thirdPartyResponseStatus = $callbackData['StatusCode'];
				$paymentStatusResponse['data'] = $callbackData;
				$paymentStatusResponse['status'] = $thirdPartyPayStatus[$thirdPartyResponseStatus];
			}
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

        return (false === $response) ? false : $response;
    }

	public function paymentCallbackHandler($callbackHandlerData = array())
	{
		global $log;
		$log->debug('Entering into help2pay paymentCallbackHandler...');
		try
        {
			$callbackResponse = array();
			$callbackResponseString = file_get_contents('php://input');$log->debug($callbackResponseString);
			if(!empty($callbackResponseString))
			{
				$callbackResponse = explode('&', $callbackResponseString);$log->debug($callbackResponse);
				$orderId = $callbackResponse['Reference'];
				if(!empty($orderId))
				{
					if (PaymentProvidersHelper::getPaymentRecord($orderId))
					{$log->debug('Entering into pending record action');
						/*Verify key */
						$isSignVerified = $this->verifySignature($callbackResponse);
						/*Verify key */
						if($isSignVerified)
						{$log->debug('sign verified');
							$thirdPartyPayStatus = array('000' => 'success', '006' => 'success', '001' => 'failed', '008' => 'cancelled', '007' => 'rejected');
							$status = strtolower($thirdPartyPayStatus[$callbackResponse['Status']]);$log->debug('callback status-'.$status);
							$paymentStatus = '';
							$recordModel = Vtiger_Record_Model::getInstanceById($callbackHandlerData['record_id'], 'Payments');
							$failedReason = "Payment failed by help2pay callback";
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
									$callbackResponse['message'] = "Callback: Success : Payment record status updated successfully.";
									$callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['provider_name'], $callbackHandlerData['provider_title'], $callbackResponse, $paymentStatus);
								}
								else
								{
									$callbackResponse['message'] = "Callback: Error : Callback Response. Payment record status not updated.";
									$callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['provider_name'], $callbackHandlerData['provider_title'], $callbackResponse, 'Failed');
								}
							}
							else
							{
								$callbackResponse['message'] = "Callback: Error : Callback Response. Payment record not found.";
								$callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['provider_name'], $callbackHandlerData['provider_title'], $callbackResponse, 'Pending');
							}
							$ackOfCallback = json_encode(array('success' => true));
						}
						else
						{
							$log->debug('signature not verified!');
							$ackOfCallback = json_encode(array('success' => false, 'msg' => 'signature not verified!'));
						}
					}
					else
					{
						$log->debug('Payment status is not in pending..');
						$ackOfCallback = json_encode(array('success' => false, 'msg' => 'Payment status is not in pending..'));
					}
				}
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

	private function generateHashKey($params = array(), $type = '1')
    {
        global $log;$log->debug('Entering into generateHashKey');$log->debug($params);
		if(!empty($params['Reference']))
		{
			$securityCode = $this->getParameter('security_code');
			if($type == '1')
			{
				$dateTime = str_replace(array('PM', 'AM'), array('', ''), $params['Datetime']);
				$dateTime = date('YmdHis', strtotime($dateTime));
				list($clientIP, $otherIp) = explode(',', $_SERVER['HTTP_CLIENTIP']);
				$hashString = $params['Merchant'].$params['Reference'].$params['Customer'].$params['Amount'].$params['Currency'].$dateTime.$securityCode.$clientIP;$log->debug($hashString);
			}
			else
			{
				$hashString = $params['Merchant'].$params['Reference'].$params['Customer'].$params['Amount'].$params['Currency'].$params['Status'].$securityCode;$log->debug($hashString);
			}
			
			$params['Key'] = strtoupper(md5($hashString));
		}
		return $params;
	}
}

?>