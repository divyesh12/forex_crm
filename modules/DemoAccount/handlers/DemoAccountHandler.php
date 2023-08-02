<?php

require_once('modules/ServiceProviders/ServiceProviders.php');

class DemoAccountHandler extends VTEventHandler {

    function handleEvent($eventName, $entityData) {
        global $log, $adb, $metaTrader_details;
        $module = $entityData->getModuleName();
        $otherParam = array();
        if ($eventName == 'vtiger.entity.beforesave' && $module == 'DemoAccount') {

            $account_duration_days = configvar('demoaccount_expiry_days');
            $isAllowSeries = $isAllowGroupSeries = false;
            $demoAccountMethod = configvar('demo_account_no_method');
            if($demoAccountMethod == 'common_series')
            {
                $isAllowSeries = true;
            }
            else if($demoAccountMethod == 'group_series')
            {
                $isAllowGroupSeries = true;
            }

            $metatrader_type = $entityData->get('metatrader_type');
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);

            $action = $entityData->get('action');
            $recordId = $entityData->get('record');
            $contactid = $entityData->get('contactid');
            $leverage = $entityData->get('leverage');
            $currency = $entityData->get('demo_currency_code');
            $balance = $entityData->get('balance');
            $label_account_type = $entityData->get('demo_label_account_type');
            $account_mapping_data = getDemoAccountType($metatrader_type, $label_account_type, $currency);
            $demo_account_type = $account_mapping_data['demo_account_type'];
            $assigned_user_id = $entityData->get('assigned_user_id');
            $request_from = $entityData->get('request_from');
            $commnet = 'Create ' . $metatrader_type . ' Account';
            $phonepassword = strtotime("now");
            $password = DemoAccount_Record_Model::RandomString(8);
            $investor_password = DemoAccount_Record_Model::RandomString(8);
            $account_creation_limit = DemoAccount_Record_Model::checkAccountCreationLimit($contactid);

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
                $otherParam['username'] = str_replace(' ', '', $Cont_modelData['firstname']).''.time();
            }

            if (empty($provider)) {
                $message = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
                throw new Exception($message);
            } else if ($account_creation_limit && $request_from == 'CustomerPortal') {
                $message = vtranslate('LBL_ACCOUNT_CONTACT_RELATION_LIMIT', $module);
                throw new Exception($message);
            } else if (empty($account_mapping_data)) {
                $message = vtranslate('LBL_ACCOUNT_MAPPING_ISSUE', $module);
                throw new Exception($message);
            }
            
            if($isAllowSeries || $isAllowGroupSeries)
            {
                if ($isAllowSeries && !$isAllowGroupSeries && !empty($provider)) {
                    $start_range = (int) $provider->parameters['demoacc_start_range'];
                    $end_range = (int) $provider->parameters['demoacc_end_range'];
                } elseif (!$isAllowSeries && $isAllowGroupSeries) {
                    $group_series_data = getDemoAccountSeriesBaseOnAccountType($metatrader_type, $demo_account_type, $label_account_type, $currency);
                    $start_range = (int) $group_series_data['start_range'];
                    $end_range = (int) $group_series_data['end_range'];
                }
                
                if ($isAllowSeries && ((!$start_range && !$end_range) || (!$end_range) || (!$start_range))) {
                    $message = vtranslate('LBL_SET_COMMON_SERIES_FROM_PROVIDER', $module);
                    throw new Exception($message);
                } elseif ($isAllowGroupSeries && ((!$start_range && !$end_range) || (!$end_range) || (!$start_range))) {
                    $message = vtranslate('LBL_SET_GROUP_SERIES_FROM_DEMOACCOUNT_MAPPING', $module);
                    throw new Exception($message);
                }
            }

            // $filepath = "modules/ServiceProviders/providers/{$metatrader_type}.php";
            // if (!file_exists($filepath)) {
            //     //checkFileAccessForInclusion($filepath);
            //     $message = $metatrader_type . ' ' . vtranslate('LBL_PROVIDER_NOT_EXIST', $module);
            //     throw new Exception($message);
            // } else

            if ($_REQUEST['action'] == 'SaveAjax' || $request_from == 'CustomerPortal') {
                if (empty($recordId)) {
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
                    if($isAllowSeries || $isAllowGroupSeries)
                    {
                        $max_accountNo = DemoAccount_Record_Model::getMetaTradeUpcommingSeqNo($module, $metatrader_type, $demo_account_type, $label_account_type, $currency);

                        if (!$max_accountNo) {
                            $message = vtranslate('LBL_SET_SERIES_TYPE', $module);
                            throw new Exception($message);exit;
                        } else if ((isset($end_range) && $max_accountNo > $end_range) && ($isAllowSeries || $isAllowGroupSeries)) {
                            $message = vtranslate('LBL_ACCOUNT_CREATION_LIMIT', $module);
                            throw new Exception($message);exit;
                        } else if ((isset($end_range) && !in_array($max_accountNo, range($start_range, $end_range))) && ($isAllowSeries || $isAllowGroupSeries)) {
                            $message = vtranslate('LBL_ACCOUNT_RANGE_FINISHED', $module);
                            throw new Exception($message);exit;
                        } 
                    }
                        $create_user_result = $provider->createDemoAccount($city, $state, $countryname, $address1, $mailingzip, $mobile, $commnet, $max_accountNo, $password, $investor_password, $phonepassword, str_replace(":", "\\", $demo_account_type), $leverage, $contact_name, $email, $label_account_type, $currency, $contactid, $otherParam);

                        $create_user_code = $create_user_result->Code;
                        $create_user_messege = $create_user_result->Message;
                        $account_number = $create_user_result->Data->login;

                        if ($create_user_messege == 'Ok' && $create_user_code == 200) {
                            $account_expriry_date = date('Y-m-d', strtotime('+' . $account_duration_days . ' day'));

                            $change_balance_result = $provider->depositToDemoAccount($account_number, $balance, 'Deposit DemoAccount From CRM');
                            $change_balance_code = $change_balance_result->Code;
                            $change_balance_messege = $change_balance_result->Message;
                            if ($change_balance_messege == 'Ok' && $change_balance_code == 200) {
                                $entityData->set('account_no', $account_number);
                                $entityData->set('password', $password);
                                $entityData->set('investor_password', $investor_password);
                                $entityData->set('username', $otherParam['username']);
                                if ($account_duration_days > 0) {
                                    $entityData->set('account_expriry_date', $account_expriry_date);
                                }
                            } else {
                                $message = vtranslate('LBL_UPDATE_BALANCE_ISSUE', $module);
                                throw new Exception($message);
                            }
                        } elseif ($create_user_code == 201) {
                            $message = vtranslate('LBL_ACCOUNT_CREATION_LIMIT', $module);
                            throw new Exception($message);
                        } else {
                            $message = vtranslate($create_user_messege, $module);
                            throw new Exception($message);
                        }
                }
            }
        }
    }

}

?>