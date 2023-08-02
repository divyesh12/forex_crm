<?php

require_once('modules/ServiceProviders/ServiceProviders.php');

class LiveAccountHandler extends VTEventHandler {

    function handleEvent($eventName, $entityData) {
        global $log, $adb, $metaTrader_details;
        $module = $entityData->getModuleName();
        $otherParam = array();
        if ($eventName == 'vtiger.entity.beforesave' && $module == 'LiveAccount') {
            //echo '<pre>';
            //print_r($entityData);
            $liveaccount_auto_approved = configvar('liveaccount_auto_approved');
            $isAllowSeries = $isAllowGroupSeries = false;
            $liveAccountMethod = configvar('live_account_no_method');
            if ($liveAccountMethod == 'common_series') {
                $isAllowSeries = true;
            } else if ($liveAccountMethod == 'group_series') {
                $isAllowGroupSeries = true;
            }

            $metatrader_type = $entityData->get('live_metatrader_type');
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);

            $action = $entityData->get('action');
            $recordId = $entityData->getId();
            $contactid = $entityData->get('contactid');
            $leverage = $entityData->get('leverage');
            $record_status = $entityData->get('record_status');
            $label_account_type = $entityData->get('live_label_account_type');
            $currency = $entityData->get('live_currency_code');
            $account_mapping_data = getLiveAccountType($metatrader_type, $label_account_type, $currency);
            $account_type = $account_mapping_data['live_account_type'];
            $assigned_user_id = $entityData->get('assigned_user_id');
            $request_from = $entityData->get('request_from');
            $commnet = 'Create ' . $metatrader_type . ' Account';
            $phonepassword = strtotime("now");
            $password = $entityData->get('password');
            $investor_password = $entityData->get('investor_password');

            if($request_from == 'Mobile' && $recordId){
                $LiveAccRecordModel = Vtiger_Record_Model::getInstanceById($recordId);
                $recordStatus = $LiveAccRecordModel->get('record_status');
                if($recordStatus == 'Approved'){
                    $message = vtranslate('LBL_STATUS_ALREADY_APPROVED', $module);
                    throw new Exception($message);
                }
            }

            if ($contactid && isset($contactid)) {
                $Cont_recordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
                $Cont_modelData = $Cont_recordModel->getData();
                $contact_name = $Cont_modelData['firstname'] . ' ' . $Cont_modelData['lastname'];
                $email = $Cont_modelData['email'];
                $city = $Cont_modelData['mailingcity'];
                $state = $Cont_modelData['mailingstate'];
                $countryname = $Cont_modelData['country_name'];
                $address1 = $Cont_modelData['mailingstreet'];
                $address2 = $Cont_modelData['otherstreet'];
                $mailingzip = $Cont_modelData['mailingzip'];
                $mobile = $Cont_modelData['mobile'];
                $otherParam['username'] = str_replace(' ', '', $Cont_modelData['firstname']) . '' . time();
            }

            if ($liveaccount_auto_approved && $request_from == 'CustomerPortal') {
                $record_status = 'Approved';
                $entityData->set('record_status', 'Approved');
            }

            if (empty($provider)) {
                $message = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
                throw new Exception($message);
            } else if (empty($account_mapping_data)) {
                $message = vtranslate('LBL_ACCOUNT_MAPPING_ISSUE', $module);
                throw new Exception($message);
            }


            if (empty($recordId)) {
                $accountCreationLimitExceed = LiveAccount_Record_Model::checkAccountCreationLimit($contactid);
                if ($accountCreationLimitExceed) {
                    $message = vtranslate('LIVEACCOUNT_CREATION_LIMIT_ERROR', $module);
                    throw new Exception($message);
                }
            }


            if ($isAllowSeries || $isAllowGroupSeries) {
                if ($isAllowSeries && !$isAllowGroupSeries && !empty($provider)) {
                    $start_range = (int) $provider->parameters['liveacc_start_range'];
                    $end_range = (int) $provider->parameters['liveacc_end_range'];
                } elseif (!$isAllowSeries && $isAllowGroupSeries) {
                    $group_series_data = getLiveAccountSeriesBaseOnAccountType($metatrader_type, $account_type, $label_account_type, $currency);
                    $start_range = (int) $group_series_data['start_range'];
                    $end_range = (int) $group_series_data['end_range'];
                }
                if ($isAllowSeries && ((!$start_range && !$end_range) || (!$end_range) || (!$start_range))) {
                    $message = vtranslate('LBL_SET_COMMON_SERIES_FROM_PROVIDER', $module);
                    throw new Exception($message);
                } elseif ($isAllowGroupSeries && ((!$start_range && !$end_range) || (!$end_range) || (!$start_range))) {
                    $message = vtranslate('LBL_SET_GROUP_SERIES_FROM_LIVEACCOUNT_MAPPING', $module);
                    throw new Exception($message);
                }
            }

//            $filepath = "modules/ServiceProviders/providers/{$metatrader_type}.php";
//            if (!file_exists($filepath)) {
//                //checkFileAccessForInclusion($filepath);
//                $message = $metatrader_type . ' ' . vtranslate('LBL_PROVIDER_NOT_EXIST', $module);
//                throw new Exception($message);
//            } else 

            if ($_REQUEST['action'] == 'SaveAjax' || $request_from == 'CustomerPortal' || $request_from == 'Mobile') {
                $providerName = getProviderType($metatrader_type);
                $providerTitleList = getLeverageHideProviderTitle();
                if(!empty($providerTitleList))
                {
                    if(in_array($providerName, $providerTitleList))
                    {
                        $entityData->set('leverage', '');
                        $leverage = "";
                    }
                }
                if ((empty($recordId) || $request_from == 'Mobile') && $record_status == 'Approved') {
                    $password = LiveAccount_Record_Model::RandomString(8);
                    $investor_password = LiveAccount_Record_Model::RandomString(8);

                    if ($isAllowSeries || $isAllowGroupSeries) {
                        $max_accountNo = LiveAccount_Record_Model::getMetaTradeUpcommingSeqNo($module, $metatrader_type, $account_type, $label_account_type, $currency);

                        if (!$max_accountNo) {
                            $message = vtranslate('LBL_SET_SERIES_TYPE', $module);
                            throw new Exception($message);
                        } elseif (isset($end_range) && $max_accountNo > $end_range) {
                            $message = vtranslate('LBL_ACCOUNT_CREATION_LIMIT', $module);
                            throw new Exception($message);
                        } else if (isset($end_range) && !in_array($max_accountNo, range($start_range, $end_range))) {
                            $message = vtranslate('LBL_ACCOUNT_RANGE_FINISHED', $module);
                            throw new Exception($message);
                        }
                    }
                    $create_user_result = $provider->createAccount($city, $state, $countryname, $address1, $mailingzip, $mobile, $commnet, $max_accountNo, $password, $investor_password, $phonepassword, str_replace(":", "\\", $account_type), $leverage, $contact_name, $email, $label_account_type, $currency, $contactid, $otherParam);

                    $create_user_code = $create_user_result->Code;
                    $create_user_messege = $create_user_result->Message;
                    $account_number = $create_user_result->Data->login;

                    if ($create_user_messege == 'Ok' && $create_user_code == 200 && $account_number) {
                        $entityData->set('account_no', $account_number);
                        $entityData->set('password', $password);
                        $entityData->set('investor_password', $investor_password);
                        $entityData->set('record_status', $record_status);
                        $entityData->set('username', $otherParam['username']);
                    } elseif ($create_user_code == 201) {
                        $message = vtranslate('LBL_ACCOUNT_CREATION_LIMIT', $module);
                        throw new Exception($message);
                    } else {
                        $message = vtranslate($create_user_messege, $module);
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