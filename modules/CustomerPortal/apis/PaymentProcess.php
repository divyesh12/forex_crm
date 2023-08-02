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

class CustomerPortal_PaymentProcess extends CustomerPortal_API_Abstract
{

    protected $recordValues = false;
    protected $mode = 'edit';
    protected $translate_module = 'CustomerPortal_Client';

    public function process(CustomerPortal_API_Request $request)
    {
        global $adb, $log;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;
        $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
        $contact = vtws_retrieve($contactId, $current_user); //Getting user full details from contact_details table
        if ($current_user) {
            $module = $request->get('module');
            $sub_operation = $request->get('sub_operation');
            /*
             * It will check module in vtiger_customerportal_tabs table. Need to add module if not added
             */
            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module), 1412);
                exit;
            }

            //It will help to convert from values JSONstring to array
            $valuesJSONString = $request->get('values', '', false);
            $values = "";

            if (!empty($valuesJSONString) && is_string($valuesJSONString)) {
                $values = Zend_Json::decode($valuesJSONString);
            } else {
                $values = $valuesJSONString; // Either empty or already decoded.
            }

            //END
            //Check configuration added by sandeep 20-02-2020
            CustomerPortal_Utils::checkConfiguration($contactId, $current_user, $module, $values, $portal_language);
            //End

            /* Validation for emoji text input Start */
            foreach ($values as $key => $value) {
                $checkInput = CustomerPortal_Utils::hasEmoji($value);
                if ($checkInput) {
                    throw new Exception(vtranslate('CAB_LBL_PLEASE_ENTER_VALID_CHARACTER', $this->translate_module), 1413);
                    exit;
                }
            }
            /* Validation for emoji text input End */

            /* Validation for non english characters input */
            foreach ($values as $key => $value) {
                if (strlen($value) == mb_strlen($value, 'utf-8')) {
                } else {
                    throw new Exception(vtranslate('CAB_LBL_ENTER_ONLY_ENGLISH_CHARACTER', $this->translate_module), 1413);
                    exit;
                }
            }
            /* Validation for non english characters input end */

            if (isset($values) && !empty($values)) {

                /*Queue validation*/
                if (!empty($sub_operation) && $sub_operation === 'Confirm') {
                    $paymentData = array();
                    $paymentData['payment_type'] = $values['payment_type'];
                    $paymentData['contact_id'] = $this->getActiveCustomer()->id;
                    $isPaymentInQueue = isPaymentInQueue($paymentData);

                    if ($isPaymentInQueue) {
                        throw new Exception(vtranslate('CAB_PAYMENT_IN_QUEUE_ERROR', $this->translate_module), 1413);exit;
                    } else {
                        insertPaymentInQueue($paymentData);
                    }
                }
                /*Queue validation*/

                //Get input data from values
                $payment_operation = $values['payment_operation'];
                $payment_from = $values['payment_from'];
                $payment_type = $values['payment_type'];
                $payment_currency = $values['payment_currency'];
                $payment_to = $values['payment_to'];
                $amount = $values['amount'];
                $values['wallet_id'] = $contact['contact_no'];
                $values['contactid'] = $this->getActiveCustomer()->id;
                //End
                if ($payment_operation == 'Deposit') {
                    $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_from);
                }

                if ($payment_operation == 'Withdrawal') {
                    $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_to);
                }

                $provider_parameters = array();
                $payment_process = '';

                if ($payment_operation == 'IBCommission') {
                    $provider = 1;
                }

                if ($provider) {

                    if ($payment_operation == 'Deposit') {

                        $form_data = $provider::getDepositFormParams();
                        $deposit_supported = $provider->parameters['deposit_supported'];
                        if ($deposit_supported == 'Yes') {
                            $provider_parameters = $provider->parameters;
                        } else {
                            throw new Exception(vtranslate('CAB_MSG_DEPOSIT_DOES_NOT_SUPPORTS_THE', $module) . $payment_from . vtranslate('CAB_MSG_PAYMENT_GATEWAY', $module), 1414);
                            exit;
                        }

                        $commission_amount = 0;
                        $net_amount = $amount;
                        $values['commission'] = 0;
                        if ($provider_parameters['commission'] != 0 && $provider_parameters['commission'] > 0) {
                            $commission_amount = ($amount * $provider_parameters['commission']) / 100;
                            $net_amount = $amount + $commission_amount;
                            $values['commission'] = $provider_parameters['commission'];
                        }
                        $values['commission_amount'] = $commission_amount;
                        $values['net_amount'] = $net_amount;

                        //Verify the user input like amount,
                        CustomerPortal_Utils::verifyInputData($values, $provider_parameters, $module, $portal_language);

                        //Check if Currency convertor Support or not
                        $values = $this->getCurrecnySupportConvertor($provider, $values, $net_amount, $payment_operation, $module);

                        //Check validation in provider
                        $res = $provider->getVerifyTransferDetails($values, $_FILES, $portal_language);
                        if (empty($res) || !$res['success']) {
                            throw new Exception(vtranslate($res['message'], $module), 1416);
                            exit;
                        }
                        //Process on Submit from cabinet or mobile side
                        $attachment_upload_response = array(); //Which stored the Document save response
                        if ($sub_operation == 'Submit') {
                            //Set label and key format for visible data on confirm time
                            if (count($_FILES) > 0) {
                                $attachments = $this->doUploadAttachment($_FILES, $current_user, $form_data, $portal_language);
                            }

                            $custom_data = $this->getCustomFormData($form_data, $values, $module, $attachments);
                            //End

                            if ($values['payment_type'] == 'P2A') {
                                if ($values['payment_from'] == 'Wallet') {
                                    $payment_process = 'PSP';
                                    $values['payment_from'] = 'Wallet';
                                } else {
                                    $payment_process = 'PSP';
                                }
                            }
                            if ($values['payment_type'] == 'P2E') {
                                $payment_process = 'PSP';
                                $values['payment_to'] = 'Wallet';
                                //$values['wallet_id'];
                            }
                            $response->addToResult('payment_process', $payment_process);
                            $response->addToResult('payment_from', $values['payment_from']);
                            $response->addToResult('payment_to', $values['payment_to']);
                            $response->addToResult('payment_currency', $values['payment_currency']);
                            $response->addToResult('amount', $values['amount']);
                            $response->addToResult('commission', $values['commission']);
                            $response->addToResult('commission_amount', $values['commission_amount']);
                            $response->addToResult('net_amount', $values['net_amount']);
                            $response->addToResult('payment_type', $values['payment_type']);
                            $response->addToResult('wallet_id', $values['wallet_id']);
                            $response->addToResult('contactid', $values['contactid']);
                            $response->addToResult('payment_term_conditions', $provider->parameters['deposit_term_conditions']);
                            $response->addToResult('custom_data', $custom_data);
                        } else if ($sub_operation == 'Confirm') {
                            //Process on Confirm from cabinet
                            if ($values['payment_type'] == 'P2A') {
                                if ($provider->parameters['deposit_allow_from'] == 'Wallet') {
                                    throw new Exception(vtranslate('CAB_MSG_WITHDRAW_NOT_ALLOWED_AT_THIS_MOMENT', $module), 1416);
                                    exit;
                                }
                            }
                            if ($values['payment_type'] == 'P2E') {
                                if($provider->parameters['deposit_allow_from'] == 'Account') {
                                    throw new Exception(vtranslate('CAB_MSG_WITHDRAW_NOT_ALLOWED_AT_THIS_MOMENT', $module), 1416);
                                    exit;
                                }
                            }
                            $res = $provider->paymentProcess($values, $portal_language);
                            if (!empty($res) && $res['success']) {
                                $payment_status = $res['result']['payment_status'];
                                $type = $res['result']['type'];
                                $redirect_url = $res['result']['redirect_url'];
                                $order_id = $res['result']['order_id'];


                                if ($values['payment_type'] == 'P2A') {
                                    if ($values['payment_from'] == 'Wallet') {
                                        $payment_process = 'PSP';
                                        $values['payment_from'] = $values['wallet_id'];
                                    } else {
                                        $payment_process = 'PSP';
                                    }
                                }
                                if ($values['payment_type'] == 'P2E') {
                                    $payment_process = 'PSP';
                                    $values['payment_to'] = $values['wallet_id'];
                                }

                                //Set Custom payment data for displaying at CRM side
                                $custom_data = $this->getCustomFormData($form_data, $values, $module, []);
                                //End

                                $tmpArr = [];
                                foreach ($custom_data as $key => $cusdata) {
                                    if ($cusdata['name'] == 'vaultspay_payment_ref_id') {
                                        $cusdata['value'] = $res['result']['order_data']->data->paymentId;
                                    }
                                    if (is_array($res['result']['order_data']) && isset($res['result']['order_data'][$cusdata['name']])) {
                                        $cusdata['value'] = $res['result']['order_data'][$cusdata['name']];
                                    }
                                    $tmpArr[] = $cusdata;
                                }
                                $custom_data = $tmpArr;

                                $this->recordValues = array(
                                    'payment_operation' => $payment_operation,
                                    'amount' => $amount,
                                    'commission' => $provider_parameters['commission'],
                                    'commission_value' => $values['commission_amount'],
                                    'contactid' => $contactId,
                                    'payment_amount' => $values['net_amount'],
                                    'payment_currency' => $payment_currency,
                                    'payment_process' => $payment_process,
                                    'payment_status' => $payment_status,
                                    'payment_type' => $values['payment_type'],
                                    'payment_from' => $values['payment_from'],
                                    'payment_to' => $values['payment_to'],
                                    'request_from' => 'CustomerPortal',
                                    'order_id' => $order_id,
                                    'custom_data' => empty($custom_data) ? '' : json_encode($custom_data),
                                );
                                // set assigned user to default assignee
                                //$this->recordValues['assigned_user_id'] = CustomerPortal_Settings_Utils::getDefaultAssignee();
                                $this->recordValues['assigned_user_id'] = '19x' . getRecordOwnerId($this->getActiveCustomer()->id)['Users']; //contact's assignee will assign to sub module record
                                //Setting source to customer portal
                                $this->recordValues['source'] = $current_user->column_fields['source']; //'CUSTOMER PORTAL';
                                $this->recordValues = vtws_create($module, $this->recordValues, $current_user);
                                if (!empty($this->recordValues)) {
                                    $record_id = $this->recordValues['id'];

                                    //Link document with Payment module
                                    $this->linkAttachmentWithPayment($record_id, $form_data, $values);
                                    //End

                                    $response->addToResult('type', $type);
                                    $response->addToResult('url', $redirect_url);
                                    $response->addToResult('payment_from', $payment_from);
                                    $response->addToResult('order_id', $order_id);
                                    $response->addToResult('record_id', $record_id);

                                    if (isset($res['result']['result_form']) && !empty($res['result']['result_form'])) {
                                        $response->addToResult('result_form', $res['result']['result_form']);
                                    }
                                    if (isset($res['result']['order_data']) && !empty($res['result']['order_data'])) {
                                        $response->addToResult('order_data', json_encode($res['result']['order_data']));
                                    }

                                    if (isset($res['result']['service_provider_type']) && !empty($res['result']['service_provider_type'])) {
                                        $response->addToResult('service_provider_type', $res['result']['service_provider_type']);
                                    }

                                    $response->addToResult('message', 'Confirmed');
                                    if ($type == 'Manual' && $this->recordValues['payment_status'] != 'Failed') {
                                        $response->addToResult('message', CustomerPortal_Utils::setMessage($module, $this->recordValues['payment_status'], $payment_operation, $portal_language));
                                    } else if ($this->recordValues['payment_status'] != 'Failed') {

                                    } else if ($this->recordValues['payment_status'] == 'Failed') {
                                        throw new Exception(vtranslate($this->recordValues['failure_reason'], $module), 1414);
                                        exit;
                                    } else {
                                        throw new Exception(vtranslate('CAB_MSG_TRANSACTION_HAS_BEEN_FAILED', $module), 1414);
                                        exit;
                                    }
                                }
                            } else {
                                throw new Exception(vtranslate($res['message'], $module), 1416);
                                exit;
                            }
                        } else {
                            throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module), 1413);
                            exit;
                        }
                    } else if ($payment_operation == 'Withdrawal') {
                        global $resendOtpDuration;
                        $customerId = $this->getActiveCustomer()->id;
                        $portal_language = $this->getActiveCustomer()->portal_language;
                        $isWithdrawalOTPEnable = configvar('mobile_withdrawal_otp');
                        $resendOtpDurationVal = $resendOtpDuration;
                        if(strtolower($current_user->column_fields['source']) == 'customer portal')
                        {
                            $isWithdrawalOTPEnable = configvar('cabinet_withdrawal_otp');
                            list($clientIp, $otherIp) = explode(',', $_SERVER['HTTP_CLIENTIP']);
                            $values['ip_address'] = $clientIp;
                        }
                        
                        $form_data = $provider::getWithdrawFormParams();
                        $withdrawal_supported = $provider->parameters['withdrawal_supported'];
                        if ($withdrawal_supported == 'Yes') {
                            $provider_parameters = $provider->parameters;
                        } else {
                            throw new Exception($payment_from . vtranslate('CAB_MSG_DOES_NOT_SUPPORTS_THE_WITHDRAW', $module), 1414);
                            exit;
                        }

                        $commission_amount = 0;
                        $net_amount = $amount;
                        $values['commission'] = 0;
                        if ($provider_parameters['commission'] != 0 && $provider_parameters['commission'] > 0) {
                            $commission_amount = ($amount * $provider_parameters['commission']) / 100;
                            $net_amount = $amount - $commission_amount;
                            $values['commission'] = $provider_parameters['commission'];
                        }
                        $values['commission_amount'] = $commission_amount;
                        $values['net_amount'] = $net_amount;

                        //Verify the user input like amount,
                        CustomerPortal_Utils::verifyInputData($values, $provider_parameters, $module, $portal_language);
                        //End
                        //Check available blance to Account and Wallet
                        CustomerPortal_Utils::checkBalance($values['payment_type'], $values['payment_from'], $net_amount, $values['payment_currency'], $module, $this->getActiveCustomer()->id, $portal_language);

                        //Check if Currency convertor Support or not
                        $values = $this->getCurrecnySupportConvertor($provider, $values, $net_amount, $payment_operation, $module);

                        //Check validation in provider
                        $res = $provider->getVerifyTransferDetails($values, $_FILES, $portal_language);
                        if (empty($res) || !$res['success']) {
                            throw new Exception(vtranslate($res['message'], $module), 1416);
                            exit;
                        }

                        //Process on Submit from cabinet or mobile side
                        if ($sub_operation == 'Submit') {
                            /*Send OTP if enabled*/
                            if($isWithdrawalOTPEnable)
                            {
                                $request->set('type', 'withdrawal');
                                customerPortal_Utils::sendOtp($request, $customerId, $portal_language);
                            }
                            $custom_data = $this->getCustomFormData($form_data, $values, $module, []);
                            //End
                            $payment_process = '';
                            if ($values['payment_type'] == 'A2P') {
                                $payment_process = 'Account';
                            }
                            if ($values['payment_type'] == 'E2P') {
                                $payment_process = 'Wallet';
                            }
                            $response->addToResult('payment_process', $payment_process);
                            $response->addToResult('payment_from', $values['payment_from']);
                            $response->addToResult('payment_to', $values['payment_to']);
                            $response->addToResult('payment_currency', $values['payment_currency']);
                            $response->addToResult('amount', $values['amount']);
                            $response->addToResult('commission', $values['commission']);
                            $response->addToResult('commission_amount', $values['commission_amount']);
                            $response->addToResult('net_amount', $values['net_amount']);
                            $response->addToResult('payment_type', $values['payment_type']);
                            $response->addToResult('wallet_id', $values['wallet_id']);
                            $response->addToResult('contactid', $values['contactid']);
                            $response->addToResult('payment_term_conditions', $provider->parameters['withdrawal_term_conditions']);
                            $response->addToResult('custom_data', $custom_data);
                            $response->addToResult('is_otp_enable', $isWithdrawalOTPEnable);
                            $response->addToResult('ip_address', $values['ip_address']);
                            $response->addToResult('resend_otp_duration', $resendOtpDurationVal);
                        } else if ($sub_operation == 'Confirm') {
                            if ($contact['withdraw_allow'] == 0)
                            {
                                throw new Exception(vtranslate('CAB_LBL_WITHDRAWAL_NOT_ALLOWED', $module), 1416);
                                exit;
                            }
                            /*Check for valid OTP if OTP authentication enabled*/
                            if($isWithdrawalOTPEnable)
                            {
                                $request->set('type', 'withdrawal');
                                customerPortal_Utils::verifyOtp($request, $customerId, $portal_language);
                            }
                            /*Check for valid OTP if OTP authentication enabled*/
                            
                            
                            //Process on Confirm from cabinet
                            
                            if ($values['payment_type'] == 'A2P') {
                                if ($provider->parameters['withdrawal_allow_from'] == 'Wallet') {
                                    throw new Exception(vtranslate('CAB_MSG_WITHDRAW_NOT_ALLOWED_AT_THIS_MOMENT', $module), 1416);
                                    exit;
                                }
                            }
                            if ($values['payment_type'] == 'E2P') {
                                if($provider->parameters['withdrawal_allow_from'] == 'Account') {
                                    throw new Exception(vtranslate('CAB_MSG_WITHDRAW_NOT_ALLOWED_AT_THIS_MOMENT', $module), 1416);
                                    exit;
                                }
                            }                            
                            
                            $res = $provider->paymentProcess($values, $portal_language);
                            if (!empty($res) && $res['success']) {
                                $payment_status = $res['result']['payment_status'];
                                $type = $res['result']['type'];
                                $redirect_url = $res['result']['redirect_url'];
                                $order_id = $res['result']['order_id'];
                                $message = $res['result']['message'];
                                $payment_process = '';
                                if ($values['payment_type'] == 'A2P') {
                                    $payment_process = 'Account';
                                    if ($values['payment_to'] == 'Wallet') {
                                        $values['payment_to'] = $values['wallet_id'];
                                    }
                                }
                                if ($values['payment_type'] == 'E2P') {
                                    $payment_process = 'Wallet';
                                    if ($values['payment_from'] == 'Wallet') {
                                        $values['payment_from'] = $values['wallet_id'];
                                    }
                                }
                                //Set Custom payment data for displaying at CRM side
                                $custom_data = $this->getCustomFormData($form_data, $values, $module, []);
                                //End
                                $this->recordValues = array(
                                    'payment_operation' => $payment_operation,
                                    'amount' => $amount,
                                    'commission' => $provider_parameters['commission'],
                                    'commission_value' => $values['commission_amount'],
                                    'contactid' => $contactId,
                                    'payment_amount' => $values['net_amount'],
                                    'payment_currency' => $payment_currency,
                                    'payment_process' => $payment_process,
                                    'payment_status' => $payment_status,
                                    'payment_type' => $values['payment_type'],
                                    'payment_from' => $values['payment_from'],
                                    'payment_to' => $values['payment_to'],
                                    'request_from' => 'CustomerPortal',
                                    'order_id' => $order_id,
                                    'custom_data' => empty($custom_data) ? '' : json_encode($custom_data),
                                    'ip_address' => $values['ip_address'],
                                );
                                // set assigned user to default assignee
                                //$this->recordValues['assigned_user_id'] = CustomerPortal_Settings_Utils::getDefaultAssignee();
                                $this->recordValues['assigned_user_id'] = '19x' . getRecordOwnerId($this->getActiveCustomer()->id)['Users']; //contact's assignee will assign to sub module record
                                //Setting source to customer portal
                                $this->recordValues['source'] = $current_user->column_fields['source']; //'CUSTOMER PORTAL';
                                $this->recordValues = vtws_create($module, $this->recordValues, $current_user);
                                if (!empty($this->recordValues)) {
                                    $record_id = $this->recordValues['id'];
                                    $response->addToResult('type', $type);
                                    $response->addToResult('url', $redirect_url);
                                    $response->addToResult('payment_from', $payment_from);
                                    $response->addToResult('order_id', $order_id);
                                    $response->addToResult('record_id', $record_id);
                                    $response->addToResult('payment_status', $this->recordValues['payment_status']);
                                    if ($this->recordValues['payment_status'] == 'Failed') {
                                        $response->addToResult('message', vtranslate($this->recordValues['failure_reason'], $module));
                                    } else {
                                        $response->addToResult('message', customerPortal_Utils::setMessage($module, $this->recordValues['payment_status'], $payment_operation, $portal_language));
                                    }
                                }
                            } else {
                                throw new Exception(vtranslate($res['message'], $module), 1416);
                                exit;
                            }
                        } else {
                            throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module), 1413);
                            exit;
                        }
                    } else if ($payment_operation == 'IBCommission') {
                        global $log;
                        $isAllow_multiple_commission_within_singleday = configvar('allow_multiple_ibcommission_sameday');
                        
                        /* add additional validation that check any pending request of withdrwal */
                        $parent_contactid = $this->getActiveCustomer()->id;
                        $paymentChecksql = "SELECT count(paymentsid) no_of_payment FROM vtiger_payments"
                            . " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_payments.paymentsid"
                            . " WHERE vtiger_crmentity.deleted = 0 AND payment_status = 'Pending' AND payment_type = 'C2E' AND contactid = '$parent_contactid'";
                        $paymentCheckResult = $adb->pquery($paymentChecksql, array());
                        $noOfPayment = $adb->query_result($paymentCheckResult, 0, 'no_of_payment');
                        if ($noOfPayment > 0) {
                            throw new Exception(vtranslate('CAB_PAYMENT_IN_QUEUE_ERROR', $module), 1413);
                            exit;
                        }
                        /* add additional validation that check any pending request of withdrwal */
                        
                        //Fetch current earned commission
                        $amount = CustomerPortal_Utils::getEarnedIBCommission($this->getActiveCustomer()->id);
                        $min_ib_comm_withdraw = configvar('min_ib_comm_withdraw');
                        if ($min_ib_comm_withdraw != '' && $amount < $min_ib_comm_withdraw) {
                            throw new Exception(vtranslate('CAB_MSG_MINI_COMM_EARNED_SHOULD_BE_US', $module) . ' ' . $min_ib_comm_withdraw, 1413);
                            exit;
                        }

                        if ($amount > 0) {

                            $values['net_amount'] = $amount;
                            //Process on Submit from cabinet or mobile side
                            if ($sub_operation == 'Confirm') {

                                //Process on Confirm from cabinet
                                $payment_process = '';
                                if ($values['payment_type'] == 'C2E') {
                                    $payment_process = 'Commission Withdrawal';
                                    if ($values['payment_to'] == 'Wallet') {
                                        $values['payment_to'] = $values['wallet_id'];
                                    }
                                }
                                $this->recordValues = array(
                                    'payment_operation' => $payment_operation,
                                    'amount' => $amount,
                                    'commission' => 0,
                                    'commission_value' => 0,
                                    'contactid' => $contactId,
                                    'payment_amount' => $values['net_amount'],
                                    'payment_currency' => $payment_currency,
                                    'payment_process' => $payment_process,
                                    'payment_status' => 'Pending',
                                    'payment_type' => $values['payment_type'],
                                    'payment_from' => $values['payment_from'],
                                    'payment_to' => $values['payment_to'],
                                    'request_from' => 'CustomerPortal',
                                );

                                /**
                                 * Multiple Ib commission within single day
                                 */
                                if (!$isAllow_multiple_commission_within_singleday && $values['payment_type'] == 'C2E') {
                                    $parent_contactid = $this->getActiveCustomer()->id;
                                    $ibCommssionSql = "SELECT paymentsid FROM vtiger_payments"
                                        . " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_payments.paymentsid"
                                        . " WHERE vtiger_crmentity.deleted = 0 AND vtiger_payments.contactid = ? AND vtiger_payments.payment_type = 'C2E' AND DATE_FORMAT(vtiger_crmentity.createdtime, '%Y-%m-%d') = CURDATE() AND (vtiger_payments.payment_status = 'Pending' OR vtiger_payments.payment_status = 'InProgress' OR vtiger_payments.payment_status = 'Confirmed' OR vtiger_payments.payment_status = 'Completed')";
                                    $ibCommssionResult = $adb->pquery($ibCommssionSql, array($parent_contactid));
                                    $noOfibcommission = $adb->num_rows($ibCommssionResult);
                                    if ($noOfibcommission > 0) {
                                        throw new Exception(vtranslate('IB_COMMISSION_TODAY_LIMIT_EXCEED', $module), 1413);
                                        exit;
                                    }
                                }

                                // set assigned user to default assignee
                                //$this->recordValues['assigned_user_id'] = CustomerPortal_Settings_Utils::getDefaultAssignee();
                                $this->recordValues['assigned_user_id'] = '19x' . getRecordOwnerId($this->getActiveCustomer()->id)['Users']; //contact's assignee will assign to sub module record
                                //Setting source to customer portal
                                $this->recordValues['source'] = $current_user->column_fields['source']; //'CUSTOMER PORTAL';
                                $this->recordValues = vtws_create($module, $this->recordValues, $current_user);
                                if (!empty($this->recordValues) && $this->recordValues['payment_process'] === 'Finish' && $this->recordValues['payment_status'] === 'Completed') {
                                    //respponse of payment module of id will update to tradecommission table as a reference
                                    $withdraw_reference_id = $this->recordValues['transaction_id'];
                                    $commission_withdraw_status = 1; // 0- Pending, 1-Completed
                                    //Bulk Update for each trade commission record to tradescommission table
                                    $parent_contactid = $this->getActiveCustomer()->id;

                                    //End
                                    $record = CustomerPortal_Utils::resolveRecordValues($this->recordValues);
                                    $record['message'] = CustomerPortal_Utils::setMessage($module, $this->recordValues['payment_status'], $payment_operation, $portal_language);
                                    $response->setResult(array('record' => $record));
                                }
                            } else {
                                throw new Exception(vtranslate('CAB_MSG_SUB_OPERATION_DOES_NOT_MATCH', $this->translate_module), 1413);
                                exit;
                            }
                        } else {
                            throw new Exception(vtranslate('CAB_INSUFFICIENT_BAL_FOR_IB_WITHDRAW', $module), 1413);
                            exit;
                        }
                    } else {
                        throw new Exception(vtranslate('CAB_MSG_PAYMENT_OPER_DOES_NOT_MATCH', $module), 1413);
                        exit;
                    }
                } else {
                    throw new Exception(vtranslate("LBL_PAYMENT_PROVIDER_NOT_MATCH", $module), 1413);
                    exit;
                }

                /*Queue validation*/
                if (!empty($sub_operation) && $sub_operation === 'Confirm') {
                    completePaymentInQueue($paymentData);
                }
                /*Queue validation*/

            } else {
                throw new Exception(vtranslate('CAB_MSG_VALUES_PARAM_SHOULD_NOT_BE_EMPTY', $module), 1413);
                exit;
            }
        }
        return $response;
    }

    public function getCustomFormData($form_data, $values, $module, $attachments)
    {
        $data = array();
        foreach ($form_data as $field) {
            if ($field['type'] == "file" && !empty($attachments)) {
                $field['value'] = $attachments[$field['name']];
            } else {
                $field['value'] = $values[$field['name']];
            }
            $data[] = $field;
        }
        return $data;
    }

    public function getLabelKeyFormat($form_data, $values, $module)
    {
        $is_visible_data = array('payment_from', 'payment_currency', 'payment_to', 'amount', 'commission', 'commission_amount', 'net_amount');
        for ($j = 0; $j < count($form_data); $j++) {
            array_push($is_visible_data, $form_data[$j]['name']);
        }
        $confirm_data = array();
        $i = 0;

        foreach ($values as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));
            $confirm_data[$i] = array(
                'label' => vtranslate($label, $module),
                'key' => $key,
                'value' => $value,
                'is_visible' => false,
            );
            if (in_array($confirm_data[$i]['key'], $is_visible_data)) {
                $confirm_data[$i]['is_visible'] = true;
            }

            $i++;
        }
        return $confirm_data;
    }

    public function getCurrecnySupportConvertor($provider, $values, $net_amount, $operation_type, $module = '')
    {
        $is_supports_currency_convertor = CustomerPortal_Utils::isCurrencyConversionSupport($provider->parameters['currency_conversion']);
        $providerName = $provider->getName();
        if($providerName === 'VirtualPayMobPay' && !empty($values['country']))
        {
            $provider->parameters['bank_currency'] = $provider->getCurrencyFromCountry($values['country']);
        }
        else if ($providerName === 'Help2Pay')
        {
            $provider->parameters['bank_currency'] = $values['bank_currency'];
        }
        //if ($provider->SUPPORTS_CURRENCY_CONVERTOR) {
        if ($is_supports_currency_convertor) {
            $fromCurrency = $values['payment_currency'];
            $toCurrency = $provider->parameters['bank_currency'];
            $providerType = $provider->getName();
            if (strtolower($providerType) == "worldpay" || strtolower($providerType) == "rapyd") {
                $fromCurrency = $values['payment_currency'];
                $toCurrency = $values['bank_currency'];
                if ($toCurrency == '' || $fromCurrency == $toCurrency)
                {
                    return $values;
                }
            }
            if (isset($toCurrency) && $toCurrency != '') {
                global $adb;
                $sql = "SELECT vc.conversion_rate FROM vtiger_currencyconverter AS vc INNER JOIN vtiger_crmentity "
                . "AS vce ON vce.crmid = vc.currencyconverterid WHERE "
                . "vce.deleted=0 AND vc.from_currency = ? AND vc.to_currency = ? AND `vc`.`operation_type` = ? ";
                $sqlResult = $adb->pquery($sql, array($fromCurrency,$toCurrency,$operation_type));
                $numRow = $adb->num_rows($sqlResult);
                if ($numRow > 0) {
                    $conversion_rate = $adb->query_result($sqlResult, 0, 'conversion_rate');
                    $values['conversion_rate'] = $conversion_rate;
                    $values['bank_amount'] = round($net_amount * $conversion_rate, 2);
                    $values['bank_currency'] = $toCurrency;
                } else {
                    throw new Exception(vtranslate('CAB_MSG_CONVERSION_RATE_NOT_FOUND', $module), 1414);
                    exit;
                }
            } else {
                throw new Exception(vtranslate('CAB_MSG_BASE_CURRENCY_NOT_FOUND', $module), 1414);
                exit;
            }
        }
        return $values;
    }

    public function doUploadAttachment($FILES, $current_user, $form_data, $portal_language)
    {
        if (count($FILES) > 0) {
            $FILES = Vtiger_Util_Helper::transformUploadedFiles($FILES, true);

            //Added by sandeep for alloed particular file type only 18-02-2020
            $files = [];
            foreach ($FILES as $key => $file) {
                $labelIndex = array_search($key, array_column($form_data, 'name'));
                $label = empty($labelIndex) ? $key : $form_data[$labelIndex]['label'];
                $fileObj = [];
                $fileObj['contactid'] = '12x' . $this->getActiveCustomer()->id;
                $fileObj['notes_title'] = vtranslate($label, 'Payments', $portal_language); //$request->get('filename');
                $fileObj['filelocationtype'] = 'I'; // location type is internal
                $fileObj['filestatus'] = '1'; //status always active
                $fileObj['filename'] = $file['name'];
                $fileObj['filetype'] = $file['type'];
                $fileObj['filesize'] = $file['size'];
                $fileObj['document_type'] = 'Payment';
                $fileObj['source'] = $current_user->column_fields['source']; //'CUSTOMER PORTAL';
                $fileObj['request_from'] = 'CustomerPortal';
                $fileObj['record_status'] = 'Pending';
                $fileObj['assigned_user_id'] = '19x' . getRecordOwnerId($this->getActiveCustomer()->id)['Users']; //contact's assignee will assign to sub module record
                $fileObj = vtws_create('Documents', $fileObj, $current_user);
                if (!empty($fileObj)) {
                    $files[$key] = $fileObj['id'];
                }
            }
            return $files;
        }
    }

    public function linkAttachmentWithPayment($recordId, $form_data, $values)
    {
        //Here Document will parent Id which genereated after save the document record
        $contact = new Contacts();
        $contact->save_related_module('Contacts', $this->getActiveCustomer()->id, 'Documents', array($recordId));
        foreach ($form_data as $field) {
            if ($field['type'] == 'file') {
                $parentId = $values[$field['name']];
                if (!empty($parentId) && $this->isRecordAccessible($parentId)) {
                    $focus = CRMEntity::getInstance('Documents');
                    $parentIdComponents = explode('x', $parentId);
                    $recordIdComponents = explode('x', $recordId);
                    $focus->insertintonotesrel($recordIdComponents[1], $parentIdComponents[1]);
                }
            }
        }
    }

}
