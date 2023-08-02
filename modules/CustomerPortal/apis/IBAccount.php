<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_IBAccount extends CustomerPortal_API_Abstract {

    protected $translate_module = 'CustomerPortal_Client';

    public function process(CustomerPortal_API_Request $request) {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $customerId = $this->getActiveCustomer()->id;
            $contactWebserviceId = vtws_getWebserviceEntityId('Contacts', $customerId);
            $module = $request->get('module');
            $moduleLabel = $request->get('moduleLabel');
            $sub_operation = $request->get('sub_operation');
            $orderBy = $request->get('orderBy');
            $order = $request->get('order');
//Custom paramter for count the data or not for export to excel
            $is_count = $request->get('is_count');
//End
            $filter = htmlspecialchars_decode($request->get('filter'));
            $activeFields = CustomerPortal_Utils::getActiveFields($module);

            $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
            $contact = vtws_retrieve($contactId, $current_user);
            $contact = CustomerPortal_Utils::resolveRecordValues($contact);
            $ib_hierarchy = $contact['ib_hierarchy'];
            $affiliate_code = $contact['affiliate_code'];

            if (!empty($orderBy)) {
                $orderBy = 'ORDER BY ' . $orderBy;
                if (empty($order)) {
                    $order = 'DESC';
                } else {
                    if (!in_array(strtoupper($order), array("DESC", "ASC"))) {
                        throw new Exception(vtranslate('CAB_MSG_INVALID_SORTING_ORDER', $this->translate_module, $portal_language), 1412);
                        exit;
                    }
                }
            }

            $page = $request->get('page');
            if (empty($page)) {
                $page = 0;
            }

            $pageLimit = $request->get('pageLimit');

            if (empty($pageLimit)) {
                $pageLimit = CustomerPortal_Config::$DEFAULT_PAGE_LIMIT;
            }

            if (empty($groupConditionsBy)) {
                $groupConditionsBy = 'AND';
            }

            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }

//Check configuration added by sandeep 20-02-2020
            $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
            CustomerPortal_Utils::checkConfiguration($contactId, $current_user, $module, array(), $portal_language);
//End

            $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), $pageLimit);

            $count = null;
            if ($module == 'Contacts') {
                $response_type = $request->get('response_type');
                if ($sub_operation == 'IBAssociatedAccount') {

                    if (empty($orderBy)) {
                        $orderBy = ' ORDER BY level';
                        $order = 'ASC';
                    }
                    $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), $pageLimit);

                    $referral_affiliate_link = configvar('liveacc_referral_url');
//Referral link generation
                    $affiliate_code = $contact['affiliate_code'];
                    if (strpos($referral_affiliate_link, '?')) {
                        $referral_affiliate_link = $referral_affiliate_link . '&ref=' . $affiliate_code;
                    } else {
                        $referral_affiliate_link = $referral_affiliate_link . '?ref=' . $affiliate_code;
                    }
//End
                    if (empty($filter)) {
                        $filter = " c.ib_hierarchy LIKE '$ib_hierarchy" . "%'";
                    }

                    if (isset($is_count) && $is_count == '0') {
//if count 0 then it will skip the count query execution
                    } else {
                        $sqlCount = "SELECT count(*) as count FROM (SELECT findIBLevel(REPLACE(c.ib_hierarchy,'" . $ib_hierarchy . "','')) as level
FROM `vtiger_contactdetails` as c INNER JOIN `vtiger_crmentity` as ce ON c.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0 AND " . $filter . ") AS t WHERE t.level <=" . configvar('max_ib_level');
                        $sqlCountResult = $adb->pquery($sqlCount, array());
                        if ($adb->num_rows($sqlCountResult) > 0) {
                            $count = $adb->query_result($sqlCountResult, 0, 'count');
                        }
                    }

                    $sql = "SELECT * FROM (SELECT c.email, c.firstname, c.lastname, c.affiliate_code, c.parent_affiliate_code,c.ib_hierarchy, c.record_status, findIBLevel(REPLACE(c.ib_hierarchy,'" . $ib_hierarchy . "','')) as level FROM `vtiger_contactdetails` as c INNER JOIN `vtiger_crmentity` as ce ON c.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0 AND " . $filter . " " . rtrim($limitClause, ';') . ") AS t WHERE t.level <=" . configvar('max_ib_level');
                    $sqlResult = $adb->pquery($sql, array());
                    $numRow = $adb->num_rows($sqlResult);
                    $rows = array();
                    for ($i = 0; $i < $numRow; $i++) {
                        $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');
                        $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                        $rows[$i]['level'] = $adb->query_result($sqlResult, $i, 'level');
                        if ($adb->query_result($sqlResult, $i, 'record_status') == 'Approved') {
                            $rows[$i]['affiliate_code'] = $adb->query_result($sqlResult, $i, 'affiliate_code');
                        } else {
                            $rows[$i]['affiliate_code'] = '';
                        }

                        $rows[$i]['parent_affiliate_code'] = $adb->query_result($sqlResult, $i, 'parent_affiliate_code');
                        $rows[$i]['ib_hierarchy'] = $adb->query_result($sqlResult, $i, 'ib_hierarchy');
                    }
                    if (!empty($response_type) && $response_type == 'List') {
//Note: In future we need to use this cabinet too, as of now using Mobile App
                        $response->addToResult('records', $rows);
                    } else {
                        $response->setResult($rows);
                    }
                    $response->addToResult('count', $count);
                    $response->addToResult('referral_affiliate_link', $referral_affiliate_link);
                } else if ($sub_operation == 'IBStatistics') {
                    if (empty($orderBy)) {
                        $orderBy = ' ORDER BY t.hierachy_level';
                    }

                    $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), $pageLimit);

                    $where = ' AND t.parent_contactid = ' . $customerId;

                    $include_child = $request->get('include_child');
                    $child_contactid = $request->get('child_contactid');

                    //if ($include_child == 'true' && $child_contactid != 'All') {
                    if ($include_child == 'true' && $child_contactid != 'My_First_Level_Child' && $child_contactid != 'All') {
                        $sqlib_hierarchy = "SELECT `ib_hierarchy` FROM `vtiger_contactdetails` WHERE contactid = " . $child_contactid;
                        $sqlib_hierarchyResult = $adb->pquery($sqlib_hierarchy, array());
                        $ib_hierarchy_child = $adb->query_result($sqlib_hierarchyResult, 0, 'ib_hierarchy');

                        $where = ' AND t.parent_contactid =  ' . $customerId . ' AND t.child_contactid IN (SELECT contactid
                            FROM `vtiger_contactdetails` WHERE `ib_hierarchy` LIKE "' . $ib_hierarchy_child . '%")';
                        //} else if ($child_contactid != 'All') {
                    } else if ($child_contactid != 'My_First_Level_Child' && $child_contactid != 'All' && !empty($child_contactid)) {
                        //$child_contactid != 'All'  need to remove from mobile then remove conditoins from this file for 'All'
                        $where = ' AND t.parent_contactid = ' . $customerId . ' AND t.child_contactid = ' . $child_contactid;
                    } else if ($child_contactid == 'My_First_Level_Child' || $child_contactid == 'All') {
                        //here include child will be disabled with un checked
                        $where = ' AND t.parent_contactid = ' . $customerId . ' AND child.parent_affiliate_code = ' . $affiliate_code;
                    }
                    $hide_zero_commission = $request->get('hide_zero_commission');
                    if ($hide_zero_commission == 'true') {
                        if (!empty($filter)) {
                            $filter .= ' AND t.commission_amount != 0';
                        } else {
                            $filter .= ' t.commission_amount != 0';
                        }
                    }

