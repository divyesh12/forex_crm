<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once('modules/ServiceProviders/ServiceProviders.php');

class CustomerPortal_Reports extends CustomerPortal_API_Abstract {

    protected $translate_module = 'CustomerPortal_Client';

    function process(CustomerPortal_API_Request $request) {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $customerId = $this->getActiveCustomer()->id;
            $contactWebserviceId = vtws_getWebserviceEntityId('Contacts', $customerId);
            $sub_operation = $request->get('sub_operation');
            $account_no = $request->get('account_no');
            $trade_type = $request->get('trade_type');
            $from_date = $request->get('from_date');
            $to_date = $request->get('to_date');
            $page = $request->get('page');
            $pageLimit = $request->get('pageLimit');
            $orderBy = $request->get('orderBy');
            $order = $request->get('order');
            $is_count = $request->get('is_count');
            $response_type = $request->get('response_type');
            $transaction_type = $request->get('transaction_type');

            if (empty($page))
                $page = 0;

            if (empty($pageLimit))
                $pageLimit = CustomerPortal_Config::$DEFAULT_PAGE_LIMIT;

            if (empty($order)) {
                $order = 'DESC';
            } else {
                if (!in_array(strtoupper($order), array("DESC", "ASC"))) {
                    throw new Exception(vtranslate('CAB_MSG_INVALID_SORTING_ORDER', $this->translate_module, $portal_language), 1412);
                    exit;
                }
            }

            if (empty($sub_operation)) {
                throw new Exception(vtranslate('CAB_SUB_OPERATION_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1413);
                exit;
            }
            if (empty($account_no)) {
                throw new Exception(vtranslate('CAB_MSG_ACCOUNT_NO_SHOULD_NOT_BE_EMPTY', 'LiveAccount', $portal_language), 1414);
                exit;
            }
            if (empty($from_date)) {
                throw new Exception(vtranslate('CAB_MSG_FROM_DATE_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1415);
                exit;
            }
            if (empty($to_date)) {
                throw new Exception(vtranslate('CAB_MSG_TO_DATE_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1416);
                exit;
            }

            $liveaccount_details = vtws_retrieve($account_no, $current_user);
            if (!empty($liveaccount_details)) {
                $account_no = $liveaccount_details['account_no'];
                $live_metatrader_type = $liveaccount_details['live_metatrader_type'];
                if ($sub_operation == 'GetTrades') {
                    $where = " WHERE 1 ";
                    $totalVolume = 0;
                    if (!empty($trade_type)) {
                        // Here table column name are different in both type

                        if (empty($orderBy)) {
                            $orderBy = ' `trades`.`ticket` '; //ticket
                        } else {
                            $orderBy = ' `trades`.`' . $orderBy . '`';
                        }
                        if ($trade_type == 'open')
                            $where .= " AND  `trades`.`open_time` >= '" . $from_date . "' AND `trades`.`open_time` <= '" . $to_date . "'";
                        if ($trade_type == 'close')
                            $where .= " AND  `trades`.`close_time` >= '" . $from_date . "' AND `trades`.`close_time` <= '" . $to_date . "'";

                        $orderby_paging_sql = sprintf(' ORDER BY %s %s LIMIT %s,%s ', $orderBy, $order, ($page * $pageLimit), $pageLimit);
                        $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);

                        if (empty($provider))
                            throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'Payments', $portal_language), 1416);

                        $sql = $provider->getTradesForReport($trade_type, $account_no);

                        $where = $where . " AND l.account_no=" . $account_no . " AND c.contactid=" . $customerId . " AND e.deleted = 0";

                        $sql = "SELECT trades.* FROM (" . $sql . ") AS trades  
                        INNER JOIN `vtiger_liveaccount` AS `l` ON  `trades`.`LOGIN` = `l`.`account_no`
                        INNER JOIN `vtiger_crmentity` AS `e` ON  `e`.`crmid` = `l`.`liveaccountid`
                        INNER JOIN `vtiger_contactdetails` AS `c` ON `l`.`contactid` = `c`.`contactid`" . $where;
                        if (isset($is_count) && $is_count == '0') {
                            //if count 0 then it will skip the count query execution
                        } else {
                            //Count data                                                                 
                            $sqlCountResult = $adb->pquery($sql, array());
                            $count = $adb->num_rows($sqlCountResult);
                            //End
                            
                            /*Calculate total volume*/
                            $totalVolumeSql = "SELECT SUM(trades.volume) total_volume_count FROM (" . $sql . ") AS trades  
                            INNER JOIN `vtiger_liveaccount` AS `l` ON  `trades`.`LOGIN` = `l`.`account_no`
                            INNER JOIN `vtiger_crmentity` AS `e` ON  `e`.`crmid` = `l`.`liveaccountid`
                            INNER JOIN `vtiger_contactdetails` AS `c` ON `l`.`contactid` = `c`.`contactid`" . $where;
                            $totalVolumeResult = $adb->pquery($totalVolumeSql, array());
                            $totalVolume = $adb->query_result($totalVolumeResult, 0, 'total_volume_count');
                            /*Calculate total volume*/
                        }
                        $sql .= $orderby_paging_sql;
                        $sqlResult = $adb->pquery($sql, array());
                        $numRow = $adb->num_rows($sqlResult);
                        $rows = array();
                        for ($i = 0; $i < $numRow; $i++) {
                            $rows[$i]['login'] = $adb->query_result($sqlResult, $i, 'login');
                            $rows[$i]['ticket'] = $adb->query_result($sqlResult, $i, 'ticket');
                            $rows[$i]['symbol'] = $adb->query_result($sqlResult, $i, 'symbol');
                            $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                            switch ($adb->query_result($sqlResult, $i, 'cmd')) {
                                case 0:
                                    $rows[$i]['cmd'] = 'Buy';
                                    break;
                                case 1:
                                    $rows[$i]['cmd'] = 'Sell';
                                    break;
                            }
                            $rows[$i]['open_time'] = $adb->query_result($sqlResult, $i, 'open_time');
                            $rows[$i]['open_price'] = $adb->query_result($sqlResult, $i, 'open_price');
                            if ($trade_type == 'close') {
                                $rows[$i]['close_time'] = $adb->query_result($sqlResult, $i, 'close_time');
                                $rows[$i]['close_price'] = $adb->query_result($sqlResult, $i, 'close_price');
                            } else {
                                $rows[$i]['close_time'] = "-";
                                $rows[$i]['close_price'] = "-";
                            }
                            $rows[$i]['tp'] = $adb->query_result($sqlResult, $i, 'tp'); //Take Profit
                            $rows[$i]['sl'] = $adb->query_result($sqlResult, $i, 'sl'); // Stop Loss
                            $rows[$i]['commission'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'commission'));
                            $rows[$i]['swaps'] = $adb->query_result($sqlResult, $i, 'swaps');
                            $rows[$i]['profit'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'profit'));
                        }
                        if (!empty($response_type) && $response_type == 'List') {
                            //Note: In future we need to use this cabinet too, as of now using Mobile App                            
                            $response->addToResult('records', $rows);
                        } else {
                            $response->setResult($rows);
                        }
                        $response->addToResult('count', $count);
                        $response->addToResult('total_volume', $totalVolume);
                    } else {
                        throw new Exception(vtranslate('CAB_MSG_TRADE_TYPE_IS_EMPTY', 'LiveAccount', $portal_language), 1417);
                        exit;
                    }
                } else if ($sub_operation == 'GetTransactions') {
                    $where = "";
                    $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
                    if (empty($provider))
                        throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'Payments', $portal_language), 1416);

                    $where .= $provider->getTranTimeConditions($from_date, $to_date);
//                    if (empty($orderBy)) {
                        $orderBy = $provider->getTranOrderByConditions(true);
//                    }
//                    $where .= " AND  `trades`.`close_time` >= '" . $from_date . "' AND `trades`.`close_time` <= '" . $to_date . "'";

                    //Check Transaction Type IN or OUT, If All the transacion type will be blank
                    if (!empty($transaction_type)) {
                        if ($transaction_type == 'IN') {
                            $where .= " AND trades.profit > 0";
                        }
                        if ($transaction_type == 'OUT') {
                            $where .= " AND trades.profit < 0";
                        }
                    }
                    //End

                    $orderby_paging_sql = sprintf(' ORDER BY %s %s LIMIT %s,%s ', $orderBy, $order, ($page * $pageLimit), $pageLimit);

                    $sql = $provider->getTransactionsForReport($account_no, $from_date, $to_date);

                    $where = $where . " AND l.account_no=" . $account_no . " AND c.contactid=" . $customerId . " AND e.deleted = 0";

                    $sqlProfitTotal = "SELECT sum(trades.profit) AS profitTotal FROM (" . $sql . ") AS trades  
                        INNER JOIN `vtiger_liveaccount` AS `l` ON  `trades`.`login` = `l`.`account_no`
                        INNER JOIN `vtiger_crmentity` AS `e` ON  `e`.`crmid` = `l`.`liveaccountid`
                        INNER JOIN `vtiger_contactdetails` AS `c` ON `l`.`contactid` = `c`.`contactid`" . $where . " Limit 1";

                    $profitTotal = 0;
                    $sqlProfitResult = $adb->pquery($sqlProfitTotal, array());
                    if ($adb->num_rows($sqlProfitResult) > 0) {
                        $profitTotal = $adb->query_result($sqlProfitResult, 'profitTotal');
                    }

                    $sql = "SELECT trades.* FROM (" . $sql . ") AS trades  
                        INNER JOIN `vtiger_liveaccount` AS `l` ON  `trades`.`login` = `l`.`account_no`
                        INNER JOIN `vtiger_crmentity` AS `e` ON  `e`.`crmid` = `l`.`liveaccountid`
                        INNER JOIN `vtiger_contactdetails` AS `c` ON `l`.`contactid` = `c`.`contactid`" . $where;

                    if (isset($is_count) && $is_count == '0') {
                        //if count 0 then it will skip the count query execution
                    } else {
                        //Count row
                        $sqlCountResult = $adb->pquery($sql, array());
                        $count = $adb->num_rows($sqlCountResult);
                        //End
                    }
                    $sql = $sql . $where . $orderby_paging_sql;

                    $sqlResult = $adb->pquery($sql, array());
                    $numRow = $adb->num_rows($sqlResult);
                    $rows = array();
                    $i = 0;
                    while ($row_result = $adb->fetchByAssoc($sqlResult))
                    {
                        $providerDetails = $provider->getProviderSpecificData($row_result);
                        $rows[$i]['login'] = $row_result['login'];
                        $rows[$i]['ticket'] = $providerDetails['ticket_no'];
                        $rows[$i]['close_time'] = $providerDetails['close_time'];
                        $rows[$i]['profit'] = number_format($row_result['profit'], 2);
                        $rows[$i]['comment'] = $row_result['comment'];
                        $i++;
                    }
                    /*for ($i = 0; $i < $numRow; $i++) {
                        $rows[$i]['login'] = $adb->query_result($sqlResult, $i, 'login');
                        $rows[$i]['ticket'] = $adb->query_result($sqlResult, $i, 'ticket');
                        $rows[$i]['close_time'] = $adb->query_result($sqlResult, $i, 'close_time');
                        // $rows[$i]['profit'] = $adb->query_result($sqlResult, $i, 'profit');
                        $rows[$i]['profit'] = number_format($adb->query_result($sqlResult, $i, 'profit'), 2);
                        $rows[$i]['comment'] = $adb->query_result($sqlResult, $i, 'comment');
                    }*/

                    if (!empty($response_type) && $response_type == 'List') {
                        //Note: In future we need to use this cabinet too, as of now using Mobile App                            
                        $response->addToResult('records', $rows);
                    } else {
                        $response->setResult($rows);
                    }
                    $response->addToResult('count', $count);
                    $response->addToResult('summarize_total', $profitTotal);
                } else {
                    throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module, $portal_language), 1418);
                    exit;
                }
            } else {
                throw new Exception(vtranslate('CAB_MSG_ACCOUNT_NUMBER_DOES_NOT_FOUND', 'LiveAccount', $portal_language), 1419);
                exit;
            }
            return $response;
        }
    }

}
