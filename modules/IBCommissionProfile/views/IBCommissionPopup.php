<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

///var/www/html/forex_crm_v2/modules/IBCommissionProfile/views/Popup.php
class IBCommissionProfile_IBCommissionPopup_View extends Vtiger_Popup_View {

    protected $listViewEntries = false;
    protected $listViewHeaders = false;

    public function requiresPermission(Vtiger_Request $request) {
        $permissions = parent::requiresPermission($request);

        $permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView');
        return $permissions;
    }

    /**
     * Function returns the module name for which the popup should be initialized
     * @param Vtiger_request $request
     * @return <String>
     */
    function getModule(Vtiger_request $request) {
        $moduleName = $request->getModule();
        return $moduleName;
    }

    function process(Vtiger_Request $request) {

        $viewer = $this->getViewer($request);
        $moduleName = $this->getModule($request);
        $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
        $companyLogo = $companyDetails->getLogo();

        $this->initializeListViewContents($request, $viewer);

        $viewer->assign('COMPANY_LOGO', $companyLogo);
        $popUpModuleName = 'IBCommissionProfileItems';
        // $securitySymbolMapping = '{"security":{"Forex 4":{"":["Forex","Commodities","Futures","CRYPTO","INDICES","Forex Exotics","Metals"],"symbol":["AUDCAD","EURCAD"]},"__DEFAULT__":{"":[],"symbol":["AUDCAD","EURCAD","EURGBP","EURJPY"]}}}';
        $securitySymbolMapping = getSecuritySymbolMapping();
        $securitySymbolMappingJson = json_encode($securitySymbolMapping);

        $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($popUpModuleName);
        $viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));
        $recordModel = Vtiger_Record_Model::getCleanInstance('IBCommissionProfileItems');
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
        $viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
        $viewer->assign('SECURITY_SYMBOL_MAPPING', $securitySymbolMappingJson);
        $viewer->view('IBCommissionPopup.tpl', $moduleName);
    }

    function postProcess(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $moduleName = $this->getModule($request);
        $viewer->view('PopupFooter.tpl', $moduleName);
    }

    /**
     * Function to get the list of Script models to be included
     * @param Vtiger_Request $request
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            'libraries.bootstrap.js.eternicode-bootstrap-datepicker.js.bootstrap-datepicker',
            '~libraries/bootstrap/js/eternicode-bootstrap-datepicker/js/locales/bootstrap-datepicker.' . Vtiger_Language_Handler::getShortLanguageName() . '.js',
            '~libraries/jquery/timepicker/jquery.timepicker.min.js',
            'modules.Vtiger.resources.Popup',
            "modules.$moduleName.resources.Popup",
            'modules.Vtiger.resources.BaseList',
            "modules.$moduleName.resources.BaseList",
            'libraries.jquery.jquery_windowmsg',
            'modules.Vtiger.resources.validator.BaseValidator',
            'modules.Vtiger.resources.validator.FieldValidator',
            "modules.$moduleName.resources.validator.FieldValidator"
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }

}
