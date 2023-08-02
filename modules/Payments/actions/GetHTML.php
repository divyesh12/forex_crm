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
 * @creator: Divyesh Chothani
 * @comment:  return provider form html
 * @date: 17-10-2019
 * */
require_once('modules/ServiceProviders/ServiceProviders.php');

class Payments_GetHTML_Action extends Vtiger_BasicAjax_Action {

    function __construct() {
        parent::__construct();
    }

    function process(Vtiger_Request $request) {
        global $root_directory;
        $module = $request->get('module');
        $provider = $request->get('provider');
        $payment_operation = $request->get('payment_operation');
        $payment_type = $request->get('payment_type');
        if ($provider != '' && $payment_operation == 'Deposit' && ($payment_type == 'P2A' || $payment_type == 'P2E')) {
            $payment_provider = $provider;
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_provider);
            if (!empty($provider)) {
                $getRequiredParams = $provider->getDepositFormParams();
            }
        } else if ($provider != '' && $payment_operation == 'Withdrawal' && ($payment_type == 'A2P' || $payment_type == 'E2P')) {
            $payment_provider = $provider;
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_provider);
            if (!empty($provider)) {
                $getRequiredParams = $provider->getWithdrawFormParams();
            }
        }

        if (!empty($provider) && !empty($getRequiredParams)) {
            $getHtml = Payments_Module_Model::getHtml($module, $getRequiredParams);
            $result = array(1, $getHtml);
        } else {
            $getHtml = '<tr class="payment_provider_fields">' . vtranslate('LBL_NO_PAYMENT_DETAILS', $module) . '</tr>';
            $result = array(0, $getHtml);
        }
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

}
