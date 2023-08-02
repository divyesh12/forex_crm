<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class LiveAccount_Detail_View extends Vtiger_Detail_View {

    /**
     * Function to get Ajax is enabled or not
     * @param Vtiger_Record_Model record model
     * @return <boolean> true/false
     */
    public function isAjaxEnabled($recordModel) {
        return false;
    }

    function preProcess(Vtiger_Request $request, $display = true) {
        global $adb;
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $modelData = $recordModel->getData();
        $account_no = $modelData['account_no'];

        if($modelData['record_status'] == 'Approved')
        {
            $metatrader_type = $modelData['live_metatrader_type'];
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
            if($provider)
            {
                $provider->updateAccountParams($account_no, $recordId);
            }
        }

        parent::preProcess($request, $display);
    }

}
