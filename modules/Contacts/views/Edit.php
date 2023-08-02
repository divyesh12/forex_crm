<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_Edit_View extends Vtiger_Edit_View {

    public function preProcess(Vtiger_Request $request, $display = true) {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        /* Add By Divyesh 
         * Comment:- Contact Creation restriction base on subscription package contact
        */
        if (empty($recordId)) {
            $contactCreationResult = contactCreationRestrictBaseOnPkg();
            $isEnableContactCreation = $contactCreationResult['success'];
            $message = $contactCreationResult['message'];
            if (!$isEnableContactCreation) {
                throw new AppException($message);
            }
        }
        /*End*/
        parent::preProcess($request, $display);
    }

    public function process(Vtiger_Request $request) {

        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $is_allow_update_parent_ib = configvar('is_allow_update_parent_ib'); /* Add By Reena 13_03_2020 */
        $is_allow_max_ib_comm = configvar('is_max_ib_comm_dist_allow');
        $recordModel = $this->record;
        if (!$recordModel) {
            if (!empty($recordId)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            } else {
                $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            }
            $this->record = $recordModel;
        }

        $viewer = $this->getViewer($request);

        $salutationFieldModel = Vtiger_Field_Model::getInstance('salutationtype', $recordModel->getModule());
        // Fix for http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/7851
        $salutationType = $request->get('salutationtype');
        if (!empty($salutationType)) {
            $salutationFieldModel->set('fieldvalue', $request->get('salutationtype'));
        } else {
            $salutationFieldModel->set('fieldvalue', $recordModel->get('salutationtype'));
        }
        $viewer->assign('SALUTATION_FIELD_MODEL', $salutationFieldModel);
        $viewer->assign('IS_ALLOW_UPDATE_PARENT_IB', $is_allow_update_parent_ib); /* Add By Reena 13_03_2020 */
        $viewer->assign('IS_ALLOW_MAX_IB_COMM', $is_allow_max_ib_comm);
        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request)); ////Added By Reena for assign for validation.js file 17-12-2019

        parent::process($request);
    }

    /**
     * Added By :- Reena Hingol
     * Date :- 17-12-2019
     * Comment:- Function to get the list of Script models to be included(here validation.js file)
     */

    /**
     * Function to get the list of Script models to be included
     * @param Vtiger_Request $request
     * @return <Array> - List of Vtiger_JsScript_Model instances
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
