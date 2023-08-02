<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class ServiceProviders_Leverate_Provider implements ServiceProviders_ITradingPlatform_Model {

    private $userName;
    private $password;
    private $apiUrl = "api/ManagementService/";
    private $userapiUrl = "api/UserActions/";
    private $volumeDivideby = 100000;
    public $syncConfig = array(
        "open_trade" => false, //false = sync using direct api
        "close_trade" => true,// true = sync using database
        "balance" => false,
    );
    
    public static $REQUIRED_PARAMETERS = array(
        array('name' => 'client_type', 'label' => 'Client Type', 'type' => 'picklist', 'picklistvalues' => array('Partner' => 'Partner'), 'mandatory' => true),
        array('name' => 'demo_meta_trader_ip', 'label' => 'DemoAccount MT IP', 'type' => 'text', 'mandatory' => true),
        array('name' => 'demo_meta_trader_user', 'label' => ' DemoAccount MT User', 'type' => 'text', 'mandatory' => true),
        array('name' => 'demo_meta_trader_password', 'label' => 'DemoAccount MT Password', 'type' => 'password', 'mandatory' => true),
        array('name' => 'live_meta_trader_ip', 'label' => 'LiveAccount MT IP', 'type' => 'text', 'mandatory' => true),
        array('name' => 'live_meta_trader_user', 'label' => 'LiveAccount MT User', 'type' => 'text', 'mandatory' => true),
        array('name' => 'live_meta_trader_password', 'label' => 'LiveAccount MT Password', 'type' => 'password', 'mandatory' => true),
        array('name' => 'liveacc_start_range', 'label' => 'LiveAccount Start Range', 'type' => 'number', 'mandatory' => true),
        array('name' => 'liveacc_end_range', 'label' => 'LiveAccount End Range', 'type' => 'number', 'mandatory' => true),
        array('name' => 'demoacc_start_range', 'label' => 'DemoAccount Start Range', 'type' => 'number', 'mandatory' => true),
        array('name' => 'demoacc_end_range', 'label' => 'DemoAccount End Range', 'type' => 'number', 'mandatory' => true),
        array('name' => 'sync_start_date', 'label' => 'Sync Start Date', 'type' => 'date', 'mandatory' => true),
        array('name' => 'trade_date', 'label' => 'Trade Date', 'type' => 'date'),
        array('name' => 'investor_pass_enable', 'label' => 'Investor Password Enable', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No')),
        array('name' => 'meta_trader_ios_link', 'label' => 'IOS', 'type' => 'text'),
        array('name' => 'meta_trader_android_link', 'label' => 'Android', 'type' => 'text'),
        array('name' => 'meta_trader_windows_link', 'label' => 'Windows', 'type' => 'text'),
        array('name' => 'db_name', 'label' => 'DB Name', 'type' => 'text'),
        array('name' => 'sequence_number', 'label' => 'Sequence Number', 'type' => 'number'),
    );

    public static $errorString = array('-1' => 'InvalidLoginInfoError','-5' => 'TradingIsClosedError');

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName() {
        return 'Leverate';
    }

    public function getDbName() {
        global $dbconfig;
        $params = $this->prepareParameters();
        $dbName = $params['db_name'];
        $currentDbName = empty($dbName) ? $dbconfig['db_name'] : $dbName;
        return $currentDbName;
    }

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getRequiredParams() {
        return array_merge(self::DEFAULT_REQUIRED_PARAMETERS, self::$REQUIRED_PARAMETERS);
    }

    /**
     * Function to set authentication parameters
     * @param <String> $userName
     * @param <String> $password
     */
    public function setAuthParameters($userName, $password) {
        $this->userName = $userName;
        $this->password = $password;
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
    protected function prepareParameters() {
        foreach (self::$REQUIRED_PARAMETERS as $key => $fieldInfo) {
            $params[$fieldInfo['name']] = $this->getParameter($fieldInfo['name']);
        }
        return $params;
    }

    public function getProviderEditFieldTemplateName() {
        return 'TextAnyWhereEditField.tpl'; //Chnaged tpl file By Reena Hingol 09-10-2019
    }

    public function getCloseTradesQueryForCommission($maxTicket) {
        $serverName = $this->getName();
        $dbName = $this->getDbName();
        return "SELECT  '$serverName'   AS server_type,  `TICKET` AS ticket,`LOGIN` AS login,`SYMBOL` AS symbol,`DIGITS` AS digits,`CMD` AS cmd,IF(CMD=6,true,false) is_deposit, `VOLUME`/$this->volumeDivideby AS volume,`OPEN_PRICE` AS open_price,`CLOSE_PRICE` AS close_price,OPEN_TIME AS open_time, CLOSE_TIME AS close_time,`COMMENT` AS `comment`, PROFIT as `profit` FROM `" . $dbName . "`.`leverate_close_trade` WHERE  `TICKET` > $maxTicket AND  ((CLOSE_TIME <> '1970-01-01 00:00:00' AND  CMD IN (1,0)) OR (CMD = 6 AND `COMMENT` LIKE '%Deposit%'))";
    }

    public function getCloseTradesQueryForCommissionByCloseTime($closeTime) {
        $serverName = $this->getName();
        $dbName = $this->getDbName();
        return "SELECT  '$serverName'   AS server_type,  `TICKET` AS ticket,`LOGIN` AS login,`SYMBOL` AS symbol,`DIGITS` AS digits,`CMD` AS cmd,IF(CMD=6,true,false) is_deposit, `VOLUME`/$this->volumeDivideby AS volume,`OPEN_PRICE` AS open_price,`CLOSE_PRICE` AS close_price,OPEN_TIME AS open_time, CLOSE_TIME AS close_time,`COMMENT` AS `comment`, PROFIT as `profit`, `COMMISSION` as brokerage_commission FROM `" . $dbName . "`.`leverate_close_trade` WHERE  `CLOSE_TIME` > '$closeTime' AND  ((CLOSE_TIME <> '1970-01-01 00:00:00' AND  CMD IN (1,0)) OR (CMD = 6 AND `COMMENT` LIKE '%Deposit%'))";
    }

    /**
     * This function is used to get Leverate trades which are missing in trade commission calculation
     * @param datetime $closeTime
     * @param date $closeDateForManual [format - "yyyy-mm-dd"] - This parameter set when we manually calculate commission
     * @return string $tradeQuery
     */
    public function getCloseTradesQueryForMissingCommission($closeTime, $closeDateForManual = '') {
        $serverName = $this->getName();
        $dbName = $this->getDbName();
        $tradeLimitQuery = " (`CLOSE_TIME` between (CONVERT_TZ( NOW(), @@session.time_zone, '+00:00') - INTERVAL 2 DAY) AND '$closeTime') ";
        $extraWhere = " AND `TICKET` NOT IN (SELECT ticket from tradescommission WHERE close_time >= (CONVERT_TZ( NOW(), @@session.time_zone, '+00:00') - INTERVAL 2 DAY)) ";
        if (!empty($closeDateForManual)) {
            $startCloseDateForManual = $closeDateForManual . ' 00:00:00';
            $endCloseDateForManual = $closeDateForManual . ' 23:59:59';
            $tradeLimitQuery = " (`CLOSE_TIME` between '$startCloseDateForManual' AND '$endCloseDateForManual') ";
            $extraWhere = " AND `TICKET` NOT IN (SELECT ticket from tradescommission WHERE DATE_FORMAT(close_time,'%Y-%m-%d') = '$closeDateForManual') ";
        }
        $tradeQuery = "SELECT  '$serverName' AS server_type, `TICKET` AS ticket,`LOGIN` AS login,`SYMBOL` AS symbol,`DIGITS` AS digits,`CMD` AS cmd,IF(CMD=6,true,false) is_deposit, `VOLUME`/$this->volumeDivideby AS volume,`OPEN_PRICE` AS open_price,`CLOSE_PRICE` AS close_price,OPEN_TIME AS open_time, CLOSE_TIME AS close_time,`COMMENT` AS `comment`, PROFIT as `profit`, `COMMISSION` as brokerage_commission FROM `" . $dbName . "`.`leverate_close_trade` WHERE " . $tradeLimitQuery . " AND ((CLOSE_TIME <> '1970-01-01 00:00:00' AND  CMD IN (1,0)) OR (CMD = 6 AND `COMMENT` LIKE '%Deposit%')) " . $extraWhere;
        return $tradeQuery;
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- return Request type
     */

    protected function fireRequest($url, $headers, $params = array(), $method = 'POST') {
        $httpCustomClient = new Vtiger_Net_Client($url);
        if (count($headers)) {
            $httpCustomClient->setHeaders($headers);
        }

        switch ($method) {
            case 'POST':
                $response = $httpCustomClient->doPostWithCode($params);
                break;
            case 'GET':
                $response = $httpCustomClient->doGet($params);
                break;
        }
        return $response;
    }

    protected function headersParams($token = '') {
        $headers = array();
        if(!empty($token))
        {
            $headers = array('cache-control' => 'no-cache',
                'content-type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            );
        }
        return $headers;
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- check traders server
     */

    public function getToken($module = '') {
        global $log,$leverateLiveAccessToken, $leverateDemoAccessToken;
        $log->debug('Entering into getToken');
        $params = $this->prepareParameters();
        if ($module == 'DemoAccount') {
            $meta_trader_ip = $params['demo_meta_trader_ip'];
            $meta_trader_user = $params['demo_meta_trader_user'];
            $meta_trader_password = $params['demo_meta_trader_password'];
            $accessToken = $leverateDemoAccessToken;
        } else {
            $meta_trader_ip = $params['live_meta_trader_ip'];
            $meta_trader_user = $params['live_meta_trader_user'];
            $meta_trader_password = $params['live_meta_trader_password'];
            $accessToken = $leverateLiveAccessToken;
        }
        $url = $meta_trader_ip.$this->apiUrl.'VerifyUser';
        $params = array("userID" => $meta_trader_user, "password" => $meta_trader_password);
        $headers = $this->headersParams($accessToken);
        $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');$log->debug($responseJson);
        $response = json_decode($responseJson,true);$log->debug($response);
        $code = $response['UserVerificationResult'];$log->debug($code);
        if ($code === 0) {//success
            $log->debug('ifff');
            $response = array('Code' => 200, 'Message' => 'Ok', 'Url' => $meta_trader_ip.$this->apiUrl, 'Data' => $accessToken, 'api_url' => $meta_trader_ip.$this->userapiUrl);
        } else {//Invalid username or password
            // $errorKey = isset(self::$errorString[$response]) ? self::$errorString[$response] : '';
            $log->debug('elsess');
            $error = vtranslate('LBL_CREDENTIALS_ERROR','ModuleConfigurationEditor');
            $response = array('Code' => 201, 'Message' => $error);
        }
        return (object) $response;
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- check traders server
     */

    public function checkLogin() {
        return false;
    }

    public function checkMetaTraderServerConfiguration($client_type, $server_type, $meta_trader_ip, $meta_trader_user, $meta_trader_password, $module = '') {
        global $log,$leverateLiveAccessToken, $leverateDemoAccessToken;
        $log->debug('Entering into getToken');
        $params = $this->prepareParameters();
        if ($module == 'DemoAccount') {
            $accessToken = $leverateDemoAccessToken;
        } else {
            $accessToken = $leverateLiveAccessToken;
        }
        $url = $meta_trader_ip.$this->apiUrl.'VerifyUser';
        $params = array("userID" => $meta_trader_user, "password" => $meta_trader_password);
        $headers = $this->headersParams($accessToken);
        $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');$log->debug($responseJson);
        $response = json_decode($responseJson,true);$log->debug($response);
        $code = $response['UserVerificationResult'];$log->debug($code);
        if ($code === 0) {//success
            $log->debug('ifff');
            $response = array('Code' => 200, 'Message' => 'Ok');
        } else {//Invalid username or password
            // $errorKey = isset(self::$errorString[$response]) ? self::$errorString[$response] : '';
            $log->debug('elsess');
            $error = vtranslate('LBL_CREDENTIALS_ERROR','ModuleConfigurationEditor');
            $response = array('Code' => 201, 'Message' => $error);
        }
        return (object) $response;
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- create meta trader account
     */

    public function createAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency, $contactid='', $otherParam = array()) {
        global $log;
        $log->debug('Entering into createAccount');
        // $params = array("City" => $city, "State" => $state, "Country" => $country, "Address" => $address, "Zipcode" => $zipcode, "Phone" => $phone_number, "Comment" => $comment, "Password" => $password, "InvestorPassword" => $investor_password, "GroupName" => $account_type, "Leverage" => $leverage, "FullName" => $client_name, "Email" => $client_email);
        $currentTime = date("Y-m-d\TH:i:s\Z");
        $params = array(
            "GroupName" => $account_type, 
            // "TradingTermName" => $account_type, 
            "FullName" => $client_name, 
            "UserDetails" => array(
                  "FullName" => $client_name, 
                  "Phone" => $phone_number, 
                  "Email" => $client_email, 
                  "CreationTime" => $currentTime, 
                  "Source" => 8, 
                  "Comment" => $comment, 
                  "Country" => $country, 
                  "State" => $state, 
                  "City" => $city, 
                  "Address" => $address, 
                  "ZipCode" => $zipcode 
            ), 
            "TradingState" => 0, 
            "Tradability" => 0, 
            "Passwords" => array(
                    "Password" => $password, 
                    "InvestorPassword" => $investor_password 
            ), 
            "MarginRequirements" => array(
                        "Leverage" => $leverage, 
                        "MarginCoefficient" => 1, 
                        "UseAccountMarginRequirements" => true 
            ), 
            "InitialDepositRequestDetails" => array(
                           "Balance" => 0, 
                           "InitialDepositComment" => "" 
            ), 
            "Currency" => $currency 
            );$log->debug('$params=');$log->debug($params);
        $tokenResponse = $this->getToken();$log->debug("tokenResponse-");$log->debug($tokenResponse);
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->api_url;
            $token = $tokenResponse->Data;
            $headers = $this->headersParams($token);
            $url = $request_url.'CreateUser';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');$log->debug("createuser response-");$log->debug($responseJson);
            $response = json_decode($responseJson, true);
            $AccountNumber = $response['User']['UserID'];
            $responseObj = new stdClass();
            if (!empty($AccountNumber)) {
                $responseObj->Code = 200;
                $responseObj->Message = 'Ok';
                $responseObj->Data->login = $AccountNumber;
                return $responseObj;
            } else {
                $responseObj->Code = 400;
                $responseObj->Message = 'LBL_LIVE_ACCOUNT_CREATE_ERROR';
            }
        } else {
            return $tokenResponse;
        }
    }

    public function createDemoAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency, $contactid = '', $otherParam = array()) {
        global $log;
        $log->debug('Entering into createDemoAccount');
        $currentTime = date("Y-m-d\TH:i:s\Z");
        // $params = array("City" => $city, "State" => $state, "Country" => $country, "Address" => $address, "Zipcode" => $zipcode, "Phone" => $phone_number, "Comment" => $comment, "Password" => $password, "InvestorPassword" => $investor_password, "GroupName" => $account_type, "Leverage" => $leverage, "FullName" => $client_name, "Email" => $client_email);
        $params = array(
            "GroupName" => $account_type, 
            // "TradingTermName" => $account_type, 
            "FullName" => $client_name, 
            "UserDetails" => array(
                  "FullName" => $client_name, 
                  "Phone" => $phone_number, 
                  "Email" => $client_email, 
                  "CreationTime" => $currentTime, 
                  "Source" => 8, 
                  "Comment" => $comment, 
                  "Country" => $country, 
                  "State" => $state, 
                  "City" => $city, 
                  "Address" => $address, 
                  "ZipCode" => $zipcode 
            ), 
            "TradingState" => 0, 
            "Tradability" => 0, 
            "Passwords" => array(
                    "Password" => $password, 
                    "InvestorPassword" => $investor_password 
            ), 
            "MarginRequirements" => array(
                        "Leverage" => $leverage, 
                        "MarginCoefficient" => 1, 
                        "UseAccountMarginRequirements" => true 
            ), 
            "InitialDepositRequestDetails" => array(
                           "Balance" => 0, 
                           "InitialDepositComment" => "" 
            ), 
            "Currency" => $currency 
        );$log->debug('$params=');$log->debug($params);
        $tokenResponse = $this->getToken();$log->debug("tokenResponse-");$log->debug($tokenResponse);
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->api_url;
            $token = $tokenResponse->Data;
            $headers = $this->headersParams($token);
            $url = $request_url.'CreateUser';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');$log->debug("createuser response-");$log->debug($responseJson);
            $response = json_decode($responseJson, true);$log->debug($response);
            $AccountNumber = $response['User']['UserID'];$log->debug($AccountNumber);
            $responseObj = new stdClass();
            if (!empty($AccountNumber)) {
                $responseObj->Code = 200;
                $responseObj->Message = 'Ok';
                $responseObj->Data->login = $AccountNumber;
                return $responseObj;
            } else {
                $responseObj->Code = 400;
                $responseObj->Message = 'LBL_DEMO_ACCOUNT_CREATE_ERROR';
                return $responseObj;
            }
        } else {
            return $tokenResponse;
        }
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- add deposit into meta trader account
     */

    public function deposit($account_no, $amount, $comment) {
        global $log;
        $log->debug('Entering into deposit');
        $log->debug($account_no);$log->debug($amount);
        $tokenResponse = $this->getToken();
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->Url;
            $token = $tokenResponse->Data;
            $params = array("userID" => $account_no, "amount" => $amount, "comment" => $comment);
            $headers = $this->headersParams($token);
            $url = $request_url . 'DepositBalanceWithResult';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($responseJson, true);
            $orderId = $response['OrderID'];
            if (!empty($orderId)) {
                $response = (object) array('Code' => 200, 'Message' => 'Ok');
                return $response;
            } else {
                return $response;
            }
        } else {
            return $tokenResponse;
        }
    }

    public function depositToDemoAccount($account_no, $amount, $comment) {
        global $log;
        $log->debug('Entering into depositToDemoAccount');
        $log->debug($account_no);$log->debug($amount);
        $tokenResponse = $this->getToken('DemoAccount');
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->Url;
            $token = $tokenResponse->Data;
            $params = array("userID" => $account_no, "amount" => $amount, "comment" => $comment);
            $headers = $this->headersParams($token);
            $url = $request_url . 'DepositBalanceWithResult';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($responseJson, true);
            $orderId = $response['OrderID'];
            if (!empty($orderId)) {
                $response = (object) array('Code' => 200, 'Message' => 'Ok');
                return $response;
            } else {
                return $response;
            }
        } else {
            return $tokenResponse;
        }
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- add withdrawal into meta trader account
     */

    public function withdrawal($account_no, $amount, $comment) {
        global $log;
        $log->debug('Entering into withdrawal');
        $log->debug($account_no);$log->debug($amount);
        $tokenResponse = $this->getToken();
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->Url;
            $token = $tokenResponse->Data;
            $params = array("userID" => $account_no, "amount" => $amount, "comment" => $comment);
            $headers = $this->headersParams($token);
            $url = $request_url . 'WithdrawBalanceWithResult';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($responseJson, true);
            $orderId = $response['OrderID'];
            if (!empty($orderId)) {
                $response = (object) array('Code' => 200, 'Message' => 'Ok');
                return $response;
            } else {
                return $response;
            }
        } else {
            return $tokenResponse;
        }
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 28-02-2020
     * @ Comment:- return account info
     */

    public function getAccountInfo($account_no) {
        global $log;
        $log->debug('Entering into getAccountInfo');
        $log->debug($account_no);
        $tokenResponse = $this->getToken();
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->Url;
            $token = $tokenResponse->Data;
            $params = array("userID" => $account_no);
            $headers = $this->headersParams($token);
            $url = $request_url . 'GetAccountBalance';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($responseJson, true);
            $userId = $response['AccountBalance']['UserID'];
            $responseObj = new stdClass();
            if (!empty($userId)) {
                $responseObj->Code = 200;
                $responseObj->Message = 'Ok';
                $responseObj->Data->login = $account_no;
                $responseObj->Data->free_margin = $response['AccountBalance']['Balance'];
                return $responseObj;
            } else {
                $responseObj->Code = 400;
                $responseObj->Message = 'LBL_FAILED_TO_FETCH_BALANCE';
                return $responseObj;
            }
        } else {
            return $tokenResponse;
        }
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- return account balance
     */

    public function getBalance($account_no) {

        return false;
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- return change Leverage Balance
     */

    public function changeLeverage($account_no, $leverage) {
        $tokenResponse = $this->getToken();
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->Url;
            $token = $tokenResponse->Data;
            $params = array("Login" => $account_no, "Leverage" => $leverage);
            $headers = $this->headersParams($token);
            $url = $request_url . '/Change_Leverage/';
            $response = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($code == 200 && $messege == 'Ok') {
                return $response;
            } elseif ($code == 201) {
                return $response;
            } else {
                return $response;
            }
        } else {
            return $tokenResponse;
        }
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- return account exist or not
     */

    public function checkAccountExist($account_no) {
        global $log;
        $log->debug('Entering into checkAccountExist');
        $log->debug($account_no);
        $tokenResponse = $this->getToken();
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->Url;
            $token = $tokenResponse->Data;
            $params = array("userID" => $account_no);
            $headers = $this->headersParams($token);
            $url = $request_url . 'GetUser';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($responseJson, true);
            $userId = $response['User']['UserID'];
            $isEnabled = $response['User']['IsEnabled'];
            if (!empty($userId) && $isEnabled) {
                $response = (object) array('Code' => 200, 'Message' => 'Ok');
                return $response;
            } else {
                return $response;
            }
        } else {
            return $tokenResponse;
        }
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- return change Account Group
     */

    public function changeAccountGroup($account_no, $account_type) {
        global $log;
        $log->debug('Entering into changeAccountGroup');
        $log->debug($account_no);
        $tokenResponse = $this->getToken();
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->api_url;
            $token = $tokenResponse->Data;
            $params = [
                "UserID" => $account_no, 
                "GroupName" => $account_type 
            ];
            $headers = $this->headersParams($token);
            $url = $request_url . 'UpdateUser';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');$log->debug('responsejson');$log->debug($responseJson);
            $response = json_decode($responseJson, true);
            $code = $response['code'];
            if ($code == 204) {
                $response = (object) array('Code' => 200, 'Message' => 'Ok');
                return $response;
            } else {
                return $response;
            }
        } else {
            return $tokenResponse;
        }
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- return change Account Password
     */

    public function changePassword($account_no, $password, $IsInvestor) {
        global $log;
        $log->debug('Entering into changePassword');
        $log->debug($account_no);
        $tokenResponse = $this->getToken();
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->api_url;
            $token = $tokenResponse->Data;
            if($IsInvestor)
            {
                $params = [
                    "UserID" => $account_no, 
                    "Passwords" => [
                          "InvestorPassword" => $password 
                       ] 
                 ];
            }
            else
            {
                $params = [
                    "UserID" => $account_no, 
                    "Passwords" => [
                          "Password" => $password 
                       ] 
                 ];
            }
            $headers = $this->headersParams($token);
            $url = $request_url . 'UpdateUser';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');$log->debug('responsejson');$log->debug($responseJson);
            $response = json_decode($responseJson, true);
            $code = $response['code'];
            if ($code == 204) {
                $response = (object) array('Code' => 200, 'Message' => 'Ok');
                return $response;
            } else {
                return $response;
            }
        } else {
            return $tokenResponse;
        }
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- return change Account Disable
     */

    public function accountDisable($account_no) {
        global $log;
        $log->debug('Entering into accountDisable');
        $log->debug($account_no);
        $tokenResponse = $this->getToken();
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->Url;
            $token = $tokenResponse->Data;
            $params = array("userID" => $account_no);
            $headers = $this->headersParams($token);
            $url = $request_url . 'DisableUser';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');$log->debug('$responseJson=');$log->debug($responseJson);
            $response = json_decode($responseJson, true);
            $code = $response['code'];
            if ($code == 204) {
                $response = (object) array('Code' => 200, 'Message' => 'Ok');
                return $response;
            } else {
                return $response;
            }
        } else {
            return $tokenResponse;
        }
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- return change Demo Account Disable
     */

    public function demoaccountDisable($account_no) {
        global $log;
        $log->debug('Entering into demoaccountDisable');
        $log->debug($account_no);
        $tokenResponse = $this->getToken('DemoAccount');
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->Url;
            $token = $tokenResponse->Data;
            $params = array("userID" => $account_no);
            $headers = $this->headersParams($token);
            $url = $request_url . 'DisableUser';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');$log->debug('$responseJson=');$log->debug($responseJson);
            $response = json_decode($responseJson, true);
            $code = $response['code'];
            if ($code == 204) {
                $response = (object) array('Code' => 200, 'Message' => 'Ok');
                return $response;
            } else {
                return $response;
            }
        } else {
            return $tokenResponse;
        }
    }

    /*
     * @ Add By:- Forex Team
     * @ Date:- 15-11-2019
     * @ Comment:- return change  Account Enable
     */

    public function accountEnable($account_no) {
        global $log;
        $log->debug('Entering into accountEnable');
        $log->debug($account_no);
        $tokenResponse = $this->getToken();
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->Url;
            $token = $tokenResponse->Data;
            $params = array("userID" => $account_no);
            $headers = $this->headersParams($token);
            $url = $request_url . 'EnableUser';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');$log->debug('$responseJson=');$log->debug($responseJson);
            $response = json_decode($responseJson, true);
            $code = $response['code'];
            if ($code == 204) {
                $response = (object) array('Code' => 200, 'Message' => 'Ok');
                return $response;
            } else {
                return $response;
            }
        } else {
            return $tokenResponse;
        }
    }

    public function getBalanceUsingApi($account_no) {
        global $log;
        $log->debug('Entering into getBalanceUsingApi');
        $balanceData = array();
        $tokenResponse = $this->getToken();
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->Url;
            $token = $tokenResponse->Data;
            $params = array("userID" => $account_no);
            $headers = $this->headersParams($token);
            $url = $request_url . 'GetAccountBalance';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');$log->debug('$responseJson=');$log->debug($responseJson);
            $response = json_decode($responseJson, true);
            $log->debug('GetAccountBalance=');
            $log->debug($response);
            if(empty($response['ErrorMessage']))
            {
                $balanceData['credit'] = $response['AccountBalance']['Credit'];
                $balanceData['account_no'] = $response['AccountBalance']['UserID'];
                $balanceData['balance'] = $response['AccountBalance']['Balance'];
                $balanceData['equity'] = $response['AccountBalance']['Equity'];
                $balanceData['margin'] = $response['AccountBalance']['Margin'];
                $balanceData['free_margin'] = ($balanceData['equity'] - $balanceData['margin']);
            }
        }
        return $balanceData;
    }

    public function updateUserData($accountNo = array()) {
        global $log,$adb;
        $log->debug('Entering into updateUserData');
        $log->debug($accountNo);
        $queryValues = array();
        foreach($accountNo as $accountNumber)
        {
            /*Check balance updated within 5 min or not*/
            // $balanceQuery = "SELECT LOGIN FROM leverate_users WHERE LOGIN = ? AND MODIFY_TIME < DATE_SUB(CONVERT_TZ(NOW(), @@session.time_zone, '+00:00'),INTERVAL 5 MINUTE) ";
            $balanceQuery = "SELECT MODIFY_TIME as modify_time FROM leverate_users WHERE LOGIN = ?;";
            $balanceResult = $adb->pquery($balanceQuery, array($accountNumber));
            $numRows = $adb->num_rows($balanceResult);
            if($numRows >= 0) {
                $storedModifyTime = "0000-00-00 00:00:00";
                if($numRows > 0) {
                    $storedModifyTime = $adb->query_result($balanceResult, 0, 'modify_time');
                }
                $modifiedTime = date('Y-m-d H:i:s');
                $pastModifiedTime = date('Y-m-d H:i:s', strtotime('-5 min'));
                if($storedModifyTime < $pastModifiedTime) {
                    $balanceData = $this->getBalanceUsingApi($accountNumber);
                    $queryValues[] = "('" . $balanceData['account_no'] . "','" . $balanceData['balance'] . "','" . $balanceData['credit'] . "','" . $balanceData['equity'] . "','" . $balanceData['margin'] . "','" . $balanceData['free_margin'] . "','" . $modifiedTime . "')";
                }
            } else {
                return true;
            }
        }
        if(!empty($queryValues)) {
            $query = "REPLACE INTO leverate_users (`LOGIN`,`BALANCE`,`CREDIT`,`EQUITY`,`MARGIN`,`MARGIN_FREE`,`MODIFY_TIME`) VALUES " . implode(',', $queryValues) . ";";
            $queryResult = $adb->pquery($query, array());
            if($queryResult) {
                return true;
            }
        }
        return false;
    }

    public function getOpenTradeUsingApi() {
        global $log;
        $openTradeData = array();
        $tokenResponse = $this->getToken();
        if ($tokenResponse->Code == 200 && $tokenResponse->Message == 'Ok') {
            $request_url = $tokenResponse->Url;
            $token = $tokenResponse->Data;
            $groups = array('TradingCompetition2020');
            $params = array("groups" => $groups);
            $headers = $this->headersParams($token);
            $url = $request_url . 'GetOpenPositionsForGroups';
            $responseJson = $this->fireRequest($url, $headers, json_encode($params), 'POST');$log->debug('$responseJson=');$log->debug($responseJson);
            $response = json_decode($responseJson, true);
            $log->debug('Getopentrade=');
            $log->debug($response);
            if(empty($response['ErrorMessage']) && !empty($response['OpenPositions']))
            {
                $openTradeData = $response['OpenPositions'];
            }
        }
        return $openTradeData;
    }

    //Live Account Dashboard functions
    public function getOutstandingForLiveAccountDashboardUsingAPI($account_no = '') {
        global $log;
        $log->debug('Entering into accountEnable');
        $log->debug($account_no);
        $balanceData = $this->getBalanceUsingApi($account_no);
        return $balanceData;
    }

    public function getOutstandingForLiveAccountDashboard($account_no = '', $accountList = array()) {
        $dbName = $this->getDbName();
        if ($account_no == '') {
            if(!empty($accountList))
            {
                $this->updateUserData($accountList);
            }
            return "SELECT `LOGIN` AS `login`, `BALANCE` AS `balance`, `EQUITY` AS `equity`, `MARGIN` AS `margin`, `MARGIN_FREE` AS `margin_free` "
                    . "FROM `" . $dbName . "`. `leverate_users`";
        } else {
            $this->updateUserData(array($account_no));
            return "SELECT `LOGIN` AS `login`, `BALANCE` AS `balance`, `EQUITY` AS `equity`, `MARGIN` AS `margin`, `MARGIN_FREE` AS `margin_free` "
                    . "FROM `" . $dbName . "`. `leverate_users` WHERE `LOGIN` = '$account_no'";
        }
    }

    public function getProfitLossForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `LOGIN` AS `login`,SUM(IF(`CMD`=0 OR `CMD` = 1, `PROFIT`, 0)) `profit_loss`, " 
                . "SUM(IF(`CMD`= 0 OR `CMD` = 1, `COMMISSION`, 0)) `commission`, "
                . "SUM(IF(`CMD`= 0 OR `CMD` = 1, 0, 0)) `swap`, "
                . "SUM(IF(`CMD` = 6 AND `PROFIT` > 0, `PROFIT`, 0)) `deposit`,"
                . "ABS(SUM(IF(`CMD` = 6 AND `PROFIT` < 0,`PROFIT`, 0))) `withdraw` "
                . "FROM `" . $dbName . "`.`leverate_close_trade` WHERE `LOGIN` = '$account_no' AND `CLOSE_TIME` <> '1970-01-01 00:00:00'";
    }

    public function getOpenTradesForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `LOGIN` AS `login`,SUM(IF(`CMD`=0 ,1,0)) `buy_count`, SUM(IF(`CMD`=1,1,0)) `sell_count`, "
                . "SUM(IF(`CMD`=0 ,`VOLUME`/$this->volumeDivideby,0)) `buy_volume`, SUM(IF(`CMD` = 1, `VOLUME`/$this->volumeDivideby,0)) `sell_volume` "
                . "FROM `" . $dbName . "`.`leverate_open_trade` WHERE `LOGIN` = '$account_no'";
    }

    public function getCloseTradesForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `LOGIN` AS `login`, SUM(IF(`PROFIT` + `COMMISSION` >= 0,1,0)) `profit_count`, "
                . "SUM(IF(`PROFIT` + `COMMISSION` < 0,1,0)) `loss_count` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` IN(0,1) AND `LOGIN` = '$account_no'";
    }

    public function getClosedTradesListForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT  `LOGIN` AS `login`, `OPEN_TIME` AS `open_time`, `CLOSE_TIME` AS `close_time`, `SYMBOL` AS `symbol`, "
                . "`VOLUME`/$this->volumeDivideby AS `volume`, `OPEN_PRICE` AS open_price,`CLOSE_PRICE` AS close_price, "
                . "`PROFIT` AS `profit`, '0' AS `is_open` FROM `" . $dbName . "`.`leverate_close_trade` WHERE  `LOGIN` = '$account_no' "
                . "AND `CMD` IN (1,0) AND `CLOSE_TIME` <> '1970-01-01 00:00:00' ORDER BY `TICKET` DESC LIMIT 0,5";
    }

    public function getOpenTradesListForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT  `LOGIN` AS `login`, `OPEN_TIME` AS `open_time`, `SYMBOL` AS `symbol`, "
                . "`VOLUME`/$this->volumeDivideby AS `volume`, `OPEN_PRICE` AS open_price, `PROFIT` AS `profit`, '1' AS `is_open`"
                . "FROM `" . $dbName . "`.`leverate_open_trade` WHERE  `LOGIN` = '$account_no' AND `CMD` IN (1,0)"
                . "  ORDER BY `TICKET` DESC LIMIT 0,5";
    }

    public function getSymbolPerformanceForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `SYMBOL` AS `symbol`,  COUNT(`SYMBOL`) AS `symbol_count`, SUM(`Volume`/$this->volumeDivideby) AS `sum_volume`, `CLOSE_TIME` AS `close_time` FROM  `" . $dbName . "`.`leverate_close_trade` WHERE `LOGIN` = '$account_no' AND
        `CLOSE_TIME` != '1970-01-01 00:00:00' AND `CMD` IN (0,1) GROUP BY `symbol` ORDER BY `sum_volume` DESC LIMIT 0,5";
    }

    public function getTradesStreakForLiveAccountDashboard($account_no = '', $trades_streak_name = '') {
        $dbName = $this->getDbName();
        switch ($trades_streak_name) {
            case 'most_and_least_effective_symbol':
                return "SELECT `a`.`SYMBOL`, COUNT(`a`.`SYMBOL`) AS `total_trade`, (SELECT COUNT(`SYMBOL`) FROM `" . $dbName . "`.`leverate_close_trade` "
                        . "WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' AND `CMD` IN(0,1) AND `LOGIN` = '$account_no' "
                        . "AND `PROFIT` > 0 AND `SYMBOL` = `a`.`SYMBOL`) AS `winning_trade`, ((SELECT COUNT(`SYMBOL`) "
                        . "FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' AND `CMD` IN(0,1) AND "
                        . "`LOGIN` = '$account_no' AND `PROFIT` > 0 AND `SYMBOL` = `a`.`SYMBOL`) / COUNT(`a`.`SYMBOL`) * 100) "
                        . "AS `winning_ratio` FROM `" . $dbName . "`.`leverate_close_trade`  AS `a` WHERE `a`.`CLOSE_TIME` <> '1970-01-01 00:00:00' "
                        . "AND `a`.`CMD` IN(0,1) AND `a`.`LOGIN` = '$account_no' GROUP BY `a`.`SYMBOL`";
                break;
            case 'longest_winning_and_losing_streak':
                return "SELECT `SYMBOL` as `symbol`, `TICKET` as `ticket`, `PROFIT` as `profit`, `CLOSE_TIME` as `close_time` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CLOSE_TIME` <> "
                        . "'1970-01-01 00:00:00' AND `CMD` IN(0,1) AND `LOGIN` = '$account_no' ORDER BY `CLOSE_TIME` DESC";
                break;
            default:
                return false;
                break;
        }
    }

    public function getTop5WinningTradesForLiveAccountDashboard($account_no) {
        $dbName = $this->getDbName();
        return "SELECT `SYMBOL` AS `symbol`, `CLOSE_TIME` AS `close_time`, `VOLUME`/$this->volumeDivideby AS `volume`, `CLOSE_PRICE` AS close_price, `PROFIT` AS `profit` FROM `" . $dbName . "`.`leverate_close_trade` WHERE  `LOGIN` = " . $account_no . " AND `CLOSE_TIME` <> '1970-01-01 00:00:00' AND `CMD` IN(0,1) HAVING `PROFIT` > 0 ORDER BY `PROFIT` DESC LIMIT 0,5";
    }

    public function getTop5LossingTradesForLiveAccountDashboard($account_no) {
        $dbName = $this->getDbName();
        return "SELECT `SYMBOL` AS `symbol`, `CLOSE_TIME` AS `close_time`, `VOLUME`/$this->volumeDivideby AS `volume`, `CLOSE_PRICE` AS close_price, `PROFIT` AS `profit` FROM `" . $dbName . "`.`leverate_close_trade` WHERE  `LOGIN` = " . $account_no . " AND `CLOSE_TIME` <> '1970-01-01 00:00:00' AND `CMD` IN(0,1) HAVING `PROFIT` < 0 ORDER BY `PROFIT` ASC LIMIT 0,5";
    }

    public function getProfitForLiveAccountDashboard($account_no, $from_date, $to_date, $day_month) {
        $dbName = $this->getDbName();
        $day_month_query_col = "DATE_FORMAT(`CLOSE_TIME`, '%Y-%m') AS `Month`";
        $day_month_query_group_by = "`Month`";
        if ($day_month == 'daily') {
            $day_month_query_col = "DATE_FORMAT(`CLOSE_TIME`, '%d') AS `Day`";
            $day_month_query_group_by = "`Day`";
        }
        return "SELECT " . $day_month_query_col . ", SUM(`PROFIT`) AS `profit` FROM `" . $dbName . "`.`leverate_close_trade` "
                . "WHERE `CMD` IN(0,1) AND `PROFIT` > 0 AND `CLOSE_TIME` > '1970-01-01' AND `LOGIN` = " . $account_no . " AND `CLOSE_TIME` >= '" . $from_date . "' AND `CLOSE_TIME` <= '" . $to_date . "' GROUP BY " . $day_month_query_group_by;
    }

    public function getLossForLiveAccountDashboard($account_no, $from_date, $to_date, $day_month) {
        $dbName = $this->getDbName();
        $day_month_query_col = "DATE_FORMAT(`CLOSE_TIME`, '%Y-%m') AS `Month`";
        $day_month_query_group_by = "`Month`";
        if ($day_month == 'daily') {
            $day_month_query_col = "DATE_FORMAT(`CLOSE_TIME`, '%d') AS `Day`";
            $day_month_query_group_by = "`Day`";
        }
        return "SELECT " . $day_month_query_col . ", SUM(`PROFIT`) AS `loss` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CMD` IN(0,1) AND `PROFIT` < 0 AND `LOGIN` = " . $account_no . " AND `CLOSE_TIME` >= '" . $from_date . "' AND `CLOSE_TIME` <= '" . $to_date . "' GROUP BY " . $day_month_query_group_by;
    }

    //END Live Account Dashboard functions
    //Transaction and Trade Report
    public function getTradesForReport($trade_type, $account_no) {
        $dbName = $this->getDbName();
        $accountWhere = "";
        $serverName = $this->getName();
        $sql = '';
        if (!empty($account_no)) {
            $accountWhere = " AND `LOGIN` = " . $account_no;
        }

        if ($trade_type == 'open') {
            $sql = "SELECT  '$serverName' AS `server_type`, `LOGIN` AS `login`, `TICKET` AS `ticket`, `SYMBOL` AS `symbol`, `VOLUME`/$this->volumeDivideby AS `volume`, "
                    . " `CMD` AS `cmd`, `OPEN_TIME` AS `open_time`, `OPEN_PRICE` AS open_price,"
                    . " `TP` as `tp`, `SL` as `sl`, `COMMISSION` as `commission`, 0 as `swaps`, "
                    . " `PROFIT` AS `profit` FROM `" . $dbName . "`.`leverate_open_trade` WHERE `CMD` IN (1,0) " . $accountWhere;
        }
        if ($trade_type == 'close') {
            $sql = "SELECT  '$serverName' AS `server_type`, `LOGIN` AS `login`, `TICKET` AS `ticket`, `SYMBOL` AS `symbol`, `VOLUME`/$this->volumeDivideby AS `volume`, "
                    . " `CMD` AS `cmd`, `OPEN_TIME` AS `open_time`, `OPEN_PRICE` AS `open_price`, "
                    . " `CLOSE_TIME` AS `close_time`, `CLOSE_PRICE` AS `close_price`, `TP` as `tp`, "
                    . "`SL` as `sl`, `COMMISSION` as `commission`, 0 as `swaps`, "
                    . " `PROFIT` AS `profit` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CMD` IN (1,0) AND `CLOSE_TIME` <> '1970-01-01 00:00:00' " . $accountWhere;
        }
        return $sql;
    }

    public function getCountQueryForSubIbTransactionReport($account_no, $from_date, $to_date) {
        $dbName = $this->getDbName();
        return "SELECT count(1) AS `count` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CMD` = 6
        AND `CLOSE_TIME` <> '1970-01-01 00:00:00'
        AND `CLOSE_TIME` >= '" . $from_date . "'
        AND `CLOSE_TIME` <= '" . $to_date . "'
        AND `LOGIN` = " . $account_no;
    }

    public function getTransactionsForReport($account_no, $from_date, $to_date) {
        $dbName = $this->getDbName();
        return "SELECT  `LOGIN` AS `login`, `TICKET` AS `ticket`, `CLOSE_TIME` AS `close_time`, "
                . "`PROFIT` AS `profit`, `COMMENT` AS `comment` FROM `" . $dbName . "`.`leverate_close_trade`
                WHERE `CMD` = 6 AND `CLOSE_TIME` <> '1970-01-01 00:00:00'
                AND `CLOSE_TIME` >= '" . $from_date . "'
        AND `CLOSE_TIME` <= '" . $to_date . "'
        AND `LOGIN` = " . $account_no;
    }

    //End Trade Report

    public function getOpenTradesForIBDashboard($account_no, $filter = '') {
        $dbName = $this->getDbName();
        $filter_query = '';
        if ($filter == 'Current Month') {
            $filter_query = " AND MONTH(`OPEN_TIME`) = MONTH(CURRENT_DATE())";
        }
        if ($filter == 'Last Month') {
            $filter_query = "AND MONTH(`OPEN_TIME`) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
        }
        return "SELECT `LOGIN` AS `login` FROM `" . $dbName . "`.`leverate_open_trade` WHERE `LOGIN` = '$account_no'" . $filter_query;
    }

    public function getCloseTradesForIBDashboard($account_no, $filter = '') {
        $dbName = $this->getDbName();
        $filter_query = '';
        if ($filter == 'Current Month') {
            $filter_query = " AND MONTH(`CLOSE_TIME`) = MONTH(CURRENT_DATE())";
        }
        if ($filter == 'Last Month') {
            $filter_query = " AND MONTH(`CLOSE_TIME`) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
        }
        return "SELECT `LOGIN` AS `login` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` IN(0,1) AND `LOGIN` = '$account_no'" . $filter_query;
    }

    //For Main Dashboard
    public function getTotalVolumeAndProfitLossForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`VOLUME`/$this->volumeDivideby) AS `total_volume`, SUM(`PROFIT`) AS `total_profit_loss` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` IN(0,1) AND `LOGIN`";
    }

    public function getTotalVolumeAndProfitLossForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT (`VOLUME`/$this->volumeDivideby) AS `volume`, `PROFIT` AS `profit`, `LOGIN` AS `login` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` IN(0,1)";
    }

    public function getTotalDepositForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`PROFIT`) AS `total_deposit` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` = 6 AND `PROFIT` > 0 AND `LOGIN`";
    }

    public function getTotalDepositForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`PROFIT`) AS `total_deposit` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` = 6 AND `PROFIT` > 0 AND `LOGIN`";
    }

    public function getTotalWithdrawalForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`PROFIT`) AS `total_withdrawal` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` = 6 AND `PROFIT` < 0 AND `LOGIN`";
    }

    public function getTotalWithdrawalForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`PROFIT`) AS `total_withdrawal` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` = 6 AND `PROFIT` < 0 AND `LOGIN`";
    }

    public function getTotalVolumeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`VOLUME`/$this->volumeDivideby) AS `total_volume` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CMD` IN(0,1) AND `LOGIN`";
    }

    public function getTotalVolumeForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`VOLUME`/$this->volumeDivideby) AS `total_volume`, `LOGIN` AS `login` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CMD` IN(0,1) AND `LOGIN`";
    }

    public function getOpenVolumeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`VOLUME`/$this->volumeDivideby) AS `total_volume` FROM `" . $dbName . "`.`leverate_open_trade` WHERE `CMD` IN(0,1) AND `LOGIN`";
    }

    public function getOpenVolumeForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`VOLUME`/$this->volumeDivideby) AS `open_volume`, `LOGIN` AS `login` FROM `" . $dbName . "`.`leverate_open_trade` WHERE `CMD` IN(0,1) AND `LOGIN`";
    }

    public function getWinTradeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT DATE_FORMAT(`CLOSE_TIME`, '%Y-%m') AS Month, COUNT(*) AS `win_trade` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CMD` IN(0,1) AND `PROFIT` > 0 AND `CLOSE_TIME` > '1970-01-01' AND `LOGIN`";
    }

    public function getLossTradeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT DATE_FORMAT(`CLOSE_TIME`, '%Y-%m') AS Month, COUNT(*) AS `loss_trade` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CMD` IN(0,1) AND `PROFIT` < 0 AND `CLOSE_TIME` > '1970-01-01' AND `LOGIN`";
    }

    public function getAccountsDataForMainDashboard($accountList = array()) {
        $dbName = $this->getDbName();
        if(!empty($accountList))
        {
            $this->updateUserData($accountList);
        }
        return "SELECT `BALANCE` AS `balance`, `EQUITY` AS `equity` , `MARGIN` AS `margin`, `MARGIN_FREE` AS `margin_free`, `LOGIN` AS `login` FROM `" . $dbName . "`.`leverate_users`";
    }

    public function getAccountsDataForMobileDashboard($accountList = array()) {
        $dbName = $this->getDbName();
        if(!empty($accountList))
        {
            $this->updateUserData($accountList);
        }
        return "SELECT `BALANCE` AS `balance`, `EQUITY` AS `equity` , `MARGIN` AS `margin`, `MARGIN_FREE` AS `margin_free`, `LOGIN` AS `login` FROM `" . $dbName . "`.`leverate_users`";
    }

    public function getGroupByForMainDashboard() {
        return " GROUP BY DATE_FORMAT(`close_time`, '%Y-%m')";
    }

    public function getDateFilterGreaterForMainDashboard() {
        return " AND `close_time` >= '";
    }

    public function getDateFilterLessForMainDashboard() {
        return "' AND `close_time` <= '";
    }

    public function getTradingTimeConditions($trade_type, $startDateTime, $endDateTime) {
        if ($trade_type == 'open') {
            $AND = " AND (trades.OPEN_TIME between '" . $startDateTime . "' and '" . $endDateTime . "') ";
        } else if ($trade_type == 'close') {
            $AND = " AND (trades.CLOSE_TIME between '" . $startDateTime . "' and '" . $endDateTime . "') ";
        }
        return $AND;
    }

    public function getTradingOrderByConditions($trade_type = '') {
        $ORDERBY = ' ORDER BY `trades`.`TICKET` ';
        return $ORDERBY;
    }

    public function getTranTimeConditions($startDateTime, $endDateTime) {
        $AND = " AND  `trades`.`close_time` >= '" . $startDateTime . "' AND `trades`.`close_time` <= '" . $endDateTime . "' ";
        return $AND;
    }

    public function getTranOrderByConditions($isCabinetReq = false) {
        if(!($isCabinetReq))
        {
            $ORDERBY = " ORDER BY `trades`.`ticket` ";
        }
        else
        {
            $ORDERBY = " `trades`.`ticket` ";
        }
        return $ORDERBY;
    }

    public function getProviderSpecificData($result = array()) {
        $returnResult = array();
        $returnResult['ticket_no'] = $result['ticket'];
        $returnResult['close_time'] = $result['close_time'];
        return $returnResult;
    }

    public function getProviderSpecificTradeData($result = array(), $trade_type = '') {
        $returnResult = array();
        $returnResult['login'] = $result['login'];
        $returnResult['ticket'] = $result['ticket'];
        $returnResult['symbol'] = $result['symbol'];
        $returnResult['volume'] = floatval($result['volume']);
        $cmd = $result['cmd'];
        $returnResult['type'] = ($cmd) ? 'Sell' : 'Buy';
        $returnResult['open_time'] = $result['open_time'];
        $returnResult['open_price'] = floatval($result['open_price']);
        if ($trade_type == 'close') {
            $returnResult['close_time'] = $result['close_time'];
            $returnResult['close_price'] = floatval($result['close_price']);
        }
        $returnResult['take_profit'] = $result['tp'];
        $returnResult['stop_loss'] = $result['sl'];
        $returnResult['commission'] = number_format($result['commission'], 4);
        $returnResult['swaps'] = $result['swaps'];
        $returnResult['profit'] = $result['profit'];
        return $returnResult;
    }

    public function updateAccountParams($account_no = '', $recordId = '')
    {
        global $adb;
        $dbName = $this->getDbName();
        $luv_table_exist = $adb->pquery("SELECT 1 FROM `" . $dbName . "`.`leverate_users` LIMIT 1", array());
        if ($luv_table_exist !== FALSE && !empty($account_no))
        {
            $this->updateUserData(array($account_no));
            $query = 'SELECT luv.`LOGIN` AS `LOGIN`, luv.`BALANCE` AS `BALANCE`, luv.`EQUITY` AS `EQUITY`,luv.`MARGIN_FREE` AS `MARGIN_FREE` FROM `' . $dbName . '`.`leverate_users` as luv WHERE luv.LOGIN =?';

            $result = $adb->pquery($query, array($account_no));
            $balance = $adb->query_result($result, 0, 'balance');
            $margin_free = $adb->query_result($result, 0, 'margin_free');
            $equity = $adb->query_result($result, 0, 'equity');

            $update_query = "UPDATE vtiger_liveaccount SET current_balance=?,free_margin=?,equity=? WHERE liveaccountid=?";
            $adb->pquery($update_query, array($balance,$margin_free,$equity,$recordId));
        }
    }
    
    public function getCurrentMonthTotalVolume() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`VOLUME`/$this->volumeDivideby) AS `total_volume` FROM `" . $dbName . "`.`leverate_close_trade` WHERE (CLOSE_TIME <> '1970-01-01 00:00:00' AND  CMD IN (1,0)) AND `LOGIN` = ? AND `CLOSE_TIME` BETWEEN ? AND ?";
    }
    
    public function getEquity() {
        $dbName = $this->getDbName();
        return "SELECT `EQUITY` AS `equity` FROM `" . $dbName . "`.`leverate_users` WHERE `LOGIN` = ?";
    }
    
    public function isInvestorButtonActive()
    {
        $status = true;
        $providerParams = $this->prepareParameters();
        if(isset($providerParams['investor_pass_enable']) && strtolower($providerParams['investor_pass_enable']) == 'no')
        {
           $status = false; 
        }
        return $status;
    }

    public function checkIsExistOpenTradesOfAccount() {
        $dbName = $this->getDbName();
        return "SELECT count(`LOGIN`) AS `Login` FROM `" . $dbName . "`.`leverate_open_trade` WHERE `LOGIN` = ?";
    }

    public function getTotalVolumeQuery() {
        $dbName = $this->getDbName();
        return "SELECT (`VOLUME`/$this->volumeDivideby) AS `volume`, `LOGIN` AS `login` FROM `" . $dbName . "`.`leverate_close_trade` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` IN(0,1) AND `CLOSE_TIME` BETWEEN ? AND ?";
    }
}
