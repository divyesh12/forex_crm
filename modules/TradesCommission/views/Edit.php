<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/** add file by Reena 06_03_2020 */
class TradesCommission_Edit_View extends Vtiger_Edit_View {

    public function preProcess(Vtiger_Request $request, $display = true) {
        $record = $request->get('record');
        $moduleName = $request->getModule();
        /* @Comment:-Restrict from url Edit when record is not empty 06_03_2020 */
        if (!empty($record)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $modelData = $recordModel->getData();
//            if ($modelData['transaction_status'] == "Approved" || $modelData['transaction_status'] == "Disapproved") {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
//            }
        }
        parent::preProcess($request, $display);
    }

    public function process(Vtiger_Request $request) {
        global $metaTraders_Type, $metaTraderCredentials;
        $moduleName = $request->getModule();
        $record = $request->get('record');
        $viewer = $this->getViewer($request);

        if (!empty($record)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $modelData = $recordModel->getData();
//            if ($modelData['transaction_status'] == "Approved" || $modelData['transaction_status'] == "Disapproved") {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
//            }
        }
        parent::process($request);
    }

}

/*END*/
