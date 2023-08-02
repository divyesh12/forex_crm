<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
/*
Added By:-  DivyeshChothani
Comment:- MobileAPI Changes For Convert Lead
*/
include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

vimport('~~/include/Webservices/ConvertLead.php');

class Mobile_WS_MainDashboard extends Mobile_WS_Controller {
    
    function __construct() {
        $this->exposeMethod('widget1_pending_request');
    }
    // Avoid retrieve and return the value obtained after Create or Update
    protected function processRetrieve(Mobile_API_Request $request)
    {
        return $this->recordValues;
    }

    function process(Mobile_API_Request $request)
    {
        global $current_user;
        $response = new Mobile_API_Response();
        try {
            $current_user = $this->getActiveUser();
            $mode = $request->get('sub_operation');
            if ($mode)
            {
                $result = $this->invokeExposedMethod($mode, $request);
                $response->setResult($result);
            }
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        return $response;
    }
    
    function widget1_pending_request(Mobile_API_Request $request)
    {
        try {
            $pendingKycCount = $this->getPendingKycCount();
            $pendingDocCount = $this->getPendingDocumentCount();
            $pendingIbCount = $this->getPendingIBCount();
            $pendingLiveAccCount = $this->getPendingLiveAccCount();
            $pendingLeverageCount = $this->getPendingLeverageCount();
            $pendingPaymentCount = $this->getPendingPaymentCount();
            
            $kycPendingFilterId = $this->getPendingKycFilterId();
            $docPendingFilterId = $this->getPendingDocFilterId();
            $IBPendingFilterId = $this->getPendingIBFilterId();
            $liveAccPendingFilterId = $this->getPendingLiveAccFilterId();
            $leveragePendingFilterId = $this->getPendingLeverageFilterId();
            $depositPendingFilterId = $this->getPendingDepositFilterId();
            $withdPendingFilterId = $this->getPendingWithdrawFilterId();
            $openTicketFilterId = $this->getOpenTicketFilterId();

            $pendingCountList['records'] = array(
                array(
                  'module' => 'Contacts',  
                  'sub_module' => 'KYC',  
                  'title' => 'Verify KYC Request',  
                  'filterid' => !empty($kycPendingFilterId) ? $kycPendingFilterId : "",
                  'count' => $pendingKycCount
                ),
                array(
                  'module' => 'Documents',  
                  'sub_module' => 'Documents',  
                  'title' => 'Document Request',
                  'filterid' => !empty($docPendingFilterId) ? $docPendingFilterId : "",
                  'count' => $pendingDocCount 
                ),
                array(
                  'module' => 'Contacts',  
                  'sub_module' => 'IB Clients',  
                  'title' => 'IB Request',
                  'filterid' => !empty($IBPendingFilterId) ? $IBPendingFilterId : "",
                  'count' => $pendingIbCount
                ),
                array(
                  'module' => 'LiveAccount',  
                  'sub_module' => 'LiveAccount',  
                  'title' => 'Live Account Request',
                  'filterid' => !empty($liveAccPendingFilterId) ? $liveAccPendingFilterId : "",
                  'count' => $pendingLiveAccCount
                ),
                array(
                  'module' => 'LeverageHistory',  
                  'sub_module' => 'Leverage',  
                  'title' => 'Leverage Request',
                  'filterid' => !empty($leveragePendingFilterId) ? $leveragePendingFilterId : "",
                  'count' => $pendingLeverageCount
                ),
                array(
                  'module' => 'Payments',  
                  'sub_module' => 'Deposit',  
                  'title' => 'Deposit Request',
                  'filterid' => !empty($depositPendingFilterId) ? $depositPendingFilterId : "", 
                  'count' => $pendingPaymentCount['deposit_count']
                ),
                array(
                  'module' => 'Payments',  
                  'sub_module' => 'Withdrawal',  
                  'title' => 'Withdrawal Request',
                  'filterid' => !empty($withdPendingFilterId) ? $withdPendingFilterId : "",
                  'count' => $pendingPaymentCount['withdrawal_count']
                ),
                array(
                    'module' => 'HelpDesk',  
                    'sub_module' => 'Ticket',  
                    'title' => 'Ticket',
                    'filterid' => !empty($openTicketFilterId) ? $openTicketFilterId : "",
                    'count' => ""
                  ),
            );
            $response = $pendingCountList;
        } catch (Exception $e) {
            $response = new Mobile_API_Response();
            $response->setError($e->getCode(), $e->getMessage());
        }
        return $response;
    }
    
    function getPendingKycCount()
    {
        global $adb;
        $sql = "SELECT COUNT(1) as kyc_count FROM vtiger_contactdetails
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
            WHERE vtiger_crmentity.deleted = 0 AND vtiger_contactdetails.is_document_verified = 0;";
        $pendingKycResult = $adb->pquery($sql, array());
        $pendingKycCount = $adb->query_result($pendingKycResult, 0, 'kyc_count');
        return $pendingKycCount;
    }

    function getPendingKycFilterId()
    {
        global $adb;
        $sql = "SELECT vtiger_cvadvfilter.cvid FROM vtiger_cvadvfilter
        INNER JOIN vtiger_customview ON vtiger_cvadvfilter.cvid = vtiger_customview.cvid
        WHERE vtiger_customview.entitytype = ? AND userid = ? AND status = ? AND
        vtiger_cvadvfilter.columnname = ? AND 
        vtiger_cvadvfilter.comparator = ? AND vtiger_cvadvfilter.value = ?;";
        $pendingKycResult = $adb->pquery($sql, array("Contacts", "1", "1", "vtiger_contactdetails:is_document_verified:is_document_verified:Contacts_LBL_DOC_STATUS:C", "e", "0"));
        $pendingKycFilterId = $adb->query_result($pendingKycResult, 0, 'cvid');
        return $pendingKycFilterId;
    }
    
    function getPendingDocumentCount()
    {
        global $adb;
        $sql = "SELECT COUNT(1) as doc_count FROM vtiger_notes
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_notes.notesid
            WHERE vtiger_crmentity.deleted = 0 AND vtiger_notes.record_status = ?;";
        $pendingDocResult = $adb->pquery($sql, array('Pending'));
        $pendingDocCount = $adb->query_result($pendingDocResult, 0, 'doc_count');
        return $pendingDocCount;
    }

    function getPendingDocFilterId()
    {
        global $adb;
        $sql = "SELECT vtiger_cvadvfilter.cvid FROM vtiger_cvadvfilter
        INNER JOIN vtiger_customview ON vtiger_cvadvfilter.cvid = vtiger_customview.cvid
        WHERE vtiger_customview.entitytype = ? AND userid = ? AND status = ? AND
        vtiger_cvadvfilter.columnname = ? AND 
        vtiger_cvadvfilter.comparator = ? AND vtiger_cvadvfilter.value = ?;";
        $pendingDocResult = $adb->pquery($sql, array("Documents", "1", "1", "vtiger_notes:record_status:record_status:Documents_LBL_STATUS:V", "e", "Pending"));
        $pendingDocFilterId = $adb->query_result($pendingDocResult, 0, 'cvid');
        return $pendingDocFilterId;
    }
    
    function getPendingIBCount()
    {
        global $adb;
        $sql = "SELECT COUNT(1) as ib_count FROM vtiger_contactdetails
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
            WHERE vtiger_crmentity.deleted = 0 AND vtiger_contactdetails.record_status = ?;";
        $pendingIbResult = $adb->pquery($sql, array('Pending'));
        $pendingIbCount = $adb->query_result($pendingIbResult, 0, 'ib_count');
        return $pendingIbCount;
    }
    
    function getPendingIBFilterId()
    {
        global $adb;
        $sql = "SELECT vtiger_cvadvfilter.cvid FROM vtiger_cvadvfilter
        INNER JOIN vtiger_customview ON vtiger_cvadvfilter.cvid = vtiger_customview.cvid
        WHERE vtiger_customview.entitytype = ? AND userid = ? AND status = ? AND
        vtiger_cvadvfilter.columnname = ? AND 
        vtiger_cvadvfilter.comparator = ? AND vtiger_cvadvfilter.value = ?;";
        $pendingIbResult = $adb->pquery($sql, array("Contacts", "1", "1", "vtiger_contactdetails:record_status:record_status:Contacts_LBL_IB_STATUS:V", "e", "Pending"));
        $pendingIbFilterId = $adb->query_result($pendingIbResult, 0, 'cvid');
        return $pendingIbFilterId;
    }

    function getPendingLiveAccCount()
    {
        global $adb;
        $sql = "SELECT COUNT(1) as liveacc_count FROM vtiger_liveaccount
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid
            WHERE vtiger_crmentity.deleted = 0 AND vtiger_liveaccount.record_status = ?;";
        $pendingLiveAccResult = $adb->pquery($sql, array('Pending'));
        $pendingLiveAccCount = $adb->query_result($pendingLiveAccResult, 0, 'liveacc_count');
        return $pendingLiveAccCount;
    }

    function getPendingLiveAccFilterId()
    {
        global $adb;
        $sql = "SELECT vtiger_cvadvfilter.cvid FROM vtiger_cvadvfilter
        INNER JOIN vtiger_customview ON vtiger_cvadvfilter.cvid = vtiger_customview.cvid
        WHERE vtiger_customview.entitytype = ? AND userid = ? AND status = ? AND
        vtiger_cvadvfilter.columnname = ? AND 
        vtiger_cvadvfilter.comparator = ? AND vtiger_cvadvfilter.value = ?;";
        $pendingLiveAccResult = $adb->pquery($sql, array("LiveAccount", "1", "1", "vtiger_liveaccount:record_status:record_status:LiveAccount_LBL_STATUS:V", "e", "Pending"));
        $pendingLiveAccFilterId = $adb->query_result($pendingLiveAccResult, 0, 'cvid');
        return $pendingLiveAccFilterId;
    }

    function getPendingLeverageCount()
    {
        global $adb;
        $sql = "SELECT COUNT(1) as leverage_count FROM vtiger_leveragehistory
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leveragehistory.leveragehistoryid
            WHERE vtiger_crmentity.deleted = 0 AND vtiger_leveragehistory.record_status = ?;";
        $pendingLeverageResult = $adb->pquery($sql, array('Pending'));
        $pendingLeverageCount = $adb->query_result($pendingLeverageResult, 0, 'leverage_count');
        return $pendingLeverageCount;
    }
    
    function getPendingLeverageFilterId()
    {
        global $adb;
        $sql = "SELECT vtiger_cvadvfilter.cvid FROM vtiger_cvadvfilter
        INNER JOIN vtiger_customview ON vtiger_cvadvfilter.cvid = vtiger_customview.cvid
        WHERE vtiger_customview.entitytype = ? AND userid = ? AND status = ? AND
        vtiger_cvadvfilter.columnname = ? AND 
        vtiger_cvadvfilter.comparator = ? AND vtiger_cvadvfilter.value = ?;";
        $pendingLiveAccResult = $adb->pquery($sql, array("LeverageHistory", "1", "1", "vtiger_leveragehistory:record_status:record_status:LeverageHistory_LBL_RECORD_STATUS:V", "e", "Pending"));
        $pendingLiveAccFilterId = $adb->query_result($pendingLiveAccResult, 0, 'cvid');
        return $pendingLiveAccFilterId;
    }

    function getPendingPaymentCount()
    {
        global $adb;
        $sql = "SELECT SUM(IF(payment_operation = 'Deposit', 1, 0)) as total_deposit, SUM(IF(payment_operation = 'Withdrawal', 1, 0)) as total_withdrawal FROM vtiger_payments
        INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_payments.paymentsid
        WHERE vtiger_crmentity.deleted = 0 AND vtiger_payments.payment_status = ?;";
        $pendingPaymentResult = $adb->pquery($sql, array('Pending'));
        $pendingCount['deposit_count'] = $adb->query_result($pendingPaymentResult, 0, 'total_deposit');
        $pendingCount['withdrawal_count'] = $adb->query_result($pendingPaymentResult, 0, 'total_withdrawal');
        return $pendingCount;
    }

    function getPendingDepositFilterId()
    {
        global $adb;
        $sql = "SELECT vtiger_cvadvfilter.cvid, count(vtiger_cvadvfilter.cvid) FROM vtiger_cvadvfilter
        INNER JOIN vtiger_customview ON vtiger_cvadvfilter.cvid = vtiger_customview.cvid
        WHERE vtiger_customview.entitytype = ? AND userid = ? AND status = ? AND
        (vtiger_cvadvfilter.columnname = ? AND 
        vtiger_cvadvfilter.comparator = ? AND vtiger_cvadvfilter.value = ?) OR (vtiger_cvadvfilter.columnname = ? AND 
        vtiger_cvadvfilter.comparator = ? AND vtiger_cvadvfilter.value = ?) GROUP BY vtiger_cvadvfilter.cvid HAVING COUNT(vtiger_cvadvfilter.cvid) > 1;";
        $pendingDepositResult = $adb->pquery($sql, array("Payments", "1", "1", "vtiger_payments:payment_operation:payment_operation:Payments_LBL_PAYMENT_OPERATION:V", "e", "Deposit", "vtiger_payments:payment_status:payment_status:Payments_LBL_PAYMENT_STATUS:V", "e", "Pending"));
        $pendingDepositFilterId = $adb->query_result($pendingDepositResult, 0, 'cvid');
        return $pendingDepositFilterId;
    }

    function getPendingWithdrawFilterId()
    {
        global $adb;
        $sql = "SELECT vtiger_cvadvfilter.cvid, count(vtiger_cvadvfilter.cvid) FROM vtiger_cvadvfilter
        INNER JOIN vtiger_customview ON vtiger_cvadvfilter.cvid = vtiger_customview.cvid
        WHERE vtiger_customview.entitytype = ? AND userid = ? AND status = ? AND
        (vtiger_cvadvfilter.columnname = ? AND 
        vtiger_cvadvfilter.comparator = ? AND vtiger_cvadvfilter.value = ?) OR (vtiger_cvadvfilter.columnname = ? AND 
        vtiger_cvadvfilter.comparator = ? AND vtiger_cvadvfilter.value = ?) GROUP BY vtiger_cvadvfilter.cvid HAVING COUNT(vtiger_cvadvfilter.cvid) > 1;";
        $pendingWithdResult = $adb->pquery($sql, array("Payments", "1", "1", "vtiger_payments:payment_operation:payment_operation:Payments_LBL_PAYMENT_OPERATION:V", "e", "Withdrawal", "vtiger_payments:payment_status:payment_status:Payments_LBL_PAYMENT_STATUS:V", "e", "Pending"));
        $pendingWithdFilterId = $adb->query_result($pendingWithdResult, 0, 'cvid');
        return $pendingWithdFilterId;
    }

    function getOpenTicketFilterId()
    {
        global $adb;
        $sql = "SELECT vtiger_cvadvfilter.cvid FROM vtiger_cvadvfilter
        INNER JOIN vtiger_customview ON vtiger_cvadvfilter.cvid = vtiger_customview.cvid
        WHERE vtiger_customview.entitytype = ? AND userid = ? AND
        vtiger_cvadvfilter.columnname = ? AND 
        vtiger_cvadvfilter.comparator = ? AND vtiger_cvadvfilter.value = ?;";
        $pendingLiveAccResult = $adb->pquery($sql, array("HelpDesk", "1", "vtiger_troubletickets:status:ticketstatus:HelpDesk_Status:V", "n", "Closed,Cancelled"));
        $openTicketFilterId = $adb->query_result($pendingLiveAccResult, 0, 'cvid');
        return $openTicketFilterId;
    }

}
