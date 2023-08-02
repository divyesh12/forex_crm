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

class ServiceProviders_VirtualPay_Provider extends ServiceProviders_AbstractPaymentGatways_Model {

	protected $module = 'Payments';
	protected $translate_module = 'CustomerPortal_Client'; // Common label file
	private static $REQUIRED_PARAMETERS = array(
		array('name' => 'mid', 'label' => 'MID', 'type' => 'text', 'mandatory' => true),
		array('name' => 'api_key', 'label' => 'API Key', 'type' => 'text', 'mandatory' => true),
		array('name' => 'private_key', 'label' => 'Private Key', 'type' => 'text', 'mandatory' => true),
		array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
		array('name' => 'test_url', 'label' => 'Test URL', 'type' => 'text', 'mandatory' => true),
		array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),
	);
	private static $DEPOSIT_FORM_PARAMETERS = array(
		array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'required' => true, 'mandatory' => true),
		array('name' => 'country', 'label' => 'CAB_LBL_COUNTRY', 'type' => 'dropdown_depended', 'picklist' => array(
			array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
			array("isAllow" => "true", "value" => "Afghanistan", "label" => "Afghanistan"),
			array("isAllow" => "true", "value" => "Albania", "label" => "Albania"),
			array("isAllow" => "true", "value" => "Algeria", "label" => "Algeria"),
			array("isAllow" => "true", "value" => "American Samoa", "label" => "American Samoa"),
			array("isAllow" => "true", "value" => "Andorra", "label" => "Andorra"),
			array("isAllow" => "true", "value" => "Angola", "label" => "Angola"),
			array("isAllow" => "true", "value" => "Anguilla", "label" => "Anguilla"),
			array("isAllow" => "true", "value" => "Antigua and Barbuda", "label" => "Antigua and Barbuda"),
			array("isAllow" => "true", "value" => "Argentina", "label" => "Argentina"),
			array("isAllow" => "true", "value" => "Armenia", "label" => "Armenia"),
			array("isAllow" => "true", "value" => "Aruba", "label" => "Aruba"),
			array("isAllow" => "true", "value" => "Ascension Island", "label" => "Ascension Island"),
			array("isAllow" => "true", "value" => "Australia", "label" => "Australia"),
			array("isAllow" => "true", "value" => "Australian External Territories", "label" => "Australian External Territories"),
			array("isAllow" => "true", "value" => "Austria", "label" => "Austria"),
			array("isAllow" => "true", "value" => "Azerbaijan", "label" => "Azerbaijan"),
			array("isAllow" => "true", "value" => "Bahamas", "label" => "Bahamas"),
			array("isAllow" => "true", "value" => "Bahrain", "label" => "Bahrain"),
			array("isAllow" => "true", "value" => "Bangladesh", "label" => "Bangladesh"),
			array("isAllow" => "true", "value" => "Barbados", "label" => "Barbados"),
			array("isAllow" => "true", "value" => "Belarus", "label" => "Belarus"),
			array("isAllow" => "true", "value" => "Belgium", "label" => "Belgium"),
			array("isAllow" => "true", "value" => "Belize", "label" => "Belize"),
			array("isAllow" => "true", "value" => "Benin", "label" => "Benin"),
			array("isAllow" => "true", "value" => "Bermuda", "label" => "Bermuda"),
			array("isAllow" => "true", "value" => "Bhutan", "label" => "Bhutan"),
			array("isAllow" => "true", "value" => "Bolivia", "label" => "Bolivia"),
			array("isAllow" => "true", "value" => "Bosnia and Herzegovina", "label" => "Bosnia and Herzegovina"),
			array("isAllow" => "true", "value" => "Botswana", "label" => "Botswana"),
			array("isAllow" => "true", "value" => "Brazil", "label" => "Brazil"),
			array("isAllow" => "true", "value" => "British Virgin Islands", "label" => "British Virgin Islands"),
			array("isAllow" => "true", "value" => "Brunei Darussalam", "label" => "Brunei Darussalam"),
			array("isAllow" => "true", "value" => "Bulgaria", "label" => "Bulgaria"),
			array("isAllow" => "true", "value" => "Burkina Faso", "label" => "Burkina Faso"),
			array("isAllow" => "true", "value" => "Burundi", "label" => "Burundi"),
			array("isAllow" => "true", "value" => "Cambodia", "label" => "Cambodia"),
			array("isAllow" => "true", "value" => "Cameroon", "label" => "Cameroon"),
			array("isAllow" => "true", "value" => "Cape Verde", "label" => "Cape Verde"),
			array("isAllow" => "true", "value" => "Cayman Islands", "label" => "Cayman Islands"),
			array("isAllow" => "true", "value" => "Central African Republic", "label" => "Central African Republic"),
			array("isAllow" => "true", "value" => "Chad", "label" => "Chad"),
			array("isAllow" => "true", "value" => "Chile", "label" => "Chile"),
			array("isAllow" => "true", "value" => "China", "label" => "China"),
			array("isAllow" => "true", "value" => "Colombia", "label" => "Colombia"),
			array("isAllow" => "true", "value" => "Comoros", "label" => "Comoros"),
			array("isAllow" => "true", "value" => "Congo", "label" => "Congo"),
			array("isAllow" => "true", "value" => "Cook Islands", "label" => "Cook Islands"),
			array("isAllow" => "true", "value" => "Costa Rica", "label" => "Costa Rica"),
			array("isAllow" => "true", "value" => "Cote dIvoire", "label" => "Cote dIvoire"),
			array("isAllow" => "true", "value" => "Croatia", "label" => "Croatia"),
			array("isAllow" => "true", "value" => "Cuba", "label" => "Cuba"),
			array("isAllow" => "true", "value" => "Cyprus", "label" => "Cyprus"),
			array("isAllow" => "true", "value" => "Czech Republic", "label" => "Czech Republic"),
			array("isAllow" => "true", "value" => "Democratic Peoples Republic of Korea", "label" => "Democratic Peoples Republic of Korea"),
			array("isAllow" => "true", "value" => "Democratic Republic of the Congo", "label" => "Democratic Republic of the Congo"),
			array("isAllow" => "true", "value" => "Denmark", "label" => "Denmark"),
			array("isAllow" => "true", "value" => "Diego Garcia", "label" => "Diego Garcia"),
			array("isAllow" => "true", "value" => "Djibouti", "label" => "Djibouti"),
			array("isAllow" => "true", "value" => "Dominica", "label" => "Dominica"),
			array("isAllow" => "true", "value" => "Dominican Republic", "label" => "Dominican Republic"),
			array("isAllow" => "true", "value" => "East Timor", "label" => "East Timor"),
			array("isAllow" => "true", "value" => "Ecuador", "label" => "Ecuador"),
			array("isAllow" => "true", "value" => "Egypt", "label" => "Egypt"),
			array("isAllow" => "true", "value" => "El Salvador", "label" => "El Salvador"),
			array("isAllow" => "true", "value" => "Equatorial Guinea", "label" => "Equatorial Guinea"),
			array("isAllow" => "true", "value" => "Eritrea", "label" => "Eritrea"),
			array("isAllow" => "true", "value" => "Estonia", "label" => "Estonia"),
			array("isAllow" => "true", "value" => "Ethiopia", "label" => "Ethiopia"),
			array("isAllow" => "true", "value" => "Falkland Islands", "label" => "Falkland Islands"),
			array("isAllow" => "true", "value" => "Faroe Islands", "label" => "Faroe Islands"),
			array("isAllow" => "true", "value" => "Federated States of Micronesia", "label" => "Federated States of Micronesia"),
			array("isAllow" => "true", "value" => "Fiji", "label" => "Fiji"),
			array("isAllow" => "true", "value" => "Finland", "label" => "Finland"),
			array("isAllow" => "true", "value" => "France", "label" => "France"),
			array("isAllow" => "true", "value" => "French Guiana", "label" => "French Guiana"),
			array("isAllow" => "true", "value" => "French Polynesia", "label" => "French Polynesia"),
			array("isAllow" => "true", "value" => "Gabon", "label" => "Gabon"),
			array("isAllow" => "true", "value" => "Gambia", "label" => "Gambia"),
			array("isAllow" => "true", "value" => "Georgia", "label" => "Georgia"),
			array("isAllow" => "true", "value" => "Germany", "label" => "Germany"),
			array("isAllow" => "true", "value" => "Ghana", "label" => "Ghana"),
			array("isAllow" => "true", "value" => "Gibraltar", "label" => "Gibraltar"),
			array("isAllow" => "true", "value" => "Greece", "label" => "Greece"),
			array("isAllow" => "true", "value" => "Greenland", "label" => "Greenland"),
			array("isAllow" => "true", "value" => "Grenada", "label" => "Grenada"),
			array("isAllow" => "true", "value" => "Guadeloupe", "label" => "Guadeloupe"),
			array("isAllow" => "true", "value" => "Guam", "label" => "Guam"),
			array("isAllow" => "true", "value" => "Guatemala", "label" => "Guatemala"),
			array("isAllow" => "true", "value" => "Guinea", "label" => "Guinea"),
			array("isAllow" => "true", "value" => "Guinea-Bissau", "label" => "Guinea-Bissau"),
			array("isAllow" => "true", "value" => "Guyana", "label" => "Guyana"),
			array("isAllow" => "true", "value" => "Haiti", "label" => "Haiti"),
			array("isAllow" => "true", "value" => "Honduras", "label" => "Honduras"),
			array("isAllow" => "true", "value" => "Hong Kong", "label" => "Hong Kong"),
			array("isAllow" => "true", "value" => "Hungary", "label" => "Hungary"),
			array("isAllow" => "true", "value" => "Iceland", "label" => "Iceland"),
			array("isAllow" => "true", "value" => "India", "label" => "India"),
			array("isAllow" => "true", "value" => "Indonesia", "label" => "Indonesia"),
			array("isAllow" => "true", "value" => "Iran", "label" => "Iran"),
			array("isAllow" => "true", "value" => "Iraq", "label" => "Iraq"),
			array("isAllow" => "true", "value" => "Ireland", "label" => "Ireland"),
			array("isAllow" => "true", "value" => "Israel", "label" => "Israel"),
			array("isAllow" => "true", "value" => "Italy", "label" => "Italy"),
			array("isAllow" => "true", "value" => "Jamaica", "label" => "Jamaica"),
			array("isAllow" => "true", "value" => "Japan", "label" => "Japan"),
			array("isAllow" => "true", "value" => "Jordan", "label" => "Jordan"),
			array("isAllow" => "true", "value" => "Kazakhstan", "label" => "Kazakhstan"),
			array("isAllow" => "true", "value" => "Kenya", "label" => "Kenya"),
			array("isAllow" => "true", "value" => "Kiribati", "label" => "Kiribati"),
			array("isAllow" => "true", "value" => "Kuwait", "label" => "Kuwait"),
			array("isAllow" => "true", "value" => "Kyrgyzstan", "label" => "Kyrgyzstan"),
			array("isAllow" => "true", "value" => "Laos", "label" => "Laos"),
			array("isAllow" => "true", "value" => "Latvia", "label" => "Latvia"),
			array("isAllow" => "true", "value" => "Lebanon", "label" => "Lebanon"),
			array("isAllow" => "true", "value" => "Lesotho", "label" => "Lesotho"),
			array("isAllow" => "true", "value" => "Liberia", "label" => "Liberia"),
			array("isAllow" => "true", "value" => "Libya", "label" => "Libya"),
			array("isAllow" => "true", "value" => "Liechtenstein", "label" => "Liechtenstein"),
			array("isAllow" => "true", "value" => "Lithuania", "label" => "Lithuania"),
			array("isAllow" => "true", "value" => "Luxembourg", "label" => "Luxembourg"),
			array("isAllow" => "true", "value" => "Macau", "label" => "Macau"),
			array("isAllow" => "true", "value" => "Macedonia", "label" => "Macedonia"),
			array("isAllow" => "true", "value" => "Madagascar", "label" => "Madagascar"),
			array("isAllow" => "true", "value" => "Malawi", "label" => "Malawi"),
			array("isAllow" => "true", "value" => "Malaysia", "label" => "Malaysia"),
			array("isAllow" => "true", "value" => "Maldives", "label" => "Maldives"),
			array("isAllow" => "true", "value" => "Mali", "label" => "Mali"),
			array("isAllow" => "true", "value" => "Malta", "label" => "Malta"),
			array("isAllow" => "true", "value" => "Marshall Islands", "label" => "Marshall Islands"),
			array("isAllow" => "true", "value" => "Martinique", "label" => "Martinique"),
			array("isAllow" => "true", "value" => "Mauritania", "label" => "Mauritania"),
			array("isAllow" => "true", "value" => "Mauritius", "label" => "Mauritius"),
			array("isAllow" => "true", "value" => "Mayotte", "label" => "Mayotte"),
			array("isAllow" => "true", "value" => "Mexico", "label" => "Mexico"),
			array("isAllow" => "true", "value" => "Moldova", "label" => "Moldova"),
			array("isAllow" => "true", "value" => "Monaco", "label" => "Monaco"),
			array("isAllow" => "true", "value" => "Mongolia", "label" => "Mongolia"),
			array("isAllow" => "true", "value" => "Montserrat", "label" => "Montserrat"),
			array("isAllow" => "true", "value" => "Morocco", "label" => "Morocco"),
			array("isAllow" => "true", "value" => "Mozambique", "label" => "Mozambique"),
			array("isAllow" => "true", "value" => "Myanmar", "label" => "Myanmar"),
			array("isAllow" => "true", "value" => "Namibia", "label" => "Namibia"),
			array("isAllow" => "true", "value" => "Nauru", "label" => "Nauru"),
			array("isAllow" => "true", "value" => "Nepal", "label" => "Nepal"),
			array("isAllow" => "true", "value" => "Netherlands", "label" => "Netherlands"),
			array("isAllow" => "true", "value" => "Netherlands Antilles", "label" => "Netherlands Antilles"),
			array("isAllow" => "true", "value" => "New Caledonia", "label" => "New Caledonia"),
			array("isAllow" => "true", "value" => "New Zealand", "label" => "New Zealand"),
			array("isAllow" => "true", "value" => "Nicaragua", "label" => "Nicaragua"),
			array("isAllow" => "true", "value" => "Niger", "label" => "Niger"),
			array("isAllow" => "true", "value" => "Nigeria", "label" => "Nigeria"),
			array("isAllow" => "true", "value" => "Niue", "label" => "Niue"),
			array("isAllow" => "true", "value" => "Northern Mariana Islands", "label" => "Northern Mariana Islands"),
			array("isAllow" => "true", "value" => "Norway", "label" => "Norway"),
			array("isAllow" => "true", "value" => "Oman", "label" => "Oman"),
			array("isAllow" => "true", "value" => "Pakistan", "label" => "Pakistan"),
			array("isAllow" => "true", "value" => "Palau", "label" => "Palau"),
			array("isAllow" => "true", "value" => "Panama", "label" => "Panama"),
			array("isAllow" => "true", "value" => "Papua New Guinea", "label" => "Papua New Guinea"),
			array("isAllow" => "true", "value" => "Paraguay", "label" => "Paraguay"),
			array("isAllow" => "true", "value" => "Peru", "label" => "Peru"),
			array("isAllow" => "true", "value" => "Philippines", "label" => "Philippines"),
			array("isAllow" => "true", "value" => "Poland", "label" => "Poland"),
			array("isAllow" => "true", "value" => "Portugal", "label" => "Portugal"),
			array("isAllow" => "true", "value" => "Puerto Rico", "label" => "Puerto Rico"),
			array("isAllow" => "true", "value" => "Qatar", "label" => "Qatar"),
			array("isAllow" => "true", "value" => "Republic of Korea", "label" => "Republic of Korea"),
			array("isAllow" => "true", "value" => "Reunion", "label" => "Reunion"),
			array("isAllow" => "true", "value" => "Romania", "label" => "Romania"),
			array("isAllow" => "true", "value" => "Russia", "label" => "Russia"),
			array("isAllow" => "true", "value" => "Rwanda", "label" => "Rwanda"),
			array("isAllow" => "true", "value" => "Samoa", "label" => "Samoa"),
			array("isAllow" => "true", "value" => "San Marino", "label" => "San Marino"),
			array("isAllow" => "true", "value" => "Sao Tome and Principe", "label" => "Sao Tome and Principe"),
			array("isAllow" => "true", "value" => "Saudi Arabia", "label" => "Saudi Arabia"),
			array("isAllow" => "true", "value" => "Senegal", "label" => "Senegal"),
			array("isAllow" => "true", "value" => "Seychelles", "label" => "Seychelles"),
			array("isAllow" => "true", "value" => "Sierra Leone", "label" => "Sierra Leone"),
			array("isAllow" => "true", "value" => "Singapore", "label" => "Singapore"),
			array("isAllow" => "true", "value" => "Slovakia", "label" => "Slovakia"),
			array("isAllow" => "true", "value" => "Slovenia", "label" => "Slovenia"),
			array("isAllow" => "true", "value" => "Solomon Islands", "label" => "Solomon Islands"),
			array("isAllow" => "true", "value" => "Somalia", "label" => "Somalia"),
			array("isAllow" => "true", "value" => "South Africa", "label" => "South Africa"),
			array("isAllow" => "true", "value" => "Spain", "label" => "Spain"),
			array("isAllow" => "true", "value" => "Sri Lanka", "label" => "Sri Lanka"),
			array("isAllow" => "true", "value" => "St. Helena", "label" => "St. Helena"),
			array("isAllow" => "true", "value" => "St. Kitts and Nevis", "label" => "St. Kitts and Nevis"),
			array("isAllow" => "true", "value" => "St. Lucia", "label" => "St. Lucia"),
			array("isAllow" => "true", "value" => "St. Pierre and Miquelon", "label" => "St. Pierre and Miquelon"),
			array("isAllow" => "true", "value" => "St. Vincent and the Grenadines", "label" => "St. Vincent and the Grenadines"),
			array("isAllow" => "true", "value" => "Sudan", "label" => "Sudan"),
			array("isAllow" => "true", "value" => "Suriname", "label" => "Suriname"),
			array("isAllow" => "true", "value" => "Swaziland", "label" => "Swaziland"),
			array("isAllow" => "true", "value" => "Sweden", "label" => "Sweden"),
			array("isAllow" => "true", "value" => "Switzerland", "label" => "Switzerland"),
			array("isAllow" => "true", "value" => "Syria", "label" => "Syria"),
			array("isAllow" => "true", "value" => "Taiwan", "label" => "Taiwan"),
			array("isAllow" => "true", "value" => "Tajikistan", "label" => "Tajikistan"),
			array("isAllow" => "true", "value" => "Tanzania", "label" => "Tanzania"),
			array("isAllow" => "true", "value" => "Thailand", "label" => "Thailand"),
			array("isAllow" => "true", "value" => "Togo", "label" => "Togo"),
			array("isAllow" => "true", "value" => "Tokelau", "label" => "Tokelau"),
			array("isAllow" => "true", "value" => "Tonga", "label" => "Tonga"),
			array("isAllow" => "true", "value" => "Trinidad and Tobago", "label" => "Trinidad and Tobago"),
			array("isAllow" => "true", "value" => "Tunisia", "label" => "Tunisia"),
			array("isAllow" => "true", "value" => "Turkey", "label" => "Turkey"),
			array("isAllow" => "true", "value" => "Turkmenistan", "label" => "Turkmenistan"),
			array("isAllow" => "true", "value" => "Turks and Caicos Islands", "label" => "Turks and Caicos Islands"),
			array("isAllow" => "true", "value" => "Tuvalu", "label" => "Tuvalu"),
			array("isAllow" => "true", "value" => "US Virgin Islands", "label" => "US Virgin Islands"),
			array("isAllow" => "true", "value" => "Uganda", "label" => "Uganda"),
			array("isAllow" => "true", "value" => "Ukraine", "label" => "Ukraine"),
			array("isAllow" => "true", "value" => "United Arab Emirates", "label" => "United Arab Emirates"),
			array("isAllow" => "true", "value" => "United Kingdom", "label" => "United Kingdom"),
			array("isAllow" => "true", "value" => "Uruguay", "label" => "Uruguay"),
			array("isAllow" => "true", "value" => "Uzbekistan", "label" => "Uzbekistan"),
			array("isAllow" => "true", "value" => "Vanuatu", "label" => "Vanuatu"),
			array("isAllow" => "true", "value" => "Vatican", "label" => "Vatican"),
			array("isAllow" => "true", "value" => "Venezuela", "label" => "Venezuela"),
			array("isAllow" => "true", "value" => "Vietnam", "label" => "Vietnam"),
			array("isAllow" => "true", "value" => "Wallis and Futuna Islands", "label" => "Wallis and Futuna Islands"),
			array("isAllow" => "true", "value" => "Yemen", "label" => "Yemen"),
			array("isAllow" => "true", "value" => "Yugoslavia", "label" => "Yugoslavia"),
			array("isAllow" => "true", "value" => "Zambia", "label" => "Zambia"),
			array("isAllow" => "true", "value" => "Zimbabwe", "label" => "Zimbabwe"),
			array("isAllow" => "true", "value" => "Canada", "label" => "Canada"),
			array("isAllow" => "true", "value" => "cocos(keeling) islands", "label" => "cocos(keeling) islands"),
			array("isAllow" => "true", "value" => "congo the democratic republic of the", "label" => "congo the democratic republic of the"),
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
		return 'VirtualPay'; // don't take name with any space or special charctor
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
				
				$base64Hash = hash('sha256', $order_id.$mid.$amount_value.$display_currency, true);
				$checkSum = base64_encode($base64Hash);

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
					'CHECKSUM' => $checkSum,
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
		$time = time();
    	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString . '-' . $time;
	}

	public function paymentCallbackHandler($callbackHandlerData = array()) {
		global $log;
		$log->debug('Entering into virtualpay paymentCallbackHandler...');
		$callbackResponse = '';
		$callbackResponseJson = file_get_contents('php://input');$log->debug($callbackResponseJson);

		$callbackResponse['order_id'] = $callbackHandlerData['order_id'];
		$callbackResponse['json'] = $callbackResponseJson;

		$callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['payment_method'], $callbackHandlerData['payment_method'], $callbackResponse);
		exit;
		if(!empty($callbackResponseJson))
		{
			$callbackResponse = $callbackResponseJson;
		}
		$callbackLogStatus = createPaymentLog($callbackHandlerData['order_id'], $callbackHandlerData['payment_method'], $callbackHandlerData['payment_method'], $callbackResponse);
		return $callbackLogStatus;
	}

	public function getPendingRecordDurationQuery() {
        $duration = "15 MINUTE";
        $testMode = $this->getParameter('test_mode');
        if(strtolower($testMode) == 'yes')
        {
            $duration = "5 MINUTE";
        }
        $query = " AND vtiger_crmentity.createdtime < DATE_SUB(NOW(),INTERVAL $duration)";
        return $query;
    }
    
    public function getPendingRecordHandlerQuery() {
        $duration = "2 HOUR";
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
        $paymentStatusResponse = array();
        $orderId = $paymentData['order_id'];
        if (!empty($orderId)) {
            $thirdPartyPayStatus = array('0' => 'success', '00' => 'success');
            
            $getPaymentCallbackRecord = "SELECT data FROM vtiger_payment_logs WHERE order_id = ? AND provider_type = ? AND status = ? AND event = ? ORDER BY id DESC LIMIT 1";
            $resultGetPaymentCallback = $adb->pquery($getPaymentCallbackRecord, array($orderId, $paymentData['payment_from'], 'Created', 'VirtualPay Callback Response'));
			$noOfRow = $adb->num_rows($resultGetPaymentCallback);        
        	if ($noOfRow > 0){
				$callbackJsonData = $adb->query_result($resultGetPaymentCallback, 0, 'data');
				$callbackJsonData = html_entity_decode($callbackJsonData);
				$callbackData = json_decode($callbackJsonData, true);
	
				$thirdPartyResponseStatus = strtolower($callbackData['json']['responseCode']);
				$paymentStatusResponse['data'] = $callbackData;
				$paymentStatusResponse['status'] = $status = $thirdPartyPayStatus[$thirdPartyResponseStatus];
			}
        }
        return $paymentStatusResponse;
    }

	public function createPaymentLog($orderId, $providerType, $providerTitle, $req, $status = 'Created', $event = "VirtualPay Callback Response") {
		global $adb;
		$date = date('Y-m-d h:i:s');
		$query = "INSERT INTO `vtiger_payment_logs` (`order_id`, `provider_type`, `provider_title`,`data`, `status`, `event`, `createdtime`) VALUES (?,?,?,?,?,?,?)";
		$result = $adb->pquery($query, array($orderId,$providerType,$providerTitle,$req,$status,$event,$date));
		if ($result)
			return true;
		else
			return false;
	}

}

?>