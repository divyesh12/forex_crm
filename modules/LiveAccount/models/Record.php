<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Add By Divyesh Chothani
 * Date:- 01-09-2019
 */
require_once('modules/ServiceProviders/ServiceProviders.php');

class LiveAccount_Record_Model extends Vtiger_Record_Model {
    /**
     * Listing fields which are decryption
     */
//      public function decryptedListingFields() {
//            //portal password  field of contact module :- (contactid ; (Contacts) portal_password)
//            $fieldList = array('password', 'investor_password');
//            return $fieldList;
//      }

    /**
     * Customize the display value for Listing  view and related module summary view.
     */
//      public function getDecryptedValue($value) {
//            global $form_security_key, $encrypt_method;
//            $decryptedString = string_Encrypt_Decrypt($value, 'D', $form_security_key, $encrypt_method);
//            return $decryptedString;
//      }

    /**
     *  Add By Divyesh Chothani
     * Date:- 06-03-2019
     * Comment:- edit and delete link from liveaccount deposit summary listing page
     */
    public function checkRecordStatus($record) {
        if (!empty($record)) {
            $module = $this->getModule();
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
            $modelData = $recordModel->getData();
            if ($modelData['record_status'] == "Approved" || $modelData['record_status'] == "Disapproved") {
                return false;
            }
        }
        return true;
    }

    /**
     *  Add By Divyesh Chothani
     * Date:- 06-03-2019
     * Comment:- return max number of liveaccount
     */
    function getMetaTradeUpcommingSeqNo_old($module, $metatrader_type) {
        global $adb, $account_search_range;
        $isAllowSeries = configvar('liveaccount_common_series_range');
        $account_no = 0;
        if ($isAllowSeries) {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
            $start_range = $provider->parameters['liveacc_start_range'];
            $end_range = $provider->parameters['liveacc_end_range'];
            $account_search_range = $account_search_range[$metatrader_type];

            $firstcharstr = substr($start_range, 0, $account_search_range);
            $sql = "SELECT  max(vtiger_liveaccount.account_no) as account_no  FROM  vtiger_liveaccount "
                    . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =vtiger_liveaccount.liveaccountid "
                    . "WHERE vtiger_crmentity.deleted = 0 AND vtiger_liveaccount.live_metatrader_type ='" . $metatrader_type . "'  AND  vtiger_liveaccount.account_no LIKE '" . $firstcharstr . "%'  ";

            $result = $adb->pquery($sql, array());
            $noofrows = $adb->num_rows($result);
            if ($adb->query_result($result, 0, 'account_no') == 0) {
                $maxNumber = $start_range;
            } else {
                $account_no = $adb->query_result($result, 0, 'account_no');
                $maxNumber = $account_no + 1;
            }
            $account_no = $maxNumber;
        }
//    elseif ($isAllowSeries == false && $isAllowSeriesGroupBase == true) {
//        if ($module == 'LiveAccount') {
//            $firstcharstr = substr($liveacc_group_range[$account_type]['start_range'], 0, 1);
//            $sql = "SELECT  max(vtiger_liveaccount.account_no) as account_no FROM  vtiger_liveaccount "
//                    . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =vtiger_liveaccount.liveaccountid  "
//                    . "WHERE vtiger_crmentity.deleted = 0 and vtiger_liveaccount.account_type = '" . $account_type . "'  and vtiger_liveaccount.account_no LIKE '" . $firstcharstr . "%' ";
//
//            $result = $adb->pquery($sql, array());
//            $noofrows = $adb->num_rows($result);
//            if ($adb->query_result($result, 0, 'account_no') == 0) {
//                $maxNumber = $liveacc_group_range[$account_type]['start_range'];
//            } else {
//                $account_no = $adb->query_result($result, 0, 'account_no');
//                $maxNumber = $account_no + 1;
//            }
//        } elseif ($module == 'DemoAccount') {
//
//            $firstcharstr = substr($demoacc_group_range[$account_type]['start_range'], 0, 1);
//
//            $sql = "SELECT   max(vtiger_liveaccount.account_no) as account_no  FROM  vtiger_liveaccount "
//                    . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =vtiger_liveaccount.liveaccountid "
//                    . "WHERE vtiger_crmentity.deleted = 0 and vtiger_liveaccount.dem_demo_account_type = '" . $account_type . "' and  vtiger_liveaccount.account_no LIKE '" . $firstcharstr . "%'";
//            $result = $adb->pquery($sql, array());
//            $noofrows = $adb->num_rows($result);
//            if ($adb->query_result($result, 0, 'account_no') == 0) {
//                $maxNumber = $demoacc_group_range[$account_type]['start_range'];
//            } else {
//                $account_no = $adb->query_result($result, 0, 'account_no');
//                $maxNumber = $account_no + 1;
//            }
//        }
//        $account_no = $maxNumber;
//    }
        return $account_no;
    }

