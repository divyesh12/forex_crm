<?php

require_once 'data/VTEntityDelta.php';
/**
 * This function is used to validate request of withdrawal for particular Dibort project
 * @global global $log
 * @param array $entityData
 */
function Payments_InternalTransferCustomValidation($entityData) {
    global $log;
    $log->debug('Entering into Payments_Payments_InternalTransferCustomValidation');
    $module = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $paymentId = $parts[1];
    
    $validationError = false;
    $validationMsg = '';
    
    $paymentData = $entityData->getData();
    $paymentData['record_id'] = $paymentId;
    $paymentType = $paymentData['payment_type'];

    if ($paymentType == 'A2A' || $paymentType == 'A2E') {
        $allowInternalTransfer = configvar('internal_transfer_validation');
        if (empty($allowInternalTransfer) || strtolower($allowInternalTransfer) == 'no') {
            $openPositionValidation = isExistOpenPositionTrades($paymentData);
            if ($openPositionValidation) {
                $validationError = true;
                $validationMsg = vtranslate('CAB_MSG_INTERNAL_TRANSFER_OPEN_POSITION_VALIDATION_ERROR', $module);
            }
        }
    }
   
    if ($validationError) {
        $log->debug('exception throw-');
        $log->debug($validationMsg);
        throw new Exception($validationMsg);
    }
}

function isExistOpenPositionTrades($paymentData = array()) {
    global $adb;
    $status = false;
    $accountNo = $paymentData['payment_from'];
    $liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($accountNo, $paymentData['contactid']);
    $metatraderType = $liveAccountDetails['live_metatrader_type'];    
    $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatraderType);
    $query = $provider->checkIsExistOpenTradesOfAccount();
    $queryResult = $adb->pquery($query, array($accountNo));
    $openTrade = $adb->query_result($queryResult, 0, 'login');
    if (!empty($openTrade)) {
        $status = true;
    }
    return $status;
}

?>