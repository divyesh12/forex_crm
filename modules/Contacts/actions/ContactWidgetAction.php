<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
require_once 'modules/Emails/mail.php';

class Contacts_ContactWidgetAction_Action extends Vtiger_Save_Action {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('getIBCommWidgetDetail');
        $this->exposeMethod('getActiveTradersAndActiveIB');
        $this->exposeMethod('getTop5IBCommissionEarnedDetail');
        $this->exposeMethod('getContactSummaryCountWidgetDetail');
        $this->exposeMethod('getTop5TransactionsWidgetDetail');
        $this->exposeMethod('getTop5LiveAccountsWidgetDetail');
        $this->exposeMethod('getTop5TicketsWidgetDetail');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }
    
    public function getIBCommWidgetDetail(Vtiger_Request $request) {
        global $adb;
        $contactId = $request->get('recordId');
        $query = "SELECT sum(t.volume) as volume, sum(IF(commission_amount>0,IF(commission_comment='',t.volume,0),0)) as earned_volume, sum(t.commission_amount) as commission_amount,"
                        . " sum(IF(t.commission_withdraw_status = 1, t.commission_amount, 0)) as withdrawal_completed, "
                        . " sum(IF(t.commission_withdraw_status = 0, t.commission_amount, 0)) as withdrawal_pending"
                        . " FROM tradescommission AS t"
                        . " INNER JOIN vtiger_contactdetails AS c ON c.`contactid` = t.parent_contactid"
                        . " WHERE c.`contactid` = ?";
        $result = $adb->pquery($query, array($contactId));
        
        $response = new Vtiger_Response();
        if($result)
        {
            $total_lots = $adb->query_result($result, 0, 'volume');
            $total_comm_earned = $adb->query_result($result, 0, 'commission_amount');
            $available_comm_amount = $adb->query_result($result, 0, 'withdrawal_pending');
            $total_comm_withdraw = $adb->query_result($result, 0, 'withdrawal_completed');
            $earned_volume = $adb->query_result($result, 0, 'earned_volume');
            
            $commDetail['total_lots'] = !empty($total_lots) ? number_format($total_lots, 4) : 0;
            $commDetail['earned_volume'] = !empty($earned_volume) ? number_format($earned_volume, 4) : 0;
            $commDetail['total_comm_earned'] = !empty($total_comm_earned) ? number_format($total_comm_earned, 4) : 0;
            $commDetail['available_comm_amount'] = !empty($available_comm_amount) ? number_format($available_comm_amount, 4) : 0;
            $commDetail['total_comm_withdraw'] = !empty($total_comm_withdraw) ? number_format($total_comm_withdraw, 4) : 0;
            $result = $commDetail;
            $response->setResult($result);
        }
        else
        {
            $result = vtranslate('COMMISSION_FAILED', $moduleName);
            $response->setError($result);
        }
        $response->emit();
    }

    public function getActiveTradersAndActiveIB(Vtiger_Request $request) {
        global $adb, $current_user;
        $activeTraders = 0;
        $activeIB = 0;
        $contactId = $request->get('recordId');
        $filter = $request->get('filter');        
        $response = new Vtiger_Response();
        $result = getActiveIBAndActiveTrader($contactId, $filter);
        $activeTraders = $result['active_traders'];
        $activeIB = $result['active_ib_traders'];

        /*$live_metatrader_type = array();
        $provider = ServiceProvidersManager::getActiveProviderInstance();
        for ($i = 0; $i < count($provider); $i++) {
            if ($provider[$i]::PROVIDER_TYPE == 1) {
                $live_metatrader_type[] = $provider[$i]->parameters['title'];
            }
        }
        $sqlContactResult = $adb->pquery("SELECT ib_hierarchy FROM vtiger_contactdetails WHERE contactid = ?", array($contactId));
        if ($adb->num_rows($sqlContactResult) > 0) {
            $ib_hierarchy = $adb->query_result($sqlContactResult, 0, 'ib_hierarchy');
        }        
        if (!empty($live_metatrader_type)) {
            foreach ($live_metatrader_type as $key => $value) {
                $where = " AND `c`.`contactid` != " . $contactId . " AND `c`.`ib_hierarchy` LIKE '%" . $ib_hierarchy . "%') AS `t`";
                
                $traderQuery = "SELECT COUNT(IF(`is_ib` IS NULL,1,NULL)) AS `active_traders`, COUNT(`is_ib`) AS `active_ib_traders` FROM (SELECT DISTINCT `c`.`contactid`, IF(`c`.`record_status` = 'Approved',1,NULL) AS `is_ib` FROM `vtiger_contactdetails` AS `c` INNER JOIN `vtiger_liveaccount` AS `l` ON `l`.`contactid` = `c`.`contactid` INNER JOIN ";
                
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($value);
                $metaQuery = $provider->getCloseTradeJoinQuery($filter);
                $traderQuery = $traderQuery . $metaQuery . $where;
                $sqlResult = $adb->pquery($traderQuery, array());
                $numRow = $adb->num_rows($sqlResult);
                if ($numRow > 0) {
                    $activeTraders = $activeTraders + $adb->query_result($sqlResult, 0, 'active_traders');
                    $activeIB = $activeIB + $adb->query_result($sqlResult, 0, 'active_ib_traders');
                }
            }
        }*/

        $traderInfo['active_traders'] = $activeTraders;
        $traderInfo['active_ib'] = $activeIB;
        $result = $traderInfo;
        $response->setResult($result);
        $response->emit();
    }

    public function getTop5IBCommissionEarnedDetail(Vtiger_Request $request) {
        global $adb;
        $contactId = $request->get('recordId');
        $response = new Vtiger_Response();

        $sql = "SELECT child.firstname, child.lastname, child.email, child.affiliate_code, SUM(t.volume) AS volume, SUM(t.commission_amount) AS commission_amount FROM `anl_comm_child` AS t INNER JOIN vtiger_contactdetails AS child ON t.child_contactid = child.contactid INNER JOIN `vtiger_crmentity` AS ce ON child.`contactid` = ce.`crmid` WHERE ce.`deleted` = 0  AND t.parent_contactid = " . $contactId . " AND t.`child_contactid` != " . $contactId . " AND child.`record_status` = 'Approved' GROUP BY t.child_contactid  ORDER BY commission_amount DESC LIMIT 0,5";
        $sqlResult = $adb->pquery($sql, array());
        $numRow = $adb->num_rows($sqlResult);
        $rows = array();
        for ($i = 0; $i < $numRow; $i++) {
            $rows[$i]['ib_earning_name'] = $adb->query_result($sqlResult, $i, 'firstname') . ' ' . $adb->query_result($sqlResult, $i, 'lastname');
            $rows[$i]['ib_earning_email'] = $adb->query_result($sqlResult, $i, 'email');
            $rows[$i]['ib_earning_affiliate_code'] = $adb->query_result($sqlResult, $i, 'affiliate_code');
            $comm = $adb->query_result($sqlResult, $i, 'commission_amount');
            $rows[$i]['ib_earning_commission'] = !empty($comm) ? number_format($comm, 4) : 0;
        }
        $result = $rows;
        $response->setResult($result);
        $response->emit();
    }

    public function getContactSummaryCountWidgetDetail(Vtiger_Request $request) {
        global $adb;
        $contactId = $request->get('recordId');
        $currency = $request->get('currency');
        $recordModel = Contacts_Record_Model::getContactSummaryCount($contactId, $currency);
        $response = new Vtiger_Response();
        $contactSummDetail['total_deposit'] = !empty($recordModel['total_deposit']) ? number_format($recordModel['total_deposit'], 2) : 0;
        $contactSummDetail['total_withdrawal'] = !empty($recordModel['total_withdrawal']) ? number_format($recordModel['total_withdrawal'], 2) : 0;
        $contactSummDetail['total_live_account'] = $recordModel['total_live_account'];
        $contactSummDetail['total_demo_account'] = $recordModel['total_demo_account'];
        $contactSummDetail['total_lots_contact'] = !empty($recordModel['total_lots_contact']) ? number_format($recordModel['total_lots_contact'], 4) : 0;
        $result = $contactSummDetail;
        $response->setResult($result);
        $response->emit();
    }

    public function getTop5TransactionsWidgetDetail(Vtiger_Request $request){
        global $adb;
        $contactId = $request->get('recordId');
        $status = $request->get('status');
        $response = new Vtiger_Response();
        $andWhere = "";
        if ($status != 'All' && $status != '') {
            $andWhere = " AND p.payment_status = "."'$status'";
            if ($status == 'Failed') {
                $andWhere = " AND (p.payment_status = "."'$status'"." || p.payment_status = 'Cancelled')"; 
            }
        }

        $sql = "SELECT p.paymentsid,p.payment_operation,p.amount,p.failure_reason,p.payment_currency,p.payment_status,p.payment_from,p.payment_to,pc.createdtime FROM vtiger_payments as p INNER JOIN vtiger_crmentity as pc ON pc.crmid = p.paymentsid INNER JOIN vtiger_contactdetails as c ON c.contactid = p.contactid WHERE pc.deleted = 0 AND p.contactid = " . $contactId . $andWhere . " ORDER BY p.paymentsid DESC LIMIT 5";
        $sqlResult = $adb->pquery($sql, array());
        $numRow = $adb->num_rows($sqlResult);
        $top5Transactions = array();
        if ($numRow > 0) {
            for ($i=0; $i < $numRow; $i++) {
                $paymentsid = $adb->query_result($sqlResult, $i, 'paymentsid');
                $payment_operation = $adb->query_result($sqlResult, $i, 'payment_operation');
                $amount = $adb->query_result($sqlResult, $i, 'amount');
                $payment_currency = $adb->query_result($sqlResult, $i, 'payment_currency');
                $payment_status = $adb->query_result($sqlResult, $i, 'payment_status');
                $payment_from = $adb->query_result($sqlResult, $i, 'payment_from');
                $payment_to = $adb->query_result($sqlResult, $i, 'payment_to');
                $failure_reason = $adb->query_result($sqlResult, $i, 'failure_reason');
                $created_time = $adb->query_result($sqlResult, $i, 'createdtime');
                $created_time = Vtiger_Datetime_UIType::getDisplayDateTimeValue(date($created_time));
                $created_time = date('d-m-Y h:i:s', strtotime($created_time));
                $recordModel = Payments_Record_Model::getInstanceById($paymentsid);
                $detailViewUrl = $recordModel->getDetailViewUrl();
                $top5Transactions[$i]['payment_from'] = $payment_from;
                $top5Transactions[$i]['payment_to'] = $payment_to;
                $top5Transactions[$i]['paymentsid'] = $paymentsid;
                $top5Transactions[$i]['payment_operation'] = $payment_operation;
                $top5Transactions[$i]['amount'] = $amount;
                $top5Transactions[$i]['payment_currency'] = $payment_currency;
                $top5Transactions[$i]['payment_status'] = $payment_status;
                $top5Transactions[$i]['failure_reason'] = $failure_reason;
                $top5Transactions[$i]['created_time'] = $created_time;
                $top5Transactions[$i]['detailViewUrl'] = $detailViewUrl;
            }
        }
        $result = $top5Transactions;
        $response->setResult($result);
        $response->emit();        
    }

    public function getTop5LiveAccountsWidgetDetail(Vtiger_Request $request){
        global $adb;
        $contactId = $request->get('recordId');
        $status = $request->get('status');
        $response = new Vtiger_Response();
        $andWhere = "";
        if ($status != 'All' && $status != '') {
            $andWhere = " AND l.record_status = "."'$status'"; 
        }

        $sql = "SELECT l.liveaccountid,l.account_no,l.live_label_account_type,l.record_status,l.leverage,l.live_currency_code,l.live_metatrader_type,pc.createdtime FROM vtiger_liveaccount AS l INNER JOIN vtiger_crmentity AS pc ON pc.crmid = l.liveaccountid INNER JOIN vtiger_contactdetails AS c ON c.contactid = l.contactid 
        WHERE pc.deleted = 0 AND l.contactid = " . $contactId . $andWhere . " ORDER BY l.liveaccountid DESC LIMIT 5";
        $sqlResult = $adb->pquery($sql, array());
        $numRow = $adb->num_rows($sqlResult);
        $top5LiveAccount = array();
        if ($numRow > 0) {
            for ($i=0; $i < $numRow; $i++) {
                $liveaccountid = $adb->query_result($sqlResult, $i, 'liveaccountid');
                $account_no = $adb->query_result($sqlResult, $i, 'account_no');
                $live_label_account_type = $adb->query_result($sqlResult, $i, 'live_label_account_type');
                $record_status = $adb->query_result($sqlResult, $i, 'record_status');
                $leverage = $adb->query_result($sqlResult, $i, 'leverage');
                $live_currency_code = $adb->query_result($sqlResult, $i, 'live_currency_code');
                $live_metatrader_type = $adb->query_result($sqlResult, $i, 'live_metatrader_type');
                $created_date = $adb->query_result($sqlResult, $i, 'createdtime');
                $created_date = Vtiger_Datetime_UIType::getDisplayDateTimeValue(date($created_date));
                $created_date = date('d-m-Y h:i:s', strtotime($created_date));
                $recordModel = LiveAccount_Record_Model::getInstanceById($liveaccountid);
                $detailViewUrl = $recordModel->getDetailViewUrl();
                $top5LiveAccount[$i]['liveaccountid'] = $liveaccountid;
                $top5LiveAccount[$i]['account_no'] = $account_no;
                $top5LiveAccount[$i]['live_label_account_type'] = $live_label_account_type;
                $top5LiveAccount[$i]['record_status'] = $record_status;
                $top5LiveAccount[$i]['leverage'] = $leverage;
                $top5LiveAccount[$i]['live_currency_code'] = $live_currency_code;
                $top5LiveAccount[$i]['live_metatrader_type'] = $live_metatrader_type;
                $top5LiveAccount[$i]['created_date'] = $created_date;
                $top5LiveAccount[$i]['detailViewUrl'] = $detailViewUrl;
            }
        }
        $result = $top5LiveAccount;
        $response->setResult($result);
        $response->emit();        
    }

    public function getTop5TicketsWidgetDetail(Vtiger_Request $request){
        global $adb;
        $contactId = $request->get('recordId');
        $status = $request->get('status');
        $dateFilter = $request->get('dateFilter');
        $response = new Vtiger_Response();
        $dateWhere = "";
        $andWhere = "";
        if ($status != 'All' && $status != '') {
            $andWhere = " AND t.status = "."'$status'"; 
        }
        if ($dateFilter != '') {
            $dateFilter = explode(" - ", $dateFilter);
            $start = date("Y-m-d 00:00:01", strtotime($dateFilter[0]));
            $end = date("Y-m-d 23:59:59", strtotime($dateFilter[1]));
            $dateWhere = " AND c.createdtime BETWEEN " . "'$start'" . " AND " . "'$end'";
        }
        $sql = "SELECT t.ticketid,t.ticket_no,t.title,t.priority,t.category,c.createdtime,c.modifiedtime,t.status FROM vtiger_troubletickets AS t INNER JOIN vtiger_crmentity AS c ON c.crmid = t.ticketid INNER JOIN vtiger_contactdetails AS ct ON ct.contactid = t.contact_id WHERE c.deleted = 0 AND ct.contactid = " . $contactId . $andWhere . $dateWhere . " ORDER BY t.ticketid DESC LIMIT 5";

        $sqlResult = $adb->pquery($sql, array());
        $numRow = $adb->num_rows($sqlResult);
        $top5Tickets = array();
        if ($numRow > 0) {
            for ($i=0; $i < $numRow; $i++) {
                $ticketid = $adb->query_result($sqlResult, $i, 'ticketid');
                $ticket_no = $adb->query_result($sqlResult, $i, 'ticket_no');
                $title = $adb->query_result($sqlResult, $i, 'title');
                $priority = $adb->query_result($sqlResult, $i, 'priority');
                $category = $adb->query_result($sqlResult, $i, 'category');
                $createdtime = $adb->query_result($sqlResult, $i, 'createdtime');
                $createdtime = Vtiger_Datetime_UIType::getDisplayDateTimeValue(date($createdtime));
                $modifiedtime = $adb->query_result($sqlResult, $i, 'modifiedtime');
                $status = $adb->query_result($sqlResult, $i, 'status');
                if ($status != 'Closed') {
                    $modifiedtime = '-';
                } else {
                    $modifiedtime = Vtiger_Datetime_UIType::getDisplayDateTimeValue(date($modifiedtime));
                    $modifiedtime = date('d-m-Y h:i:s', strtotime($modifiedtime));
                }
                $createdtime = date('d-m-Y h:i:s', strtotime($createdtime));
                $recordModel = HelpDesk_Record_Model::getInstanceById($ticketid);
                $detailViewUrl = $recordModel->getDetailViewUrl();
                $top5Tickets[$i]['ticket_no'] = $ticket_no;
                $top5Tickets[$i]['title'] = $title;
                $top5Tickets[$i]['priority'] = $priority;
                $top5Tickets[$i]['category'] = $category;
                $top5Tickets[$i]['createdtime'] = $createdtime;
                $top5Tickets[$i]['modifiedtime'] = $modifiedtime;
                $top5Tickets[$i]['status'] = $status;
                $top5Tickets[$i]['detailViewUrl'] = $detailViewUrl;
            }
        }
        $result = $top5Tickets;
        $response->setResult($result);
        $response->emit();        
    }

}
