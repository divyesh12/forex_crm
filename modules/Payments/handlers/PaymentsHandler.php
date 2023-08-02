<?php

require_once('modules/ServiceProviders/ServiceProviders.php');

class PaymentsHandler extends VTEventHandler {

    function handleEvent($eventName, $entityData) {
        global $log, $adb, $metaTrader_details, $paymentStatusMappingArr;
        $module = $entityData->getModuleName();
        if ($eventName == 'vtiger.entity.beforesave' && $module == 'Payments') {
            $log->debug('Entering into Payments vtiger.entity.beforesave');
            $isAllow_wallet_to_wallet_auto_approved = configvar('ewallet_to_ewallet_auto_approved');
            $isAllow_wallet_to_account_auto_approved = configvar('ewallet_to_tradingaccount_auto_approved');
            $isAllow_account_to_wallet_auto_approved = configvar('tradingaccount_to_ewallet_auto_approved');
            $isAllow_account_to_account_auto_approved = configvar('liveacc_to_liveacc_auto_approved');

            $action = $entityData->get('action');
            $recordId = $entityData->get('id');
            $mode = $entityData->get('mode');

            $payment_operation = $entityData->get('payment_operation');
            $contactid = $entityData->get('contactid');
            $payment_type = $entityData->get('payment_type');
            $payment_process = $entityData->get('payment_process');
            $payment_currency = $entityData->get('payment_currency');
            $amount = $entityData->get('amount');
            $commission = $entityData->get('commission');
            $commission_value = $entityData->get('commission_value');
            $payment_amount = $entityData->get('payment_amount');
            $payment_status = $entityData->get('payment_status');
            $reject_reason = $entityData->get('reject_reason');
            $transaction_id = $entityData->get('transaction_id');
            $comment = $entityData->get('comment');
            $description = $entityData->get('description');
            $failure_reason = $entityData->get('failure_reason');
            $payment_from = $entityData->get('payment_from');
            $payment_to = $entityData->get('payment_to');
            $assigned_user_id = $entityData->get('assigned_user_id');
            $request_from = $entityData->get('request_from');

            /**
             * Solution1: If current payment status in db is Completed,Rejected,Cancelled 
             * And user wants to save again that record then system throws an exception
             * **/
            $paymentStatusSql = "SELECT payment_status FROM vtiger_payments"
                                . " WHERE paymentsid = ?";
            $paymentStatusResult = $adb->pquery($paymentStatusSql, array($recordId));
            $paymentCurrentStatus = $adb->query_result($paymentStatusResult, 0, 'payment_status');
            if(!empty($paymentCurrentStatus) && in_array($paymentCurrentStatus, array('Completed','Rejected','Cancelled')))
            {
                $message = vtranslate('NOT_SAVE_PAYMENT_DUE_TO_INVALID_STATUS_ERROR', $module) .' '. $paymentCurrentStatus;
                throw new AppException($message);
            }
            
            /**
             * Solution2: If new payment status that want be to save will not be allowed by config $paymentStatusMappingArr array
             * then user unable to save that record and system throws an exception
             * **/
            if(!empty($paymentCurrentStatus) && isset($paymentStatusMappingArr[$paymentCurrentStatus]) && !empty($paymentStatusMappingArr[$paymentCurrentStatus]))
            {
                $allowedPaymentStatus = $paymentStatusMappingArr[$paymentCurrentStatus];
                if(!in_array($payment_status, $allowedPaymentStatus) && $payment_status !== $paymentCurrentStatus)
                {
                    $message = vtranslate('NOT_SAVE_PAYMENT_DUE_TO_INVALID_STATUS_ERROR', $module) .' '. $paymentCurrentStatus;
                    throw new AppException($message);
                }
            }
            
            /*Withdrawal validation start*/
            require_once('modules/com_vtiger_workflow/VTEntityMethodManager.inc');
            try
            {
                if($payment_operation == 'Withdrawal')
                {
                    $log->debug('Entering into additional withdrawal validation -'.$payment_status);
                    $emm = new VTEntityMethodManager($adb);
                    $emm->executeMethod($entityData, 'CommonWithdrawalValidation');
                    $emm->executeMethod($entityData, 'WithdrawalCustomValidation');
                }
            }
            catch (Exception $e)
            {
                throw new Exception($e->getMessage());
            }
            /*Withdrawal validation end*/

            /* Internal Transfer validation start */
            try {
                if ($payment_operation == 'InternalTransfer') {
                    $log->debug('Entering into additional internal transfer validation -'.$payment_status);
                    $emm = new VTEntityMethodManager($adb);
                    $emm->executeMethod($entityData, 'InternalTransferCustomValidation');
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
            /* Internal Transfer validation end */
            
            if ($contactid && isset($contactid)) {
                $Cont_recordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
                $Cont_modelData = $Cont_recordModel->getData();
                //   $ewallet_balance = Payments_Record_Model::getWalletBalance($contactid);
                $ewalletBalanceResult = getEwalletBalanceBaseOnCurrency($contactid);
                $ewallet_balance = 0;
                if (array_key_exists($payment_currency, $ewalletBalanceResult)) {
                    $ewallet_balance = $ewalletBalanceResult[$payment_currency];
                }
                $ewallet_no = $Cont_modelData['contact_no'];
            }

            if($payment_operation == 'IBCommission' && $payment_type == 'C2E' && isset($_REQUEST['c2e_process']) && $_REQUEST['c2e_process'] === 'Finish')
            {
                    $log->debug('finish complete c2e before save..');
                    $transaction_type = '1'; // for deposit from ewallet
                    $ewallet_focus = CRMEntity::getInstance('Ewallet');
                    $ewallet_focus->column_fields['contactid'] = $contactid;
                    $ewallet_focus->column_fields['amount'] = $amount;
                    $ewallet_focus->column_fields['currency'] = $payment_currency;
                    $ewallet_focus->column_fields['ewallet_no'] = $payment_to;
                    $ewallet_focus->column_fields['transaction_type'] = $transaction_type;
                    $ewallet_focus->column_fields['transaction_id'] = $transaction_id;
                    $ewallet_focus->save('Ewallet');

                    if($ewallet_focus->id)
                    {
                        $log->debug('Complete entry of commission withdrawal');
                        $modifiedtime = date('Y-m-d h:i:s');
                        $sql = "UPDATE `tradescommission` set commission_withdraw_status = 1, "
                                . " withdraw_reference_id = '" . $transaction_id . "', modifiedtime = '" . $modifiedtime . "'"
                                . " WHERE parent_contactid = $contactid AND commission_withdraw_status = 0";
                        $sqlResult = $adb->pquery($sql, array());
                    }
            }

            if ($_REQUEST['action'] == 'SaveAjax' || $request_from == 'CustomerPortal' || $_REQUEST['action'] == 'Save') {
                //This condition for when cabinet user request to  wallet to wallet auto transfer amount then check this condition
                if ($isAllow_wallet_to_wallet_auto_approved && $payment_type == 'E2E' && $request_from == 'CustomerPortal' && $payment_process == 'Wallet Withdrawal' && $payment_status == 'Pending') {
                    $entityData->set('payment_status', 'InProgress');
                }
                if ($isAllow_account_to_wallet_auto_approved && $payment_type == 'A2P' && $request_from == 'CustomerPortal' && $ewallet_no == $payment_to && $payment_status == 'Pending') {
                    $entityData->set('payment_status', 'InProgress');
                }
                if ($isAllow_account_to_account_auto_approved && $payment_type == 'A2A' && $request_from == 'CustomerPortal' && $payment_process == 'Account Withdrawal' && $payment_status == 'Pending') {
                    $entityData->set('payment_status', 'InProgress');
                }
                if ($isAllow_wallet_to_account_auto_approved && $payment_type == 'P2A' && $request_from == 'CustomerPortal' && $ewallet_no == $payment_from && $payment_status == 'Pending') {
                    $entityData->set('payment_status', 'InProgress');
                }
               
                if ($payment_operation == 'Deposit') {
                    
                    if ($payment_type == 'P2A' && $payment_status == 'InProgress') {

                        $account_no = $payment_to;
                        
                        $liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($account_no, $contactid);
                        $metatrader_type = $liveAccountDetails['live_metatrader_type'];
                        $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
                        $isLiveAccountExist = isLiveAccountExist($contactid,$account_no);
                        if(!$isLiveAccountExist){
                            $message = vtranslate('LBL_NOT_FOUND_FROM_CRM', $module);
                            throw new AppException($message);
                        }else if (empty($provider)) {
                            $message = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
                            throw new AppException($message);
                        } else {
                            $check_account_exist_result = $provider->checkAccountExist($account_no);
                            if ($ewallet_balance < $amount && $ewallet_no == $payment_from) {
                                $message = vtranslate('LBL_INSUFFICIENT_WALLET_BALANCE', $module);
                                throw new AppException($message);
                            } else if ($check_account_exist_result->Code != 200) {
                                $message = vtranslate($check_account_exist_result->Message, $module);
                                //$message = $account_no . ' ' . vtranslate('LBL_LOGIN_ID_NOT_FOUND', $module);
                                throw new AppException($message);
                            }
                        }
                    } else if ($payment_type == 'P2E' && $payment_status == 'InProgress') {
                        return true;
                    }
                } else if ($payment_operation == 'Withdrawal') {
                    if ($payment_type == 'A2P' && $payment_status == 'InProgress') {
                        $account_no = $payment_from;
                        $liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($account_no, $contactid);
                        $metatrader_type = $liveAccountDetails['live_metatrader_type'];
                        //$filepath = "modules/ServiceProviders/providers/{$metatrader_type}.php";
                        $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
                        
                        $isLiveAccountExist = isLiveAccountExist($contactid,$account_no);
                        if(!$isLiveAccountExist){
                            $message = vtranslate('LBL_NOT_FOUND_FROM_CRM', $module);
                            throw new AppException($message);
                        }else if (empty($provider)) {
                            $message = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
                            throw new AppException($message);
                        } else {
                            $check_account_exist_result = $provider->checkAccountExist($account_no);
                            //$account_balance_result = $provider->getBalance($account_no);
                            $account_balance_result = $provider->getAccountInfo($account_no);$log->debug('$account_balance_result=');$log->debug($account_balance_result);
                            if ($check_account_exist_result->Code != 200) {
                                $message = vtranslate($check_account_exist_result->Message, $module);
                                throw new AppException($message);
                            } else if ($account_balance_result->Code == 200 && $account_balance_result->Message == 'Ok') {
                                if ($account_balance_result->Data->free_margin < $amount && $account_no == $account_balance_result->Data->login) {
                                    $message = vtranslate('LBL_INSUFFICIENT_ACCOUNT_BALANCE', $module);
                                    throw new AppException($message);
                                }
                            } else {
                                $message = vtranslate($account_balance_result->Message, $module);
                                throw new AppException($message);
                            }
                        }
                    } else if ($payment_type == 'E2P' && $payment_status == 'InProgress') {
                        if ($ewallet_balance < $amount && $ewallet_no == $payment_from) {
                            $message = vtranslate('LBL_INSUFFICIENT_WALLET_BALANCE', $module);
                            throw new AppException($message);
                        }
                    }
//                    else {
//                        $message = "Something wrong!, Please check form parameters.";
//                        throw new Exception($message);
//                    }
                } else if ($payment_operation == 'InternalTransfer') {
                    $isInternalTranEnable = configvar('is_internal_transfer_enable');
                    if (!$isInternalTranEnable) {
                        $message = vtranslate('LBL_INTERNAL_TRANSFER_IS_DISABLED', $module);
                        throw new AppException($message);
                    }
                    
                    if ($payment_type == 'A2A' && $payment_status == 'InProgress') {

                        $from_account_no = $payment_from;
                        $from_liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($from_account_no, $contactid);
                        $from_metatrader_type = $from_liveAccountDetails['live_metatrader_type'];
                        //  $from_filepath = "modules/ServiceProviders/providers/{$from_metatrader_type}.php";

                        $to_account_no = $payment_to;
                        $to_liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($to_account_no, $contactid);
                        $to_metatrader_type = $to_liveAccountDetails['live_metatrader_type'];
                        //$to_filepath = "modules/ServiceProviders/providers/{$to_metatrader_type}.php";

                        $from_provider = ServiceProvidersManager::getActiveInstanceByProvider($from_metatrader_type);
                        $to_provider = ServiceProvidersManager::getActiveInstanceByProvider($to_metatrader_type);
                        // if (!file_exists($from_filepath)) {
                        //     //checkFileAccessForInclusion($filepath);
                        //     $message = $from_metatrader_type . ' ' . vtranslate('LBL_PROVIDER_NOT_EXIST', $module);
                        //     throw new Exception($message);
                        // } else
                        //  if (!file_exists($to_filepath)) {
                        //     //checkFileAccessForInclusion($filepath);
                        //     $message = $to_metatrader_type . ' ' . vtranslate('LBL_PROVIDER_NOT_EXIST', $module);
                        //     throw new Exception($message);
                        // } else

                        $isFromLiveAccountExist = isLiveAccountExist($contactid,$from_account_no);
                        $isToLiveAccountExist = isLiveAccountExist($contactid,$to_account_no);
                        if(!$isFromLiveAccountExist || !$isToLiveAccountExist){
                            $message = vtranslate('LBL_NOT_FOUND_FROM_CRM', $module);
                            throw new AppException($message);
                        }else if (empty($from_provider) || empty($to_provider)) {
                            $message = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
                            throw new AppException($message);
                        } else {
                            $from_check_account_exist_result = $from_provider->checkAccountExist($from_account_no);
                            //$account_balance_result = $from_provider->getBalance($from_account_no);
                            $account_balance_result = $from_provider->getAccountInfo($from_account_no);

                            $to_check_account_exist_result = $to_provider->checkAccountExist($to_account_no);

                            if ($from_check_account_exist_result->Code != 200) {
                                $message = vtranslate($from_check_account_exist_result->Message, $module);
                                throw new AppException($message);
                            } else if ($to_check_account_exist_result->Code != 200) {
                                $message = vtranslate($to_check_account_exist_result->Message, $module);
                                throw new AppException($message);
                            } else if ($account_balance_result->Code == 200 && $account_balance_result->Message == 'Ok') {
                                if ($account_balance_result->Data->free_margin < $amount && $from_account_no == $account_balance_result->Data->login) {

                                    // if ($account_balance_result->Data->free_margin < $amount) {
                                    $message = vtranslate('LBL_INSUFFICIENT_ACCOUNT_BALANCE', $module);
                                    throw new AppException($message);
                                }
                            } else {
                                $message = vtranslate($account_balance_result->Message, $module);
                                throw new AppException($message);
                            }
                        }
                    } else if ($payment_type == 'E2E' && $payment_status == 'InProgress') {
                        $ewallet_no_exits = Payments_Record_Model::checkWalletNoExist($payment_to);
                        if ($ewallet_balance < $amount && $ewallet_no == $payment_from) {
                            $message = vtranslate('LBL_INSUFFICIENT_WALLET_BALANCE', $module);
                            throw new Exception($message);
                        } else if (!$ewallet_no_exits) {
                            $message = $payment_to . " " . vtranslate('LBL_WALLET_NO_NOT_EXIST', $module);
                            throw new Exception($message);
                        }
                    }
                } else if ($payment_operation == 'IBCommission') {
                    if ($payment_type == 'C2E' && $payment_status == 'Pending' && $payment_process == 'Commission Withdrawal') {
                        $ewallet_no_exits = Payments_Record_Model::checkWalletNoExist($payment_to);
                        if (!$ewallet_no_exits) {
                            $message = $payment_to . " " . vtranslate('LBL_WALLET_NO_NOT_EXIST', $module);
                            throw new AppException($message);
                        }
                    }
                } else {
                    $message = "Something wrong!, Please check form parameters.";
                    throw new AppException($message);
                }
            }
        }
    }

    function paymentSave($entityData, $focus)
    {
        require_once("include/events/include.inc");
        require_once 'include/events/VTEntityData.inc';
        global $log, $adb;
        
        $workflowEntityData = VTEntityData::fromCRMEntity($focus);
        $module = $entityData->getModuleName();
        
        $ems = new VTEventsManager($adb);
        $ems->initTriggerCache();
        $ems->triggerEvent("vtiger.entity.beforesave.modifiable", $workflowEntityData);
        $ems->triggerEvent("vtiger.entity.beforesave", $workflowEntityData);
        $ems->triggerEvent("vtiger.entity.beforesave.final", $workflowEntityData);
        $focus->saveentity($module);
        
        $workflowManger = new VTWorkflowManager($adb);
        $workflowHandler = new VTWorkflowEventHandler();
        $workflowHandler->workflows = $workflowManger->getPaymentWorkflows($module);
        $workflowHandler->handleEvent($eventName, $workflowEntityData);
        $VTEntityDeltaHandler = new VTEntityDelta();
        $VTEntityDeltaHandler->handleEvent("vtiger.entity.aftersave", $workflowEntityData);
    }
}

function Payments_updatePaymentProcessAndStatus($entityData) {

    $tmpFiles = $_FILES;
    unset($_FILES);
    global $adb, $current_user, $default_charset, $default_timezone;
    $referenceModuleUpdated = array();
    $util = new VTWorkflowUtils();
    $util->adminUser();

    $module = $entityData->getModuleName();
    $entityId = $entityData->getId();
    $recordId = vtws_getIdComponents($entityId);
    $recordId = $recordId[1];

    $moduleHandler = vtws_getModuleHandlerFromName($module, $current_user);
    $handlerMeta = $moduleHandler->getMeta();
    $moduleFields = $handlerMeta->getModuleFields();

    require_once('data/CRMEntity.php');
    $focus = CRMEntity::getInstance($module);
    $focus->id = $recordId;
    $focus->mode = 'edit';
    $focus->retrieve_entity_info($recordId, $module);
    $focus->clearSingletonSaveFields();

    $util->loggedInUser();

    // The data is transformed from db format to user format, there is a chance that date and currency fields might get tracked for changes which should be avoided
    $focus->column_fields->pauseTracking();
    $focus->column_fields = DataTransform::sanitizeDateFieldsForInsert($focus->column_fields, $handlerMeta);
    $focus->column_fields = DataTransform::sanitizeCurrencyFieldsForInsert($focus->column_fields, $handlerMeta);
    $entityFields = $referenceEntityFields = false;
    $focus->column_fields->resumeTracking();

    $payment_operation = $entityData->get('payment_operation');
    $payment_process = $entityData->get('payment_process');
    $payment_status = $entityData->get('payment_status');
    $payment_type = $entityData->get('payment_type');

//    $entityData->data['modifiedtime'] = date("Y-m-d h:i:s");
//    $focus->column_fields['modifiedtime'] = date("Y-m-d h:i:s");

    if ($payment_operation == 'Deposit') {
        if ($payment_type == 'P2A') {
            if ($payment_process == 'PSP' && $payment_status == 'Confirmed') {
                $entityData->data['payment_process'] = 'Account';
                $entityData->data['payment_status'] = 'InProgress';
                $focus->column_fields['payment_process'] = 'Account';
                $focus->column_fields['payment_status'] = 'InProgress';
                //    Payments_Record_Model::updateChangedon(date("Y-m-d h:i:s"),$recordId);
            } elseif ($payment_process == 'Account' && $payment_status == 'Confirmed') {
                $entityData->data['payment_process'] = 'Finish';
                $entityData->data['payment_status'] = 'Completed';
                $entityData->data['failure_reason'] = '';
                $focus->column_fields['payment_process'] = 'Finish';
                $focus->column_fields['payment_status'] = 'Completed';
                $focus->column_fields['failure_reason'] = '';
                //  Payments_Record_Model::updateChangedon(date("Y-m-d h:i:s"),$recordId);
            }
        } elseif ($payment_type == 'P2E') {
            if ($payment_process == 'PSP' && $payment_status == 'Confirmed') {
                $entityData->data['payment_process'] = 'Wallet';
                $entityData->data['payment_status'] = 'InProgress';
                $focus->column_fields['payment_process'] = 'Wallet';
                $focus->column_fields['payment_status'] = 'InProgress';
                //    Payments_Record_Model::updateChangedon(date("Y-m-d h:i:s"),$recordId);
            } elseif ($payment_process == 'Wallet' && $payment_status == 'Confirmed') {
                $entityData->data['payment_process'] = 'Finish';
                $entityData->data['payment_status'] = 'Completed';
                $entityData->data['failure_reason'] = '';
                $focus->column_fields['payment_process'] = 'Finish';
                $focus->column_fields['payment_status'] = 'Completed';
                $focus->column_fields['failure_reason'] = '';
                //  Payments_Record_Model::updateChangedon(date("Y-m-d h:i:s"),$recordId);
            }
        }
    } else if ($payment_operation == 'Withdrawal') {
        if ($payment_type == 'A2P') {
            if ($payment_process == 'Account' && $payment_status == 'Confirmed') {
                $entityData->data['payment_process'] = 'PSP';
                $entityData->data['payment_status'] = 'InProgress';
                $focus->column_fields['payment_process'] = 'PSP';
                $focus->column_fields['payment_status'] = 'InProgress';
                //   Payments_Record_Model::updateChangedon(date("Y-m-d h:i:s"),$recordId);
            } elseif ($payment_process == 'PSP' && $payment_status == 'Confirmed') {
                $entityData->data['payment_process'] = 'Finish';
                $entityData->data['payment_status'] = 'Completed';
                $entityData->data['failure_reason'] = '';
                $focus->column_fields['payment_process'] = 'Finish';
                $focus->column_fields['payment_status'] = 'Completed';
                $focus->column_fields['failure_reason'] = '';
                //   Payments_Record_Model::updateChangedon(date("Y-m-d h:i:s"),$recordId);
            }
        } else if ($payment_type == 'E2P') {
            if ($payment_process == 'Wallet' && $payment_status == 'Confirmed') {
                $entityData->data['payment_process'] = 'Finish';
                $entityData->data['payment_status'] = 'Completed';
                $entityData->data['failure_reason'] = '';
                $focus->column_fields['payment_process'] = 'Finish';
                $focus->column_fields['payment_status'] = 'Completed';
                $focus->column_fields['failure_reason'] = '';
                //    Payments_Record_Model::updateChangedon(date("Y-m-d h:i:s"),$recordId);
            }
        }
    } else if ($payment_operation == 'InternalTransfer') {
        if ($payment_type == 'E2E') {
            if ($payment_process == 'Wallet Withdrawal' && $payment_status == 'Confirmed') {
                $entityData->data['payment_process'] = 'Wallet Deposit';
                $entityData->data['payment_status'] = 'InProgress';
                $focus->column_fields['payment_process'] = 'Wallet Deposit';
                $focus->column_fields['payment_status'] = 'InProgress';
                // Payments_Record_Model::updateChangedon(date("Y-m-d h:i:s"),$recordId);
            } elseif ($payment_process == 'Wallet Deposit' && $payment_status == 'Confirmed') {
                $entityData->data['payment_process'] = 'Finish';
                $entityData->data['payment_status'] = 'Completed';
                $entityData->data['failure_reason'] = '';
                $focus->column_fields['payment_process'] = 'Finish';
                $focus->column_fields['payment_status'] = 'Completed';
                $focus->column_fields['failure_reason'] = '';
                //  Payments_Record_Model::updateChangedon(date("Y-m-d h:i:s"),$recordId);
            }
        } elseif ($payment_type == 'A2A') {
            if ($payment_process == 'Account Withdrawal' && $payment_status == 'Confirmed') {
                $entityData->data['payment_process'] = 'Account Deposit';
                $entityData->data['payment_status'] = 'InProgress';
                $focus->column_fields['payment_process'] = 'Account Deposit';
                $focus->column_fields['payment_status'] = 'InProgress';
                //Payments_Record_Model::updateChangedon(date("Y-m-d h:i:s"),$recordId);
            } elseif ($payment_process == 'Account Deposit' && $payment_status == 'Confirmed') {
                $entityData->data['payment_process'] = 'Finish';
                $entityData->data['payment_status'] = 'Completed';
                $entityData->data['failure_reason'] = '';
                $focus->column_fields['payment_process'] = 'Finish';
                $focus->column_fields['payment_status'] = 'Completed';
                $focus->column_fields['failure_reason'] = '';
                // Payments_Record_Model::updateChangedon(date("Y-m-d h:i:s"),$recordId);
            }
        }
    }
    $_REQUEST['file'] = '';
    $_REQUEST['ajxaction'] = '';
    $actionName = $_REQUEST['action'];
    $_REQUEST['action'] = '';

// For workflows update field tasks is deleted all the lineitems.
    $focus->isLineItemUpdate = false;

//For workflows we need to update all the fields irrespective if the logged in user has the
//permission or not.
    $focus->isWorkFlowFieldUpdate = true;
    $changedFields = $focus->column_fields->getChanged();
    if (count($changedFields) > 0) {
        PaymentsHandler::paymentSave($entityData,$focus);
    }

    $_REQUEST['action'] = $actionName;
    $_FILES = $tmpFiles;
}

function Payments_MTOperationsForDepositWithdrawInternalTransfer($entityData, $request = array()) {
    global $adb, $current_user,$log;

    $isAllow_account_to_wallet_auto_approved = configvar('tradingaccount_to_ewallet_auto_approved');

    $module = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $recordId = $parts[1];

    $action = $entityData->get('action');
    //$recordId = $entityData->get('record');

    $tmpFiles = $_FILES;
    unset($_FILES);
    global $adb, $current_user, $default_charset, $default_timezone;
    $referenceModuleUpdated = array();
    $util = new VTWorkflowUtils();
    $util->adminUser();

    $module = $entityData->getModuleName();
    $entityId = $entityData->getId();
    $recordId = vtws_getIdComponents($entityId);
    $recordId = $recordId[1];

    $moduleHandler = vtws_getModuleHandlerFromName($module, $current_user);
    $handlerMeta = $moduleHandler->getMeta();
    $moduleFields = $handlerMeta->getModuleFields();

    require_once('data/CRMEntity.php');
    $focus = CRMEntity::getInstance($module);
    $focus->id = $recordId;
    $focus->mode = 'edit';
    $focus->retrieve_entity_info($recordId, $module);
    $focus->clearSingletonSaveFields();

    $util->loggedInUser();

    // The data is transformed from db format to user format, there is a chance that date and currency fields might get tracked for changes which should be avoided
    $focus->column_fields->pauseTracking();
    $focus->column_fields = DataTransform::sanitizeDateFieldsForInsert($focus->column_fields, $handlerMeta);
    $focus->column_fields = DataTransform::sanitizeCurrencyFieldsForInsert($focus->column_fields, $handlerMeta);
    $entityFields = $referenceEntityFields = false;
    $focus->column_fields->resumeTracking();

    $payment_operation = $entityData->get('payment_operation');
    $contactid = end(explode('x', $entityData->get('contactid')));
    $payment_type = $entityData->get('payment_type');
    $payment_process = $entityData->get('payment_process');
    $payment_currency = $entityData->get('payment_currency');
    $amount = $entityData->get('amount');
    $commission = $entityData->get('commission');
    $commission_value = $entityData->get('commission_value');
    $payment_amount = $entityData->get('payment_amount');
    $payment_status = $entityData->get('payment_status');
    $reject_reason = $entityData->get('reject_reason');
    $transaction_id = $entityData->get('transaction_id');
    $comment = $entityData->get('comment');
    $description = $entityData->get('description');
    $failure_reason = $entityData->get('failure_reason');
    // $liveaccountid = $entityData->get('liveaccountid');
    //$payment_getways = $entityData->get('payment_getways');
    $payment_from = $entityData->get('payment_from');
    $payment_to = $entityData->get('payment_to');
    $assigned_user_id = $entityData->get('assigned_user_id');
    $request_from = $entityData->get('request_from');



//    $entityData->data['modifiedtime'] = date("Y-m-d h:i:s");
//    $focus->column_fields['modifiedtime'] = date("Y-m-d h:i:s");

    if ($contactid && isset($contactid)) {
        $Cont_recordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
        $Cont_modelData = $Cont_recordModel->getData();
        //  $ewallet_balance = Payments_Record_Model::getWalletBalance($contactid);
        $ewalletBalanceResult = getEwalletBalanceBaseOnCurrency($contactid);
        $ewallet_balance = 0;
        if (array_key_exists($payment_currency, $ewalletBalanceResult)) {
            $ewallet_balance = $ewalletBalanceResult[$payment_currency];
        }
        $ewallet_no = $Cont_modelData['contact_no'];
    }

    //This condition for when cabinet user request to Account to Wallet transfer amount then check this condition
    

    if ($payment_process == 'Account' && $payment_status == 'InProgress') {

        if ($payment_operation == 'Deposit' && $payment_type == 'P2A') {
            $account_no = $payment_to;
        } elseif ($payment_operation == 'Withdrawal' && $payment_type == 'A2P') {
            $account_no = $payment_from;
        }
        $liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($account_no, $contactid);
        $metatrader_type = $liveAccountDetails['live_metatrader_type'];
        $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
        $check_account_exist_result = $provider->checkAccountExist($account_no);

        if ($check_account_exist_result->Code == 200 && $check_account_exist_result->Message == 'Ok') {

            if ($payment_operation == 'Deposit' && $payment_type == 'P2A') {
                $comment = 'Deposit: ' . $transaction_id . '|' . $payment_from;
                $change_balance_result = $provider->deposit($account_no, $amount, $comment);
            } elseif ($payment_operation == 'Withdrawal' && $payment_type == 'A2P') {
                $comment = 'Wdl: ' . $transaction_id . '|' . $payment_to;
                $change_balance_result = $provider->withdrawal($account_no, $amount, $comment);
            }

            $change_balance_code = $change_balance_result->Code;
            $change_balance_messege = $change_balance_result->Message;
//            echo "<pre>";
//            print_r($change_balance_result);
//            exit;
            if ($change_balance_code == 200 && $change_balance_messege == 'Ok') {
                $entityData->data['payment_process'] = 'Account';
                $entityData->data['payment_status'] = 'Confirmed';
                $focus->column_fields['payment_process'] = 'Account';
                $focus->column_fields['payment_status'] = 'Confirmed';
            } else {
                $failure_reason = vtranslate('LBL_UPDATE_BALANCE_ISSUE', $module);
                $entityData->set('payment_process', 'Account');
                $entityData->set('payment_status', 'Failed');
                $entityData->set('failure_reason', $failure_reason);
                Payments_Record_Model::UpdateFields('Failed', 'Account', $failure_reason, $recordId); //parameter sequence :- Status,Process,Failure Reason,RecordId
            }
        } else {
            $failure_reason = $check_account_exist_result->Message;
            $entityData->set('payment_process', 'Account');
            $entityData->set('payment_status', 'Failed');
            $entityData->set('failure_reason', $failure_reason);
            Payments_Record_Model::UpdateFields('Failed', 'Account', $failure_reason, $recordId); //parameter sequence :- Status,Process,Failure Reason,RecordId
        }
    } else if ($payment_process == 'Account Withdrawal' && $payment_status == 'InProgress') {

        if ($payment_operation == 'InternalTransfer' && $payment_type == 'A2A') {
            $account_no = $payment_from;
        }
        $liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($account_no, $contactid);
        $metatrader_type = $liveAccountDetails['live_metatrader_type'];
        $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
        $check_account_exist_result = $provider->checkAccountExist($account_no);
        if ($check_account_exist_result->Code == 200 && $check_account_exist_result->Message == 'Ok') {

            if ($payment_operation == 'InternalTransfer' && $payment_type == 'A2A') {
                $comment = 'Trf: ' . $transaction_id . '|T:' . $payment_to;
                $change_balance_result = $provider->withdrawal($account_no, $amount, $comment);
            }
            $change_balance_code = $change_balance_result->Code;
            $change_balance_messege = $change_balance_result->Message;
            if ($change_balance_code == 200 && $change_balance_messege == 'Ok') {
                $entityData->data['payment_process'] = 'Account Withdrawal';
                $entityData->data['payment_status'] = 'Confirmed';
                $focus->column_fields['payment_process'] = 'Account Withdrawal';
                $focus->column_fields['payment_status'] = 'Confirmed';
            } else {
                $failure_reason = vtranslate('LBL_UPDATE_BALANCE_ISSUE', $module);
                $entityData->set('payment_process', 'Account Withdrawal');
                $entityData->set('payment_status', 'Failed');
                $entityData->set('failure_reason', $failure_reason);
                Payments_Record_Model::UpdateFields('Failed', 'Account Withdrawal', $failure_reason, $recordId); //parameter sequence :- Status,Process,Failure Reason,RecordId
            }
        } else {
            $failure_reason = $check_account_exist_result->Message;
            $entityData->set('payment_process', 'Account Withdrawal');
            $entityData->set('payment_status', 'Failed');
            $entityData->set('failure_reason', $failure_reason);
            Payments_Record_Model::UpdateFields('Failed', 'Account Withdrawal', $failure_reason, $recordId); //parameter sequence :- Status,Process,Failure Reason,RecordId
        }
    } else if ($payment_process == 'Account Deposit' && $payment_status == 'InProgress') {

        if ($payment_operation == 'InternalTransfer' && $payment_type == 'A2A') {
            $account_no = $payment_to;
        }
        $liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($account_no, $contactid);
        $metatrader_type = $liveAccountDetails['live_metatrader_type'];
        $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
        $check_account_exist_result = $provider->checkAccountExist($account_no);
        if ($check_account_exist_result->Code == 200 && $check_account_exist_result->Message == 'Ok') {

            if ($payment_operation == 'InternalTransfer' && $payment_type == 'A2A') {
                $comment = 'Trf: ' . $transaction_id . '|F:' . $payment_from;
                $change_balance_result = $provider->deposit($account_no, $amount, $comment);
            }
            $change_balance_code = $change_balance_result->Code;
            $change_balance_messege = $change_balance_result->Message;
            if ($change_balance_code == 200 && $change_balance_messege == 'Ok') {
                $entityData->data['payment_process'] = 'Account Deposit';
                $entityData->data['payment_status'] = 'Confirmed';
                $focus->column_fields['payment_process'] = 'Account Deposit';
                $focus->column_fields['payment_status'] = 'Confirmed';
            } else {
                $failure_reason = vtranslate('LBL_UPDATE_BALANCE_ISSUE', $module);
                $entityData->set('payment_process', 'Account Deposit');
                $entityData->set('payment_status', 'Failed');
                $entityData->set('failure_reason', $failure_reason);
                Payments_Record_Model::UpdateFields('Failed', 'Account Deposit', $failure_reason, $recordId); //parameter sequence :- Status,Process,Failure Reason,RecordId
            }
        } else {
            $failure_reason = $check_account_exist_result->Message;
            $entityData->set('payment_process', 'Account Deposit');
            $entityData->set('payment_status', 'Failed');
            $entityData->set('failure_reason', $failure_reason);
            Payments_Record_Model::UpdateFields('Failed', 'Account Deposit', $failure_reason, $recordId); //parameter sequence :- Status,Process,Failure Reason,RecordId
        }
    }

    $_REQUEST['file'] = '';
    $_REQUEST['ajxaction'] = '';
    $actionName = $_REQUEST['action'];
    $_REQUEST['action'] = '';

// For workflows update field tasks is deleted all the lineitems.
    $focus->isLineItemUpdate = false;

//For workflows we need to update all the fields irrespective if the logged in user has the
//permission or not.
    $focus->isWorkFlowFieldUpdate = true;
    $changedFields = $focus->column_fields->getChanged();
    if (count($changedFields) > 0) {
        PaymentsHandler::paymentSave($entityData,$focus);
    }

    $_REQUEST['action'] = $actionName;
    $_FILES = $tmpFiles;
}

function Payments_WalletOperationsForDepositWithdrawInternalTransfer($entityData, $request = array()) {

    global $adb, $current_user,$log;
    $isAllow_wallet_to_wallet_auto_approved = configvar('ewallet_to_ewallet_auto_approved');
    $isAllow_wallet_to_account_auto_approved = configvar('ewallet_to_tradingaccount_auto_approved');

    $module = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $recordId = $parts[1];

    $action = $entityData->get('action');
//$recordId = $entityData->get('record');


    $tmpFiles = $_FILES;
    unset($_FILES);
    global $adb, $current_user, $default_charset, $default_timezone;
    $referenceModuleUpdated = array();
    $util = new VTWorkflowUtils();
    $util->adminUser();

    $module = $entityData->getModuleName();
    $entityId = $entityData->getId();
    $recordId = vtws_getIdComponents($entityId);
    $recordId = $recordId[1];

    $moduleHandler = vtws_getModuleHandlerFromName($module, $current_user);
    $handlerMeta = $moduleHandler->getMeta();
    $moduleFields = $handlerMeta->getModuleFields();

    require_once('data/CRMEntity.php');
    $focus = CRMEntity::getInstance($module);
    $focus->id = $recordId;
    $focus->mode = 'edit';
    $focus->retrieve_entity_info($recordId, $module);
    $focus->clearSingletonSaveFields();

    $util->loggedInUser();

    // The data is transformed from db format to user format, there is a chance that date and currency fields might get tracked for changes which should be avoided
    $focus->column_fields->pauseTracking();
    $focus->column_fields = DataTransform::sanitizeDateFieldsForInsert($focus->column_fields, $handlerMeta);
    $focus->column_fields = DataTransform::sanitizeCurrencyFieldsForInsert($focus->column_fields, $handlerMeta);
    $entityFields = $referenceEntityFields = false;
    $focus->column_fields->resumeTracking();

    $payment_operation = $entityData->get('payment_operation');
    $contactid = end(explode('x', $entityData->get('contactid')));
    $payment_type = $entityData->get('payment_type');
    $payment_process = $entityData->get('payment_process');
    $payment_currency = $entityData->get('payment_currency');
    $amount = $entityData->get('amount');
    $commission = $entityData->get('commission');
    $commission_value = $entityData->get('commission_value');
    $payment_amount = $entityData->get('payment_amount');
    $payment_status = $entityData->get('payment_status');
    $reject_reason = $entityData->get('reject_reason');
    $transaction_id = $entityData->get('transaction_id');
    $comment = $entityData->get('comment');
    $description = $entityData->get('description');
    $failure_reason = $entityData->get('failure_reason');
    $payment_from = $entityData->get('payment_from');
    $payment_to = $entityData->get('payment_to');
    $assigned_user_id = $entityData->get('assigned_user_id');
    $request_from = $entityData->get('request_from');


//    $entityData->data['modifiedtime'] = date("Y-m-d h:i:s");
//    $focus->column_fields['modifiedtime'] = date("Y-m-d h:i:s");

    if ($contactid && isset($contactid)) {
        $Cont_recordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
        $Cont_modelData = $Cont_recordModel->getData();
        // $ewallet_balance = Payments_Record_Model::getWalletBalance($contactid);
        $ewalletBalanceResult = getEwalletBalanceBaseOnCurrency($contactid);
        $ewallet_balance = 0;
        if (array_key_exists($payment_currency, $ewalletBalanceResult)) {
            $ewallet_balance = $ewalletBalanceResult[$payment_currency];
        }
        $ewallet_no = $Cont_modelData['contact_no'];
    }

    if ($payment_process == 'PSP' && $payment_status == 'InProgress') {

        if ($payment_operation == 'Deposit' && ($payment_type == 'P2A' || $payment_type == 'P2E')) {
            $ewallet_recordId = 1;
// For From Value Wallet ID
            if ($ewallet_no == $payment_from) {
                $transaction_type = '2'; // for withdrwa from ewallet
                $ewallet_recordId = 0;
                $ewallet_focus = CRMEntity::getInstance('Ewallet');
                $ewallet_focus->column_fields['contactid'] = $contactid;
                $ewallet_focus->column_fields['amount'] = $amount;
                $ewallet_focus->column_fields['currency'] = $payment_currency;
                $ewallet_focus->column_fields['ewallet_no'] = $ewallet_no;
                $ewallet_focus->column_fields['transaction_type'] = $transaction_type;
                $ewallet_focus->column_fields['transaction_id'] = $transaction_id;
                $ewallet_focus->save('Ewallet');
                $ewallet_recordId = $ewallet_focus->id;
            }
        } elseif ($payment_operation == 'Withdrawal' && $payment_type == 'A2P') {
            $ewallet_recordId = 1;
// For To Value Wallet ID
            if ($ewallet_no == $payment_to) {
                $transaction_type = '1'; // for deposit from ewallet
                $ewallet_recordId = 0;
                $ewallet_focus = CRMEntity::getInstance('Ewallet');
                $ewallet_focus->column_fields['contactid'] = $contactid;
                $ewallet_focus->column_fields['amount'] = $amount;
                $ewallet_focus->column_fields['currency'] = $payment_currency;
                $ewallet_focus->column_fields['ewallet_no'] = $ewallet_no;
                $ewallet_focus->column_fields['transaction_type'] = $transaction_type;
                $ewallet_focus->column_fields['transaction_id'] = $transaction_id;
                $ewallet_focus->save('Ewallet');
                $ewallet_recordId = $ewallet_focus->id;
            }
        }
        if (($payment_operation == 'Deposit' && ($payment_type == 'P2A' || $payment_type == 'P2E')) || ($payment_operation == 'Withdrawal' && $payment_type == 'A2P' )) {
            if ($ewallet_recordId) {
                $entityData->data['payment_process'] = 'PSP';
                $entityData->data['payment_status'] = 'Confirmed';
                $focus->column_fields['payment_process'] = 'PSP';
                $focus->column_fields['payment_status'] = 'Confirmed';
            } else {
                $failure_reason = vtranslate('LBL_WALLET_UPDATE_BALANCE_ISSUE', $module);
                $entityData->set('payment_process', 'PSP');
                $entityData->set('payment_status', 'Failed');
                $entityData->set('failure_reason', $failure_reason);
                Payments_Record_Model::UpdateFields('Failed', 'PSP', $failure_reason, $recordId); //parameter sequence :- Status,Process,Failure Reason,RecordId
            }
        }
    } elseif ($payment_process == 'Wallet' && $payment_status == 'InProgress') {

        if ($payment_operation == 'Deposit' && $payment_type == 'P2E') {
            $transaction_type = '1'; // for deposit from ewallet
            $ewallet_recordId = 0;
            $ewallet_focus = CRMEntity::getInstance('Ewallet');
            $ewallet_focus->column_fields['contactid'] = $contactid;
            $ewallet_focus->column_fields['amount'] = $amount;
            $ewallet_focus->column_fields['currency'] = $payment_currency;
            $ewallet_focus->column_fields['ewallet_no'] = $ewallet_no;
            $ewallet_focus->column_fields['transaction_type'] = $transaction_type;
            $ewallet_focus->column_fields['transaction_id'] = $transaction_id;
            $ewallet_focus->save('Ewallet');
            $ewallet_recordId = $ewallet_focus->id;
        } elseif ($payment_operation == 'Withdrawal' && $payment_type == 'E2P') {
            $transaction_type = '2'; // for withdrwa from ewallet
            $ewallet_recordId = 0;
            $ewallet_focus = CRMEntity::getInstance('Ewallet');
            $ewallet_focus->column_fields['contactid'] = $contactid;
            $ewallet_focus->column_fields['amount'] = $amount;
            $ewallet_focus->column_fields['currency'] = $payment_currency;
            $ewallet_focus->column_fields['ewallet_no'] = $ewallet_no;
            $ewallet_focus->column_fields['transaction_type'] = $transaction_type;
            $ewallet_focus->column_fields['transaction_id'] = $transaction_id;
            $ewallet_focus->save('Ewallet');
            $ewallet_recordId = $ewallet_focus->id;
        }

        if (($payment_operation == 'Deposit' && $payment_type == 'P2E' ) || ($payment_operation == 'Withdrawal' && $payment_type == 'E2P' )) {
            if ($ewallet_recordId) {
                $entityData->data['payment_process'] = 'Wallet';
                $entityData->data['payment_status'] = 'Confirmed';
                $focus->column_fields['payment_process'] = 'Wallet';
                $focus->column_fields['payment_status'] = 'Confirmed';
            } else {
                $failure_reason = vtranslate('LBL_WALLET_UPDATE_BALANCE_ISSUE', $module);
                $entityData->set('payment_process', 'Wallet');
                $entityData->set('payment_status', 'Failed');
                $entityData->set('failure_reason', $failure_reason);
                Payments_Record_Model::UpdateFields('Failed', 'Wallet', $failure_reason, $recordId); //parameter sequence :- Status,Process,Failure Reason,RecordId
            }
        }
    } elseif ($payment_process == 'Wallet Withdrawal' && $payment_status == 'InProgress') {
        $from_contactid = Payments_Record_Model::getContactId($payment_from);
        $to_contactid = Payments_Record_Model::getContactId($payment_to);

        if ($payment_operation == 'InternalTransfer' && $payment_type == 'E2E') {
            $transaction_type = '2'; // for withdraw from ewallet
            $ewallet_withdraw_recordId = 0;
            $ewallet_withdraw_focus = CRMEntity::getInstance('Ewallet');
            $ewallet_withdraw_focus->column_fields['contactid'] = $from_contactid;
            $ewallet_withdraw_focus->column_fields['amount'] = $amount;
            $ewallet_withdraw_focus->column_fields['currency'] = $payment_currency;
            $ewallet_withdraw_focus->column_fields['ewallet_no'] = $payment_from;
            $ewallet_withdraw_focus->column_fields['transaction_type'] = $transaction_type;
            $ewallet_withdraw_focus->column_fields['transaction_id'] = $transaction_id;
            $ewallet_withdraw_focus->save('Ewallet');
            $ewallet_withdraw_recordId = $ewallet_withdraw_focus->id;

            if ($ewallet_withdraw_recordId) {
                $entityData->data['payment_process'] = 'Wallet Withdrawal';
                $entityData->data['payment_status'] = 'Confirmed';
                $focus->column_fields['payment_process'] = 'Wallet Withdrawal';
                $focus->column_fields['payment_status'] = 'Confirmed';
            } else {
                $failure_reason = vtranslate('LBL_WALLET_UPDATE_BALANCE_ISSUE', $module);
                $entityData->set('payment_process', 'Wallet Withdrawal');
                $entityData->set('payment_status', 'Failed');
                $entityData->set('failure_reason', $failure_reason);
                Payments_Record_Model::UpdateFields('Failed', 'Wallet Withdrawal', $failure_reason, $recordId); //parameter sequence :- Status,Process,Failure Reason,RecordId
            }
        }
    } elseif ($payment_process == 'Wallet Deposit' && $payment_status == 'InProgress') {

        $from_contactid = Payments_Record_Model::getContactId($payment_from);
        $to_contactid = Payments_Record_Model::getContactId($payment_to);

        if ($payment_operation == 'InternalTransfer' && $payment_type == 'E2E') {
            $transaction_type = '1'; // for deposit from ewallet
            $ewallet_deposit_recordId = 0;
            $ewallet_deposit_focus = CRMEntity::getInstance('Ewallet');
            $ewallet_deposit_focus->column_fields['contactid'] = $to_contactid;
            $ewallet_deposit_focus->column_fields['amount'] = $amount;
            $ewallet_deposit_focus->column_fields['currency'] = $payment_currency;
            $ewallet_deposit_focus->column_fields['ewallet_no'] = $payment_to;
            $ewallet_deposit_focus->column_fields['transaction_type'] = $transaction_type;
            $ewallet_deposit_focus->column_fields['transaction_id'] = $transaction_id;
            $ewallet_deposit_focus->save('Ewallet');
            $ewallet_deposit_recordId = $ewallet_deposit_focus->id;

            if ($ewallet_deposit_recordId) {
                $entityData->data['payment_process'] = 'Wallet Deposit';
                $entityData->data['payment_status'] = 'Confirmed';
                $focus->column_fields['payment_process'] = 'Wallet Deposit';
                $focus->column_fields['payment_status'] = 'Confirmed';
            } else {
                $failure_reason = vtranslate('LBL_WALLET_UPDATE_BALANCE_ISSUE', $module);
                $entityData->set('payment_process', 'Wallet Deposit');
                $entityData->set('payment_status', 'Failed');
                $entityData->set('failure_reason', $failure_reason);
                Payments_Record_Model::UpdateFields('Failed', 'Wallet Deposit', $failure_reason, $recordId); //parameter sequence :- Status,Process,Failure Reason,RecordId
            }
        }
    }

    $_REQUEST['file'] = '';
    $_REQUEST['ajxaction'] = '';
    $actionName = $_REQUEST['action'];
    $_REQUEST['action'] = '';

// For workflows update field tasks is deleted all the lineitems.
    $focus->isLineItemUpdate = false;

//For workflows we need to update all the fields irrespective if the logged in user has the
//permission or not.
    $focus->isWorkFlowFieldUpdate = true;
    $changedFields = $focus->column_fields->getChanged();
    if (count($changedFields) > 0) {
        PaymentsHandler::paymentSave($entityData,$focus);
    }

//    $em = new VTEventsManager($adb);
//    $em->initTriggerCache();
//    $entityData = VTEntityData::fromCRMEntity($focus);
//    $em->triggerEvent("vtiger.entity.aftersave.modifiable", $entityData);
//    $em->triggerEvent("vtiger.entity.aftersave", $entityData);
//    $em->triggerEvent("vtiger.entity.aftersave.final", $entityData);
    $_REQUEST['action'] = $actionName;
    $_FILES = $tmpFiles;
}

function Payments_IBCommissionWithdrawToWallet($entityData, $request = array()) {

    global $adb, $current_user,$log;

    $module = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $recordId = $parts[1];

    $action = $entityData->get('action');
    //$recordId = $entityData->get('record');


    $tmpFiles = $_FILES;
    unset($_FILES);
    global $adb, $current_user, $default_charset, $default_timezone;
    $referenceModuleUpdated = array();
    $util = new VTWorkflowUtils();
    $util->adminUser();

    $module = $entityData->getModuleName();
    $entityId = $entityData->getId();
    $recordId = vtws_getIdComponents($entityId);
    $recordId = $recordId[1];


    $moduleHandler = vtws_getModuleHandlerFromName($module, $current_user);
    $handlerMeta = $moduleHandler->getMeta();
    $moduleFields = $handlerMeta->getModuleFields();

    require_once('data/CRMEntity.php');
    $focus = CRMEntity::getInstance($module);
    $focus->id = $recordId;
    $focus->mode = 'edit';
    $focus->retrieve_entity_info($recordId, $module);
    $focus->clearSingletonSaveFields();

    $util->loggedInUser();

    // The data is transformed from db format to user format, there is a chance that date and currency fields might get tracked for changes which should be avoided
    $focus->column_fields->pauseTracking();
    $focus->column_fields = DataTransform::sanitizeDateFieldsForInsert($focus->column_fields, $handlerMeta);
    $focus->column_fields = DataTransform::sanitizeCurrencyFieldsForInsert($focus->column_fields, $handlerMeta);
    $entityFields = $referenceEntityFields = false;
    $focus->column_fields->resumeTracking();

    $payment_operation = $entityData->get('payment_operation');
    $contactid = end(explode('x', $entityData->get('contactid')));
    $payment_type = $entityData->get('payment_type');
    $payment_process = $entityData->get('payment_process');
    $payment_currency = $entityData->get('payment_currency');
    $amount = $entityData->get('amount');
    $commission = $entityData->get('commission');
    $commission_value = $entityData->get('commission_value');
    $payment_amount = $entityData->get('payment_amount');
    $payment_status = $entityData->get('payment_status');
    $reject_reason = $entityData->get('reject_reason');
    $transaction_id = $entityData->get('transaction_id');
    $comment = $entityData->get('comment');
    $description = $entityData->get('description');
    $failure_reason = $entityData->get('failure_reason');
    $payment_from = $entityData->get('payment_from');
    $payment_to = $entityData->get('payment_to');
    $assigned_user_id = $entityData->get('assigned_user_id');
    $request_from = $entityData->get('request_from');

    if ($payment_operation == 'IBCommission' && $payment_type == 'C2E' && $payment_process == 'Commission Withdrawal' && $payment_status == 'Pending') {
        $log->debug('Inprogress entry of commission withdrawal');
//        $modifiedtime = date('Y-m-d h:i:s');
//        $sql = "UPDATE `tradescommission` set commission_withdraw_status = 2, "
//                . "withdraw_reference_id = '" . $transaction_id . "',  modifiedtime = '" . $modifiedtime . "'"
//                . " WHERE parent_contactid = $contactid AND commission_withdraw_status = 0 "
//                . "AND withdraw_reference_id = ''";
//        $sqlResult = $adb->pquery($sql, array());
        $focus->column_fields['payment_process'] = 'Finish';
        $focus->column_fields['payment_status'] = 'Completed';
        $_REQUEST['c2e_process'] = 'Finish';
    }
    
    $_REQUEST['file'] = '';
    $_REQUEST['ajxaction'] = '';
    $actionName = $_REQUEST['action'];
    $_REQUEST['action'] = '';

// For workflows update field tasks is deleted all the lineitems.
    $focus->isLineItemUpdate = false;

//For workflows we need to update all the fields irrespective if the logged in user has the
//permission or not.
    $focus->isWorkFlowFieldUpdate = true;
    $changedFields = $focus->column_fields->getChanged();
    if (count($changedFields) > 0) {
        PaymentsHandler::paymentSave($entityData,$focus);
    }
    $_REQUEST['action'] = $actionName;
    $_FILES = $tmpFiles;
}

function Payments_FirstTimeDeposit($entityData, $request = array()) {
    $module = $entityData->getModuleName();
    $entityId = $entityData->getId();
    $recordId = vtws_getIdComponents($entityId);
    $recordId = $recordId[1];

    $payment_operation = $entityData->get('payment_operation');
    $contactid = end(explode('x', $entityData->get('contactid')));
    $payment_type = $entityData->get('payment_type');
    $payment_process = $entityData->get('payment_process');
    $payment_currency = $entityData->get('payment_currency');
    $amount = $entityData->get('amount');
    $commission = $entityData->get('commission');
    $commission_value = $entityData->get('commission_value');
    $payment_amount = $entityData->get('payment_amount');
    $payment_status = $entityData->get('payment_status');
    $reject_reason = $entityData->get('reject_reason');
    $transaction_id = $entityData->get('transaction_id');
    $comment = $entityData->get('comment');
    $description = $entityData->get('description');
    $failure_reason = $entityData->get('failure_reason');
    $payment_from = $entityData->get('payment_from');
    $payment_to = $entityData->get('payment_to');
    $assigned_user_id = $entityData->get('assigned_user_id');


    $recordModel1 = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
    $modelData1 = $recordModel1->getData();

    if ($payment_operation == 'Deposit' && $payment_type == 'P2A' && $payment_process == 'Finish' && $payment_status == 'Completed' && ($modelData1['is_first_time_deposit'] == 0 || $modelData1['is_first_time_deposit'] == null)) {
        $recordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
        $modelData = $recordModel->getData();
        $recordModel->set('mode', 'edit');
        $recordModel->set('is_first_time_deposit', '1');
        $recordModel->save();
    }
}

?>