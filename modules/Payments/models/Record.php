<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
require_once('modules/ServiceProviders/ServiceProviders.php');

class Payments_Record_Model extends Vtiger_Record_Model {

    /**
      @Add_by:-Reena Hingol
      @Date:-25_11_19
      @Comment:-edit and delete link from Payments listing page when payment_status is Completed and payment_process is Finish.
     */
    public function checkRecordStatus($record) {
        if ($record) {
            $module = $this->getModule();
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
            $modelData = $recordModel->getData();
            if (($modelData['payment_status'] == "Completed") || ($modelData['payment_status'] == "Rejected") || ($modelData['payment_status'] == "Cancelled")) {
                return false;
            }
        }
        return true;
    }

    public function getPaymentGatewayToAccountDetails($request) {
        global $adb;

        $ewallet_to_tradingaccount = configvar('ewallet_to_tradingaccount');

        $module = $request->get('module');
        $action = $request->get('action');
        $mode = $request->get('mode');

        $payment_operation = $request->get('payment_operation');
        $payment_type = $request->get('payment_type');
        $contactid = $request->get('contactid');
        $record_status = $request->get('record_status');
        $live_currency_code = $request->get('live_currency_code');

        $paymentGetwaysList = Payments_Record_Model::getDepositPaymentGetways();
        //$from_values = array('Skrill' => 'Skrill', 'PayPal' => 'PayPal');
        $from_values = $paymentGetwaysList;

        if ($contactid && $ewallet_to_tradingaccount) {
            $cont_result = $adb->pquery('SELECT vtiger_contactdetails.contact_no FROM vtiger_contactdetails WHERE  vtiger_contactdetails.contactid =? ', array($contactid));
            $cont_result_row = $adb->fetchByAssoc($cont_result);
            $ewallet_no = $cont_result_row['contact_no'];
            $from_values[$ewallet_no] = $ewallet_no;
        }

        $sql = 'SELECT vtiger_liveaccount.liveaccountid,vtiger_liveaccount.account_no FROM vtiger_liveaccount INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid WHERE vtiger_crmentity.deleted = 0 AND vtiger_liveaccount.contactid = ? AND vtiger_liveaccount.record_status = ? AND vtiger_liveaccount.live_currency_code =? ';
        $result = $adb->pquery($sql, array($contactid, $record_status, $live_currency_code));
//        echo "<pre>";
//        print_r($result);
//        exit;
        $num_rows = $adb->num_rows($result);
        if ($num_rows > 0) {
            $to_values = array();
            while ($result_row = $adb->fetchByAssoc($result)) {
                $to_values[$result_row['account_no']] = $result_row['account_no'];
            }
        }
        $return_result = array($from_values, $to_values);
        return $return_result;
    }

    public function getPaymentGatewayToEWalletDetails($request) {
        global $adb;

        $contactid = $request->get('contactid');
        if ($contactid) {
            $recordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
            $modelData = $recordModel->getData();
            $ewallet_no = $modelData['contact_no'];
        }
        $paymentGetwaysList = Payments_Record_Model::getDepositPaymentGetways();
        //$from_values = array('Skrill' => 'Skrill', 'PayPal' => 'PayPal');
        $from_values = $paymentGetwaysList;
        $to_values = array($ewallet_no => $ewallet_no);
        $return_result = array($from_values, $to_values);
        return $return_result;
    }

