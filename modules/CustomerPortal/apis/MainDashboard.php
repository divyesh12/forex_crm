<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer Widget4_IBTeamPerformanceof the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once 'modules/ServiceProviders/ServiceProviders.php';

class CustomerPortal_MainDashboard extends CustomerPortal_API_Abstract
{

    protected $translate_module = 'CustomerPortal_Client';

    public function process(CustomerPortal_API_Request $request)
    {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $customerId = $this->getActiveCustomer()->id;
            $sub_operation = $request->get('sub_operation');
            $currency = $request->get('currency');

            //For IB Info
            $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
            $contact = vtws_retrieve($contactId, $current_user);
            $contact = CustomerPortal_Utils::resolveRecordValues($contact);
            $ib_hierarchy = $contact['ib_hierarchy']; //current login cabinet user's IB hierarchy
            $affiliate_code = $contact['affiliate_code'];
            $max_ib_level = configvar('max_ib_level'); //All count that client that level equal to less tham max ib level

            if (empty($sub_operation)) {
                throw new Exception(vtranslate('CAB_SUB_OPERATION_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1413);
                exit;
            }

            //Get the active meta trader service proivder
            //$live_metatrader_type = CustomerPortal_Utils::getPicklist('live_metatrader_type')->getResult()['live_metatrader_type'];
            $live_metatrader_type = array();
            $provider = ServiceProvidersManager::getActiveProviderInstance();
            for ($i = 0; $i < count($provider); $i++) {
                if ($provider[$i]::PROVIDER_TYPE == 1) {
                    $live_metatrader_type[] = $provider[$i]->parameters['title'];
                }
            }


            if ($sub_operation == 'TotalFinancials') {
                $this->validateCurrency($currency);

                $total_balance = 0;
                $total_equity = 0;
                $total_margin = 0;
                $total_free_margin = 0;
                $total_volume = 0;
                $total_profit_loss = 0;

                if (!empty($live_metatrader_type)) {
                    foreach ($live_metatrader_type as $key => $value) {
                        $meta_trader_type = $value;

                        $provider = ServiceProvidersManager::getActiveInstanceByProvider($value);
                        if (empty($provider)) {
                            throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'LiveAccount', $portal_language), 1416);
                        }
                        $syncConfig = $provider->syncConfig;
                        if(!$syncConfig['balance']) {
                            $accountList = getLiveAccountList($customerId, $provider->parameters['title']);
                            $trades_query = $provider->getAccountsDataForMainDashboard($accountList);
                        } else {
                            $trades_query = $provider->getAccountsDataForMainDashboard();
                        }
                        $from_query = " FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c`
                         ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = " . $customerId . " AND `l`.`live_currency_code` = '" . $currency . "' AND `c`.`deleted` = 0";

                        $liveaccount_sql = "SELECT SUM(`t`.`balance`) AS `total_balance`, SUM(`t`.`equity`)
                        AS `total_equity`, SUM(`t`.`margin`) AS `total_margin`, SUM(`t`.`margin_free`) AS `margin_free`
                        FROM `vtiger_liveaccount` AS `l`
                        INNER JOIN `vtiger_crmentity` AS `c` ON `l`.`liveaccountid` = `c`.`crmid`
                        INNER JOIN (" . $trades_query . ") AS `t` ON `t`.`login` = `l`.`account_no`
                        WHERE `c`.`deleted` = 0 AND `l`.`account_no` != 0 AND `record_status` = 'Approved'
                        AND `l`.`contactid` = " . $customerId . "
                        AND `l`.`live_metatrader_type` = '" . $meta_trader_type . "' AND `l`.`live_currency_code` = '" . $currency . "'";

                        $sqlResult = $adb->pquery($liveaccount_sql, array());
                        $numRow = $adb->num_rows($sqlResult);
                        if ($numRow > 0) {
                            $total_balance = $total_balance + $adb->query_result($sqlResult, 0, 'total_balance');
                            $total_equity = $total_equity + $adb->query_result($sqlResult, 0, 'total_equity');
                            $total_margin = $total_margin + $adb->query_result($sqlResult, 0, 'total_margin');
                            $total_free_margin = $total_free_margin + $adb->query_result($sqlResult, 0, 'margin_free');
                        }
                        //End
                        //Trades data

                        $trades_query = $provider->getTotalVolumeAndProfitLossForMainDashboard();
                        $trades_query .= " IN(SELECT `l`.`account_no` " . $from_query . ")";
                        $sqlResult = $adb->pquery($trades_query, array());
                        $numRow = $adb->num_rows($sqlResult);
                        if ($numRow > 0) {
                            $total_volume = $adb->query_result($sqlResult, 0, 'total_volume');
                            $total_profit_loss += $adb->query_result($sqlResult, 0, 'total_profit_loss');
                        }
                        //End
                    }
                }
                $response->addToResult('records', array(
                    'total_balance' => CustomerPortal_Utils::setNumberFormat($total_balance),
                    'total_equity' => CustomerPortal_Utils::setNumberFormat($total_equity), 'total_margin' => CustomerPortal_Utils::setNumberFormat($total_margin),
                    'total_free_margin' => CustomerPortal_Utils::setNumberFormat($total_free_margin), 'total_profit_loss' => CustomerPortal_Utils::setNumberFormat($total_profit_loss),
                    'total_volume' => (float) $total_volume
                ));
            } else if ($sub_operation == 'TotalLiveDemoAccounts') {
                $this->validateCurrency($currency);

                $total_live_account = 0;
                $total_demo_account = 0;
                $sql = "SELECT COUNT(1) AS `total_live_account` FROM `vtiger_liveaccount` AS `l` INNER JOIN `vtiger_crmentity`  AS `c` ON `c`.`crmid` = `l`.`liveaccountid` WHERE `l`.`account_no` != '' AND `l`.`account_no` != 0 AND `l`.`record_status`= 'Approved' AND `l`.`live_currency_code`= '" . $currency . "' AND `c`.`deleted` = 0 AND `l`.`contactid` = " . $customerId;

                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                if ($numRow > 0) {
                    $total_live_account = $adb->query_result($sqlResult, 0, 'total_live_account');
                }

                //Get total Demo accoutns
                $sql = "SELECT COUNT(1) AS `total_demo_account` FROM `vtiger_demoaccount` AS `d` INNER JOIN `vtiger_crmentity`  AS `c` ON `c`.`crmid` = `d`.`demoaccountid` WHERE `d`.`account_no` != '' AND `d`.`account_no` != 0 AND `d`.`demo_currency_code` = '" . $currency . "' AND `c`.`deleted` = 0 AND `d`.`contactid` = " . $customerId;

                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                if ($numRow > 0) {
                    $total_demo_account = $adb->query_result($sqlResult, 0, 'total_demo_account');
                }
                $response->addToResult('records', array('total_live_account' => $total_live_account, 'total_demo_account' => $total_demo_account));
            } else if ($sub_operation == 'TotalDepositWithdrawal') {
                $this->validateCurrency($currency);
                $total_deposit = 0;
                $total_withdrawal = 0;

                //Get metatraer type list from picklist
                //$live_metatrader_type = CustomerPortal_Utils::getPicklist('live_metatrader_type')->getResult()['live_metatrader_type'];
                if (!empty($live_metatrader_type)) {
                    foreach ($live_metatrader_type as $key => $value) {
                        $meta_trader_type = $value;
                        $provider = ServiceProvidersManager::getActiveInstanceByProvider($value);
                        if (empty($provider)) {
                            throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'LiveAccount', $portal_language), 1416);
                        }

                        $from_query = " FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c` ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = " . $customerId . " AND `l`.`live_metatrader_type` = '" . $meta_trader_type . "' AND `l`.`live_currency_code` = '" . $currency . "' AND `c`.`deleted` = 0";


                        //Trades data
                        $trades_query = $provider->getTotalDepositForMainDashboard();
                        $trades_query .= " IN(SELECT `l`.`account_no` " . $from_query . ")";
                        $sqlResult = $adb->pquery($trades_query, array());
                        $numRow = $adb->num_rows($sqlResult);
                        if ($numRow > 0) {
                            $total_deposit = $total_deposit + $adb->query_result($sqlResult, 0, 'total_deposit');
                        }
                        //End
                    }
                }

