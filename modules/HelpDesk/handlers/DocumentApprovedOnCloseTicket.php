<?php

require_once 'data/VTEntityDelta.php';
/**
 * This function is used to send reset password email of Investor password
 * @global global $log
 * @param array $entityData
 */
function HelpDesk_DocumentApprovedOnCloseTicket($entityData) {
    global $log, $adb;
    $log->debug('Entering into HelpDesk_DocumentApprovedOnCloseTicket');
    $moduleName = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $ticketData = $entityData->getData();$log->debug($ticketData);
    $parts = explode('x', $wsId);
    $paymentId = $parts[1];
    $selQuery = "SELECT notesid FROM vtiger_senotesrel WHERE crmid = ?";
    $res = $adb->pquery($selQuery, array($paymentId));
    if(!empty($res) && $adb->num_rows($res))
    {
        $docStatus = "";
        if(strtolower($ticketData['ticketstatus']) == "cancelled")
        {
            $docStatus = "Disapproved";
        }
        else if(strtolower($ticketData['ticketstatus']) == "closed")
        {
            $docStatus = "Approved";
        }
        if(!empty($docStatus))
        {
            $notesid = $adb->query_result($res, 0, 'notesid');
            $upQuery = "UPDATE vtiger_notes SET record_status = ? WHERE notesid = ?";
            $res = $adb->pquery($upQuery, array($docStatus, $notesid));
        }
    }
}

?>