    public function getAccountToPaymentGatewayDetails($request) {
        global $adb;
        $tradingaccount_to_ewallet = configvar('tradingaccount_to_ewallet');
        $module = $request->get('module');
        $action = $request->get('action');
        $mode = $request->get('mode');

        $payment_operation = $request->get('payment_operation');
        $payment_type = $request->get('payment_type');
        $contactid = $request->get('contactid');
        $record_status = $request->get('record_status');
        $live_currency_code = $request->get('live_currency_code');

        $sql = 'SELECT vtiger_liveaccount.liveaccountid,vtiger_liveaccount.account_no FROM vtiger_liveaccount INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid WHERE vtiger_crmentity.deleted = 0 AND vtiger_liveaccount.contactid = ? AND vtiger_liveaccount.record_status = ? AND vtiger_liveaccount.live_currency_code =? ';
        $result = $adb->pquery($sql, array($contactid, $record_status, $live_currency_code));
        $num_rows = $adb->num_rows($result);
        if ($num_rows > 0) {
            $from_values = array();
            while ($result_row = $adb->fetchByAssoc($result)) {
                $from_values[$result_row['account_no']] = $result_row['account_no'];
            }
        }

        $paymentGetwaysList = Payments_Record_Model::getWithdrawalPaymentGetways();
//        echo "<pre>";
//        print_r($paymentGetwaysList);
//        exit;
        //$to_values = array('Skrill' => 'Skrill', 'PayPal' => 'PayPal');
        $to_values = $paymentGetwaysList;
        if ($contactid && $tradingaccount_to_ewallet) {
            $cont_result = $adb->pquery('SELECT vtiger_contactdetails.contact_no FROM vtiger_contactdetails WHERE  vtiger_contactdetails.contactid =? ', array($contactid));
            $cont_result_row = $adb->fetchByAssoc($cont_result);
            $ewallet_no = $cont_result_row['contact_no'];
            $to_values[$ewallet_no] = $ewallet_no;
        }

        $return_result = array($from_values, $to_values);
        return $return_result;
    }

    public function getEWalletToPaymentGatewayDetails($request) {
        global $adb;
        $contactid = $request->get('contactid');
        if ($contactid) {
            $recordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
            $modelData = $recordModel->getData();
            $ewallet_no = $modelData['contact_no'];
        }
        $from_values = array($ewallet_no => $ewallet_no);

        $paymentGetwaysList = Payments_Record_Model::getWithdrawalPaymentGetways();
        //$to_values = array('Skrill' => 'Skrill', 'PayPal' => 'PayPal');
        $to_values = $paymentGetwaysList;
        $return_result = array($from_values, $to_values);
        return $return_result;
    }

    public function getEWalletToEWalletDetails($request) {
        global $adb;
        $contactid = $request->get('contactid');
        if ($contactid) {
            $recordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
            $modelData = $recordModel->getData();
            $ewallet_no = $modelData['contact_no'];
        }
        $from_values = array($ewallet_no => $ewallet_no);
        $to_values = array();
        $return_result = array($from_values, $to_values);
        return $return_result;
    }

    public function getAccountToAccountDetails($request) {
        global $adb;

        $module = $request->get('module');
        $action = $request->get('action');
        $mode = $request->get('mode');

        $payment_operation = $request->get('payment_operation');
        $payment_type = $request->get('payment_type');
        $contactid = $request->get('contactid');
        $record_status = $request->get('record_status');
        $live_currency_code = $request->get('live_currency_code');
        $payment_from = $request->get('payment_from');

        $sql = 'SELECT vtiger_liveaccount.liveaccountid,vtiger_liveaccount.account_no FROM vtiger_liveaccount INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid WHERE vtiger_crmentity.deleted = 0 AND vtiger_liveaccount.contactid = ? AND vtiger_liveaccount.record_status = ? AND vtiger_liveaccount.live_currency_code =? ';
        $result = $adb->pquery($sql, array($contactid, $record_status, $live_currency_code));
//        echo "<pre>";
//        print_r($result);
//        exit;
        $num_rows = $adb->num_rows($result);
        if ($num_rows > 0) {
            $from_values = array();
            while ($result_row = $adb->fetchByAssoc($result)) {
                $from_values[$result_row['account_no']] = $result_row['account_no'];
            }
        }

//        $to_values = array();
//        if ($payment_from) {
//            $to_values = array_diff($from_values, array($payment_from));
//        }
//        $from_values = array('12345' => '12345', '12346' => '12346', '12347' => '12347', '12348' => '12348', '12349' => '12349', '12350' => '12350',);
        $to_values = $from_values;

        $return_result = array($from_values, $to_values);
        return $return_result;
    }

