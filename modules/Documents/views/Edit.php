<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

Class Documents_Edit_View extends Vtiger_Edit_View {

    /**
     *   Add By Divyesh - 13-09-2019*
     * Permission denied when document approved and disapproved
     */
    public function preProcess(Vtiger_Request $request, $display = true) {
        $record = $request->get('record');
        $moduleName = $request->getModule();
        if (!empty($record)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $modelData = $recordModel->getData();
            if ($modelData['record_status'] == "Approved" || $modelData['record_status'] == "Disapproved") {
                $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED') . ' because of ' . $moduleName . ' status are ' . $modelData['record_status'];
                throw new AppException($permission_denied_messege);
            }
        }
        parent::preProcess($request, $display);
    }

    /* end */

    /**
     * Function to get the list of Script models to be included
     * @param Vtiger_Request $request
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $moduleName = $request->getModule();

        $jsFileNames = array(
            "libraries.jquery.ckeditor.ckeditor",
            "libraries.jquery.ckeditor.adapters.jquery",
            'modules.Vtiger.resources.CkEditor',
        );
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }

}

?>
