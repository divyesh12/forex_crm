<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Payments_MassDelete_Action extends Vtiger_MassDelete_Action {

    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    function preProcess(Vtiger_Request $request) {
        return true;
    }

    function postProcess(Vtiger_Request $request) {
        return true;
    }

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        if ($request->get('selected_ids') == 'all' && $request->get('mode') == 'FindDuplicates') {
            $recordIds = Vtiger_FindDuplicate_Model::getMassDeleteRecords($request);
        } else {
            $recordIds = $this->getRecordsListFromRequest($request);
        }
        $cvId = $request->get('viewname');
        foreach ($recordIds as $recordId) {
            if (Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
                $recordModel->delete();
                deleteRecordFromDetailViewNavigationRecords($recordId, $cvId, $moduleName);
            }
        }
        $response = new Vtiger_Response();
        $response->setResult(array('viewname' => $cvId, 'module' => $moduleName));
        $response->emit();
    }

}
