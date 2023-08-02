<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
/* new file
 * Add by:- Divyesh Chothani
 * Date:- 18-12-2019
 * Comment:- Change Portal Passowrd
 * */

class Contacts_SavePortalPassword_Action extends Vtiger_Save_Action {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('savePortalPassword');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    /*
    Added By:-  DivyeshChothani
    Comment:- MobileAPI Changes For Change Portal Password
    */
    public function SavePortalPassword(Vtiger_Request $request) {
        $response = new Vtiger_Response();

        $result = changePortalPassword($request);
        $success = $result['success'];
        //echo "<pre>"; print_r($result); exit;
        if($success){
            $message = array('message' => $result['message']);
            $response->setResult($message);
        }else{
            $errorMsg = $result['message'];
            $response->setError($errorMsg);
        }
        $response->emit();

        // global $adb, $current_user, $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME, $admin_email_id, $form_security_key, $encrypt_method, $template_id, $PORTAL_URL;

        // $module = $request->getModule();
        // $recordId = $request->get('record');
        // $newPassword = $request->get('new_password');
        // $confirmPassword = $request->get('confirm_password');
        // $isPortalPasswordEnable = configvar('is_portal_password_enable');

        // $response = new Vtiger_Response();
        // $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
        // $modelData = $recordModel->getData();
        // $email = $modelData['email'];
        // $contact_name = $modelData['firstname'] . ' ' . $modelData['lastname'];

        // if (strcmp($newPassword, $confirmPassword) === 0) {

        //     $columnName = 'is_login_varified';
        //     $password = $newPassword;
        //     $enc_password = Vtiger_Functions::generateEncryptedPassword($password);

        //     $sql = "UPDATE vtiger_portalinfo SET user_password=? WHERE id=?";
        //     $params = array($enc_password, $recordId);
        //     $adb->pquery($sql, $params);
            
        //     $recordModel->set('id', $recordId);
        //     $recordModel->set('mode', 'edit');
        //     $recordModel->set($columnName, '0');
        //     if($isPortalPasswordEnable){
        //         $recordModel->set('plain_password', $password);
        //     }
        //     $recordModel->save();

        //     require_once("modules/Emails/mail.php");
        //     require_once 'config.inc.php';

        //     $emailData = Contacts_ChanagePortalPassword_View::getPortalPasswordContent($request);
        //     $subject = $emailData['subject'];
        //     $contents = $emailData['body'];
        //     $contents = decode_html($contents);

        //     send_mail('Contacts', $email, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $contents, '', '', '', '', '', true);
        //     $result = array('message' => vtranslate('LBL_PASSWORD_SUCCESS', $module));
        //     $response->setResult($result);
        // } else {
        //     $result = vtranslate('LBL_PASSWORD_NOT_MATCH', $module);
        //     $response->setError($result);
        // }
        //$response->emit();
    }

}
