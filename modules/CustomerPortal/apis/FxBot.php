<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_FxBot extends CustomerPortal_API_Abstract {

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();


        if ($current_user) {

            //Custom parameter added by Sandeep Thakkar 13-09-2019
            $op = $request->get('op');
            $account_no = $request->get('account_no');
            $op_orderBy = $request->get('op_orderBy');
            $op_order = $request->get('op_order');
            //End


            $customerId = $this->getActiveCustomer()->id;
            $contactWebserviceId = vtws_getWebserviceEntityId('Contacts', $customerId);
            $accountId = $this->getParent($contactWebserviceId);
            $mode = $request->get('mode');
            $module = $request->get('module');
            $moduleLabel = $request->get('moduleLabel');
            $fieldsArray = $request->get('fields');
            $orderBy = $request->get('orderBy');
            $order = $request->get('order');
            //Custom paramter for count the data or not for export to excel
            $is_count = $request->get('is_count');
            //End
            $activeFields = CustomerPortal_Utils::getActiveFields($module);


            //Custom code added for order by and order custom field by Sandeep Thakkar 16-09-219           
            if (empty($op_order)) {
                $op_order = 'DESC';
            } else {
                if (!in_array(strtoupper($op_order), array("DESC", "ASC"))) {
                    throw new Exception("Invalid sorting order", 1412);
                    exit;
                }
            }
            //End

            if (empty($orderBy)) {
                $orderBy = 'modifiedtime';
            } else {
                if (!in_array($orderBy, $activeFields)) {
                    throw new Exception("sort by $orderBy not allowed", 1412);
                    exit;
                }
            }

            if (empty($order)) {
                $order = 'DESC';
            } else {
                if (!in_array(strtoupper($order), array("DESC", "ASC"))) {
                    throw new Exception("Invalid sorting order", 1412);
                    exit;
                }
            }
            $fieldsArray = Zend_Json::decode($fieldsArray);
            $groupConditionsBy = $request->get('groupConditions');
            $page = $request->get('page');
            if (empty($page))
                $page = 0;

            $pageLimit = $request->get('pageLimit');

            if (empty($pageLimit))
                $pageLimit = CustomerPortal_Config::$DEFAULT_PAGE_LIMIT;

            if (empty($groupConditionsBy))
                $groupConditionsBy = 'AND';

            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception("Module not accessible", 1412);
                exit;
            }

            if (empty($mode)) {
                $mode = CustomerPortal_Settings_Utils::getDefaultMode($module);
            }
            $count = null;

            if ($fieldsArray !== null) {
                foreach ($fieldsArray as $key => $value) {
                    if (!in_array($key, $activeFields)) {
                        throw new Exception($key . " is not accessible.", 1412);
                        exit;
                    }
                }
            }
            $fields = implode(',', $activeFields);


            //Check the Liveaccount available or not
            global $adb;
            $live_sql = "SELECT account_no FROM `vtiger_liveaccount` WHERE `contactid` = ? AND `account_no` = ? ";
            $live_res = $adb->pquery($live_sql, array($customerId, $account_no));
            if ($adb->num_rows($live_res) > 0) {
                
            } else {
                throw new Exception("No records found", 1412);
                exit;
            }
            //End

            if ($module == 'LiveAccount' && $op == 'FetchBalance') {
                global $adb;
                //We will take MargeenFree as a balance, Because balance column update after closed the trade only
                //So MarginFree is real balance, which frequenctly changing based on trade profit/loss
                //$sql = "SELECT `MarginFree` AS balance FROM `mt5_accounts` INNER JOIN `vtiger_liveaccount` ON `vtiger_liveaccount`.`account_no` = `mt5_accounts`.`Login` WHERE `vtiger_liveaccount`.`contactid` = ? AND `vtiger_liveaccount`.`account_no` = ?";
                //`MarginFree`
                $sql = "SELECT `Balance` as `balance`, `MarginFree` as `margin_free`, `Equity` AS `equity` FROM `mt5_accounts` INNER JOIN `vtiger_liveaccount` "
                        . "ON `vtiger_liveaccount`.`account_no` = `mt5_accounts`.`Login` "
                        . "WHERE `vtiger_liveaccount`.`contactid` = ? AND `vtiger_liveaccount`.`account_no` = ?";
                $res = $adb->pquery($sql, array($customerId, $account_no));
                $count = (string) $adb->num_rows($res);
                if ($adb->num_rows($res) > 0) {
                    $balance = $adb->query_result($res, 0, 'balance');
                    $margin_free = $adb->query_result($res, 0, 'margin_free');
                    $equity = $adb->query_result($res, 0, 'equity');
                    $result = array('balance' => $balance, 'margin_free' => $margin_free, 'equity' => $equity);
                }
            } else if ($module == 'LiveAccount' && $op == 'FetchOpenTrades') {
                global $adb;
                if (empty($op_orderBy)) {
                    $op_orderBy = 'position';
                }
                $limitClause = sprintf('ORDER BY %s %s LIMIT %s,%s', "`o`.`" . $op_orderBy . "`", $op_order, ($page * $pageLimit), $pageLimit);

                //Volume will be divided by 10000 as per prvious syntax of MT4.                
                /* $sql = "SELECT `b`.`Order` AS `TICKET`,`b`.`Login` AS `LOGIN`,REPLACE(`b`.`Symbol`,' ','') "
                  . "AS `SYMBOL`,`b`.`Digits` AS `DIGITS`,`b`.`Action` AS `CMD`,(`o`.`Volume` / 10000) "
                  . "AS `VOLUME`,`b`.`Time` AS `OPEN_TIME`,`b`.`Price` AS `OPEN_PRICE`,'0' AS `SL`,'0' "
                  . "AS `TP`,'1970-01-01 00:00:00' AS `CLOSE_TIME`,'0000-00-00 00:00:00' "
                  . "AS `EXPIRATION`,'0' AS `REASON`,'0' AS `CONV_RATE1`,'0' AS `CONV_RATE2`,"
                  . "`b`.`Commission` AS `COMMISSION`,'0' AS `COMMISSION_AGENT`,`o`.`Storage` "
                  . "AS `SWAPS`,'0' AS `CLOSE_PRICE`,`o`.`Profit` AS `PROFIT`,'0' AS `TAXES`,`o`.`Comment` "
                  . "AS `COMMENT`,`o`.`Position` AS `POSITIONID`,'0' AS `INTERNAL_ID`,'0' AS `MARGIN_RATE`,"
                  . "`o`.`Timestamp` AS `TIMESTAMP`,'0' AS `MAGIC`,'0' AS `GW_VOLUME`,'0' "
                  . "AS `GW_OPEN_PRICE`,'0' AS `GW_CLOSE_PRICE`,'0000-00-00 00:00:00' AS `MODIFY_TIME` "
                  . "FROM `mt5_deals` `b` JOIN `mt5_positions` `o` ON `b`.`PositionID` = `o`.`Position` "
                  . "INNER JOIN vtiger_liveaccount ON `vtiger_liveaccount`.`account_no` = `o`.`Login` "
                  . "WHERE ((`o`.`RateProfit` <> 0) AND ((`o`.`Action` = 1) OR (`o`.`Action` = 0))) "
                  . "AND `vtiger_liveaccount`.`contactid` = ? AND `o`.`Login` = ? "; */
                $sql = "SELECT `o`.`Position` AS `TICKET`,`o`.`Login` AS `LOGIN`,REPLACE(`o`.`Symbol`,' ','') AS `SYMBOL`, "
                        . "`o`.`Digits` AS `DIGITS`,`o`.`Action` AS `CMD`,(`o`.`Volume` / 10000) AS `VOLUME`,"
                        . "`o`.`TimeCreate` AS `OPEN_TIME`,`o`.`PriceOpen` AS `OPEN_PRICE`, `o`.`PriceSL` AS `SL`,"
                        . "`o`.`PriceTP` AS `TP`,'1970-01-01 00:00:00' AS `CLOSE_TIME`, `o`.`Storage` AS `SWAPS`, "
                        . "`o`.`Profit` AS `PROFIT`, `o`.`Comment` AS `COMMENT`,  `o`.`Position` AS `POSITIONID`, "
                        . "`o`.`Timestamp` AS `TIMESTAMP` FROM `mt5_positions` `o` "
                        . "INNER JOIN vtiger_liveaccount `la` ON `la`.`account_no` = `o`.`Login` "
                        . "WHERE `o`.`RateProfit` <> 0 AND ((`o`.`Action` = 1) OR (`o`.`Action` = 0)) "
                        . "AND `la`.`contactid` = ? AND `o`.`Login` = ? ";
                //Count Query
                $res_count = $adb->pquery($sql, array($customerId, $account_no));
                $count = (string) $adb->num_rows($res_count);
                //End
                $sql = $sql . $limitClause;
                $res = $adb->pquery($sql, array($customerId, $account_no));
                $num_rows = $adb->num_rows($res);
                if ($num_rows > 0) {
                    for ($i = 0; $i < $num_rows; $i++) {
                        $result[$i] = array(
                            'ticket' => $adb->query_result($res, $i, 'ticket'),
                            'login' => $adb->query_result($res, $i, 'login'),
                            'symbol' => $adb->query_result($res, $i, 'symbol'),
                            'digits' => $adb->query_result($res, $i, 'digits'),
                            'cmd' => $adb->query_result($res, $i, 'cmd'),
                            'volume' => $adb->query_result($res, $i, 'volume'),
                            'open_time' => $adb->query_result($res, $i, 'open_time'),
                            'open_price' => $adb->query_result($res, $i, 'open_price'),
                            'sl' => $adb->query_result($res, $i, 'sl'),
                            'tp' => $adb->query_result($res, $i, 'tp'),
                            'close_time' => $adb->query_result($res, $i, 'close_time'),
                            'swaps' => $adb->query_result($res, $i, 'swaps'),
                            'profit' => $adb->query_result($res, $i, 'profit'),
                            'comment' => $adb->query_result($res, $i, 'comment'),
                            'positionid' => $adb->query_result($res, $i, 'positionid'),
                            'timestamp' => $adb->query_result($res, $i, 'timestamp')
                        );
                    }
                } else {
                    $result = array();
                }
            } else if ($module == 'LiveAccount' && $op == 'FetchCloseTrades') {
                global $adb;
                if (empty($op_orderBy)) {
                    $op_orderBy = 'Order';
                }
                $limitClause = sprintf('ORDER BY %s %s LIMIT %s,%s', "`b`.`" . $op_orderBy . "`", $op_order, ($page * $pageLimit), $pageLimit);
                $sql = "SELECT `j`.`Order` AS `TICKET`,`b`.`Login` AS `LOGIN`,REPLACE(`b`.`Symbol`,' ','') "
                        . "AS `SYMBOL`,`b`.`Digits` AS `DIGITS`,`b`.`Action` AS `CMD`,(`j`.`Volume` / 10000) "
                        . "AS `VOLUME`,`b`.`Time` AS `OPEN_TIME`,`b`.`Price` AS `OPEN_PRICE`,'0' "
                        . "AS `SL`,'0' AS `TP`,`j`.`Time` AS `CLOSE_TIME`,'0000-00-00 00:00:00' "
                        . "AS `EXPIRATION`,'0' AS `REASON`,'0' AS `CONV_RATE1`,'0' AS `CONV_RATE2`,"
                        . "`j`.`Commission` AS `COMMISSION`,'0' AS `COMMISSION_AGENT`,`j`.`Storage` "
                        . "AS `SWAPS`,`j`.`Price` AS `CLOSE_PRICE`,`j`.`Profit` AS `PROFIT`,'0' AS `TAXES`,"
                        . "`j`.`Comment` AS `COMMENT`,`j`.`PositionID` AS `POSITIONID`,'0' AS `INTERNAL_ID`,"
                        . "'0' AS `MARGIN_RATE`,`j`.`Timestamp` AS `TIMESTAMP`,'0' AS `MAGIC`,'0' AS `GW_VOLUME`,"
                        . "'0' AS `GW_OPEN_PRICE`,'0' AS `GW_CLOSE_PRICE`,'0000-00-00 00:00:00' AS `MODIFY_TIME` "
                        . "FROM `mt5_deals` `b` JOIN `mt5_deals` `j` ON `b`.`PositionID` = `j`.`PositionID` "
                        . "INNER JOIN vtiger_liveaccount ON `vtiger_liveaccount`.`account_no` = `b`.`Login` "
                        . "WHERE ((`b`.`RateProfit` = 0) AND ((`b`.`Action` = 1) OR (`b`.`Action` = 0)) AND "
                        . "(`j`.`RateProfit` <> 0) AND ((`j`.`Action` = 1) OR (`j`.`Action` = 0))) AND "
                        . "`vtiger_liveaccount`.`contactid` = ? AND `b`.`Login` = ? ";

                //Count Query
                $res_count = $adb->pquery($sql, array($customerId, $account_no));
                $count = (string) $adb->num_rows($res_count);
                //End
                $sql = $sql . $limitClause;
                $res = $adb->pquery($sql, array($customerId, $account_no));

                $num_rows = $adb->num_rows($res);
                if ($num_rows > 0) {
                    for ($i = 0; $i < $num_rows; $i++) {
                        $result[$i] = array(
                            'ticket' => $adb->query_result($res, $i, 'ticket'),
                            'login' => $adb->query_result($res, $i, 'login'),
                            'symbol' => $adb->query_result($res, $i, 'symbol'),
                            'digits' => $adb->query_result($res, $i, 'digits'),
                            'cmd' => $adb->query_result($res, $i, 'cmd'),
                            'volume' => $adb->query_result($res, $i, 'volume'),
                            'open_time' => $adb->query_result($res, $i, 'open_time'),
                            'open_price' => $adb->query_result($res, $i, 'open_price'),
                            'sl' => $adb->query_result($res, $i, 'sl'),
                            'tp' => $adb->query_result($res, $i, 'tp'),
                            'close_time' => $adb->query_result($res, $i, 'close_time'),
                            'expiration' => $adb->query_result($res, $i, 'expiration'),
                            'reason' => $adb->query_result($res, $i, 'reason'),
                            'conv_rate1' => $adb->query_result($res, $i, 'conv_rate1'),
                            'conv_rate2' => $adb->query_result($res, $i, 'conv_rate2'),
                            'commission' => $adb->query_result($res, $i, 'commission'),
                            'commission_agent' => $adb->query_result($res, $i, 'commission_agent'),
                            'swaps' => $adb->query_result($res, $i, 'swaps'),
                            'close_price' => $adb->query_result($res, $i, 'close_price'),
                            'profit' => $adb->query_result($res, $i, 'profit'),
                            'taxes' => $adb->query_result($res, $i, 'taxes'),
                            'comment' => $adb->query_result($res, $i, 'comment'),
                            'positionid' => $adb->query_result($res, $i, 'positionid'),
                            'internal_id' => $adb->query_result($res, $i, 'internal_id'),
                            'margin_rate' => $adb->query_result($res, $i, 'margin_rate'),
                            'timestamp' => $adb->query_result($res, $i, 'timestamp'),
                            'magic' => $adb->query_result($res, $i, 'magic'),
                            'gw_volume' => $adb->query_result($res, $i, 'gw_volume'),
                            'gw_open_price' => $adb->query_result($res, $i, 'gw_open_price'),
                            'gw_close_price' => $adb->query_result($res, $i, 'gw_close_price'),
                            'modify_time' => $adb->query_result($res, $i, 'modify_time')
                        );
                    }
                } else {
                    $result = array();
                }
            } else if ($module == 'Contacts') {
                $result = vtws_query(sprintf("SELECT %s FROM %s WHERE id='%s';", $fields, $module, $contactWebserviceId), $current_user);
            } else if ($module == 'Accounts') {
                if (!empty($accountId))
                    $result = vtws_query(sprintf("SELECT %s FROM %s WHERE id='%s';", $fields, $module, $accountId), $current_user);
            } else {
                $relatedId = null;
                $defaultMode = CustomerPortal_Settings_Utils::getDefaultMode($module);
                if (!empty($fieldsArray)) {
                    $countSql = sprintf('SELECT count(*) FROM %s WHERE ', $module);
                    $sql = sprintf('SELECT %s FROM %s WHERE ', $fields, $module);

                    foreach ($fieldsArray as $key => $value) {
                        $countSql .= $key . '=\'' . $value . "' " . $groupConditionsBy . " ";
                        $sql .= $key . '=\'' . $value . "' " . $groupConditionsBy . " ";
                    }

                    $countSql = CustomerPortal_Utils::str_replace_last($groupConditionsBy, '', $countSql);
                    $sql = CustomerPortal_Utils::str_replace_last($groupConditionsBy, '', $sql);
                } else {
                    $countSql = sprintf('SELECT count(*) FROM %s', $module);
                    $sql = sprintf('SELECT %s FROM %s', $fields, $module);
                }
                if ($mode == 'mine') {
                    $relatedId = $contactWebserviceId;
                    if (isset($is_count) && $is_count == '0') {
                        //if count 0 then it will skip the count query execution
                    } else {
                        $countResult = vtws_query_related($countSql, $relatedId, $moduleLabel, $current_user);
                        $count = $countResult[0]['count'];
                    }

                    $limitClause = sprintf('ORDER BY %s %s LIMIT %s,%s', $orderBy, $order, ($page * $pageLimit), $pageLimit);
                    $result = vtws_query_related($sql, $relatedId, $moduleLabel, $current_user, $limitClause);
                } else if ($mode == 'all') {
                    if (in_array($module, array('Products', 'Services'))) {
                        $countSql = sprintf('SELECT count(*) FROM %s;', $module);
                        $sql = sprintf('SELECT %s FROM %s', $fields, $module);
                        $limitClause = sprintf('ORDER BY %s %s LIMIT %s,%s;', $orderBy, $order, ($page * $pageLimit), $pageLimit);
                        $sql = $sql . ' ' . $limitClause;
                        $result = vtws_query($sql, $current_user);
                        if (isset($is_count) && $is_count == '0') {
                            //if count 0 then it will skip the count query execution
                        } else {
                            $countResult = vtws_query($countSql, $current_user);
                            $count = $countResult[0]['count'];
                        }
                    } else {
                        if (!empty($accountId)) {
                            if ($defaultMode == 'all')
                                $relatedId = $accountId;
                            else
                                $relatedId = $contactWebserviceId;
                        }
                        else {
                            $relatedId = $contactWebserviceId;
                        }

                        if (isset($is_count) && $is_count == '0') {
                            //if count 0 then it will skip the count query execution
                        } else {
                            $countResult = vtws_query_related($countSql, $relatedId, $moduleLabel, $current_user);
                            $count = $countResult[0]['count'];
                        }

                        $limitClause = sprintf('ORDER BY %s %s LIMIT %s,%s', $orderBy, $order, ($page * $pageLimit), $pageLimit);
                        $result = vtws_query_related($sql, $relatedId, $moduleLabel, $current_user, $limitClause);
                    }
                }
            }


            foreach ($result as $key => $recordValues) {
                $result[$key] = CustomerPortal_Utils::resolveRecordValues($recordValues);
            }

            $response->setResult($result);
            $response->addToResult('count', $count);
            return $response;
        }
    }

}
