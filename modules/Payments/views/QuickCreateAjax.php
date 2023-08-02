<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Payments_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View {

    public function process(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $hidden_html = getHiddenHTML();
        $hidden_field_values = $hidden_html[$moduleName];
        $viewer->assign('INPUT_HIDDEN_DATA', $hidden_field_values);
        parent::process($request);
    }

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

}
