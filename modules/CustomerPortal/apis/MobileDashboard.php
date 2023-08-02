<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_MobileDashboard extends CustomerPortal_API_Abstract {

    function process(CustomerPortal_API_Request $request) {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();


        if ($current_user) {
            $customerId = $this->getActiveCustomer()->id;
            $currency_param = $request->get('currency');

            $total_live_accounts = 0;
            $total_demo_accounts = 0;
            $total_volume = 0;
            $total_open_volume = 0;
            $total_balance = 0;
            $total_equity = 0;
            $total_margin = 0;
            $total_free_margin = 0;
            $total_profit_loss = 0;
            $total_deposit = 0;
            $total_withdrawal = 0;
            $wallet = 0;
            $currencies_wise_trade_user_data = array();
            $currencies_wise_volume_profit_loss_data = array();
            $currecies_wise_live_account = array();
            $currecies_wise_demo_account = array();
            $depo_with_wallet_cur = array();
            $raw = array(
                'wallet' => $wallet,
                'total_live_accounts' => $total_live_accounts,
                'total_demo_accounts' => $total_demo_accounts,
                'total_volume' => $total_volume,
                'total_open_volume' => $total_open_volume,
                'total_balance' => $total_balance,
                'total_equity' => $total_equity,
                'total_margin' => $total_margin,
                'total_free_margin' => $total_free_margin,
                'total_profit_loss' => $total_profit_loss,
                'total_deposit' => $total_deposit,
                'total_withdrawal' => $total_withdrawal
            );

//Get metatraer type list from picklist
//            $live_metatrader_type = CustomerPortal_Utils::getPicklist('live_metatrader_type')->getResult()['live_metatrader_type'];
            //Getting title of meta trader based on active
            $live_metatrader_type = array();
            $provider = ServiceProvidersManager::getActiveProviderInstance();
            for ($i = 0; $i < count($provider); $i++) {
                if ($provider[$i]::PROVIDER_TYPE == 1) {
                    $live_metatrader_type[] = $provider[$i]->parameters['title'];
                }
            }

            $meta_trader_type_string = '"' . implode('", "', $live_metatrader_type) . '"'; //For in query
            //End

            if (!empty($live_metatrader_type)) {
                foreach ($live_metatrader_type as $key => $value) {
                    $meta_trader_type = $value;

                    $provider = ServiceProvidersManager::getActiveInstanceByProvider($value);
                    if (empty($provider)) {
                        throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', 'LiveAccount', $portal_language), 1416);
                    }
                    $syncConfig = $provider->syncConfig;
                    if(!$syncConfig['balance']) {
                        $accountList = getLiveAccountList($customerId, $provider->parameters['title']);
                        $trades_query = $provider->getAccountsDataForMobileDashboard($accountList);
                    } else {
                        $trades_query = $provider->getAccountsDataForMobileDashboard();
                    }

                    $liveaccount_sql = "SELECT `l`.`live_currency_code` AS `currency`, `l`.`live_metatrader_type` AS `metatrader_type`, SUM(`t`.`balance`) AS `total_balance`, SUM(`t`.`equity`)
                        AS `total_equity`, SUM(`t`.`margin`) AS `total_margin`, SUM(`t`.`margin_free`) AS `margin_free`
                        FROM `vtiger_liveaccount` AS `l`
                        INNER JOIN `vtiger_crmentity` AS `c` ON `l`.`liveaccountid` = `c`.`crmid`
                        INNER JOIN (" . $trades_query . ") AS `t` ON `t`.`login` = `l`.`account_no`
                        WHERE `c`.`deleted` = 0 AND `l`.`account_no` != 0 AND `record_status` = 'Approved'
                        AND `l`.`contactid` = " . $customerId . "
                        AND `l`.`live_currency_code` = '" . $currency_param . "'    
                        AND `l`.`live_metatrader_type` = '" . $meta_trader_type . "'";

                    $sqlResult = $adb->pquery($liveaccount_sql, array());
                    $numRow = $adb->num_rows($sqlResult);
                    if ($numRow > 0) {
                        for ($i = 0; $i < $numRow; $i++) {
                            $currency = $adb->query_result($sqlResult, $i, 'currency');
                            if ($currency != '') {
                                $total_balance = $total_balance + $adb->query_result($sqlResult, $i, 'total_balance');
                                $total_equity = $total_equity + $adb->query_result($sqlResult, $i, 'total_equity');
                                $total_margin = $total_margin + $adb->query_result($sqlResult, $i, 'total_margin');
                                $total_free_margin = $total_free_margin + $adb->query_result($sqlResult, $i, 'margin_free');
                            }
                        }
                    }
                    //End
                    
                    $providerType = strtolower(getProviderType($value));
                    //get Total profit loss
                    if ($providerType == 'vertex') {
                        $from_query = " FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c` ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = " . $customerId . "  AND `l`.`live_currency_code` = '" . $currency_param . "' AND `c`.`deleted` = 0 AND `l`.`live_metatrader_type` = '" . $meta_trader_type . "'";  
                        $trades_query = $provider->getTotalVolumeAndProfitLossForMobileDashboard();
                        $trades_query .= " IN(SELECT `l`.`account_no` " . $from_query . ")";
                    } else {
                        $trades_query = $provider->getTotalVolumeAndProfitLossForMobileDashboard();
                        $trades_query = "SELECT `l`.`contactid`, `l`.`live_currency_code` AS `currency`, `l`.`live_metatrader_type` AS `metatrader_type`, `l`.`live_currency_code`, SUM(`t`.`profit`) AS `total_profit_loss` FROM `vtiger_liveaccount` AS `l`
                        INNER JOIN `vtiger_crmentity` AS `c` ON `l`.`liveaccountid` = `c`.`crmid`
                        INNER JOIN (" . $trades_query . ") AS `t` ON `t`.`login` = `l`.`account_no`
                        WHERE `c`.`deleted` = 0 AND `l`.`account_no` != 0 AND `record_status` = 'Approved'
                        AND `l`.`contactid` = " . $customerId . " AND `l`.`live_currency_code` = '" . $currency_param . "' AND `l`.`live_metatrader_type` = '" . $meta_trader_type . "'";
                    }

                    $sqlResult = $adb->pquery($trades_query, array());
                    $numRow = $adb->num_rows($sqlResult);
                    if ($numRow > 0) {
                        for ($i = 0; $i < $numRow; $i++) {
                            $currency = $adb->query_result($sqlResult, $i, 'currency');
                            if ($providerType == 'vertex') {
                                $total_profit_loss = $total_profit_loss + $adb->query_result($sqlResult, $i, 'total_profit_loss');
                            } else {
                                if ($currency != '') {
                                    $total_profit_loss = $total_profit_loss + $adb->query_result($sqlResult, $i, 'total_profit_loss');
                                }
                            }
                        }
                    }

                    //End
                    //Total Volume
                    $from_query = " FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c`
                            ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' 
                            AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = " . $customerId . " "
                        . "AND `l`.`live_currency_code` = '" . $currency_param . "' AND `c`.`deleted` = 0 AND `l`.`live_metatrader_type` = '" . $meta_trader_type . "'";

                    /* Total Volume Query */
                    if ($providerType == 'vertex') {
                        $trades_query = $provider->getTotalVolumeForMobileDashboard();
                        $sqlResult = $adb->pquery($trades_query, array($customerId, $currency_param, $meta_trader_type, $customerId, $currency_param, $meta_trader_type));
                    } else {
                        $trades_query = $provider->getTotalVolumeForMobileDashboard();
                        $trades_query .= " IN(SELECT `l`.`account_no` " . $from_query . ")";
                        $sqlResult = $adb->pquery($trades_query, array());
                    }
                    
                    $numRow = $adb->num_rows($sqlResult);
                    if ($numRow > 0) {
                        for ($i = 0; $i < $numRow; $i++) {
                            $total_volume = $total_volume + $adb->query_result($sqlResult, $i, 'total_volume');
                        }
                    }

                    //Total Open Volume
                    /* Open Volume Query */
                    $trades_query_open = $provider->getOpenVolumeForMobileDashboard();
                    $trades_query_open .= " IN(SELECT `l`.`account_no` " . $from_query . ")";
                    $sqlResultOpen = $adb->pquery($trades_query_open, array());
                    $numRowOpen = $adb->num_rows($sqlResultOpen);
                    if ($numRowOpen > 0) {
                        $total_open_volume = $total_open_volume + $adb->query_result($sqlResultOpen, 0, 'open_volume');
                    }
                    /* End */

                    $live_query = "SELECT `l`.`live_metatrader_type`, `l`.`live_currency_code` AS `currency`, COUNT(1) AS `total_live_account` FROM `vtiger_liveaccount` AS `l` 
                        INNER JOIN `vtiger_crmentity` AS c ON `l`.`liveaccountid` = `c`.`crmid` 
                        WHERE `c`.`deleted` = 0 AND `l`.`record_status` = 'Approved' 
                        AND `l`.`contactid` = " . $customerId . " 
                            AND `l`.`live_currency_code` = '" . $currency_param . "'    
                        AND `l`.`live_metatrader_type` = '" . $meta_trader_type . "'";

                    $sqlResult = $adb->pquery($live_query, array());
                    $numRow = $adb->num_rows($sqlResult);
                    if ($numRow > 0) {
                        for ($i = 0; $i < $numRow; $i++) {
                            $currency = $adb->query_result($sqlResult, $i, 'currency');
                            if ($currency != '') {
                                $total_live_accounts = $total_live_accounts + $adb->query_result($sqlResult, $i, 'total_live_account');
                            }
                        }
                    }

                    $demo_query = "SELECT `d`.`metatrader_type`, `d`.`demo_currency_code` AS `currency`, COUNT(1) AS `total_demo_account` 
                        FROM `vtiger_demoaccount` AS `d` 
                        INNER JOIN `vtiger_crmentity` AS c ON `d`.`demoaccountid` = `c`.`crmid` 
                        WHERE `c`.`deleted` = 0 AND `d`.`contactid` = " . $customerId . " "
                        . "AND `d`.`metatrader_type` = '" . $meta_trader_type . "' AND `d`.`demo_currency_code` = '" . $currency_param . "'";

                    $sqlResult = $adb->pquery($demo_query, array());
                    $numRow = $adb->num_rows($sqlResult);
                    if ($numRow > 0) {
                        for ($i = 0; $i < $numRow; $i++) {
                            $currency = $adb->query_result($sqlResult, $i, 'currency');
                            if ($currency != '') {
                                $total_demo_accounts = $total_demo_accounts + $adb->query_result($sqlResult, $i, 'total_demo_account');
                            }
                        }
                    }

                    //Total Deposit
                    $from_query = " FROM vtiger_liveaccount AS `l` INNER JOIN `vtiger_crmentity`  AS `c` ON `c`.`crmid` = `l`.`liveaccountid` WHERE  `l`.`account_no` != 0 AND `l`.`account_no` != '' AND `l`.`record_status` = 'Approved' AND `l`.`contactid` = " . $customerId . " AND `l`.`live_currency_code` = '" . $currency_param . "' "
                        . "AND `c`.`deleted` = 0 AND `l`.`live_metatrader_type` = '" . $meta_trader_type . "'";

                    //Trades data
                    $trades_query = $provider->getTotalDepositForMobileDashboard();
                    $trades_query .= " IN(SELECT `l`.`account_no` " . $from_query . ")";
                    $sqlResult = $adb->pquery($trades_query, array());
                    $numRow = $adb->num_rows($sqlResult);
                    if ($numRow > 0) {
                        $total_deposit = $total_deposit + $adb->query_result($sqlResult, 0, 'total_deposit');
                    }

                    // Total Withdrawal
                    $trades_query = $provider->getTotalWithdrawalForMobileDashboard();
                    $trades_query .= " IN(SELECT `l`.`account_no` " . $from_query . ")";
                    $sqlResult = $adb->pquery($trades_query, array());
                    $numRow = $adb->num_rows($sqlResult);
                    if ($numRow > 0) {
                        $total_withdrawal = $total_withdrawal + ABS($adb->query_result($sqlResult, 0, 'total_withdrawal'));
                    }
                    //End
                }
            }

            //KYC Verified details
            $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
            $response->addToResult('is_document_verified', vtws_retrieve($contactId, $current_user)['is_document_verified']);

            //wallet currency wise
            $res = CustomerPortal_Utils::getEwalletBalance($this->getActiveCustomer()->id);
            //usort($res, "cmp"); //Need to ascending order based on currency
            $wallet_bal = array();
            if (!empty($res)) {
                foreach ($res as $key => $value) {
                    $wallet_bal[$value['currency']] = array('total_amount' => $value['total_amount']);
                }
            }
            $wallet = (float) $wallet_bal[$currency_param]['total_amount'];

            $raw = array(
                'wallet' => $wallet,
                'total_live_accounts' => $total_live_accounts,
                'total_demo_accounts' => $total_demo_accounts,
                'total_volume' => (float) CustomerPortal_Utils::setNumberFormatWithoutCommaSeparater($total_volume, 2),
                'total_open_volume' => (float) CustomerPortal_Utils::setNumberFormatWithoutCommaSeparater($total_open_volume, 2),
                'total_balance' => $total_balance,
                'total_equity' => $total_equity,
                'total_margin' => (float) CustomerPortal_Utils::setNumberFormatWithoutCommaSeparater($total_margin, 2),
                'total_free_margin' => (float) CustomerPortal_Utils::setNumberFormatWithoutCommaSeparater($total_free_margin, 2),
                'total_profit_loss' => $total_profit_loss,
                'total_deposit' => $total_deposit,
                'total_withdrawal' => $total_withdrawal,
            );

            $response->addToResult('your_total_financials', $raw);
            $ib_commission = 0;
            $ib_commission = CustomerPortal_Utils::getEarnedIBCommission($this->getActiveCustomer()->id);
            $response->addToResult('ib_commission', array('earned_commission' => round($ib_commission, 4), 'currency' => 'USD'));
            return $response;
        }
    }

    public function cmp($a, $b)
    {
        if ($a == $b) {
            return 0;
        }

        return ($a < $b) ? -1 : 1;
    }

}
