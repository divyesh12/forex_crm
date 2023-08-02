<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once 'modules/ServiceProviders/ServiceProviders.php';

class CustomerPortal_DescribePaymentGateway extends CustomerPortal_API_Abstract
{

    protected $translate_module = 'CustomerPortal_Client';

    public function process(CustomerPortal_API_Request $request)
    {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $module = $request->get('module');
            // Deposit, Withdrwal ot Transfer
            $payment_operation = $request->get('payment_operation');
            // Name of service provider
            $paymentName = $request->get('payment_name');
            if(!empty($paymentName)) {
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($paymentName);
                $providerType = $provider->getName();
            }
            /*
             * It will check module in vtiger_customerportal_tabs table. Need to add module if not added
             */
            if (!CustomerPortal_Utils::isModuleActive($module)) {

                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }

            //Check configuration added by sandeep 20-02-2020
            CustomerPortal_Utils::checkConfiguration($contactId, $current_user, $module, $values, $portal_language);
            //End
            //It will fetch the approved live account with currency
            $liveaccount_data = CustomerPortal_Utils::getLiveAccountsWithWalletId($this->getActiveCustomer()->id, $current_user, $providerType);
            $providerParameters = array();

            if ($payment_operation == 'InternalTransfer') {
                $wallet_arr = CustomerPortal_Utils::getEwalletBalance($this->getActiveCustomer()->id);
                $wallet_currencies = '';
                foreach ($wallet_arr as $key => $value) {
                    $wallet_currencies = $wallet_currencies . $value['currency'] . ',';
                }
                $wallet_currencies = rtrim($wallet_currencies, ',');
                $liveaccount_data[count($liveaccount_data) - 1]['wallet_currencies'] = $wallet_currencies;
                $providerParameters['liveaccount_data'] = $liveaccount_data;
                $providerParameters['internaltransfer_account_min'] = configvar('min_account_transfer');
                $providerParameters['internaltransfer_account_max'] = configvar('max_account_transfer');
                $providerParameters['internaltransfer_ewallet_min'] = configvar('min_ewallet_transfer');
                $providerParameters['internaltransfer_ewallet_max'] = configvar('max_ewallet_transfer');
                //Get Trasnfer Min-Max value
                $providerParameters['transfer_details'] = $this->getTransferMinMaxValues();
                //End
                $response->addToResult('paymentdescribe', $providerParameters);
                return $response;
            }

            if ($paymentName) {
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($paymentName);
                if ($provider) {
                    if ($provider::PROVIDER_TYPE == 2) {
                        $payment_logo_path = $provider->parameters['payment_logo_path'];

                        if ($payment_operation == 'Deposit') {
                            $deposit_supported = $provider->parameters['deposit_supported'];
                            if ($deposit_supported == 'Yes') {
                                $form_data = $provider::getDepositFormParams();
                                $requiredParams = $provider::getRequiredParams();
                                $transfer_details = array();
                                $other_details = array();
                                $currencyconverter = array();
                                $j = 0;
                                for ($i = 0; $i < count($requiredParams); $i++) {
                                    if ($requiredParams[$i]['block'] == $provider::TRANSFER_DETAILS_BLOCK) {
                                        $transfer_details['transfer_details'][] = array('key' => $requiredParams[$i]['name'], 'label' => getTranslatedString($requiredParams[$i]['label'], $module, $portal_language), 'value' => $provider->parameters[$requiredParams[$i]['name']], 'display_type' => $requiredParams[$i]['display_type']);
                                    }
                                    if ($requiredParams[$i]['block'] == $provider::OTHER_DETAILS_BLOCK) {
                                        $other_details['other_details'][] = array('key' => $requiredParams[$i]['name'], 'label' => vtranslate($requiredParams[$i]['label'], $module, $portal_language), 'value' => $provider->parameters[$requiredParams[$i]['name']]);
                                    }
                                }

                                $is_supports_currency_convertor = CustomerPortal_Utils::isCurrencyConversionSupport($provider->parameters['currency_conversion']);

                                //Check if Currency convertor Support or not
                                //if ($provider->SUPPORTS_CURRENCY_CONVERTOR) {
                                if ($is_supports_currency_convertor) {
                                    $currencyconverter = CustomerPortal_Utils::getCurrecnySupportConvertor($payment_operation);
                                }

                                $providerParameters = array(
                                    //'payment_logo' => CustomerPortal_Utils::setPaymentGatewayLogo($paymentName),
                                    'payment_logo' => CustomerPortal_Utils::setPaymentGatewayLogoPath($payment_logo_path),
                                    'payment_name' => $paymentName,
                                    'currencies' => $provider->parameters['currencies'],
                                    'commission' => $provider->parameters['commission'],
                                    'deposit_min' => $provider->parameters['deposit_min'],
                                    'deposit_max' => $provider->parameters['deposit_max'],
                                    'allowed_decimal' => $provider->parameters['allowed_decimal'],
                                    'processing_time' => $provider->parameters['deposit_processing_time'],
                                    'deposit_allow_from' => $provider->parameters['deposit_allow_from'],
                                    'transfer_details' => $transfer_details['transfer_details'],
                                    'other_details' => $other_details['other_details'],
                                    //'is_supports_currency_convertor' => (object)$provider->SUPPORTS_CURRENCY_CONVERTOR,
                                    'is_supports_currency_convertor' => (object) $is_supports_currency_convertor,
                                    'currency_convertor_rate' => (object) $currencyconverter,
                                    'form_data' => $form_data,
                                );

                                if (isset($paymentName) && $paymentName == 'FairPay') {
                                    if ($provider->parameters['test_mode'] == 'Yes') {
                                        $apiUrlToken = $provider->parameters['test_url_token'];
                                        $apiUrlPaymentMethod = $provider->parameters['test_url_payment_method'];
                                    } else {
                                        $apiUrlToken = $provider->parameters['base_url_token'];
                                        $apiUrlPaymentMethod = $provider->parameters['base_url_payment_method'];
                                    }
                                }
                                // else if (isset($paymentName) && $paymentName == 'NowPay') {
                                //     if ($provider->parameters['test_mode'] == 'No') {
                                //         $key = array_search('case', array_column($providerParameters['form_data'], 'name'));
                                //         unset($providerParameters['form_data'][$key]);
                                //     }
                                // }

                            } else {
                                throw new Exception($paymentName . vtranslate('CAB_MSG_DOES_NOT_SUPPORTS_THE_DEPOSIT', $module, $portal_language), 1414);
                                exit;
                            }

                            $providerParameters['liveaccount_data'] = $liveaccount_data;
                            $response->addToResult('paymentdescribe', $providerParameters);
                        } else if ($payment_operation == 'Withdrawal') {
                            $withdrawal_supported = $provider->parameters['withdrawal_supported'];
                            if ($withdrawal_supported == 'Yes') {
                                $form_data = $provider::getWithdrawFormParams();
                                $requiredParams = $provider::getRequiredParams();
                                $transfer_details = array();
                                $other_details = array();
                                $currencyconverter = array();
                                $j = 0;
                                for ($i = 0; $i < count($requiredParams); $i++) {
                                    if ($requiredParams[$i]['block'] == $provider::TRANSFER_DETAILS_BLOCK) {
                                        $transfer_details['transfer_details'][] = array('key' => $requiredParams[$i]['name'], 'label' => $requiredParams[$i]['label'], 'value' => $provider->parameters[$requiredParams[$i]['name']], 'display_type' => $requiredParams[$i]['display_type']);
                                    }
                                    if ($requiredParams[$i]['block'] == $provider::OTHER_DETAILS_BLOCK) {
                                        if ($requiredParams[$i]['name'] != 'bank_details' && $requiredParams[$i]['name'] != 'client_qr_code' && $requiredParams[$i]['name'] != 'wallet_address' && $requiredParams[$i]['name'] != 'wallet_currency') {
                                            $other_details['other_details'][] = array('key' => $requiredParams[$i]['name'], 'label' => $requiredParams[$i]['label'], 'value' => $provider->parameters[$requiredParams[$i]['name']]);
                                        }

                                    }
                                }

                                $is_supports_currency_convertor = CustomerPortal_Utils::isCurrencyConversionSupport($provider->parameters['currency_conversion']);
                                //Check if Currency convertor Support or not
                                if ($is_supports_currency_convertor) {
                                    $currencyconverter = CustomerPortal_Utils::getCurrecnySupportConvertor($payment_operation);
                                }

                                $providerParameters = array(
                                    //'payment_logo' => CustomerPortal_Utils::setPaymentGatewayLogo($paymentName),
                                    'payment_logo' => CustomerPortal_Utils::setPaymentGatewayLogoPath($payment_logo_path),
                                    'payment_name' => $paymentName,
                                    'currencies' => $provider->parameters['currencies'],
                                    'commission' => $provider->parameters['commission'],
                                    'withdrawal_min' => $provider->parameters['withdrawal_min'],
                                    'withdrawal_max' => $provider->parameters['withdrawal_max'],
                                    'allowed_decimal' => $provider->parameters['allowed_decimal'],
                                    'processing_time' => $provider->parameters['withdrawal_processing_time'],
                                    'withdrawal_allow_from' => $provider->parameters['withdrawal_allow_from'],
                                    'transfer_details' => $transfer_details['transfer_details'],
                                    'other_details' => $other_details['other_details'],
                                    'is_supports_currency_convertor' => (object) $is_supports_currency_convertor,
                                    'currency_convertor_rate' => (object) $currencyconverter,
                                    'form_data' => $form_data,
                                );
                            } else {
                                throw new Exception($paymentName . vtranslate('CAB_MSG_DOES_NOT_SUPPORTS_THE_WITHDRAW', $module, $portal_language), 1414);
                                exit;
                            }

                            $providerParameters['liveaccount_data'] = $liveaccount_data;
                            $response->addToResult('paymentdescribe', $providerParameters);
                        } else {
                            throw new Exception(vtranslate('CAB_MSG_OPERATION_DOESNOT_MATCH', $module, $portal_language), 1413);
                            exit;
                        }
                    } else {
                        throw new Exception(vtranslate('CAB_MSG_PAYMENT_PROVIDER_DOES_NOT_ENABLED', $module, $portal_language), 1415);
                        exit;
                    }
                } else {
                    throw new Exception(vtranslate("LBL_PAYMENT_PROVIDER_NOT_MATCH", $module, $portal_language), 1413);
                    exit;
                }
            } else {
                throw new Exception(vtranslate('CAB_MSG_PAYMENT_NAME_MISSING', $module, $portal_language), 1413);
                exit;
            }
        }
        return $response;
    }

    public function getTransferMinMaxValues()
    {
        return array(
            array(
                "key" => "internaltransfer_account_min",
                "label" => "CAB_LBL_MINIMUM_TRANSFER",
                "value" => configvar('min_account_transfer'),
                "display_type" => "Account",
            ),
            array(
                "key" => "internaltransfer_account_max",
                "label" => "CAB_LBL_MAXIMUM_TRANSFER",
                "value" => configvar('max_account_transfer'),
                "display_type" => "Account",
            ),
            array(
                "key" => "internaltransfer_ewallet_min",
                "label" => "CAB_LBL_MINIMUM_TRANSFER",
                "value" => configvar('min_ewallet_transfer'),
                "display_type" => "Wallet",
            ),
            array(
                "key" => "internaltransfer_ewallet_max",
                "label" => "CAB_LBL_MAXIMUM_TRANSFER",
                "value" => configvar('max_ewallet_transfer'),
                "display_type" => "Wallet",
            ),
        );
    }

}
