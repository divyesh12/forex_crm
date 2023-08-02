<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class IBCommissionProfile_CommissionCombitionForm_View extends Vtiger_IndexAjax_View {

    function __construct() {
        parent::__construct();
        // $this->exposeMethod('changeLeverage');
        $this->exposeMethod('addCommissionCombition');
        $this->exposeMethod('updateCommissionCombition');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    public function addCommissionCombition(Vtiger_Request $request) {
        echo "asdfdsaf";
        exit;
        global $adb;
        $viewer = $this->getViewer($request);
        $module = $request->get('module');
        $recordId = $request->get('recordId');
//        if ($recordId && isset($recordId)) {
//            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
//            $modelData = $recordModel->getData();
//            $metatrader_type = $modelData['live_metatrader_type'];
//        }
        $viewer->assign('MODULE', $module);
//        $viewer->assign('RECORD', $recordId);
//        $viewer->assign('METATRADER_TYPE', $metatrader_type);
        $viewer->view('ChangePassword.tpl', $module);
    }

    public function updateCommissionCombition(Vtiger_Request $request) {
        global $adb;
        $viewer = $this->getViewer($request);
        $module = $request->get('module');
//        $recordId = $request->get('recordId');
//        if ($recordId && isset($recordId)) {
//            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
//            $modelData = $recordModel->getData();
//            $metatrader_type = $modelData['live_metatrader_type'];
//        }
        $viewer->assign('MODULE', $module);
//        $viewer->assign('RECORD', $recordId);
//        $viewer->assign('METATRADER_TYPE', $metatrader_type);
        $viewer->view('ChangeInvestorPassword.tpl', $module);
    }

}

/*  END */
