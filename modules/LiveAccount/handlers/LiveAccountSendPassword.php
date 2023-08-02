<?php

require_once 'data/VTEntityDelta.php';
require_once('modules/ServiceProviders/ServiceProviders.php');
/**
 * This function is used to send email of liveaccount password to customer
 * @global global $log
 * @param array $entityData
 */
function LiveAccount_LiveAccountSendPassword($entityData) {
    global $log, $adb, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID;
    $log->debug('Entering into LiveAccount_LiveAccountSendPassword');
    $moduleName = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $liveAccId = $parts[1];
    
    $liveAccData = $entityData->getData();
    $liveAccData['record_id'] = $liveAccId;
    
    /*Get Liveaccount mapping detail*/
    $livaAccMappingsql = "SELECT send_master_password FROM  `vtiger_accountmapping`  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_accountmapping.accountmappingid  WHERE vtiger_crmentity.deleted = 0 AND `live_metatrader_type`  = '" . $liveAccData['live_metatrader_type'] . "' AND   `live_label_account_type` = '" . $liveAccData['live_label_account_type'] . "' AND `live_currency_code` = '" . $liveAccData['live_currency_code'] . "' ";
    $livaAccMappingResult = $adb->pquery($livaAccMappingsql);
    $isMasterPasswordAllow = $adb->query_result($livaAccMappingResult, 0, 'send_master_password');
    
    /*Send password to customer*/
    //list($tabUserId, $contactId) = explode('x', $liveAccData['contactid']);
    $contactId = end(explode('x', $liveAccData['contactid']));
    if (!empty($contactId)) {
        $contactData = getContactInfoFromId($contactId);

        $templName = 'Live Account Creation with Approved Request';
        $templsql = "SELECT subject,body FROM vtiger_emailtemplates WHERE templatename LIKE '%$templName%'";
        $templates = $adb->pquery($templsql);
        $subject = $adb->query_result($templates, 0, 'subject');
        $body = $adb->query_result($templates, 0, 'body');
        
        if(!empty($isMasterPasswordAllow) && $isMasterPasswordAllow == 'No')
        {
            $body = str_replace('$liveaccount-password$', '****', $body);
        }
        
        $newAccountTypeArr = explode('_',$liveAccData['live_label_account_type']);
        if(!empty($newAccountTypeArr))
        {
            $newAccountType = end($newAccountTypeArr);
            $body = str_replace('$liveaccount-live_label_account_type$', $newAccountType, $body);
        }
        
        if(isset($liveAccData['live_metatrader_type']) && !empty($liveAccData['live_metatrader_type']))
        {
            $providerType = getProviderType($liveAccData['live_metatrader_type']);
            if(strtolower($providerType) !== 'vertex')
            {
                $body = str_replace('Username:', '', $body);
                $body = str_replace('$liveaccount-username$', '', $body);
            }
            
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($liveAccData['live_metatrader_type']);
            if (isset($provider->parameters['investor_pass_enable']) && strtolower($provider->parameters['investor_pass_enable']) == 'no')
            {
                $body = str_replace('Investor Password:', '', $body);
                $body = str_replace('$liveaccount-investor_password$', '', $body); 
            }
        }
        
        $body = getMergedDescription($body, $liveAccId, 'LiveAccount');
        send_mail('Contacts', $contactData['email'], $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $body, '', '', '', '', '', true);
    }
}

?>