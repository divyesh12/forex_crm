<?php

require_once 'data/VTEntityDelta.php';
/**
 * This function is used to validate request of withdrawal
 * @global global $log
 * @param array $entityData
 */
function Payments_CommonWithdrawalValidation($entityData) {
    global $log;
    $log->debug('Entering into Payments_CommonWithdrawalValidation');
    $module = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $paymentId = $parts[1];
    
    $validationError = false;
    $validationMsg = '';
    
    $paymentData = $entityData->getData();
    $paymentData['record_id'] = $paymentId;
    
    $entryPointValidation = entryPointWithdrawalValidation($paymentData);
    if($entryPointValidation)
    {
        $countValidation = withdCountValidation($paymentData);
        if($countValidation)
        {
        }
        else
        {
            $validationError = true;
            $validationMsg = vtranslate('CAB_MSG_PENDING_WITHDRAWAL_REQUEST_EXIST', $module);
        }
    }
    
    if($validationError)
    {
        $log->debug('exception throw-');
        $log->debug($validationMsg);
        throw new Exception($validationMsg);
    }
//    return array('success' => $validationError, 'msg' => $validationMsg);
}

function entryPointWithdrawalValidation($paymentData = array())
{
    $status = false;
    if($paymentData['payment_type'] == 'A2P' && in_array($paymentData['payment_status'], array('Pending')))
    {
        $status = true;
    }
    return $status;
}

function withdCountValidation($paymentData = array())
{
    global $adb;
    $status = true;
    $contactId = $paymentData['contactid'];
    $accountNo = $paymentData['payment_from'];
    $paymentSql = "SELECT paymentsid FROM vtiger_payments"
        . " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_payments.paymentsid"
        . " WHERE vtiger_crmentity.deleted = 0 AND vtiger_payments.contactid = ? AND vtiger_payments.payment_type = 'A2P'"
        . " AND vtiger_payments.payment_status = 'Pending' AND vtiger_payments.payment_from = ?";
    $paymentResult = $adb->pquery($paymentSql, array($contactId, $accountNo));
    $noOfWithdrawal = $adb->num_rows($paymentResult);
    if ($noOfWithdrawal > 0)
    {
        $status = false;
    }
    return $status;
}

?>