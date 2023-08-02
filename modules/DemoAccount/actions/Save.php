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

class DemoAccount_Save_Action extends Vtiger_Save_Action {

    public function checkPermission(Vtiger_Request $request) {
        $recordPermission = Users_Privileges_Model::isPermitted('DemoAccount', 'CreateView');

        if (!$recordPermission) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    public function validateRequest(Vtiger_Request $request) {
        return $request->validateWriteAccess();
    }

    public function process(Vtiger_Request $request) {
        global $adb;
        $error = false;
        $otherParam = array();
        $account_duration_days = configvar('demoaccount_expiry_days');
        $isAllowSeries = $isAllowGroupSeries = false;
        $liveAccountMethod = configvar('demo_account_no_method');
        if($liveAccountMethod == 'common_series')
        {
            $isAllowSeries = true;
        }
        else if($liveAccountMethod == 'group_series')
        {
            $isAllowGroupSeries = true;
        }

        $module = $request->get('module');
        $recordId = $request->get('record');
        $metatrader_type = $request->get('metatrader_type');
        $contactid = $request->get('contactid');
        $leverage = $request->get('leverage');
        $currency = $request->get('demo_currency_code');
        $balance = $request->get('balance');
        $label_account_type = $request->get('demo_label_account_type');
        $account_mapping_data = getDemoAccountType($metatrader_type, $label_account_type, $currency);
        $demo_account_type = $account_mapping_data['demo_account_type'];
        //  $demo_account_type = $request->get('demo_account_type');
        $assigned_user_id = $request->get('assigned_user_id');
        $account_no = $request->get('account_no');
        $is_account_disable = $request->get('is_account_disable');
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

        // $filepath = "modules/ServiceProviders/providers/{$metatrader_type}.php";
        // checkFileAccessForInclusion($filepath);

        $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);

        if (empty($provider)) {
            $message = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
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
        }

        if (empty($recordId)) {
            
            if($isAllowSeries || $isAllowGroupSeries)
            {
                $max_accountNo = DemoAccount_Record_Model::getMetaTradeUpcommingSeqNo($module, $metatrader_type, $demo_account_type, $label_account_type, $currency);

                if (!$max_accountNo) {
                    $error = true;
                    $error_label = 'SET_SERIES_TYPE_ERROR';
                } else if ($isAllowSeries && ((!$start_range && !$end_range) || (!$end_range) || (!$start_range))) {
                    $error = true;
                    $error_label = 'COMMON_SERIES_ERROR';
                } else if ($isAllowGroupSeries && ((!$start_range && !$end_range) || (!$end_range) || (!$start_range))) {
                    $error = true;
                    $error_label = 'GROUP_SERIES_ERROR';
                } else if (isset($end_range) && $max_accountNo > $end_range) {
                    $error = true;
                    $error_label = 'ACCOUNT_LIMIT_ERROR';
                } else if (isset($end_range) && !in_array($max_accountNo, range($start_range, $end_range))) {
                    $error = true;
                    $error_label = 'ACCOUNT_RANGE_LIMIT_ERROR';
                } 
            } else if (empty($account_mapping_data)) {
                $error = true;
                $error_label = 'ACCOUNT_MAPPING_ISSUE';
            } else if ($account_creation_limit) {
                $error = true;
                $error_label = 'ACCOUNT_CONTACT_RELATION_LIMIT_ERROR';
            }
            
            
            if (!$error){
                $create_user_result = $provider->createDemoAccount($city, $state, $countryname, $address1, $mailingzip, $mobile, $commnet, $max_accountNo, $password, $investor_password, $phonepassword, str_replace(":", "\\", $demo_account_type), $leverage, $contact_name, $email, $label_account_type, $currency, $contactid, $otherParam);
                
                $create_user_code = $create_user_result->Code;
                $create_user_messege = $create_user_result->Message;
                $account_number = $create_user_result->Data->login;

                if ($create_user_messege == 'Ok' && $create_user_code == 200 && $account_number) {
                    $account_expriry_date = date('Y-m-d', strtotime('+' . $account_duration_days . ' day'));
                    $change_balance_result = $provider->depositToDemoAccount($account_number, $balance, 'Deposit DemoAccount From CRM');
                    $change_balance_code = $change_balance_result->Code;
                    $change_balance_messege = $change_balance_result->Message;
                    if ($change_balance_messege == 'Ok' && $change_balance_code == 200) {
                        $request->set('account_no', $account_number);
                        $request->set('investor_password', $investor_password);
                        $request->set('password', $password);
                        $request->set('username', $otherParam['username']);
                        if ($account_duration_days > 0) {
                            $request->set('account_expriry_date', $account_expriry_date);
                        }
                        parent::process($request);
                    } else {
                        $error = true;
                        $error_label = 'MQT_UPDATE_BALANCE_ISSUE';
                    }
                } elseif ($create_user_code == 201) {
                    $error = true;
                    $error_label = 'ACCOUNT_LIMIT_ERROR';
                } else {
                    $error = true;
                    //   $error_label = 'MQT_ERROR';
                    $error_label = vtranslate($create_user_messege, $module);
                }
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
        } else {
            if (($is_account_disable == 'on' || $is_account_disable) && $account_no) {
                $account_disable_result = $provider->demoaccountDisable($account_no);
                $account_disable_code = $account_disable_result->Code;
                $account_disable_messege = $account_disable_result->Message;
                if ($account_disable_messege == 'Ok' && $account_disable_code == 200) {
                    $request->set('is_account_disable', '1');
                    parent::process($request);
                } else {
                    $error_label = vtranslate($account_disable_messege, $module);
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
            } else {
                parent::process($request);
            }
        }
    }

}
