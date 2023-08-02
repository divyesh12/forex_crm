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

class DemoAccount_Record_Model extends Vtiger_Record_Model {

    function getMetaTradeUpcommingSeqNo_old($module, $metatrader_type, $account_type, $label_account_type, $currency) {
        global $adb, $account_search_range;
        $account_no = 0;
        $isAllowSeries = configvar('demoaccount_common_series_range');
        $isAllowGroupSeries = configvar('demoaccount_group_series_range');

        $account_search_range = $account_search_range[$metatrader_type];
        if ($isAllowSeries && !$isAllowGroupSeries) {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
            $start_range = (int) $provider->parameters['demoacc_start_range'];
            $end_range = (int) $provider->parameters['demoacc_end_range'];
            $is_change_common_series = (int) $provider->parameters['is_change_common_series'];
            $start_range_length = strlen($start_range);
            $account_search_range = $start_range;
            if ($start_range_length > 1) {
                $account_search_range = $start_range_length - 1;
            }
            $firstcharstr = substr($start_range, 0, $account_search_range);
            // $firstcharstr = substr($start_range, 0, $account_search_range);
//            $sql = "SELECT  max(vtiger_demoaccount.account_no) as account_no  FROM  vtiger_demoaccount "
//                    . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =vtiger_demoaccount.demoaccountid "
//                    . "WHERE vtiger_crmentity.deleted = 0 AND vtiger_demoaccount.metatrader_type ='" . $metatrader_type . "'  AND  vtiger_demoaccount.account_no LIKE '" . $firstcharstr . "%'  ";
            $sql = "SELECT  max(vtiger_demoaccount.account_no) as account_no  FROM  vtiger_demoaccount "
                    . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =vtiger_demoaccount.demoaccountid "
                    . "WHERE vtiger_crmentity.deleted = 0 AND vtiger_demoaccount.metatrader_type = ? AND   vtiger_demoaccount.account_no LIKE '" . $firstcharstr . "%'   ORDER BY  vtiger_demoaccount.demoaccountid DESC "; //vtiger_demoaccount.account_no = ?
            $result = $adb->pquery($sql, array($metatrader_type, $start_range));
            $noofrows = $adb->num_rows($result);
            if ($adb->query_result($result, 0, 'account_no') == 0) {
                $maxNumber = $start_range;
            } else {
                $account_no = $adb->query_result($result, 0, 'account_no');
                //$maxNumber = $account_no + 1;
                $maxNumber = DemoAccount_Record_Model::getNextAccountNo($metatrader_type, $account_no);

                if (isset($end_range) && !in_array($account_no, range($start_range, $end_range))) {
                    $maxNumber = $start_range;
                }
            }

            $account_no = $maxNumber;
        } elseif (!$isAllowSeries && $isAllowGroupSeries) {
            $group_series_data = getDemoAccountSeriesBaseOnAccountType($metatrader_type, $account_type, $label_account_type, $currency);
            $start_range = (int) $group_series_data['start_range'];
            $end_range = (int) $group_series_data['end_range'];
            $start_range_length = strlen($start_range);
            $account_search_range = $start_range;
            if ($start_range_length > 1) {
                $account_search_range = $start_range_length - 1;
            }
            $firstcharstr = substr($start_range, 0, $account_search_range);
//                $sql = "SELECT   max(vtiger_demoaccount.account_no) as account_no  FROM  vtiger_demoaccount "
//                        . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =vtiger_demoaccount.demoaccountid "
//                        . "WHERE vtiger_crmentity.deleted = 0 and vtiger_demoaccount.dem_demo_account_type = '" . $account_type . "' and  vtiger_demoaccount.account_no LIKE '" . $firstcharstr . "%'";
            $sql = "SELECT   max(vtiger_demoaccount.account_no) as account_no  FROM  vtiger_demoaccount "
                    . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =vtiger_demoaccount.demoaccountid "
                    . "WHERE vtiger_crmentity.deleted = 0 AND vtiger_demoaccount.metatrader_type =? AND  vtiger_demoaccount.demo_label_account_type =  ?  AND vtiger_demoaccount.demo_currency_code =? AND vtiger_demoaccount.account_no LIKE '" . $firstcharstr . "%'  ORDER BY  vtiger_demoaccount.demoaccountid DESC ";

            $result = $adb->pquery($sql, array($metatrader_type, $label_account_type, $currency));
//            echo "<pre>";
//            print_r($result);
//            exit;
            $noofrows = $adb->num_rows($result);
            if ($adb->query_result($result, 0, 'account_no') == 0) {
                $maxNumber = $start_range;
            } else {
                $account_no = $adb->query_result($result, 0, 'account_no');
                //$maxNumber = $account_no + 1;
                $maxNumber = DemoAccount_Record_Model::getNextAccountNo($metatrader_type, $account_no);
                if (isset($end_range) && !in_array($account_no, range($start_range, $end_range))) {
                    $maxNumber = $start_range;
                }
            }
            $account_no = $maxNumber;
        }
        return $account_no;
    }

