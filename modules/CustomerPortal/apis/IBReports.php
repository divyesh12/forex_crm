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

class CustomerPortal_IBReports extends CustomerPortal_API_Abstract
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
            $contactWebserviceId = vtws_getWebserviceEntityId('Contacts', $customerId);
            $sub_operation = $request->get('sub_operation');
            $page = $request->get('page');
            $pageLimit = $request->get('pageLimit');
            $orderBy = $request->get('orderBy');
            $order = $request->get('order');
            //Custom paramter for count the data or not for export to excel
            $is_count = $request->get('is_count');
            //End
            $response_type = $request->get('response_type');

            $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
            $contact = vtws_retrieve($contactId, $current_user);
            $contact = CustomerPortal_Utils::resolveRecordValues($contact);
            $ib_hierarchy = $contact['ib_hierarchy']; //current login cabinet user's IB hierarchy

            if (empty($page)) {
                $page = 0;
            }

            if (empty($pageLimit)) {
                $pageLimit = CustomerPortal_Config::$DEFAULT_PAGE_LIMIT;
            }

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

            $count = null;

//Query generating of all the available meta trader service provider
            $service_provider_list = CustomerPortal_Utils::getListOfServiceProviders(1, 'title');
            $meta_service_provider_query = array();
//End

            /*if ($sub_operation == 'SubIBTransaction') {
            $from_date = $request->get('from_date');
            $to_date = $request->get('to_date');

            if (empty($from_date)) {
            throw new Exception(vtranslate('CAB_MSG_FROM_DATE_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1415);
            exit;
            }
            if (empty($to_date)) {
            throw new Exception(vtranslate('CAB_MSG_TO_DATE_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1416);
            exit;
            }

            if (!empty($service_provider_list)) {
            for ($i = 0; $i < count($service_provider_list); $i++) {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($service_provider_list[$i]);
            $meta_service_provider_query[] = $provider->getTransactionsForReport();
            }
            $meta_service_provider_query = implode(" UNION ", $meta_service_provider_query);
            } else {
            throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'Payments', $portal_language), 1416);
            exit;
            }
            //End
            if (!empty($orderBy)) {
            $orderBy = str_replace('name', 'c.firstname', $orderBy);
            $orderBy = str_replace('email', 'c.email', $orderBy);
            $orderBy = str_replace('parent_affiliate_code', 'c.parent_affiliate_code', $orderBy);
            $orderBy = str_replace('level', 'level', $orderBy);
            $orderBy = str_replace('login', 'trades.login', $orderBy);
            $orderBy = str_replace('ticket', 'trades.ticket', $orderBy);
            $orderBy = str_replace('close_time', 'trades.close_time', $orderBy);
            $orderBy = str_replace('profit', 'trades.profit', $orderBy);
            $orderBy = str_replace('comment', 'trades.comment', $orderBy);
            $orderBy = 'ORDER BY ' . $orderBy;
            if (empty($order)) {
            $order = 'DESC';
            } else {
            if (!in_array(strtoupper($order), array("DESC", "ASC"))) {
            throw new Exception(vtranslate('CAB_MSG_INVALID_SORTING_ORDER', $this->translate_module, $portal_language), 1412);
            exit;
            }
            }
            } else {
            $orderBy = ' ORDER BY `trades`.`close_time`';
            }

            $transaction_type = $request->get('transaction_type');
            $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), $pageLimit);

            $where = ' ';

            $include_child = $request->get('include_child');
            $child_contactid = $request->get('child_contactid');
            $sql_get_level = "SELECT findIBLevel(REPLACE(`c`.`ib_hierarchy`, '" . $ib_hierarchy . "', ''))";
            if ($include_child == 'true') {
            $sqlib_hierarchy = "SELECT `ib_hierarchy` FROM `vtiger_contactdetails` WHERE contactid = " . $child_contactid;
            $sqlib_hierarchyResult = $adb->pquery($sqlib_hierarchy, array());
            $ib_hierarchy = $adb->query_result($sqlib_hierarchyResult, 0, 'ib_hierarchy');
            $where .= ' AND `c`.`contactid` IN (SELECT contactid
            FROM `vtiger_contactdetails` WHERE `ib_hierarchy` LIKE "' . $ib_hierarchy . '%")';
            } else if ($child_contactid != '') {
            $where .= ' AND `c`.contactid = ' . $child_contactid;
            }
            $where .= ' AND `c`.contactid != ' . $customerId;

            //Taking column name from fron request so need to replace alias with respective table name in filter.
            $where .= " AND  `trades`.`close_time` >= '" . $from_date . "' AND `trades`.`close_time` <= '" . $to_date . "'";
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
            if (isset($is_count) && $is_count == '0') {
            //if count 0 then it will skip the count query execution
            } else {
            $sqlCount = "SELECT count(`c`.`firstname`) as `count` FROM `vtiger_contactdetails` AS `c` INNER JOIN `vtiger_liveaccount` AS `l` ON `c`.`contactid` = `l`.`contactid` INNER JOIN `vtiger_crmentity` AS `e` ON `e`.`crmid` = `l`.`liveaccountid` LEFT JOIN (" . $meta_service_provider_query . ") AS trades ON trades.login=l.account_no WHERE e.deleted = 0" . $where;
            $sqlCountResult = $adb->pquery($sqlCount, array());
            if ($adb->num_rows($sqlCountResult) > 0) {
            $count = $adb->query_result($sqlCountResult, 0, 'count');
            }
            }

            $sql = "SELECT `c`.`firstname`, `c`.`lastname`, `c`.`email`, `c`.`parent_affiliate_code`, (" . $sql_get_level . ") AS `level`, trades.* FROM `vtiger_contactdetails` AS `c` INNER JOIN `vtiger_liveaccount` AS `l` ON `c`.`contactid` = `l`.`contactid` INNER JOIN `vtiger_crmentity` AS `e` ON `e`.`crmid` = `l`.`liveaccountid` LEFT JOIN (" . $meta_service_provider_query . ") AS trades ON trades.login=l.account_no WHERE e.deleted = 0" . $where;

            $sql = $sql . ' ' . $limitClause;

            $sqlResult = $adb->pquery($sql, array());
            $numRow = $adb->num_rows($sqlResult);
            $rows = array();
            for ($i = 0; $i < $numRow; $i++) {
            $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
            $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');
            $rows[$i]['parent_affiliate_code'] = $adb->query_result($sqlResult, $i, 'parent_affiliate_code');
            $rows[$i]['level'] = $adb->query_result($sqlResult, $i, 'level');
            $rows[$i]['login'] = $adb->query_result($sqlResult, $i, 'login');
            $rows[$i]['ticket'] = $adb->query_result($sqlResult, $i, 'ticket');
            $rows[$i]['close_time'] = $adb->query_result($sqlResult, $i, 'close_time');
            $rows[$i]['profit'] = CustomerPortal_Utils::setNumberFormat(ABS($adb->query_result($sqlResult, $i, 'profit')));
            $rows[$i]['comment'] = $adb->query_result($sqlResult, $i, 'comment');
            }
            if (!empty($response_type) && $response_type == 'List') {
            //Note: In future we need to use this cabinet too, as of now using Mobile App
            $response->addToResult('records', $rows);
            } else {
            $response->setResult($rows);
            }
            $response->addToResult('count', $count);
            }*/
            if ($sub_operation == 'SubIBTransaction') {
                $from_date = $request->get('from_date');
                $to_date = $request->get('to_date');
                $parentId = $request->get('parentId');
                $recordId = $request->get('recordId');

                if (empty($from_date)) {
                    throw new Exception(vtranslate('CAB_MSG_FROM_DATE_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1415);
                    exit;
                }
                if (empty($to_date)) {
                    throw new Exception(vtranslate('CAB_MSG_TO_DATE_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1416);
                    exit;
                }


                if (empty($recordId)) {
                    throw new Exception(vtranslate('CAB_MSG_SEEMS_THAT_YOU_HAVE_NOT_ANY_ACCOUNT', $this->translate_module, $portal_language), 1412);
                    exit;
                }

                $module = VtigerWebserviceObject::fromId($adb, $recordId)->getEntityName();
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

                $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
                              

                // if (!empty($service_provider_list)) {
                //     for ($i = 0; $i < count($service_provider_list); $i++) {
                //         $provider = ServiceProvidersManager::getActiveInstanceByProvider($service_provider_list[$i]);
                //         $meta_service_provider_query[] = $provider->getTransactionsForReport();
                //     }
                //     $meta_service_provider_query = implode(" UNION ", $meta_service_provider_query);
                // } else {
                //     throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'Payments', $portal_language), 1416);
                //     exit;
                // }
//End
                if (!empty($orderBy)) {
                    $orderBy = str_replace('name', 'c.firstname', $orderBy);
                    $orderBy = str_replace('email', 'c.email', $orderBy);
                    $orderBy = str_replace('parent_affiliate_code', 'c.parent_affiliate_code', $orderBy);
                    $orderBy = str_replace('level', '`level`', $orderBy);
                    $orderBy = str_replace('login', 'trades.login', $orderBy);
                    $orderBy = str_replace('ticket', 'trades.ticket', $orderBy);
                    $orderBy = str_replace('close_time', 'trades.close_time', $orderBy);
                    $orderBy = str_replace('profit', 'trades.profit', $orderBy);
                    $orderBy = str_replace('comment', 'trades.comment', $orderBy);
                    $orderBy = 'ORDER BY ' . $orderBy;
                    if (empty($order)) {
                        $order = 'DESC';
                    } else {
                        if (!in_array(strtoupper($order), array("DESC", "ASC"))) {
                            throw new Exception(vtranslate('CAB_MSG_INVALID_SORTING_ORDER', $this->translate_module, $portal_language), 1412);
                            exit;
                        }
                    }
                } else {
                    $orderBy = ' ORDER BY `trades`.`close_time`';
                }

                $transaction_type = $request->get('transaction_type');
                $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), $pageLimit);

                $where = '';

                //$include_child = $request->get('include_child');
                //$child_contactid = $request->get('child_contactid');
                $sql_get_level = "SELECT findIBLevel(REPLACE(`c`.`ib_hierarchy`, '" . $ib_hierarchy . "', ''))";
                // if ($include_child == 'true') {
                //     $sqlib_hierarchy = "SELECT `ib_hierarchy` FROM `vtiger_contactdetails` WHERE contactid = " . $child_contactid;
                //     $sqlib_hierarchyResult = $adb->pquery($sqlib_hierarchy, array());
                //     $ib_hierarchy = $adb->query_result($sqlib_hierarchyResult, 0, 'ib_hierarchy');
                //     $where .= ' AND `c`.`contactid` IN (SELECT contactid
                //           FROM `vtiger_contactdetails` WHERE `ib_hierarchy` LIKE "' . $ib_hierarchy . '%")';
                // } else if ($child_contactid != '') {
                //     $where .= ' AND `c`.contactid = ' . $child_contactid;
                // }
                //$where .= ' AND `c`.contactid != ' . $customerId;
                
//Taking column name from fron request so need to replace alias with respective table name in filter.
                //$where .= " AND  `trades`.`close_time` >= '" . $from_date . "' AND `trades`.`close_time` <= '" . $to_date . "'";
//Check Transaction Type IN or OUT, If All the transacion type will be blank

                if (!empty($transaction_type)) {
                    if ($transaction_type == 'IN') {
                        $where .= "AND trades.profit > 0";
                    }
                    if ($transaction_type == 'OUT') {
                        $where .= "AND trades.profit < 0";
                    }
                }
                
//End
                if (isset($is_count) && $is_count == '0') {
                    //if count 0 then it will skip the count query execution
                } else {
                    //$sqlCount = "SELECT count(`c`.`firstname`) as `count` FROM `vtiger_contactdetails` AS `c` INNER JOIN `vtiger_liveaccount` AS `l` ON `c`.`contactid` = `l`.`contactid` INNER JOIN `vtiger_crmentity` AS `e` ON `e`.`crmid` = `l`.`liveaccountid` LEFT JOIN (" . $meta_service_provider_query . ") AS trades ON trades.login=l.account_no WHERE e.deleted = 0" . $where;
                    $sqlCount = $provider->getCountQueryForSubIbTransactionReport($account_no, $from_date, $to_date);                                        
                    $sqlCountResult = $adb->pquery($sqlCount, array());
                    if ($adb->num_rows($sqlCountResult) > 0) {
                        $count = $adb->query_result($sqlCountResult, 0, 'count');
                    }
                }
                $trade_query = $provider->getTransactionsForReport($account_no, $from_date, $to_date);

                $sql = "SELECT `c`.`firstname`, `c`.`lastname`, `c`.`email`, `c`.`parent_affiliate_code`, 
                (" . $sql_get_level . ") AS `level`, trades.* FROM `vtiger_contactdetails` AS `c` 
                INNER JOIN `vtiger_liveaccount` AS `l` ON `c`.`contactid` = `l`.`contactid`                 
                INNER JOIN (" . $trade_query . ") AS trades ON trades.login=l.account_no WHERE 1 " . $where;
                
                $sql = $sql . ' ' . $limitClause;                

                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                $rows = array();
                for ($i = 0; $i < $numRow; $i++) {
                    $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                    $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');
                    $rows[$i]['parent_affiliate_code'] = $adb->query_result($sqlResult, $i, 'parent_affiliate_code');
                    $rows[$i]['level'] = $adb->query_result($sqlResult, $i, 'level');
                    $rows[$i]['login'] = $adb->query_result($sqlResult, $i, 'login');
                    $rows[$i]['ticket'] = $adb->query_result($sqlResult, $i, 'ticket');
                    $rows[$i]['close_time'] = $adb->query_result($sqlResult, $i, 'close_time');
                    $rows[$i]['profit'] = CustomerPortal_Utils::setNumberFormat(ABS($adb->query_result($sqlResult, $i, 'profit')));
                    $rows[$i]['comment'] = $adb->query_result($sqlResult, $i, 'comment');
                }
                if (!empty($response_type) && $response_type == 'List') {
//Note: In future we need to use this cabinet too, as of now using Mobile App
                    $response->addToResult('records', $rows);
                } else {
                    $response->setResult($rows);
                }
                $response->addToResult('count', $count);
            } else if ($sub_operation == 'SubIBLiveAccount') {
                if (!empty($service_provider_list)) {
                    for ($i = 0; $i < count($service_provider_list); $i++) {
                        $provider = ServiceProvidersManager::getActiveInstanceByProvider($service_provider_list[$i]);
                        $syncConfig = $provider->syncConfig;
                        if(!$syncConfig['balance']) {
                            $accountList = getLiveAccountList($customerId, $provider->parameters['title']);
                            $meta_service_provider_query[] = $provider->getOutstandingForLiveAccountDashboard('',$accountList);
                        } else {
                            $meta_service_provider_query[] = $provider->getOutstandingForLiveAccountDashboard();
                        }
                    }
                    $meta_service_provider_query = implode(" UNION ", $meta_service_provider_query);
                } else {
                    throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'Payments', $portal_language), 1416);
                    exit;
                }

                if (!empty($orderBy)) {
                    $orderBy = str_replace('name', 'c.firstname', $orderBy);
                    $orderBy = str_replace('email', 'c.email', $orderBy);
                    $orderBy = str_replace('parent_affiliate_code', 'c.parent_affiliate_code', $orderBy);
                    $orderBy = str_replace('ib_depth', 'c.ib_depth', $orderBy);
                    $orderBy = str_replace('live_label_account_type', 'l.live_label_account_type', $orderBy);
                    $orderBy = str_replace('login', 'trades.login', $orderBy);
                    $orderBy = str_replace('live_label_account_type', 'l.live_label_account_type', $orderBy);
                    $orderBy = str_replace('balance', 'trades.balance', $orderBy);
                    $orderBy = str_replace('equity', 'trades.equity', $orderBy);
                    $orderBy = str_replace('margin_free', 'trades.margin_free', $orderBy);
                    $orderBy = 'ORDER BY ' . $orderBy;
                    if (empty($order)) {
                        $order = 'DESC';
                    } else {
                        if (!in_array(strtoupper($order), array("DESC", "ASC"))) {
                            throw new Exception(vtranslate('CAB_MSG_INVALID_SORTING_ORDER', $this->translate_module, $portal_language), 1412);
                            exit;
                        }
                    }
                } else {
                    $orderBy = ' ORDER BY `trades`.`login`';
                }

                $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), $pageLimit);

                $where = ' ';

                $include_child = $request->get('include_child');
                $child_contactid = $request->get('child_contactid');

                if ($include_child == 'true' && $child_contactid == '') {
                    $sqlib_hierarchy = "SELECT `ib_hierarchy` FROM `vtiger_contactdetails` WHERE contactid = " . $customerId;
                    $sqlib_hierarchyResult = $adb->pquery($sqlib_hierarchy, array());
                    $ib_hierarchy = $adb->query_result($sqlib_hierarchyResult, 0, 'ib_hierarchy');
                    $where .= ' AND `c`.`contactid` IN (SELECT `contactid` FROM `vtiger_contactdetails` WHERE `ib_hierarchy` LIKE "' . $ib_hierarchy . '%")';
                } else if ($include_child == 'true' && $child_contactid != '') {
                    $sqlib_hierarchy = "SELECT `ib_hierarchy` FROM `vtiger_contactdetails` WHERE contactid = " . $child_contactid;
                    $sqlib_hierarchyResult = $adb->pquery($sqlib_hierarchy, array());
                    $ib_hierarchy = $adb->query_result($sqlib_hierarchyResult, 0, 'ib_hierarchy');
                    $where .= ' AND `c`.`contactid` IN (SELECT `contactid` FROM `vtiger_contactdetails` WHERE `ib_hierarchy` LIKE "' . $ib_hierarchy . '%")';
                } else if ($child_contactid != '') {
                    $where .= ' AND `c`.contactid = ' . $child_contactid;
                }
                $where .= ' AND `c`.contactid != ' . $customerId;

                if (isset($is_count) && $is_count == '0') {
                    //if count 0 then it will skip the count query execution
                } else {
                    $sqlCount = "SELECT count(`c`.`firstname`) as `count` FROM `vtiger_contactdetails` AS `c` INNER JOIN `vtiger_liveaccount` AS `l` ON `c`.`contactid` = `l`.`contactid` INNER JOIN `vtiger_crmentity` AS `e` ON `e`.`crmid` = `l`.`liveaccountid` INNER JOIN (" . $meta_service_provider_query . ") AS trades ON trades.login=l.account_no WHERE e.deleted = 0" . $where;
                    $sqlCountResult = $adb->pquery($sqlCount, array());
                    if ($adb->num_rows($sqlCountResult) > 0) {
                        $count = $adb->query_result($sqlCountResult, 0, 'count');
                    }
                }

                $sql = "SELECT `c`.`firstname`, `c`.`lastname`, `c`.`email`, `c`.`parent_affiliate_code`, `c`.`ib_depth`, l.live_label_account_type, l.live_metatrader_type, trades.* FROM `vtiger_contactdetails` AS `c` INNER JOIN `vtiger_liveaccount` AS `l` ON `c`.`contactid` = `l`.`contactid` INNER JOIN `vtiger_crmentity` AS `e` ON `e`.`crmid` = `l`.`liveaccountid` INNER JOIN (" . $meta_service_provider_query . ") AS trades ON trades.login=l.account_no WHERE e.deleted = 0" . $where;
                $sql = $sql . ' ' . $limitClause;

                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                $rows = array();
                for ($i = 0; $i < $numRow; $i++) {

                    $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                    $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');
                    $rows[$i]['parent_affiliate_code'] = $adb->query_result($sqlResult, $i, 'parent_affiliate_code');
                    $rows[$i]['ib_depth'] = $adb->query_result($sqlResult, $i, 'ib_depth');
                    $rows[$i]['live_metatrader_type'] = $adb->query_result($sqlResult, $i, 'live_metatrader_type');
                    $rows[$i]['login'] = $adb->query_result($sqlResult, $i, 'login');
                    //Remove meta trade type from account type and assign it
                    $live_label_account_type = end(explode('_', $adb->query_result($sqlResult, $i, 'live_label_account_type')));
                    //End
                    $rows[$i]['live_label_account_type'] = $live_label_account_type;
                    $rows[$i]['balance'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'balance'));
                    $rows[$i]['equity'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'equity'));
                    $rows[$i]['margin_free'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'margin_free'));
                }
                if (!empty($response_type) && $response_type == 'List') {
//Note: In future we need to use this cabinet too, as of now using Mobile App
                    $response->addToResult('records', $rows);
                } else {
                    $response->setResult($rows);
                }
                $response->addToResult('count', $count);
            } else if ($sub_operation == 'SubIBTrade') {
                $from_date = $request->get('from_date');
                $to_date = $request->get('to_date');
                $parentId = $request->get('parentId');
                $recordId = $request->get('recordId');
                $trade_type = $request->get('trade_type');

                if (empty($from_date)) {
                    throw new Exception(vtranslate('CAB_MSG_FROM_DATE_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1415);
                    exit;
                }
                if (empty($to_date)) {
                    throw new Exception(vtranslate('CAB_MSG_TO_DATE_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1416);
                    exit;
                }

                if (empty($recordId)) {
                    throw new Exception(vtranslate('CAB_MSG_SEEMS_THAT_YOU_HAVE_NOT_ANY_ACCOUNT', $this->translate_module, $portal_language), 1412);
                    exit;
                }

                $module = VtigerWebserviceObject::fromId($adb, $recordId)->getEntityName();
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

                $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
                $meta_service_provider_query = $provider->getTradesForReport($trade_type, $account_no);

                if (!empty($orderBy)) {
                    $orderBy = str_replace('name', 'c.firstname', $orderBy);
                    $orderBy = str_replace('email', 'c.email', $orderBy);
                    $orderBy = str_replace('parent_affiliate_code', 'c.parent_affiliate_code', $orderBy);
                    $orderBy = str_replace('level', 'level', $orderBy);
                    $orderBy = str_replace('live_label_account_type', 'l.live_label_account_type', $orderBy);
                    $orderBy = str_replace('login', 'trades.login', $orderBy);
                    $orderBy = str_replace('ticket', 'trades.ticket', $orderBy);
                    $orderBy = str_replace('symbol', 'trades.symbol', $orderBy);
                    $orderBy = str_replace('volume', 'trades.volume', $orderBy);
                    $orderBy = str_replace('cmd', 'trades.cmd', $orderBy);
                    $orderBy = str_replace('open_time', 'trades.open_time', $orderBy);
                    $orderBy = str_replace('open_price', 'trades.open_price', $orderBy);
                    $orderBy = str_replace('close_time', 'trades.close_time', $orderBy);
                    $orderBy = str_replace('close_price', 'trades.close_price', $orderBy);
                    $orderBy = str_replace('tp', 'trades.tp', $orderBy);
                    $orderBy = str_replace('sl', 'trades.sl', $orderBy);
                    $orderBy = str_replace('commission', 'trades.commission', $orderBy);
                    $orderBy = str_replace('swaps', 'trades.swaps', $orderBy);
                    $orderBy = str_replace('profit', 'trades.profit', $orderBy);
                    $orderBy = 'ORDER BY ' . $orderBy;
                    if (empty($order)) {
                        $order = 'DESC';
                    } else {
                        if (!in_array(strtoupper($order), array("DESC", "ASC"))) {
                            throw new Exception(vtranslate('CAB_MSG_INVALID_SORTING_ORDER', $this->translate_module, $portal_language), 1412);
                            exit;
                        }
                    }
                } else {
                    $orderBy = ' ORDER BY `trades`.`login`';
                }

                $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), $pageLimit);

                $where = '';
                $ib_hierarchy = $contact['ib_hierarchy'];
                $child_contactid = $request->get('child_contactid');

                $where .= " AND `c`.`contactid` =  " . $child_contactid;

                if ($trade_type == 'open') {
                    $where .= " AND  `trades`.`open_time` >= '" . $from_date . "' AND `trades`.`open_time` <= '" . $to_date . "'";
                }

                if ($trade_type == 'close') {
                    $where .= " AND  `trades`.`close_time` >= '" . $from_date . "' AND `trades`.`close_time` <= '" . $to_date . "'";
                }

                if (isset($is_count) && $is_count == '0') {
                    //if count 0 then it will skip the count query execution
                } else {
                    $sqlCount = "SELECT count(`c`.`firstname`) as `count` FROM `vtiger_contactdetails` AS `c` INNER JOIN `vtiger_liveaccount` AS `l` ON `c`.`contactid` = `l`.`contactid` INNER JOIN `vtiger_crmentity` AS `e` ON `e`.`crmid` = `l`.`liveaccountid` INNER JOIN (" . $meta_service_provider_query . ") AS trades ON trades.login=l.account_no WHERE e.deleted = 0" . $where;
                    $sqlCountResult = $adb->pquery($sqlCount, array());
                    if ($adb->num_rows($sqlCountResult) > 0) {
                        $count = $adb->query_result($sqlCountResult, 0, 'count');
                    }
                }

                $sql_get_level = "SELECT findIBLevel(REPLACE(`c`.`ib_hierarchy`, '" . $ib_hierarchy . "', ''))";
                $sql = "SELECT `c`.`firstname`, `c`.`lastname`, `c`.`email`, `c`.`parent_affiliate_code`, (" . $sql_get_level . ") AS `level`, l.live_label_account_type, l.live_metatrader_type, trades.* FROM `vtiger_contactdetails` AS `c` INNER JOIN `vtiger_liveaccount` AS `l` ON `c`.`contactid` = `l`.`contactid` INNER JOIN `vtiger_crmentity` AS `e` ON `e`.`crmid` = `l`.`liveaccountid` INNER JOIN (" . $meta_service_provider_query . ") AS trades ON trades.login=l.account_no WHERE e.deleted = 0 " . $where;
                $sql = $sql . ' ' . $limitClause;
                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                $rows = array();
                for ($i = 0; $i < $numRow; $i++) {
                    $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                    $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');
                    $rows[$i]['parent_affiliate_code'] = $adb->query_result($sqlResult, $i, 'parent_affiliate_code');
                    $rows[$i]['level'] = $adb->query_result($sqlResult, $i, 'level');
                    $rows[$i]['live_metatrader_type'] = $adb->query_result($sqlResult, $i, 'live_metatrader_type');
                    $rows[$i]['login'] = $adb->query_result($sqlResult, $i, 'login');
                    $rows[$i]['ticket'] = $adb->query_result($sqlResult, $i, 'ticket');
                    $rows[$i]['symbol'] = $adb->query_result($sqlResult, $i, 'symbol');
                    $rows[$i]['volume'] = $adb->query_result($sqlResult, $i, 'volume');
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
                    $rows[$i]['tp'] = $adb->query_result($sqlResult, $i, 'tp');
                    $rows[$i]['sl'] = $adb->query_result($sqlResult, $i, 'sl');
                    $rows[$i]['commission'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'commission'));
                    $rows[$i]['swaps'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'swaps'));
                    $rows[$i]['profit'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'profit'));
                }
                if (!empty($response_type) && $response_type == 'List') {
//Note: In future we need to use this cabinet too, as of now using Mobile App
                    $response->addToResult('records', $rows);
                } else {
                    $response->setResult($rows);
                }
                $response->addToResult('count', $count);
            } else if ($sub_operation == 'SubIBCommissionSummary') {
                $level = " findIBLevel(REPLACE(`child`.`ib_hierarchy`,'" . $ib_hierarchy . "','')) ";

                $from_date = $request->get('from_date');
                $to_date = $request->get('to_date');

                if (empty($from_date)) {
                    throw new Exception(vtranslate('CAB_MSG_FROM_DATE_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1415);
                    exit;
                }
                if (empty($to_date)) {
                    throw new Exception(vtranslate('CAB_MSG_TO_DATE_SHOULD_NOT_BE_EMPTY', $this->translate_module, $portal_language), 1416);
                    exit;
                }

                if (!empty($orderBy)) {
                    $orderBy = str_replace('name', 'child.firstname', $orderBy);
                    $orderBy = str_replace('email', 'child.email', $orderBy);
                    $orderBy = str_replace('affiliate_code', 'child.affiliate_code', $orderBy);
                    //$orderBy = str_replace('ib_depth', 'child.ib_depth', $orderBy);
                    $orderBy = str_replace('volume', 't.volume', $orderBy);
                    $orderBy = str_replace('commission_amount', 't.commission_amount', $orderBy);
                    $orderBy = 'ORDER BY ' . $orderBy;

                    if (empty($order)) {
                        $order = 'DESC';
                    } else {
                        if (!in_array(strtoupper($order), array("DESC", "ASC"))) {
                            throw new Exception(vtranslate('CAB_MSG_INVALID_SORTING_ORDER', $this->translate_module, $portal_language), 1412);
                            exit;
                        }
                    }
                } else {
                    $orderBy = ' ORDER BY `child`.`ib_depth`';
                }

                $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), $pageLimit);

                $child_contactid = $request->get('child_contactid');
                $include_child = $request->get('include_child');

                if ($include_child == 'true' && $child_contactid == 'All') {
                    $sqlib_hierarchy = "SELECT `ib_hierarchy` FROM `vtiger_contactdetails` WHERE contactid = " . $customerId;
                    $sqlib_hierarchyResult = $adb->pquery($sqlib_hierarchy, array());
                    $ib_hierarchy = $adb->query_result($sqlib_hierarchyResult, 0, 'ib_hierarchy');
                    $where .= " AND `t`.`parent_contactid` IN (SELECT `contactid` FROM `vtiger_contactdetails` "
                        . "WHERE `ib_hierarchy` LIKE '" . $ib_hierarchy . "%' AND `record_status` = 'Approved' AND contactid !=" . $customerId . ")";
                } else if ($include_child == 'true' && $child_contactid != 'All') {
                    $sqlib_hierarchy = "SELECT `ib_hierarchy` FROM `vtiger_contactdetails` WHERE contactid = " . $child_contactid;
                    $sqlib_hierarchyResult = $adb->pquery($sqlib_hierarchy, array());
                    $ib_hierarchy = $adb->query_result($sqlib_hierarchyResult, 0, 'ib_hierarchy');
                    $where .= " AND `t`.`parent_contactid` IN (SELECT `contactid` FROM `vtiger_contactdetails` "
                        . "WHERE `ib_hierarchy` LIKE '" . $ib_hierarchy . "%' AND `record_status` = 'Approved' AND contactid !=" . $customerId . ")";
                } else if ($child_contactid != 'All') {
                    $where .= ' AND `t`.parent_contactid = ' . $child_contactid;
                }

                //Taking column name from fron request so need to replace alias with respective table name in filter.
                $filter .= " `t`.`close_time` >= '" . $from_date . "' AND `t`.`close_time` <= '" . $to_date . "'";

                /*$sqlCount = "SELECT COUNT(distinct(`t`.`child_contactid`)) AS `count` "
                . "FROM tradescommission AS t INNER JOIN vtiger_contactdetails AS child "
                . "ON child.`contactid` = t.parent_contactid INNER JOIN `vtiger_crmentity` "
                . "AS ce ON ce.`crmid` = child.`contactid` WHERE ce.`deleted` = 0" . $where;
                 */

                $sqlCount = "SELECT COUNT(distinct(`t`.`child_contactid`)) AS `count` "
                    . "FROM `anl_comm_child` AS t INNER JOIN vtiger_contactdetails AS child "
                    . "ON child.`contactid` = t.parent_contactid INNER JOIN `vtiger_crmentity` "
                    . "AS ce ON ce.`crmid` = child.`contactid` WHERE ce.`deleted` = 0" . $where;

                if (!empty($filter)) {
                    $sqlCount .= " AND " . $filter . " GROUP BY `t`.`parent_contactid`";
                } else {
                    $sqlCount .= " GROUP BY `t`.`parent_contactid`";
                }

                if (isset($is_count) && $is_count == '0') {
                    //if count 0 then it will skip the count query execution
                } else {
                    $sqlCountResult = $adb->pquery($sqlCount, array());
                    $count = $adb->num_rows($sqlCountResult);
                }

                // $sql = "SELECT sum(t.volume) as volume, sum(t.commission_amount) as commission_amount, child.firstname, "
                //     . "child.lastname, child.email, child.affiliate_code, child.record_status, " . $level . " AS ib_depth "
                //     . "FROM tradescommission AS t INNER JOIN vtiger_contactdetails AS child "
                //     . "ON child.`contactid` = t.parent_contactid INNER JOIN `vtiger_crmentity` AS ce "
                //     . "ON ce.`crmid` = child.`contactid` WHERE ce.`deleted` = 0 " . $where;

                $sql = "SELECT sum(t.volume) as volume, sum(t.commission_amount) as commission_amount, child.firstname, "
                    . "child.lastname, child.email, child.affiliate_code, child.record_status, " . $level . " AS ib_depth "
                    . "FROM anl_comm_child AS t INNER JOIN vtiger_contactdetails AS child "
                    . "ON child.`contactid` = t.parent_contactid INNER JOIN `vtiger_crmentity` AS ce "
                    . "ON ce.`crmid` = child.`contactid` WHERE ce.`deleted` = 0 " . $where;

                if (!empty($filter)) {
                    $sql .= " AND $filter GROUP BY t.parent_contactid";
                } else {
                    $sql .= " GROUP BY t.parent_contactid";
                }

                $sql = $sql . ' ' . $limitClause;

                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                $rows = array();
                for ($i = 0; $i < $numRow; $i++) {
                    $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                    $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');
                    $rows[$i]['ib_depth'] = $adb->query_result($sqlResult, $i, 'ib_depth');
                    $rows[$i]['affiliate_code'] = $adb->query_result($sqlResult, $i, 'affiliate_code');
                    $rows[$i]['volume'] = (float) $adb->query_result($sqlResult, $i, 'volume');
                    $rows[$i]['commission_amount'] = CustomerPortal_Utils::setNumberFormat($adb->query_result($sqlResult, $i, 'commission_amount'));
                }

                if (!empty($response_type) && $response_type == 'List') {
                    //Note: In future we need to use this cabinet too, as of now using Mobile App
                    $response->addToResult('records', $rows);
                } else {
                    $response->setResult($rows);
                }
                $response->addToResult('count', $count);
            } else if ($sub_operation == 'SubIBAnalytics') {
                $level = " findIBLevel(REPLACE(`c`.`ib_hierarchy`,'" . $ib_hierarchy . "','')) ";
                $is_kyc_approved = $request->get('is_kyc_approved');
                $is_liveaccount = $request->get('is_liveaccount');
                $is_ftd = $request->get('is_ftd');
                $ib_status = $request->get('is_ib_status');
                $where = ' ';

                //is_kyc_approved filter
                if (!empty($is_kyc_approved) && $is_kyc_approved == 'true') {
                    $where .= " AND `c`.`is_document_verified` = 1";
                }
                if (!empty($is_kyc_approved) && $is_kyc_approved == 'false') {
                    $where .= " AND `c`.`is_document_verified` != 1";
                }
                //is_liveaccount filter
                if (!empty($is_liveaccount) && $is_liveaccount == 'true') {
                    $where .= " AND (IF((SELECT COUNT(*) AS `is_liveaccount` FROM `vtiger_liveaccount` AS `vl` WHERE `vl`.`contactid` = `c`.`contactid` AND `vl`.`record_status` = 'Approved' AND `vl`.`account_no` != 0 AND `vl`.`account_no` != '') > 0,1,0)) = 1";
                }
                if (!empty($is_liveaccount) && $is_liveaccount == 'false') {
                    $where .= " AND (IF((SELECT COUNT(*) AS `is_liveaccount` FROM `vtiger_liveaccount` AS `vl` WHERE `vl`.`contactid` = `c`.`contactid` AND `vl`.`record_status` = 'Approved' AND `vl`.`account_no` != 0 AND `vl`.`account_no` != '') > 0,1,0)) = 0";
                }

                //is_ftd filter
                if (!empty($is_ftd) && $is_ftd == 'true') {
                    $where .= " AND `c`.`is_first_time_deposit` = 1";
                }
                if (!empty($is_ftd) && $is_ftd == 'false') {
                    $where .= " AND `c`.`is_first_time_deposit` != 1";
                }

                //ib_status filter
                if (!empty($ib_status) && $ib_status == 'true') {
                    $where .= " AND `c`.`record_status` = 'Approved'";
                }
                if (!empty($ib_status) && $ib_status == 'false') {
                    $where .= " AND `c`.`record_status` != 'Approved'";
                }
                if (!empty($orderBy)) {
                    $orderBy = str_replace('name', 'c.firstname', $orderBy);
                    $orderBy = str_replace('email', 'c.email', $orderBy);
                    //$orderBy = str_replace('ib_depth', 'c.ib_depth', $orderBy);
                    $orderBy = str_replace('affiliate_code', 'c.affiliate_code', $orderBy);
                    $orderBy = str_replace('parent_affiliate_code', 'c.parent_affiliate_code', $orderBy);
                    $orderBy = str_replace('is_document_verified', 'c.is_document_verified', $orderBy);
                    $orderBy = str_replace('is_liveaccount', 'is_liveaccount', $orderBy);
                    $orderBy = str_replace('is_first_time_deposit', 'c.is_first_time_deposit', $orderBy);
                    $orderBy = str_replace('record_status', 'c.record_status', $orderBy);

                    $orderBy = 'ORDER BY ' . $orderBy;
                    if (empty($order)) {
                        $order = 'DESC';
                    } else {
                        if (!in_array(strtoupper($order), array("DESC", "ASC"))) {
                            throw new Exception(vtranslate('CAB_MSG_INVALID_SORTING_ORDER', $this->translate_module, $portal_language), 1412);
                            exit;
                        }
                    }
                } else {
                    $orderBy = ' ORDER BY `ib_depth`';
                }

                $limitClause = sprintf('%s %s LIMIT %s,%s ;', $orderBy, $order, ($page * $pageLimit), $pageLimit);

                $include_child = $request->get('include_child');
                $child_contactid = $request->get('child_contactid');

                if ($include_child == 'true' && $child_contactid == 'All') {
                    $where .= ' AND `c`.`contactid` IN (SELECT contactid FROM `vtiger_contactdetails` WHERE `ib_hierarchy` LIKE "' . $ib_hierarchy . '%")';
                } else if ($include_child == 'true' && $child_contactid != 'All') {
                    $sqlib_hierarchy = "SELECT `ib_hierarchy` FROM `vtiger_contactdetails` WHERE contactid = " . $child_contactid;
                    $sqlib_hierarchyResult = $adb->pquery($sqlib_hierarchy, array());
                    $ib_hierarchy = $adb->query_result($sqlib_hierarchyResult, 0, 'ib_hierarchy');
                    $where .= ' AND `c`.`contactid` IN (SELECT contactid FROM `vtiger_contactdetails` WHERE `ib_hierarchy` LIKE "' . $ib_hierarchy . '%")';
                } else if ($child_contactid != 'All') {
                    $where .= ' AND `c`.contactid = ' . $child_contactid;
                }
                $where .= ' AND `c`.contactid != ' . $customerId;

                if (isset($is_count) && $is_count == '0') {
                    //if count 0 then it will skip the count query execution
                } else {
                    $sqlCount = "SELECT count(`c`.`firstname`) as `count` FROM `vtiger_contactdetails` AS `c`
INNER JOIN `vtiger_crmentity` AS `e` ON `e`.`crmid` = `c`.`contactid` WHERE e.deleted = 0 " . $where;
                    $sqlCountResult = $adb->pquery($sqlCount, array());
                    if ($adb->num_rows($sqlCountResult) > 0) {
                        $count = $adb->query_result($sqlCountResult, 0, 'count');
                    }
                }

                $is_liveaccount_query = "IF((SELECT COUNT(*) AS `is_liveaccount` FROM `vtiger_liveaccount` AS `vl` WHERE `vl`.`contactid` = `c`.`contactid` AND `vl`.`record_status` = 'Approved' AND `vl`.`account_no` != 0 AND `vl`.`account_no` != '') > 0,'Yes','No')";
                $sql = "SELECT (" . $is_liveaccount_query . ") AS `is_liveaccount`,`c`.`firstname`, `c`.`lastname`, `c`.`email`, `c`.`parent_affiliate_code`, `c`.`record_status`, `c`.`is_first_time_deposit`,
`c`.`affiliate_code`, `c`.`is_document_verified`, " . $level . " as `ib_depth` FROM `vtiger_contactdetails` AS `c`
INNER JOIN `vtiger_crmentity` AS `e` ON `e`.`crmid` = `c`.`contactid`
WHERE `e`.`deleted` = 0" . $where;
                $sql = $sql . ' ' . $limitClause;
                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                $rows = array();
                for ($i = 0; $i < $numRow; $i++) {
                    $rows[$i]['name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
                    $rows[$i]['email'] = $adb->query_result($sqlResult, $i, 'email');
                    $rows[$i]['ib_depth'] = $adb->query_result($sqlResult, $i, 'ib_depth'); //level
                    if ($adb->query_result($sqlResult, $i, 'record_status') == 'Approved') {
                        $rows[$i]['affiliate_code'] = $adb->query_result($sqlResult, $i, 'affiliate_code');
                    } else {
                        $rows[$i]['affiliate_code'] = '';
                    }

                    $rows[$i]['parent_affiliate_code'] = $adb->query_result($sqlResult, $i, 'parent_affiliate_code');
                    $rows[$i]['is_document_verified'] = $adb->query_result($sqlResult, $i, 'is_document_verified') ? 'Yes' : 'No';
                    $rows[$i]['is_liveaccount'] = $adb->query_result($sqlResult, $i, 'is_liveaccount');
                    $rows[$i]['is_first_time_deposit'] = $adb->query_result($sqlResult, $i, 'is_first_time_deposit') ? 'Yes' : 'No';
                    $rows[$i]['record_status'] = $adb->query_result($sqlResult, $i, 'record_status') == 'Approved' ? 'Approved' : 'Pending';
                }

                if (!empty($response_type) && $response_type == 'List') {
                    //Note: In future we need to use this cabinet too, as of now using Mobile App
                    $response->addToResult('records', $rows);
                } else {
                    $response->setResult($rows);
                }
                $response->addToResult('count', $count);
            } else {
                throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module, $portal_language), 1418);
                exit;
            }
            return $response;
        }
    }

}
