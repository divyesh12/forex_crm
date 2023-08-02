<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * @creator: Reena Hingol
 * @date: 14-11-2019
 * @comment: Create file for Get Auto select Current Leverage from LiveAccount
 */
class LeverageHistory_GetRecordData_Action extends Vtiger_BasicAjax_Action {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('GetLiveAccountData');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    public function GetLiveAccountData(Vtiger_Request $request) {
        global $adb;
        $module = $request->get('source_module');
        $recordId = $request->get('record_id');

        $dataResult = array();
        if ($recordId) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'LiveAccount');
            $modelData = $recordModel->getData();
            $leverage = $modelData['leverage'];
            $contactid = $modelData['contactid'];
            $metaType = getProviderType($modelData['live_metatrader_type']);
            $leverageEnable = getProviderLeverageEnable($modelData['live_metatrader_type']);
            $dataResult = array('leverage' => $leverage, 'meta_type' => $metaType, 'leverage_enable' => $leverageEnable);
            if ($contactid) {
                $cont_recordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
                $cont_modelData = $cont_recordModel->getData();
                $contactName = $cont_modelData['firstname'] . ' ' . $cont_modelData['lastname'];
                $dataResult['contact_name'] = $contactName;
                $dataResult['contactid'] = $contactid;
            }
        }
        $response = new Vtiger_Response();
        $response->setResult($dataResult);
        $response->emit();
    }

}

/*END*/
