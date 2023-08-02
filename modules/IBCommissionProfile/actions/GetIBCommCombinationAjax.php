<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class IBCommissionProfile_GetIBCommCombinationAjax_Action extends Settings_Vtiger_Index_Action {

    public function process(Vtiger_Request $request) {
        $recordId = $request->get('record');
        $moduleName = $request->get('module');

        $live_metatrader_type = $request->get('live_metatrader_type');
        $ibcommission_level = $request->get('ibcommission_level');
        $security = $request->get('security');
        $securityCount = is_array($security) ? count($security) : 0;

        $live_label_account_type = $request->get('live_label_account_type');
        $live_currency_code = $request->get('live_currency_code');
        $ib_commission_type = $request->get('ib_commission_type');
        $ib_commission_value = $request->get('ib_commission_value');
        $symbols = $request->get('symbol');
        $symbolCount = is_array($symbols) ? count($symbols) : 0;

//        $LabelAccountTypeCurrencyData = $this->getLabelAccountTypeCurrency($live_metatrader_type, $live_label_account_type, $live_currency_code);
        $accounMappings = $this->getLiveAccountMapping($live_metatrader_type, $live_label_account_type, $live_currency_code);
        $IBCommCombinationResult = array();
        foreach ($accounMappings as $mapping) {
            $obj = array('live_metatrader_type' => $live_metatrader_type, 'ibcommission_level' => $ibcommission_level, 'security' => "", 'symbol' => "", 'live_label_account_type' => $mapping['live_label_account_type'], 'live_currency_code' => $mapping['live_currency_code'], 'ib_commission_type' => $ib_commission_type, 'ib_commission_value' => $ib_commission_value);
            if (empty($security) && $ib_commission_type == "PIP") {
            } else if (empty($security) || $ib_commission_type == "DEPOSIT") {
                $IBCommCombinationResult[] = $obj;
            } else if ($symbolCount > 0 && $securityCount === 1) {
                foreach ($symbols as $symbol) {
                    if(!empty($security[0]) && !empty($symbol))
                    {
                        $obj ['security'] = $security[0];
                        $obj ['symbol'] = $symbol;
                        $IBCommCombinationResult[] = $obj;
                    }
                }
            } else {
                foreach ($security as $sec) {
                    if(!empty($sec))
                    {
                        $obj ['security'] = $sec;
                        $IBCommCombinationResult[] = $obj;
                    }
                }
            }
        }
        $response = new Vtiger_Response();
        $response->setResult($IBCommCombinationResult);
        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }

    public function getLabelAccountTypeCurrency($server_type, $label_account_type, $currency) {
        global $adb;

        $string_label_account_type = implode("','", $label_account_type);
        $string_currency = implode("','", $currency);
        $query = "SELECT live_currency_code,live_label_account_type FROM  `vtiger_accountmapping`  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_accountmapping.accountmappingid  WHERE vtiger_crmentity.deleted = 0 AND `live_metatrader_type`  = '" . $server_type . "' AND   `live_label_account_type` IN ('" . $string_label_account_type . "') AND `live_currency_code`  IN ('" . $string_currency . "')  ";
//exit;
        $result = $adb->pquery($query, array());
        $num_rows = $adb->num_rows($result);
        $row_result = array();
        if ($num_rows > 0) {
            while ($row = $adb->fetchByAssoc($result)) {
                $account_type[] = $row['live_label_account_type'];
                $live_currency_code[] = $row['live_currency_code'];
            }
            $row_result = array('live_label_account_type' => array_unique($account_type), 'live_currency_code' => array_unique($live_currency_code));
        }
        return $row_result;
    }

    public function getLiveAccountMapping($server_type, $label_account_type, $currency) {
        global $adb;
        $string_label_account_type = implode("','", $label_account_type);
        $string_currency = implode("','", $currency);
        $query = "SELECT live_currency_code,live_label_account_type FROM  `vtiger_accountmapping`  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_accountmapping.accountmappingid  WHERE vtiger_crmentity.deleted = 0 AND `live_metatrader_type`  = '" . $server_type . "' AND   `live_label_account_type` IN ('" . $string_label_account_type . "') AND `live_currency_code`  IN ('" . $string_currency . "')  ";
//exit;
        $result = $adb->pquery($query, array());
        $num_rows = $adb->num_rows($result);
        $row_result = array();
        if ($num_rows > 0) {
            while ($row = $adb->fetchByAssoc($result)) {
                $row_result[] = $row;
            }
        }
        return $row_result;
    }

}
