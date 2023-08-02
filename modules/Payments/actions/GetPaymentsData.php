<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Payments_GetPaymentsData_Action extends Vtiger_BasicAjax_Action {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('OperationPaymentGatewayToAccount');
        $this->exposeMethod('OperationPaymentGatewayToEWallet');
        $this->exposeMethod('OperationAccountToPaymentGateway');
        $this->exposeMethod('OperationEWalletToPaymentGateway');
        $this->exposeMethod('OperationEWalletToEWallet');
        $this->exposeMethod('OperationAccountToAccount');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    public function OperationPaymentGatewayToAccount(Vtiger_Request $request) {
        global $adb;

        $module = $request->get('module');
        $action = $request->get('action');
        $mode = $request->get('mode');
        $payment_type = $request->get('payment_type');

        $P2A_Details = Payments_Record_Model::getPaymentGatewayToAccountDetails($request);

        $from_value_html = '';
        if (!empty($P2A_Details[0])) {
            foreach ($P2A_Details[0] as $from_value) {
                $from_value_html .= '<option value="' . $from_value . '" class="' . $payment_type . '">' . $from_value . '</option>';
            }
        }

        $to_value_html = '';
        if (!empty($P2A_Details[1])) {
            foreach ($P2A_Details[1] as $to_value) {
                $to_value_html .= '<option value="' . $to_value . '" class="' . $payment_type . '">' . $to_value . '</option>';
            }
        }

        $responseResult = array('from_value' => $from_value_html, 'to_value' => $to_value_html);
//        echo "<pre>";
//        print_r($responseResult);
//        exit;
        $response = new Vtiger_Response();
        $response->setResult($responseResult);
        $response->emit();
    }

    public function OperationPaymentGatewayToEWallet(Vtiger_Request $request) {
        global $adb;

        $module = $request->get('module');
        $action = $request->get('action');
        $mode = $request->get('mode');
        $payment_type = $request->get('payment_type');

        $P2E_Details = Payments_Record_Model::getPaymentGatewayToEWalletDetails($request);

        $from_value_html = '';
        if (!empty($P2E_Details[0])) {
            foreach ($P2E_Details[0] as $from_value) {
                $from_value_html .= '<option value="' . $from_value . '" class="' . $payment_type . '">' . $from_value . '</option>';
            }
        }

        $to_value_html = '';
        if (!empty($P2E_Details[1])) {
            foreach ($P2E_Details[1] as $to_value) {
                $to_value_html .= '<option value="' . $to_value . '" class="' . $payment_type . '">' . $to_value . '</option>';
            }
        }

        $responseResult = array('from_value' => $from_value_html, 'to_value' => $to_value_html);
        $response = new Vtiger_Response();
        $response->setResult($responseResult);
        $response->emit();
    }

    public function OperationAccountToPaymentGateway(Vtiger_Request $request) {
        global $adb;

        $module = $request->get('module');
        $action = $request->get('action');
        $mode = $request->get('mode');
        $payment_type = $request->get('payment_type');

        $A2P_Details = Payments_Record_Model::getAccountToPaymentGatewayDetails($request);

        $from_value_html = '';
        if (!empty($A2P_Details[0])) {
            foreach ($A2P_Details[0] as $from_value) {
                $from_value_html .= '<option value="' . $from_value . '" class="' . $payment_type . '">' . $from_value . '</option>';
            }
        }

        $to_value_html = '';
        if (!empty($A2P_Details[1])) {
            foreach ($A2P_Details[1] as $to_value) {
                $to_value_html .= '<option value="' . $to_value . '" class="' . $payment_type . '">' . $to_value . '</option>';
            }
        }

        $responseResult = array('from_value' => $from_value_html, 'to_value' => $to_value_html);
//        echo "<pre>";
//        print_r($responseResult);
//        exit;
        $response = new Vtiger_Response();
        $response->setResult($responseResult);
        $response->emit();
    }

    public function OperationEWalletToPaymentGateway(Vtiger_Request $request) {
        global $adb;

        $module = $request->get('module');
        $action = $request->get('action');
        $mode = $request->get('mode');
        $payment_type = $request->get('payment_type');

        $E2P_Details = Payments_Record_Model::getEWalletToPaymentGatewayDetails($request);

        $from_value_html = '';
        if (!empty($E2P_Details[0])) {
            foreach ($E2P_Details[0] as $from_value) {
                $from_value_html .= '<option value="' . $from_value . '" class="' . $payment_type . '">' . $from_value . '</option>';
            }
        }

        $to_value_html = '';
        if (!empty($E2P_Details[1])) {
            foreach ($E2P_Details[1] as $to_value) {
                $to_value_html .= '<option value="' . $to_value . '" class="' . $payment_type . '">' . $to_value . '</option>';
            }
        }

        $responseResult = array('from_value' => $from_value_html, 'to_value' => $to_value_html);
        $response = new Vtiger_Response();
        $response->setResult($responseResult);
        $response->emit();
    }

    public function OperationEWalletToEWallet(Vtiger_Request $request) {
        global $adb;

        $module = $request->get('module');
        $action = $request->get('action');
        $mode = $request->get('mode');
        $payment_type = $request->get('payment_type');

        $E2E_Details = Payments_Record_Model::getEWalletToEWalletDetails($request);

        $from_value_html = '';
        if (!empty($E2E_Details[0])) {
            foreach ($E2E_Details[0] as $from_value) {
                $from_value_html .= '<option value="' . $from_value . '" class="' . $payment_type . '">' . $from_value . '</option>';
            }
        }

        $to_value_html = '';

        $responseResult = array('from_value' => $from_value_html, 'to_value' => $to_value_html);
        $response = new Vtiger_Response();
        $response->setResult($responseResult);
        $response->emit();
    }

    public function OperationAccountToAccount(Vtiger_Request $request) {
        global $adb;

        $module = $request->get('module');
        $action = $request->get('action');
        $mode = $request->get('mode');
        $payment_type = $request->get('payment_type');

        $A2A_Details = Payments_Record_Model::getAccountToAccountDetails($request);

        $from_value_html = '';
        if (!empty($A2A_Details[0])) {
            foreach ($A2A_Details[0] as $from_value) {
                $from_value_html .= '<option value="' . $from_value . '" class="' . $payment_type . '">' . $from_value . '</option>';
            }
        }

        $to_value_html = '';
        if (!empty($A2A_Details[1])) {
            $to_value_html .= '<option value="">' . vtranslate('LBL_SELECT_OPTION', 'Vtiger') . '</option>';
            foreach ($A2A_Details[1] as $to_value) {
                $to_value_html .= '<option value="' . $to_value . '" class="' . $payment_type . '">' . $to_value . '</option>';
            }
        }

        $responseResult = array('from_value' => $from_value_html, 'to_value' => $to_value_html);
//        echo "<pre>";
//        print_r($responseResult);
//        exit;
        $response = new Vtiger_Response();
        $response->setResult($responseResult);
        $response->emit();
    }

}
