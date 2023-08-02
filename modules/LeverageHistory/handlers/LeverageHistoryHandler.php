<?php

require_once('modules/ServiceProviders/ServiceProviders.php');

class LeverageHistorytHandler extends VTEventHandler {

    function handleEvent($eventName, $entityData) {

        global $log, $adb, $metaTrader_details;
        $module = $entityData->getModuleName();

        if ($eventName == 'vtiger.entity.beforesave' && $module == 'LeverageHistory') {
            
            $leverage_auto_approved = configvar('leverage_auto_approved');
            $recordId = $entityData->getId();
            $contactid = $entityData->get('contactid');
            $liveaccountid = $entityData->get('liveaccountid');
            $new_leverage = $entityData->get('leverage');
            $old_leverage = $entityData->get('old_leverage');
            $record_status = $entityData->get('record_status');
            $assigned_user_id = $entityData->get('assigned_user_id');
            $request_from = $entityData->get('request_from');

            if ($liveaccountid && isset($liveaccountid)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($liveaccountid, 'LiveAccount');
                $recordModel->set('mode', 'edit');
                $modelData = $recordModel->getData();
                $metatrader_type = $modelData['live_metatrader_type'];
                $liveaccount_no = $modelData['account_no'];
            }

            if($request_from == 'Mobile' && $recordId){
                $LeverageRecordModel = Vtiger_Record_Model::getInstanceById($recordId);
                $recordStatus = $LeverageRecordModel->get('record_status');
                if($recordStatus == 'Approved'){
                    $message = vtranslate('LBL_STATUS_ALREADY_APPROVED', $module);
                    throw new Exception($message);
                }
            }

            $totatPendingRequest = LeverageHistory_Record_Model::checkPendingRequestExist($contactid, $liveaccountid);

            //$filepath = "modules/ServiceProviders/providers/{$metatrader_type}.php";
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
            // if (!file_exists($filepath)) {
            //     //checkFileAccessForInclusion($filepath);
            //     $message = $metatrader_type . ' ' . vtranslate('LBL_PROVIDER_NOT_EXIST', $module);
            //     throw new Exception($message);
            // } else
            if (empty($provider)) {
                $message = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
                throw new Exception($message);
            }

            if ($leverage_auto_approved && $request_from == 'CustomerPortal') {
                if ($record_status == 'Cancelled' || $record_status == 'Disapproved') {
                    $entityData->set('record_status', 'Cancelled');
                } else if ($record_status == 'Disapproved') {
                    $entityData->set('record_status', 'Disapproved');
                } else {
                    $record_status = 'Approved';
                    $entityData->set('record_status', 'Approved');
                }
            }

            if ($_REQUEST['action'] == 'SaveAjax' || $request_from == 'CustomerPortal' || $request_from == 'Mobile' ) {
                if (empty($recordId) && $totatPendingRequest && $record_status != 'Cancelled') {
                    $message = vtranslate('LBL_PENDING_REQUEST_EXIST', $module);
                    throw new Exception($message);
                }
                if ((empty($recordId) || $request_from == 'Mobile') && $record_status == 'Approved') {
//                    include 'MetaQuote/MetaQuoteHelper.php';
//                    $metaQuoteObj = new MetaQuoteHelper();
//                    $change_leverage_params = array("Login" => $liveaccount_no, "Leverage" => $new_leverage);
//                    $change_leverage_result = $metaQuoteObj->ChangeLeverage($change_leverage_params, $metatrader_type);
                    $change_leverage_result = $provider->changeLeverage($liveaccount_no, $new_leverage);
                    $change_leverage_code = $change_leverage_result->Code;
                    $change_leverage_messege = $change_leverage_result->Message;
                    if ($change_leverage_messege == 'Ok' && $change_leverage_code == 200 && $liveaccount_no) {
                        $recordModel->set('leverage', $new_leverage);
                        $recordModel->set('record_status', $record_status);
                        $recordModel->set('leverage', $new_leverage);
                        $recordModel->set('old_leverage', $old_leverage);
                        $recordModel->save();
                    } elseif ($change_leverage_code == 201) {
                        $message = vtranslate('LBL_LEVERAGE_UPDATE_ISSUE', $module);
                        throw new Exception($message);
                    } else {
                        $message = vtranslate($change_leverage_messege, $module);
                        throw new Exception($message);
                    }
                } else {
                    return true;
                }
            }
        }
    }

}

?>