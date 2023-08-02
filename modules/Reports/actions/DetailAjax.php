<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_DetailAjax_Action extends Vtiger_BasicAjax_Action{
    
    public function __construct() {
        parent::__construct();
		$this->exposeMethod('getRecordsCount');
	}
	
	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'record');
		return $permissions;
	}
    
    public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
    
    /**
	 * Function to get related Records count from this relation
	 * @param <Vtiger_Request> $request
	 * @return <Number> Number of record from this relation
	 */
	public function getRecordsCount(Vtiger_Request $request) {
        global $customReportids,$walletSummary_reportId,$openTrade_reportId,$closeTrade_reportId,$accoutTransaction_reportId,$ibStatistics_reportId,$ibSummary_reportId,$associateAccount_reportId,$payment_reportId,$ibCommissionEarn_reportId,$ibCommissionAnalysis_reportId,$pendingIBCommissionAmount_reportId; //add by divyesh chothani 
		$record = $request->get('record');
		$reportModel = Reports_Record_Model::getInstanceById($record);
		$reportModel->setModule('Reports');
		$reportModel->set('advancedFilter', $request->get('advanced_filter'));
        
        $advFilterSql = $reportModel->getAdvancedFilterSQL();

        //Add by Divyesh Chothani Start
        if($record == $walletSummary_reportId){
        	$reportResult = Reports_Record_Model::getWalletAllTransactionsCabinetUser($outputformat, $operation);
        	$count = count($reportResult);
        }else if($record == $openTrade_reportId){
        	$reportResult = Reports_Record_Model::getTradingReports($outputformat, $operation,$advFilterSql,'open');
        	$count = count($reportResult);
        }else if($record == $closeTrade_reportId){
        	$reportResult = Reports_Record_Model::getTradingReports($outputformat, $operation,$advFilterSql,'close');
        	$count = count($reportResult);
        }else if($record == $accoutTransaction_reportId){
            $reportResult = Reports_Record_Model::getTradingTransactions($outputformat, $operation,$advFilterSql);
            $count = count($reportResult);
        }else if($record == $ibStatistics_reportId){
            $reportResult = Reports_Record_Model::getIBStatistics($outputformat, $operation,$advFilterSql);
            unset($reportResult['summary']);
            $count = count($reportResult);
        }else if($record == $ibSummary_reportId){
            $reportResult = Reports_Record_Model::getIBSummary($outputformat, $operation,$advFilterSql);
            $count = count($reportResult);
        }else if($record == $associateAccount_reportId){
            $reportResult = Reports_Record_Model::getAssociatedAccounts($outputformat, $operation,$advFilterSql);
            $count = count($reportResult);
        }else if($record == $payment_reportId){
            $reportResult = Reports_Record_Model::getPaymentData($outputformat, $operation,$advFilterSql);
            unset($reportResult['summary']);
            $count = count($reportResult);
        }else if($record == $ibCommissionEarn_reportId){
            $reportResult = Reports_Record_Model::getIbCommissionEarnedData($outputformat, $operation,$advFilterSql);
            unset($reportResult['summary']);
            $count = count($reportResult);
        }else if($record == $ibCommissionAnalysis_reportId){
            $reportResult = Reports_Record_Model::getIbCommissionAnalyzedData($outputformat, $operation,$advFilterSql);
            unset($reportResult['summary']);
            $count = count($reportResult);
        }else if($record == $pendingIBCommissionAmount_reportId){
            $reportResult = Reports_Record_Model::getIbPendingCommissionData($outputformat, $operation,$advFilterSql);
            $count = count($reportResult);
        }else{
        	$query = $reportModel->getReportSQL($advFilterSql, 'PDF');
        	$countQuery = $reportModel->generateCountQuery($query);
        	$count = $reportModel->getReportsCount($countQuery);
        }
        //Add by Divyesh Chothani End
        $response = new Vtiger_Response();
        $response->setResult($count);
        $response->emit();
    }
    
}