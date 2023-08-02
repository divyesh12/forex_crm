<?php

require_once 'data/VTEntityDelta.php';
/**
 * This function is used to validate request of withdrawal for particular Dibort project
 * @global global $log
 * @param array $entityData
 */


function Payments_WithdrawalCustomValidation($entityData) {
    global $log;
    $log->debug('Entering into Payments_WithdrawalCustomValidation');
    $module = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $paymentId = $parts[1];
    $validationError = false;
    $validationMsg = '';
    
    $paymentData = $entityData->getData();
    $paymentData['record_id'] = $paymentId;
    
    $entryPointValidation = alphaEntryPointWithdrawalValidation($paymentData);
    if ($entryPointValidation) {
        $countValidation = alphaWithdCountValidation($paymentData);
        if ($countValidation) {
        } else {
            $validationError = true;
            $validationMsg = vtranslate('CAB_MSG_PENDING_WITHDRAWAL_REQUEST_EXIST', $module);
        }
    }
    
    if ($validationError) {
        $log->debug('exception throw-');
        $log->debug($validationMsg);
        throw new Exception($validationMsg);
    }
    //    return array('success' => $validationError, 'msg' => $validationMsg);
}

function alphaEntryPointWithdrawalValidation($paymentData = array()) {
    $status = false;
    if ($paymentData['payment_type'] == 'A2P' && in_array($paymentData['payment_status'], array('Pending'))) {
        $status = true;
    }
    return $status;
}

function alphaWithdCountValidation($paymentData = array()) {
    global $adb;
    $status = true;
    $contactId = $paymentData['contactid'];
    $accountNo = $paymentData['payment_from'];
    $paymentSql = "SELECT paymentsid FROM vtiger_payments"
        . " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_payments.paymentsid"
        . " WHERE vtiger_crmentity.deleted = 0 AND vtiger_payments.contactid = ? AND vtiger_payments.payment_type = 'A2P'"
        . " AND vtiger_payments.payment_status = 'Pending'";
    $paymentResult = $adb->pquery($paymentSql, array($contactId));
    $noOfWithdrawal = $adb->num_rows($paymentResult);
    if ($noOfWithdrawal > 0) {
        $status = false;
    }
    return $status;
}

?>