<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class IBCommissionProfile_Edit_View extends Vtiger_Edit_View {

    public function process(Vtiger_Request $request) {
        global $current_user;
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        $recordModel = $this->record;
        if (!$recordModel) {
            if (!empty($recordId)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
                $getData = $recordModel->getData();
            } else {
                $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            }
            $this->record = $recordModel;
        }

        $viewer = $this->getViewer($request);

        if (!empty($recordId)) {
            $IBCommCombinationResult = IBCommissionProfile_Record_Model::getIBCommCombinationData($recordId);
            $viewer->assign('RECORD_ID', $recordId);
            $viewer->assign('IBCOMMISSION_ITEMS', $IBCommCombinationResult);
        }
        parent::process($request);
    }

    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $moduleEditFile = 'modules.' . $moduleName . '.resources.Edit';
        $jsFileNames = array(
            'modules.' . $moduleName . '.resources.validation',
        );
        $jsFileNames[] = $moduleEditFile;
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }

}