    public function getLiveAccountDetails($account_no, $contactid) {
        global $adb;
        
        $sql = 'SELECT vtiger_liveaccount.live_metatrader_type, vtiger_liveaccount.live_label_account_type, vtiger_liveaccount.live_currency_code FROM vtiger_liveaccount INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid WHERE vtiger_crmentity.deleted = 0 AND vtiger_liveaccount.account_no = ? AND vtiger_liveaccount.contactid = ?';
        $result = $adb->pquery($sql, array($account_no, $contactid));
        $num_rows = $adb->num_rows($result);
        if ($num_rows > 0) {
            $result_row = $adb->fetchByAssoc($result);
        }
        return $result_row;
    }

    public function getDepositPaymentGetways() {
        $provider = ServiceProvidersManager::getActiveProviderInstance();
        $paymentGetways = array();
        for ($i = 0; $i < count($provider); $i++) {
            $deposit_supported = $provider[$i]->parameters['deposit_supported'];
            $title = $provider[$i]->parameters['title'];
            if ($provider[$i]::PROVIDER_TYPE == 2 && $deposit_supported == 'Yes' && $provider[$i]::getName() != 'Wallet') {
                $payment_name = $provider[$i]::getName();
                // $paymentGetways[$payment_name] = $payment_name;
                $paymentGetways[$title] = $title;
            }
        }

        return $paymentGetways;
    }

    public function getWithdrawalPaymentGetways() {
        $provider = ServiceProvidersManager::getActiveProviderInstance();
        $paymentGetways = array();
        for ($i = 0; $i < count($provider); $i++) {
            $withdrawal_supported = $provider[$i]->parameters['withdrawal_supported'];
            $title = $provider[$i]->parameters['title'];
            if ($provider[$i]::PROVIDER_TYPE == 2 && $withdrawal_supported == 'Yes' && $provider[$i]::getName() != 'Wallet') {
                $payment_name = $provider[$i]::getName();
                //  $paymentGetways[$payment_name] = $payment_name;
                $paymentGetways[$title] = $title;
            }
        }
        return $paymentGetways;
    }

//    public function getWalletBalance($contactid) {
//        global $adb;
//
//        $deposit_query = 'SELECT CASE WHEN(SUM(amount) IS NULL ) THEN "0" ELSE SUM(amount) END as total_deposit FROM `vtiger_ewallet` INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =  vtiger_ewallet.ewalletid  WHERE vtiger_crmentity.deleted = 0 AND  vtiger_ewallet.`transaction_type` = ? AND vtiger_ewallet.contactid = ?';
//
//        $deposit_result = $adb->pquery($deposit_query, array(1, $contactid));
//        $deposit_row = $adb->fetchByAssoc($deposit_result);
//        $total_deposit = (float) $deposit_row['total_deposit'];
//
//        $withdraw_query = 'SELECT CASE WHEN(SUM(amount) IS NULL ) THEN "0" ELSE SUM(amount) END  as total_withdraw FROM `vtiger_ewallet` INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =  vtiger_ewallet.ewalletid  WHERE vtiger_crmentity.deleted = 0 AND vtiger_ewallet.`transaction_type` = ? AND vtiger_ewallet.contactid = ?';
//        $withdraw_result = $adb->pquery($withdraw_query, array(2, $contactid));
//        $withdraw_row = $adb->fetchByAssoc($withdraw_result);
//        $total_withdraw = (float) $withdraw_row['total_withdraw'];
//
//        $total_balance = (float) ($total_deposit - $total_withdraw);
//        return $total_balance;
//    }

    public function UpdateFields($payment_status, $payment_process, $failure_reason, $recordId) {
        global $adb;
        $adb->pquery("UPDATE vtiger_payments SET payment_status=?,payment_process=?,failure_reason=? WHERE paymentsid=?", array($payment_status, $payment_process, $failure_reason, $recordId));
    }

//    public function updateChangedon($date_time, $recordId) {
//        global $adb;
////        echo "UPDATE `vtiger_modtracker_basic` SET `changedon`='" . $date_time . "'  WHERE crmid =" . $recordId;
////        exit;
//        $adb->pquery("UPDATE `vtiger_modtracker_basic` SET `changedon`='" . $date_time . "'  WHERE crmid =" . $recordId, array());
//    }

    /* @Added By:- Reena Hingol
     * @Date:-25-11-2019
     * @Comment:-getContactDetails for create record in Ewallet while Payments with InternalTransfer
     * */

