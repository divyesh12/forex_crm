<?php

require_once 'data/VTEntityDelta.php';
/**
 * This function is used to validate request of withdrawal for particular Dibort project
 * @global global $log
 * @param array $entityData
 */
function Payments_WithdrawalCustomValidation($entityData) {
    global $log;
    $log->debug('Entering into Payments_DibortWithdrawValidation');
    $module = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $paymentId = $parts[1];
    
    $validationError = false;
    $validationMsg = '';
    
    $paymentData = $entityData->getData();
    $paymentData['record_id'] = $paymentId;
    
    $entryPointValidation = dibortCommonWithdrawalValidation($paymentData);
    if($entryPointValidation)
    {
        $countValidation = dibortWithdCountValidation($paymentData);
        if($countValidation)
        {
            $dateRangeValidation = dibortWithdDateRangeValidation($paymentData);
            if($dateRangeValidation)
            {
                $equityValidation = dibortWithdEquityValidation($paymentData);
                if($equityValidation)
                {
                    $noOfLotsValidation = dibortWithdNoOfLotsValidation($paymentData);
                    if($noOfLotsValidation)
                    {
                        
                    }
                    else
                    {
                        $validationError = true;
                        $validationMsg = vtranslate('CAB_MSG_WITH_NO_OF_LOTS_VALIDATION_ERROR', $module);
                    }
                }
                else
                {
                    $validationError = true;
                    $validationMsg = vtranslate('CAB_MSG_WITH_EQUITY_VALIDATION_ERROR', $module);
                }
            }
            else
            {
                $validationError = true;
                $validationMsg = vtranslate('CAB_MSG_WITH_DATE_RANGE_VALIDATION_ERROR', $module);
            }
        }
        else
        {
            $validationError = true;
            $validationMsg = vtranslate('CAB_MSG_WITH_COUNT_VALIDATION_ERROR', $module);
        }
    }
    
    if($validationError)
    {
        $log->debug('exception throw-');
        $log->debug($validationMsg);
        throw new Exception($validationMsg);
    }
//    return array('success' => $validationError, 'msg' => $validationMsg);
}

function dibortCommonWithdrawalValidation($paymentData = array())
{
    $status = false;
    if($paymentData['payment_type'] == 'A2P' && in_array($paymentData['payment_status'], array('Pending', 'InProgress')) && (strtolower($paymentData['source']) != 'crm' && $paymentData['source'] != ''))
    {
        $accountNo = $paymentData['payment_from'];
        if(isAccountFromSpecialGroup($accountNo))
        {
            $status = true;
        }
    }
    return $status;
}

function dibortWithdCountValidation($paymentData = array())
{
    global $adb,$log;$log->debug('Entering into dibortWithdCountValidation');$log->debug($paymentData);
    if(!empty($paymentData['payment_status']) && $paymentData['payment_status'] == 'InProgress')
    {
        return true;
    }
    $status = true;
    $contactId = $paymentData['contactid'];
    $paymentId = $paymentData['id'];
    $paymentSql = "SELECT paymentsid FROM vtiger_payments"
        . " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_payments.paymentsid"
        . " WHERE vtiger_crmentity.deleted = 0 AND vtiger_payments.contactid = ? AND vtiger_payments.payment_type = 'A2P'"
        . " AND (MONTH(vtiger_crmentity.createdtime) = MONTH(CURRENT_DATE()) AND YEAR(vtiger_crmentity.createdtime) = YEAR(CURRENT_DATE())) AND vtiger_payments.payment_status NOT IN ('Rejected', 'Cancelled', 'Failed')";
    $paymentResult = $adb->pquery($paymentSql, array($contactId));
    $noOfWithdrawal = $adb->num_rows($paymentResult);
    if ($noOfWithdrawal > 0)
    {
        $status = false;
    }
    return $status;
}