    function getMetaTradeUpcommingSeqNo($module, $metatrader_type, $account_type, $label_account_type, $currency) {
        global $adb, $account_search_range;
        $account_no = 0;
        $demoAccountMethod = configvar('demo_account_no_method');
        if($demoAccountMethod == 'common_series')
        {
            $isAllowSeries = true;
        }
        else if($demoAccountMethod == 'group_series')
        {
            $isAllowGroupSeries = true;
        }

        $account_search_range = $account_search_range[$metatrader_type];
        if ($isAllowSeries && !$isAllowGroupSeries) {
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
            $start_range = (int) $provider->parameters['demoacc_start_range'];
            $end_range = (int) $provider->parameters['demoacc_end_range'];
//            $is_change_series = (int) $provider->parameters['is_update_series_ranges'];
//            $start_range_length = strlen($start_range);
//            $account_search_range = $start_range;
//            if ($start_range_length > 1) {
//                $account_search_range = $start_range_length - 1;
//            }
//            $firstcharstr = substr($start_range, 0, $account_search_range);
            // $firstcharstr = substr($start_range, 0, $account_search_range);
//            $sql = "SELECT  max(vtiger_demoaccount.account_no) as account_no  FROM  vtiger_demoaccount "
//                    . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =vtiger_demoaccount.demoaccountid "
//                    . "WHERE vtiger_crmentity.deleted = 0 AND vtiger_demoaccount.metatrader_type ='" . $metatrader_type . "'  AND  vtiger_demoaccount.account_no LIKE '" . $firstcharstr . "%'  ";
            $sql = "SELECT MAX( vtiger_demoaccount.account_no )  AS account_no FROM vtiger_demoaccount INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_demoaccount.demoaccountid WHERE vtiger_crmentity.deleted =0
AND vtiger_demoaccount.metatrader_type = ? AND vtiger_demoaccount.account_no >=? AND vtiger_demoaccount.account_no <=? LIMIT 1";
            $result = $adb->pquery($sql, array($metatrader_type, $start_range, $end_range));
            $noofrows = $adb->num_rows($result);
            $account_no = $adb->query_result($result, 0, 'account_no');

            if ($account_no) {
                //$account_no = $adb->query_result($result, 0, 'account_no');
                //$maxNumber = $account_no + 1;
                $maxNumber = DemoAccount_Record_Model::getNextAccountNo($metatrader_type, $account_no);
//                if (isset($end_range) && !in_array($account_no, range($start_range, $end_range))) {
//                    $maxNumber = $start_range;
//                }
            } else {
                $maxNumber = $start_range;
            }

            $account_no = $maxNumber;
        } elseif (!$isAllowSeries && $isAllowGroupSeries) {
            $group_series_data = getDemoAccountSeriesBaseOnAccountType($metatrader_type, $account_type, $label_account_type, $currency);
            $start_range = (int) $group_series_data['start_range'];
            $end_range = (int) $group_series_data['end_range'];
//            $start_range_length = strlen($start_range);
//            $account_search_range = $start_range;
//            if ($start_range_length > 1) {
//                $account_search_range = $start_range_length - 1;
//            }
//            $firstcharstr = substr($start_range, 0, $account_search_range);
//                $sql = "SELECT   max(vtiger_demoaccount.account_no) as account_no  FROM  vtiger_demoaccount "
//                        . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =vtiger_demoaccount.demoaccountid "
//                        . "WHERE vtiger_crmentity.deleted = 0 and vtiger_demoaccount.dem_demo_account_type = '" . $account_type . "' and  vtiger_demoaccount.account_no LIKE '" . $firstcharstr . "%'";
//            $sql = "SELECT   max(vtiger_demoaccount.account_no) as account_no  FROM  vtiger_demoaccount "
//                    . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =vtiger_demoaccount.demoaccountid "
//                    . "WHERE vtiger_crmentity.deleted = 0 AND vtiger_demoaccount.metatrader_type =? AND  vtiger_demoaccount.demo_label_account_type =  ?  AND vtiger_demoaccount.demo_currency_code =? AND vtiger_demoaccount.account_no LIKE '" . $firstcharstr . "%'  ORDER BY  vtiger_demoaccount.demoaccountid DESC ";

            $sql = "SELECT MAX(vtiger_demoaccount.account_no)  AS account_no FROM vtiger_demoaccount INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_demoaccount.demoaccountid WHERE vtiger_crmentity.deleted =0
AND vtiger_demoaccount.metatrader_type = ? AND  vtiger_demoaccount.demo_label_account_type =  ?  AND vtiger_demoaccount.demo_currency_code =? AND vtiger_demoaccount.account_no >=? AND vtiger_demoaccount.account_no <=? LIMIT 1";

            $result = $adb->pquery($sql, array($metatrader_type, $label_account_type, $currency, $start_range, $end_range));
//            echo "<pre>";
//            print_r($result);
//            exit;
            $noofrows = $adb->num_rows($result);
            $account_no = $adb->query_result($result, 0, 'account_no');
            if ($account_no) {
                // $account_no = $adb->query_result($result, 0, 'account_no');
                //$maxNumber = $account_no + 1;
                $maxNumber = DemoAccount_Record_Model::getNextAccountNo($metatrader_type, $account_no);
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
        $sql = "SELECT  COUNT(vtiger_demoaccount.account_no) AS total_account_no FROM  vtiger_demoaccount "
                . "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid =vtiger_demoaccount.demoaccountid "
                . "WHERE vtiger_crmentity.deleted = 0 AND vtiger_demoaccount.metatrader_type = ?  AND vtiger_demoaccount.account_no = ?  LIMIT 1 ";
        $result = $adb->pquery($sql, array($metatrader_type, $account_no));
        $noofrows = $adb->num_rows($result);
        $row_result = $adb->fetchByAssoc($result);
        $total_account_no = $row_result['total_account_no'];
        if ($total_account_no) {
            $login = $account_no + 1;
            $account_no = $login;
            return DemoAccount_Record_Model::getNextAccountNo($metatrader_type, $account_no);
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

    /**
     *  Add by Divyesh- 16-06-2019
     * comment:- edit and delete link from listing page
     */
    public function checkRecordStatus($record) {
        if (!empty($record)) {
            $module = $this->getModule();
            if ($record) {
                return false;
            }
        }
        return true;
    }

    /*
     * @creator: Divyesh
     */

    public function checkAccountCreationLimit($contactid) {
        global $adb;
        $demoaccount_creation_limit = configvar('demoaccount_creation_limit');
        $sql = 'SELECT count(demoaccountid) AS total_account FROM  `vtiger_demoaccount` INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_demoaccount.demoaccountid WHERE  vtiger_crmentity.deleted = 0 AND vtiger_demoaccount.contactid =?';
        $result = $adb->pquery($sql, array($contactid));
        $total_account = $adb->query_result($result, 0, 'total_account');

        if ($total_account >= $demoaccount_creation_limit) {
            return true;
        }
        return false;
    }

//    public function updateCommonSeriesRangeStauts($metatrader_type) {
//        global $adb;
//        $sql = "SELECT parameters FROM  `vtiger_serviceproviders_servers` WHERE isactive =? AND providertype =? ";
//        $result = $adb->pquery($sql, array(1, $metatrader_type));
//        $row_result = $adb->fetchByAssoc($result);
//        $json_parameters = Zend_Json::decode(decode_html($row_result['parameters']));
//        $json_parameters['is_change_common_series'] = 0;
//        $encode_parameters_string = Zend_Json::encode($json_parameters);
//
//        $update = "UPDATE `vtiger_serviceproviders_servers` SET `parameters`=? WHERE isactive =? AND providertype =? ";
//        $adb->pquery($update, array($encode_parameters_string, 1, $metatrader_type));
//    }
}
