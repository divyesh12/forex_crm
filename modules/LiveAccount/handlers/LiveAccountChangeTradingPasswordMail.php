<?php

require_once 'data/VTEntityDelta.php';
/**
 * This function is used to send reset password email of Trading password
 * @global global $log
 * @param array $entityData
 */
function LiveAccount_LiveAccountChangeTradingPasswordMail($entityData) {
    global $log;
    $moduleName = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $liveAccId = $parts[1];
    
    $liveAccData = $entityData->getData();
    $liveAccData['record_id'] = $liveAccId;
    $passwordType = 'trading_password';
    /*check old password value*/
    $entityDelta = new VTEntityDelta();
    $passwordPrevalue = $entityDelta->getOldValue($entityData->getModuleName(), $liveAccId, 'password');
    if(!empty($passwordPrevalue))
    {
        sendLiveAccResetPasswordEmail($liveAccData, $passwordType);
    }
}

?>