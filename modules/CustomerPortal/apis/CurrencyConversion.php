<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_CurrencyConversion extends CustomerPortal_API_Abstract {

    protected $translate_module = 'CustomerPortal_Client';

    function process(CustomerPortal_API_Request $request) {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $customerId = $this->getActiveCustomer()->id;
            $module = $request->get('module');
            $sub_operation = $request->get('sub_operation');
            if ($sub_operation == 'FetchRateList') {
                $fieldsArray = $request->get('fields');
                $orderBy = $request->get('orderBy');
                $order = $request->get('order');

                $activeFields = CustomerPortal_Utils::getActiveFields($module);

                if (empty($orderBy)) {
                    $orderBy = ' `vtiger_crmentity`.`modifiedtime`';
                } else if (!empty($orderBy)) {
                    $orderBy = str_replace('modifiedtime', 'vtiger_crmentity.modifiedtime', $orderBy);
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
                $where = " AND vtiger_currencyconverter.operation_type = 'Deposit'";

                $sql = "SELECT vtiger_currencyconverter.from_currency, vtiger_currencyconverter.to_currency, vtiger_currencyconverter.conversion_rate, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime, vtiger_currencyconverter.operation_type, vtiger_currencyconverter.currencyconverterid FROM vtiger_currencyconverter  
                INNER JOIN vtiger_crmentity ON vtiger_currencyconverter.currencyconverterid = vtiger_crmentity.crmid 
                WHERE vtiger_crmentity.deleted=0 AND vtiger_currencyconverter.currencyconverterid > 0 " . $where;
                //Count data                                                                 
                $sqlCountResult = $adb->pquery($sql, array());
                $count = $adb->num_rows($sqlCountResult);
                //End                       
                $sql .= $orderby_paging_sql;

                $sqlResult = $adb->pquery($sql, array());
                $numRow = $adb->num_rows($sqlResult);
                $rows = array();
                for ($i = 0; $i < $numRow; $i++) {
                    $rows[$i]['from_currency'] = $adb->query_result($sqlResult, $i, 'from_currency');
                    $rows[$i]['to_currency'] = $adb->query_result($sqlResult, $i, 'to_currency');
                    $rows[$i]['conversion_rate'] = $adb->query_result($sqlResult, $i, 'conversion_rate');
                    $rows[$i]['modifiedtime'] = $adb->query_result($sqlResult, $i, 'modifiedtime');
                }
                $response->addToResult('records', $rows);
                $response->addToResult('count', $count);
            } else if ($sub_operation == 'FetchRate') {
                $result = array();
                $toAmount = 0;
                $jsonValues = $request->get('values');
                $values = json_decode($jsonValues, true);
                $fromCurrency = $values['from_currency'];
                $toCurrency = $values['to_currency'];
                $fromAmount = $values['from_amount'];
                $currencyRate = CustomerPortal_Utils::getCurrencyConvertionRate($fromCurrency, $toCurrency);
                if($currencyRate)
                {
                    $toAmount = $fromAmount * $currencyRate;
                }
                else
                {
                    throw new Exception(vtranslate('CAB_MSG_CONVERSION_NOT_FOUND', $this->translate_module, $portal_language), 1418);
                }
                $result['conversion_rate'] = $currencyRate;
                $result['conversion_amount'] = number_format($toAmount, 4);
                $response->addToResult('records', $result);
            } else {
                throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module, $portal_language), 1418);
                exit;
            }
            return $response;
        }
    }

}
