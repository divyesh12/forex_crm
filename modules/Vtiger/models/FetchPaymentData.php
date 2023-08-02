<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger FetchPaymentData Model
 */
class Vtiger_FetchPaymentData_Model extends Vtiger_Base_Model {

	public static function getTotalRecords($startDateTime, $endDateTime) {
		global $adb;
        
        $widgetData = [];
        $totalDeposit = $totalWithdrawal = $totalCommission = $totalLots = 0;
        
		$totalDepQuery = "SELECT SUM(vtiger_payments.amount) as total_deposit FROM vtiger_payments INNER JOIN vtiger_crmentity ON vtiger_payments.paymentsid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = ? AND vtiger_payments.payment_operation = ? AND vtiger_payments.payment_status = ? AND vtiger_payments.payment_type = 'P2A' AND vtiger_crmentity.modifiedtime BETWEEN ? AND ?";
		$totalDepQueryResult = $adb->pquery($totalDepQuery, array(0, 'Deposit', 'Completed', $startDateTime, $endDateTime));
        $noOfRecord = $adb->num_rows($totalDepQueryResult);
        if ($noOfRecord > 0) {
            $totalDeposit = $adb->query_result($totalDepQueryResult, 0, 'total_deposit');
        }

        $totalWithdrawalQuery = "SELECT SUM(vtiger_payments.amount) as total_withdrawal FROM vtiger_payments INNER JOIN vtiger_crmentity ON vtiger_payments.paymentsid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = ? AND vtiger_payments.payment_operation = ? AND vtiger_payments.payment_status = ? AND vtiger_payments.payment_type = 'A2P' AND vtiger_crmentity.modifiedtime BETWEEN ? AND ?";
		$totalWithdrawalQueryResult = $adb->pquery($totalWithdrawalQuery, array(0, 'Withdrawal', 'Completed', $startDateTime, $endDateTime));
        $noOfRecord = $adb->num_rows($totalWithdrawalQueryResult);
        if ($noOfRecord > 0) {
            $totalWithdrawal = $adb->query_result($totalWithdrawalQueryResult, 0, 'total_withdrawal');
        }

        /*Fetch Total IB commission*/
        $totalIBCommissionQuery = "SELECT SUM(`commission_amount`) AS total_commission_amount FROM anl_comm_child WHERE close_time BETWEEN ? AND ?";
        $totalIBCommissionQueryResult = $adb->pquery($totalIBCommissionQuery, array($startDateTime, $endDateTime));
        $totalCommission = $adb->query_result($totalIBCommissionQueryResult, 0, 'total_commission_amount');
        /*Fetch Total IB commission*/

        $serviceTypeList = ServiceProvidersManager::getActiveMTProviderList();
        foreach($serviceTypeList as $serverType)
        {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($serverType);
            $totalLotQuery = $provider->getTotalVolumeQuery();
            $allProviderLotQuery = "SELECT SUM(`t`.`volume`) AS `total_lots` FROM `vtiger_liveaccount` AS `l`
            INNER JOIN `vtiger_crmentity` AS `c` ON `l`.`liveaccountid` = `c`.`crmid`
            INNER JOIN (" . $totalLotQuery . ") AS `t` ON `t`.`login` = `l`.`account_no`
            WHERE `c`.`deleted` = 0 AND `l`.`account_no` != 0 AND `l`.`record_status` = 'Approved'";
            $allProviderLotQueryResult = $adb->pquery($allProviderLotQuery, array($startDateTime, $endDateTime));
            $totalLots += number_format($adb->query_result($allProviderLotQueryResult, 0, 'total_lots'), 2);
        }

        $widgetData = array(
                'deposit_count' => number_format($totalDeposit,2),
                'withdrawal_count' => number_format($totalWithdrawal,2),
                'commission_count' => number_format($totalCommission,2),
                'lots_count' => number_format($totalLots,2)
            );
        return $widgetData;
    }
}
