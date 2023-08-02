<?php

require_once 'data/VTEntityDelta.php';
/**
 * This function is used to send reset password email of Investor password
 * @global global $log
 * @param array $entityData
 */
function LiveAccount_LiveAccountChangeInvestorPasswordMail($entityData) {
    global $log;
    $log->debug('Entering into LiveAccount_LiveAccountChangeInvestorPasswordMail');
    $moduleName = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $liveAccId = $parts[1];
    
    $liveAccData = $entityData->getData();
    $liveAccData['record_id'] = $liveAccId;
    $passwordType = 'investor_password';
    /*check old password value*/
    $entityDelta = new VTEntityDelta();
    $passwordPrevalue = $entityDelta->getOldValue($entityData->getModuleName(), $liveAccId, 'investor_password');
    if(!empty($passwordPrevalue))
    {
        sendLiveAccResetPasswordEmail($liveAccData, $passwordType);
    }
}

?>