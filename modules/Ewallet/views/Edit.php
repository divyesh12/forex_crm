<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Ewallet_Edit_View extends Vtiger_Edit_View {

    /**
     * @add by:- Reena Hingol
     * @Date:- 25-11-2019
     * @comment:-Add restriction for Ewallet module while Edit record and isDuplicate=true from url 
     */
    public function preProcess(Vtiger_Request $request, $display = true) {
        $record = $request->get('record');
        $moduleName = $request->getModule();
        $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED');
        throw new AppException($permission_denied_messege);
        parent::preProcess($request, $display);
    }

    /**
     * @add by:- Reena Hingol
     * @Date:- 26-11-2019
     * @comment:-Add restriction for Contacts related Ewallet module while click on Edit pencil icon 
     */
    public function process(Vtiger_Request $request, $display = true) {
        $record = $request->get('record');
        $moduleName = $request->getModule();
        $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED');
        throw new AppException($permission_denied_messege);
        parent::process($request, $display);
    }

}
