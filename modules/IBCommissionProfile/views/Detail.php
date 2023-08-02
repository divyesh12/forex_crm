<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class IBCommissionProfile_Detail_View extends Vtiger_Detail_View {

    protected $record = false;
    protected $isAjaxEnabled = null;

    function __construct() {
        parent::__construct();
        $this->exposeMethod('showRelatedRecords');
        $this->exposeMethod('showDetailViewByMode');
        $this->exposeMethod('showModuleDetailView');
        $this->exposeMethod('showModuleSummaryView');
        $this->exposeMethod('showModuleBasicView');
    }

    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
  
        $recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId);
        if (!$recordPermission) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }

        if ($recordId) {
            $recordEntityName = getSalesEntityType($recordId);
            if ($recordEntityName !== $moduleName) {
                throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }
      
        return true;
    }

    function process(Vtiger_Request $request) {
             

        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
            return;
        }

        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if ($currentUserModel->get('default_record_view') === 'Summary') {
            echo $this->showModuleBasicView($request);
        } else {
            echo $this->showModuleDetailView($request);
        }
    }

    function showDetailViewByMode($request) {
        $requestMode = $request->get('requestMode');
        if ($requestMode == 'full') {
            return $this->showModuleDetailView($request);
        }
        return $this->showModuleBasicView($request);
    }

    /**
     * Function shows the entire detail for the record
     * @param Vtiger_Request $request
     * @return <type>
     */
    function showModuleDetailView(Vtiger_Request $request) {
        global $current_user;
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        if (!$this->record) {
            $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
        }
        $recordModel = $this->record->getRecord();
        $recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
        $structuredValues = $recordStrucure->getStructure();

        $moduleModel = $recordModel->getModule();

        $IBCommCombinationResult = IBCommissionProfile_Record_Model::getIBCommCombinationData($recordId);

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('RECORD_STRUCTURE', $structuredValues);
        $viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('IBCOMMISSION_ITEMS', $IBCommCombinationResult);
        
        $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
        $viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));

        if ($request->get('displayMode') == 'overlay') {
            $viewer->assign('MODULE_MODEL', $moduleModel);
            $this->setModuleInfo($request, $moduleModel);
            $viewer->assign('SCRIPTS', $this->getOverlayHeaderScripts($request));

            $detailViewLinkParams = array('MODULE' => $moduleName, 'RECORD' => $recordId);
            $detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);
            $viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
            return $viewer->view('OverlayDetailView.tpl', $moduleName);
        } else {
            return $viewer->view('DetailViewFullContents.tpl', $moduleName, true);
        }
    }

    /**
     * Function to get Ajax is enabled or not
     * @param Vtiger_Record_Model record model
     * @return <boolean> true/false
     */
    public function isAjaxEnabled($recordModel) {
        return false;
    }

}
