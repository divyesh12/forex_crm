<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
require_once('modules/ServiceProviders/ServiceProviders.php');

class LeverageHistory_Save_Action extends Vtiger_Save_Action {

    public function checkPermission(Vtiger_Request $request) {
        $recordPermission = Users_Privileges_Model::isPermitted('LeverageHistory', 'CreateView');

        if (!$recordPermission) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    public function validateRequest(Vtiger_Request $request) {
        return $request->validateWriteAccess();
    }

    public function process(Vtiger_Request $request) {
        global $metaTrader_details;
        $module = $request->get('module');
        $recordId = $request->get('record_id');
        $contactid = $request->get('contactid');
        $liveaccountid = $request->get('liveaccountid');
        $new_leverage = $request->get('leverage');
        $record_status = $request->get('record_status');
        $assigned_user_id = $request->get('assigned_user_id');

        $totatPendingRequest = LeverageHistory_Record_Model::checkPendingRequestExist($contactid, $liveaccountid);
        if ($liveaccountid && isset($liveaccountid)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($liveaccountid, 'LiveAccount');
            $recordModel->set('mode', 'edit');
            $modelData = $recordModel->getData();
            $metatrader_type = $modelData['live_metatrader_type'];
            $liveaccount_no = $modelData['account_no'];
        }

        // $filepath = "modules/ServiceProviders/providers/{$metatrader_type}.php";
        // checkFileAccessForInclusion($filepath);

        $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
        if (empty($provider)) {
            $message = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
            throw new Exception($message);
        }
        if ($record_status == 'Approved' && $request->get('action') == 'Save') {
            if ($liveaccount_no) {
                $change_leverage_result = $provider->changeLeverage($liveaccount_no, $new_leverage);
//                echo "<pre>";
//                print_r($change_leverage_result);
//                exit;
                $change_leverage_code = $change_leverage_result->Code;
                $change_leverage_messege = $change_leverage_result->Message;
                if ($change_leverage_messege == 'Ok' && $change_leverage_code == 200 && $liveaccount_no) {
                    $recordModel->set('leverage', $new_leverage);
                    $recordModel->save();
                    parent::process($request);
                } elseif ($change_leverage_code == 201) {
                    $error = true;
                    $error_label = 'LEVERAGE_UPDATE_ISSUE';
                } else {
                    $error = true;
                    $error_label = vtranslate($change_leverage_messege, $module);
                }

                if ($error) {
                    $requestData = $request->getAll();
                    //  $moduleName = $request->getModule();
                    unset($requestData['action']);
                    unset($requestData['__vtrftk']);
                    $requestData['view'] = 'Edit';
                    $moduleModel = Vtiger_Module_Model::getInstance($module);
                    $viewer = new Vtiger_Viewer();
                    $viewer->assign('REQUEST_DATA', $requestData);
                    $viewer->assign('REQUEST_URL', $moduleModel->getCreateRecordUrl() . '&error=' . $error_label . '&record=' . $request->get('record'));
                    $viewer->view('RedirectToEditView.tpl', 'Vtiger');
                }
            }
        } else if ($record_status == 'Pending' && $request->get('action') == 'Save') {
            if ($totatPendingRequest) {
                $error = true;
                $error_label = 'PENDING_REQUEST_EXIST';
                $requestData = $request->getAll();
                //  $moduleName = $request->getModule();
                unset($requestData['action']);
                unset($requestData['__vtrftk']);
                $requestData['view'] = 'Edit';
                $moduleModel = Vtiger_Module_Model::getInstance($module);
                $viewer = new Vtiger_Viewer();
                $viewer->assign('REQUEST_DATA', $requestData);
                $viewer->assign('REQUEST_URL', $moduleModel->getCreateRecordUrl() . '&error=' . $error_label . '&record=' . $request->get('record'));
                $viewer->view('RedirectToEditView.tpl', 'Vtiger');
            } else {
                parent::process($request);
            }
        } else {
            parent::process($request);
        }
    }

}
