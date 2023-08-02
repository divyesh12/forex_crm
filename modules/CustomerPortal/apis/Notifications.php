<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_Notifications extends CustomerPortal_API_Abstract {

    protected $translate_module = 'CustomerPortal_Client';

    function process(CustomerPortal_API_Request $request) {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $customerId = $this->getActiveCustomer()->id;
            $module = $request->get('module');
            $moduleLabel = $request->get('moduleLabel');
            $sub_operation = $request->get('sub_operation');
            if ($sub_operation == 'FetchNotifications') {
                $fieldsArray = $request->get('fields');
                $orderBy = $request->get('orderBy');
                $order = $request->get('order');

                $activeFields = CustomerPortal_Utils::getActiveFields($module);

                if (empty($orderBy)) {
                    $orderBy = ' `e`.`modifiedtime`';
                } else if (!empty($orderBy)) {
                    $orderBy = str_replace('modifiedtime', 'e.modifiedtime', $orderBy);
                } else {
                    if (!in_array($orderBy, $activeFields)) {
                        throw new Exception(vtranslate('CAB_MSG_SORT_BY', $this->translate_module, $portal_language) . $orderBy . vtranslate('CAB_MSG_NOT_ALLOWED', $this->translate_module, $portal_language), 1412);
                        exit;
                    }
                }

                if (empty($order)) {
                    $order = 'DESC';
                } else {
                    if (!in_array(strtoupper($order), array("DESC", "ASC"))) {
                        throw new Exception(vtranslate('CAB_MSG_INVALID_SORTING_ORDER', $this->translate_module, $portal_language), 1412);
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
                    throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                    exit;
                }

                $count = null;

                if ($fieldsArray !== null) {
                    foreach ($fieldsArray as $key => $value) {
                        if (!in_array($key, $activeFields)) {
                            throw new Exception($key . vtranslate('CAB_MSG_IS_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                            exit;
                        }
                    }
                }
                $fields = implode(',', $activeFields);

                $orderby_paging_sql = sprintf(' ORDER BY %s %s LIMIT %s,%s ', $orderBy, $order, ($page * $pageLimit), $pageLimit);
                $where = " AND `n`.`status` = 0 AND `notification_type` = 'Cabinet' AND `n`.`contact_id` = " . $customerId;

                $sql = "SELECT `n`.* FROM vtiger_notifications  AS `n` INNER JOIN `vtiger_contactdetails` AS `c` 
                ON `n`.`contact_id` = `c`.`contactid` INNER JOIN `vtiger_crmentity` AS `e` 
                ON  `e`.`crmid` =  `n`.`notificationsid` WHERE 1 " . $where;
                //Count data                                                                 
                $sqlCountResult = $adb->pquery($sql, array());
                $count = $adb->num_rows($sqlCountResult);
                //End                       
                $sql .= $orderby_paging_sql;

                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                $rows = array();
                for ($i = 0; $i < $numRow; $i++) {
                    $rows[$i]['notificationsid'] = $adb->query_result($sqlResult, $i, 'notificationsid');
                    $rows[$i]['title'] = $adb->query_result($sqlResult, $i, 'title');
                    $rows[$i]['link'] = $adb->query_result($sqlResult, $i, 'link');
                    $rows[$i]['status'] = $adb->query_result($sqlResult, $i, 'status');
                    $rows[$i]['notification_type'] = $adb->query_result($sqlResult, $i, 'notification_type');
                    $rows[$i]['contact_id'] = $adb->query_result($sqlResult, $i, 'contact_id');
                }
                $response->addToResult('records', $rows);
                $response->addToResult('count', $count);
            } else if ($sub_operation == 'UpdateNotification') {
                $recordId = $request->get('recordId');
                if ($recordId == 'ClearAll') {
                    $sql = "UPDATE `vtiger_notifications` SET `status` = 1 WHERE `contact_id` = " . $customerId;
                    $sqlResult = $adb->pquery($sql, array());
                    $res = $adb->num_rows($sqlResult);
                    $response->addToResult('message', vtranslate('CAB_MSG_NOTIFICATION_HAS_BEEN_CLEARED_ALL', $this->translate_module, $portal_language));
                } else {
                    if (!empty($recordId)) {
                        //Stop edit record if edit is disabled
                        if (!CustomerPortal_Utils::isModuleRecordEditable($module)) {
                            throw new Exception(vtranslate('CAB_MSG_RECORD_CANNOT_BE_EDITED', $this->translate_module), 1412);
                            exit;
                        }
                        $idComponents = explode('x', $recordId);
                        $recordId = $idComponents[1];
                        $sql = "UPDATE `vtiger_notifications` SET `status` = 1 WHERE `notificationsid` = " . $recordId . " AND `contact_id` = " . $customerId;
                        $sqlResult = $adb->pquery($sql, array());
                        $res = $adb->num_rows($sqlResult);
                        $response->addToResult('message', vtranslate('CAB_MSG_NOTIFICATION_HAS_BEEN_DELETED', $this->translate_module, $portal_language));
                    } else {
                        throw new Exception(vtranslate('CAB_MSG_RECORD_ID_SHOULD_NOT_EMPTY', $this->translate_module), 1412);
                        exit;
                    }
                }
            } else {
                throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module, $portal_language), 1418);
                exit;
            }
            return $response;
        }
    }

}
