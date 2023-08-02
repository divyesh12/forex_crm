<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class IBCommissionProfile_IndexAjax_View extends Vtiger_Basic_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('showDeleteView');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        if($this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }
    
    public function showDeleteView(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $recordId = $request->get('record_id');
        $IBCommProfileList = IBCommissionProfile_Record_Model::getIBCommProfileList($recordId);
        $viewer->assign('PROFILE_LIST',$IBCommProfileList);
        $viewer->assign('MODULE',$moduleName);
        $viewer->assign('QUALIFIED_MODULE',$moduleName);
        $viewer->assign('RECORDID',$recordId);
        echo $viewer->view('DeleteView.tpl', $moduleName, true);
    }

}