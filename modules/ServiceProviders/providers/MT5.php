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

class ServiceProviders_MT5_Provider implements ServiceProviders_ITradingPlatform_Model {

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
        //array('name' => 'server_type', 'label' => 'Server Type', 'type' => 'picklist', 'picklistvalues' => array('MT5' => 'MT5')),
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
        return 'MT5'; //Can't Change Provider Name
    }

    public function getDbName() {
        global $dbconfig;
        $params = $this->prepareParameters();
        $dbName = $params['db_name'];
        $currentDbName = empty($dbName) ? $dbconfig['db_name'] : $dbName;
        return $currentDbName;
    }

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function providerType() {
        return array('TradingPlatform' => 1);
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
        return "SELECT  '$serverName'   AS server_type, Deal AS ticket,`Login` AS login,Symbol AS symbol,Digits AS digits,`Action` AS cmd,IF(ACTION=2 AND Profit>0,TRUE,0 ) is_deposit, Volume/10000 AS volume,PricePosition AS open_price,
Price AS close_price,`Time` AS open_time,`Time` AS close_time,`Comment` AS `comment`,Profit AS profit FROM `" . $dbName . "`. `mt5_deals`
WHERE `Deal` > $maxTicket AND  ((`Action` IN (0,1) AND Entry = 1) OR (`Action`=2 AND `Comment` LIKE '%Deposit%' AND Profit>0))";
    }

    public function getCloseTradesQueryForCommissionByCloseTime($closeTime) {
        $serverName = $this->getName();
        $dbName = $this->getDbName();
        return "SELECT  '$serverName'   AS server_type, Deal AS ticket,`Login` AS login,Symbol AS symbol,Digits AS digits,`Action` AS cmd,IF(ACTION=2 AND Profit>0,TRUE,0 ) is_deposit, Volume/10000 AS volume,PricePosition AS open_price,
Price AS close_price,`Time` AS open_time,`Time` AS close_time,`Comment` AS `comment`,Profit AS profit,`Commission` AS brokerage_commission FROM `" . $dbName . "`. `mt5_deals`
WHERE `Time` > '$closeTime' AND  ((`Action` IN (0,1) AND Entry = 1) OR (`Action`=2 AND `Comment` LIKE '%Deposit%' AND Profit>0))";
    }

    /**
     * This function is used to get MT5 trades which are missing in trade commission calculation
     * @param datetime $closeTime
     * @param date $closeDateForManual [format - "yyyy-mm-dd"] - This parameter set when we manually calculate commission
     * @return string $tradeQuery
     */
    public function getCloseTradesQueryForMissingCommission($closeTime, $closeDateForManual = '') {
        $serverName = $this->getName();
        $dbName = $this->getDbName();
        $tradeLimitQuery = " (`Time` between (CONVERT_TZ( NOW(), @@session.time_zone, '+00:00') - INTERVAL 2 DAY) AND '$closeTime') ";
        $extraWhere = " AND `Deal` NOT IN (SELECT ticket from tradescommission WHERE close_time >= (CONVERT_TZ( NOW(), @@session.time_zone, '+00:00') - INTERVAL 2 DAY)) ";
        if (!empty($closeDateForManual)) {
            $startCloseDateForManual = $closeDateForManual . ' 00:00:00';
            $endCloseDateForManual = $closeDateForManual . ' 23:59:59';
            $tradeLimitQuery = " (`Time` between '$startCloseDateForManual' AND '$endCloseDateForManual') ";
            $extraWhere = " AND `Deal` NOT IN (SELECT ticket from tradescommission WHERE DATE_FORMAT(close_time,'%Y-%m-%d') = '$closeDateForManual') ";
        }

        $tradeQuery = "SELECT  '$serverName'   AS server_type, Deal AS ticket,`Login` AS login,Symbol AS symbol,Digits AS digits,`Action` AS cmd,IF(ACTION=2 AND Profit>0,TRUE,0 ) is_deposit, Volume/10000 AS volume,PricePosition AS open_price,
Price AS close_price,`Time` AS open_time,`Time` AS close_time,`Comment` AS `comment`,Profit AS profit,`Commission` AS brokerage_commission FROM `" . $dbName . "`. `mt5_deals`
WHERE " . $tradeLimitQuery . " AND ((`Action` IN (0,1) AND Entry = 1) OR (`Action`=2 AND `Comment` LIKE '%Deposit%' AND Profit>0)) " . $extraWhere;
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
        global $metaTrader_details;
        //   $params = $this->prepareParameters();
        //$clientId = $params['client_id'];
        //$client_secret = $params['client_secret'];
        //$authorizationKey = 'Basic ' . base64_encode("$clientId:$client_secret");
        $headers = array('cache-control' => 'no-cache',
            'content-type' => 'application/json');
        if ($token != '') {
            $headers['token'] = $token;
        }
        return $headers;
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- check traders server
     */

    public function getToken($module = '') {
        global $metaTrader_details, $MT5_request_URL, $token_URL, $MT5_clientId, $MT5_client_secret;
        $params = $this->prepareParameters();
        $clientId = $MT5_clientId;
        $client_secret = $MT5_client_secret;
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
        $url = $token_URL;
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
        global $metaTrader_details, $MT5_request_URL;
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
        global $token_URL, $MT5_clientId, $MT5_client_secret;
        $url = $token_URL;
        $clientId = $MT5_clientId;
        $client_secret = $MT5_client_secret;
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

//    public function createAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency) {
    //
    //        global $metaTrader_details, $isAllowSeries, $MT5_request_URL;
    //        $params = array("City" => $city, "State" => $state, "Country" => $country, "Address" => $address, "Zipcode" => $zipcode, "PhoneNumber" => $phone_number, "Comment" => $comment, "Login" => $account_no, "Password" => $password, "InvestorPassword" => $investor_password, "PhonePassword" => $phonepassword, "Group" => $account_type, "Leverage" => $leverage, "Name" => $client_name, "Email" => $client_email);
    //        //;
    ////        $checklogin = $this->checkLogin();
    ////        $request_url = $MT5_request_URL;
    //
    //        $token_responce = $this->getToken();
    //        if ($token_responce->Code == 200 && $token_responce->Message == 'Ok') {
    //            $request_url = $token_responce->Url;
    //            $token = $token_responce->Data;
    //            $headers = $this->headersParams($token);
    //            $url = $request_url . '/Create_User/';
    //            $response = $this->fireRequest($url, $headers, json_encode($params), 'POST');
    //            $response = json_decode($response);
    //            $code = $response->Code;
    //            $messege = $response->Message;
    //            if ($isAllowSeries) {
    //                if ($code == 200 && $messege == 'Ok') {
    //                    return $response;
    //                } elseif ($code == 206 || $code == 214) {
    //                    $login = $account_no + 1;
    //                    $account_no = $login;
    //                    return $this->createAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email);
    //                } else {
    //                    return $response;
    //                }
    //            } else {
    //                return $response;
    //            }
    //        } else {
    //            return $token_responce;
    //        }
    //    }

    public function createAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency,$contactid='', $otherParam = array()) {

        //;
        //        $checklogin = $this->checkLogin();
        //        $request_url = $MT5_request_URL;

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
                $group_series_data = getLiveAccountSeriesBaseOnAccountType('MT5', str_replace("\\", ":", $account_type), $label_account_type, $currency);
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

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- create meta trader Demo account
     */

    public function createDemoAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency, $contactid = '', $otherParam = array()) {

        //;
        //        $checklogin = $this->checkLogin();
        //        $request_url = $MT5_request_URL;

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
                $group_series_data = getDemoAccountSeriesBaseOnAccountType('MT5', str_replace("\\", ":", $account_type), $label_account_type, $currency);
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
        global $metaTrader_details, $MT5_request_URL;
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

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- add deposit into meta trader account
     */

    public function depositToDemoAccount($account_no, $amount, $comment) {
        global $metaTrader_details, $MT5_request_URL;
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
        global $metaTrader_details, $MT5_request_URL;
        //$request_url = $MT5_request_URL;
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
        global $metaTrader_details;
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
        global $metaTrader_details;
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
        global $metaTrader_details;
//        $request_url = $metaTrader_details[$metatrader_type]['request_url'];
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
        global $metaTrader_details;
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
        global $metaTrader_details;
//        $request_url = $metaTrader_details[$metatrader_type]['request_url'];
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
        global $metaTrader_details;
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
        global $metaTrader_details;
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
        global $metaTrader_details;
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
        global $metaTrader_details;
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
            return "SELECT `Login` as `login`, `Balance` as `balance`, `Equity` as `equity`, `Margin` as `margin`, `MarginFree` as `margin_free` "
                    . "FROM `" . $dbName . "`. `mt5_accounts`";
        } else {
            return "SELECT `Login` as `login`, `Balance` as `balance`, `Equity` as `equity`, `Margin` as `margin`, `MarginFree` as `margin_free` "
                    . "FROM `" . $dbName . "`. `mt5_accounts` WHERE `Login` = '$account_no'";
        }
    }

    public function getProfitLossForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        // return "SELECT `Login` AS `login`,SUM(IF((`Action`=0 OR `Action`=1) AND `Entry`=1,`Profit` + `Commission`, 0)) "
        //         . "`profit_loss`,SUM(IF(`ACTION`=2 AND `Profit` > 0,`Profit`, 0)) `deposit`,ABS(SUM(IF(`ACTION`=2 "
        //         . "AND `Profit` < 0, `Profit`, 0 ))) `withdraw` FROM `mt5_deals`"
        //         . " WHERE `Login` = '$account_no'";
        return "SELECT `Login` AS `login`, SUM(IF((`Action`=0 OR `Action`=1) AND `Entry`=1,`Profit`, 0)) "
            . "`profit_loss`, SUM(IF((`Action`=0 OR `Action`=1) AND `Entry`=1,`Storage`, 0)) "
            . "`swap`, SUM(IF((`Action`=0 OR `Action`=1) AND `Entry`=1,`Commission`, 0)) "
            . "`commission`, SUM(IF(`ACTION`=2 AND `Profit` > 0,`Profit`, 0)) `deposit`,ABS(SUM(IF(`ACTION`=2 "
            . "AND `Profit` < 0, `Profit`, 0 ))) `withdraw` FROM `" . $dbName . "`. `mt5_deals`"
            . " WHERE `Login` = '$account_no'";
    }

    public function getOpenTradesForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `Login` AS login,SUM(IF(`Action`=0 ,1,0)) `buy_count`,SUM(IF(`Action`=1,1,0)) `sell_count`,"
                . "SUM(IF(`Action`=0 ,`Volume`/10000,0)) `buy_volume`,SUM(IF(`Action`=1,`Volume`/10000,0)) `sell_volume` "
                . "FROM `" . $dbName . "`. `mt5_positions` WHERE `Login`= '$account_no'";
    }

    public function getCloseTradesForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `Login` AS `login`,SUM(IF(`Profit` + `Commission` >= 0 , 1, 0)) `profit_count`, "
                . "SUM(IF(`Profit` + `Commission` < 0, 1, 0)) `loss_count` FROM `" . $dbName . "`. `mt5_deals` "
                . "WHERE `Login` = '$account_no' AND `Action` IN (1,0) AND `Entry` = 1";
    }

    public function getClosedTradesListForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `Login` as `login`, `Time` AS `close_time`, `Symbol` AS `symbol`, `Volume`/10000 AS `volume`, `PricePosition` AS open_price,"
                . "`Price` AS close_price, `Profit` AS `profit`, '0' AS `is_open`, `Action` as `cmd` FROM `" . $dbName . "`. `mt5_deals` WHERE  `Login` = '$account_no' "
                . "AND Entry = 1 AND  `Action` IN (0,1) ORDER BY `Deal` DESC LIMIT 0,5";
    }

    public function getOpenTradesListForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `Login` AS `login` ,`TimeCreate` AS `open_time`, `Symbol` AS `symbol`, `Volume`/10000 AS `volume`,"
                . "`PriceOpen` AS `open_price`, '0' AS `close_price`, `Profit` AS `profit`, '1' AS `is_open`, `Action` as `cmd` FROM `" . $dbName . "`. `mt5_positions` "
                . "WHERE `Login` = '$account_no' ORDER BY `Position_ID` DESC LIMIT 0,5";
    }

    public function getSymbolPerformanceForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `Symbol` AS `symbol`,  COUNT(`Symbol`) AS `symbol_count`, SUM(`Volume`/10000) AS `sum_volume`, `Time` AS `close_time` FROM  `" . $dbName . "`. `mt5_deals` WHERE login = '$account_no' AND
        `Entry` = 1 AND `Action` IN (0,1) GROUP BY `symbol` ORDER BY `sum_volume` DESC LIMIT 0,5";
        // return "(SELECT `Symbol` AS `symbol`,  `Volume`/10000 AS `volume`,  `Profit` AS `min_max_profit`, `Time` AS `close_time` "
        //     . "FROM mt5_deals where `Profit` = (select max(`Profit`) from mt5_deals WHERE `LOGIN` = '$account_no' AND "
        //     . "`Entry` = 1 AND `Action` IN (0,1) AND `Profit` > 0) AND `LOGIN` = '$account_no' AND "
        //     . "`Entry` = 1 AND `Action` IN (0,1)  ORDER BY `Time` DESC limit 1) "
        //     . "UNION ALL (SELECT `Symbol` AS `symbol`,  `VOLUME`/100 AS `volume`,  `Profit` AS `min_max_profit`, "
        //     . "`Time` AS `close_time` FROM mt5_deals where `Profit`=(select min(`Profit`) from mt5_deals "
        //     . "WHERE `LOGIN` = '$account_no' AND `Entry` = 1 AND `Action` IN (0,1) AND `Profit` <  0)  "
        //     . "AND `LOGIN` = '$account_no' AND `Entry` = 1 AND `Action` IN (0,1) "
        //     . "ORDER BY `Time` DESC limit 1)";
    }

    public function getTradesStreakForLiveAccountDashboard($account_no = '', $trades_streak_name = '') {
        $dbName = $this->getDbName();
        switch ($trades_streak_name) {
            case 'most_and_least_effective_symbol':
                return "SELECT `a`.`Symbol`, COUNT(`a`.`Symbol`) AS `total_trade`, (SELECT COUNT(`Symbol`) FROM `" . $dbName . "`. `mt5_deals` "
                        . "WHERE `Entry` = 1 AND `Action` IN(0,1) AND `Login` = '$account_no' AND `Profit` > 0  "
                        . "AND `Symbol` = `a`.`Symbol`) AS `winning_trade`, ((SELECT COUNT(`Symbol`) FROM `" . $dbName . "`. `mt5_deals` "
                        . "WHERE `Entry` = 1 AND `Action` IN(0,1) AND `LOGIN` = '$account_no' AND `PROFIT` > 0  "
                        . "AND `Symbol` = `a`.`Symbol`) / COUNT(`a`.`Symbol`) * 100) AS `winning_ratio` FROM `" . $dbName . "`. `mt5_deals`  "
                        . "AS `a` WHERE `a`.`Entry` = 1 AND `a`.`Action` IN(0,1) AND `a`.`Login` = '$account_no' "
                        . "GROUP BY `a`.`Symbol`";
                break;
            case 'longest_winning_and_losing_streak':
                return "SELECT `Symbol` as `symbol`, `deal` as `ticket`, `Profit` as `profit`, `Time` as `close_time` "
                        . "FROM `" . $dbName . "`. `mt5_deals` WHERE `Entry` = 1 AND `Action` IN(0,1) AND `Login` = '$account_no' ORDER BY `Time` DESC";
                break;
            default:
                return '';
                break;
        }
    }

    public function getTop5WinningTradesForLiveAccountDashboard($account_no) {
        $dbName = $this->getDbName();
        return "SELECT `Symbol` AS `symbol`, `Time` AS `close_time`, `Volume`/10000 AS `volume`, `Price` AS close_price, `Profit` AS `profit` FROM `" . $dbName . "`. `mt5_deals` WHERE  `Login` = " . $account_no . " AND Entry = 1 AND  `Action` IN (0,1) HAVING `Profit` > 0 ORDER BY `Profit` DESC LIMIT 0,5";
    }

    public function getTop5LossingTradesForLiveAccountDashboard($account_no) {
        $dbName = $this->getDbName();
        return "SELECT `Symbol` AS `symbol`, `Time` AS `close_time`, `Volume`/10000 AS `volume`, `Price` AS close_price, `Profit` AS `profit` FROM `" . $dbName . "`. `mt5_deals` WHERE  `Login` = " . $account_no . " AND Entry = 1 AND  `Action` IN (0,1) HAVING `Profit` < 0 ORDER BY `Profit` ASC LIMIT 0,5";
    }

    public function getProfitForLiveAccountDashboard($account_no, $from_date, $to_date, $day_month) {
        $dbName = $this->getDbName();
        $day_month_query_col = "DATE_FORMAT(`Time`, '%Y-%m') AS `Month`";
        $day_month_query_group_by = "`Month`";
        if ($day_month == 'daily') {
            $day_month_query_col = "DATE_FORMAT(`Time`, '%d') AS `Day`";
            $day_month_query_group_by = "`Day`";
        }
        return "SELECT " . $day_month_query_col . ", SUM(`Profit`) AS `profit` FROM `" . $dbName . "`. `mt5_deals` WHERE `Entry` = 1 AND `Action` IN(0,1) AND `Profit` > 0 AND `Login` = " . $account_no . " AND `Time` >= '" . $from_date . "' AND `Time` <= '" . $to_date . "' GROUP BY " . $day_month_query_group_by;
    }

    public function getLossForLiveAccountDashboard($account_no, $from_date, $to_date, $day_month) {
        $dbName = $this->getDbName();
        $day_month_query_col = "DATE_FORMAT(`Time`, '%Y-%m') AS `Month`";
        $day_month_query_group_by = "`Month`";
        if ($day_month == 'daily') {
            $day_month_query_col = "DATE_FORMAT(`Time`, '%d') AS `Day`";
            $day_month_query_group_by = "`Day`";
        }
        return "SELECT " . $day_month_query_col . ", SUM(`Profit`) AS `loss` FROM `" . $dbName . "`. `mt5_deals` WHERE `Entry` = 1 AND `Action` IN(0,1) AND `Profit` < 0 AND `Login` = " . $account_no . " AND `Time` >= '" . $from_date . "' AND `Time` <= '" . $to_date . "' GROUP BY " . $day_month_query_group_by;
    }

    //END Live Account Dashboard functions
    //Transaction and Trade Report
    public function getTradesForReport($trade_type, $account_no) {
        $dbName = $this->getDbName();
        $accountWhere = "";
        $serverName = $this->getName();
        $sql = '';
        if ($trade_type == 'open') {
            if (!empty($account_no)) {
                $accountWhere = " AND `Login` = " . $account_no;
            }

            $sql = "SELECT '$serverName' AS `server_type`, `Login` AS `login`, `Position` AS `ticket`, `Symbol` AS `symbol`, "
                    . "`Volume`/10000 AS `volume`, `Action` as `cmd`,"
                    . "`TimeCreate` AS `open_time`,  "
                    . "`PriceOpen` AS `open_price`, `PriceTP` AS `tp`,"
                    . "`PriceSL` AS `sl`, '0' AS `commission`, `Storage` AS `swaps`, `Profit` AS `profit` "
                    . "FROM `" . $dbName . "`. `mt5_positions` WHERE 1 " . $accountWhere;
        }
        if ($trade_type == 'close') {
            if (!empty($account_no)) {
                $accountWhere = " AND `b`.`Login` = " . $account_no;
            }

            $sql = "SELECT '$serverName' AS `server_type`, `b`.`Login` AS `login`, `b`.`Order` AS `ticket`, `b`.`Symbol` AS `symbol`,
            `b`.`Volume`/10000 AS `volume`, `b`.`Action` AS `cmd`, `j`.`Time` AS `open_time`,
            `b`.`PricePosition` AS `open_price`, `b`.`Time` AS `close_time`, `b`.`Price` AS `close_price`,
            `b`.`PriceTP` AS `tp`, `b`.`PriceSL` AS `sl`, `b`.`Commission` AS `commission`,
            `b`.`Storage` AS `swaps`, `b`.`Profit` AS `profit` FROM `" . $dbName . "`. `mt5_deals` as `b`
            LEFT JOIN `" . $dbName . "`. `mt5_deals` as `j` ON `j`.`Order` = `b`.`PositionID`
            WHERE   `b`.`Entry` = 1 AND  `b`.`Action` IN (0,1)" . $accountWhere;
        }
        return $sql;
    }

    public function getCountQueryForSubIbTransactionReport($account_no, $from_date, $to_date) {
        $dbName = $this->getDbName();
        return "SELECT count(1) AS `count` FROM `" . $dbName . "`. `mt5_deals` WHERE `Entry` = 0 AND `Action` = 2
        AND `Time` >= '" . $from_date . "'
        AND `Time` <= '" . $to_date . "'
        AND `Login` = " . $account_no;
    }

    // public function getTransactionsForReport($account_no)
    // {
    //     $whereAccount_no = "";
    //     if (!empty($account_no)) {
    //         $whereAccount_no = " AND `Login` = $account_no ";
    //     }
    //     return "SELECT `Login` AS `login`, `deal` AS `ticket`, `Time` AS `close_time`,
    //     `Profit` AS `profit`, `Comment` as `comment` FROM `mt5_deals`
    //     WHERE `Entry` = 0 AND `Action` = 2 " . $whereAccount_no;
    // }

    public function getTransactionsForReport($account_no, $from_date, $to_date) {
        $dbName = $this->getDbName();
        return "SELECT `Login` AS `login`, `deal` AS `ticket`, `Time` AS `close_time`,
        `Profit` AS `profit`, `Comment` as `comment` FROM `" . $dbName . "`. `mt5_deals`
        WHERE `Entry` = 0 AND `Action` = 2 AND `Time` >= '" . $from_date . "'
        AND `Time` <= '" . $to_date . "'
        AND `Login` = " . $account_no;
    }

    //End Trade Report

    public function getOpenTradesForIBDashboard($account_no, $filter = '') {
        $dbName = $this->getDbName();
        $filter_query = '';
        if ($filter == 'Current Month') {
            $filter_query = " AND MONTH(`TimeCreate`) = MONTH(CURRENT_DATE())";
        }
        if ($filter == 'Last Month') {
            $filter_query = " AND MONTH(`TimeCreate`) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
        }
        return "SELECT `Login` AS login FROM `" . $dbName . "`. `mt5_positions` WHERE `Login`= '$account_no'" . $filter_query;
    }

    public function getCloseTradesForIBDashboard($account_no, $filter = '') {
        $dbName = $this->getDbName();
        $filter_query = '';
        if ($filter == 'Current Month') {
            $filter_query = " AND MONTH(`Time`) = MONTH(CURRENT_DATE())";
        }
        if ($filter == 'Last Month') {
            $filter_query = " AND MONTH(`Time`) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
        }
        return "SELECT `Login` AS `login` FROM `" . $dbName . "`. `mt5_deals` "
                . "WHERE `Login` = '$account_no' AND `Action` IN (1,0) AND `Entry` = 1" . $filter_query;
    }

    //For Main Dashboard
    public function getTotalVolumeAndProfitLossForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`Volume`/10000) AS `total_volume`, SUM(`Profit`) AS `total_profit_loss`  FROM `" . $dbName . "`. `mt5_deals` WHERE  `Action` IN (1,0) AND `Entry` = 1 AND `Login`";
    }

    public function getTotalVolumeAndProfitLossForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT (`Volume`/10000) AS `volume`, (`Profit`) AS `profit`, `Login` AS `login`  FROM `" . $dbName . "`. `mt5_deals` WHERE  `Action` IN (1,0) AND `Entry` = 1";
    }

    public function getTotalDepositForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`Profit`) AS `total_deposit`  FROM `" . $dbName . "`. `mt5_deals` WHERE  `Action` = 2 AND `Profit` > 0 AND `Entry` = 0 AND `Login`";
    }

    public function getTotalDepositForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`Profit`) AS `total_deposit`  FROM `" . $dbName . "`. `mt5_deals` WHERE  `Action` = 2 AND `Profit` > 0 AND `Entry` = 0 AND `Login`";
    }

    public function getTotalWithdrawalForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`Profit`) AS `total_withdrawal`  FROM `" . $dbName . "`. `mt5_deals` WHERE  `Action` = 2 AND `Profit` < 0 AND `Entry` = 0 AND `Login`";
    }

    public function getTotalWithdrawalForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`Profit`) AS `total_withdrawal`  FROM `" . $dbName . "`. `mt5_deals` WHERE  `Action` = 2 AND `Profit` < 0 AND `Entry` = 0 AND `Login`";
    }

    public function getTotalVolumeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`Volume`/10000) AS `total_volume` FROM `" . $dbName . "`. `mt5_deals` WHERE `Entry` = 0 AND `Login`";
    }

    public function getTotalVolumeForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`Volume`/10000) AS `total_volume`, `Login` AS `login` FROM `" . $dbName . "`. `mt5_deals` WHERE `Entry` = 0 AND `Login`";
    }

    public function getOpenVolumeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`Volume`/10000) AS `open_volume` FROM `" . $dbName . "`. `mt5_positions` WHERE `Login`";
    }

    public function getOpenVolumeForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`Volume`/10000) AS `open_volume`, `Login` AS `login` FROM `" . $dbName . "`. `mt5_positions` WHERE `Login`";
    }

    public function getWinTradeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT DATE_FORMAT(`Time`, '%Y-%m') AS Month, COUNT(*) AS `win_trade` FROM `" . $dbName . "`. `mt5_deals` WHERE `Entry` = 1 AND `Action` IN(0,1) AND `Profit` > 0 AND `Login`";
    }

    public function getLossTradeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT DATE_FORMAT(`Time`, '%Y-%m') AS Month, COUNT(*) AS `loss_trade` FROM `" . $dbName . "`. `mt5_deals` WHERE `Entry` = 1 AND `Action` IN(0,1) AND `Profit` < 0 AND `Login`";
    }

    public function getAccountsDataForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT `Balance` AS `balance`, `Equity` AS `equity`, `Margin` AS `margin`, `MarginFree` AS `margin_free`, `Login` AS `login` FROM `" . $dbName . "`. `mt5_accounts`";
    }

    public function getAccountsDataForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT `Balance` AS `balance`, `Equity` AS `equity`, `Margin` AS `margin`, `MarginFree` AS `margin_free`, `Login` AS `login` FROM `" . $dbName . "`. `mt5_accounts`";
    }

    public function getGroupByForMainDashboard() {
        return " GROUP BY DATE_FORMAT(`Time`, '%Y-%m')";
    }

    public function getDateFilterGreaterForMainDashboard() {
        return " AND `Time` >= '";
    }

    public function getDateFilterLessForMainDashboard() {
        return "' AND `Time` <= '";
    }

    public function getTradingTimeConditions($trade_type, $startDateTime, $endDateTime) {
        if ($trade_type == 'open') {
            $AND = " AND `trades`.`open_time` >= '" . $startDateTime . "' AND `trades`.`open_time` <= '" . $endDateTime . "' ";
        } else if ($trade_type == 'close') {
            $AND = " AND `trades`.`close_time`  >= '" . $startDateTime . "' AND `trades`.`close_time` <= '" . $endDateTime . "' ";
        }
        return $AND;
    }

    public function getTradingOrderByConditions($trade_type = '') {
        if ($trade_type == 'open') {
            $ORDERBY = ' ORDER BY `trades`.`ticket` '; //ticket
        } else if ($trade_type == 'close') {
            $ORDERBY = ' ORDER BY `trades`.`close_time` '; //ticket
        }
        return $ORDERBY;
    }

    public function getTranTimeConditions($startDateTime, $endDateTime) {
        $AND = " AND  `trades`.`close_time` >= '" . $startDateTime . "' AND `trades`.`close_time` <= '" . $endDateTime . "' ";
        return $AND;
    }

    public function getTranOrderByConditions($isCabinetReq = false)
    {
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

    public function getProviderSpecificData($result = array())
    {
        $returnResult['ticket_no'] = $result['ticket'];
        $returnResult['close_time'] = $result['close_time'];
        return $returnResult;
    }

    public function getProviderSpecificTradeData($result = array(), $trade_type = '') {
        $returnResult = array();
        $returnResult['login'] = $result['login'];
        $returnResult['take_profit'] = $result['pricetp'];
        $returnResult['stop_loss'] = $result['pricesl'];
        $returnResult['commission'] = number_format($result['commission'], 4);
        $returnResult['swaps'] = $result['swaps'];
        $returnResult['profit'] = $result['profit'];
        $returnResult['symbol'] = $result['symbol'];
        $returnResult['volume'] = floatval($result['volume']);
        $cmd = $result['cmd'];
        $returnResult['type'] = ($cmd) ? 'Sell' : 'Buy';

        if ($trade_type == 'open') {
            $returnResult['open_time'] = $result['open_time']; //open_time
            $returnResult['open_price'] = floatval($result['open_price']); //open_price
            $returnResult['ticket'] = $result['ticket'];
        }
        if ($trade_type == 'close') {
            $returnResult['open_time'] = $result['open_time']; //open_time
            $returnResult['open_price'] = floatval($result['open_price']); //open_price
            $returnResult['close_time'] = $result['close_time']; // close_time
            $returnResult['close_price'] = floatval($result['close_price']); //close_price
            $returnResult['ticket'] = $result['ticket'];
        }
        return $returnResult;
    }
    
    public function updateAccountParams($account_no = '', $recordId = '')
    {
        global $adb;
        $dbName = $this->getDbName();
        $mt5_accounts_table_exist = $adb->pquery("SELECT 1 FROM `" . $dbName . "`. `mt5_accounts` LIMIT 1", array());

        if ($mt5_accounts_table_exist !== FALSE && !empty($account_no))
        {
            $query = "SELECT `Login` as `login`, `Balance` as `balance`, `Equity` as `equity`, `Margin` as `margin`, `MarginFree` as `margin_free` FROM `" . $dbName . "`. `mt5_accounts` WHERE `Login` = ?";;

            $result = $adb->pquery($query, array($account_no));
            $balance = $adb->query_result($result, 0, 'balance');
            $margin_free = $adb->query_result($result, 0, 'margin_free');
            $equity = $adb->query_result($result, 0, 'equity');

            $update_query = "UPDATE vtiger_liveaccount SET current_balance=?,free_margin=?,equity=? WHERE liveaccountid=?";
            $adb->pquery($update_query, array($balance, $margin_free, $equity, $recordId));
        }
    }
    
    public function getCurrentMonthTotalVolume() {
        $dbName = $this->getDbName();
        return "SELECT SUM(`Volume`/10000) AS `total_volume` FROM `" . $dbName . "`. `mt5_deals` WHERE (`Action` IN (0,1) AND Entry = 1) AND `Login` = ? AND `Time` BETWEEN ? AND ?";
    }
    
    public function getEquity() {
        $dbName = $this->getDbName();
        return "SELECT `EQUITY` AS `equity` FROM `" . $dbName . "`. `mt5_accounts` WHERE `Login` = ?";
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
        return "SELECT count(`Login`) AS `Login` FROM `" . $dbName . "`. `mt5_positions` WHERE `Login` = ?";
    }

    public function getTotalVolumeQuery() {
        $dbName = $this->getDbName();
        return "SELECT (`Volume`/10000) AS `volume`, `Login` AS `login`  FROM `" . $dbName . "`. `mt5_deals` WHERE  `Action` IN (1,0) AND `Entry` = 1 AND `Time` BETWEEN ? AND ?";
    }
}
