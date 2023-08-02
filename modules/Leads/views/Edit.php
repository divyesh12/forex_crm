<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Leads_Edit_View extends Vtiger_Edit_View {

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $recordModel = $this->record;
        if (!$recordModel) {
            if (!empty($recordId)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            } else {
                $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            }
        }

        $viewer = $this->getViewer($request);

        $salutationFieldModel = Vtiger_Field_Model::getInstance('salutationtype', $recordModel->getModule());
        $salutationValue = $request->get('salutationtype');
        if (!empty($salutationValue)) {
            $salutationFieldModel->set('fieldvalue', $salutationValue);
        } else {
            $salutationFieldModel->set('fieldvalue', $recordModel->get('salutationtype'));
        }
        $viewer->assign('SALUTATION_FIELD_MODEL', $salutationFieldModel);
        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request)); //Added By Reena for assign for validation.js file 17-12-2019
        parent::process($request);
    }

    /**
     * Added By :- Reena Hingol
     * Date :- 17-12-2019
     * Comment:- Function to get the list of Script models to be included(here validation.js file)
     */
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

    /* End */
}
