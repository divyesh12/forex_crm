<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */


/* @Add By Divyesh
 * @Date:- 15-11-2019
 * @Comment:-  Registor Payment Getways functions
 */

interface IPaymentGetways {

//  const MSG_STATUS_DISPATCHED = 'Dispatched';
    const PROVIDER_TYPE = 2; // 2 for Payment Getways type
    const TRANSFER_DETAILS_BLOCK = 1;
    const OTHER_DETAILS_BLOCK = 2;
    const DEFAULT_REQUIRED_PARAMETERS = array(
        array('name' => 'auto_confirm', 'label' => 'Payment Auto Confirm', 'type' => 'picklist',
            'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No')),
        array('name' => 'deposit_supported', 'label' => 'Deposit Supported', 'type' => 'picklist',
            'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No')),
        array('name' => 'withdrawal_supported', 'label' => 'Withdrawal Supported', 'type' => 'picklist',
            'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No')),
        array('name' => 'commission', 'label' => 'Commission(%)', 'type' => 'text', 'block' => self::TRANSFER_DETAILS_BLOCK),
        array('name' => 'description', 'label' => 'Description', 'type' => 'text'),
        array('name' => 'deposit_processing_time', 'label' => 'Deposit Processing Time', 'type' => 'text', 'block' => self::TRANSFER_DETAILS_BLOCK, 'display_type' => 'Deposit'),
        array('name' => 'withdrawal_processing_time', 'label' => 'Withdrawal Processing Time', 'type' => 'text', 'block' => self::TRANSFER_DETAILS_BLOCK, 'display_type' => 'Withdrawal'),
        array('name' => 'currencies', 'label' => 'Currencies', 'type' => 'text', 'block' => self::TRANSFER_DETAILS_BLOCK),
        array('name' => 'deposit_min', 'label' => 'Minimum Deposit', 'type' => 'number', 'block' => self::TRANSFER_DETAILS_BLOCK, 'display_type' => 'Deposit'),
        array('name' => 'deposit_max', 'label' => 'Maximum Deposit', 'type' => 'number', 'block' => self::TRANSFER_DETAILS_BLOCK, 'display_type' => 'Deposit'),
        array('name' => 'withdrawal_min', 'label' => 'Minimum Withdrawal', 'type' => 'number', 'block' => self::TRANSFER_DETAILS_BLOCK, 'display_type' => 'Withdrawal'),
        array('name' => 'withdrawal_max', 'label' => 'Maximum Withdrawal', 'type' => 'number', 'block' => self::TRANSFER_DETAILS_BLOCK, 'display_type' => 'Withdrawal'),
        array('name' => 'allowed_decimal', 'label' => 'Decimal Allowed', 'type' => 'number'),
        array('name' => 'deposit_term_conditions', 'label' => 'Deposit Term & Conditions', 'type' => 'textarea', 'display_type' => 'Deposit'),
        array('name' => 'withdrawal_term_conditions', 'label' => 'Withdrawal Term & Conditions', 'type' => 'textarea', 'display_type' => 'Withdrawal'),
        array('name' => 'term_conditions', 'label' => 'Term & Conditions', 'type' => 'textarea', 'block' => self::OTHER_DETAILS_BLOCK),
        array('name' => 'sequence_number', 'label' => 'Sequence Number', 'type' => 'number'),
        array('name' => 'payment_logo_path', 'label' => 'Payment Gateway Logo Path', 'type' => 'text', 'mandatory' => true),
    );

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName();

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getRequiredParams();

    /**
     * Function to get deposit parameters
     * @return <array> required parameters list
     */
    public function getDepositFormParams();

    /**
     * Function to get withdrawal parameters
     * @return <array> required parameters list
     */
    public function getWithdrawFormParams();

    /**
     * Function to set non-auth parameter.
     * @param <String> $key
     * @param <String> $value
     */
    public function setParameter($key, $value);

    /**
     * Function to get parameter value
     * @param <String> $key
     * @param <String> $defaultValue
     * @return <String> value/$default value
     */
    public function getParameter($key, $defaultValue = false);

    /**
     * Function to prepare parameters
     * @return <Array> parameters
     */
    public function prepareParameters();

    public function paymentProcess($request, $portal_language);

    public function getVerifyTransferDetails($request, $FILES, $portal_language);

    public function paymentResponseVerification($status, $payment_response, $order_id, $portal_language);
}

?>
