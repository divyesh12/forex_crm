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
include_once 'modules/ServiceProviders/VertexHelper.php';

class ServiceProviders_Vertex_Provider implements ServiceProviders_ITradingPlatform_Model {

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
        array('name' => 'demo_meta_trader_ip', 'label' => 'DemoAccount MT IP', 'type' => 'text', 'mandatory' => true),
        array('name' => 'demo_meta_trader_user', 'label' => ' DemoAccount MT User', 'type' => 'text', 'mandatory' => true),
        array('name' => 'demo_meta_trader_password', 'label' => 'DemoAccount MT Password', 'type' => 'password', 'mandatory' => true),
        array('name' => 'demoacc_start_range', 'label' => 'DemoAccount Start Range', 'type' => 'number', 'mandatory' => true),
        array('name' => 'demoacc_end_range', 'label' => 'DemoAccount End Range', 'type' => 'number', 'mandatory' => true),
        array('name' => 'live_meta_trader_ip', 'label' => 'LiveAccount MT IP', 'type' => 'text', 'mandatory' => true),
        array('name' => 'live_meta_trader_user', 'label' => 'LiveAccount MT User', 'type' => 'text', 'mandatory' => true),
        array('name' => 'live_meta_trader_password', 'label' => 'LiveAccount MT Password', 'type' => 'password', 'mandatory' => true),
        array('name' => 'dealer_id', 'label' => 'Dealer Id', 'type' => 'number', 'mandatory' => true),
        array('name' => 'liveacc_start_range', 'label' => 'LiveAccount Start Range', 'type' => 'number', 'mandatory' => true),
        array('name' => 'liveacc_end_range', 'label' => 'LiveAccount End Range', 'type' => 'number', 'mandatory' => true),
        array('name' => 'investor_pass_enable', 'label' => 'Investor Password Enable', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No')),
        array('name' => 'meta_trader_ios_link', 'label' => 'IOS', 'type' => 'text'),
        array('name' => 'meta_trader_android_link', 'label' => 'Android', 'type' => 'text'),
        array('name' => 'meta_trader_windows_link', 'label' => 'Windows', 'type' => 'text'),
        array('name' => 'trade_date', 'label' => 'Trade Date', 'type' => 'date'),
        array('name' => 'db_name', 'label' => 'DB Name', 'type' => 'text'),
        array('name' => 'sequence_number', 'label' => 'Sequence Number', 'type' => 'number'),
    );
    
    public static $errorString = array('-1' => 'InvalidLoginInfoError','-5' => 'TradingIsClosedError','-6' => 'OrderAlreadyProcessed','-2' => 'NotEnoughMoneyError','-10' => 'PositionClosedError','-4' => 'CannotHedgeError','-11' => 'PositionInPendingModeError','-50' => 'MarketConditionError','-51' => 'BadConnectionError','-200' => 'InternalError','-201' => 'LoginRequiredError','-202' => 'InvalidAccountError','-203' => 'InvalidTicketError','-204' => 'InvalidOrderError','-205' => 'InvalidAmountError','-206' => 'InvalidCloseByHedgeError','-207' => 'InvalidLoginInfoError','-208' => 'SymbolNotFoundError','-209' => 'InvalidDateFormatError','-210' => 'NoDateFoundError','-211' => 'InvalidOrderTypeEror','-212' => 'CancelLimitOrderError','-213' => 'DeleteSLTPOrderError','-214' => 'UpdateSLTPError','-215' => 'NewLimitOrderError','-216' => 'UpdateLimitOrderError','-217' => 'LimitOrderNotFoundError','-218' => 'LimitOrderDeletedExecutedError','-219' => 'IsReadOnlyError','-220' => 'IsLockedError','-221' => 'SendingMailError','-222' => 'SendMailInvalidUserError','-223' => 'JustCloseSymbol','-224' => 'BuyOnlySymbol','-225' => 'DateISNotLogical','-226' => 'InvalidDepositeAmountError','-229' => 'MarketOrderNotFound','-227' => 'InvalidOrMissingParametersError','-228' => 'InvalidPrice','-230' => 'AllLotsAreManaged','-231' => 'NoAccount','-232' => 'InvalidLogin','-233' => 'InvalidOperationType','-235' => 'InvalidSerial','-248' => 'HedgeingNotAllowed','-240' => 'InvalidUsername','-241' => 'NoPrivilege','-242' => 'InvalidClientID','-243' => 'PositionIsClosed','-244' => 'PositionHasSLTP','-246' => 'AlreadyProcessed','-247' => 'DataBaseError','-1000' => 'NoData','-1200' => 'IsPaging','-236' => 'PositionIsFreezed','-237' => 'InvalidNewOldSamePassword','-239' => 'InvalidOldPassword','-238' => 'InvalidPassword','-249' => 'InvalidDeliveryPrice','-251' => 'InvalidPeriodID');

//    private $currentDBName;
//    
//    function __construct() {
//        $dbName = $this->getDbName();
//    }
        
    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName() {
        return 'Vertex'; //Can't Change Provider Name
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
        return 'TextAnyWhereEditField.tpl'; //Chnaged tpl file By Reena Hingol 09-10-2019
    }

    public function getCloseTradesQueryForCommission($maxTicket) {
        $serverName = $this->getName();
        $dbName = $this->getDbName();
        return "SELECT  '$serverName'   AS server_type, ClosedTicket AS ticket,AccountID AS login,SymbolName AS symbol,Digits AS digits,IF(ACTION=2 AND Profit>0,TRUE,0 ) is_deposit, Lots AS volume,OpenPrice AS open_price,
ClosePrice AS close_price,OpenTime AS open_time,CloseTime AS close_time,Comment AS `comment`,ProfitLoss AS profit FROM `" . $dbName . "`.vertex_trades
WHERE ClosedTicket > $maxTicket";
    }

