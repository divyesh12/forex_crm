<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class LiveAccount_Edit_View extends Vtiger_Edit_View {

    public function preProcess(Vtiger_Request $request, $display = true) {
        $record = $request->get('record');
        $moduleName = $request->getModule();
        if (!empty($record)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $modelData = $recordModel->getData();
            if ($modelData['record_status'] == "Disapproved") {
                $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED') . ' because of ' . $moduleName . ' status are ' . $modelData['record_status'];
                throw new AppException($permission_denied_messege);
            }
        }
        parent::preProcess($request, $display);
    }

//
//    public function process(Vtiger_Request $request) {
//        global $metaTraders_Type, $metaTraderCredentials;
//        $moduleName = $request->getModule();
//        $record = $request->get('record');
//        $viewer = $this->getViewer($request);
//        //  $viewer->assign('metaTraders_Type', $metaTraders_Type);
//        if (!empty($record)) {
//            $recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
//            $modelData = $recordModel->getData();
//            //$viewer->assign('status_', $status['liveaccount_status']);
//            if ($modelData['record_status'] == "Approved" || $modelData['record_status'] == "Disapproved") {
//                throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
//            }
//        }
//        parent::process($request);
//    }
}