    /**
     *  Add By Divyesh Chothani
     * Date:- 06-03-2019
     * Comment:- return max number of liveaccount
     */
    function getMetaTradeUpcommingSeqNo($module, $metatrader_type, $account_type, $label_account_type, $currency) {
        global $adb, $account_search_range;
        
        $isAllowSeries = $isAllowGroupSeries = false;
        $liveAccountMethod = configvar('live_account_no_method');
        if($liveAccountMethod == 'common_series')
        {
            $isAllowSeries = true;
        }
        else if($liveAccountMethod == 'group_series')
        {
            $isAllowGroupSeries = true;
        }

        $account_no = 0;
        if ($isAllowSeries && !$isAllowGroupSeries) {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
            $start_range = (int) $provider->parameters['liveacc_start_range'];
            $end_range = (int) $provider->parameters['liveacc_end_range'];


            $sql = "SELECT MAX( vtiger_liveaccount.account_no)  AS account_no FROM vtiger_liveaccount INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid WHERE vtiger_crmentity.deleted =0
AND vtiger_liveaccount.live_metatrader_type = ? AND vtiger_liveaccount.account_no >=? AND vtiger_liveaccount.account_no <=? LIMIT 1";
            $result = $adb->pquery($sql, array($metatrader_type, $start_range, $end_range));
            $noofrows = $adb->num_rows($result);
            $account_no = $adb->query_result($result, 0, 'account_no');

            if ($account_no) {
                //$account_no = $adb->query_result($result, 0, 'account_no');
                //$maxNumber = $account_no + 1;
                $maxNumber = LiveAccount_Record_Model::getNextAccountNo($metatrader_type, $account_no);
//                if (isset($end_range) && !in_array($account_no, range($start_range, $end_range))) {
//                    $maxNumber = $start_range;
//                }
            } else {
                $maxNumber = $start_range;
            }

            $account_no = $maxNumber;
        } elseif (!$isAllowSeries && $isAllowGroupSeries) {
            $group_series_data = getLiveAccountSeriesBaseOnAccountType($metatrader_type, $account_type, $label_account_type, $currency);
            $start_range = (int) $group_series_data['start_range'];
            $end_range = (int) $group_series_data['end_range'];

            $sql = "SELECT MAX(vtiger_liveaccount.account_no)  AS account_no FROM vtiger_liveaccount INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid WHERE vtiger_crmentity.deleted =0
AND vtiger_liveaccount.live_metatrader_type = ? AND  vtiger_liveaccount.live_label_account_type =  ?  AND vtiger_liveaccount.live_currency_code =? AND vtiger_liveaccount.account_no >=? AND vtiger_liveaccount.account_no <=? LIMIT 1";
            $result = $adb->pquery($sql, array($metatrader_type, $label_account_type, $currency, $start_range, $end_range));

            $noofrows = $adb->num_rows($result);
            $account_no = $adb->query_result($result, 0, 'account_no');
            if ($account_no) {
                // $account_no = $adb->query_result($result, 0, 'account_no');
                //$maxNumber = $account_no + 1;
                $maxNumber = LiveAccount_Record_Model::getNextAccountNo($metatrader_type, $account_no);
//                if (isset($end_range) && !in_array($account_no, range($start_range, $end_range))) {
//                    $maxNumber = $start_range;
//                }
            } else {
                $maxNumber = $start_range;
            }
            $account_no = $maxNumber;
        }
        return $account_no;
    }

    function getNextAccountNo($metatrader_type, $account_no) {
        global $adb;
        //$account_no = $account_no + 1;
        $sql = "SELECT  COUNT(vtiger_liveaccount.account_no) AS total_account_no FROM  vtiger_liveaccount "
                . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =vtiger_liveaccount.liveaccountid "
                . "WHERE vtiger_crmentity.deleted = 0 AND vtiger_liveaccount.live_metatrader_type = ?  AND vtiger_liveaccount.account_no = ?  LIMIT 1 ";
        $result = $adb->pquery($sql, array($metatrader_type, $account_no));
        $noofrows = $adb->num_rows($result);
        $row_result = $adb->fetchByAssoc($result);
        $total_account_no = $row_result['total_account_no'];
        if ($total_account_no) {
            $login = $account_no + 1;
            $account_no = $login;
            return LiveAccount_Record_Model::getNextAccountNo($metatrader_type, $account_no);
        } else {
            return $account_no;
        }
    }

    /**
     * function to generate random strings
     * @param int $length number of characters in the generated string
     * @return string a new string is created with random characters of the desired length
     */
    function RandomString($length = 32) {
        $randstr;
        srand((double) microtime(TRUE) * 1000000);
//our array add all letters and numbers if you wish
        $chars = array(
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'p',
            'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5',
            '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
            'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '!', '@', '%', '^', '*', '(', ')');
        for ($rand = 0; $rand < $length; $rand++) {
            $random = rand(0, count($chars) - 1);
            $randstr .= $chars[$random];
        }
        return $randstr;
    }

    function string_Encrypt_Decrypt($string, $action = 'E', $secret_key, $encrypt_method) {
        $secret_iv = $secret_key;
        $output = false;
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ($action == 'E') {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        } else if ($action == 'D') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
    
    public function checkAccountCreationLimit($contactid) {
        global $adb;
        $liveaccCreationLimit = configvar('liveaccount_creation_limit');
        $sql = 'SELECT count(liveaccountid) AS total_account FROM  `vtiger_liveaccount` INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid WHERE vtiger_crmentity.deleted = 0 AND vtiger_liveaccount.contactid =? AND record_status IN ("Approved","Pending")';
        $result = $adb->pquery($sql, array($contactid));
        $totalAccount = $adb->query_result($result, 0, 'total_account');

        if ($totalAccount >= $liveaccCreationLimit) {
            return true;
        }
        return false;
    }
    
    public function getcontactIdFromAccounNo($accountno = '', $isDemo = false)
    {
        global $adb;
        $contactId = '';
        if(!empty($accountno))
        {
            $table = 'vtiger_liveaccount';
            if($isDemo)
            {
                $table = 'vtiger_demoaccount';
            }
            $sql = 'SELECT contactid FROM '.$table.' WHERE account_no =? limit 1';
            $result = $adb->pquery($sql, array($accountno));
            $contactId = $adb->query_result($result, 0, 'contactid');
        }
        return $contactId;
    }

}
