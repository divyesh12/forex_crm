<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_Record_Model extends Vtiger_Record_Model {

    /**
     * Function returns the url for create event
     * @return <String>
     */
    function getCreateEventUrl() {
        $calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
        return $calendarModuleModel->getCreateEventRecordUrl() . '&contact_id=' . $this->getId();
    }

    /**
     * Function returns the url for create todo
     * @return <String>
     */
    function getCreateTaskUrl() {
        $calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
        return $calendarModuleModel->getCreateTaskRecordUrl() . '&contact_id=' . $this->getId();
    }

    /**
     * Function to get List of Fields which are related from Contacts to Inventory Record
     * @return <array>
     */
    public function getInventoryMappingFields() {
        return array(
            array('parentField' => 'account_id', 'inventoryField' => 'account_id', 'defaultValue' => ''),
            //Billing Address Fields
            array('parentField' => 'mailingcity', 'inventoryField' => 'bill_city', 'defaultValue' => ''),
            array('parentField' => 'mailingstreet', 'inventoryField' => 'bill_street', 'defaultValue' => ''),
            array('parentField' => 'mailingstate', 'inventoryField' => 'bill_state', 'defaultValue' => ''),
            array('parentField' => 'mailingzip', 'inventoryField' => 'bill_code', 'defaultValue' => ''),
            array('parentField' => 'mailingcountry', 'inventoryField' => 'bill_country', 'defaultValue' => ''),
            array('parentField' => 'mailingpobox', 'inventoryField' => 'bill_pobox', 'defaultValue' => ''),
            //Shipping Address Fields
            array('parentField' => 'otherstreet', 'inventoryField' => 'ship_street', 'defaultValue' => ''),
            array('parentField' => 'othercity', 'inventoryField' => 'ship_city', 'defaultValue' => ''),
            array('parentField' => 'otherstate', 'inventoryField' => 'ship_state', 'defaultValue' => ''),
            array('parentField' => 'otherzip', 'inventoryField' => 'ship_code', 'defaultValue' => ''),
            array('parentField' => 'othercountry', 'inventoryField' => 'ship_country', 'defaultValue' => ''),
            array('parentField' => 'otherpobox', 'inventoryField' => 'ship_pobox', 'defaultValue' => '')
        );
    }

    /**
     *  Add By Divyesh Chothani
     * Date:- 12-12-2019
     * Comment:- Field Readonly when record create and  record update
     */
    public function get_readonlyFields($source) {

        if ($source == 'CRM') {
            $createRecordReadonlyFields = array('is_first_time_deposit');
            $editRecordReadonlyFields = array();
//            $createRecordReadonlyFields = array('is_first_time_deposit', 'birthday');
//            $editRecordReadonlyFields = array('email', 'leadsource');
        } elseif ($source == 'CUSTOMERPORTAL') {
            $createRecordReadonlyFields = array();
            $editRecordReadonlyFields = array();
        }
        $readonlyFields = array('edit_fields' => $editRecordReadonlyFields, 'create_fields' => $createRecordReadonlyFields);
        return $readonlyFields;
    }

    function isIBStatusApproved($contactid) {
        global $adb;
        if ($contactid) {
            $sql = "SELECT vtiger_contactdetails.record_status FROM vtiger_contactdetails WHERE  vtiger_contactdetails.contactid = '" . $contactid . "' ";
            $result = $adb->pquery($sql, array());
            $ib_status = $adb->query_result($result, 0, 'record_status');
            return $ib_status;
        }
    }

    public function getPaymentSummaryWidgetData($recordId) {
        global $adb;

        $deposit_sql = "SELECT vtiger_payments.payment_from as payment_gateway_name,SUM(`amount`) AS total_deposit FROM `vtiger_payments` INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_payments.paymentsid  WHERE vtiger_crmentity.deleted = 0 AND vtiger_payments.`payment_operation` = ? AND vtiger_payments.contactid= ? AND vtiger_payments.payment_status = ? GROUP BY vtiger_payments.payment_from;";
        $deposit_result = $adb->pquery($deposit_sql, array('Deposit', $recordId,'Completed'));
        $data1 = array();
        while ($deposit_row = $adb->fetchByAssoc($deposit_result)) {
            $data1[] = $deposit_row;
        }

        $withdrw_sql = "SELECT vtiger_payments.payment_to as payment_gateway_name,SUM(`amount`) AS total_withdrw FROM `vtiger_payments` INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_payments.paymentsid  WHERE vtiger_crmentity.deleted = 0 AND vtiger_payments.`payment_operation` = ? AND vtiger_payments.contactid= ? AND vtiger_payments.payment_status = ? GROUP BY vtiger_payments.payment_to;";
        $withdrw_result = $adb->pquery($withdrw_sql, array('Withdrawal', $recordId,'Completed'));
        $data2 = array();
        while ($withdrw_row = $adb->fetchByAssoc($withdrw_result)) {
            $data2[] = $withdrw_row;
        }
        $data = array_merge($data1, $data2);
        //echo "<pre>"; print_r($data);
        $final_result = array();
        foreach($data as $k => $value){
            $final_result[$value['payment_gateway_name']]['total_deposit'] += $value['total_deposit'];
            $final_result[$value['payment_gateway_name']]['total_withdrw'] += $value['total_withdrw'];
        }
        return $final_result;
    }

    public function getContactSummaryCount($contactid, $currency) {
        global $adb;
        $total_deposit = 0;
        $total_withdrawal = 0;
        $total_live_account = 0;
        $total_demo_account = 0;
        $total_lots_contact = 0;

        $live_metatrader_type = array();
        $provider = ServiceProvidersManager::getActiveProviderInstance();
        for ($i = 0; $i < count($provider); $i++) {
            if ($provider[$i]::PROVIDER_TYPE == 1) {
                $live_metatrader_type[] = $provider[$i]->parameters['title'];
            }
        }
        $meta_trader_type_string = '"' . implode('", "', $live_metatrader_type) . '"'; 

        //Get Total Live Accounts
        $sql = "SELECT COUNT(1) AS `total_live_account` FROM `vtiger_liveaccount` AS `l` INNER JOIN `vtiger_crmentity`  AS `c` ON `c`.`crmid` = `l`.`liveaccountid` WHERE `l`.`account_no` != '' AND `l`.`account_no` != 0 AND `l`.`record_status`= 'Approved' AND `l`.`live_currency_code`= '" . $currency . "' AND `c`.`deleted` = 0 AND `l`.`contactid` = " . $contactid;
        $sqlResult = $adb->pquery($sql, array());
        $numRow = $adb->num_rows($sqlResult);
        if ($numRow > 0) {
            $total_live_account = $adb->query_result($sqlResult, 0, 'total_live_account');
        }

        //Get Total Demo Accounts
        $sql = "SELECT COUNT(1) AS `total_demo_account` FROM `vtiger_demoaccount` AS `d` INNER JOIN `vtiger_crmentity`  AS `c` ON `c`.`crmid` = `d`.`demoaccountid` WHERE `d`.`account_no` != '' AND `d`.`account_no` != 0 AND `d`.`demo_currency_code` = '" . $currency . "' AND `c`.`deleted` = 0 AND `d`.`contactid` = " . $contactid;
        $sqlResult = $adb->pquery($sql, array());
        $numRow = $adb->num_rows($sqlResult);
        if ($numRow > 0) {
            $total_demo_account = $adb->query_result($sqlResult, 0, 'total_demo_account');
        }

        // Get Total Volume
        if (!empty($live_metatrader_type)) {
            foreach ($live_metatrader_type as $key => $value) {
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($value);
                if (empty($provider)) {
                    throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'LiveAccount', $portal_language), 1416);
                }
                $trades_query = " IN(SELECT `l`.`account_no` FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c` ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = " . $contactid . " AND `l`.`live_currency_code` = '" . $currency . "' AND `c`.`deleted` = 0)";
                $lots_query = $provider->getTotalVolumeAndProfitLossForMainDashboard();
                $lots_query = $lots_query . $trades_query;
                $sqlResult = $adb->pquery($lots_query, array());
                $numRow = $adb->num_rows($sqlResult);
                if ($numRow > 0) {
                    $total_lots_contact = $total_lots_contact + $adb->query_result($sqlResult, 0, 'total_volume');
                }
            }
        }

        $querySum = "SELECT SUM(amount) as total_amount From vtiger_payments as p INNER JOIN vtiger_crmentity as c ON c.crmid = p.paymentsid WHERE p.contactid = " . $contactid . " AND p.payment_status = 'Completed' AND c.deleted = 0 AND p.payment_currency = '" . $currency . "'";

        /* Total Deposit And Withdrawal */
        $depositType = ' AND p.payment_type = "P2A"';
        $deposit_query = $querySum . $depositType;
        $sqlResult = $adb->pquery($deposit_query, array());
        $numRow = $adb->num_rows($sqlResult);
        if ($numRow > 0) {
            $total_deposit = $total_deposit + $adb->query_result($sqlResult, 0, 'total_amount');
        }
        
        $withdrawalType = '  AND p.payment_type = "A2P"';
        $withdrawal_query = $querySum . $withdrawalType;
        $sqlResult = $adb->pquery($withdrawal_query, array());
        $numRow = $adb->num_rows($sqlResult);
        if ($numRow > 0) {
            $total_withdrawal = $total_withdrawal + ABS($adb->query_result($sqlResult, 0, 'total_amount'));
        }
            
        $rcordModelFields = array('total_deposit' => $total_deposit, 'total_withdrawal' => $total_withdrawal, 'total_live_account' => $total_live_account, 'total_demo_account' => $total_demo_account, 'total_lots_contact' => $total_lots_contact);
        return $rcordModelFields;
    }
}