    public function getCloseTradesQueryForCommissionByCloseTime($closeTime) {
        $serverName = $this->getName();
        $dbName = $this->getDbName();
        return "SELECT  '$serverName'   AS server_type, ClosedTicket AS ticket,AccountID AS login,SymbolName AS symbol,6 AS digits,'' AS cmd,0 as is_deposit, Lots AS volume,OpenPrice AS open_price,
ClosePrice AS close_price,OpenTime AS open_time,CloseTime AS close_time,Comments AS `comment`,ProfitLoss AS profit,`Commission` AS brokerage_commission FROM `" . $dbName . "`.vertex_trades
WHERE CloseTime > '$closeTime' 
    UNION
SELECT '$serverName' AS server_type, TicketID as ticket, AccountID AS login, '' AS symbol, 6 AS digits,'' AS cmd, IF(Amount>0,TRUE,0) AS is_deposit, 0 AS volume,
0 AS open_price,0 AS close_price,DateTime AS open_time,DateTime AS close_time,Description AS `comment`,Amount AS profit,0 AS brokerage_commission FROM `" . $dbName . "`.vertex_transactions
WHERE DateTime > '$closeTime' AND TransactionTypeEnum = '1'";
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
        $tradeLimitQuery = " (CloseTime between (CONVERT_TZ( NOW(), @@session.time_zone, '+00:00') - INTERVAL 2 DAY) AND '$closeTime') ";
        $tradeLimitQuery1 = " (DateTime between (CONVERT_TZ( NOW(), @@session.time_zone, '+00:00') - INTERVAL 2 DAY) AND '$closeTime') AND TransactionTypeEnum = '1' ";
        $extraWhere = " AND ClosedTicket NOT IN (SELECT ticket from tradescommission WHERE close_time >= (CONVERT_TZ( NOW(), @@session.time_zone, '+00:00') - INTERVAL 2 DAY)) ";
        $extraWhere1 = " AND TicketID NOT IN (SELECT ticket from tradescommission WHERE close_time >= (CONVERT_TZ( NOW(), @@session.time_zone, '+00:00') - INTERVAL 2 DAY)) ";
        if (!empty($closeDateForManual)) {
            $startCloseDateForManual = $closeDateForManual . ' 00:00:00';
            $endCloseDateForManual = $closeDateForManual . ' 23:59:59';
            $tradeLimitQuery = " (CloseTime between '$startCloseDateForManual' AND '$endCloseDateForManual') ";
            $tradeLimitQuery1 = " (DateTime between '$startCloseDateForManual' AND '$endCloseDateForManual') AND TransactionTypeEnum = '1' ";
            $extraWhere = " AND ClosedTicket NOT IN (SELECT ticket from tradescommission WHERE DATE_FORMAT(close_time,'%Y-%m-%d') = '$closeDateForManual') ";
            $extraWhere1 = " AND TicketID NOT IN (SELECT ticket from tradescommission WHERE DATE_FORMAT(close_time,'%Y-%m-%d') = '$closeDateForManual') ";
        }

        $missingQuery1 = "SELECT  '$serverName' AS server_type, ClosedTicket AS ticket,AccountID AS login,SymbolName AS symbol,6 AS digits,'' AS cmd,0 as is_deposit, Lots AS volume,OpenPrice AS open_price,
ClosePrice AS close_price,OpenTime AS open_time,CloseTime AS close_time,Comments AS comment,ProfitLoss AS profit,`Commission` AS brokerage_commission FROM `" . $dbName . "`.vertex_trades
WHERE " . $tradeLimitQuery . $extraWhere;
        
        $missingQuery2 = "SELECT  '$serverName' AS server_type, TicketID AS ticket,AccountID AS login,'' AS symbol,6 AS digits,'' AS cmd,IF(Amount>0,TRUE,0) AS is_deposit, 0 AS volume,0 AS open_price,
0 AS close_price,DateTime AS open_time,DateTime AS close_time,Description AS comment,Amount AS profit,0 AS brokerage_commission FROM `" . $dbName . "`.vertex_transactions
WHERE " . $tradeLimitQuery1 . $extraWhere1;
        
        $tradeQuery = $missingQuery1 . ' UNION ' . $missingQuery2;
        return $tradeQuery;
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return Request type
     */

    protected function fireRequest($url, $headers, $params = array(), $method = 'POST') {
        $httpCustomClient = new Vtiger_Net_Client($url);
        
        $curlHandler = curl_init();
        if (count($headers))
        {
            $httpCustomClient->setHeaders($headers);
            if(isset($headers['cookie']) && !empty($headers['cookie']))
            {
                $cookieName = $headers['cookie']['name'];
                $cookieVal = $headers['cookie']['value'];
                curl_setopt_array($curlHandler, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                  "cache-control: no-cache",
                  "Cookie: $cookieName=$cookieVal"
                ),
                ]);
                
                $response = curl_exec($curlHandler);
                curl_close($curlHandler);
                
                return $response;
            }
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

    protected function headersParams($token = '') {
        $headers = array();
        $headers = array(
            'cache-control' => 'no-cache',
        );
        if(!empty($token))
        {
            $headers['cookie']['name'] = 'ASP.NET_SessionId';
            $headers['cookie']['value'] = $token;
        }
        return $headers;
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- check traders server
     */

    public function getToken($module = '') {
        $params = $this->prepareParameters();
        $server_type = $this->getName();
        if ($module == 'DemoAccount') {
            $meta_trader_ip = $params['demo_meta_trader_ip'];
            $meta_trader_user = $params['demo_meta_trader_user'];
            $meta_trader_password = $params['demo_meta_trader_password'];
        } else {
            $meta_trader_ip = $params['live_meta_trader_ip'];
            $meta_trader_user = $params['live_meta_trader_user'];
            $meta_trader_password = $params['live_meta_trader_password'];
        }

        $headers = $this->headersParams();
        $url = $meta_trader_ip.'/BackofficeLogin?username='.$meta_trader_user.'&password='.$meta_trader_password;
        $responseInnerJson = $this->fireRequest($url, $headers, array(), 'GET');
        $responseInner = json_decode($responseInnerJson, true);
        $response = json_decode($responseInner['d'],true);
        
        if (isset($response['SessionID']) && !empty($response['SessionID'])) {
            $response = array('Code' => 200, 'Message' => 'Ok', 'sessionid' => $response['SessionID']);
        } else {
            $errorKey = isset(self::$errorString[$response]) ? self::$errorString[$response] : '';
            $error = vtranslate($errorKey,'ModuleConfigurationEditor');
            $response = array('Code' => 201, 'Message' => $error);
        }
        return (object) $response;
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- check traders server
     */

    public function checkLogin() {
    }

    public function checkMetaTraderServerConfiguration($client_type, $server_type, $meta_trader_ip, $meta_trader_user, $meta_trader_password, $module = '') {
        $headers = $this->headersParams();
        $url = $meta_trader_ip.'/BackofficeLogin?username='.$meta_trader_user.'&password='.$meta_trader_password;
        $responseInnerJson = $this->fireRequest($url, $headers, array(), 'GET');
        $responseInner = json_decode($responseInnerJson, true);
        $response = json_decode($responseInner['d'],true);
        
        if (isset($response['SessionID']) && !empty($response['SessionID'])) {
            $response = array('Code' => 200, 'Message' => 'Ok', 'sessionid' => $response['SessionID']);
        } else {
            $errorKey = isset(self::$errorString[$response]) ? self::$errorString[$response] : '';
            $error = vtranslate($errorKey,'ModuleConfigurationEditor');
            $response = array('Code' => 201, 'Message' => $error);
        }
        return (object) $response;
    }

    public function createClient($contactData, $dealerId = '', $accountParams = array())
    {
        global $log;$log->debug('Entering into createClient...');
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['demo_meta_trader_ip'];
        if(empty($dealerId))
        {
            $dealerId = $providerParams['dealer_id'];
        }
        $log->debug('$dealerId='.$dealerId);
        $params = $responseInner = array();
        $accountType = $accountParams['account_type'];
        $token_response = $this->getToken($accountType);
        if ($token_response->Code == 200 && $token_response->Message == 'Ok') {
            $token = $token_response->sessionid;
            $headers = $this->headersParams($token);
            
            $address = urlencode(trim($contactData->get('mailingstreet') . ',' . $contactData->get('mailingcity') . ',' . $contactData->get('mailingstate') . ',' . $contactData->get('mailingzip'),','));
            $countryName = urlencode($contactData->get('country_name'));
            $pob = urlencode($contactData->get('mailingpobox'));
            
            $data = array(
                'ParentID' => $dealerId,//group id
                'FirstName' => str_replace(' ', '', $contactData->get('firstname')),
                'SecondName' => '',
                'ThirdName' => '',
                'LastName' => str_replace(' ', '', $contactData->get('lastname')),
                'Username' => $accountParams['username'],
                'Password' => $accountParams['password'],
                'Phone' => '',
                'Fax' => '',
                'Mobile' => $contactData->get('mobile'),
                'TelPW' => str_replace('#', '', $contactData->get('plain_password')),
                'POB' => $pob,
                'Country' => $countryName,
                'Email' => $contactData->get('email'),
                'Address' => $address,
                'ReadOnlyLogin' => 'false',
                'ForceChangePassword' => 'false',
//                'leverage' => '1:'.$accountParams['leverage'],
            );$log->debug('$data==');$log->debug($data);
            $stringParams = '';
            foreach ($data as $key => $value)
            {
                $stringParams .= $key . '=' . $value . '&';
            }
            $stringParams = trim($stringParams, '&');
            
            $url = $meta_trader_ip . '/CreateClient?'.$stringParams;$log->debug('$url==');$log->debug($url);
            $responseInnerJson = $this->fireRequest($url, $headers, json_encode($params), 'GET');$log->debug('$responseInnerJson==');$log->debug($responseInnerJson);
            $responseInner = json_decode($responseInnerJson, true);
            $responseInner['d'] = trim($responseInner['d'],'"');
            if(isset($responseInner['d']) && !empty($responseInner['d']))
            {
                if(!array_key_exists($responseInner['d'], self::$errorString))
                {
                    $response = array('Code' => 200, 'Message' => 'Ok', 'clientid' => $responseInner['d']);
                }
                else
                {
                    $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : '';
                    $error = vtranslate($errorKey,'ServiceProviders');
                    $response = array('Code' => 201, 'Message' => $error);
                    throw new Exception($error);exit;
                }
            }
            else
            {
                $errorKey = self::$errorString['-200'];
                $error = vtranslate($errorKey,'ServiceProviders');
                throw new Exception($error);exit;
            }
            $responseObj = json_decode(json_encode($response));
            return $responseObj;
        } else {
            return $token_response;
        }
    }
            
            
    public function createAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency, $contactid='', $otherParam = array()) {
        
        global $adb,$log;$log->debug('createAccount...');$log->debug('$account_type-'.$account_type);
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['live_meta_trader_ip'];
        $serverName = $this->getName();
        
        /*Series related code*/
        $liveAccountMethod = configvar('live_account_no_method');
        if ($liveAccountMethod == 'common_series')
        {
            $isAllowSeries = true;
        }
        else if ($liveAccountMethod == 'group_series')
        {
            $isAllowGroupSeries = true;
        }
        else
        {
            $account_no = 0;
        }
        
        if ($isAllowSeries || $isAllowGroupSeries)
        {
            if ($isAllowSeries && !$isAllowGroupSeries)
            {
                $provider_params = $this->prepareParameters();
                $start_range = (int) $provider_params['liveacc_start_range'];
                $end_range = (int) $provider_params['liveacc_end_range'];
            }
            elseif (!$isAllowSeries && $isAllowGroupSeries)
            {
                $group_series_data = getLiveAccountSeriesBaseOnAccountType($serverName, str_replace("\\", ":", $account_type), $label_account_type, $currency);
                $start_range = (int) $group_series_data['start_range'];
                $end_range = (int) $group_series_data['end_range'];
            }

            if ($account_no > $end_range && isset($end_range))
            {
                $responce = (object) array('Code' => 201);
                return $responce;
            }
        }$log->debug('$account_no-'.$account_no);
        /*Series related code*/
        
        $accountParams = array(
            'leverage' => $leverage,
            'password' => $password,
            'username' => $otherParam['username'],
            'account_type' => 'LiveAccount',
        );
        
        /*get vertex liveaccount mapping*/
        if(isset($otherParam['vertex_client_id']) && !empty($otherParam['vertex_client_id']))
        {
            $vertexClientId = $otherParam['vertex_client_id'];
        }
        else if(!empty($contactid))
        {
            $accountArrRev = array_reverse(explode(":", str_replace("\\", ":", $account_type)));
            $entityData = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
            $clientResponse = $this->createClient($entityData, $accountArrRev[0], $accountParams);
            if($clientResponse->Code == 200 && $clientResponse->Message == 'Ok' && $clientResponse->clientid != 0 && $clientResponse->clientid != '')
            {
                $vertexClientId = $clientResponse->clientid;
            }
            else
            {
                $errorKey = self::$errorString['-242'];
                $error = vtranslate($errorKey,'ServiceProviders');
                throw new Exception($error);exit;
            }
        }
        /*get vertex liveaccount mapping*/
        
        $params = array();
        $token_response = $this->getToken();
        if ($token_response->Code == 200 && $token_response->Message == 'Ok') {
            $token = $token_response->sessionid;
            $headers = $this->headersParams($token);
            
            $url = $meta_trader_ip . '/CreateAccount?ParentID='.$vertexClientId.'&AccountID='.$account_no.'&AccountType=1&IsDemo=False&IsLocked=False&DontLiquidate=False&IsMargin=True';$log->debug('$url-'.$url);
            $responseInnerJson = $this->fireRequest($url, $headers, json_encode($params), 'GET');
            $responseInner = json_decode($responseInnerJson, true);$log->debug('$responseInner==');$log->debug($responseInner['d']);
            
            if(!array_key_exists($responseInner['d'], self::$errorString))
            {
                $account_no = $responseInner;
                $response = array('Code' => 200, 'Message' => 'Ok', 'Data' => array('login' => $responseInner['d']));
                $mappingSql = "INSERT INTO vertex_liveaccount_mapping (`liveaccount_number`, `vertex_clientid`) VALUES (?, ?);";
                $mappingResult = $adb->pquery($mappingSql, array($account_no, $vertexClientId));
            }
            elseif ($isAllowSeries && isset($responseInner['d']) && $responseInner['d'] == '-202')
            {
                $otherParam['vertex_client_id'] = $vertexClientId;
                $login = $account_no + 1;
                $account_no = $login;
                return $this->createAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency,$contactid,$otherParam);
            }
            else
            {
                $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : '';
                $error = vtranslate($errorKey,'ServiceProviders');
                $response = array('Code' => 201, 'Message' => $error);
                throw new Exception($error);exit;
            }
            $responseObj = json_decode(json_encode($response));
            return $responseObj;
        } else {
            return $token_response;
        }

        
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- create meta trader Demo account
     */

    public function createDemoAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency, $contactid = '', $otherParam = array()) {

        global $adb,$log;$log->debug('createDemoAccount...');$log->debug('$account_type-'.$account_type);
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['demo_meta_trader_ip'];
        $serverName = $this->getName();
        
        /*Series related code*/
        $demoAccountMethod = configvar('demo_account_no_method');
        if ($demoAccountMethod == 'common_series')
        {
            $isAllowSeries = true;
        }
        else if ($demoAccountMethod == 'group_series')
        {
            $isAllowGroupSeries = true;
        }
        else
        {
            $account_no = 0;
        }
        
        if ($isAllowSeries || $isAllowGroupSeries)
        {
            if ($isAllowSeries && !$isAllowGroupSeries)
            {
                $provider_params = $this->prepareParameters();
                $start_range = (int) $provider_params['demoacc_start_range'];
                $end_range = (int) $provider_params['demoacc_end_range'];
            }
            elseif (!$isAllowSeries && $isAllowGroupSeries)
            {
                $group_series_data = getDemoAccountSeriesBaseOnAccountType($serverName, str_replace("\\", ":", $account_type), $label_account_type, $currency);
                $start_range = (int) $group_series_data['start_range'];
                $end_range = (int) $group_series_data['end_range'];
            }

            if (isset($end_range) && $account_no > $end_range)
            {
                $response = (object) array('Code' => 201);
                return $response;
            }
        }
        /*Series related code*/
        $log->debug('$account_no-'.$account_no);
        
        $accountParams = array(
            'leverage' => $leverage,
            'password' => $password,
            'username' => $otherParam['username'],
            'account_type' => 'DemoAccount',
        );
        /*get vertex contact mapping*/
        if(isset($otherParam['vertex_client_id']) && !empty($otherParam['vertex_client_id']))
        {$log->debug('client creation skip-'.$otherParam['vertex_client_id']);
            $vertexClientId = $otherParam['vertex_client_id'];
        }
        else if(!empty($contactid))
        {$log->debug('client creation-');
            $accountArrRev = array_reverse(explode(":", str_replace("\\", ":", $account_type)));
            
            if($otherParam['source'] == 'FrontForm') {
                $contactid = explode('x', $contactid);
                $entityData = Vtiger_Record_Model::getInstanceById($contactid[1], 'Leads');                                
                $entityData->set('mailingstreet', $address);
                $entityData->set('mailingcity', $city);
                $entityData->set('mailingstate', $state);
                $entityData->set('mailingzip', $zipcode);
                $entityData->set('plain_password', $password);
                $entityData->set('mailingpobox', '');
            } else {
                $entityData = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
            }
            
            $clientResponse = $this->createClient($entityData, $accountArrRev[0], $accountParams);
            if($clientResponse->Code == 200 && $clientResponse->Message == 'Ok' && $clientResponse->clientid != 0 && $clientResponse->clientid != '')
            {
                $vertexClientId = $clientResponse->clientid;
            }
            else
            {
                $errorKey = self::$errorString['-242'];
                $error = vtranslate($errorKey,'ServiceProviders');
                throw new Exception($error);exit;
            }
        }
        
        /*get vertex contact mapping*/
        
        $params = array();
        $token_response = $this->getToken('DemoAccount');
        if ($token_response->Code == 200 && $token_response->Message == 'Ok') {
            $token = $token_response->sessionid;
            $headers = $this->headersParams($token);
            $url = $meta_trader_ip . '/CreateAccount?ParentID='.$vertexClientId.'&AccountID='.$account_no.'&AccountType=1&IsDemo=True&IsLocked=False&DontLiquidate=False&IsMargin=True';$log->debug('demo $url==');$log->debug($url);
            $responseInnerJson = $this->fireRequest($url, $headers, json_encode($params), 'GET');$log->debug('$responseInnerJson==');$log->debug($responseInnerJson);
            $responseInner = json_decode($responseInnerJson, true);$log->debug('$responseInner==');$log->debug($responseInner['d']);
            
            if(!array_key_exists($responseInner['d'], self::$errorString))
            {
                $account_no = $responseInner;
                $response = array('Code' => 200, 'Message' => 'Ok', 'Data' => array('login' => $responseInner['d']));
                $mappingSql = "INSERT INTO vertex_demoaccount_mapping (`demoaccount_number`, `vertex_clientid`) VALUES (?, ?);";
                $mappingResult = $adb->pquery($mappingSql, array($account_no, $vertexClientId));
            }
            elseif ($isAllowSeries && isset($responseInner['d']) && $responseInner['d'] == '-202')
            {
                $otherParam['vertex_client_id'] = $vertexClientId;
                $login = $account_no + 1;
                $account_no = $login;
                return $this->createDemoAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency,$contactid,$otherParam);
            }
            else
            {
                $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : '';
                $error = vtranslate($errorKey,'ServiceProviders');
                $response = array('Code' => 201, 'Message' => $error);
                throw new Exception($error);exit;
            }
            $responseObj = json_decode(json_encode($response));
            return $responseObj;
        } else {
            return $token_response;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- add deposit into meta trader account
     */

    public function deposit($account_no, $amount, $comment) {
        global $log;$log->debug('deposit to liveaccount');$log->debug($account_no.'--'.$amount.'---'.$comment);
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['live_meta_trader_ip'];
        $token_response = $this->getToken();
        if ($token_response->Code == 200 && $token_response->Message == 'Ok') {
            $params = array();
            $comment = urlencode($comment);
            $request_url = $meta_trader_ip.'/MoneyTransactions?AccountID='.$account_no.'&TransType=1&Amount='.$amount.'&Description='.$comment;
            $log->debug('$request_url=');$log->debug($request_url);
            $token = $token_response->sessionid;
            $headers = $this->headersParams($token);
            
            $responseInnerJson = $this->fireRequest($request_url, $headers, json_encode($params), 'GET');$log->debug('$responseInnerJson=');$log->debug($responseInnerJson);
            $responseInner = json_decode($responseInnerJson, true);
            $responseInner['d'] = trim($responseInner['d'],'"');
            if(isset($responseInner['d']) && !empty($responseInner['d']))
            {
                if(!array_key_exists($responseInner['d'], self::$errorString))
                {
                    $response = array('Code' => 200, 'Message' => 'Ok');
                }
                else
                {
                    $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : '';
                    $error = vtranslate($errorKey,'ServiceProviders');
                    $response = array('Code' => 201, 'Message' => $error);
                    throw new Exception($error);exit;
                }
            }
            else
            {
                $errorKey = self::$errorString['-200'];
                $error = vtranslate($errorKey,'ServiceProviders');
                throw new Exception($error);exit;
            }
            $responseObj = json_decode(json_encode($response));
            return $responseObj;
        } else {
            return $token_response;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- add deposit into meta trader account
     */

    public function depositToDemoAccount($account_no, $amount, $comment) {
        global $log;$log->debug('Entering into depositToDemoAccount');
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['demo_meta_trader_ip'];
        $token_response = $this->getToken('DemoAccount');
        if ($token_response->Code == 200 && $token_response->Message == 'Ok') {
            $params = array();
            $comment = urlencode($comment);
            $request_url = $meta_trader_ip.'/MoneyTransactions?AccountID='.$account_no.'&TransType=1&Amount='.$amount.'&Description='.$comment;
            $token = $token_response->sessionid;
            $headers = $this->headersParams($token);
            
            $responseInnerJson = $this->fireRequest($request_url, $headers, json_encode($params), 'GET');$log->debug('$responseInnerJson');$log->debug($responseInnerJson);
            $responseInner = json_decode($responseInnerJson, true);
            $responseInner['d'] = trim($responseInner['d'],'"');
            if(isset($responseInner['d']) && !empty($responseInner['d']))
            {$log->debug('iffff');
                if(!array_key_exists($responseInner['d'], self::$errorString))
                {
                    $response = array('Code' => 200, 'Message' => 'Ok');
                }
                else
                {
                    $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : '';
                    $error = vtranslate($errorKey,'ServiceProviders');
                    $response = array('Code' => 201, 'Message' => $error);
                    throw new Exception($error);exit;
                }
            }
            else
            {
                $errorKey = self::$errorString['-200'];
                $error = vtranslate($errorKey,'ServiceProviders');
                throw new Exception($error);exit;
            }
            $responseObj = json_decode(json_encode($response));$log->debug('$responseObj');$log->debug($responseObj);
            return $responseObj;
        } else {
            return $token_response;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- add withdrawal into meta trader account
     */

    public function withdrawal($account_no, $amount, $comment) {
        global $log;$log->debug('withdrawal from liveaccount');$log->debug($account_no.'--'.$amount.'---'.$comment);
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['live_meta_trader_ip'];
        $token_response = $this->getToken();
        if ($token_response->Code == 200 && $token_response->Message == 'Ok') {
            $params = array();
            $comment = urlencode($comment);
            $request_url = $meta_trader_ip.'/MoneyTransactions?AccountID='.$account_no.'&TransType=-1&Amount='.$amount.'&Description='.$comment;
            $log->debug('$request_url=');$log->debug($request_url);
            $token = $token_response->sessionid;
            $headers = $this->headersParams($token);
            
            $responseInnerJson = $this->fireRequest($request_url, $headers, json_encode($params), 'GET');$log->debug('$responseInnerJson=');$log->debug($responseInnerJson);
            $responseInner = json_decode($responseInnerJson, true);
            $responseInner['d'] = trim($responseInner['d'],'"');
            if(isset($responseInner['d']) && !empty($responseInner['d']))
            {
                if(!array_key_exists($responseInner['d'], self::$errorString))
                {
                    $response = array('Code' => 200, 'Message' => 'Ok');
                }
                else
                {
                    $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : '';
                    $error = vtranslate($errorKey,'ServiceProviders');
                    $response = array('Code' => 201, 'Message' => $error);
                    throw new Exception($error);exit;
                }
            }
            else
            {
                $errorKey = self::$errorString['-200'];
                $error = vtranslate($errorKey,'ServiceProviders');
                throw new Exception($error);exit;
            }
            $responseObj = json_decode(json_encode($response));
            return $responseObj;
        } else {
            return $token_response;
        }
    }

    /*
     * @ Add By:- Sandeep
     * @ Date:- 28-02-2020
     * @ Comment:- return account info
     */

    public function getAccountInfo($account_no) {
        global $log;$log->debug('getAccountInfo..');$log->debug($account_no);
        /*get client id from account no*/
//        $contactId = LiveAccount_Record_Model::getcontactIdFromAccounNo($account_no);$log->debug('$contactId-'.$contactId);
        /*get vertex client id from contact id*/
        $vertexClientId = VertexHelper::getVertexClientId($account_no);$log->debug('$vertexClientId-'.$vertexClientId);
        
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['live_meta_trader_ip'];
        $token_response = $this->getToken();
        if ($token_response->Code == 200 && $token_response->Message == 'Ok' && !empty($account_no)) {
            $params = $responseArrFinal = $responseArr = array();
            $comment = urlencode($comment);
            $request_url = $meta_trader_ip.'/AccountStatusReport?ClientID='.$vertexClientId.'&AccountType=1';
            $log->debug('$request_url=');$log->debug($request_url);
            $token = $token_response->sessionid;
            $headers = $this->headersParams($token);
            
            $responseInnerJson = $this->fireRequest($request_url, $headers, json_encode($params), 'GET');$log->debug('$responseInnerJson=');$log->debug($responseInnerJson);
            $responseInner = json_decode($responseInnerJson, true);
            $responseInner['d'] = trim($responseInner['d'],'"');$log->debug('$responseInnerd=');$log->debug($responseInner['d']);
            
            if(isset($responseInner['d']) && !empty($responseInner['d']))
            {
                $responseArr = json_decode($responseInner['d'],true);$log->debug('$responseArr=');$log->debug($responseArr);
                
                $accountKey = array_search($account_no, array_column($responseArr, 'AccountID'));
                $responseArrFinal = $responseArr[$accountKey];
                
                if(!array_key_exists($responseInner['d'], self::$errorString))
                {
                    $freeMargin = (double) ($responseArrFinal['Equity'] - $responseArrFinal['MarginReq']);$log->debug('$freeMargin=');$log->debug($freeMargin);
                    $response = array('Code' => 200, 'Message' => 'Ok', 'Data' => array('free_margin' => $freeMargin, 'login' => $account_no));
                }
                else
                {
                    $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : '';
                    $error = vtranslate($errorKey,'ServiceProviders');
                    $response = array('Code' => 201, 'Message' => $error);
                    throw new Exception($error);exit;
                }
            }
            else
            {
                $errorKey = self::$errorString['-200'];
                $error = vtranslate($errorKey,'ServiceProviders');
                throw new Exception($error);exit;
            }
            $responseObj = json_decode(json_encode($response));
            return $responseObj;
        } else {
            return $token_response;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return account balance
     */

    public function getBalance($account_no) {
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return change Leverage Balance
     */

    public function changeLeverage($account_no, $leverage) {
        global $log;$log->debug('changeLeverage..');$log->debug($account_no);$log->debug($leverage);
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['live_meta_trader_ip'];
        $token_response = $this->getToken();
        if ($token_response->Code == 200 && $token_response->Message == 'Ok') {
            $params = array();
            $vertexClientId = VertexHelper::getVertexClientId($account_no);            
            if (!strpos($leverage, '1:') !== false) {
                $leverage = '1:'.$leverage;
            }
            $request_url = $meta_trader_ip.'/UpdateClientCustomInfos?ClientID='.$vertexClientId.'&InformationIDsValues=Leverage,'.$leverage;$log->debug($request_url);
            $token = $token_response->sessionid;
            $headers = $this->headersParams($token);
            $responseInnerJson = $this->fireRequest($request_url, $headers, json_encode($params), 'GET');
            $responseInner = json_decode($responseInnerJson, true);
            $responseInner['d'] = trim($responseInner['d'],'"');
            if (isset($responseInner['d']) && !empty($responseInner['d'])) {
                if (!array_key_exists($responseInner['d'], self::$errorString)) {
                    if ($responseInner['d'] == $vertexClientId) {
                        $response = array('Code' => 200, 'Message' => 'Ok');
                    } else {
                        $errorKey = self::$errorString['-200'];
                        $error = vtranslate($errorKey,'ServiceProviders');
                        throw new Exception($error);exit;
                    }
                } else {
                    $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : '';
                    $error = vtranslate($errorKey,'ServiceProviders');
                    $response = array('Code' => 201, 'Message' => $error);
                    throw new Exception($error);exit;
                }
            } else {
                $errorKey = self::$errorString['-200'];
                $error = vtranslate($errorKey,'ServiceProviders');
                throw new Exception($error);exit;
            }
            $responseObj = json_decode(json_encode($response));
            return $responseObj;
        } else {
            return $token_response;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return account exist or not
     */

    public function checkAccountExist($account_no = '') {
        global $log;$log->debug('checkAccountExist..');$log->debug($account_no);
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['live_meta_trader_ip'];
        $token_response = $this->getToken();
        if ($token_response->Code == 200 && $token_response->Message == 'Ok' && !empty($account_no)) {
            $params = array();
            $comment = urlencode($comment);
            $request_url = $meta_trader_ip.'/GetAccountByID?AccountID='.$account_no;
            $log->debug('$request_url=');$log->debug($request_url);
            $token = $token_response->sessionid;
            $headers = $this->headersParams($token);
            
            $responseInnerJson = $this->fireRequest($request_url, $headers, json_encode($params), 'GET');$log->debug('$responseInnerJson=');$log->debug($responseInnerJson);
            $responseInner = json_decode($responseInnerJson, true);
            $responseInner['d'] = trim($responseInner['d'],'"');$log->debug('$responseInnerd=');$log->debug($responseInner['d']);
            if(isset($responseInner['d']) && !empty($responseInner['d']))
            {
                $responseArr = json_decode($responseInner['d'],true);$log->debug('$responseArr=');$log->debug($responseArr);
                if(isset($responseArr['Id']) && !empty($responseArr['Id']) && $responseArr['Id'] != -202)
                {
                    $response = array('Code' => 200, 'Message' => 'Ok');
                }
                else
                {
                    $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : self::$errorString['-202'];
                    $error = vtranslate($errorKey,'ServiceProviders');
                    $response = array('Code' => 201, 'Message' => $error);
                    throw new Exception($error);exit;
                }
            }
            else
            {
                $errorKey = self::$errorString['-200'];
                $error = vtranslate($errorKey,'ServiceProviders');
                throw new Exception($error);exit;
            }
            $responseObj = json_decode(json_encode($response));
            return $responseObj;
        } else {
            return $token_response;
        }
    }
    
    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return change Account Group
     */

    public function changeAccountGroup($account_no, $account_type) {
        global $log;$log->debug('changeAccountGroup liveaccount');$log->debug($account_no.'--'.$account_type);
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['live_meta_trader_ip'];
        $token_response = $this->getToken();
        if ($token_response->Code == 200 && $token_response->Message == 'Ok') {
            $params = array();
            $groupId = '';
            /*get client id from account no*/
//            $contactId = LiveAccount_Record_Model::getcontactIdFromAccounNo($account_no);$log->debug('$contactId-'.$contactId);
            /*get vertex client id from contact id*/
            $vertexClientId = VertexHelper::getVertexClientId($account_no);$log->debug('$vertexClientId-'.$vertexClientId);
            
            $groupArrRev = array_reverse(explode(":", str_replace("\\", ":", $account_type)));
            $groupId = $groupArrRev[0];
            $log->debug('$groupId='.$groupId);
            if(!empty($groupId) && !empty($vertexClientId))
            {
                $request_url = $meta_trader_ip.'/TransferClient?ClientID='.$vertexClientId.'&ParentID='.$groupId;
                $log->debug('$request_url=');$log->debug($request_url);
                $token = $token_response->sessionid;
                $headers = $this->headersParams($token);

                $responseInnerJson = $this->fireRequest($request_url, $headers, json_encode($params), 'GET');$log->debug('$responseInnerJson=');$log->debug($responseInnerJson);
                $responseInner = json_decode($responseInnerJson, true);
                $responseInner['d'] = trim($responseInner['d'],'"');
                if(isset($responseInner['d']) && !empty($responseInner['d']))
                {
                    if(!array_key_exists($responseInner['d'], self::$errorString))
                    {
                        $response = array('Code' => 200, 'Message' => 'Ok');
                    }
                    else
                    {
                        $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : '';
                        $error = vtranslate($errorKey,'ServiceProviders');
                        $response = array('Code' => 201, 'Message' => $error);
                        throw new Exception($error);exit;
                    }
                }
                else
                {
                    $errorKey = self::$errorString['-200'];
                    $error = vtranslate($errorKey,'ServiceProviders');
                    throw new Exception($error);exit;
                }
            }
            else
            {
                $errorKey = self::$errorString['-227'];
                $error = vtranslate($errorKey,'ServiceProviders');
                throw new Exception($error);exit;
            }
            $responseObj = json_decode(json_encode($response));
            return $responseObj;
        } else {
            return $token_response;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return change Account Password
     */

    public function changePassword($account_no, $password, $IsInvestor) {
        global $log,$adb;$log->debug('changePassword liveaccount');$log->debug($account_no.'--'.$password.'--'.$IsInvestor);
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['live_meta_trader_ip'];
        $token_response = $this->getToken();
        if ($token_response->Code == 200 && $token_response->Message == 'Ok') {
            
            /*get client id from account no*/
//            $contactId = LiveAccount_Record_Model::getcontactIdFromAccounNo($account_no);$log->debug('$contactId-'.$contactId);
            /*get vertex client id from contact id*/
            $vertexClientId = VertexHelper::getVertexClientId($account_no);$log->debug('$vertexClientId-'.$vertexClientId);
            
            $params = array();
            $comment = urlencode($comment);
            $request_url = $meta_trader_ip.'/UpdateClientCustomInfos?ClientID='.$vertexClientId.'&InformationIDsValues=password,'.urlencode($password);
            $log->debug('$request_url=');$log->debug($request_url);
            $token = $token_response->sessionid;
            $headers = $this->headersParams($token);
            
            $responseInnerJson = $this->fireRequest($request_url, $headers, json_encode($params), 'GET');$log->debug('$responseInnerJson=');$log->debug($responseInnerJson);
            $responseInner = json_decode($responseInnerJson, true);
            $responseInner['d'] = trim($responseInner['d'],'"');
            if(isset($responseInner['d']) && !empty($responseInner['d']))
            {
                if($responseInner['d'] == $vertexClientId)
                {
                    $response = array('Code' => 200, 'Message' => 'Ok');
                    
                    /*Update password for all account of same contact*/
                    /*$updateAccount = 'UPDATE vtiger_liveaccount SET password = ? WHERE contactid = ? AND live_metatrader_type = ?';
                    $updateAccresult = $adb->pquery($updateAccount, array($password, $contactId, 'Vertex'));*/
                }
                else
                {
                    $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : '';
                    $error = vtranslate($errorKey,'ServiceProviders');
                    $response = array('Code' => 201, 'Message' => $error);
                    throw new Exception($error);exit;
                }
            }
            else
            {
                $errorKey = self::$errorString['-200'];
                $error = vtranslate($errorKey,'ServiceProviders');
                throw new Exception($error);exit;
            }
            $responseObj = json_decode(json_encode($response));
            return $responseObj;
        } else {
            return $token_response;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return change Account Disable
     */

    public function accountDisable($account_no) {
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return change Demo Account Disable
     */

    public function demoaccountDisable($account_no) {
        global $log;$log->debug('demoaccountDisable..');$log->debug($account_no);
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['demo_meta_trader_ip'];
        $token_response = $this->getToken('DemoAccount');
        if ($token_response->Code == 200 && $token_response->Message == 'Ok' && !empty($account_no)) {
            $params = array();
            /*get client id from account no*/
//            $contactId = LiveAccount_Record_Model::getcontactIdFromAccounNo($account_no, true);$log->debug('$contactId-'.$contactId);
            /*get vertex client id from contact id*/
            $vertexClientId = VertexHelper::getVertexClientId($account_no, 'DemoAccount');$log->debug('$vertexClientId-'.$vertexClientId);
            
            $request_url = $meta_trader_ip.'/UpdateClientCustomInfos?ClientID='.$vertexClientId.'&InformationIDsValues=ReadOnlyLogin,true';
            $log->debug('$request_url=');$log->debug($request_url);
            $token = $token_response->sessionid;
            $headers = $this->headersParams($token);

            $responseInnerJson = $this->fireRequest($request_url, $headers, json_encode($params), 'GET');$log->debug('$responseInnerJson=');$log->debug($responseInnerJson);
            $responseInner = json_decode($responseInnerJson, true);
            $responseInner['d'] = trim($responseInner['d'],'"');$log->debug('$responseInnerd=');$log->debug($responseInner['d']);
            if(isset($responseInner['d']) && !empty($responseInner['d']))
            {
                $responseArr = json_decode($responseInner['d'],true);$log->debug('$responseArr=');$log->debug($responseArr);
                if(!array_key_exists($responseInner['d'], self::$errorString))
                {
                    $response = array('Code' => 200, 'Message' => 'Ok');
                }
                else
                {
                    $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : '';
                    $error = vtranslate($errorKey,'ServiceProviders');
                    $response = array('Code' => 201, 'Message' => $error);
                    throw new Exception($error);exit;
                }
            }
            else
            {
                $errorKey = self::$errorString['-200'];
                $error = vtranslate($errorKey,'ServiceProviders');
                throw new Exception($error);exit;
            }
            $responseObj = json_decode(json_encode($response));
            return $responseObj;
        } else {
            return $token_response;
        }
    }

    /*
     * @ Add By:- Divyesh
     * @ Date:- 15-11-2019
     * @ Comment:- return change  Account Enable
     */

    public function accountEnable($account_no) {
    }

    //Live Account Dashboard functions
    public function getOutstandingForLiveAccountDashboard($account_no = '') {
        if ($account_no == '') {
            return "SELECT `AccountID` as `login`, `Balance` as `balance`, `Equity` as `equity`, `UsedMargin` as `margin`, `FreeMargin` as `margin_free` "
                    . "FROM `vertex_accounts`";
        } else {
            $this->updateAccountSummary($account_no);
            return "SELECT `AccountID` as `login`, `Balance` as `balance`, `Equity` as `equity`, `UsedMargin` as `margin`, `FreeMargin` as `margin_free` "
                    . "FROM `vertex_accounts` WHERE `AccountID` = '$account_no'";
        }
    }

    public function getProfitLossForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        // return "SELECT `AccountID` AS `login`,SUM(`ProfitLoss` + `Commission`) `profit_loss`,0 as `deposit`,0 as `withdraw` FROM `" . $dbName . "`.`vertex_trades` WHERE `AccountID` = '$account_no'"
        //         . " UNION "
        //         . "SELECT `AccountID` AS `login`,`Amount` as `profit_loss`,0 as `commission`,0 as `swap`,SUM(IF(`TransactionTypeEnum`=1,`Amount`, 0)) as `deposit`,SUM(IF(`TransactionTypeEnum`=-1,`Amount`, 0)) as `withdraw` FROM `" . $dbName . "`.`vertex_transactions` WHERE `AccountID` = '$account_no'";

        return "SELECT t.`login` AS `login`,SUM(t.`profit_loss`) `profit_loss`,SUM(t.`commission`) `commission`,SUM(t.`swap`) `swap`,SUM(t.`deposit`) as `deposit`,ABS(SUM(t.`withdraw`)) as `withdraw`
                FROM (SELECT `AccountID` AS `login`,SUM(`ProfitLoss`) `profit_loss`,SUM(`Commission`) `commission`,SUM(`Interest`) `swap`,0 as `deposit`,0 as `withdraw` FROM `" . $dbName . "`.`vertex_trades` WHERE `AccountID` = '$account_no'"
                . " UNION "
                . "SELECT `AccountID` AS `login`,0 as `profit_loss`,0 as `commission`,0 as `swap`,SUM(IF(`TransactionTypeEnum`=1,`Amount`, 0)) as `deposit`,SUM(IF(`TransactionTypeEnum`=-1,`Amount`, 0)) as `withdraw` FROM `" . $dbName . "`.`vertex_transactions` WHERE `AccountID` = '$account_no') as t;";
    }

    public function getOpenTradesForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `AccountID` AS login,SUM(IF(`OperationTypeEnum`=0 ,1,0)) `buy_count`,SUM(IF(`OperationTypeEnum`=1,1,0)) `sell_count`,"
                . "SUM(IF(`OperationTypeEnum`=0 ,Amount,0)) `buy_volume`,SUM(IF(`Action`=1,Amount,0)) `sell_volume` "
                . "FROM `" . $dbName . "`.`vertex_positions` WHERE `AccountID`= '$account_no'";
    }

    public function getCloseTradesForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `AccountID` AS `login`,SUM(IF(`ProfitLoss` + `Commission` >= 0 , 1, 0)) `profit_count`, "
                . "SUM(IF(`ProfitLoss` + `Commission` < 0, 1, 0)) `loss_count` FROM `" . $dbName . "`.`vertex_trades` "
                . "WHERE `AccountID` = '$account_no'";
    }

    public function getClosedTradesListForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `AccountID` as `login`, `CloseTime` AS `close_time`, `SymbolName` AS `symbol`, Lots AS `volume`, `OpenPrice` AS open_price,"
                . "`ClosePrice` AS close_price, `ProfitLoss` AS `profit`, '0' AS `is_open`,IF(OperationTypeEnum=-1,1,0) as `cmd` FROM `" . $dbName . "`.`vertex_trades` WHERE  `AccountID` = '$account_no' "
                . " ORDER BY `ClosedTicket` DESC LIMIT 0,5";
    }

    public function getOpenTradesListForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `AccountID` AS `login` ,`DateTime` AS `open_time`, `SymbolName` AS `symbol`, `Amount` AS `volume`,"
                . "`OpenPrice` AS `open_price`, '0' AS `close_price`, `ProfitLoss` AS `profit`, '1' AS `is_open`,IF(OperationTypeEnum=-1,1,0) as `cmd` FROM `" . $dbName . "`.`vertex_positions` "
                . "WHERE `AccountID` = '$account_no' ORDER BY `TicketID` DESC LIMIT 0,5";
    }

    public function getSymbolPerformanceForLiveAccountDashboard($account_no = '') {
        $dbName = $this->getDbName();
        return "SELECT `SymbolName` AS `symbol`,  COUNT(`SymbolName`) AS `symbol_count`, SUM(Lots) AS `sum_volume`, `CloseTime` AS `close_time` FROM `" . $dbName . "`.vertex_trades WHERE AccountID = '$account_no'
        GROUP BY `SymbolName` ORDER BY `sum_volume` DESC LIMIT 0,5";
    }

    public function getTradesStreakForLiveAccountDashboard($account_no = '', $trades_streak_name = '') {
        $dbName = $this->getDbName();
        switch ($trades_streak_name) {
            case 'most_and_least_effective_symbol':
                return "SELECT `a`.`SymbolName`, COUNT(`a`.`SymbolName`) AS `total_trade`, (SELECT COUNT(`SymbolName`) FROM `" . $dbName . "`.`vertex_trades` "
                        . "WHERE `AccountID` = '$account_no' AND `ProfitLoss` > 0  "
                        . "AND `SymbolName` = `a`.`SymbolName`) AS `winning_trade`, ((SELECT COUNT(`SymbolName`) FROM `" . $dbName . "`.`vertex_trades` "
                        . "WHERE `AccountID` = '$account_no' AND `ProfitLoss` > 0  "
                        . "AND `SymbolName` = `a`.`SymbolName`) / COUNT(`a`.`SymbolName`) * 100) AS `winning_ratio` FROM `" . $dbName . "`.`vertex_trades`  "
                        . "AS `a` WHERE `a`.`AccountID` = '$account_no' "
                        . "GROUP BY `a`.`SymbolName`";
                break;
            case 'longest_winning_and_losing_streak':
                return "SELECT `SymbolName` as `symbol`, `ClosedTicket` as `ticket`, `ProfitLoss` as `profit`, `CloseTime` as `close_time` "
                        . "FROM `" . $dbName . "`.vertex_trades WHERE `AccountID` = '$account_no' ORDER BY `CloseTime` DESC";
                break;
            default:
                return '';
                break;
        }
    }

    public function getTop5WinningTradesForLiveAccountDashboard($account_no) {
        $dbName = $this->getDbName();
        return "SELECT `SymbolName` AS `symbol`, `CloseTime` AS `close_time`, Lots AS `volume`, `ClosePrice` AS close_price, `ProfitLoss` AS `profit` FROM `" . $dbName . "`.`vertex_trades` WHERE  `AccountID` = " . $account_no . " HAVING `ProfitLoss` > 0 ORDER BY `ProfitLoss` DESC LIMIT 0,5";
    }

    public function getTop5LossingTradesForLiveAccountDashboard($account_no) {
        $dbName = $this->getDbName();
        return "SELECT `SymbolName` AS `symbol`, `CloseTime` AS `close_time`, Lots `volume`, `ClosePrice` AS close_price, `ProfitLoss` AS `profit` FROM `" . $dbName . "`.`vertex_trades` WHERE  `AccountID` = " . $account_no . " HAVING `ProfitLoss` < 0 ORDER BY `ProfitLoss` ASC LIMIT 0,5";
    }

    public function getProfitForLiveAccountDashboard($account_no, $from_date, $to_date, $day_month) {
        $dbName = $this->getDbName();
        $day_month_query_col = "DATE_FORMAT(`CloseTime`, '%Y-%m') AS `Month`";
        $day_month_query_group_by = "`Month`";
        if ($day_month == 'daily') {
            $day_month_query_col = "DATE_FORMAT(`CloseTime`, '%d') AS `Day`";
            $day_month_query_group_by = "`Day`";
        }
        return "SELECT " . $day_month_query_col . ", SUM(`ProfitLoss`) AS `profit` FROM `" . $dbName . "`.`vertex_trades` WHERE `ProfitLoss` > 0 AND `AccountID` = " . $account_no . " AND `CloseTime` >= '" . $from_date . "' AND `CloseTime` <= '" . $to_date . "' GROUP BY " . $day_month_query_group_by;
    }

    public function getLossForLiveAccountDashboard($account_no, $from_date, $to_date, $day_month) {
        $dbName = $this->getDbName();
        $day_month_query_col = "DATE_FORMAT(`CloseTime`, '%Y-%m') AS `Month`";
        $day_month_query_group_by = "`Month`";
        if ($day_month == 'daily') {
            $day_month_query_col = "DATE_FORMAT(`CloseTime`, '%d') AS `Day`";
            $day_month_query_group_by = "`Day`";
        }
        return "SELECT " . $day_month_query_col . ", SUM(`ProfitLoss`) AS `loss` FROM `" . $dbName . "`.`vertex_trades` WHERE `ProfitLoss` < 0 AND `AccountID` = " . $account_no . " AND `CloseTime` >= '" . $from_date . "' AND `CloseTime` <= '" . $to_date . "' GROUP BY " . $day_month_query_group_by;
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
                $accountWhere = " AND `AccountID` = " . $account_no;
            }

            $sql = "SELECT '$serverName' AS `server_type`, `AccountID` AS `login`, `TicketID` AS `ticket`, `SymbolName` AS `symbol`, "
                    . "Amount AS `volume`, IF(OperationTypeEnum=-1,1,0) as `cmd`,"
                    . "`DateTime` AS `open_time`,  "
                    . "`OpenPrice` AS `open_price`, '' AS `tp`,"
                    . "'' AS `sl`, `Commission` AS `commission`, `Interest` AS `swaps`, `ProfitLoss` AS `profit` "
                    . "FROM `" . $dbName . "`.`vertex_positions` WHERE 1 " . $accountWhere;
        }
        if ($trade_type == 'close') {
            if (!empty($account_no)) {
                $accountWhere = " AND `b`.`AccountID` = " . $account_no;
            }

            $sql = "SELECT '$serverName' AS `server_type`, `b`.`AccountID` AS `login`, `b`.`ClosedTicket` AS `ticket`, `b`.`SymbolName` AS `symbol`,
            `b`.`Lots` AS `volume`, IF(OperationTypeEnum=-1,1,0) as `cmd`, `b`.`OpenTime` AS `open_time`,
            `b`.`OpenPrice` AS `open_price`, `b`.`CloseTime` AS `close_time`, `b`.`ClosePrice` AS `close_price`,
            `b`.`TP` AS `tp`, `b`.`SL` AS `sl`, `b`.`Commission` AS `commission`,
            `b`.`Interest` AS `swaps`, `b`.`ProfitLoss` AS `profit` FROM `" . $dbName . "`.`vertex_trades` as `b`
            WHERE  1=1" . $accountWhere;
        }
        return $sql;
    }

    public function getCountQueryForSubIbTransactionReport($account_no, $from_date, $to_date) {
        $dbName = $this->getDbName();
        return "SELECT count(1) AS `count` FROM `" . $dbName . "`.`vertex_transactions` WHERE 1=1 
        AND `DateTime` >= '" . $from_date . "'
        AND `DateTime` <= '" . $to_date . "'
        AND `AccountID` = " . $account_no;
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
        return "SELECT `AccountID` AS `login`, `TicketID` AS `ticket`, `DateTime` AS `close_time`,
        `Amount` AS `profit`, `Description` as `comment` FROM `" . $dbName . "`.`vertex_transactions`
        WHERE `DateTime` >= '" . $from_date . "'
        AND `DateTime` <= '" . $to_date . "'
        AND `AccountID` = " . $account_no;
    }

    //End Trade Report

    public function getOpenTradesForIBDashboard($account_no, $filter = '') {
        $dbName = $this->getDbName();
        $filter_query = '';
        if ($filter == 'Current Month') {
            $filter_query = " AND MONTH(`DateTime`) = MONTH(CURRENT_DATE())";
        }
        if ($filter == 'Last Month') {
            $filter_query = " AND MONTH(`DateTime`) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
        }
        return "SELECT `AccountID` AS login FROM `" . $dbName . "`.`vertex_positions` WHERE `AccountID`= '$account_no'" . $filter_query;
    }

    public function getCloseTradesForIBDashboard($account_no, $filter = '') {
        $dbName = $this->getDbName();
        $filter_query = '';
        if ($filter == 'Current Month') {
            $filter_query = " AND MONTH(`CloseTime`) = MONTH(CURRENT_DATE())";
        }
        if ($filter == 'Last Month') {
            $filter_query = " AND MONTH(`CloseTime`) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
        }
        return "SELECT `AccountID` AS `login` FROM `" . $dbName . "`.`vertex_trades` "
                . "WHERE `AccountID` = '$account_no' " . $filter_query;
    }

    //For Main Dashboard
    public function getTotalVolumeAndProfitLossForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(Lots) AS `total_volume`, SUM(ProfitLoss) AS `total_profit_loss`  FROM `" . $dbName . "`.`vertex_trades` WHERE `AccountID`";
    }

    public function getTotalVolumeAndProfitLossForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(Lots) AS `volume`, SUM(ProfitLoss) AS `total_profit_loss`, AccountID AS `login`  FROM `" . $dbName . "`.`vertex_trades` WHERE `AccountID`";
    }

    public function getTotalDepositForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(Amount) AS `total_deposit`  FROM `" . $dbName . "`.`vertex_transactions` WHERE  TransactionTypeEnum = 1 AND `AccountID`";
    }

    public function getTotalDepositForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(Amount) AS `total_deposit`  FROM `" . $dbName . "`.`vertex_transactions` WHERE  TransactionTypeEnum = 1 AND `AccountID`";
    }

    public function getTotalWithdrawalForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(Amount) AS `total_withdrawal`  FROM `" . $dbName . "`.`vertex_transactions` WHERE  TransactionTypeEnum = -1 AND `AccountID`";
    }

    public function getTotalWithdrawalForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(Amount) AS `total_withdrawal`  FROM `" . $dbName . "`.`vertex_transactions` WHERE  `TransactionTypeEnum` = -1 AND `AccountID`";
    }

    public function getTotalVolumeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(t.total_volume) as total_volume from (SELECT SUM(Lots) AS `total_volume` FROM `" . $dbName . "`.`vertex_trades` WHERE `AccountID` IN (SELECT `l`.`account_no` FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c`
        ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = ? AND `l`.`live_currency_code` = ? AND `c`.`deleted` = 0 AND `l`.`live_metatrader_type`='Vertex')
        UNION
        SELECT SUM(Amount) AS `total_volume` FROM `" . $dbName . "`.`vertex_positions` WHERE `AccountID` IN (SELECT `l`.`account_no` FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c`
        ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = ? AND `l`.`live_currency_code` = ? AND `c`.`deleted` = 0 AND `l`.`live_metatrader_type`='Vertex')) as t;";
    }

    public function getTotalVolumeForMobileDashboard() {
        $dbName = $this->getDbName();
//        return "SELECT SUM(Lots) AS `total_volume`, `AccountID` AS `login` FROM `" . $dbName . "`.`" . $dbName . "`.`vertex_trades` WHERE `AccountID`";
        return "SELECT SUM(t.total_volume) as total_volume, t.AccountID AS `login` from (SELECT SUM(Lots) AS `total_volume`,AccountID FROM `" . $dbName . "`.`vertex_trades` WHERE `AccountID` IN (SELECT `l`.`account_no` FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c`
                            ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != ''
                            AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = ?
                        AND `l`.`live_currency_code` = ? AND `c`.`deleted` = 0 AND `l`.`live_metatrader_type` = ?)
        UNION
        SELECT SUM(Amount) AS `total_volume`,AccountID FROM `" . $dbName . "`.`vertex_positions` WHERE `AccountID` IN (SELECT `l`.`account_no` FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c`
                            ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != ''
                            AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = ?
                        AND `l`.`live_currency_code` = ? AND `c`.`deleted` = 0 AND `l`.`live_metatrader_type` = ?)) as t;";
    }

    public function getOpenVolumeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(Amount) AS `open_volume` FROM `" . $dbName . "`.`vertex_positions` WHERE `AccountID`";
    }

    public function getOpenVolumeForMobileDashboard() {
        $dbName = $this->getDbName();
        return "SELECT SUM(Amount) AS `open_volume`, `AccountID` AS `login` FROM `" . $dbName . "`.`vertex_positions` WHERE `AccountID`";
    }

    public function getWinTradeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT DATE_FORMAT(`CloseTime`, '%Y-%m') AS Month, COUNT(*) AS `win_trade` FROM `" . $dbName . "`.`vertex_trades` WHERE `ProfitLoss` > 0 AND `AccountID`";
    }

    public function getLossTradeForMainDashboard() {
        $dbName = $this->getDbName();
        return "SELECT DATE_FORMAT(`CloseTime`, '%Y-%m') AS Month, COUNT(*) AS `loss_trade` FROM `" . $dbName . "`.`vertex_trades` WHERE `ProfitLoss` < 0 AND `AccountID`";
    }

    public function getAccountsDataForMainDashboard() {
        return "SELECT `Balance` AS `balance`, `Equity` AS `equity`, `UsedMargin` AS `margin`, `FreeMargin` AS `margin_free`, `AccountID` AS `login` FROM `vertex_accounts`";
    }

    public function getAccountsDataForMobileDashboard() {
        return "SELECT `Balance` AS `balance`, `Equity` AS `equity`, `UsedMargin` AS `margin`, `FreeMargin` AS `margin_free`, `AccountID` AS `login` FROM `vertex_accounts`";
    }

    public function getGroupByForMainDashboard() {
        return " GROUP BY DATE_FORMAT(`CloseTime`, '%Y-%m')";
    }

    public function getDateFilterGreaterForMainDashboard() {
        return " AND `CloseTime` >= '";
    }

    public function getDateFilterLessForMainDashboard() {
        return "' AND `CloseTime` <= '";
    }

    public function getTradingTimeConditions($trade_type, $startDateTime, $endDateTime) {
        if ($trade_type == 'open') {
            $AND = " AND `trades`.open_time >= '" . $startDateTime . "' AND `trades`.open_time <= '" . $endDateTime . "' ";
        } else if ($trade_type == 'close') {
            $AND = " AND `trades`.close_time  >= '" . $startDateTime . "' AND `trades`.close_time <= '" . $endDateTime . "' ";
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

    public function getTranOrderByConditions($isCabinetReq = false) {
        if(!$isCabinetReq)
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
        $accounts_table_exist = $adb->pquery("SELECT 1 FROM `vertex_accounts` LIMIT 1", array());

        if ($accounts_table_exist !== FALSE && !empty($account_no))
        {
            $this->updateAccountSummary($account_no);
            
            $query = "SELECT `AccountID` as `login`, `Balance` as `balance`, `Equity` as `equity`, `UsedMargin` as `margin`, `FreeMargin` as `margin_free` FROM `vertex_accounts` WHERE `AccountID` = ?";
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
        return "SELECT SUM(Lots) AS `total_volume` FROM `" . $dbName . "`.`vertex_trades` WHERE `AccountID` = ? AND `CloseTime` BETWEEN ? AND ?";
    }

    public function getEquity() {
        return "SELECT `EQUITY` AS `equity` FROM `vertex_accounts` WHERE `LOGIN` = ?";
    }
    
    
    public function updateAccountSummary($accountNo = '')
    {
        global $log,$adb;
        $log->debug('updateAccountSummary..'.$accountNo);
        if(!empty($accountNo))
        {
            $accountSummaryDetail = $this->getAccountSummary($accountNo);
            if(!empty($accountSummaryDetail))
            {
                $selectQuery = "SELECT AccountID FROM vertex_accounts WHERE AccountID=?";
                $selectResult = $adb->pquery($selectQuery, array($accountNo));
                $numRows = $adb->num_rows($selectResult);

                if($numRows > 0)
                {
                    $updateQuery = "UPDATE vertex_accounts SET Balance=?,Equity=?,UsedMargin=?,FreeMargin=?,ProfitLoss=?,Credit=? WHERE AccountID=?";
                    $adb->pquery($updateQuery, array($accountSummaryDetail['Balance'], $accountSummaryDetail['Equity'], $accountSummaryDetail['UsedMargin'], $accountSummaryDetail['FreeMargin'], $accountSummaryDetail['ProfitLoss'], $accountSummaryDetail['Credit'], $accountNo));
                }
                else
                {
                    $insertQuery = "INSERT INTO vertex_accounts (AccountID,Balance,Equity,UsedMargin,FreeMargin,ProfitLoss,Credit) VALUES (?,?,?,?,?,?,?)";
                    $adb->pquery($insertQuery, array($accountNo, $accountSummaryDetail['Balance'], $accountSummaryDetail['Equity'], $accountSummaryDetail['UsedMargin'], $accountSummaryDetail['FreeMargin'], $accountSummaryDetail['ProfitLoss'], $accountSummaryDetail['Credit']));
                }
            }
        }
    }

    public function getAccountSummary($accountNo = '') {
        global $log;
        $log->debug('getAccountSummary..'.$accountNo);
        $response = array();
        $providerParams = $this->prepareParameters();
        $meta_trader_ip = $providerParams['live_meta_trader_ip'];
        $token_response = $this->getToken();
        if ($token_response->Code == 200 && $token_response->Message == 'Ok') {
            $params = array();
            $comment = urlencode($comment);
            $request_url = $meta_trader_ip.'/GetAccountSummary?AccountID='.$accountNo;
            $log->debug('$request_url=');
            $log->debug($request_url);
            $token = $token_response->sessionid;
            $headers = $this->headersParams($token);
            $responseInnerJson = $this->fireRequest($request_url, $headers, json_encode($params), 'GET');
            $log->debug('$responseInnerJson=');
            $log->debug($responseInnerJson);
            $responseInner = json_decode($responseInnerJson, true);
            $responseInner['d'] = trim($responseInner['d'],'"');
            $responseInnerJsonDecodArr = json_decode($responseInner['d'], true);
            if (isset($responseInner['d']) && !empty($responseInner['d'])) {
                if (!array_key_exists($responseInnerJsonDecodArr, self::$errorString)) {
                    if ($responseInnerJsonDecodArr['AccountID'] == $accountNo) {
                        $response = $responseInnerJsonDecodArr;
                    } else {
                        $errorKey = isset(self::$errorString[-231]) ? self::$errorString[-231] : '';
                        $error = vtranslate($errorKey,'ServiceProviders');
                        $log->debug('Throw error-'.$error);
                    }
                } else {
                    $errorKey = isset(self::$errorString[$responseInner['d']]) ? self::$errorString[$responseInner['d']] : '';
                    $error = vtranslate($errorKey,'ServiceProviders');
                    $log->debug('Throw error-'.$error);
                }
            } else {
                $errorKey = self::$errorString['-200'];
                $error = vtranslate($errorKey,'ServiceProviders');
                $log->debug('Throw error-'.$error);
            }
            return $response;
        } else {
            return false;
        }
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
        return "SELECT count(`AccountID`) AS `Login` FROM `" . $dbName . "`.`vertex_positions` WHERE `AccountID`= ?";
    }

    public function getTotalVolumeQuery() {
        $dbName = $this->getDbName();
        return "SELECT Lots AS `volume`, AccountID  AS `login`  FROM `" . $dbName . "`.`vertex_trades` WHERE CloseTime BETWEEN ? AND ?";
    }
}
//End