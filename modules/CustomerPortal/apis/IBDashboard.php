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

class CustomerPortal_IBDashboard extends CustomerPortal_API_Abstract {

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
            //Widget 1
            if ($sub_operation == 'Widget1_IBStatestics') {
                //Initializtions
                $balance = 0.00;
                $total_commission = 0.00;
                $total_volume = 0.00;
                $total_sub_IB = 0;
                $total_clients = 0;
                //End

                $check_max_level = " AND findIBLevel(REPLACE(`c`.`ib_hierarchy`,'" . $ib_hierarchy . "','')) <= " . $max_ib_level;

                $where = " WHERE parent_contactid = " . $customerId; // . " AND child_contactid != " . $customerId;

                //Balance only in which withdrawal Pending commission condition                
                $sqlBalance = "SELECT SUM(commission_amount) AS total_commission_amount FROM tradescommission "
                        . $where . " AND `commission_withdraw_status` = 0";
                $sqlResult = $adb->pquery($sqlBalance, array());
                if ($adb->num_rows($sqlResult) > 0) {
                    $balance = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, 0, 'total_commission_amount'));
                }

                //Total Commission and volume with all status
                $ib_hierarchy = $contact['ib_hierarchy'];
                $where_with_child = " WHERE parent_contactid = " . $customerId . " AND child_contactid IN 
                (SELECT contactid FROM vtiger_contactdetails WHERE ib_hierarchy LIKE '" . $ib_hierarchy . "%')";

                $sql = "SELECT sum(commission_amount) as total_commission_amount, sum(volume) as total_volume FROM anl_comm_child " . $where_with_child;
                
                $sqlResult = $adb->pquery($sql, array());
                if ($adb->num_rows($sqlResult) > 0) {
                    // $total_commission = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, 0, 'total_commission_amount'));
                    $total_volume = (float) $adb->query_result($sqlResult, 0, 'total_volume');
                }

                // Fetch Total Commission 
                $sqlEarnComm = "SELECT SUM(commission_amount) AS total_commission_amount FROM tradescommission " . $where . " AND `commission_withdraw_status` = 1";
                $sqlResult = $adb->pquery($sqlEarnComm, array());
                if ($adb->num_rows($sqlResult) > 0) {
                    $total_commission = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, 0, 'total_commission_amount'));
                }

                //Total Sub IB: Total no. of Associated Accounts having IB Status = Approved
                //All child contact ids                
                $with_approved = ' AND `c`.record_status = "Approved"';
                $total_clients_sql = 'SELECT COUNT(1) FROM `vtiger_contactdetails` AS c INNER JOIN `vtiger_crmentity` AS ce ON c.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0  AND `c`.`contactid` !=  ' . $customerId . ' AND `c`.`ib_hierarchy` LIKE "' . $ib_hierarchy . '%"' . $check_max_level;
                $total_sub_ib_sql = 'SELECT COUNT(1) FROM `vtiger_contactdetails` AS c INNER JOIN `vtiger_crmentity` AS ce ON c.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0  AND `c`.`contactid` !=  ' . $customerId . ' AND `c`.`ib_hierarchy` LIKE "' . $ib_hierarchy . '%"' . $with_approved . $check_max_level;
                $sql = "SELECT (" . $total_clients_sql . ") AS `total_clients`, (" . $total_sub_ib_sql . ") AS `total_sub_ib`";
                $sqlResult = $adb->pquery($sql, array());
                if ($adb->num_rows($sqlResult) > 0) {
                    $total_sub_IB = $adb->query_result($sqlResult, 0, 'total_sub_ib');
                    $total_clients = $adb->query_result($sqlResult, 0, 'total_clients');
                }
                $response->addToResult('records', array('balance' => $balance, 'total_commission' => $total_commission, 'total_volume' => $total_volume, 'total_sub_ib' => $total_sub_IB, 'total_clients' => $total_clients));
            } else if ($sub_operation == 'Widget2_IB_Analytics') { //Widget 2
                $filter = $request->get('filter');
                if (empty($filter))
                    $filter = 'Monthly';
                if ($filter == 'Monthly') {
                    $from_date = date("Y-m-01", strtotime(date('Y-m-d') . " -11 months"));
                    $to_date = date("Y-m-d");

                    $where = " AND `close_time` >= '" . $from_date . "' AND `close_time` <= '" . $to_date . "'";

                    // This is for y-axis
                    $where .= ' AND parent_contactid = ' . $customerId;
                    $where .= ' AND child_contactid != ' . $customerId;


                    $sql = "SELECT  `close_time`, SUM(`commission_amount`) AS commission_amount,
SUM(`volume`) AS volume FROM anl_comm_child WHERE  1 " . $where . " 
GROUP BY MONTH(`close_time`) ORDER BY DATE_FORMAT(`close_time`,'%Y%m') ASC LIMIT 12";
                    $sqlResult = $adb->pquery($sql, array());
                    $numRow = $adb->num_rows($sqlResult);
                    $rows = array();
                    for ($i = 0; $i < $numRow; $i++) {
                        $rows[$i]['month_format'] = date("M Y", strtotime($adb->query_result($sqlResult, $i, 'close_time')));
                        $rows[$i]['close_time'] = $adb->query_result($sqlResult, $i, 'close_time');
                        $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                        $rows[$i]['commission_amount'] = CustomerPortal_Utils::setNumberFormatWithoutCommaSeparater($adb->query_result($sqlResult, $i, 'commission_amount'));
                    }
                    $y = array();
                    $no_of_lots = array();
                    // This is for x-axis
                    for ($i = 11; $i > -1; $i--) {
                        $months[] = date("M Y", strtotime(date('Y-m-01') . " -$i months"));
                    }

                    $x = $months; //array_reverse($months);                    
                    $monthly_data = array();
                    $flag = 0;
                    for ($j = 0; $j < count($x); $j++) {
                        for ($i = 0; $i < count($rows); $i++) {
                            if ($x[$j] == $rows[$i]['month_format']) {
                                $monthly_data[$j] = array('x' => $x[$j], 'y' => $rows[$i]['commission_amount'], 'volume' => $rows[$i]['volume'], 'commission_amount' => $rows[$i]['commission_amount']);
                                $flag = 1;
                                break;
                            } else {
                                $flag = 0;
                            }
                        }
                        if ($flag == 0) {
                            $monthly_data[$j] = array('x' => $x[$j], 'y' => 0.00, 'volume' => 0.00, 'commission_amount' => 0.0000);
                        }
                    }
                    $response->addToResult('records', $monthly_data);
                }
                if ($filter == 'Weekly') {
                    //*Note: Date is defined as a starting date of week. And week consider as last 7 days from current date

                    $data_set = $this->getLast12WeeksDataSet();
                    $weekly = array();
                    for ($i = 0; $i < count($data_set[1]); $i++) {
                        $from_date = explode('AND', $data_set[1][$i])[0];
                        $to_date = explode('AND', $data_set[1][$i])[1];
                        $where = " AND DATE(`close_time`) >= '" . $from_date . "' AND DATE(`close_time`) <= '" . $to_date . "'";
                        // This is for y-axis
                        $where .= ' AND parent_contactid = ' . $customerId;
                        $where .= ' AND child_contactid != ' . $customerId;

                        $sql = "SELECT sum(volume) as volume, sum(commission_amount) as commission_amount FROM `tradescommission` WHERE 1 " . $where;
                        $sqlResult = $adb->pquery($sql, array());
                        $numRow = $adb->num_rows($sqlResult);
                        if ($numRow > 0) {
                            $volume = (float) $adb->query_result($sqlResult, 0, 'volume');
                            $commission_amount = CustomerPortal_Utils::setNumberFormatWithoutCommaSeparater($adb->query_result($sqlResult, 0, 'commission_amount'));
                            $weekly[] = array('x' => $data_set[0][$i], 'y' => $commission_amount, 'volume' => $volume, 'commission_amount' => $commission_amount);
                        } else {
                            $weekly[] = array('x' => $data_set[0][$i], 'y' => 0.00, 'volume' => 0.00, 'commission_amount' => 0.00);
                        }
                    }
                    $response->addToResult('records', $weekly);
                }

                if ($filter == 'Daily') {
                    $data_set = $this->getLast12DaysDataSet();
                    $daily = array();

                    $from_date = date("Y-m-d", strtotime("-11 Day"));
                    $to_date = date("Y-m-d");
                    $where = " AND DATE(`close_time`) >= '" . $from_date . "' AND DATE(`close_time`) <= '" . $to_date . "'";

                    // This is for y-axis
                    $where .= ' AND parent_contactid = ' . $customerId;
                    $where .= ' AND child_contactid != ' . $customerId;

                    $sql = "SELECT DAY(`close_time`) AS `day`, SUM(`commission_amount`) AS `commission_amount`,
SUM(`volume`) AS `volume` FROM anl_comm_child WHERE 1 " . $where . " GROUP BY DAY(`close_time`) ORDER BY DAY(`close_time`) ASC LIMIT 12";
                    $sqlResult = $adb->pquery($sql, array());
                    $numRow = $adb->num_rows($sqlResult);
                    $rows = array();
                    if ($numRow > 0) {
                        for ($i = 0; $i < $numRow; $i++) {
                            $rows[$i]['day'] = $adb->query_result($sqlResult, $i, 'day');
                            $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                            $rows[$i]['commission_amount'] = CustomerPortal_Utils::setNumberFormatWithoutCommaSeparater($adb->query_result($sqlResult, $i, 'commission_amount'));
                        }
                    } else {
                        foreach ($data_set[0] as $key => $value) {
                            $daily[] = array('x' => $value, 'y' => 0.00, 'volume' => 0.00, 'commission_amount' => 0.00);
                        }
                    }
                    if (!empty($rows)) {
                        foreach ($data_set[0] as $key => $value) {
                            $flag = 0;
                            foreach ($rows as $k => $v) {
                                if ($data_set[2][$key] == $rows[$k]['day']) {
                                    $flag = 1;
                                    $daily[] = array('x' => $value, 'y' => $rows[$k]['commission_amount'], 'volume' => $rows[$k]['volume'], 'commission_amount' => $rows[$k]['commission_amount']);
                                }
                            }
                            if ($flag == 0) {
                                $daily[] = array('x' => $value, 'y' => 0.00, 'volume' => 0.00, 'commission_amount' => 0.00);
                            }
                        }
                    }
                    $response->addToResult('records', $daily);
                }
            } else if ($sub_operation == 'Widget3_Performance_Analytics') {
                $level = "findIBLevel(REPLACE(`child`.`ib_hierarchy`,'" . $ib_hierarchy . "',''))";
                $sql = "SELECT `child`.`firstname`, `child`.`lastname`, `child`.`email`,  " . $level . " AS `hierachy_level`,  
`t`.* FROM (SELECT SUM(`volume`) AS `volume`, SUM(`commission_amount`) AS `commission_amount`, `child_contactid` 
FROM `anl_comm_child` WHERE `parent_contactid` = " . $customerId . " AND `child_contactid` != " . $customerId . " GROUP BY `child_contactid` 
ORDER BY `commission_amount` DESC LIMIT 0,5) AS `t` INNER JOIN `vtiger_contactdetails` AS `child` 
ON `t`.`child_contactid`=`child`.`contactid` HAVING hierachy_level != 0";
                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                $rows = array();
                for ($i = 0; $i < $numRow; $i++) {
                    $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                    $ib_depth = $adb->query_result($sqlResult, $i, 'hierachy_level');
                    $rows[$i]['hierachy_level'] = ($ib_depth == '0') ? '-' : $ib_depth; //level
                    $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');
                    $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                    $rows[$i]['commission_amount'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'commission_amount'));
                }
                $response->addToResult('records', $rows);
            } else if ($sub_operation == 'Widget4_IBTeamPerformance') {
                $sql = "SELECT `child`.`contactid`,`child`.`firstname`, `child`.`lastname`, `child`.`affiliate_code`, "
                        . "`child`.`record_status`, `t`.* FROM (SELECT SUM(`commission_amount`) "
                        . "AS `commission_amount`,SUM(`volume`) AS `volume`, `parent_contactid` "
                        . "FROM anl_comm_child WHERE `parent_contactid` IN(SELECT contactid FROM `vtiger_contactdetails` "
                        . "WHERE `parent_affiliate_code` = " . $affiliate_code . " AND record_status = 'Approved') "
                        . "GROUP BY `parent_contactid`) AS `t`INNER JOIN `vtiger_contactdetails` AS `child` "
                        . "ON `t`.`parent_contactid` = `child`.`contactid` "
                        . "UNION "
                        . "SELECT `child`.`contactid`,`child`.`firstname`, `child`.`lastname`, "
                        . "`child`.`affiliate_code`, `child`.`record_status`, `t`.* "
                        . "FROM (SELECT SUM(`commission_amount`) AS `commission_amount`,SUM(`volume`) AS `volume`, "
                        . "child_contactid FROM anl_comm_child WHERE `child_contactid` "
                        . "IN(SELECT contactid FROM `vtiger_contactdetails` "
                        . "WHERE `parent_affiliate_code` = " . $affiliate_code . " AND record_status != 'Approved') "
                        . " AND `parent_contactid` = " . $customerId . " GROUP BY `child_contactid`) AS `t`INNER JOIN `vtiger_contactdetails` "
                        . "AS `child` ON `t`.`child_contactid` = `child`.`contactid` "
                        . "ORDER BY `commission_amount` DESC LIMIT 0,5";
                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                $rows = array();
                for ($i = 0; $i < $numRow; $i++) {
                    if ($adb->query_result($sqlResult, $i, 'record_status') == 'Approved') {
                        $name = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                        $short_name = implode('', array_map(function($v) {
                                    return $v[0];
                                }, explode(' ', $name)));
                        $rows[$i]['short_name'] = strtoupper($short_name);
                        $rows[$i]['name'] .= $name . ' and downline';
                    } else {
                        $name = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                        $short_name = implode('', array_map(function($v) {
                                    return $v[0];
                                }, explode(' ', $name)));
                        $rows[$i]['short_name'] = strtoupper($short_name);
                        $rows[$i]['name'] = $name;
                    }
                    $rows[$i]['affiliate_code'] = $adb->query_result($sqlResult, $i, 'affiliate_code');
                    $rows[$i]['commission_amount'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'commission_amount'));
                    $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                }
                $response->addToResult('records', $rows);
            } else if ($sub_operation == 'Widget5_Clients_Analytics') {
                $where = ' ';
                $where .= ' AND `contactid` IN (SELECT contactid FROM `vtiger_contactdetails` WHERE `ib_hierarchy` LIKE "' . $ib_hierarchy . '%")';
                $where .= ' AND contactid != ' . $customerId;
                $where .= ' GROUP BY contactid ';


                $live_account_count = 0;

                $contactIdColumn = "SELECT c.contactid";
                $contactIdCount = "SELECT count(c.contactid)";
                #All Childs
                $contactIdsSql = " FROM `vtiger_contactdetails` AS c JOIN
                `vtiger_crmentity` AS `e`
                ON `e`.`crmid` = `c`.`contactid` WHERE c.`ib_hierarchy` LIKE '" . $ib_hierarchy . "%' AND
                findIBLevel(REPLACE(`c`.`ib_hierarchy`, '" . $ib_hierarchy . "', '')) <= " . $max_ib_level . "
                AND e.`deleted` = 0 AND `c`.`contactid` != " . $customerId;

                #Total Clients
                $total_child = 0;
                $total_child_sql = $contactIdCount . " AS `total_child` " . $contactIdsSql;
                $total_child_sqlResult = $adb->pquery($total_child_sql, array());
                if ($adb->num_rows($total_child_sqlResult) > 0) {
                    $total_child = $adb->query_result($total_child_sqlResult, 0, 'total_child');
                }

                #Live account available
                $approved_total_live = 0;
                $approved_total_live_sql = "SELECT count(DISTINCT `l`.`contactid`) AS `approved_total_live` FROM `vtiger_liveaccount` AS `l` "
                        . "JOIN `vtiger_crmentity` AS `ce` ON `ce`.`crmid` = `l`.`liveaccountid` "
                        . "WHERE contactid IN (" . $contactIdColumn . $contactIdsSql . ") AND account_no != 0 "
                        . "AND ce.`deleted` = 0";
                $approved_total_live_sqlResult = $adb->pquery($approved_total_live_sql, array());
                if ($adb->num_rows($approved_total_live_sqlResult) > 0) {
                    $approved_total_live = $adb->query_result($approved_total_live_sqlResult, 0, 'approved_total_live');
                }

                #Pending Live Account
                $total_pending_liveaccount = 0;
                $total_pending_liveaccount_sql = "SELECT COUNT(DISTINCT l.contactid) AS `total_pending_liveaccount` FROM `vtiger_liveaccount` "
                        . "AS l JOIN `vtiger_crmentity` ce ON `ce`.`crmid` = `l`.`liveaccountid` "
                        . "WHERE contactid IN (" . $contactIdColumn . $contactIdsSql . ") "
                        . "AND `l`.`record_status` = 'Pending' AND ce.`deleted` = 0";
                $total_pending_liveaccount_sqlResult = $adb->pquery($total_pending_liveaccount_sql, array());
                if ($adb->num_rows($total_pending_liveaccount_sqlResult) > 0) {
                    $total_pending_liveaccount = $adb->query_result($total_pending_liveaccount_sqlResult, 0, 'total_pending_liveaccount');
                }

                $live_account_count = $total_pending_liveaccount + ($total_child - $total_pending_liveaccount - $approved_total_live);

                $kyc_doc_count = "SELECT COUNT(contactid) FROM vtiger_contactdetails WHERE is_document_verified = 0" . $where;
                $sqlResult = $adb->pquery($kyc_doc_count, array());
                $kyc_doc_count = $adb->num_rows($sqlResult);

                $is_ftd_count = "SELECT COUNT(contactid) FROM vtiger_contactdetails WHERE is_first_time_deposit = 0" . $where;
                $sqlResult = $adb->pquery($is_ftd_count, array());
                $is_ftd_count = $adb->num_rows($sqlResult);

                $ib_status_count = "SELECT COUNT(contactid) FROM vtiger_contactdetails WHERE (record_status = '' OR record_status = 'Pending')" . $where;
                $sqlResult = $adb->pquery($ib_status_count, array());
                $ib_status_count = $adb->num_rows($sqlResult);

                global $PORTAL_URL;
                $response->addToResult('records', array('kyc_doc_count' => $kyc_doc_count, 'live_account_count' => $live_account_count, 'is_ftd_count' => $is_ftd_count, 'ib_status_count' => $ib_status_count));
            } else if ($sub_operation == 'Widget6_Active_Trader_OR_IB') {
                $filter = $request->get('filter');
                if (!empty($filter) && $filter == 'Current Month') {
                    $from_date = date('Y-m-01');
                    $to_date = date('Y-m-d');
                    $filter = " AND `a`.`close_time` BETWEEN '" . $from_date . "' AND '" . $to_date . "'";
                }
                if (!empty($filter) && $filter == 'Last Month') {
                    $from_date = date("Y-n-j", strtotime("first day of previous month"));
                    $to_date = date("Y-n-j", strtotime("last day of previous month"));
                    $filter = " AND `a`.`close_time` BETWEEN '" . $from_date . "' AND '" . $to_date . "'";
                }
                $sql = "SELECT COUNT(IF(`is_ib` IS NULL,1,NULL)) AS `active_traders`, 
                    COUNT(`is_ib`) AS `active_ib_traders` FROM (SELECT DISTINCT `a`.`child_contactid`,
                    IF(`c`.`record_status` = 'Approved',1,NULL) AS `is_ib` FROM `anl_comm_child` AS `a`
INNER JOIN `vtiger_contactdetails` AS `c` ON `a`.`child_contactid`= `c`.`contactid` AND 
`a`.`parent_contactid` = $customerId " . $filter . ") AS `t`";
                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                if ($numRow > 0) {
                    $active_traders = $adb->query_result($sqlResult, 0, 'active_traders');
                    $active_ib_traders = $adb->query_result($sqlResult, 0, 'active_ib_traders');
                } else {
                    $active_traders = 0;
                    $active_ib_traders = 0;
                }
                /* $where .= ' AND `c`.`contactid` IN (SELECT contactid FROM `vtiger_contactdetails` WHERE `ib_hierarchy` LIKE "' . $ib_hierarchy . '%")';

                  $where .= ' AND `c`.contactid != ' . $customerId;


                  $sql = "SELECT `c`.`record_status`,`c`.`contactid`,`l`.`account_no`, `l`.`live_metatrader_type` "
                  . "FROM `vtiger_contactdetails` AS `c` INNER JOIN `vtiger_crmentity` AS `e` ON `e`.`crmid` = `c`.`contactid` "
                  . "INNER JOIN `vtiger_liveaccount` AS `l` ON `l`.`contactid` = `c`.`contactid` "
                  . "WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' AND `e`.`deleted` = 0  " . $where;
                  $sql .= " ORDER BY e.createdtime DESC";

                  $sqlResult = $adb->pquery($sql, array());
                  $numRow = $adb->num_rows($sqlResult);
                  $rows = array();
                  $active_traders = array();
                  $active_ib_traders = array();
                  for ($i = 0; $i < $numRow; $i++) {
                  $contactid = $adb->query_result($sqlResult, $i, 'contactid');
                  if (!array_key_exists($contactid, $active_traders)) { // if contact id already exist then that already count as a active trader
                  $live_metatrader_type = $adb->query_result($sqlResult, $i, 'live_metatrader_type');
                  $account_no = $adb->query_result($sqlResult, $i, 'account_no');
                  $record_status = $adb->query_result($sqlResult, $i, 'record_status');
                  $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
                  $meta_service_provider_query = $provider->getOpenTradesForIBDashboard($account_no, $filter);
                  $meta_sqlResult = $adb->pquery($meta_service_provider_query, array());
                  $meta_numRow = $adb->num_rows($meta_sqlResult);
                  if ($meta_numRow > 0) {
                  if ($record_status == 'Approved') {
                  $active_ib_traders[$contactid] = $record_status;
                  } else {
                  $active_traders[$contactid] = 1;
                  }
                  } else {
                  $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
                  $meta_service_provider_query = $provider->getCloseTradesForIBDashboard($account_no, $filter);
                  $meta_sqlResult = $adb->pquery($meta_service_provider_query, array());
                  $meta_numRow = $adb->num_rows($meta_sqlResult);
                  if ($meta_numRow > 0) {
                  if ($record_status == 'Approved') {
                  $active_ib_traders[$contactid] = $record_status;
                  } else {
                  $active_traders[$contactid] = 1;
                  }
                  }
                  }
                  }
                  }
                  $active_traders = count($active_traders);
                  $active_ib_traders = array_count_values($active_ib_traders);
                  $active_ib_traders = $active_ib_traders['Approved'];
                  if (!$active_ib_traders)
                  $active_ib_traders = 0;
                 * 
                 */
                $response->addToResult('records', array('active_traders' => $active_traders, 'active_ib_traders' => $active_ib_traders));
            } else if ($sub_operation == 'Widget7_Top_5_Earning_of_Sub_IBs') {
                $level = " findIBLevel(REPLACE(child.ib_hierarchy,'" . $ib_hierarchy . "','')) ";
                $sql = "SELECT child.firstname, child.lastname, child.email, SUM(t.volume) AS volume, "
                        . "SUM(t.commission_amount) AS commission_amount, " . $level . " AS ib_depth "
                        . "FROM `anl_comm_child` AS t INNER JOIN vtiger_contactdetails AS "
                        . "child ON t.child_contactid=child.contactid "
                        . "INNER JOIN `vtiger_crmentity` AS ce ON child.`contactid` = ce.`crmid` "
                        . "WHERE ce.`deleted` = 0  AND t.parent_contactid = " . $customerId . " "
                        . "AND t.`child_contactid` != " . $customerId . " AND "
                        . "child.`record_status` = 'Approved' GROUP BY t.child_contactid  "
                        . "ORDER BY commission_amount DESC LIMIT 0,5";
                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                $rows = array();
                for ($i = 0; $i < $numRow; $i++) {
                    $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                    $ib_depth = $adb->query_result($sqlResult, $i, 'ib_depth');
                    $rows[$i]['ib_depth'] = ($ib_depth == '0') ? '-' : $ib_depth; //level
                    $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');
                    $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                    $rows[$i]['commission_amount'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'commission_amount'));
                }
                $response->addToResult('records', $rows);
            } else if ($sub_operation == 'Widget8_Social_Links') {
                $referral_affiliate_link = configvar('liveacc_referral_url');
                //Referral link generation
                $affiliate_code = $contact['affiliate_code'];
                if (strpos($referral_affiliate_link, '?')) {
                    $referral_affiliate_link = $referral_affiliate_link . '&ref=' . $affiliate_code;
                } else {
                    $referral_affiliate_link = $referral_affiliate_link . '?ref=' . $affiliate_code;
                }
                //End
                $social_links = array();
                $social_links = array('referral_affiliate_link' => $referral_affiliate_link, 'affiliate_code' => $affiliate_code);

                $social_links['facebook_link'] = configvar('facebook_link');
                $social_links['linkedin_link'] = configvar('linkedin_link');
                $social_links['twitter_link'] = configvar('twitter_link');
                $social_links['instagram_link'] = configvar('instagram_link');
                $social_links['youtube_link'] = configvar('youtube_link');
                $response->addToResult('records', $social_links);
            } else {
                throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module, $portal_language), 1418);
                exit;
            }
            return $response;
        }
    }

    function getLast12WeeksDataSet() {
        $date_set = array();
        $date_set_of_fro_to = array();

        for ($i = 11; $i > -1; $i--) {
            $lastWeek = date("Y-m-d", strtotime("-$i week -1 day"));
            $first_day_of_lastWeek = date("dM Y", strtotime($lastWeek));

            $last_day_of_lastweek_1 = date("Y-m-d", strtotime("+6 day", strtotime($lastWeek)));
            $last_day_of_lastweek = date("jS M 'y", strtotime($last_day_of_lastweek_1));

            $date_set[] = $first_day_of_lastWeek;
            $date_set_of_fro_to[] = $lastWeek . ' AND ' . $last_day_of_lastweek_1;
        }
        return array($date_set, $date_set_of_fro_to);
    }

    function getLast12DaysDataSet() {
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
