<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once 'modules/ServiceProviders/ServiceProviders.php';

class CustomerPortal_LiveAccountDashboard extends CustomerPortal_API_Abstract
{

    protected $translate_module = 'CustomerPortal_Client';

    protected function processRetrieve(CustomerPortal_API_Request $request)
    {
        global $current_user, $adb;
        $customerId = $this->getActiveCustomer()->id;
        $parentId = $request->get('parentId');
        $recordId = $request->get('recordId');
        $sub_operation = $request->get('sub_operation');
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if (empty($recordId)) {
            throw new Exception(vtranslate('CAB_MSG_SEEMS_THAT_YOU_HAVE_NOT_ANY_ACCOUNT', $this->translate_module, $portal_language), 1412);
            exit;
        }
        $module = VtigerWebserviceObject::fromId($adb, $recordId)->getEntityName();

        //Check configuration added by sandeep 20-02-2020
        $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
        CustomerPortal_Utils::checkConfiguration($contactId, $current_user, $module, array(), $portal_language);
        //End

        if (!CustomerPortal_Utils::isModuleActive($module)) {
            throw new Exception(vtranslate('CAB_MSG_RECORDS_NOT_ACCESSIBLE_FOR_THIS_MODULE', $this->translate_module, $portal_language), 1412);
            exit;
        }

        if (!empty($parentId)) {
            $relatedRecordIds = $this->relatedRecordIds($module, CustomerPortal_Utils::getRelatedModuleLabel($module), $parentId);
            if (!in_array($recordId, $relatedRecordIds)) {
                throw new Exception(vtranslate('CAB_MSG_RECORD_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }
        } else {
            if (!$this->isRecordAccessible($recordId, $module)) {
                throw new Exception(vtranslate('CAB_MSG_RECORD_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }
        }
        //Fecth live account details
        $account_no = '';
        $live_metatrader_type = '';
        $liveaccount_details = vtws_retrieve($recordId, $current_user);
        if (!empty($liveaccount_details)) {
            $account_no = $liveaccount_details['account_no'];
            $live_metatrader_type = $liveaccount_details['live_metatrader_type'];
        } else {
            throw new Exception(vtranslate('CAB_MSG_LIVE_ACCOUNT_DOES_NOT_EXIST', $module, $portal_language), 1412);
            exit;
        }
        //End
        $liveaccount_dashboard_data = array();
        if ($sub_operation == 'Outstanding') {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
            if (empty($provider)) {
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
            }
            $syncConfig = $provider->syncConfig;
            $balance = 0;
            $currency = '';
            $live_metatrader_type = '';
            $leverage = '';
            $profit_loss = 0;
            $commission = 0;
            $swap = 0;
            $margin = 0;
            $margin_free = 0;
            $equity = 0;
            $total_deposit = 0;
            $total_withdrawal = 0;

            $currency = $liveaccount_details['live_currency_code'];
            $live_metatrader_type = $liveaccount_details['live_metatrader_type'];
            $leverage = $liveaccount_details['leverage'];

            /*Get Balance liveaccount dashboard */
            if(!$syncConfig['balance']) {
                $balanceData = $provider->getOutstandingForLiveAccountDashboardUsingAPI($account_no);
                $balance = $balanceData['balance'];
                $equity = $balanceData['equity'];
                $margin = $balanceData['margin'];
                $margin_free = $balanceData['free_margin'];
            } else {
                $sql = $provider->getOutstandingForLiveAccountDashboard($account_no);
                $sqlResult = $adb->pquery($sql, array());
                $num_rows = $adb->num_rows($sqlResult);
                if ($num_rows > 0) {
                    $balance = $adb->query_result($sqlResult, 0, 'balance');
                    $equity = $adb->query_result($sqlResult, 0, 'equity');
                    $margin = $adb->query_result($sqlResult, 0, 'margin');
                    $margin_free = $adb->query_result($sqlResult, 0, 'margin_free');
                }
            }
            /*Get Balance liveaccount dashboard */

            /*Get profit loss for liveaccount dashboard */
            $sql = $provider->getProfitLossForLiveAccountDashboard($account_no);
            $sqlResult = $adb->pquery($sql, array());
            $num_rows = $adb->num_rows($sqlResult);
            if ($num_rows > 0) {
                $profit_loss = $adb->query_result($sqlResult, 0, 'profit_loss');
                $commission = $adb->query_result($sqlResult, 0, 'commission');
                $swap = $adb->query_result($sqlResult, 0, 'swap');
                $total_deposit = $adb->query_result($sqlResult, 0, 'deposit');
                $total_withdrawal = $adb->query_result($sqlResult, 0, 'withdraw');
            }
            /*Get profit loss for liveaccount dashboard */

            $investor_pass_enable = true;
            if (isset($provider->parameters['investor_pass_enable']) && strtolower($provider->parameters['investor_pass_enable']) == 'no') {
                $investor_pass_enable = false; 
            }
            $liveaccount_dashboard_data = array(
                'balance' => round($balance, 4), //Total Balance
                'live_currency_code' => $currency,
                'live_metatrader_type' => $live_metatrader_type, // Plat Form
                'leverage' => $leverage,
                'profit_loss' => round($profit_loss, 4),
                'commission' => round($commission, 4),
                'swap' => round($swap, 4),
                'margin' => round($margin, 4),
                'margin_free' => round($margin_free, 4),
                'equity' => round($equity, 4),
                'total_deposit' => round($total_deposit, 4),
                'total_withdrawal' => round($total_withdrawal, 4),
                'investor_pass_enable' => $investor_pass_enable,
            );
        } else if ($sub_operation == 'ProfitLoss_old') {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);

            if (empty($provider)) {
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
            }

            $sql = $provider->getProfitLossForLiveAccountDashboard($account_no);
            $sqlResult = $adb->pquery($sql, array());
            $num_rows = $adb->num_rows($sqlResult);
            if ($num_rows > 0) {
                $profit_loss = $adb->query_result($sqlResult, 0, 'profit_loss');
                $total_deposit = $adb->query_result($sqlResult, 0, 'deposit');
                $total_withdrawal = $adb->query_result($sqlResult, 0, 'withdraw');
                $liveaccount_dashboard_data = array(
                    array('key' => 'profit_loss', 'label' => 'CAB_LBL_PROFIT_LOSS', 'value' => round($profit_loss, 4)),
                    array('key' => 'total_deposit', 'label' => 'CAB_LBL_TOTAL_DEPOSIT', 'value' => round($total_deposit, 4)),
                    array('key' => 'total_withdrawal', 'label' => 'CAB_LBL_TOTAL_WITHDRAWAL', 'value' => round($total_withdrawal, 4)),
                );
            } else {
                $liveaccount_dashboard_data = array(
                    array('key' => 'profit_loss', 'label' => 'CAB_LBL_PROFIT_LOSS', 'value' => 0),
                    array('key' => 'total_deposit', 'label' => 'CAB_LBL_TOTAL_DEPOSIT', 'value' => 0),
                    array('key' => 'total_withdrawal', 'label' => 'CAB_LBL_TOTAL_WITHDRAWAL', 'value' => 0),
                );
            }
        } else if ($sub_operation == 'ProfitLoss') {
            $profit_loss_filter = $request->get('profit_loss_filter');
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
            if (empty($provider)) {
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
            }

            if (empty($profit_loss_filter)) {
                throw new Exception(vtranslate('CAB_LBL_MSG_PROFIT_LOSS_FILTER_SHOULD_NOT_BE_EMPTY', $module, $portal_language), 1416);
            }

            $rowsWin = array();
            $rowsLoss = array();
            $months = array();
            $with_year = array();
            $main_sum_array = array();
            $daily = array();

            if ($profit_loss_filter == 'daily') {
                $data_set = $this->getLast12DaysDataSet();
                $with_year = $data_set[0]; // 28Jun 2021
                $days = $data_set[2]; // 2021-06-28 and  $data_set[1]  return 28 as day only
                foreach ($days as $key => $value) {
                    $main_sum_array[$value] = array('x' => $value, 'profit' => 0, 'loss' => 0, 'with_year' => $with_year[$key]);
                }
                $from_date = $data_set[1][0] . ' 00:00:00';
                $to_date = date("Y-m-d 23:59:59");

                $winTradeQuery = $provider->getProfitForLiveAccountDashboard($account_no, $from_date, $to_date, $profit_loss_filter);
                $sqlResultWinTrade = $adb->pquery($winTradeQuery, array());
                $num_rows_wintrade = $adb->num_rows($sqlResultWinTrade);
                $rowsWin = array();
                for ($i = 0; $i < $num_rows_wintrade; $i++) {
                    $rowsWin[$i]['x'] = $adb->query_result($sqlResultWinTrade, $i, 'day');
                    $rowsWin[$i]['profit'] = $adb->query_result($sqlResultWinTrade, $i, 'profit');
                    $rowsWin[$i]['loss'] = 0;
                }

                $lossTradeQuery = $provider->getLossForLiveAccountDashboard($account_no, $from_date, $to_date, $profit_loss_filter);
                $sqlResultLossTrade = $adb->pquery($lossTradeQuery, array());
                $num_rows_losstrade = $adb->num_rows($sqlResultLossTrade);
                $rowsLoss = array();
                for ($i = 0; $i < $num_rows_losstrade; $i++) {
                    $rowsLoss[$i]['x'] = $adb->query_result($sqlResultLossTrade, $i, 'day');
                    $rowsLoss[$i]['profit'] = 0;
                    $rowsLoss[$i]['loss'] = $adb->query_result($sqlResultLossTrade, $i, 'loss');
                }
                $main_sum_array = $this->getTradeBehaviourGraphDataMappingDaily($rowsWin, $rowsLoss, $days, $main_sum_array);
            } else {
                for ($i = 11; $i > -1; $i--) {
                    $months[$i] = date("M", strtotime(date('Y-m-01') . " -$i months"));
                    $with_year[$i] = date("M Y", strtotime(date('Y-m-01') . " -$i months"));
                }

                $with_year = array_values($with_year);
                $months = array_values($months);

                foreach ($months as $key => $value) {
                    $main_sum_array[$value] = array('x' => $value, 'profit' => 0, 'loss' => 0, 'with_year' => $with_year[$key]);
                }

                $from_date = date("Y-m-01 00:00:00", strtotime(date('Y-m-d') . " -11 months"));
                $to_date = date("Y-m-d 23:59:59");

                $winTradeQuery = $provider->getProfitForLiveAccountDashboard($account_no, $from_date, $to_date, $profit_loss_filter);
                $sqlResultWinTrade = $adb->pquery($winTradeQuery, array());
                $num_rows_wintrade = $adb->num_rows($sqlResultWinTrade);
                $rowsWin = array();
                for ($i = 0; $i < $num_rows_wintrade; $i++) {
                    $rowsWin[$i]['x'] = date("M", strtotime($adb->query_result($sqlResultWinTrade, $i, 'month')));
                    $rowsWin[$i]['profit'] = $adb->query_result($sqlResultWinTrade, $i, 'profit');
                    $rowsWin[$i]['loss'] = 0;
                }

                $lossTradeQuery = $provider->getLossForLiveAccountDashboard($account_no, $from_date, $to_date, $profit_loss_filter);
                $sqlResultLossTrade = $adb->pquery($lossTradeQuery, array());
                $num_rows_losstrade = $adb->num_rows($sqlResultLossTrade);
                $rowsLoss = array();
                for ($i = 0; $i < $num_rows_losstrade; $i++) {
                    $rowsLoss[$i]['x'] = date("M", strtotime($adb->query_result($sqlResultLossTrade, $i, 'month')));
                    $rowsLoss[$i]['profit'] = 0;
                    $rowsLoss[$i]['loss'] = $adb->query_result($sqlResultLossTrade, $i, 'loss');
                }
                $main_sum_array = $this->getTradeBehaviourGraphDataMapping($rowsWin, $rowsLoss, $months, $main_sum_array);
            }
            $liveaccount_dashboard_data = $main_sum_array;
        } else if ($sub_operation == 'Top5Winning') {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
            if (empty($provider)) {
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
            }

            $sql = $provider->getTop5WinningTradesForLiveAccountDashboard($account_no);
            $sqlResult = $adb->pquery($sql, array());
            $count = $adb->num_rows($sqlResult);
            $rows = array();
            for ($i = 0; $i < $count; $i++) {
                $rows[$i]['symbol'] = $adb->query_result($sqlResult, $i, 'symbol');
                $rows[$i]['close_time'] = $adb->query_result($sqlResult, $i, 'close_time');
                $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                $rows[$i]['close_price'] = $adb->query_result($sqlResult, $i, 'close_price');
                $rows[$i]['profit'] = round($adb->query_result($sqlResult, $i, 'profit'), 4);
            }
            $liveaccount_dashboard_data = $rows;
        } else if ($sub_operation == 'Top5Losing') {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
            if (empty($provider)) {
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
            }

            $sql = $provider->getTop5LossingTradesForLiveAccountDashboard($account_no);
            $sqlResult = $adb->pquery($sql, array());
            $count = $adb->num_rows($sqlResult);
            $rows = array();
            for ($i = 0; $i < $count; $i++) {
                $rows[$i]['symbol'] = $adb->query_result($sqlResult, $i, 'symbol');
                $rows[$i]['close_time'] = $adb->query_result($sqlResult, $i, 'close_time');
                $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                $rows[$i]['close_price'] = $adb->query_result($sqlResult, $i, 'close_price');
                $rows[$i]['profit'] = round($adb->query_result($sqlResult, $i, 'profit'), 4);
            }
            $liveaccount_dashboard_data = $rows;
        } else if ($sub_operation == 'OpenTrades') {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
            if (empty($provider)) {
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
            }

            $sql = $provider->getOpenTradesForLiveAccountDashboard($account_no);
            $sqlResult = $adb->pquery($sql, array());
            $num_rows = $adb->num_rows($sqlResult);
            if ($num_rows > 0) {
                $long_trades = $adb->query_result($sqlResult, 0, 'buy_count');
                $short_trades = $adb->query_result($sqlResult, 0, 'sell_count');

                $long_trades_percentage = ($long_trades * 100) / ($long_trades + $short_trades);
                $short_trades_percentage = ($short_trades * 100) / ($long_trades + $short_trades);

                if (is_nan($long_trades_percentage)) {
                    $long_trades_percentage = 0;
                }

                if (is_nan($short_trades_percentage)) {
                    $short_trades_percentage = 0;
                }

                $liveaccount_dashboard_data = array(
                    array('key' => 'long_trades', 'label' => 'CAB_LBL_LONG_TRADES', 'value' => $long_trades, 'percentage' => $long_trades_percentage),
                    array('key' => 'short_trades', 'label' => 'CAB_LBL_SHORT_TRADES', 'value' => $short_trades, 'percentage' => $short_trades_percentage),
                    array('key' => 'total_no_of_trades', 'label' => 'CAB_LBL_TOTAL_NO_OF_TRADES', 'value' => ($long_trades + $short_trades)),
                );
            } else {
                $liveaccount_dashboard_data = array(
                    array('key' => 'long_trades', 'label' => 'CAB_LBL_LONG_TRADES', 'value' => 0, 'percentage' => 0),
                    array('key' => 'short_trades', 'label' => 'CAB_LBL_SHORT_TRADES', 'value' => 0, 'percentage' => 0),
                    array('key' => 'total_no_of_trades', 'label' => 'CAB_LBL_TOTAL_NO_OF_TRADES', 'value' => 0),
                );
            }
        } else if ($sub_operation == 'CloseTrades') {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
            if (empty($provider)) {
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
            }

            $sql = $provider->getCloseTradesForLiveAccountDashboard($account_no);
            $sqlResult = $adb->pquery($sql, array());
            $num_rows = $adb->num_rows($sqlResult);

            if ($num_rows > 0) {
                $winner_trades = $adb->query_result($sqlResult, 0, 'profit_count');
                $lost_trades = $adb->query_result($sqlResult, 0, 'loss_count');
                $winner_trades_percentage = ($winner_trades * 100) / ($winner_trades + $lost_trades);
                $lost_trades_percentage = ($lost_trades * 100) / ($winner_trades + $lost_trades);

                if (is_nan($winner_trades_percentage)) {
                    $winner_trades_percentage = 0;
                }

                if (is_nan($lost_trades_percentage)) {
                    $lost_trades_percentage = 0;
                }

                $liveaccount_dashboard_data = array(
                    array('key' => 'winner_trades', 'label' => 'CAB_LBL_WINNER_TRADES', 'value' => $winner_trades, 'percentage' => $winner_trades_percentage),
                    array('key' => 'lost_trades', 'label' => 'CAB_LBL_LOST_TRADES', 'value' => $lost_trades, 'percentage' => $lost_trades_percentage),
                    array('key' => 'total_no_of_trades', 'label' => 'CAB_LBL_TOTAL_NO_OF_TRADES', 'value' => ($winner_trades + $lost_trades)),
                );
            } else {
                $liveaccount_dashboard_data = array(
                    array('key' => 'winner_trades', 'label' => 'CAB_LBL_WINNER_TRADES', 'value' => 0, 'percentage' => 0),
                    array('key' => 'lost_trades', 'label' => 'CAB_LBL_LOST_TRADES', 'value' => 0, 'percentage' => 0),
                    array('key' => 'total_no_of_trades', 'label' => 'CAB_LBL_TOTAL_NO_OF_TRADES', 'value' => 0),
                );
            }
        } else if ($sub_operation == 'ClosedTradesList') {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
            if (empty($provider)) {
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
            }

            $sql = $provider->getClosedTradesListForLiveAccountDashboard($account_no);
            $sqlResult = $adb->pquery($sql, array());
            $count = $adb->num_rows($sqlResult);
            $rows = array();
            for ($i = 0; $i < $count; $i++) {
                $rows[$i]['open_time'] = $adb->query_result($sqlResult, $i, 'open_time');
                $rows[$i]['close_time'] = $adb->query_result($sqlResult, $i, 'close_time');
                $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                $cmd = $adb->query_result($sqlResult, $i, 'cmd');
                if ($cmd == 0) {
                    $rows[$i]['type'] = 'Buy';
                } else {
                    $rows[$i]['type'] = 'Sell';
                }
                $rows[$i]['symbol'] = $adb->query_result($sqlResult, $i, 'symbol');
                $rows[$i]['open_price'] = $adb->query_result($sqlResult, $i, 'open_price');
                $rows[$i]['close_price'] = $adb->query_result($sqlResult, $i, 'close_price');
                $rows[$i]['profit'] = round($adb->query_result($sqlResult, $i, 'profit'), 4);
                $rows[$i]['is_open_trade'] = $adb->query_result($sqlResult, $i, 'is_open');
            }
            $liveaccount_dashboard_data = $rows;
        } else if ($sub_operation == 'OpenTradesList') {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
            if (empty($provider)) {
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
            }

            $sql = $provider->getOpenTradesListForLiveAccountDashboard($account_no);
            $sqlResult = $adb->pquery($sql, array());
            $count = $adb->num_rows($sqlResult);
            $rows = array();
            for ($i = 0; $i < $count; $i++) {
                $rows[$i]['open_time'] = $adb->query_result($sqlResult, $i, 'open_time');
                $rows[$i]['close_time'] = $adb->query_result($sqlResult, $i, 'close_time');
                $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                $cmd = $adb->query_result($sqlResult, $i, 'cmd');
                if ($cmd == 0) {
                    $rows[$i]['type'] = 'Buy';
                } else {
                    $rows[$i]['type'] = 'Sell';
                }

                $rows[$i]['symbol'] = $adb->query_result($sqlResult, $i, 'symbol');
                $rows[$i]['open_price'] = $adb->query_result($sqlResult, $i, 'open_price');
                $rows[$i]['close_price'] = $adb->query_result($sqlResult, $i, 'close_price');
                $rows[$i]['profit'] = round($adb->query_result($sqlResult, $i, 'profit'), 4);
                $rows[$i]['is_open_trade'] = $adb->query_result($sqlResult, $i, 'is_open');
            }
            $liveaccount_dashboard_data = $rows;
        } else if ($sub_operation == 'SymbolPerformance_old') {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
            if (empty($provider)) {
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
            }

            $sql = $provider->getSymbolPerformanceForLiveAccountDashboard($account_no);

            $sqlResult = $adb->pquery($sql, array());
            $num_rows = $adb->num_rows($sqlResult);
            $best_performance = array('symbol' => '', 'volume' => '', 'profit' => '', 'close_time' => '');
            $worst_performance = array('symbol' => '', 'volume' => '', 'profit' => '', 'close_time' => '');
            if ($num_rows > 0) {
                for ($i = 0; $i < $num_rows; $i++) {
                    $min_max_profit = $adb->query_result($sqlResult, $i, 'min_max_profit');
                    if ($min_max_profit > 0) {
                        $symbol = $adb->query_result($sqlResult, $i, 'symbol');
                        $volume = $adb->query_result($sqlResult, $i, 'volume');
                        $close_time = $adb->query_result($sqlResult, $i, 'close_time');
                        $best_performance = array('symbol' => $symbol, 'volume' => $volume, 'profit' => $min_max_profit, 'close_time' => $close_time);
                    } else {
                        $symbol = $adb->query_result($sqlResult, $i, 'symbol');
                        $volume = $adb->query_result($sqlResult, $i, 'volume');
                        $close_time = $adb->query_result($sqlResult, $i, 'close_time');
                        $worst_performance = array('symbol' => $symbol, 'volume' => $volume, 'profit' => $min_max_profit, 'close_time' => $close_time);
                    }
                }
                $liveaccount_dashboard_data = array('best_performance' => $best_performance, 'worst_performance' => $worst_performance);
            } else {
                $liveaccount_dashboard_data = array('best_performance' => $best_performance, 'worst_performance' => $worst_performance);
            }
        } else if ($sub_operation == 'SymbolPerformance') {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
            if (empty($provider)) {
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
            }

            $sql = $provider->getSymbolPerformanceForLiveAccountDashboard($account_no);
            $sqlResult = $adb->pquery($sql, array());
            $count = $adb->num_rows($sqlResult);
            $rows = array();
            for ($i = 0; $i < $count; $i++) {
                $rows[$i]['symbol'] = $adb->query_result($sqlResult, $i, 'symbol');
                //$rows[$i]['symbol_count'] = $adb->query_result($sqlResult, $i, 'symbol_count');
                $rows[$i]['sum_volume'] = $adb->query_result($sqlResult, $i, 'sum_volume');
            }
            $liveaccount_dashboard_data = $rows;
        } else if ($sub_operation == 'TradesStreak') {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
            if (empty($provider)) {
                throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
            }

            $most_effective_symbol = array('symbol' => '', 'value' => '');
            $least_effective_symbol = array('symbol' => '', 'value' => '');

            $sql = $provider->getTradesStreakForLiveAccountDashboard($account_no, 'most_and_least_effective_symbol');
            if ($sql) {
                $sqlResult = $adb->pquery($sql, array());
                $num_rows = $adb->num_rows($sqlResult);
                $most_effective_symbol_ar = array();

                if ($num_rows > 0) {
                    for ($i = 0; $i < $num_rows; $i++) {
                        $most_effective_symbol_ar[$adb->query_result($sqlResult, $i, 'symbol')] = $adb->query_result($sqlResult, $i, 'winning_ratio');
                    }
                    arsort($most_effective_symbol_ar);
                    $most_effective_symbol = array('symbol' => array_search(max($most_effective_symbol_ar), $most_effective_symbol_ar), 'value' => (int) max($most_effective_symbol_ar));

                    asort($most_effective_symbol_ar);
                    $least_effective_symbol = array('symbol' => array_search(min($most_effective_symbol_ar), $most_effective_symbol_ar), 'value' => (int) min($most_effective_symbol_ar));
                }
            }

            $longest_winning_streak = array('symbol' => '', 'value' => '');
            $longest_losing_streak = array('symbol' => '', 'value' => '');
            $sql = $provider->getTradesStreakForLiveAccountDashboard($account_no, 'longest_winning_and_losing_streak');

            if ($sql) {
                $sqlResult = $adb->pquery($sql, array());
                $num_rows = $adb->num_rows($sqlResult);
                $longest_winning_and_losing_streak = array();

                $profit_count = 0;
                $consecutive_profit = 0;
                $loss_count = 0;
                $consecutive_loss = 0;
                if ($num_rows > 0) {
                    for ($i = 0; $i < $num_rows; $i++) {
                        if ($adb->query_result($sqlResult, $i, 'profit') > 0) {
                            if ($loss_count > $consecutive_loss) {
                                $consecutive_loss = $loss_count;
                            }
                            $loss_count = 0;
                            $profit_count = $profit_count + 1;
                        } else {
                            if ($profit_count > $consecutive_profit) {
                                $consecutive_profit = $profit_count;
                            }
                            $profit_count = 0;
                            $loss_count = $loss_count + 1;
                        }
                    }
                    if ($loss_count > $consecutive_loss) {
                        $consecutive_loss = $loss_count;
                    }
                    if ($profit_count > $consecutive_profit) {
                        $consecutive_profit = $profit_count;
                    }
                    $longest_winning_streak = array('symbol' => '', 'value' => $consecutive_profit);
                    $longest_losing_streak = array('symbol' => '', 'value' => $consecutive_loss);
                }
            }
            $liveaccount_dashboard_data = array('most_effective_symbol' => $most_effective_symbol, 'least_effective_symbol' => $least_effective_symbol, 'longest_winning_streak' => $longest_winning_streak, 'longest_losing_streak' => $longest_losing_streak);
        } else {
            throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module, $portal_language), 1418);
            exit;
        }
        return $liveaccount_dashboard_data;
    }

    public function process(CustomerPortal_API_Request $request)
    {
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();

        if ($current_user) {
            $record = $this->processRetrieve($request);
            $response->setResult(array('record' => $record));
        }
        return $response;
    }

    public function getTradeBehaviourGraphDataMapping($rowsWin, $rowsLoss, $months, $main_sum_array)
    {
        //for Win trade array mapping
        foreach ($rowsWin as $k => $v) {
            if (in_array($v['x'], $months)) {
                $main_sum_array[$v['x']]['profit'] = $main_sum_array[$v['x']]['profit'] + $v['profit'];
            }
        }

        //For Loss trade array mapping
        foreach ($rowsLoss as $k => $v) {
            if (in_array($v['x'], $months)) {
                $main_sum_array[$v['x']]['loss'] = $main_sum_array[$v['x']]['loss'] + $v['loss'];
            }
        }
        return $main_sum_array;
    }

    public function getTradeBehaviourGraphDataMappingDaily($rowsWin, $rowsLoss, $days, $main_sum_array)
    {
        //for Win trade array mapping
        foreach ($rowsWin as $k => $v) {
            if (in_array($v['x'], $days)) {
                $main_sum_array[$v['x']]['profit'] = $main_sum_array[$v['x']]['profit'] + $v['profit'];
            }
        }

        //For Loss trade array mapping
        foreach ($rowsLoss as $k => $v) {
            if (in_array($v['x'], $days)) {
                $main_sum_array[$v['x']]['loss'] = $main_sum_array[$v['x']]['loss'] + $v['loss'];
            }
        }
        return $main_sum_array;
    }

    public function getLast12DaysDataSet()
    {
        $date_set = array();
        $date_set_of_from_to = array();
        $day_set = array();
        for ($i = 11; $i > -1; $i--) {
            $lastDay = date("Y-m-d", strtotime("-$i Day"));

            $last_day_of_lastday = date("dM Y", strtotime($lastDay));
            $day = date("d", strtotime($lastDay));

            $date_set[] = $last_day_of_lastday;
            $date_set_of_from_to[] = $lastDay;
            $day_set[] = $day;
        }
        return array($date_set, $date_set_of_from_to, $day_set);
    }

}
