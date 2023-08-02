<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once('modules/ServiceProviders/ServiceProviders.php');

class CustomerPortal_FetchPaymentGateways extends CustomerPortal_API_Abstract {

    protected $translate_module = 'CustomerPortal_Client';

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;
        if ($current_user) {
            $module = $request->get('module');
            // Deposit, Withdrwal ot Transfer
            $payment_operation = $request->get('payment_operation');

            /*
             * It will check module in vtiger_customerportal_tabs table. Need to add module if not added
             */
            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }

            $paymentGetways = array();
            $provider = ServiceProvidersManager::getActiveProviderInstance();
            if ($payment_operation == 'Deposit') {
                for ($i = 0; $i < count($provider); $i++) {
                    if ($provider[$i]->parameters['cabinet_deposit_supported'] != 'No')  {
                        if ($provider[$i]::PROVIDER_TYPE == 2) {                        
                            $payment_logo_path = $provider[$i]->parameters['payment_logo_path'];
                            
                            $provider_name = $provider[$i]::getName();
                            $payment_name = $provider[$i]->parameters['title'];
                            if ($payment_name == 'Wallet' && !configvar('ewallet_to_tradingaccount')) {
                                
                            } else {
                                if ($provider[$i]->parameters['deposit_supported'] == 'Yes' && $provider_name != 'BankOffline') {
                                $payment_iframe_url = '';
                                 if($provider_name === 'IframePSP')
                                 {
                                     $payment_iframe_url = $provider[$i]->parameters['payment_link'];
                                 }
                                    $paymentGetways[] = array(
                                        //'payment_logo' => CustomerPortal_Utils::setPaymentGatewayLogo($payment_name),
                                        'payment_logo' => CustomerPortal_Utils::setPaymentGatewayLogoPath($payment_logo_path),
                                        'payment_name' => $payment_name,
                                        'currencies' => $provider[$i]->parameters['currencies'],
                                        'commission' => $provider[$i]->parameters['commission'],
                                        'deposit_min' => $provider[$i]->parameters['deposit_min'],
                                        'deposit_max' => $provider[$i]->parameters['deposit_max'],
                                        'processing_time' => $provider[$i]->parameters['deposit_processing_time'],
                                        'payment_iframe_url' => $payment_iframe_url
                                    );
                                
                                }
                            }
                        }
                        }
                    }
                $response->addToResult('paymentgatewaylist', $paymentGetways);
            } else if ($payment_operation == 'Withdrawal') {
                for ($i = 0; $i < count($provider); $i++) {
                    if ($provider[$i]::PROVIDER_TYPE == 2) {
                        $payment_logo_path = $provider[$i]->parameters['payment_logo_path'];
                        $provider_name = $provider[$i]::getName();
                        $payment_name = $provider[$i]->parameters['title'];
                        if ($payment_name == 'Wallet' && !configvar('tradingaccount_to_ewallet')) {
                            
                        } else {
                            if ($provider[$i]->parameters['withdrawal_supported'] == 'Yes' && $provider_name != 'BankOffline') {
                                $paymentGetways[] = array(
                                    //'payment_logo' => CustomerPortal_Utils::setPaymentGatewayLogo($payment_name),
                                    'payment_logo' => CustomerPortal_Utils::setPaymentGatewayLogoPath($payment_logo_path),
                                    'payment_name' => $payment_name,
                                    'currencies' => $provider[$i]->parameters['currencies'],
                                    'commission' => $provider[$i]->parameters['commission'],
                                    'withdrawal_min' => $provider[$i]->parameters['withdrawal_min'],
                                    'withdrawal_max' => $provider[$i]->parameters['withdrawal_max'],
                                    'processing_time' => $provider[$i]->parameters['withdrawal_processing_time']
                                );
                            }
                            }
                    }
                }
                $response->addToResult('paymentgatewaylist', $paymentGetways);
            } else {
                throw new Exception(vtranslate('CAB_MSG_OPERATION_DOESNOT_MATCH', $this->translate_module, $portal_language), 1412);
                exit;
            }
        }
        return $response;
    }

}
