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

class LiveAccount_SendTradingPassword_Action extends Vtiger_Action_Controller {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('resendTradingPassword');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }
    
    public function resendTradingPassword(Vtiger_Request $request) {
        global $adb;
        $liveAccountId = $request->get('recordId');
        global $current_user,$HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
        
        $response = new Vtiger_Response();
        $moduleName = 'LiveAccount';
        $entityData = Vtiger_Record_Model::getInstanceById($liveAccountId, $moduleName);
        $liveAccData = $entityData->getData();
        $contactId = $liveAccData['contactid'];
        $contactEntityData = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
        $email = $contactEntityData->get('email');

        $query = 'SELECT vtiger_emailtemplates.subject,vtiger_emailtemplates.body FROM vtiger_emailtemplates WHERE templatename=?';
        $result = $adb->pquery($query, array('Resend Trading Password'));
        $contents = decode_html($adb->query_result($result, 0, 'body'));
        $subject = decode_html($adb->query_result($result, 0, 'subject'));

        if(isset($liveAccData['live_metatrader_type']) && !empty($liveAccData['live_metatrader_type']))
        {
            $providerType = getProviderType($liveAccData['live_metatrader_type']);
            if(strtolower($providerType) !== 'vertex')
            {
                $contents = str_replace('Username:', '', $contents);
                $contents = str_replace('$liveaccount-username$', '', $contents);
            }
        }
        $contents= decode_html(getMergedDescription($contents, $liveAccountId, $moduleName));
        $subject = decode_html(getMergedDescription($subject, $liveAccountId,$moduleName));
        $sendResult = send_mail($moduleName, $email, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $contents);
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
