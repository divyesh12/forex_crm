<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

//require_once 'vtlib/Vtiger/Net/Client.php';
//require_once '../models/ITradingPlatform.php';

class ServiceProviders_MT4_Provider implements ServiceProviders_ITradingPlatform_Model {

    private $userName;
    private $password;
    //private $parameters = array();
    public $syncConfig = array(
        "open_trade" => true, //false = sync using direct api
        "close_trade" => true,// true = sync using database
        "balance" => true,
    );

    public static $REQUIRED_PARAMETERS = array(
        array('name' => 'client_type', 'label' => 'Client Type', 'type' => 'picklist', 'picklistvalues' => array('Partner' => 'Partner'), 'mandatory' => true),
//        array('name' => 'server_type', 'label' => 'Server Type', 'type' => 'picklist', 'picklistvalues' => array('MT4' => 'MT4')),
        //        array('name' => 'meta_trader_ip', 'label' => 'MT IP', 'type' => 'text'),
        //        array('name' => 'meta_trader_user', 'label' => 'MT User', 'type' => 'text'),
        //        array('name' => 'meta_trader_password', 'label' => 'MT Password', 'type' => 'password'),
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
//        array('name' => 'ib_start_ticketid', 'label' => 'IB Start Ticket No', 'type' => 'number'),
        array('name' => 'trade_date', 'label' => 'Trade Date', 'type' => 'date'),
        array('name' => 'investor_pass_enable', 'label' => 'Investor Password Enable', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No')),
        array('name' => 'meta_trader_ios_link', 'label' => 'IOS', 'type' => 'text'),
        array('name' => 'meta_trader_android_link', 'label' => 'Android', 'type' => 'text'),
        array('name' => 'meta_trader_windows_link', 'label' => 'Windows', 'type' => 'text'),
        array('name' => 'sequence_number', 'label' => 'Sequence Number', 'type' => 'number'),
    );

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName() {
        return 'MT4';
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
//        return 'Twilio.tpl';
        return 'TextAnyWhereEditField.tpl'; //Chnaged tpl file By Reena Hingol 09-10-2019
    }

    public function getCloseTradesQueryForCommission($maxTicket) {
        $serverName = $this->getName();
        $dbName = $this->getDbName();
        return "SELECT  '$serverName'   AS server_type,  `TICKET` AS ticket,`LOGIN` AS login,`SYMBOL` AS symbol,`DIGITS` AS digits,`CMD` AS cmd,IF(CMD=6,true,false) is_deposit, `VOLUME`/100 AS volume,`OPEN_PRICE` AS open_price,`CLOSE_PRICE` AS close_price,OPEN_TIME AS open_time, CLOSE_TIME AS close_time,`COMMENT` AS `comment`, PROFIT as `profit` FROM `" . $dbName . "`. `mt4_trades` WHERE  `TICKET` > $maxTicket AND  ((CLOSE_TIME <> '1970-01-01 00:00:00' AND  CMD IN (1,0)) OR (CMD = 6 AND `COMMENT` LIKE '%Deposit%'))";
    }

    public function getCloseTradesQueryForCommissionByCloseTime($closeTime) {
        $serverName = $this->getName();
        $dbName = $this->getDbName();
        return "SELECT  '$serverName'   AS server_type,  `TICKET` AS ticket,`LOGIN` AS login,`SYMBOL` AS symbol,`DIGITS` AS digits,`CMD` AS cmd,IF(CMD=6,true,false) is_deposit, `VOLUME`/100 AS volume,`OPEN_PRICE` AS open_price,`CLOSE_PRICE` AS close_price,OPEN_TIME AS open_time, CLOSE_TIME AS close_time,`COMMENT` AS `comment`, PROFIT as `profit`, `COMMISSION` as brokerage_commission FROM `" . $dbName . "`. `mt4_trades` WHERE  `CLOSE_TIME` > '$closeTime' AND  ((CLOSE_TIME <> '1970-01-01 00:00:00' AND  CMD IN (1,0)) OR (CMD = 6 AND `COMMENT` LIKE '%Deposit%'))";
    }

    /**
     * This function is used to get MT4 trades which are missing in trade commission calculation
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
        $tradeQuery = "SELECT  '$serverName' AS server_type, `TICKET` AS ticket,`LOGIN` AS login,`SYMBOL` AS symbol,`DIGITS` AS digits,`CMD` AS cmd,IF(CMD=6,true,false) is_deposit, `VOLUME`/100 AS volume,`OPEN_PRICE` AS open_price,`CLOSE_PRICE` AS close_price,OPEN_TIME AS open_time, CLOSE_TIME AS close_time,`COMMENT` AS `comment`, PROFIT as `profit`, `COMMISSION` as brokerage_commission FROM `" . $dbName . "`. `mt4_trades` WHERE " . $tradeLimitQuery . " AND ((CLOSE_TIME <> '1970-01-01 00:00:00' AND  CMD IN (1,0)) OR (CMD = 6 AND `COMMENT` LIKE '%Deposit%')) " . $extraWhere;
        return $tradeQuery;
    }

    /*
     * @ Add By:- Divyesh
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
                $response = $httpCustomClient->doPost($params);
                break;
            case 'GET':
                $response = $httpCustomClient->doGet($params);
                break;
        }
        return $response;
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- set header parameter
     */

//     protected function headersParams() {
    //        global $metaTrader_details;
    //        $params = $this->prepareParameters();
    //        $clientId = $params['client_id'];
    //        $client_secret = $params['client_secret'];
    //        $authorizationKey = 'Basic ' . base64_encode("$clientId:$client_secret");
    //        $headers = array(
    //            'cache-control' => 'no-cache',
    //            'content-type' => 'application/json',
    //            'authorization' => $authorizationKey
    //        );
    //        return $headers;
    //    }
    //
    protected function headersParams($token = '') {

        //   $params = $this->prepareParameters();
        //$clientId = $params['client_id'];
        //$client_secret = $params['client_secret'];
        //$authorizationKey = 'Basic ' . base64_encode("$clientId:$client_secret");
        $headers = array('cache-control' => 'no-cache',
            'content-type' => 'application/json');
        if ($token != '') {
            $headers['Token'] = $token;
        }
        return $headers;
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- check traders server
     */

    public function getToken($module = '') {
        global $token_URL, $MT4_clientId, $MT4_client_secret;

        $params = $this->prepareParameters();
        $url = $token_URL;
        $clientId = $MT4_clientId;
        $client_secret = $MT4_client_secret;
        $client_type = $params['client_type'];
        //$server_type = $params['server_type'];
        $server_type = $this->getName();
//        $meta_trader_ip = $params['meta_trader_ip'];
        //        $meta_trader_user = $params['meta_trader_user'];
        //        $meta_trader_password = $params['meta_trader_password'];
        if ($module == 'DemoAccount') {
            $meta_trader_ip = $params['demo_meta_trader_ip'];
            $meta_trader_user = $params['demo_meta_trader_user'];
            $meta_trader_password = $params['demo_meta_trader_password'];
        } else {
            $meta_trader_ip = $params['live_meta_trader_ip'];
            $meta_trader_user = $params['live_meta_trader_user'];
            $meta_trader_password = $params['live_meta_trader_password'];
        }

        $params = array("ClientId" => $clientId, "ClientSecretKey" => $client_secret, "ClientType" => $client_type, "Servertype" => $server_type, "MtIp" => $meta_trader_ip, "MtUser" => $meta_trader_user, "MtPassword" => $meta_trader_password);
//        echo "<pre>";
        //        print_r($params);
        //        exit;
        $headers = $this->headersParams();
        $response = $this->fireRequest($url, $headers, json_encode($params), 'POST');
        $response = json_decode($response);
//        echo "<pre>";
        //        print_r($response);
        //        exit;
        $code = $response->Code;
        $messege = $response->Message;
        if ($code == 200 && $messege == 'Ok') {
            //define("MT_TOKEN", $response->Data);
            //$result_response = array('token' => $response->Data, 'request_url' => $response->Url, 'responce' => true);
            return $response;
        } else {
            //define("MT_TOKEN", "");
            return $response;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- check traders server
     */

    public function checkLogin() {
        $token_responce = $this->getToken();

        $request_url = $token_responce->Url;
        $token = $token_responce->Data;
        $headers = $this->headersParams($token);
        $url = $request_url . 'checkLogin/';
        $response = $this->fireRequest($url, $headers, array(), 'GET');
        $response = json_decode($response);
        $Code = $response->Code;
        //$Message = $response->Message;
        if ($Code == 200) {
            return $response;
        } else {
            return $response;
        }
    }

    public function checkMetaTraderServerConfiguration($client_type, $server_type, $meta_trader_ip, $meta_trader_user, $meta_trader_password, $module = '') {
        global $token_URL, $MT4_clientId, $MT4_client_secret;
        $url = $token_URL;
        $clientId = $MT4_clientId;
        $client_secret = $MT4_client_secret;
        $params = array("ClientId" => $clientId, "ClientSecretKey" => $client_secret, "ClientType" => $client_type, "Servertype" => $server_type, "MtIp" => $meta_trader_ip, "MtUser" => $meta_trader_user, "MtPassword" => $meta_trader_password);
        $headers = $this->headersParams();
        $response = $this->fireRequest($url, $headers, json_encode($params), 'POST');
        $token_responce = json_decode($response);
        $code = $token_responce->Code;
        $messege = $token_responce->Message;
        if ($code == 200 && $messege == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $headers = $this->headersParams($token);
            $url = $request_url . 'checkLogin/';
            $response = $this->fireRequest($url, $headers, array(), 'GET');
            $response = json_decode($response);
            $Code = $response->Code;
            if ($Code == 200) {
                return $response;
            } else {
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- create meta trader account
     */

    public function createAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency, $contactid='', $otherParam = array()) {

        //;
        //        $checklogin = $this->checkLogin();
        //        $request_url = $MT4_request_URL;

        $liveAccountMethod = configvar('live_account_no_method');
        if ($liveAccountMethod == 'common_series') {
            $isAllowSeries = true;
        } else if ($liveAccountMethod == 'group_series') {
            $isAllowGroupSeries = true;
        } else {
            $account_no = '';
        }

        $params = array("City" => $city, "State" => $state, "Country" => $country, "Address" => $address, "Zipcode" => $zipcode, "PhoneNumber" => $phone_number, "Comment" => $comment, "Login" => $account_no, "Password" => $password, "InvestorPassword" => $investor_password, "PhonePassword" => $phonepassword, "Group" => $account_type, "Leverage" => $leverage, "Name" => $client_name, "Email" => $client_email);

        if ($isAllowSeries || $isAllowGroupSeries) {
            if ($isAllowSeries && !$isAllowGroupSeries) {
                $provider_params = $this->prepareParameters();
                $start_range = (int) $provider_params['liveacc_start_range'];
                $end_range = (int) $provider_params['liveacc_end_range'];
            } elseif (!$isAllowSeries && $isAllowGroupSeries) {
                $group_series_data = getLiveAccountSeriesBaseOnAccountType('MT4', str_replace("\\", ":", $account_type), $label_account_type, $currency);
                $start_range = (int) $group_series_data['start_range'];
                $end_range = (int) $group_series_data['end_range'];
            }

            if ($account_no > $end_range && isset($end_range)) {
                $responce = (object) array('Code' => 201);
                return $responce;
            }
        }

        $token_responce = $this->getToken();
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $headers = $this->headersParams($token);
            $url = $request_url . '/Create_User/';
            $response = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($isAllowSeries) {
                if ($code == 200 && $messege == 'Ok') {
                    return $response;
                } elseif ($code == 206 || $code == 214) {
                    $login = $account_no + 1;
                    $account_no = $login;
                    return $this->createAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency);
                } else {
                    return $response;
                }
            } else {
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    public function createDemoAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency, $contactid = '', $otherParam = array()) {

        //;
        //        $checklogin = $this->checkLogin();
        //        $request_url = $MT4_request_URL;

        $demoAccountMethod = configvar('demo_account_no_method');

        if ($demoAccountMethod == 'common_series') {
            $isAllowSeries = true;
        } else if ($demoAccountMethod == 'group_series') {
            $isAllowGroupSeries = true;
        } else {
            $account_no = '';
        }

        $params = array("City" => $city, "State" => $state, "Country" => $country, "Address" => $address, "Zipcode" => $zipcode, "PhoneNumber" => $phone_number, "Comment" => $comment, "Login" => $account_no, "Password" => $password, "InvestorPassword" => $investor_password, "PhonePassword" => $phonepassword, "Group" => $account_type, "Leverage" => $leverage, "Name" => $client_name, "Email" => $client_email);

        if ($isAllowSeries || $isAllowGroupSeries) {
            if ($isAllowSeries && !$isAllowGroupSeries) {
                $provider_params = $this->prepareParameters();
                $start_range = (int) $provider_params['demoacc_start_range'];
                $end_range = (int) $provider_params['demoacc_end_range'];
            } elseif (!$isAllowSeries && $isAllowGroupSeries) {
                $group_series_data = getDemoAccountSeriesBaseOnAccountType('MT4', str_replace("\\", ":", $account_type), $label_account_type, $currency);
                $start_range = (int) $group_series_data['start_range'];
                $end_range = (int) $group_series_data['end_range'];
            }

            if ($account_no > $end_range && isset($end_range)) {
                $responce = (object) array('Code' => 201);
                return $responce;
            }
        }

        $token_responce = $this->getToken('DemoAccount');
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $headers = $this->headersParams($token);
            $url = $request_url . '/Create_User/';
            $response = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($isAllowSeries) {
                if ($code == 200 && $messege == 'Ok') {
                    return $response;
                } elseif ($code == 206 || $code == 214) {
                    $login = $account_no + 1;
                    $account_no = $login;
                    return $this->createDemoAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency);
                } else {
                    return $response;
                }
            } else {
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- add deposit into meta trader account
     */

    public function deposit($account_no, $amount, $comment) {
        $token_responce = $this->getToken();
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $params = array("Login" => $account_no, "Amount" => $amount, "Type" => 'IN', "Comment" => $comment);
            $headers = $this->headersParams($token);
            $url = $request_url . '/Change_Balance/';
            $response = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($code == 200 && $messege == 'Ok') {
                return $response;
            } else {
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    public function depositToDemoAccount($account_no, $amount, $comment) {

        $token_responce = $this->getToken('DemoAccount');
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $params = array("Login" => $account_no, "Amount" => $amount, "Type" => 'IN', "Comment" => $comment);
            $headers = $this->headersParams($token);
            $url = $request_url . '/Change_Balance/';
            $response = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($code == 200 && $messege == 'Ok') {
                return $response;
            } else {
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- add withdrawal into meta trader account
     */

    public function withdrawal($account_no, $amount, $comment) {

        //$request_url = $MT4_request_URL;
        // $checklogin = $this->checkLogin();
        $token_responce = $this->getToken();
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $params = array("Login" => $account_no, "Amount" => $amount, "Type" => 'OUT', "Comment" => $comment);
            $headers = $this->headersParams($token);
            $url = $request_url . '/Change_Balance/';
            $response = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($code == 200 && $messege == 'Ok') {
                return $response;
            } else {
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    /*
     * @ Add By:- Sandeep
     * @ Date:- 28-02-2020
     * @ Comment:- return account info
     */

    public function getAccountInfo($account_no) {

        $token_responce = $this->getToken();
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $headers = $this->headersParams($token);
            $url = $request_url . '/Account_Info/?Login=' . $account_no;
            $response = $this->fireRequest($url, $headers, array(), 'GET');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($code == 200 && $messege == 'Ok') {
                return $response;
            } else {
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return account balance
     */

    public function getBalance($account_no) {

        $token_responce = $this->getToken();
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $headers = $this->headersParams($token);
            $url = $request_url . '/Account_Balance/?Login=' . $account_no;
            $response = $this->fireRequest($url, $headers, array(), 'GET');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($code == 200 && $messege == 'Ok') {
                return $response;
            } else {
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return change Leverage Balance
     */

    public function changeLeverage($account_no, $leverage) {

//        $checklogin = $this->CheckLogin($metatrader_type);
        $token_responce = $this->getToken();
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
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
            return $token_responce;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return account exist or not
     */

    public function checkAccountExist($account_no) {

        $token_responce = $this->getToken();
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $headers = $this->headersParams($token);
            $url = $request_url . '/User_Exist/?Login=' . $account_no;
            $response = $this->fireRequest($url, $headers, array(), 'GET');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($code == 200 && $messege == 'Ok') {
                //  return true;
                return $response;
                //return $response;
            } else {
                //return array(false, $response->Message);
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return change Account Group
     */

    public function changeAccountGroup($account_no, $account_type) {
//        $checklogin = $this->CheckLogin($metatrader_type);
        $token_responce = $this->getToken();
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $params = array("Login" => $account_no, "Group" => $account_type);
            $headers = $this->headersParams($token);
            $url = $request_url . '/Change_Group/';
            $response = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($code == 200 && $messege == 'Ok') {
                return $response;
            } else {
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return change Account Password
     */

    public function changePassword($account_no, $password, $IsInvestor) {

        $token_responce = $this->getToken();
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $params = array("Login" => $account_no, "Password" => $password, "IsInvestor" => $IsInvestor);
            $headers = $this->headersParams($token);
            $url = $request_url . '/Change_Password/';
            $response = $this->fireRequest($url, $headers, json_encode($params), 'POST');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($code == 200 && $messege == 'Ok') {
                return $response;
            } else {
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return change Account Disable
     */

    public function accountDisable($account_no) {

        $token_responce = $this->getToken();
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $headers = $this->headersParams($token);
            $url = $request_url . '/Account_Disable/?Login=' . $account_no;
            $response = $this->fireRequest($url, $headers, array(), 'GET');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($code == 200 && $messege == 'Ok') {
                //  return true;
                return $response;
                //return $response;
            } else {
                //return array(false, $response->Message);
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return change Demo Account Disable
     */

    public function demoaccountDisable($account_no) {

        $token_responce = $this->getToken('DemoAccount');
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $headers = $this->headersParams($token);
            $url = $request_url . '/Account_Disable/?Login=' . $account_no;
            $response = $this->fireRequest($url, $headers, array(), 'GET');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($code == 200 && $messege == 'Ok') {
                //  return true;
                return $response;
                //return $response;
            } else {
                //return array(false, $response->Message);
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return change  Account Enable
     */

    public function accountEnable($account_no) {

        $token_responce = $this->getToken();
        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
            $request_url = $token_responce->Url;
            $token = $token_responce->Data;
            $headers = $this->headersParams($token);
            $url = $request_url . '/Account_Enable/?Login=' . $account_no;
            $response = $this->fireRequest($url, $headers, array(), 'GET');
            $response = json_decode($response);
            $code = $response->Code;
            $messege = $response->Message;
            if ($code == 200 && $messege == 'Ok') {
                //  return true;
                return $response;
                //return $response;
            } else {
                //return array(false, $response->Message);
                return $response;
            }
        } else {
            return $token_responce;
        }
    }

    //Live Account Dashboard functions
    public function getOutstandingForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        if ($account_no == '') {
            return "SELECT `LOGIN` AS `login`, `BALANCE` AS `balance`, `EQUITY` AS `equity`, `MARGIN` AS `margin`, `MARGIN_FREE` AS `margin_free` "
                    . "FROM `" . $dbName . "`. `mt4_users`";
        } else {
            return "SELECT `LOGIN` AS `login`, `BALANCE` AS `balance`, `EQUITY` AS `equity`, `MARGIN` AS `margin`, `MARGIN_FREE` AS `margin_free` "
                    . "FROM `" . $dbName . "`. `mt4_users` WHERE `LOGIN` = '$account_no'";
        }
    }

    public function getProfitLossForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        // return "SELECT `LOGIN` AS `login`,SUM(IF(`CMD`=0 OR `CMD` = 1, `PROFIT` + `COMMISSION`, 0)) `profit_loss`,"
        //         . "SUM(IF(`CMD` = 6 AND `PROFIT` > 0, `PROFIT`, 0)) `deposit`,"
        //         . "ABS(SUM(IF(`CMD` = 6 AND `PROFIT` < 0,`PROFIT`, 0))) `withdraw` "
        //         . "FROM `mt4_trades` WHERE `LOGIN` = '$account_no' AND `CLOSE_TIME` <> '1970-01-01 00:00:00'";

        return "SELECT `LOGIN` AS `login`,SUM(IF(`CMD`=0 OR `CMD` = 1, `PROFIT`, 0)) `profit_loss`, " 
                . "SUM(IF(`CMD`= 0 OR `CMD` = 1, `COMMISSION`, 0)) `commission`, "
                . "SUM(IF(`CMD`= 0 OR `CMD` = 1, `SWAPS`, 0)) `swap`, "
                . "SUM(IF(`CMD` = 6 AND `PROFIT` > 0, `PROFIT`, 0)) `deposit`,"
                . "ABS(SUM(IF(`CMD` = 6 AND `PROFIT` < 0,`PROFIT`, 0))) `withdraw` "
                . "FROM `" . $dbName . "`. `mt4_trades` WHERE `LOGIN` = '$account_no' AND `CLOSE_TIME` <> '1970-01-01 00:00:00'";
    }

    public function getOpenTradesForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `LOGIN` AS `login`,SUM(IF(`CMD`=0 ,1,0)) `buy_count`, SUM(IF(`CMD`=1,1,0)) `sell_count`, "
                . "SUM(IF(`CMD`=0 ,`VOLUME`/100,0)) `buy_volume`, SUM(IF(`CMD` = 1, `VOLUME`/100,0)) `sell_volume` "
                . "FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` = '1970-01-01 00:00:00' AND `LOGIN` = '$account_no'";
    }

    public function getCloseTradesForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `LOGIN` AS `login`, SUM(IF(`PROFIT` + `COMMISSION` >= 0,1,0)) `profit_count`, "
                . "SUM(IF(`PROFIT` + `COMMISSION` < 0,1,0)) `loss_count` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` IN(0,1) AND `LOGIN` = '$account_no'";
    }

    public function getClosedTradesListForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT  `LOGIN` AS `login`, `OPEN_TIME` AS `open_time`, `CLOSE_TIME` AS `close_time`, `SYMBOL` AS `symbol`, "
                . "`VOLUME`/100 AS `volume`, `OPEN_PRICE` AS open_price,`CLOSE_PRICE` AS close_price, "
                . "`PROFIT` AS `profit`, '0' AS `is_open` FROM `" . $dbName . "`. `mt4_trades` WHERE  `LOGIN` = '$account_no' "
                . "AND `CMD` IN (1,0) AND `CLOSE_TIME` <> '1970-01-01 00:00:00' ORDER BY `TICKET` DESC LIMIT 0,5";
    }

    public function getOpenTradesListForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT  `LOGIN` AS `login`, `OPEN_TIME` AS `open_time`, `CLOSE_TIME` AS `close_time`, `SYMBOL` AS `symbol`, "
                . "`VOLUME`/100 AS `volume`, `OPEN_PRICE` AS open_price,`CLOSE_PRICE` AS close_price, `PROFIT` AS `profit`, '1' AS `is_open`"
                . "FROM `" . $dbName . "`. `mt4_trades` WHERE  `LOGIN` = '$account_no' AND `CMD` IN (1,0) AND `CLOSE_TIME` = '1970-01-01 00:00:00'"
                . "  ORDER BY `TICKET` DESC LIMIT 0,5";
    }

    public function getSymbolPerformanceForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `SYMBOL` AS `symbol`,  COUNT(`Symbol`) AS `symbol_count`, SUM(`Volume`/100) AS `sum_volume`, `CLOSE_TIME` AS `close_time` FROM  `" . $dbName . "`. `mt4_trades` WHERE `LOGIN` = '$account_no' AND
        `CLOSE_TIME` != '1970-01-01 00:00:00' AND `CMD` IN (0,1) GROUP BY `symbol` ORDER BY `sum_volume` DESC LIMIT 0,5";
        // return "(SELECT `SYMBOL` AS `symbol`,  `VOLUME`/100 AS `volume`,  `PROFIT` AS `min_max_profit`, `CLOSE_TIME` AS `close_time` "
        //     . "FROM mt4_trades where `PROFIT` = (select max(`PROFIT`) from mt4_trades WHERE `LOGIN` = '$account_no' "
        //     . "AND `CLOSE_TIME` != '1970-01-01 00:00:00' AND `CMD` IN (0,1) AND `PROFIT` > 0) AND `LOGIN` = '$account_no' "
        //     . "AND `CLOSE_TIME` != '1970-01-01 00:00:00' AND `CMD` IN (0,1)  ORDER BY `CLOSE_TIME` DESC limit 1) "
        //     . "UNION ALL (SELECT `SYMBOL` AS `symbol`,  `VOLUME`/100 AS `volume`,  `PROFIT` AS `min_max_profit`, "
        //     . "`CLOSE_TIME` AS `close_time` FROM mt4_trades where `PROFIT`=(select min(`PROFIT`) from mt4_trades "
        //     . "WHERE `LOGIN` = '$account_no' AND `CLOSE_TIME` != '1970-01-01 00:00:00' AND `CMD` IN (0,1) AND "
        //     . "`PROFIT` <  0)  AND `LOGIN` = '$account_no' AND `CLOSE_TIME` != '1970-01-01 00:00:00' AND `CMD` IN (0,1) "
        //     . "ORDER BY `CLOSE_TIME` DESC limit 1)";
    }

    public function getTradesStreakForLiveAccountDashboard($account_no = '', $trades_streak_name = '') {
        $dbName = $this->getDbName();
        switch ($trades_streak_name) {
            case 'most_and_least_effective_symbol':
                return "SELECT `a`.`SYMBOL`, COUNT(`a`.`SYMBOL`) AS `total_trade`, (SELECT COUNT(`SYMBOL`) FROM `" . $dbName . "`. `mt4_trades` "
                        . "WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' AND `CMD` IN(0,1) AND `LOGIN` = '$account_no' "
                        . "AND `PROFIT` > 0 AND `SYMBOL` = `a`.`SYMBOL`) AS `winning_trade`, ((SELECT COUNT(`SYMBOL`) "
                        . "FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' AND `CMD` IN(0,1) AND "
                        . "`LOGIN` = '$account_no' AND `PROFIT` > 0 AND `SYMBOL` = `a`.`SYMBOL`) / COUNT(`a`.`SYMBOL`) * 100) "
                        . "AS `winning_ratio` FROM `" . $dbName . "`. `mt4_trades`  AS `a` WHERE `a`.`CLOSE_TIME` <> '1970-01-01 00:00:00' "
                        . "AND `a`.`CMD` IN(0,1) AND `a`.`LOGIN` = '$account_no' GROUP BY `a`.`SYMBOL`";
                break;
            case 'longest_winning_and_losing_streak':
                return "SELECT `SYMBOL` as `symbol`, `TICKET` as `ticket`, `PROFIT` as `profit`, `CLOSE_TIME` as `close_time` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` <> "
                        . "'1970-01-01 00:00:00' AND `CMD` IN(0,1) AND `LOGIN` = '$account_no' ORDER BY `CLOSE_TIME` DESC";
                break;
            default:
                return false;
                break;
        }
    }

    public function getTop5WinningTradesForLiveAccountDashboard($account_no) {
        $dbName = $this->getDbName();
        return "SELECT `SYMBOL` AS `symbol`, `CLOSE_TIME` AS `close_time`, `VOLUME`/100 AS `volume`, `CLOSE_PRICE` AS close_price, `PROFIT` AS `profit` FROM `" . $dbName . "`. `mt4_trades` WHERE  `LOGIN` = " . $account_no . " AND `CLOSE_TIME` <> '1970-01-01 00:00:00' AND `CMD` IN(0,1) HAVING `PROFIT` > 0 ORDER BY `PROFIT` DESC LIMIT 0,5";
    }

    public function getTop5LossingTradesForLiveAccountDashboard($account_no) {
        $dbName = $this->getDbName();
        return "SELECT `SYMBOL` AS `symbol`, `CLOSE_TIME` AS `close_time`, `VOLUME`/100 AS `volume`, `CLOSE_PRICE` AS close_price, `PROFIT` AS `profit` FROM `" . $dbName . "`. `mt4_trades` WHERE  `LOGIN` = " . $account_no . " AND `CLOSE_TIME` <> '1970-01-01 00:00:00' AND `CMD` IN(0,1) HAVING `PROFIT` < 0 ORDER BY `PROFIT` ASC LIMIT 0,5";
    }

    public function getProfitForLiveAccountDashboard($account_no, $from_date, $to_date, $day_month) {
        $dbName = $this->getDbName();
        $day_month_query_col = "DATE_FORMAT(`CLOSE_TIME`, '%Y-%m') AS `Month`";
        $day_month_query_group_by = "`Month`";
        if ($day_month == 'daily') {
            $day_month_query_col = "DATE_FORMAT(`CLOSE_TIME`, '%d') AS `Day`";
            $day_month_query_group_by = "`Day`";
        }
        return "SELECT " . $day_month_query_col . ", SUM(`PROFIT`) AS `profit` FROM `" . $dbName . "`. `mt4_trades` "
                . "WHERE `cmd` IN(0,1) AND `profit` > 0 AND `CLOSE_TIME` > '1970-01-01' AND `LOGIN` = " . $account_no . " AND `CLOSE_TIME` >= '" . $from_date . "' AND `CLOSE_TIME` <= '" . $to_date . "' GROUP BY " . $day_month_query_group_by;
    }

    public function getLossForLiveAccountDashboard($account_no, $from_date, $to_date, $day_month) {
        $dbName = $this->getDbName();
        $day_month_query_col = "DATE_FORMAT(`CLOSE_TIME`, '%Y-%m') AS `Month`";
        $day_month_query_group_by = "`Month`";
        if ($day_month == 'daily') {
            $day_month_query_col = "DATE_FORMAT(`CLOSE_TIME`, '%d') AS `Day`";
            $day_month_query_group_by = "`Day`";
        }
        return "SELECT " . $day_month_query_col . ", SUM(`PROFIT`) AS `loss` FROM `" . $dbName . "`. `mt4_trades` WHERE `CMD` IN(0,1) AND `PROFIT` < 0 AND `LOGIN` = " . $account_no . " AND `CLOSE_TIME` >= '" . $from_date . "' AND `CLOSE_TIME` <= '" . $to_date . "' GROUP BY " . $day_month_query_group_by;
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
            $sql = "SELECT  '$serverName' AS `server_type`, `LOGIN` AS `login`, `TICKET` AS `ticket`, `SYMBOL` AS `symbol`, `VOLUME`/100 AS `volume`, "
                    . " `CMD` AS `cmd`, `OPEN_TIME` AS `open_time`, `OPEN_PRICE` AS open_price,"
                    . " `TP` as `tp`, `SL` as `sl`, `COMMISSION` as `commission`, `SWAPS` as `swaps`, "
                    . " `PROFIT` AS `profit` FROM `" . $dbName . "`. `mt4_trades` WHERE `CMD` IN (1,0) AND `CLOSE_TIME` = '1970-01-01 00:00:00' " . $accountWhere;
        }
        if ($trade_type == 'close') {
            $sql = "SELECT  '$serverName' AS `server_type`, `LOGIN` AS `login`, `TICKET` AS `ticket`, `SYMBOL` AS `symbol`, `VOLUME`/100 AS `volume`, "
                    . " `CMD` AS `cmd`, `OPEN_TIME` AS `open_time`, `OPEN_PRICE` AS `open_price`, "
                    . " `CLOSE_TIME` AS `close_time`, `CLOSE_PRICE` AS `close_price`, `TP` as `tp`, "
                    . "`SL` as `sl`, `COMMISSION` as `commission`, `SWAPS` as `swaps`, "
                    . " `PROFIT` AS `profit` FROM `" . $dbName . "`. `mt4_trades` WHERE `CMD` IN (1,0) AND `CLOSE_TIME` <> '1970-01-01 00:00:00' " . $accountWhere;
        }
        return $sql;
    }

    public function getCountQueryForSubIbTransactionReport($account_no, $from_date, $to_date) {
        $dbName = $this->getDbName();
        return "SELECT count(1) AS `count` FROM `" . $dbName . "`. `mt4_trades` WHERE `CMD` = 6
        AND `CLOSE_TIME` <> '1970-01-01 00:00:00'
        AND `CLOSE_TIME` >= '" . $from_date . "'
        AND `CLOSE_TIME` <= '" . $to_date . "'
        AND `LOGIN` = " . $account_no;
    }

    public function getTransactionsForReport($account_no, $from_date, $to_date) {
        $dbName = $this->getDbName();
        return "SELECT  `LOGIN` AS `login`, `TICKET` AS `ticket`, `CLOSE_TIME` AS `close_time`, "
                . "`PROFIT` AS `profit`, `COMMENT` AS `comment` FROM `" . $dbName . "`. `mt4_trades`
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
        return "SELECT `LOGIN` AS `login` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` = '1970-01-01 00:00:00' "
                . "AND `LOGIN` = '$account_no'" . $filter_query;
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
        return "SELECT `LOGIN` AS `login` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` IN(0,1) AND `LOGIN` = '$account_no'" . $filter_query;
    }

    //For Main Dashboard
    public function getTotalVolumeAndProfitLossForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`volume`/100) AS `total_volume`, SUM(`profit`) AS `total_profit_loss` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` IN(0,1) AND `LOGIN`";
    }

    public function getTotalVolumeAndProfitLossForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT (`VOLUME`/100) AS `volume`, `PROFIT` AS `profit`, `LOGIN` AS `login` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` IN(0,1)";
    }

    public function getTotalDepositForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`profit`) AS `total_deposit` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` = 6 AND `profit` > 0 AND `LOGIN`";
    }

    public function getTotalDepositForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`profit`) AS `total_deposit` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` = 6 AND `profit` > 0 AND `LOGIN`";
    }

    public function getTotalWithdrawalForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`profit`) AS `total_withdrawal` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` = 6 AND `profit` < 0 AND `LOGIN`";
    }

    public function getTotalWithdrawalForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`profit`) AS `total_withdrawal` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` = 6 AND `profit` < 0 AND `LOGIN`";
    }

    public function getTotalVolumeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`volume`/100) AS `total_volume` FROM `" . $dbName . "`. `mt4_trades` WHERE `CMD` IN(0,1) AND `LOGIN`";
    }

    public function getTotalVolumeForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`volume`/100) AS `total_volume`, `LOGIN` AS `login` FROM `" . $dbName . "`. `mt4_trades` WHERE `CMD` IN(0,1) AND `LOGIN`";
    }

    public function getOpenVolumeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`volume`/100) AS `total_volume` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` = '1970-01-01 00:00:00' AND `CMD` IN(0,1) AND `LOGIN`";
    }

    public function getOpenVolumeForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`volume`/100) AS `open_volume`, `LOGIN` AS `login` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` = '1970-01-01 00:00:00' AND `CMD` IN(0,1) AND `LOGIN`";
    }

    public function getWinTradeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT DATE_FORMAT(`close_time`, '%Y-%m') AS Month, COUNT(*) AS `win_trade` FROM `" . $dbName . "`. `mt4_trades` WHERE `cmd` IN(0,1) AND `profit` > 0 AND `close_time` > '1970-01-01' AND `login`";
    }

    public function getLossTradeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT DATE_FORMAT(`close_time`, '%Y-%m') AS Month, COUNT(*) AS `loss_trade` FROM `" . $dbName . "`. `mt4_trades` WHERE `cmd` IN(0,1) AND `profit` < 0 AND `close_time` > '1970-01-01' AND `login`";
    }

    public function getAccountsDataForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT `BALANCE` AS `balance`, `EQUITY` AS `equity` , `MARGIN` AS `margin`, `MARGIN_FREE` AS `margin_free`, `LOGIN` AS `login` FROM `" . $dbName . "`. `mt4_users`";
    }

    public function getAccountsDataForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT `BALANCE` AS `balance`, `EQUITY` AS `equity` , `MARGIN` AS `margin`, `MARGIN_FREE` AS `margin_free`, `LOGIN` AS `login` FROM `" . $dbName . "`. `mt4_users`";
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
        $mt4_users_table_exist = $adb->pquery("SELECT 1 FROM `" . $dbName . "`. `mt4_users` LIMIT 1", array());
        if ($mt4_users_table_exist !== FALSE && !empty($account_no))
        {
            $query = 'SELECT mt4_users.`LOGIN` AS `LOGIN`, mt4_users.`BALANCE` AS `BALANCE`, mt4_users.`EQUITY` AS `EQUITY`,mt4_users.`MARGIN_FREE` AS `MARGIN_FREE` FROM `' . $dbName . '`. `mt4_users` WHERE mt4_users.LOGIN =?';

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
        return "SELECT SUM(`volume`/100) AS `total_volume` FROM `" . $dbName . "`. `mt4_trades` WHERE (CLOSE_TIME <> '1970-01-01 00:00:00' AND  CMD IN (1,0)) AND `LOGIN` = ? AND `CLOSE_TIME` BETWEEN ? AND ?";
    }
    
    public function getEquity() {
        $dbName = $this->getDbName();
        return "SELECT `EQUITY` AS `equity` FROM `" . $dbName . "`. `mt4_users` WHERE `LOGIN` = ?";
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
        return "SELECT count(`LOGIN`) AS `Login` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` = '1970-01-01 00:00:00' AND `LOGIN` = ?";
    }

    public function getTotalVolumeQuery() {
        $dbName = $this->getDbName();
        return "SELECT (`VOLUME`/100) AS `volume`, `LOGIN` AS `login` FROM `" . $dbName . "`. `mt4_trades` WHERE `CLOSE_TIME` <> '1970-01-01 00:00:00' "
                . "AND `CMD` IN(0,1) AND `CLOSE_TIME` BETWEEN ? AND ?";
    }
}
