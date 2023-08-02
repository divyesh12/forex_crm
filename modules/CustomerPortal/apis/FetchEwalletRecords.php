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

class CustomerPortal_FetchEwalletRecords extends CustomerPortal_API_Abstract {

    protected $translate_module = 'CustomerPortal_Client';

    function process(CustomerPortal_API_Request $request) {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;
        
        if ($current_user) {
            $contactId = $this->getActiveCustomer()->id;
            $module = $request->get('module');
            $moduleLabel = $request->get('moduleLabel');
            $orderBy = $request->get('orderBy');
            $order = $request->get('order');
            $filter = htmlspecialchars_decode($request->get('filter'));
            $activeFields = CustomerPortal_Utils::getActiveFields($module);
            $response_type = $request->get('response_type');
            
            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }

            if (empty($orderBy)) {
                $orderBy = 'modifiedtime';
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
            
            $page = $request->get('page');
            if (empty($page))
            $page = 0;
            $pageLimit = $request->get('pageLimit');
            if (empty($pageLimit))
            $pageLimit = CustomerPortal_Config::$DEFAULT_PAGE_LIMIT;
            $limitClause = sprintf('ORDER BY %s %s LIMIT %s,%s', $orderBy, $order, ($page * $pageLimit), $pageLimit);

            $finalResultArr = CustomerPortal_Utils::getEwalletRecords($contactId, $filter, $limitClause);
            $finalResult = $finalResultArr['data'];
            $count = $finalResultArr['count'];

            if (!empty($response_type) && $response_type == 'List') {
                $response->addToResult('records', $finalResult);
            } else {
                $response->setResult($finalResult);
            }
            $response->addToResult('count', $count);
        }
        return $response;
    }

}
