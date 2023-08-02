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
require_once('data/VTEntityDelta.php');

class LiveAccount_Save_Action extends Vtiger_Save_Action {

    public function checkPermission(Vtiger_Request $request) {
        $recordPermission = Users_Privileges_Model::isPermitted('LiveAccount', 'CreateView');

        if (!$recordPermission) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    public function validateRequest(Vtiger_Request $request) {
        return $request->validateWriteAccess();
    }

    public function process(Vtiger_Request $request) {
        global $adb;
        $isAllowSeries = $isAllowGroupSeries = false;
        $otherParam = array();
        $liveAccountMethod = configvar('live_account_no_method');
        if ($liveAccountMethod == 'common_series') {
            $isAllowSeries = true;
        } else if ($liveAccountMethod == 'group_series') {
            $isAllowGroupSeries = true;
        }

        $module = $request->get('module');
        $recordId = $request->get('record');
        $metatrader_type = $request->get('live_metatrader_type');
        $contactid = $request->get('contactid');
        $account_no = $request->get('account_no');
        $leverage = $request->get('leverage');
        $record_status = $request->get('record_status');
        $currency = $request->get('live_currency_code');
        $label_account_type = $request->get('live_label_account_type');
        $account_mapping_data = getLiveAccountType($metatrader_type, $label_account_type, $currency);
        $account_type = $account_mapping_data['live_account_type'];
        //$account_type = $request->get('live_account_type');
        $assigned_user_id = $request->get('assigned_user_id');
        $commnet = 'Create ' . $metatrader_type . ' Account';
        $phonepassword = strtotime("now");
        $password = LiveAccount_Record_Model::RandomString(8);
        $investor_password = LiveAccount_Record_Model::RandomString(8);
        if (empty($recordId)) {
            $accountCreationLimitExceed = LiveAccount_Record_Model::checkAccountCreationLimit($contactid);
            if ($accountCreationLimitExceed) {
                $error_label = vtranslate('LIVEACCOUNT_CREATION_LIMIT_ERROR', $module);
                getErrorHandleOnSave($request, $module, $request->get('record'), $error_label);
            }
        }
        // $filepath = "modules/ServiceProviders/providers/{$metatrader_type}.php";
        // checkFileAccessForInclusion($filepath);

        $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
        if (empty($provider)) {
            $error_label = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
            getErrorHandleOnSave($request, $module, $request->get('record'), $error_label);
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

        if ($record_status == 'Approved') {
            if (!$account_no) {
                $error = false;
                if ($isAllowSeries || $isAllowGroupSeries) {
                    $max_accountNo = LiveAccount_Record_Model::getMetaTradeUpcommingSeqNo($module, $metatrader_type, $account_type, $label_account_type, $currency);

                    if (!$max_accountNo) {
                        $error = true;
                        $error_label = 'SET_SERIES_TYPE_ERROR';
                    } else if ($isAllowSeries && ((!$start_range && !$end_range) || (!$end_range) || (!$start_range))) {
                        $error = true;
                        $error_label = 'COMMON_SERIES_ERROR';
                    } else if ($isAllowGroupSeries && ((!$start_range && !$end_range) || (!$end_range) || (!$start_range))) {
                        $error = true;
                        $error_label = 'GROUP_SERIES_ERROR';
                    } else if ($max_accountNo > $end_range && isset($end_range)) {
                        $error = true;
                        $error_label = 'ACCOUNT_LIMIT_ERROR';
                    } else if (isset($end_range) && !in_array($max_accountNo, range($start_range, $end_range))) {
                        $error = true;
                        $error_label = 'ACCOUNT_RANGE_LIMIT_ERROR';
                    }
                }

                if (!$error && empty($account_mapping_data)) {
                    $error = true;
                    $error_label = 'ACCOUNT_MAPPING_ISSUE';
                } else if (!$error) {
                    $create_user_result = $provider->createAccount($city, $state, $countryname, $address1, $mailingzip, $mobile, $commnet, $max_accountNo, $password, $investor_password, $phonepassword, str_replace(":", "\\", $account_type), $leverage, $contact_name, $email, $label_account_type, $currency, $contactid, $otherParam);

                    $create_user_code = $create_user_result->Code;
                    $create_user_messege = $create_user_result->Message;
                    $account_number = $create_user_result->Data->login;
                    if ($create_user_messege == 'Ok' && $create_user_code == 200 && $account_number) {
                        $request->set('account_no', $account_number);
                        $request->set('investor_password', $investor_password);
                        $request->set('password', $password);
                        $request->set('username', $otherParam['username']);
                        parent::process($request);
                    } else if ($create_user_code == 201) {
                        $error = true;
                        $error_label = 'ACCOUNT_LIMIT_ERROR';
                    } else {
                        $error = true;
//                        $error_label = vtranslate($create_user_code, $module);
                        $error_label = vtranslate($create_user_messege, $module);
                    }
                }

                if ($error) {
                    // $requestData = $request->getAll();
                    // //  $moduleName = $request->getModule();
                    // unset($requestData['action']);
                    // unset($requestData['__vtrftk']);
                    // $requestData['view'] = 'Edit';
                    // $moduleModel = Vtiger_Module_Model::getInstance($module);
                    // $viewer = new Vtiger_Viewer();
                    // $viewer->assign('REQUEST_DATA', $requestData);
                    // $viewer->assign('REQUEST_URL', $moduleModel->getCreateRecordUrl() . '&error=' . $error_label . '&record=' . $request->get('record'));
                    // $viewer->view('RedirectToEditView.tpl', 'Vtiger');

                    getErrorHandleOnSave($request, $module, $request->get('record'), $error_label);
                }
            } else {
                $is_account_type_changed = false;
                $is_leverage_changed = false;
                if ($recordId && isset($recordId)) {
                    $Live_recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'LiveAccount');
                    $Live_modelData = $Live_recordModel->getData();
                    if (($Live_modelData['live_label_account_type'] != $label_account_type)) {
                        $is_account_type_changed = true;
                    }
                    if ($Live_modelData['leverage'] != $leverage) {
                        $is_leverage_changed = true;
                    }
                }

                if ($is_leverage_changed) {
                    $change_leverage_result = $provider->changeLeverage($account_no, $leverage);
                    $change_leverage_code = $change_leverage_result->Code;
                    $change_leverage_messege = $change_leverage_result->Message;
                    if ($change_leverage_messege == 'Ok' && $change_leverage_code == 200 && $account_no) {
                        $request->set('leverage', $leverage);
                        parent::process($request);
                    } elseif ($change_leverage_code == 201) {
                        $error = true;
                        $error_label = 'LEVERAGE_UPDATE_ISSUE';
                    } else {
                        $error = true;
//                        $error_label = 'MQT_ERROR';   
                        $error_label = vtranslate($change_leverage_messege, $module);
                    }
                    // parent::process($request);
                }
                if ($is_account_type_changed) {
                    if (empty($account_mapping_data)) {
                        $error = true;
                        $error_label = 'ACCOUNT_MAPPING_ISSUE';
                    } else {
                        $change_account_type_result = $provider->changeAccountGroup($account_no, str_replace(":", "\\", $account_type));
                        $change_account_type_code = $change_account_type_result->Code;
                        $change_account_type_messege = $change_account_type_result->Message;
                        if ($change_account_type_code == 200 && $change_account_type_messege == 'Ok' && $account_no) {
                            $request->set('live_label_account_type', $label_account_type);
                            $request->set('live_account_type', $account_type);
                            $request->set('live_currency_code', $currency);
                            parent::process($request);
                        } elseif ($change_account_type_code == 201) {
                            $error = true;
                            $error_label = 'ACCOUNT_TYPE_UPDATE_ISSUE';
                        } else {
                            $error = true;
                            // $error_label = 'MQT_ERROR';
                            $error_label = vtranslate($change_account_type_messege, $module);
                        }
                    }
                }
                if (!$is_account_type_changed && !$is_leverage_changed) {
                    parent::process($request);
                }

                if ($error) {
                    // $requestData = $request->getAll();
                    // //  $moduleName = $request->getModule();
                    // unset($requestData['action']);
                    // unset($requestData['__vtrftk']);
                    // $requestData['view'] = 'Edit';
                    // $moduleModel = Vtiger_Module_Model::getInstance($module);
                    // $viewer = new Vtiger_Viewer();
                    // $viewer->assign('REQUEST_DATA', $requestData);
                    // $viewer->assign('REQUEST_URL', $moduleModel->getCreateRecordUrl() . '&error=' . $error_label . '&record=' . $request->get('record'));
                    // $viewer->view('RedirectToEditView.tpl', 'Vtiger');

                    getErrorHandleOnSave($request, $module, $request->get('record'), $error_label);
                }
            }
        } else {
            parent::process($request);
        }
    }

}