function dibortWithdDateRangeValidation($paymentData = array())
{
    if(!empty($paymentData['payment_status']) && $paymentData['payment_status'] == 'InProgress')
    {
        return true;
    }
    $status = true;
    $singaporeTimezone = 'UTC+08:00';
    @date_default_timezone_set($singaporeTimezone);
    $currentDateTime = date('Y-m-d H:i:s');
    
    if(isset($paymentData['createdtime']) && !empty($paymentData['createdtime']))
    {
        $createdTime = $paymentData['createdtime'];
        $currentDateTime = date("Y-m-d H:i:s", strtotime($createdTime.' +8 hours'));
    }
    
    $startDateTime = date('Y-m').'-01 00:00:00';
    $endDateTime = date('Y-m').'-01 23:59:59';

    if(($startDateTime <= $currentDateTime) && ($currentDateTime <= $endDateTime))
    {
        $status = true;
    }
    else
    {
        $status = false;
    }
    return $status;
}

function dibortWithdEquityValidation($paymentData = array())
{
    global $adb;
    if(!empty($paymentData['payment_status']) && $paymentData['payment_status'] == 'InProgress')
    {
        return true;
    }
    $status = true;
    $tenPercOfequity = 0;
    $accountNo = $paymentData['payment_from'];
    $contactId = $paymentData['contactid'];
    $paymentData['amount'] = str_replace(",", "", $paymentData['amount']);
    $amount = filter_var($paymentData['amount'], FILTER_VALIDATE_FLOAT);
    $liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($accountNo, $contactId);
    $metatraderType = $liveAccountDetails['live_metatrader_type'];
    
    $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatraderType);
    $equityQuery = $provider->getEquity();
    $equityResult = $adb->pquery($equityQuery, array($accountNo));
    $equity = $adb->query_result($equityResult, 0, 'equity');
    
    if(!empty($equity))
    {
        $tenPercOfequity = (((float)$equity)*0.10);
    }
    
    if(($amount <= $tenPercOfequity))
    {
        $status = true;
    }
    else
    {
        $status = false;
    }
    return $status;
}

function dibortWithdNoOfLotsValidation($paymentData = array())
{
    global $adb;
    $status = false;
    $singaporeTimezone = 'UTC+08:00';
    @date_default_timezone_set($singaporeTimezone);
    $currentDateTime = date('Y-m-d H:i:s');
    $startDateTime = date('Y-m-d', strtotime("first day of last month")) . ' 00:00:00';
    $endDateTime = date('Y-m-d', strtotime("last day of last month")) . ' 23:59:59';
    
    $accountNo = $paymentData['payment_from'];
    $contactId = $paymentData['contactid'];
    
    $liveAccountDetails = Payments_Record_Model::getLiveAccountDetails($accountNo);
    $metatraderType = $liveAccountDetails['live_metatrader_type'];
    
    /*get minimum withdrawal lots of particular group*/
    $liveAccQuery = 'SELECT min_lots_for_withdraw FROM vtiger_accountmapping WHERE live_metatrader_type = ? AND live_label_account_type = ? AND live_currency_code = ?';
    $liveAccResult = $adb->pquery($liveAccQuery, array($liveAccountDetails['live_metatrader_type'], $liveAccountDetails['live_label_account_type'], $liveAccountDetails['live_currency_code']));
    $minLotsForWithdraw = $adb->query_result($liveAccResult, 0, 'min_lots_for_withdraw');
    
    /*get total lots of particular account of current month*/
    $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatraderType);
    $lotSql = $provider->getCurrentMonthTotalVolume();
    $lotsResult = $adb->pquery($lotSql, array($accountNo, $startDateTime, $endDateTime));
    $sumOfLots = $adb->query_result($lotsResult, 0, 'total_volume');
    if(empty($sumOfLots))
    {
        $sumOfLots = 0;
    }
    $minLotsForWithdrawFloat = floatval($minLotsForWithdraw);
    $sumOfLotsFloat = floatval($sumOfLots);
    if($sumOfLotsFloat >= $minLotsForWithdrawFloat)
    {
        $status = true;
    }
    return $status;
}
?>