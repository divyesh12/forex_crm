<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
/*
  Added By:-  DivyeshChothani
  Comment:- MobileAPI Changes For Change Password and Investor Password
 */
include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

class Mobile_WS_ChangeTradingAndInvestorPassword extends Mobile_WS_FetchRecordWithGrouping {

    protected $recordValues = false;

    // Avoid retrieve and return the value obtained after Create or Update
    protected function processRetrieve(Mobile_API_Request $request) {
        return $this->recordValues;
    }

    function process(Mobile_API_Request $request) {
        global $current_user; // Required for vtws_update API
        $current_user = $this->getActiveUser();

        $module = $request->get('module');
        $recordId = end(explode('x', $request->get('record')));

        $newPassword = $request->get('new_password');
        $confirmPassword = $request->get('confirm_password');
        $is_change_investor_password = $request->get('is_change_investor_password');

        if ($is_change_investor_password) {
            $IsInvestor = 1;
            $columnName = 'investor_password';
        } else {
            $IsInvestor = 0;
            $columnName = 'password';
        }

        $response = new Mobile_API_Response();

        try {
            if (vtws_recordExists($request->get('record'))) {

                $LiveAcc_recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
                $metatrader_type = $LiveAcc_recordModel->get('live_metatrader_type');
                $contactid = $LiveAcc_recordModel->get('contactid');
                $account_no = $LiveAcc_recordModel->get('account_no');

                $pattern = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{8,}$/';

                if (!preg_match($pattern, $newPassword) || !preg_match($pattern, $confirmPassword)) {
                    $response->setError(201, vtranslate('LBL_PASSWORD_VALIDATION', $module));
                } else if ($newPassword != $confirmPassword) {
                    $response->setError(201, vtranslate('LBL_PASSWORD_NOT_MATCH', $module));
                } else {
                    $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
                    if (empty($provider)) {
                        $response->setError(201, vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module));
                    } else {
                        $check_account_exist_result = $provider->checkAccountExist($account_no);
                        if ($check_account_exist_result->Code != 200) {
                            $message = $check_account_exist_result->Message;
                            $result = $message;
                            $response->setError(201, $result);
                        } else {
                            $change_password_result = $provider->changePassword($account_no, $newPassword, $IsInvestor);
                            if ($change_password_result->Code == 200 && $change_password_result->Message == 'Ok') {
                                $LiveAcc_recordModel->set('id', $recordId);
                                $LiveAcc_recordModel->set('mode', 'edit');
                                $LiveAcc_recordModel->set($columnName, $newPassword);
                                $LiveAcc_recordModel->save();
                                $result = array('code' => 200, 'message' => vtranslate('LBL_PASSWORD_SUCCESS', $module));
                                $response->setResult($result);
                            } else {
                                $result = vtranslate($change_password_result->Message, $module);
                                $response->setError(201, $result);
                            }
                        }
                    }
                }
                return $response;
            } else {
                $response->setError(201, "Record does not exist");
                return $response;
            }
        } catch (DuplicateException $e) {
            $response->setError($e->getCode(), $e->getMessage());
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        return $response;
    }

}