                //This for withdrawal account total
                //Get metatraer type list from picklist
                //$live_metatrader_type = CustomerPortal_Utils::getPicklist('live_metatrader_type')->getResult()['live_metatrader_type'];
                if (!empty($live_metatrader_type)) {
                    foreach ($live_metatrader_type as $key => $value) {
                        $meta_trader_type = $value;
                        $provider = ServiceProvidersManager::getActiveInstanceByProvider($value);
                        if (empty($provider)) {
                            throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'LiveAccount', $portal_language), 1416);
                        }

                        $from_query = " FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c` ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = " . $customerId . " AND `l`.`live_metatrader_type` = '" . $meta_trader_type . "' AND `l`.`live_currency_code` = '" . $currency . "' AND `c`.`deleted` = 0";

                        //Trades data
                        $trades_query = $provider->getTotalWithdrawalForMainDashboard();
                        $trades_query .= " IN(SELECT `l`.`account_no` " . $from_query . ")";
                        $sqlResult = $adb->pquery($trades_query, array());
                        $numRow = $adb->num_rows($sqlResult);
                        if ($numRow > 0) {
                            $total_withdrawal = $total_withdrawal + ABS($adb->query_result($sqlResult, 0, 'total_withdrawal'));
                        }
                        //End
                    }
                }

                $response->addToResult('records', array('total_deposit' => $total_deposit, 'total_withdrawal' => $total_withdrawal));
            } else if ($sub_operation == 'Ewallet') {
                $this->validateCurrency($currency);

                $ewallet_balance = 0;
                $ewallet_total_deposit = 0;
                $ewallet_total_withdrawal = 0;

                $ewallet_balance_ar = CustomerPortal_Utils::getEwalletBalance($customerId, $currency);
                foreach ($ewallet_balance_ar as $key => $value) {
                    if ($value['currency'] == $currency) {
                        $ewallet_balance = $value['total_amount'];
                        break;
                    }
                }

                $getEwaINOutSum = CustomerPortal_Utils::getSumOfEwalletInAndOut($customerId, $currency);
                $ewallet_total_deposit = $getEwaINOutSum['depositSum'];
                $ewallet_total_withdrawal = $getEwaINOutSum['withdrawalSum'];
                $response->addToResult('records', array('ewallet_total_deposit' => CustomerPortal_Utils::setNumberFormat($ewallet_total_deposit, 2), 'ewallet_total_withdrawal' => CustomerPortal_Utils::setNumberFormat($ewallet_total_withdrawal, 2), 'ewallet_balance' => CustomerPortal_Utils::setNumberFormat($ewallet_balance, 2)));
            } else if ($sub_operation == 'Volume') {
                $this->validateCurrency($currency);

                $openVolume = 0;
                $totalVolume = 0;

                //$live_metatrader_type = CustomerPortal_Utils::getPicklist('live_metatrader_type')->getResult()['live_metatrader_type'];
                if (!empty($live_metatrader_type)) {
                    foreach ($live_metatrader_type as $key => $value) {

                        $provider = ServiceProvidersManager::getActiveInstanceByProvider($value);
                        if (empty($provider)) {
                            throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'LiveAccount', $portal_language), 1416);
                        }

                        $from_query = " FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c`
                            ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = " . $customerId . " AND `l`.`live_currency_code` = '" . $currency . "' AND `c`.`deleted` = 0 AND `l`.`live_metatrader_type` = '".$value."'";

                        /* Total Volume Query */
                        $providerType = strtolower(getProviderType($value));
                        if($providerType == 'vertex')
                        {
                            $trades_query_total = $provider->getTotalVolumeForMainDashboard();
                            $sqlResultTotal = $adb->pquery($trades_query_total, array($customerId, $currency, $customerId, $currency));
                        }
                        else
                        {
                            $trades_query_total = $provider->getTotalVolumeForMainDashboard();
                            $trades_query_total .= " IN(SELECT `l`.`account_no` " . $from_query . ")";
                            $sqlResultTotal = $adb->pquery($trades_query_total, array());
                        }
                        
                        $numRowTotal = $adb->num_rows($sqlResultTotal);
                        if ($numRowTotal > 0) {
                            $totalVolume = $totalVolume + $adb->query_result($sqlResultTotal, 0, 'total_volume');
                        }
                        /* End */

                        /* Open Volume Query */
                        $trades_query_open = $provider->getOpenVolumeForMainDashboard();
                        $trades_query_open .= " IN(SELECT `l`.`account_no` " . $from_query . ")";
                        $sqlResultOpen = $adb->pquery($trades_query_open, array());
                        $numRowOpen = $adb->num_rows($sqlResultOpen);
                        if ($numRowOpen > 0) {
                            $openVolume = $openVolume + $adb->query_result($sqlResultOpen, 0, 'open_volume');
                        }
                        /* End */
                    }
                }
                $response->addToResult('records', array('open_volume' => CustomerPortal_Utils::setNumberFormatWithoutCommaSeparater($openVolume, 2), 'total_volume' => CustomerPortal_Utils::setNumberFormat($totalVolume, 2)));
            } else if ($sub_operation == 'AccountInfo') {
                $this->validateCurrency($currency);
                $liveAccInfo = array();
                $account_number = 0;
                $leverage = 0;
                $metatrader_type = '';
                $created_time = 0;
                $from_query = " FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c` ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = " . $customerId . " AND `l`.`live_currency_code` = '" . $currency . "' AND `c`.`deleted` = 0";
                $liveaccount_sql = "SELECT `l`.`account_no` AS `account_number`, `l`.`leverage`, `l`.`live_metatrader_type` AS `metatrader_type`, `c`.`createdtime` AS `created_time`" . $from_query;
                $sqlResult = $adb->pquery($liveaccount_sql, array());
                $numRow = $adb->num_rows($sqlResult);
                if ($numRow > 0) {
                    for ($i = 0; $i < $numRow; $i++) {
                        $account_number = $adb->query_result($sqlResult, $i, 'account_number');
                        $leverage = $adb->query_result($sqlResult, $i, 'leverage');
                        $metatrader_type = $adb->query_result($sqlResult, $i, 'metatrader_type');
                        $created_time = date('M, Y', strtotime($adb->query_result($sqlResult, $i, 'created_time')));
                        $liveAccInfo[$i][$account_number] = array('leverage' => $leverage, 'metatrader_type' => $metatrader_type, 'created_time' => $created_time);
                    }
                }
                $response->addToResult('records', array('live_account_info' => $liveAccInfo));
            } else if ($sub_operation == 'TradeBehaviour') {
                $this->validateCurrency($currency);

                $TradeBehaviour = array();
                $TradeBehaviourWinTrade = array();
                $TradeBehaviourLossTrade = array();

                $rowsWin = array();
                $rowsWin_month = array();
                $rowsLoss = array();

                //Defualt
                $months = array();
                $month_year = array();
                $main_sum_array = array();

                for ($i = 11; $i > -1; $i--) {
                    $months[$i] = date("M", strtotime(date('Y-m-01') . " -$i months"));
                    $month_year[$i] = date("M Y", strtotime(date('Y-m-01') . " -$i months"));
                }

                $month_year = array_values($month_year);
                $months = array_values($months);

                foreach ($months as $key => $value) {
                    $main_sum_array[$value] = array('month' => $value, 'win_trade' => 0, 'loss_trade' => 0, 'month_year' => $month_year[$key]);
                }
                //End

                //$live_metatrader_type = CustomerPortal_Utils::getPicklist('live_metatrader_type')->getResult()['live_metatrader_type'];
                if (!empty($live_metatrader_type)) {
                    foreach ($live_metatrader_type as $key => $value) {
                        $from_date = date("Y-m-01", strtotime(date('Y-m-d') . " -11 months"));
                        $to_date = date("Y-m-d");

                        $provider = ServiceProvidersManager::getActiveInstanceByProvider($value);
                        if (empty($provider)) {
                            throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'LiveAccount', $portal_language), 1416);
                        }

                        $groupBy = $provider->getGroupByForMainDashboard();
                        $dateFilterGreater = $provider->getDateFilterGreaterForMainDashboard();
                        $dateFilterLess = $provider->getDateFilterLessForMainDashboard();
                        $dateFilter = $dateFilterGreater . $from_date . $dateFilterLess . $to_date . "' ";

                        /*if ($value == 'MT4') {
                        $groupBy = " GROUP BY DATE_FORMAT(`close_time`, '%Y-%m')";
                        $dateFilter = " AND `close_time` >= '" . $from_date . "' AND `close_time` <= '" . $to_date . "' ";
                        } else {
                        $groupBy = " GROUP BY DATE_FORMAT(`Time`, '%Y-%m')";
                        $dateFilter = " AND `Time` >= '" . $from_date . "' AND `Time` <= '" . $to_date . "' ";
                        }*/

                        $from_query = " FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c` ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = " . $customerId . " AND `l`.`live_currency_code` = '" . $currency . "' AND `l`.`live_metatrader_type` = '" . $value . "' AND `c`.`deleted` = 0";
                        $joinQuery = " IN(SELECT `l`.`account_no` " . $from_query . ") " . $dateFilter . $groupBy;

                        $sqlWin = $provider->getWinTradeForMainDashboard();
                        $winTradeQuery = $sqlWin . $joinQuery;
                        $sqlResultWinTrade = $adb->pquery($winTradeQuery, array());
                        $num_rows_wintrade = $adb->num_rows($sqlResultWinTrade);
                        $rowsWin = array();
                        for ($i = 0; $i < $num_rows_wintrade; $i++) {
                            $rowsWin[$i]['month'] = date("M", strtotime($adb->query_result($sqlResultWinTrade, $i, 'month')));
                            $rowsWin[$i]['win_trade'] = $adb->query_result($sqlResultWinTrade, $i, 'win_trade');
                            $rowsWin[$i]['loss_trade'] = 0;
                        }

                        $sqlLoss = $provider->getLossTradeForMainDashboard();
                        $lossTradeQuery = $sqlLoss . $joinQuery;
                        $sqlResultLossTrade = $adb->pquery($lossTradeQuery, array());
                        $num_rows_losstrade = $adb->num_rows($sqlResultLossTrade);
                        $rowsLoss = array();
                        for ($i = 0; $i < $num_rows_losstrade; $i++) {
                            $rowsLoss[$i]['month'] = date("M", strtotime($adb->query_result($sqlResultLossTrade, $i, 'month')));
                            $rowsLoss[$i]['win_trade'] = 0;
                            $rowsLoss[$i]['loss_trade'] = $adb->query_result($sqlResultLossTrade, $i, 'loss_trade');
                        }
                        $main_sum_array = $this->getTradeBehaviourGraphDataMapping($rowsWin, $rowsLoss, $months, $main_sum_array);
                    }
                }
                $response->addToResult('records', array('tradeBehaviour' => $main_sum_array));
            } else if ($sub_operation == 'IBInfo') {
                $total_clients = 0;
                $total_commission = 0;

                $referral_affiliate_link = configvar('liveacc_referral_url');
                //Referral link generation
                $affiliate_code = $contact['affiliate_code'];
                $record_status = $contact['record_status'];
                $ib_hierarchy = $contact['ib_hierarchy'];
                if (strpos($referral_affiliate_link, '?')) {
                    $referral_affiliate_link = $referral_affiliate_link . '&ref=' . $affiliate_code;
                } else {
                    $referral_affiliate_link = $referral_affiliate_link . '?ref=' . $affiliate_code;
                }

                $check_max_level = " AND findIBLevel(REPLACE(`c`.`ib_hierarchy`,'" . $ib_hierarchy . "','')) <= " . $max_ib_level;

                //$where = " WHERE parent_contactid = " . $customerId . " AND child_contactid != " . $customerId;

                $where = " WHERE parent_contactid = " . $customerId . " AND child_contactid IN
                (SELECT contactid FROM vtiger_contactdetails WHERE ib_hierarchy LIKE '" . $ib_hierarchy . "%')";

                //Total Commission and volume with all status
                $sql = "SELECT sum(commission_amount) as total_commission_amount FROM anl_comm_child " . $where;

                $sqlResult = $adb->pquery($sql, array());
                if ($adb->num_rows($sqlResult) > 0) {
                    $total_commission = $adb->query_result($sqlResult, 0, 'total_commission_amount');
                }

                //Total Sub IB: Total no. of Associated Accounts having IB Status = Approved
                //All child contact ids
                $with_approved = ' AND `c`.record_status = "Approved"';
                $total_clients_sql = 'SELECT COUNT(1) FROM `vtiger_contactdetails` AS c INNER JOIN `vtiger_crmentity` AS ce ON c.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0  AND `c`.`contactid` !=  ' . $customerId . ' AND `c`.`ib_hierarchy` LIKE "' . $ib_hierarchy . '%"' . $check_max_level;
                $sql = "SELECT (" . $total_clients_sql . ") AS `total_clients`";
                $sqlResult = $adb->pquery($sql, array());
                if ($adb->num_rows($sqlResult) > 0) {
                    $total_clients = $adb->query_result($sqlResult, 0, 'total_clients');
                }

                $response->addToResult('records', array(
                    'total_clients' => $total_clients,
                    'total_commission' => CustomerPortal_Utils::setNumberFormat($total_commission),
                    'affiliate_code' => $affiliate_code,
                    'referral_affiliate_link' => $referral_affiliate_link, 'record_status' => $record_status
                ));
            } else {
                throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module, $portal_language), 1418);
                exit;
            }
            return $response;
        }
    }

    public function validateCurrency($currency)
    {
        if (empty($currency)) {
            throw new Exception(vtranslate('CAB_MSG_CURRENCY_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1413);
            exit;
        }
    }

    public function getTradeBehaviourGraphDataMapping($rowsWin, $rowsLoss, $months, $main_sum_array)
    {
        //for Win trade array mapping
        foreach ($rowsWin as $k => $v) {
            if (in_array($v['month'], $months)) {
                $main_sum_array[$v['month']]['win_trade'] = $main_sum_array[$v['month']]['win_trade'] + $v['win_trade'];
            }
        }

        //For Loss trade array mapping
        foreach ($rowsLoss as $k => $v) {
            if (in_array($v['month'], $months)) {
                $main_sum_array[$v['month']]['loss_trade'] = $main_sum_array[$v['month']]['loss_trade'] + $v['loss_trade'];
            }
        }
        return $main_sum_array;
    }
}
