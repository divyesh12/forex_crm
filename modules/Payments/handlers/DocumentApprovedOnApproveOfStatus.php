<?php

require_once 'data/VTEntityDelta.php';
/**
 * This function is used to send reset password email of Investor password
 * @global global $log
 * @param array $entityData
 */
function Payments_DocumentApprovedOnApproveOfStatus($entityData) {
    global $log, $adb;
    $log->debug('Entering into Payments_DocumentApprovedOnApproveOfStatus');
    $moduleName = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $paymentId = $parts[1];
    
    $paymentQuery = "SELECT payment_operation, payment_status FROM vtiger_payments WHERE paymentsid = ?";
    $paymentQueryRes = $adb->pquery($paymentQuery, array($paymentId));

    if ($adb->num_rows($paymentQueryRes) > 0) {
        $payment_operation = $adb->query_result($paymentQueryRes, 0, 'payment_operation');
        $payment_status = $adb->query_result($paymentQueryRes, 0, 'payment_status');
        if ($payment_operation == 'Deposit') {            
            $selQuery = "SELECT notesid FROM vtiger_senotesrel WHERE crmid = ?";
            $res = $adb->pquery($selQuery, array($paymentId));

            if(!empty($res) && $adb->num_rows($res)) {
                $notesid = $adb->query_result($res, 0, 'notesid');
                
                if ($payment_status == 'Completed') {
                    $upQuery = "UPDATE vtiger_notes SET record_status = 'Approved' WHERE notesid = ? AND record_status != 'Disapproved'";
                    $res = $adb->pquery($upQuery, array($notesid));
                } else if ($payment_status == 'Rejected' || $payment_status == 'Cancelled' || $payment_status == 'Failed') {
                    $upQuery = "UPDATE vtiger_notes SET record_status = 'Disapproved' WHERE notesid = ? AND record_status != 'Approved'";
                    $res = $adb->pquery($upQuery, array($notesid));
                }
            }
        }
    }
}

?>