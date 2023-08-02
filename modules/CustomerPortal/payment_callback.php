<?php

// include_once './include/payment_helper.php';
include_once 'include.inc';
session_start();
if (isset($_REQUEST) && !empty($_REQUEST) && isset($_SESSION['confirm_data_response'])) {
    $paymentHelper = new PaymentHelper($_SESSION['request']);
    $paymentHelper->isValid();
    if ($_REQUEST['status'] == 'Failed') {
        $paymentHelper->postProcess(array("sub_operation" => $_REQUEST['status'], "payment_response" => $_REQUEST));
    } else {
        if(!$_REQUEST['fp_batchnumber'] && $_REQUEST['pm'] == 'FasaPay') {
            $paymentHelper->postProcess(array("sub_operation" => "Failed", "payment_response" => $_REQUEST));
        } else if (isset($_REQUEST['pm1']) && $_REQUEST['pm1'] == 'PayTechno') {
            if(isset($_REQUEST['redirection_type']) && $_REQUEST['redirection_type'] == 'mob' && $_SESSION['confirm_data_response']['order_id'] == $_REQUEST['OrderNumber']) {
                $_REQUEST['orid'] = $_REQUEST['OrderNumber'];
                if (isset($_REQUEST['Status']) && ($_REQUEST['Status'] == 'Authorized' || $_REQUEST['Status'] == 'Captured')) {
                    $paymentHelper->postProcess(array("sub_operation" => 'Success', "payment_response" => $_REQUEST));                
                } else if ($_REQUEST['Status'] == 'Rejected' || $_REQUEST['Status'] == 'Canceled') {
                    $paymentHelper->postProcess(array("sub_operation" => 'Failed', "payment_response" => $_REQUEST));
                }
            } 
        } else if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'PerfectMoney') {
            if ($_REQUEST['PAYMENT_BATCH_NUM'] == 0 || empty($_REQUEST['PAYMENT_BATCH_NUM'])) {
                $paymentHelper->postProcess(array("sub_operation" => 'Failed', "payment_response" => $_REQUEST));
            } else {
                $paymentHelper->postProcess(array("sub_operation" => 'Success', "payment_response" => $_REQUEST));
            }
        } else if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'VaultsPay') {
            $paymentHelper->postProcess(array("sub_operation" => 'Pending', "payment_response" => $_REQUEST));
        } else if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'AwePay') {
            $paymentHelper->postProcess(array("sub_operation" => 'Pending', "payment_response" => $_REQUEST));
        } else if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'Rapyd') {
            if (isset($_REQUEST['restatus']) && $_REQUEST['restatus'] == 'success') {
                $paymentHelper->postProcess(array("sub_operation" => "Success", "payment_response" => $_REQUEST));
            } else {
                $paymentHelper->postProcess(array("sub_operation" => 'Failed', "payment_response" => $_REQUEST));
            }
        } else if (isset($_REQUEST['pm']) && $_REQUEST['pm'] == 'VirtualPay') {
            $paymentHelper->postProcess(array("sub_operation" => 'Pending', "payment_response" => $_REQUEST));
        } else {
            $paymentHelper->postProcess(array("sub_operation" => "Success", "payment_response" => $_REQUEST));
        }       
    }
} else {
    exit("Parameters Not Found!");
}
?>