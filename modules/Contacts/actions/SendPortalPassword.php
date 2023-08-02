<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
require_once 'modules/Emails/mail.php';

class Contacts_SendPortalPassword_Action extends Vtiger_Save_Action {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('resendPortalPassword');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }
    
    public function resendPortalPassword(Vtiger_Request $request) {
        global $adb;
        $contactId = $request->get('recordId');
        $result = $adb->pquery("SELECT plain_password FROM vtiger_contactdetails WHERE contactid = ? ", array($contactId));
        $plainPassword = decode_html($adb->query_result($result, 0, 'plain_password'));
        
        global $current_user,$HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
        
        $response = new Vtiger_Response();
        $moduleName = 'Contacts';
        $entityData = Vtiger_Record_Model::getInstanceById($contactId, $moduleName);
        $emailData = Contacts::getResendPortalEmailContents($entityData,$plainPassword,'LoginDetails');
        $email = $entityData->get('email');
        $subject = $emailData['subject'];
        if(empty($subject)) {
                $subject = 'Customer Portal Login Details';
        }

        $contents = $emailData['body'];
        $contents= decode_html(getMergedDescription($contents, $contactId, 'Contacts'));
        if(empty($contents)) {
                
                global $PORTAL_URL;
                $contents = 'LoginDetails';
                $contents .= "<br><br> User ID : $email";
                $contents .= "<br> Password: ".$plainPassword;
                $portalURL = vtranslate('Please ',$moduleName).'<a href="'.$PORTAL_URL.'" style="font-family:Arial, Helvetica, sans-serif;font-size:13px;">'. vtranslate('click here', $moduleName).'</a>';
                $contents .= "<br>".$portalURL;
        }
        $subject = decode_html(getMergedDescription($subject, $contactId,'Contacts'));
        $sendResult = send_mail('Contacts', $email, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $contents);
        if($sendResult)
        {
            $result = array('message' => vtranslate('LBL_PASSWORD_RESEND_SUCCESS', $moduleName));
            $response->setResult($result);
        }
        else
        {
            $result = vtranslate('LBL_PASSWORD_RESEND_FAILED', $moduleName);
            $response->setError($result);
        }
        $response->emit();
    }

}