    public function getContactId($ewallet_no) {
        global $adb;
        $result = $adb->pquery('SELECT contactid FROM vtiger_contactdetails WHERE contact_no = ?', array($ewallet_no));
        $row = $adb->fetchByAssoc($result);
        $contactid = $row['contactid'];
        return $contactid;
    }

    public function checkWalletNoExist($ewallet_no) {
        global $adb;
        $result = $adb->pquery('SELECT contactid FROM vtiger_contactdetails WHERE contact_no = ?', array($ewallet_no));
        $row = $adb->fetchByAssoc($result);
        if (count($row) == 0) {
            return false;
        }
        return true;
    }

    function getDownloadFileURL($documentid) {
        $fileDetails = Payments_Record_Model::getFileDetails($documentid);
        $document_download_URL = 'index.php?module=Documents&action=DownloadFile&record=' . $documentid . '&fileid=' . $fileDetails['attachmentsid'] . '&name=' . $fileDetails['name'];
        $documentDetails = array('document_name' => $fileDetails['name'], 'download_URL' => $document_download_URL);
        return $documentDetails;
    }

    function getFileDetails($documentid) {
        $db = PearDatabase::getInstance();
        $fileDetails = array();
        $result = $db->pquery("SELECT * FROM vtiger_attachments 	INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid WHERE crmid = ?", array($documentid));
        if ($db->num_rows($result)) {
            $fileDetails = $db->query_result_rowdata($result);
        }
        return $fileDetails;
    }
    
