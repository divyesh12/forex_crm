<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class LeverageHistory_Edit_View extends Vtiger_Edit_View {

    public function preProcess(Vtiger_Request $request, $display = true) {
        $record = $request->get('record');
        $moduleName = $request->getModule();
        if (!empty($record)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $modelData = $recordModel->getData();
            if ($modelData['record_status'] == "Disapproved" || $modelData['record_status'] == "Approved" || $modelData['record_status'] == "Cancelled") {
                $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED') . ' because of ' . $moduleName . ' status are ' . $modelData['record_status'];
                throw new AppException($permission_denied_messege);
            }
        }
        parent::preProcess($request, $display);
    }

}