//Taking column name from fron request so need to replace alias with respective table name in filter.
                    $filter = str_replace('close_time', 't.close_time', $filter);
                    $filter = str_replace('commission_withdraw_status', 't.commission_withdraw_status', $filter);

                    if (isset($is_count) && $is_count == '0') {
//if count 0 then it will skip the count query execution
                    } else {
                        $sqlCount = "SELECT count(child.firstname) as `count` FROM `tradescommission` as t "
                                . "  INNER JOIN vtiger_contactdetails as child ON "
                                . "t.child_contactid=child.contactid INNER JOIN `vtiger_crmentity` as ce ON child.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0 " . $where;
                        if (!empty($filter)) {
                            $sqlCount .= " AND " . $filter;
                        }

                        $sqlCountResult = $adb->pquery($sqlCount, array());
                        $count = $adb->query_result($sqlCountResult, 0, 'count');
                    }
                    $sql = "SELECT t.*, child.firstname, child.lastname, child.email, child.affiliate_code, child.record_status FROM `tradescommission` as t "
                            . "  INNER JOIN vtiger_contactdetails as child ON "
                            . "t.child_contactid=child.contactid INNER JOIN `vtiger_crmentity` as ce ON child.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0 " . $where;

                    if (!empty($filter)) {
                        $sql .= " AND $filter";
                    }

                    $sql = $sql . ' ' . $limitClause;

                    $sqlResult = $adb->pquery($sql, array());
                    $numRow = $adb->num_rows($sqlResult);
                    $rows = array();
                    for ($i = 0; $i < $numRow; $i++) {
                        $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                        $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');

                        if ($adb->query_result($sqlResult, $i, 'record_status') == 'Approved') {
                            $rows[$i]['affiliate_code'] = $adb->query_result($sqlResult, $i, 'affiliate_code');
                        } else {
                            $rows[$i]['affiliate_code'] = '';
                        }

                        $rows[$i]['tradescommissionid'] = $adb->query_result($sqlResult, $i, 'tradescommissionid');
                        $rows[$i]['parent_contactid'] = $adb->query_result($sqlResult, $i, 'parent_contactid');
                        $rows[$i]['child_contactid'] = $adb->query_result($sqlResult, $i, 'child_contactid');
                        $rows[$i]['server_type'] = $adb->query_result($sqlResult, $i, 'server_type');
                        $rows[$i]['login'] = $adb->query_result($sqlResult, $i, 'login');
                        $rows[$i]['label_account_type'] = $adb->query_result($sqlResult, $i, 'label_account_type');
                        $rows[$i]['currency'] = $adb->query_result($sqlResult, $i, 'currency');
                        $rows[$i]['ticket'] = $adb->query_result($sqlResult, $i, 'ticket');
                        $rows[$i]['symbol'] = $adb->query_result($sqlResult, $i, 'symbol');
                        $rows[$i]['security'] = $adb->query_result($sqlResult, $i, 'security');
                        $rows[$i]['open_price'] = (float) $adb->query_result($sqlResult, $i, 'open_price');
                        $rows[$i]['close_price'] = (float) $adb->query_result($sqlResult, $i, 'close_price');
                        $rows[$i]['open_time'] = $adb->query_result($sqlResult, $i, 'open_time');
                        $rows[$i]['close_time'] = $adb->query_result($sqlResult, $i, 'close_time');
                        $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                        $rows[$i]['digits'] = $adb->query_result($sqlResult, $i, 'digits');
                        $rows[$i]['comment'] = $adb->query_result($sqlResult, $i, 'comment');
                        $rows[$i]['profile_id'] = $adb->query_result($sqlResult, $i, 'profile_id');
                        $rows[$i]['hierachy_level'] = $adb->query_result($sqlResult, $i, 'hierachy_level');
                        $rows[$i]['commission_type'] = $adb->query_result($sqlResult, $i, 'commission_type');
                        $rows[$i]['commission_value'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'commission_value'));
                        $rows[$i]['pip'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'pip'));
                        $rows[$i]['commission_amount'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'commission_amount'));
                        $rows[$i]['data'] = $adb->query_result($sqlResult, $i, 'data');
                        $rows[$i]['tags'] = $adb->query_result($sqlResult, $i, 'tags');
                        $rows[$i]['profit'] = (float) $adb->query_result($sqlResult, $i, 'profit');
                        $rows[$i]['commission_withdraw_status'] = $adb->query_result($sqlResult, $i, 'commission_withdraw_status');
                        $rows[$i]['withdraw_reference_id'] = $adb->query_result($sqlResult, $i, 'withdraw_reference_id');
                        $rows[$i]['commission_comment'] = $adb->query_result($sqlResult, $i, 'commission_comment');
                        $rows[$i]['type'] = $adb->query_result($sqlResult, $i, 'type');
                        $rows[$i]['brokerage_commission'] = $adb->query_result($sqlResult, $i, 'brokerage_commission');
                    }
                    if (!empty($response_type) && $response_type == 'List') {
//Note: In future we need to use this cabinet too, as of now using Mobile App
                        $response->addToResult('records', $rows);
                    } else {
                        $response->setResult($rows);
                    }
                    $response->addToResult('count', $count);
                } else if ($sub_operation == 'IBSummary') {
                    $child_contactid = $request->get('child_contactid');
                    $include_child = $request->get('include_child');

                    $where = ' AND t.parent_contactid = ' . $customerId;
                    $limit = sprintf(' LIMIT %s,%s ;', ($page * $pageLimit), $pageLimit);

                    /*  Count query Query */
                    $sqlCount = "SELECT COUNT(1) AS `count` FROM (SELECT SUM(`volume`) "
                            . "AS `volume`, SUM(`commission_amount`) AS `commission_amount`, "
                            . "child_contactid FROM anl_comm_child  "
                            . "WHERE " . $filter . " GROUP BY `child_contactid`) "
                            . "AS `t` INNER JOIN `vtiger_contactdetails` AS `child` "
                            . "ON `t`.`child_contactid` = `child`.`contactid` "
                            . "WHERE 1 ";
                    /*                     * ********** End count Query ************** */

                    $level = "findIBlevel(REPLACE(`child`.`ib_hierarchy`,'" . $ib_hierarchy . "',''))";

                    $sql = "SELECT `child`.`firstname`, `child`.`lastname`, " . $level . " AS `hierachy_level`, `child`.`email`, "
                            . "`child`.`affiliate_code`, `child`.`record_status`, "
                            . "t.* FROM (SELECT SUM(`volume`) AS `volume`, SUM(`commission_amount`) "
                            . "AS `commission_amount`, child_contactid FROM anl_comm_child "
                            . "WHERE " . $filter . " "
                            . "GROUP BY `child_contactid`) AS `t` "
                            . "INNER JOIN `vtiger_contactdetails` AS `child` "
                            . "ON `t`.`child_contactid` = `child`.`contactid` "
                            . "WHERE 1 ";
                    if (isset($include_child) && $include_child == 'true' && isset($child_contactid) && !empty($child_contactid) && ($child_contactid != 'My_First_Level_Child' && $child_contactid != 'All')) {
                        $sqlib_hierarchy = "SELECT `ib_hierarchy` FROM `vtiger_contactdetails` WHERE contactid = " . $child_contactid;
                        $sqlib_hierarchyResult = $adb->pquery($sqlib_hierarchy, array());
                        $ib_hierarchy_child = $adb->query_result($sqlib_hierarchyResult, 0, 'ib_hierarchy');

                        $where = 'SELECT contactid FROM `vtiger_contactdetails` WHERE `ib_hierarchy` LIKE "' . $ib_hierarchy_child . '%"';

                        $sqlCount = "SELECT COUNT(1) AS `count` FROM (SELECT SUM(`volume`) "
                                . "AS `volume`, SUM(`commission_amount`) AS `commission_amount`, "
                                . "child_contactid FROM anl_comm_child  "
                                . "WHERE " . $filter . " AND parent_contactid = " . $customerId . " AND child_contactid IN(" . $where . ") GROUP BY `child_contactid`) "
                                . "AS `t` INNER JOIN `vtiger_contactdetails` AS `child` "
                                . "ON `t`.`child_contactid` = `child`.`contactid` "
                                . "WHERE 1 ";

                        $sql = "SELECT `child`.`firstname`, `child`.`lastname`, " . $level . " AS `hierachy_level`, `child`.`email`, "
                                . "`child`.`affiliate_code`, `child`.`record_status`, "
                                . "t.* FROM (SELECT SUM(`volume`) AS `volume`, SUM(`commission_amount`) "
                                . "AS `commission_amount`, child_contactid FROM anl_comm_child "
                                . "WHERE " . $filter . " AND parent_contactid = " . $customerId . " AND child_contactid IN(" . $where . ") "
                                . "GROUP BY `child_contactid`) AS `t` "
                                . "INNER JOIN `vtiger_contactdetails` AS `child` "
                                . "ON `t`.`child_contactid` = `child`.`contactid` "
                                . "WHERE 1 ";
                    } else if (isset($child_contactid) && !empty($child_contactid) && ($child_contactid != 'My_First_Level_Child' && $child_contactid != 'All')) {
                        $where = " AND parent_contactid = " . $customerId . " AND child_contactid = " . $child_contactid;
                        $sqlCount = "SELECT COUNT(1) AS `count` FROM (SELECT SUM(`volume`) "
                                . "AS `volume`, SUM(`commission_amount`) AS `commission_amount`, "
                                . "child_contactid FROM anl_comm_child  "
                                . "WHERE " . $filter . $where . "  GROUP BY `child_contactid`) "
                                . "AS `t` INNER JOIN `vtiger_contactdetails` AS `child` "
                                . "ON `t`.`child_contactid` = `child`.`contactid` "
                                . "WHERE 1 ";

                        $sql = "SELECT `child`.`firstname`, `child`.`lastname`, " . $level . " AS `hierachy_level`, `child`.`email`, "
                                . "`child`.`affiliate_code`, `child`.`record_status`, "
                                . "t.* FROM (SELECT SUM(`volume`) AS `volume`, SUM(`commission_amount`) "
                                . "AS `commission_amount`, child_contactid FROM anl_comm_child "
                                . "WHERE " . $filter . $where . " "
                                . "GROUP BY `child_contactid`) AS `t` "
                                . "INNER JOIN `vtiger_contactdetails` AS `child` "
                                . "ON `t`.`child_contactid` = `child`.`contactid` "
                                . "WHERE 1 ";
                    } else if (isset($child_contactid) && !empty($child_contactid) && ($child_contactid == 'My_First_Level_Child' || $child_contactid == 'All')) { //For my first childs
                        $sqlCount = "SELECT COUNT(1) AS `count` FROM (SELECT SUM(`volume`) "
                                . "AS `volume`, SUM(`commission_amount`) AS `commission_amount`, "
                                . "child_contactid FROM anl_comm_child  "
                                . "WHERE " . $filter . " AND `parent_contactid` = " . $customerId . " GROUP BY `child_contactid`) "
                                . "AS `t` INNER JOIN `vtiger_contactdetails` AS `child` "
                                . "ON `t`.`child_contactid` = `child`.`contactid` "
                                . "WHERE 1 ";

                        $sql = "SELECT `child`.`firstname`, `child`.`lastname`, " . $level . " AS `hierachy_level`, `child`.`email`, "
                                . "`child`.`affiliate_code`, `child`.`record_status`, "
                                . "t.* FROM (SELECT SUM(`volume`) AS `volume`, SUM(`commission_amount`) "
                                . "AS `commission_amount`, child_contactid FROM anl_comm_child "
                                . "WHERE " . $filter . " AND `parent_contactid` = " . $customerId . " "
                                . "GROUP BY `child_contactid`) AS `t` "
                                . "INNER JOIN `vtiger_contactdetails` AS `child` "
                                . "ON `t`.`child_contactid` = `child`.`contactid` "
                                . "WHERE 1 ";

                        $where = " AND `child`.`parent_affiliate_code` = " . $affiliate_code;
                        //$sqlVolumeCommSum .= $where;
                        $sqlCount .= $where;
                        $sql .= $where;
                    } else {
                        
                    }

                    $sql .= " ORDER BY `hierachy_level` ASC " . $limit;

                    if (isset($is_count) && $is_count == '0') {
                        //if count 0 then it will skip the count query execution
                    } else {
                        $sqlCountResult = $adb->pquery($sqlCount, array());
                        if ($adb->num_rows($sqlCountResult) > 0) {
                            $count = $adb->query_result($sqlCountResult, 0, 'count');
                        }
                    }

                    //main query
                    $sqlResult = $adb->pquery($sql, array());
                    $numRow = $adb->num_rows($sqlResult);
                    $rows = array();
                    for ($i = 0; $i < $numRow; $i++) {
                        $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                        $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');
                        $rows[$i]['hierachy_level'] = $adb->query_result($sqlResult, $i, 'hierachy_level');
                        if ($adb->query_result($sqlResult, $i, 'record_status') == 'Approved') {
                            $rows[$i]['affiliate_code'] = $adb->query_result($sqlResult, $i, 'affiliate_code');
                        } else {
                            $rows[$i]['affiliate_code'] = '';
                        }

                        $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                        $rows[$i]['commission_amount'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'commission_amount'));
                        $rows[$i]['child_contactid'] = $adb->query_result($sqlResult, $i, 'child_contactid');
                    }

                    if (!empty($response_type) && $response_type == 'List') {
                        //Note: In future we need to use this cabinet too, as of now using Mobile App
                        $response->addToResult('records', $rows);
                    } else {
                        $response->setResult($rows);
                    }
                    $response->addToResult('count', (int) $count);
                } else if ($sub_operation == 'IBWithdrawal') {
                    $count = 0;
                    $total_records = 0;

                    //Taking column name from fron request so need to replace alias with respective table name in filter.

                    if (isset($is_count) && $is_count == '0') {
                        //if count 0 then it will skip the count query execution
                    } else {
                        $sqlCount = "SELECT COUNT(tradescommissionid) AS `count` FROM `tradescommission`
WHERE parent_contactid = " . $customerId . " AND commission_withdraw_status = 0";
                        $sqlCountResult = $adb->pquery($sqlCount, array());
                        $numRow = $adb->num_rows($sqlCountResult);
                        if ($numRow > 0) {
                            //Need to set limit cabinet or mobile
                            $record_limit = $request->get('record_limit');
                            $count = $adb->query_result($sqlCountResult, 0, 'count');
                            $total_records = $count;

                            if (isset($record_limit) && !empty($record_limit) && $count > $record_limit) {
                                $count = $record_limit;
                                $page_condition = $page * $pageLimit;
                                if ($page_condition > $record_limit && $page == 0) {
                                    $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, $page, $record_limit);
                                } else if (($page_condition + $pageLimit) > $record_limit && $page == 0) {
                                    $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), $record_limit);
                                } else if (($page_condition + $pageLimit) > $record_limit) {
                                    $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), ($page_condition + $pageLimit) - $record_limit);
                                }
                            }
                        }
                    }

                    $sql = "SELECT t.*, child.firstname, child.lastname, child.email, child.affiliate_code FROM `tradescommission` as t "
                            . " INNER JOIN vtiger_contactdetails as child ON "
                            . "t.child_contactid = child.contactid INNER JOIN `vtiger_crmentity` as ce ON child.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0 AND t.parent_contactid = $customerId AND t.commission_withdraw_status = 0" . $where;

                    $sql = $sql . '  ' . $limitClause;
                    $sqlResult = $adb->pquery($sql, array());
                    $numRow = $adb->num_rows($sqlResult);
                    $rows = array();
                    for ($i = 0; $i < $numRow; $i++) {
                        $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                        $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');
                        $rows[$i]['hierachy_level'] = $adb->query_result($sqlResult, $i, 'hierachy_level');
                        $rows[$i]['login'] = $adb->query_result($sqlResult, $i, 'login');
                        $rows[$i]['ticket'] = $adb->query_result($sqlResult, $i, 'ticket');
                        $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                        $rows[$i]['symbol'] = $adb->query_result($sqlResult, $i, 'symbol');
                        $rows[$i]['open_price'] = (float) $adb->query_result($sqlResult, $i, 'open_price');
                        $rows[$i]['close_price'] = (float) $adb->query_result($sqlResult, $i, 'close_price');
                        $rows[$i]['close_time'] = $adb->query_result($sqlResult, $i, 'close_time');
                        $rows[$i]['profit'] = (float) $adb->query_result($sqlResult, $i, 'profit');
                        $rows[$i]['commission_amount'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'commission_amount'));
                        $rows[$i]['comment'] = $adb->query_result($sqlResult, $i, 'comment');
                        $rows[$i]['label_account_type'] = $adb->query_result($sqlResult, $i, 'label_account_type');
                        $rows[$i]['currency'] = $adb->query_result($sqlResult, $i, 'currency');
                        $rows[$i]['security'] = $adb->query_result($sqlResult, $i, 'security');
                        $rows[$i]['open_time'] = $adb->query_result($sqlResult, $i, 'open_time');
                        $rows[$i]['digits'] = $adb->query_result($sqlResult, $i, 'digits');
                        $rows[$i]['profile_id'] = $adb->query_result($sqlResult, $i, 'profile_id');
                        $rows[$i]['commission_type'] = $adb->query_result($sqlResult, $i, 'commission_type');
                        $rows[$i]['commission_value'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'commission_value'));
                        $rows[$i]['data'] = $adb->query_result($sqlResult, $i, 'data');
                        $rows[$i]['tags'] = $adb->query_result($sqlResult, $i, 'tags');
                        $rows[$i]['commission_withdraw_status'] = $adb->query_result($sqlResult, $i, 'commission_withdraw_status');
                        $rows[$i]['withdraw_reference_id'] = $adb->query_result($sqlResult, $i, 'withdraw_reference_id');
                        $rows[$i]['commission_comment'] = $adb->query_result($sqlResult, $i, 'commission_comment');
                    }
                    if (!empty($response_type) && $response_type == 'List') {
                        //Note: In future we need to use this cabinet too, as of now using Mobile App
                        $response->addToResult('records', $rows);
                    } else {
                        $response->setResult($rows);
                    }
                    $response->addToResult('count', $count);
                    $response->addToResult('total_records', $total_records);
                } else if ($sub_operation == 'GetIBChilds') {
                    //For search value based on data will be return
                    $search_value = $request->get('search_value');
                    //End
                    //It's for Sub IB filter, which will help to return only childs of login cabinet user
                    $sub_ib = $request->get('sub_ib');
                    //End
                    //It's for getting only IB approved contacts
                    $is_only_sub_ib = $request->get('is_only_sub_ib');
                    //End
                    $ib_hierarchy = $contact['ib_hierarchy'];

                    $limit = " LIMIT 0, 20";
                    $search_value_query = "";
                    if (!empty($search_value)) {
                        $search_value_query = " (c.firstname LIKE '" . $search_value . "%'  OR c.lastname LIKE '" . $search_value . "%' OR c.affiliate_code LIKE '" . $search_value . "%') AND";
                        $limit = "";
                    }

                    //Check self rebeate
                    //$ibcommission_self_rebate = configvar('ibcommission_self_rebate');
                    //End
                    $sql = "SELECT * FROM (SELECT c.contactid, c.firstname, c.lastname, c.affiliate_code, c.record_status, c.ib_hierarchy, findIBLevel(REPLACE(c.ib_hierarchy, '" . $ib_hierarchy . "', '')) as level FROM `vtiger_contactdetails` as c INNER JOIN `vtiger_crmentity` as ce ON c.`contactid` = ce.`crmid` WHERE " . $search_value_query . " ce.`deleted` = 0";
                    if ($is_only_sub_ib == 'true') {
                        $sql = $sql . " AND c.record_status = 'Approved' ";
                    }
                    $sql = $sql . " AND c.ib_hierarchy LIKE '" . $ib_hierarchy . "%') AS t WHERE t.level <= " . configvar('max_ib_level') . $limit;
                    $sqlResult = $adb->pquery($sql, array());
                    $count = $adb->num_rows($sqlResult);
                    $rows = array();
                    for ($i = 0; $i < $count; $i++) {
                        //It's for Sub IB filter, which will help to return only childs of login cabinet user
                        if ($sub_ib == 'true' && $adb->query_result($sqlResult, $i, 'contactid') == $customerId) {
                            
                        } else {
                            // if ($is_only_sub_ib == 'true') {
                            //     //if is only sub ib is true then returns the IB approved contact name only
                            //     if ($adb->query_result($sqlResult, $i, 'record_status') == 'Approved') {
                            //         $rows[$i]['child_contactid'] = $adb->query_result($sqlResult, $i, 'contactid');
                            //         $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                            //         $rows[$i]['affiliate_code'] = $adb->query_result($sqlResult, $i, 'affiliate_code');
                            //     }
                            // } else {
                            //     $rows[$i]['child_contactid'] = $adb->query_result($sqlResult, $i, 'contactid');
                            //     $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                            //     if ($adb->query_result($sqlResult, $i, 'record_status') == 'Approved') {
                            //         $rows[$i]['affiliate_code'] = $adb->query_result($sqlResult, $i, 'affiliate_code');
                            //     } else {
                            //         $rows[$i]['affiliate_code'] = '';
                            //     }
                            // }
                            $rows[$i]['child_contactid'] = $adb->query_result($sqlResult, $i, 'contactid');
                            $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');

                            if ($adb->query_result($sqlResult, $i, 'record_status') == 'Approved') {
                                $rows[$i]['affiliate_code'] = $adb->query_result($sqlResult, $i, 'affiliate_code');
                            } else {
                                $rows[$i]['affiliate_code'] = '';
                            }
                        }
                    }
                    if (!empty($response_type) && $response_type == 'List') {
                        $rows = array_values($rows);
                        //Note: In future we need to use this cabinet too, as of now using Mobile App
                        $response->addToResult('records', $rows);
                    } else {
                        $response->setResult($rows);
                    }
                } else if ($sub_operation == 'IBTree') {
                    if (empty($orderBy)) {
                        $orderBy = ' ORDER BY level';
                        $order = 'ASC';
                    }
                    $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), $pageLimit);
                    $ib_hierarchy = $contact['ib_hierarchy'];
                    if (empty($filter)) {
                        $filter = " c.ib_hierarchy LIKE '$ib_hierarchy" . "%'";
                    }
                    $sql = "SELECT * FROM (SELECT c.contactid, c.email, c.firstname, c.lastname, c.affiliate_code, c.parent_affiliate_code, c.ib_hierarchy, c.record_status, findIBLevel(REPLACE(c.ib_hierarchy, '" . $ib_hierarchy . "', '')) as level FROM `vtiger_contactdetails` as c INNER JOIN `vtiger_crmentity` as ce ON c.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0 AND " . $filter . ") AS t WHERE t.level <= " . configvar('max_ib_level');
                    $sqlResult = $adb->pquery($sql, array());
                    $numRow = $adb->num_rows($sqlResult);
                    $rows = array();
                    for ($i = 0; $i < $numRow; $i++) {
                        $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                        if ($adb->query_result($sqlResult, $i, 'record_status') == 'Approved') {
                            $rows[$i]['affiliate_code'] = $adb->query_result($sqlResult, $i, 'affiliate_code');
                        } else {
                            $rows[$i]['affiliate_code'] = '';
                        }

                        $rows[$i]['parent_affiliate_code'] = $adb->query_result($sqlResult, $i, 'parent_affiliate_code');
                        $rows[$i]['depth'] = $adb->query_result($sqlResult, $i, 'level');
                        $rows[$i]['contactid'] = $adb->query_result($sqlResult, $i, 'contactid');
                        //Set for new IB tree by sandeep thakkar at 03-08-2021
                        $ib_hierarchy_find_level = $adb->query_result($sqlResult, $i, 'ib_hierarchy');
                        $rows[$i]['directSubordinates'] = $this->getDirectSubordinates($ib_hierarchy_find_level);
                        $rows[$i]['totalSubordinates'] = $this->getTotalSubordinates($ib_hierarchy_find_level);
                        //End
                    }
                    $response->addToResult('records', $rows);
                } else if ($sub_operation == 'FetchIBSummaryTotalCommissionVolume') {

                    //For setting the total commission amount and no of lots
                    $commission_amount = 0.00;
                    $no_of_lots = 0.00;
                    //End

                    $child_contactid = $request->get('child_contactid');
                    $include_child = $request->get('include_child');

                    $where = ' AND t.parent_contactid = ' . $customerId;
                    $limit = sprintf(' LIMIT %s,%s ;', ($page * $pageLimit), $pageLimit);

                    $sqlCount = "SELECT SUM(`t`.`volume`) AS `sum_of_volume`, "
                            . "SUM(`t`.`commission_amount`) AS `sum_of_commission_amount` FROM (SELECT SUM(`volume`) "
                            . "AS `volume`, SUM(`commission_amount`) AS `commission_amount`, "
                            . "child_contactid FROM anl_comm_child  "
                            . "WHERE " . $filter . " GROUP BY `child_contactid`) "
                            . "AS `t` INNER JOIN `vtiger_contactdetails` AS `child` "
                            . "ON `t`.`child_contactid` = `child`.`contactid` "
                            . "WHERE 1 ";
                    /*                     * ********** End count Query ************** */

                    $level = "findIBlevel(REPLACE(`child`.`ib_hierarchy`,'" . $ib_hierarchy . "',''))";

                    if (isset($include_child) && $include_child == 'true' && isset($child_contactid) && !empty($child_contactid) && ($child_contactid != 'My_First_Level_Child' && $child_contactid != 'All')) {
                        $sqlib_hierarchy = "SELECT `ib_hierarchy` FROM `vtiger_contactdetails` WHERE contactid = " . $child_contactid;
                        $sqlib_hierarchyResult = $adb->pquery($sqlib_hierarchy, array());
                        $ib_hierarchy_child = $adb->query_result($sqlib_hierarchyResult, 0, 'ib_hierarchy');

                        $where = 'SELECT contactid FROM `vtiger_contactdetails` WHERE `ib_hierarchy` LIKE "' . $ib_hierarchy_child . '%"';

                        $sqlCount = "SELECT SUM(`t`.`volume`) AS `sum_of_volume`, "
                                . "SUM(`t`.`commission_amount`) AS `sum_of_commission_amount` FROM (SELECT SUM(`volume`) "
                                . "AS `volume`, SUM(`commission_amount`) AS `commission_amount`, "
                                . "child_contactid FROM anl_comm_child  "
                                . "WHERE " . $filter . " AND parent_contactid = " . $customerId . " AND child_contactid IN(" . $where . ") GROUP BY `child_contactid`) "
                                . "AS `t` INNER JOIN `vtiger_contactdetails` AS `child` "
                                . "ON `t`.`child_contactid` = `child`.`contactid` "
                                . "WHERE 1 ";
                    } else if (isset($child_contactid) && !empty($child_contactid) && ($child_contactid != 'My_First_Level_Child' && $child_contactid != 'All')) {
                        $where = " AND parent_contactid = " . $customerId . " AND child_contactid = " . $child_contactid;
                        $sqlCount = "SELECT SUM(`t`.`volume`) AS `sum_of_volume`, "
                                . "SUM(`t`.`commission_amount`) AS `sum_of_commission_amount` FROM (SELECT SUM(`volume`) "
                                . "AS `volume`, SUM(`commission_amount`) AS `commission_amount`, "
                                . "child_contactid FROM anl_comm_child  "
                                . "WHERE " . $filter . $where . "  GROUP BY `child_contactid`) "
                                . "AS `t` INNER JOIN `vtiger_contactdetails` AS `child` "
                                . "ON `t`.`child_contactid` = `child`.`contactid` "
                                . "WHERE 1 ";
                    } else if (isset($child_contactid) && !empty($child_contactid) && ($child_contactid == 'My_First_Level_Child' || $child_contactid == 'All')) { //For my first childs
                        $sqlCount = "SELECT SUM(`t`.`volume`) AS `sum_of_volume`, "
                                . "SUM(`t`.`commission_amount`) AS `sum_of_commission_amount` FROM (SELECT SUM(`volume`) "
                                . "AS `volume`, SUM(`commission_amount`) AS `commission_amount`, "
                                . "child_contactid FROM anl_comm_child  "
                                . "WHERE " . $filter . " AND `parent_contactid` = " . $customerId . " GROUP BY `child_contactid`) "
                                . "AS `t` INNER JOIN `vtiger_contactdetails` AS `child` "
                                . "ON `t`.`child_contactid` = `child`.`contactid` "
                                . "WHERE 1 ";
                        $where = " AND `child`.`parent_affiliate_code` = " . $affiliate_code;
                        $sqlCount .= $where;
                    } else {
                        
                    }

                    $sqlCountResult = $adb->pquery($sqlCount, array());
                    if ($adb->num_rows($sqlCountResult) > 0) {
                        $no_of_lots = $adb->query_result($sqlCountResult, 0, 'sum_of_volume');
                        $commission_amount = CustomerPortal_Utils::setNumberFormatWithoutCommaSeparater($adb->query_result($sqlCountResult, 0, 'sum_of_commission_amount'));
                    }

                    $response->addToResult('records', array('no_of_lots' => (float) $no_of_lots, 'commission_amount' => CustomerPortal_Utils::setNumberFormat($commission_amount)));
                } else if ($sub_operation == 'IBStatisticsTotalCommissionVolume') {
                    //For setting the total commission amount and no of lots
                    $commission_amount = 0.00;
                    $no_of_lots = 0.00;
                    //End

                    $where = ' AND t.parent_contactid = ' . $customerId;

                    $include_child = $request->get('include_child');
                    $child_contactid = $request->get('child_contactid');

                    if ($include_child == 'true' && $child_contactid != 'My_First_Level_Child' && $child_contactid != 'All') {
                        $sqlib_hierarchy = "SELECT `ib_hierarchy` FROM `vtiger_contactdetails` WHERE contactid = " . $child_contactid;
                        $sqlib_hierarchyResult = $adb->pquery($sqlib_hierarchy, array());
                        $ib_hierarchy_child = $adb->query_result($sqlib_hierarchyResult, 0, 'ib_hierarchy');

                        $where = ' AND t.parent_contactid =  ' . $customerId . ' AND t.child_contactid IN (SELECT contactid
                                                FROM `vtiger_contactdetails` WHERE `ib_hierarchy` LIKE "' . $ib_hierarchy_child . '%")';
                    } else if ($child_contactid != 'My_First_Level_Child' && $child_contactid != 'All' && !empty($child_contactid)) {
                        $where = ' AND t.parent_contactid = ' . $customerId . ' AND t.child_contactid = ' . $child_contactid;
                    } else if ($child_contactid == 'My_First_Level_Child' || $child_contactid == 'All') {
                        //here include child will be disabled with un checked
                        $where = ' AND t.parent_contactid = ' . $customerId . ' AND child.parent_affiliate_code = ' . $affiliate_code;
                    }
                    $hide_zero_commission = $request->get('hide_zero_commission');
                    if ($hide_zero_commission == 'true') {
                        if (!empty($filter)) {
                            $filter .= ' AND t.commission_amount != 0';
                        } else {
                            $filter .= ' t.commission_amount != 0';
                        }
                    }

                    //Taking column name from fron request so need to replace alias with respective table name in filter.
                    $filter = str_replace('close_time', 't.close_time', $filter);
                    $filter = str_replace('commission_withdraw_status', 't.commission_withdraw_status', $filter);

                    $sqlCount = "SELECT sum(t.volume) as `sum_of_volume`, sum(t.commission_amount) as `sum_of_commission_amount`  FROM `tradescommission` as t "
                            . "  INNER JOIN vtiger_contactdetails as child ON "
                            . "t.child_contactid=child.contactid INNER JOIN `vtiger_crmentity` as ce ON child.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0 " . $where;
                    if (!empty($filter)) {
                        $sqlCount .= " AND " . $filter;
                    }

                    $sqlCountResult = $adb->pquery($sqlCount, array());
                    $no_of_lots = $adb->query_result($sqlCountResult, 0, 'sum_of_volume');
                    $commission_amount = $adb->query_result($sqlCountResult, 0, 'sum_of_commission_amount');

                    $response->addToResult('records', array('no_of_lots' => (float) $no_of_lots,
                        'commission_amount' => CustomerPortal_Utils::setNumberFormat($commission_amount)));
                } else if ($sub_operation == 'IBWithdrawalTotalCommissionVolume') {
                    $no_of_lots = 0;
                    $earned_commission = 0;

                    $sqlCount = "SELECT sum(IF(commission_amount>0,IF(commission_comment='',volume,0),0)) AS no_of_lots, SUM(commission_amount) AS earned_commission FROM `tradescommission`
WHERE parent_contactid = " . $customerId . " AND commission_withdraw_status = 0";
                    $sqlCountResult = $adb->pquery($sqlCount, array());
                    $numRow = $adb->num_rows($sqlCountResult);
                    if ($numRow > 0) {
                        $earned_commission = $adb->query_result($sqlCountResult, 0, 'earned_commission');
                        $no_of_lots = $adb->query_result($sqlCountResult, 0, 'no_of_lots');
                    }

                    $response->addToResult('records', array('earned_commission' => CustomerPortal_Utils::setNumberFormat($earned_commission),
                        'no_of_lots' => (float) $no_of_lots));
                } else {
                    throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module, $portal_language), 1413);
                    exit;
                }
                return $response;
            }
        }
    }

    function getDirectSubordinates($ib_hierarchy) {
        global $adb;
        $sql = "SELECT * FROM (SELECT c.contactid, findIBLevel(REPLACE(c.ib_hierarchy, '" . $ib_hierarchy . "', '')) AS `level` FROM `vtiger_contactdetails` AS c INNER JOIN `vtiger_crmentity` AS ce ON c.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0 AND  c.ib_hierarchy LIKE '" . $ib_hierarchy . "%') AS t HAVING `level` = 1";
        $sqlResult = $adb->pquery($sql, array());
        $numRow = $adb->num_rows($sqlResult);
        return $numRow;
    }

    function getTotalSubordinates($ib_hierarchy) {
        global $adb;
        $sql = "SELECT * FROM (SELECT c.contactid, findIBLevel(REPLACE(c.ib_hierarchy, '" . $ib_hierarchy . "', '')) AS `level` FROM `vtiger_contactdetails` AS c INNER JOIN `vtiger_crmentity` AS ce ON c.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0 AND  c.ib_hierarchy LIKE '" . $ib_hierarchy . "%') AS t WHERE `level` != 0";
        $sqlResult = $adb->pquery($sql, array());
        $numRow = $adb->num_rows($sqlResult);
        return $numRow;
    }

}
