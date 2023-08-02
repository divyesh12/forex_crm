<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_ModuleConfigurationEditor_Index_View extends Settings_Vtiger_Index_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('showModuleCreationLayout');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->getMode();
        if ($this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        } else {
            //by default show field layout
            $this->showModuleCreationLayout($request);
        }
    }

    public function showModuleCreationLayout(Vtiger_Request $request) {
        global $adb, $log;
        $qualifiedModule = $request->getModule(false);
        $viewer = $this->getViewer($request);
        $view = $request->get('modeview');
        $getFieldHTML = Settings_ModuleConfigurationEditor_Module_Model::getFieldHTML($fieldlabel, $fieldname, $fieldtype, $fieldvalue, $view);

        if (isset($_REQUEST['button_submit']) && $_REQUEST['button_submit'] == 'saveFormData') {
            $update = Settings_ModuleConfigurationEditor_Module_Model::updateConfigurations($_REQUEST);
            header('Location:index.php?module=ModuleConfigurationEditor&parent=Settings&view=Index&modeview=Detail&block=8&fieldid=40');
        }

        $viewer->assign('FORM_HTML', $getFieldHTML);
        $viewer->assign('MODEVIEW', $view);
        $viewer->view('Index.tpl', $qualifiedModule);
    }

}
