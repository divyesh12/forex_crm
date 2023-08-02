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

class LiveAccount_ChangePassword_Action extends Vtiger_Save_Action {

    function __construct() {
        parent::__construct();
        // $this->exposeMethod('changeLeverage');
        $this->exposeMethod('ChangePasswordInvestorPassword');
        //$this->exposeMethod('changeInvestorPassword');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    public function ChangePasswordInvestorPassword(Vtiger_Request $request) {
        global $adb, $current_user, $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME, $admin_email_id, $form_security_key, $encrypt_method, $template_id;

        $module = $request->getModule();
        $recordId = $request->get('record');
        $newPassword = $request->get('new_password');
        $confirmPassword = $request->get('confirm_password');
        $changeAction = $request->get('change_action');
        $metatrader_type = $request->get('metatrader_type');
        if ($changeAction == 'changeAccountPassword') {
            $IsInvestor = 0;
            $columnName = 'password';
        } elseif ($changeAction == 'changeAccountInvestorPassword') {
            $IsInvestor = 1;
            $columnName = 'investor_password';
        }
        // $password_encrypted = CustomUtils::string_Encrypt_Decrypt($newPassword, 'E', $form_security_key, $encrypt_method);
        //$password_encrypted = string_Encrypt_Decrypt($newPassword, 'E', $form_security_key, $encrypt_method);

        $response = new Vtiger_Response();

        //$filepath = "modules/ServiceProviders/providers/{$metatrader_type}.php";
        $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
        // if (!file_exists($filepath)) {
        //     //checkFileAccessForInclusion($filepath);
        //     $message = $metatrader_type . ' ' . vtranslate('LBL_PROVIDER_NOT_EXIST', $module);
        //     $result = $message;
        //     $response->setError($result);
        // } else
        if (empty($provider)) {
            $message = vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module);
            $result = $message;
            $response->setError($result);
        } else {
            $LiveAcc_recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
            $LiveAcc_modelData = $LiveAcc_recordModel->getData();
            $contactid = $LiveAcc_modelData['contactid'];
            $account_no = $LiveAcc_modelData['account_no'];

            if ($contactid && isset($contactid)) {
                $Con_recordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
                $Con_modelData = $Con_recordModel->getData();
                $contact_email = $Con_modelData['email'];
                $contact_name = $Con_modelData['firstname'] . ' ' . $Con_modelData['lastname'];
            }

            $check_account_exist_result = $provider->checkAccountExist($account_no);
            if ($check_account_exist_result->Code != 200) {
                $message = $check_account_exist_result->Message;
                $result = $message;
                $response->setError($result);
            } else {
                if ($newPassword == $confirmPassword) {

                    $chage_password_result = $provider->changePassword($account_no, $newPassword, $IsInvestor);
                    if ($chage_password_result->Code == 200 && $chage_password_result->Message == 'Ok') {
                        $LiveAcc_recordModel->set('id', $recordId);
                        $LiveAcc_recordModel->set('mode', 'edit');
                        $LiveAcc_recordModel->set($columnName, $newPassword);
                        $LiveAcc_recordModel->save();
                        $result = array('message' => vtranslate('LBL_PASSWORD_SUCCESS', $module));
                        $response->setResult($result);
                    } else {
                        $result = vtranslate($chage_password_result->Message, $module);
                        $response->setError($result);
                    }
                } else {
                    $result = vtranslate('LBL_PASSWORD_NOT_MATCH', $module);
                    $response->setError($result);
                }
            }
        }

        $response->emit();
    }

}
