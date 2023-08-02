<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

/* @Add By Divyesh
 * @Date:- 15-11-2019
 * @Comment:-  Registor meta traders functions
 */

interface ITradingPlatform {
    //  const MSG_STATUS_DISPATCHED = 'Dispatched';

    /**
     * Function to get required parameters other than (userName, password)
     */
    public function getRequiredParams();

    /**
     * Function to get query for status using messgae id
     * @param <Number> $messageId
     */
    public function getToken($module);

    /**
     * Function to get query for status using messgae id
     * @param <Number> $messageId
     */
    public function checkLogin();

    /**
     * Function to Check Server Configuration
     * @param <Number> $messageId
     */
    public function checkMetaTraderServerConfiguration($client_type, $server_type, $meta_trader_ip, $meta_trader_user, $meta_trader_password, $module);

    /**
     * Function to get responce of account creation
     */
    public function createAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency, $contactid='', $otherParam = array());

    /**
     * Function to get responce of account creation
     */
    public function createDemoAccount($city, $state, $country, $address, $zipcode, $phone_number, $comment, $account_no, $password, $investor_password, $phonepassword, $account_type, $leverage, $client_name, $client_email, $label_account_type, $currency, $contactid = '', $otherParam = array());

    //public function createAccount($params);

    /**
     * Function to get responce of  deposit into  account balance
     */
    public function deposit($account_no, $amount, $comment);

    /**
     * Function to get responce of  deposit into  account balance
     */
    public function depositToDemoAccount($account_no, $amount, $comment);

    /**
     * Function to get responce of  withdrawal into  account balance
     */
    public function withdrawal($account_no, $amount, $comment);

    /**
     * Function to get response  of  get account info
     */
    public function getAccountInfo($account_no);

    /**
     * Function to get responce of  get account balance
     */
    public function getBalance($account_no);

    /**
     * Function to get responce of  change leverage of account
     */
    public function changeLeverage($account_no, $leverage);

    /**
     * Function to get responce of  login exist or not
     */
    public function checkAccountExist($account_no);

    /**
     * Function to get responce of changeAccountType
     */
    public function changeAccountGroup($account_no, $account_type);

    /**
     * Function to get responce of change Password and Investor Password
     */
    public function changePassword($account_no, $password, $IsInvestor);

    /**
     * Function to get responce of LiveAccount or DemoAccount Disable
     */
    public function accountDisable($account_no);

    /**
     * Function to get responce of DemoAccount Disable
     */
    public function demoaccountDisable($account_no);

    /**
     * Function to get responce of LiveAccount or DemoAccount Enable
     */
    public function accountEnable($account_no);

    public function getOutstandingForLiveAccountDashboard($account_no);

    public function getProfitLossForLiveAccountDashboard($account_no);

    public function getOpenTradesForLiveAccountDashboard($account_no);

    public function getCloseTradesForLiveAccountDashboard($account_no);

    public function getClosedTradesListForLiveAccountDashboard($account_no);

    public function getOpenTradesListForLiveAccountDashboard($account_no);

    public function getSymbolPerformanceForLiveAccountDashboard($account_no);

    public function getTradesStreakForLiveAccountDashboard($account_no, $trades_streak_name);

    public function getTradesForReport($trade_type, $account_no);

    public function getTransactionsForReport($account_no, $from_date, $to_date);

    public function getOpenTradesForIBDashboard($account_no, $filter = '');

    public function getCloseTradesForIBDashboard($account_no, $filter = '');

    public function getTotalVolumeAndProfitLossForMainDashboard();

    public function getTotalVolumeAndProfitLossForMobileDashboard();

    public function getTotalDepositForMainDashboard();

    public function getTotalDepositForMobileDashboard();

    public function getTotalWithdrawalForMainDashboard();

    public function getTotalWithdrawalForMobileDashboard();

    public function getTotalVolumeForMainDashboard();

    public function getTotalVolumeForMobileDashboard();

    public function getOpenVolumeForMainDashboard();

    public function getOpenVolumeForMobileDashboard();

    public function getWinTradeForMainDashboard();

    public function getLossTradeForMainDashboard();

    public function getAccountsDataForMainDashboard();

    public function getAccountsDataForMobileDashboard();

    public function getGroupByForMainDashboard();

    public function getDateFilterGreaterForMainDashboard();

    public function getDateFilterLessForMainDashboard();

    public function getCountQueryForSubIbTransactionReport($account_no, $from_date, $to_date);

    public function getTradingTimeConditions($trade_type, $startDateTime, $endDateTime);

    public function getTradingOrderByConditions($trade_type = '');

    public function getTranTimeConditions($startDateTime, $endDateTime);

    public function getTranOrderByConditions($isCabinetReq = false);

    public function getProviderSpecificData($result = array());

    public function getProviderSpecificTradeData($result = array(), $trade_type = '');
    
    public function updateAccountParams($account_no = '', $recordId = '');
    
    public function getCurrentMonthTotalVolume();
    
    public function getEquity();
    
    public function isInvestorButtonActive();

    public function checkIsExistOpenTradesOfAccount();
}