    function getReceiptDocId($customData = '') {
        global $log;
        $db = PearDatabase::getInstance();
        $documentId = '';
        
        if(!empty($customData))
        {
            $json_parameters = Zend_Json::decode(decode_html($customData));
            foreach ($json_parameters as $key => $value)
            {
                if ($value['type'] == 'file')
                {
                    $documentId = end(explode("x", $value['value']));
                }
            }
        }
        return $documentId;
    }

//    public function fieldsUpdateAfterWorkflowExecute() {
//        //    echo "<pre>";
////    print_r($entityData);
////    exit;
//        $tmpFiles = $_FILES;
//        unset($_FILES);
//        global $adb, $current_user, $default_charset, $default_timezone;
//        $referenceModuleUpdated = array();
//        $util = new VTWorkflowUtils();
//        $util->adminUser();
//
//        $moduleName = $entityData->getModuleName();
//        $entityId = $entityData->getId();
//        $recordId = vtws_getIdComponents($entityId);
//        $recordId = $recordId[1];
//
//        $moduleHandler = vtws_getModuleHandlerFromName($moduleName, $current_user);
//        $handlerMeta = $moduleHandler->getMeta();
//        $moduleFields = $handlerMeta->getModuleFields();
//
//        require_once('data/CRMEntity.php');
//        $focus = CRMEntity::getInstance($moduleName);
//        $focus->id = $recordId;
//        $focus->mode = 'edit';
//        $focus->retrieve_entity_info($recordId, $moduleName);
//        $focus->clearSingletonSaveFields();
//
//        $util->loggedInUser();
//
//        // The data is transformed from db format to user format, there is a chance that date and currency fields might get tracked for changes which should be avoided
//        $focus->column_fields->pauseTracking();
//        $focus->column_fields = DataTransform::sanitizeDateFieldsForInsert($focus->column_fields, $handlerMeta);
//        $focus->column_fields = DataTransform::sanitizeCurrencyFieldsForInsert($focus->column_fields, $handlerMeta);
//        $entityFields = $referenceEntityFields = false;
//        $focus->column_fields->resumeTracking();
//
//        $payment_operation = $entityData->get('payment_operation');
//        $payment_process = $entityData->get('payment_process');
//        $payment_status = $entityData->get('payment_status');
//        $payment_type = $entityData->get('payment_type');
//
////    if ($payment_operation == 'Deposit' && $payment_type == 'P2A' && $payment_process == 'PSP' && $payment_status == 'Confirmed') {
////        $entityData->data['payment_process'] = 'Account';
////        $entityData->data['payment_status'] = 'InProgress';
////        $focus->column_fields['payment_process'] = 'Account';
////        $focus->column_fields['payment_status'] = 'InProgress';
////    }
////    if ($payment_operation == 'Deposit' && $payment_type == 'P2E' && $payment_process == 'PSP' && $payment_status == 'Confirmed') {
////
////        $entityData->data['payment_process'] = 'Wallet';
////        $entityData->data['payment_status'] = 'InProgress';
////        $focus->column_fields['payment_process'] = 'Wallet';
////        $focus->column_fields['payment_status'] = 'InProgress';
////    }
//        if ($payment_operation == 'Deposit' && $payment_type == 'P2A' && $payment_process == 'Account' && $payment_status == 'Confirmed') {
//            $entityData->data['payment_process'] = 'Finish';
//            $entityData->data['payment_status'] = 'Completed';
//            $focus->column_fields['payment_process'] = 'Finish';
//            $focus->column_fields['payment_status'] = 'Completed';
//        }
//
//        $_REQUEST['file'] = '';
//        $_REQUEST['ajxaction'] = '';
//
//        // Added as Mass Edit triggers workflow and date and currency fields are set to user format
//        // When saving the information in database saveentity API should convert to database format
//        // and save it. But it converts in database format only if that date & currency fields are
//        // changed(massedit) other wise they wont be converted thereby changing the values in user
//        // format, CRMEntity.php line 474 has the login to check wheather to convert to database format
//        $actionName = $_REQUEST['action'];
//        $_REQUEST['action'] = '';
//
//        // For workflows update field tasks is deleted all the lineitems.
//        $focus->isLineItemUpdate = false;
//
//        //For workflows we need to update all the fields irrespective if the logged in user has the
//        //permission or not.
//        $focus->isWorkFlowFieldUpdate = true;
//        $changedFields = $focus->column_fields->getChanged();
//        if (count($changedFields) > 0) {
//            // save only if any field is changed
//            $focus->saveentity($moduleName);
//        }
////    echo "<pre>";
////    echo count($changedFields);
////    print_r($changedFields);
////    exit;
//        // if (count($changedFields) > 0) {
//        //  if ($VTIGER_BULK_SAVE_MODE) {
//        $em = new VTEventsManager($adb);
////    // Initialize Event trigger cache
//        $em->initTriggerCache();
//        $entityData = VTEntityData::fromCRMEntity($focus);
//        // save only if any field is changed
//        //$em->triggerEvent("vtiger.entity.aftersave.modifiable", $entityData);
//        $em->triggerEvent("vtiger.entity.aftersave.modifiable", $entityData);
//        $em->triggerEvent("vtiger.entity.aftersave", $entityData);
//        $em->triggerEvent("vtiger.entity.aftersave.final", $entityData);
//        //$focus->saveentity($moduleName);
//        //}
////    if ($em) {
////        //Event triggering code
////        $em->triggerEvent("vtiger.entity.aftersave", $entityData);
////        $em->triggerEvent("vtiger.entity.aftersave.final", $entityData);
////        //Event triggering code ends
////    }
//        $_REQUEST['action'] = $actionName;
//        $_FILES = $tmpFiles;
//    }
//
//    public function fieldsUpdateBeforeWorkflowExecute() {
//        //    echo "<pre>";
////    print_r($entityData);
////    exit;
//        $tmpFiles = $_FILES;
//        unset($_FILES);
//        global $adb, $current_user, $default_charset, $default_timezone;
//        $referenceModuleUpdated = array();
//        $util = new VTWorkflowUtils();
//        $util->adminUser();
//
//        $moduleName = $entityData->getModuleName();
//        $entityId = $entityData->getId();
//        $recordId = vtws_getIdComponents($entityId);
//        $recordId = $recordId[1];
//
//        $moduleHandler = vtws_getModuleHandlerFromName($moduleName, $current_user);
//        $handlerMeta = $moduleHandler->getMeta();
//        $moduleFields = $handlerMeta->getModuleFields();
//
//        require_once('data/CRMEntity.php');
//        $focus = CRMEntity::getInstance($moduleName);
//        $focus->id = $recordId;
//        $focus->mode = 'edit';
//        $focus->retrieve_entity_info($recordId, $moduleName);
//        $focus->clearSingletonSaveFields();
//
//        $util->loggedInUser();
//
//        // The data is transformed from db format to user format, there is a chance that date and currency fields might get tracked for changes which should be avoided
//        $focus->column_fields->pauseTracking();
//        $focus->column_fields = DataTransform::sanitizeDateFieldsForInsert($focus->column_fields, $handlerMeta);
//        $focus->column_fields = DataTransform::sanitizeCurrencyFieldsForInsert($focus->column_fields, $handlerMeta);
//        $entityFields = $referenceEntityFields = false;
//        $focus->column_fields->resumeTracking();
//
//        $payment_operation = $entityData->get('payment_operation');
//        $payment_process = $entityData->get('payment_process');
//        $payment_status = $entityData->get('payment_status');
//        $payment_type = $entityData->get('payment_type');
//
////    if ($payment_operation == 'Deposit' && $payment_type == 'P2A' && $payment_process == 'PSP' && $payment_status == 'Confirmed') {
////        $entityData->data['payment_process'] = 'Account';
////        $entityData->data['payment_status'] = 'InProgress';
////        $focus->column_fields['payment_process'] = 'Account';
////        $focus->column_fields['payment_status'] = 'InProgress';
////    }
////    if ($payment_operation == 'Deposit' && $payment_type == 'P2E' && $payment_process == 'PSP' && $payment_status == 'Confirmed') {
////
////        $entityData->data['payment_process'] = 'Wallet';
////        $entityData->data['payment_status'] = 'InProgress';
////        $focus->column_fields['payment_process'] = 'Wallet';
////        $focus->column_fields['payment_status'] = 'InProgress';
////    }
//        if ($payment_operation == 'Deposit' && $payment_type == 'P2A' && $payment_process == 'Account' && $payment_status == 'Confirmed') {
//            $entityData->data['payment_process'] = 'Finish';
//            $entityData->data['payment_status'] = 'Completed';
//            $focus->column_fields['payment_process'] = 'Finish';
//            $focus->column_fields['payment_status'] = 'Completed';
//        }
//
//        $_REQUEST['file'] = '';
//        $_REQUEST['ajxaction'] = '';
//
//        // Added as Mass Edit triggers workflow and date and currency fields are set to user format
//        // When saving the information in database saveentity API should convert to database format
//        // and save it. But it converts in database format only if that date & currency fields are
//        // changed(massedit) other wise they wont be converted thereby changing the values in user
//        // format, CRMEntity.php line 474 has the login to check wheather to convert to database format
//        $actionName = $_REQUEST['action'];
//        $_REQUEST['action'] = '';
//
//        // For workflows update field tasks is deleted all the lineitems.
//        $focus->isLineItemUpdate = false;
//
//        //For workflows we need to update all the fields irrespective if the logged in user has the
//        //permission or not.
//        $focus->isWorkFlowFieldUpdate = true;
//        $changedFields = $focus->column_fields->getChanged();
//        if (count($changedFields) > 0) {
//            // save only if any field is changed
//            $focus->saveentity($moduleName);
//        }
////    echo "<pre>";
////    echo count($changedFields);
////    print_r($changedFields);
////    exit;
//        // if (count($changedFields) > 0) {
//        //  if ($VTIGER_BULK_SAVE_MODE) {
//        $em = new VTEventsManager($adb);
////    // Initialize Event trigger cache
//        $em->initTriggerCache();
//        $entityData = VTEntityData::fromCRMEntity($focus);
//        // save only if any field is changed
//        //$em->triggerEvent("vtiger.entity.aftersave.modifiable", $entityData);
//        $em->triggerEvent("vtiger.entity.aftersave.modifiable", $entityData);
//        $em->triggerEvent("vtiger.entity.aftersave", $entityData);
//        $em->triggerEvent("vtiger.entity.aftersave.final", $entityData);
//        //$focus->saveentity($moduleName);
//        //}
////    if ($em) {
////        //Event triggering code
////        $em->triggerEvent("vtiger.entity.aftersave", $entityData);
////        $em->triggerEvent("vtiger.entity.aftersave.final", $entityData);
////        //Event triggering code ends
////    }
//        $_REQUEST['action'] = $actionName;
//        $_FILES = $tmpFiles;
//    }
}
