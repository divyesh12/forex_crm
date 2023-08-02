<?php

require_once 'data/VTEntityDelta.php';
require_once('modules/ServiceProviders/ServiceProviders.php');
/**
 * This function is used to send email of demoaccount password to customer
 * @global global $log
 * @param array $entityData
 */
function DemoAccount_DemoAccountCreation($entityData) {
    global $log, $adb, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID;
    $log->debug('Entering into DemoAccount_DemoAccountCreation');
    $moduleName = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $demoAccId = $parts[1];
    
    $demoAccData = $entityData->getData();
    $demoAccData['record_id'] = $demoAccId;
    
    // /*Get Liveaccount mapping detail*/
    // $livaAccMappingsql = "SELECT send_master_password FROM  `vtiger_accountmapping`  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_accountmapping.accountmappingid  WHERE vtiger_crmentity.deleted = 0 AND `live_metatrader_type`  = '" . $liveAccData['live_metatrader_type'] . "' AND   `live_label_account_type` = '" . $liveAccData['live_label_account_type'] . "' AND `live_currency_code` = '" . $liveAccData['live_currency_code'] . "' ";
    // $livaAccMappingResult = $adb->pquery($livaAccMappingsql);
    // $isMasterPasswordAllow = $adb->query_result($livaAccMappingResult, 0, 'send_master_password');
    
    /*Send password to customer*/
    list($tabUserId, $contactId) = explode('x', $demoAccData['contactid']);
    if (!empty($contactId)) {
        $contactData = getContactInfoFromId($contactId);

        $templName = 'Demo Account Creation';
        $templsql = "SELECT subject,body FROM vtiger_emailtemplates WHERE templatename LIKE '%$templName%'";
        $templates = $adb->pquery($templsql);
        $subject = $adb->query_result($templates, 0, 'subject');
        $body = $adb->query_result($templates, 0, 'body');
        
        // if(!empty($isMasterPasswordAllow) && $isMasterPasswordAllow == 'No')
        // {
        //     $body = str_replace('$liveaccount-password$', '****', $body);
        // }
        
        $newAccountTypeArr = explode('_',$demoAccData['demo_label_account_type']);
        if(!empty($newAccountTypeArr))
        {
            $newAccountType = end($newAccountTypeArr);
            $body = str_replace('$demoaccount-demo_label_account_type$', $newAccountType, $body);
        }
        
        if(isset($demoAccData['metatrader_type']) && !empty($demoAccData['metatrader_type']))
        {
            $providerType = getProviderType($demoAccData['metatrader_type']);
            if(strtolower($providerType) !== 'vertex')
            {
                $body = str_replace('Username:', '', $body);
                $body = str_replace('$demoaccount-username$', '', $body);
            }
            
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($demoAccData['metatrader_type']);
            if (isset($provider->parameters['investor_pass_enable']) && strtolower($provider->parameters['investor_pass_enable']) == 'no')
            {
                $body = str_replace('Investor Password:', '', $body);
                $body = str_replace('$demoaccount-investor_password$', '', $body); 
            }
        }
        
        $body = getMergedDescription($body, $demoAccId, 'DemoAccount');
        send_mail('Contacts', $contactData['email'], $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $body, '', '', '', '', '', true);
    }
}

?>