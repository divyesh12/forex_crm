<?php
include_once 'include.inc';
session_start();
global $resendOtpDuration;
if (isset($_POST) && !empty($_POST) && isset($_SESSION['request'])) {
    $paymentHelper = new PaymentHelper($_SESSION['request']);
    $paymentHelper->isValid();

    $paymentHelper->postProcess($_POST);
} else {
    if(isset($_REQUEST['resend_otp']))
    {
        $paymentHelper = new PaymentHelper($_SESSION['request']);
        $paymentHelper->resendOtpProcess();
    }
    
    $paymentHelper = new PaymentHelper($_REQUEST);
    $paymentHelper->isValid();
}
$label_arr = $paymentHelper->fetchLanguageLabels();
$crm_config = $paymentHelper->fetchConfiguration();
$is_wallet_enabled = false;
foreach ($crm_config['config'] as $key => $v) {
    if ($v['module'] == 'Ewallet' && $v['fieldname'] == 'ewallet_module_enabled') {
        $is_wallet_enabled = $v['fieldvalue'];
    }
}
$formSchema = $paymentHelper->describePaymentGateway();

$withdrawal_allow_from = $formSchema['paymentdescribe']['withdrawal_allow_from'];
$checked = '';
if ($withdrawal_allow_from == 'Wallet') {
    $checked = 'checked';
}
$deposit_allow_from = $formSchema['paymentdescribe']['deposit_allow_from'];
$checkedDep = '';
if ($deposit_allow_from == 'Wallet') {
    $checkedDep = 'checked';
}
$token = urldecode($_REQUEST['token']);
$payment_operation = urldecode($_REQUEST['payment_operation']);
$payment_gateway = urldecode($_REQUEST['payment_gateway']);
$theme = urldecode($_REQUEST['theme']);
$picklist_arr = array();
if ($payment_gateway == 'BankOffline') {
    echo "Invalid Payment Method.";exit;
}
?>


<html>
    <head>
        <meta charset="utf-8">
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
        <meta name="viewport" content= "width=device-width, user-scalable=no">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="payment/css/bootstrap.min.css">
        <link rel="stylesheet" href="payment/css/custom.css">
        <link rel="stylesheet" href="payment/css/tailwind.min.css">
        <script>
            var label_arr = <?= json_encode($label_arr); ?>
        </script>
        <style type="text/css">
            .btn_disabled {
                display: none !important;
            }
            label.error{
                color: red;
            }
        </style>
    </head>
    <body>
        <div class="cs-page-loading active">
            <div class="cs-page-loading-inner">
                <div class="cs-page-spinner"></div><span class="set_donnot_refresh_message">Loading...</span>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-cus">
                    <div class="wrapper">
                        <ul class="payment-tabs nav nav-tabs">
                            <li class="active tab-link transfer-tab"><a href="#"><?= $label_arr['Payments']['CAB_LBL_TRANSFER_DETAILS'] ?></a></li>
                            <li class="tab-link confirm-tab"><a href="#"><?= $label_arr['Payments']['CAB_LBL_CONFIRM_TRANSFER'] ?></a></li>
                        </ul>
                        <div class="well-sm"></div>
                        <div class="content-block">
                            <div class="transfer-detail-block" id="setpaymentdescribe">
                                <form method="post" enctype="multipart/form-data" name="submit-form" id="submit-form" class="submit-form">
                                    <input type="hidden" value="Submit" name="sub_operation" />

                                    <div class="detail-block">
                                        <div class="title-block">
                                            <img class="payment-logo" alt="Payment Gateway" id="payment_gateway" src="<?= $formSchema['paymentdescribe']['payment_logo'] ?>" />
                                            <div class="title pull-right" id="CAB_LBL_PAYMENT_METHOD">
                                                <?= $label_arr['Payments']['CAB_LBL_PAYMENT_METHOD'] ?><span class="text-left"><?= $formSchema['paymentdescribe']['payment_name'] ?> </span>
                                            </div><!-- Set here payment method label and name both -->
                                            <div class="well-sm"></div>
                                        </div>
                                        <div class="well-sm"></div>
                                        <div class="form-group">
                                            <label class="CAB_LBL_PAYMENT_PROCESS"><?= $label_arr['Payments']['CAB_LBL_PAYMENT_PROCESS'] ?> <font color="red">*</font></label>
                                            <div class="radio-box">

                                                <?php if ($payment_operation == 'Deposit') { ?>
                                                    <?php if (empty($deposit_allow_from) || $deposit_allow_from == 'Account' || $deposit_allow_from == 'WalletAccount') { ?>
                                                        <div class="radio-block">
                                                            <?php $payment_type_account = 'P2A'; ?>
                                                        
                                                            <input type="radio" id="p-account" name="payment_type" value="<?= $payment_type_account ?>" checked>
                                                            <label for="p-account" class="CAB_LBL_ACCOUNT"> <?= $label_arr['Payments']['CAB_LBL_ACCOUNT'] ?></label> <!-- Account -->
                                                            <div class="check"></div>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if ($is_wallet_enabled == 'true' && $payment_gateway != 'Wallet') { ?>
                                                        <?php if (empty($deposit_allow_from) || $deposit_allow_from == 'Wallet' || $deposit_allow_from == 'WalletAccount') { ?>
                                                            <div class="radio-block">
                                                                <?php $payment_type_wallet = 'P2E'; ?>

                                                                <input type="radio" id="p-wallet" name="payment_type" value="<?= $payment_type_wallet ?>" <?= $checkedDep ?>>
                                                                <label for="p-wallet" class="CAB_LBL_WALLET"><?= $label_arr['Payments']['CAB_LBL_WALLET'] ?></label> <!-- Wallet -->
                                                                <div class="check"><div class="inside"></div></div>
                                                            </div>
                                                        <?php } ?>
                                                    <?php } ?>                                                    
                                                <?php } ?>

                                                <?php if ($payment_operation == 'Withdrawal') { ?>
                                                    <?php if (empty($withdrawal_allow_from) || $withdrawal_allow_from == 'Account' || $withdrawal_allow_from == 'WalletAccount') { ?>
                                                        <div class="radio-block">
                                                            <?php $payment_type_account = 'A2P';?>
                                                            
                                                            <input type="radio" id="p-account" name="payment_type" value="<?= $payment_type_account ?>" checked>
                                                            <label for="p-account" class="CAB_LBL_ACCOUNT"> <?= $label_arr['Payments']['CAB_LBL_ACCOUNT'] ?></label> <!-- Account -->
                                                            <div class="check"></div>
                                                        </div>    
                                                    <?php } ?>
                                                    <?php if ($is_wallet_enabled == 'true' && $payment_gateway != 'Wallet') { ?>
                                                        <?php if (empty($withdrawal_allow_from) || $withdrawal_allow_from == 'Wallet' || $withdrawal_allow_from == 'WalletAccount') { ?>
                                                            <div class="radio-block">
                                                                <?php $payment_type_wallet = 'E2P'; ?>

                                                                <input type="radio" id="p-wallet" name="payment_type" value="<?= $payment_type_wallet ?>" <?= $checked ?>>
                                                                <label for="p-wallet" class="CAB_LBL_WALLET"><?= $label_arr['Payments']['CAB_LBL_WALLET'] ?></label> <!-- Wallet -->
                                                                <div class="check"><div class="inside"></div></div>
                                                            </div>
                                                        <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>


                                                <?php /* if (empty($withdrawal_allow_from) || $withdrawal_allow_from == 'Account' || $withdrawal_allow_from == 'WalletAccount') { ?>
                                                    <div class="radio-block">
                                                        <?php
                                                        $payment_type_account = '';
                                                        if ($payment_operation == 'Deposit') {
                                                            $payment_type_account = 'P2A';
                                                        }

                                                        if ($payment_operation == 'Withdrawal') {
                                                            $payment_type_account = 'A2P';
                                                        }
                                                        ?>
                                                    
                                                        <input type="radio" id="p-account" name="payment_type" value="<?= $payment_type_account ?>" checked>
                                                        <label for="p-account" class="CAB_LBL_ACCOUNT"> <?= $label_arr['Payments']['CAB_LBL_ACCOUNT'] ?></label> <!-- Account -->
                                                        <div class="check"></div>
                                                    </div>
                                                <?php } ?>
                                                <?php if ($is_wallet_enabled == 'true' && $payment_gateway != 'Wallet') { ?>
                                                    <?php if (empty($withdrawal_allow_from) || $withdrawal_allow_from == 'Wallet' || $withdrawal_allow_from == 'WalletAccount') { ?>
                                                        <div class="radio-block">
                                                            <?php
                                                            $payment_type_wallet = '';
                                                            if ($payment_operation == 'Deposit') {
                                                                $payment_type_wallet = 'P2E';
                                                            }

                                                            if ($payment_operation == 'Withdrawal') {
                                                                $payment_type_wallet = 'E2P';
                                                            }
                                                            ?>
                                                            <input type="radio" id="p-wallet" name="payment_type" value="<?= $payment_type_wallet ?>" <?= $checked ?>>
                                                            <label for="p-wallet" class="CAB_LBL_WALLET"><?= $label_arr['Payments']['CAB_LBL_WALLET'] ?></label> <!-- Wallet -->
                                                            <div class="check"><div class="inside"></div></div>
                                                        </div>
                                                    <?php } ?>
                                                <?php }*/ ?>




                                                
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="CAB_LBL_CURRENCY"><?= $label_arr['Payments']['CAB_LBL_CURRENCY'] ?> <font color="red">*</font></label><!-- Currency -->
                                            <div class="radio-box">
                                                <div id="set_currency">
                                                    <?php
                                                    $currencies = explode(",", $formSchema['paymentdescribe']['currencies']);
                                                    foreach ($currencies as $currency) {
                                                        ?>
                                                        <div class="radio-block"><input value="<?= $currency ?>" type="radio" id="p-<?= $currency ?>" name="payment_currency" <?php
                                                            if ($currencies[0] == $currency) {
                                                                echo 'checked';
                                                            }
                                                            ?> ><label for="p-<?= $currency ?>"> <?= $currency ?></label><div class="check"></div></div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="set_payment_to">
                                            <div class="form-group"><label class="set_label"><?= $label_arr['Payments']['CAB_LBL_ACCOUNT'] ?></label>
                                                <select name="payment_to" id="payment_to" class="form-control" required=true >
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="CAB_LBL_AMOUNT"><?= $label_arr['Payments']['CAB_LBL_AMOUNT'] ?><font color="red">*</font></label> <!-- Amount -->
                                                <input type="number" step="0.01" name="amount" id="amount" class="form-control" placeholder="<?= $label_arr['Payments']['CAB_LBL_AMOUNT'] ?>" required="true">
                                            </div>
                                            <div id="form_data">
                                                <?php
                                                foreach ($formSchema['paymentdescribe']['form_data'] as $item) {
                                                    $req_field_design = "";
                                                    $placeholder_txt = "";
                                                    $req_field = false;
                                                    $type = $item['type'];
                                                    $name = $item['name'];
                                                    if ($item['required']) {
                                                        $req_field = true;
                                                        $req_field_design = '<font color="red">*</font>';
                                                    } else {
                                                        $req_field = false;
                                                        $req_field_design = '';
                                                    }
                                                    if (isset($item['placeholder'])) {
                                                        $placeholder_txt = $label_arr['Payments'][$item['placeholder']];
                                                    } else {
                                                        $placeholder_txt = $label_arr['Payments'][$item['label']];
                                                    }
                                                    if ($type == "hidden") {
                                                        echo '<input class="form-control" type="' . $type . '" name="' . $name . '" id="' . $name . '">';
                                                    } else if ($type == 'email' || $type == 'text' || $type == 'number' || $type == 'file') {
                                                        $input = '<div class="form-group" id="form_data"><label>' . $label_arr['Payments'][$item['label']] . $req_field_design . '</label>'
                                                        . '<input class="form-control" type="' . $type . '" name="' . $name
                                                        . '" id="' . $name 
                                                        . '" class="form-control" placeholder="' . $placeholder_txt . '"';
                                                        // if ($type == 'file') {
                                                            // $input .= ' accept="image/*" capture ';
                                                        // }
                                                        if ($req_field) {
                                                            $input .= 'required="' . $req_field . '">';
                                                        } else {
                                                            $input .= '>';
                                                        }
                                                        if ($type == 'file') {
                                                            $input .= '<input type="hidden" name="file_size" id="filesize" value="' . $item['size'] . '">';
                                                            $input .= '<small>' . $label_arr['Payments'][$item['note']] . '<small>';
                                                            $input .= '<div class="errorTxt"></div>';
                                                        }
                                                        $input .= '</div>';
                                                        echo $input;
                                                    } else if ($type == 'textarea') {
                                                        echo '<div class="form-group" id="form_data"><label>' . $label_arr['Payments'][$item['label']] . $req_field_design . '</label>'
                                                        . '<textarea class="form-control" name="' . $name . '" id="' . $name . '" class="form-control" placeholder="' . $placeholder_txt . '" required="' . $req_field . '"></textarea></div>';
                                                    } else if ($type == 'dropdown_depended') {
                                                        $picklist_arr[$item['name']] = $item['picklist'];
                                                        $select = '<div class="form-group" id="form_data"><label>' . $label_arr['Payments'][$item['label']] . $req_field_design . '</label>'
                                                                . '<select class="form-control" name="' . $name . '" id="' . $name . '" class="form-control" required="' . $req_field . '">';
                                                        if (!empty($item['picklist'])) {
                                                            foreach ($item['picklist'] as $key => $value) {
                                                                $select .= '<option value="' . $value['value'] . '">' . $value['label'] . '</option>';
                                                            }
                                                        }
                                                        echo $select .= '</select></div>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                            
                                            <?php if (isset($formSchema['paymentdescribe']['is_supports_currency_convertor']) && $formSchema['paymentdescribe']['is_supports_currency_convertor']->scalar == 1 && isset($formSchema['paymentdescribe']['currency_convertor_rate']) && !empty($formSchema['paymentdescribe']['currency_convertor_rate'])) { ?>
                                                <div class="well-sm"></div>
                                                <div class="detail-block">
                                                    <div class="panel-group" id="currencyconverter">
                                                        <div class="panel panel-default">
                                                            <div class="panel-heading">
                                                                <h4 class="panel-title">
                                                                    <a data-toggle="collapse" data-parent="#currencyconverter" href="#collapsefour" class="CAB_LBL_CONVERSION_TOOL"> <?= $label_arr['Payments']['CAB_LBL_CONVERSION_TOOL'] ?> <img src="payment/arrow.png" class="pull-right"> </a>
                                                                </h4>
                                                            </div>
                                                            <div id="collapsefour" class="panel-collapse collapse">
                                                                <br><br>
                                                                <div class="panel-body">
                                                                    <div class="row">
                                                                        <div class="col-md-6 col-xs-6">
                                                                            <h4 class="panel-title"><?= $label_arr['Payments']['CAB_LBL_CURRENCY'] ?></h4>
                                                                        </div>
                                                                        <div class="col-md-6 col-xs-6">
                                                                            <h4 class="panel-title"><?= $label_arr['Payments']['CAB_LBL_RATE'] ?></h4>
                                                                        </div>
                                                                    </div>
                                                                    <div class="well-sm"></div>
                                                                    <?php foreach ($formSchema['paymentdescribe']['currency_convertor_rate'] as $key => $item) { ?>
                                                                        <div class="row">
                                                                            <div class="col-md-6 col-xs-6">
                                                                                <h6><?= $key ?></h6>
                                                                            </div>
                                                                            <div class="col-md-6 col-xs-6">
                                                                                <h6><?= $item ?></h6>
                                                                            </div>
                                                                        </div>
                                                                    <?php } ?>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <button type="button" data-toggle="modal" data-target="#modelCurrCon" class="btn btn-info btn-curr-conv"><?= $label_arr['Payments']['CAB_LBL_CURRENCY_CONVERTER'] ?></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <div class="well-sm"></div>
                                            <div class="detail-block">
                                                <div class="panel-group" id="termscondition">
                                                    <div class="panel panel-default">
                                                        <div class="panel-heading">
                                                            <h4 class="panel-title">
                                                                <a data-toggle="collapse" data-parent="#termscondition" href="#collapsetwo" class="CAB_LBL_OTHER_DETAILS"> <?= $label_arr['Payments']['CAB_LBL_OTHER_DETAILS'] ?> <img src="payment/arrow.png" class="pull-right"> </a> <!-- Other Details -->
                                                            </h4>
                                                        </div>
                                                        <div id="collapsetwo" class="panel-collapse collapse">
                                                            <br><br>
                                                            <!-- <div id="other_details"></div>-->
                                                            <?php
                                                            foreach ($formSchema['paymentdescribe']['other_details'] as $item) {
                                                                $label_value = isset($label_arr['Payments'][$item['label']]) ? $label_arr['Payments'][$item['label']] : $item['label'];
                                                                ?>
                                                                <div class="panel-heading">
                                                                    <h4 class="panel-title"><?= $label_value ?></h4>
                                                                </div>
                                                                <div class="panel-body">
                                                                    <?php if ($item['key'] == 'file') { ?>
                                                                        <div class="roatete-off">
                                                                            <img src="<?= $item['value'] ?>" width="70%">
                                                                        </div>
                                                                        <div class="well-sm"></div>
                                                                        <div class="well-sm"></div>
                                                                    <?php } else { ?>
                                                                        <?php if ($item['key'] == 'term_conditions') { ?>
                                                                            <pre class="terms-text terms-wid"><?= $item['value'] ?></pre>
                                                                        <?php } else { ?>
                                                                            <pre class="terms-text"><?= $item['value'] ?></pre>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                </div>
                                                                <?php
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="well-sm"></div>
                                            <div class="detail-block">
                                                <div class="panel-group" id="transferdetail">
                                                    <div class="panel panel-default">
                                                        <div class="panel-heading">
                                                            <h4 class="panel-title">
                                                                <a data-toggle="collapse" data-parent="#transferdetail" href="#collapseOne" class="CAB_LBL_TRANSFER_DETAILS_BLOCK">

                                                                    <?= $label_arr['Payments']['CAB_LBL_TRANSFER_DETAILS'] ?>  <img src="payment/arrow.png" class="pull-right">
                                                                </a> <!-- Transfer Details -->
                                                            </h4>
                                                        </div>
                                                        <div id="collapseOne" class="panel-collapse collapse">
                                                            <div class="panel-body">
                                                                <div id="transfer_details">
                                                                    <?php
                                                                    foreach ($formSchema['paymentdescribe']['transfer_details'] as $item) {
                                                                        $label_value = isset($label_arr['Payments'][$item['label']]) ? $label_arr['Payments'][$item['label']] : $item['label'];
                                                                        $display_type = $item['display_type'];
                                                                        if (($display_type == 'Deposit' || $display_type == '' || $display_type == null) && $payment_operation == 'Deposit') {
                                                                            ?>
                                                                            <div class="detail-box"><span><?= $label_value ?></span><span class="pull-right"><?= $item['value'] ?></span></div>
                                                                            <?php
                                                                        } else if (($display_type == 'Withdrawal' || $display_type == '' || $display_type == null) && $payment_operation == 'Withdrawal') {
                                                                            ?>
                                                                            <div class="detail-box"><span><?= $label_value ?></span><span class="pull-right"><?= $item['value'] ?></span></div>
                                                                            <?php
                                                                        }
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="well-sm"></div>
                                            <div class="well-sm"></div>

                                            <div class="panel panel-danger hide error_message"></div>

                                            <div class="form-group">
                                                <img src="payment/loader.gif" class="loading" style="display:none"/>
                                                <button type="submit" name="submit" id="suboperation_submit" class="btn submit-btn">Submit</button>
                                            </div>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="confirm-transfer-block">
                            <form method="POST" name="confirm-form" id="confirm-form" class="confirm-form">
                                <div class="detail-block">
                                    <h4><?= isset($label_arr['Payments']['CAB_LBL_CONFIRM_TRANSFER_DETAILS']) ? $label_arr['Payments']['CAB_LBL_CONFIRM_TRANSFER_DETAILS'] : 'CAB_LBL_CONFIRM_TRANSFER_DETAILS' ?></h4>
                                    <div class="well-sm"></div>
                                    <div class="confirm-details">
                                        <?php if ($payment_operation == 'Deposit') { ?>
                                            <div class="confirm-box">
                                                <label><?= isset($label_arr['Payments']['CAB_LBL_PAYMENT_METHOD']) ? $label_arr['Payments']['CAB_LBL_PAYMENT_METHOD'] : 'CAB_LBL_PAYMENT_METHOD' ?></label>
                                                <p id="confirm_payment_from"></p>
                                            </div>
                                            <div class="confirm-box">
                                                <label><?= isset($label_arr['Payments']['CAB_LBL_CURRENCY']) ? $label_arr['Payments']['CAB_LBL_CURRENCY'] : 'CAB_LBL_CURRENCY' ?></label>
                                                <p id="confirm_payment_currency"></p>
                                            </div>
                                            <div class="confirm-box">
                                                <label><?= isset($label_arr['Payments']['CAB_LBL_ACCOUNT']) ? $label_arr['Payments']['CAB_LBL_ACCOUNT'] : 'CAB_LBL_ACCOUNT' ?></label>
                                                <p id="confirm_payment_to"></p>
                                            </div>
                                            <div class="confirm-box">
                                                <label><?= isset($label_arr['Payments']['CAB_LBL_AMOUNT']) ? $label_arr['Payments']['CAB_LBL_AMOUNT'] : 'CAB_LBL_AMOUNT' ?></label>
                                                <p id="confirm_payment_amount"></p>
                                            </div>
                                            <?php
                                        }
                                        if ($payment_operation == 'Withdrawal') {
                                            ?>
                                            <div class="confirm-box">
                                                <label><?= isset($label_arr['Payments']['CAB_LBL_FROM_ACCOUNT']) ? $label_arr['Payments']['CAB_LBL_FROM_ACCOUNT'] : 'CAB_LBL_FROM_ACCOUNT' ?></label>
                                                <p id="confirm_payment_from"></p>
                                            </div>

                                            <div class="confirm-box">
                                                <label><?= isset($label_arr['Payments']['CAB_LBL_TO_ACCOUNT']) ? $label_arr['Payments']['CAB_LBL_TO_ACCOUNT'] : 'CAB_LBL_TO_ACCOUNT' ?></label>
                                                <p id="confirm_payment_to"></p>
                                            </div>
                                            <div class="confirm-box">
                                                <label><?= isset($label_arr['Payments']['CAB_LBL_CURRENCY']) ? $label_arr['Payments']['CAB_LBL_CURRENCY'] : 'CAB_LBL_CURRENCY' ?></label>
                                                <p id="confirm_payment_currency"></p>
                                            </div>
                                            <div class="confirm-box">
                                                <label><?= isset($label_arr['Payments']['CAB_LBL_AMOUNT']) ? $label_arr['Payments']['CAB_LBL_AMOUNT'] : 'CAB_LBL_AMOUNT' ?></label>
                                                <p id="confirm_payment_amount"></p>
                                            </div>



                                            <?php
                                        }
                                        ?>
                                        <div class="confirm-box" id="custom_data">
                                            <!--                                            <label>Email</label>
                                                                                        <p></p>-->

                                        </div>

                                        <div class="confirm-box">
                                            <label><?= isset($label_arr['Payments']['CAB_LBL_COMMISSION_PERCENTAGE']) ? $label_arr['Payments']['CAB_LBL_COMMISSION_PERCENTAGE'] : 'CAB_LBL_COMMISSION_PERCENTAGE' ?></label>
                                            <p id="confirm_commission"></p>
                                        </div>
                                        <div class="confirm-box">
                                            <label><?= isset($label_arr['Payments']['CAB_LBL_COMM_AMOUNT']) ? $label_arr['Payments']['CAB_LBL_COMM_AMOUNT'] : 'CAB_LBL_COMM_AMOUNT' ?></label>
                                            <p id="commission_amount"></p>
                                        </div>
                                        <div class="confirm-box">
                                            <label><?= isset($label_arr['Payments']['CAB_LBL_NET_AMOUNT']) ? $label_arr['Payments']['CAB_LBL_NET_AMOUNT'] : 'CAB_LBL_NET_AMOUNT' ?></label>
                                            <p id="commission_net_amount"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="detail-block payment_term_conditions">
                                    <h4><?= isset($label_arr['Payments']['CAB_LBL_PAYMENT_TERM_AND_CONDITIONS']) ? $label_arr['Payments']['CAB_LBL_PAYMENT_TERM_AND_CONDITIONS'] : 'CAB_LBL_PAYMENT_TERM_AND_CONDITIONS' ?></h4>
                                    <div class="payment_term_condition_text" id="set_payment_term_and_condition"></div>
                                    <div class="accept_text"><input id="is_agree_payment_term" name="is_agree_payment_term" type="checkbox" value="1">
                                        <label><?= isset($label_arr['Payments']['CAB_LBL_PAYMENT_TERMS_AND_CONDITIONS_TEXT']) ? $label_arr['Payments']['CAB_LBL_PAYMENT_TERMS_AND_CONDITIONS_TEXT'] : 'CAB_LBL_PAYMENT_TERMS_AND_CONDITIONS_TEXT' ?></label>
                                    </div>
                                </div>

                                <div class="detail-block otp-block hide">
                                    <h4><?= isset($label_arr['Payments']['CAB_LBL_OTP_BLOCK_NAME']) ? $label_arr['Payments']['CAB_LBL_OTP_BLOCK_NAME'] : 'CAB_LBL_OTP_BLOCK_NAME' ?></h4>
                                    <div class="flex justify-center otp_container" id="OTPInput"></div>
                                    <div class="text-left" style="display:inline-block;">Time: <span id="timer"></span></div>
                                    <div class="text-right" style="display:inline-block;float:right;">
                                        <a href="javascript:void(0);" title="Resend OTP" id="resend_otp_link" class="resend_otp_link"><?= isset($label_arr['Payments']['CAB_LBL_OTP_RESEND_LINK']) ? $label_arr['Payments']['CAB_LBL_OTP_RESEND_LINK'] : 'CAB_LBL_OTP_RESEND_LINK' ?></a>
                                    </div>
                                    <div class="well-sm"></div>
                                    <p class="otp_note">
                                        <b>Note: </b><?= isset($label_arr['CustomerPortal_Client']['CAB_MSG_PAYMENT_PLEASE_CHECK_EMAIL_FOR_OTP']) ? $label_arr['CustomerPortal_Client']['CAB_MSG_PAYMENT_PLEASE_CHECK_EMAIL_FOR_OTP'] : 'CAB_MSG_PAYMENT_PLEASE_CHECK_EMAIL_FOR_OTP' ?>
                                    </p>
                                </div>
                                <input type="hidden" id="file_id" name="file" />
                                <input type="hidden" value="Confirm" name="sub_operation" />
                                <input type="hidden" id="ip_address_val" value="" name="ip_address" />


                                <div class="panel panel-danger hide error_message"></div>
                                <div class="panel panel-success hide success_message"></div>
                                <img src="payment/loader.gif" class="loading" style="display:none"/>
                                <div class="col-md-5"><button type="button" name="submit" class="btn submit-btn confirm" id="confirm_back">Back</button></div>
                                <div class="col-md-2"></div>
                                <div class="col-md-5"><button type="submit" name="submit" class="btn submit-btn confirm" id="suboperation_confirm">Confirm</button></div>

                            </form>
                        </div>

                        <div class="form_deposit hide" id="form_deposit"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>


<div id="modelCurrCon" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-label="Close" id="hidePopupBtn" class="close" type="button" data-dismiss="modal"><span aria-hidden="true">×</span></button>
                <div class="row">
                    <div class="col-md-10">
                        <h4 class="modal-title" id="modelCurrCon"><?= $label_arr['Payments']['CAB_LBL_CURRENCY_CONVERTER'] ?></h4>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <?php
                $currConv = $formSchema['paymentdescribe']['currency_convertor_rate'];
                $currConvArray = [];
                $j = 0;
                foreach ($currConv as $key => $value) {
                    $currConvArray[$j]['key'] = $key;
                    $currConvArray[$j]['value'] = $value;
                    $j++;
                }
                ?>
                <div class="text-center margin-bottom-15">
                    <h4 class="currency-converter-rate padding-top-5 padding-bottom-5">
                        <label class="modal-title"><?= $label_arr['Payments']['CAB_LBL_TODAYS_RATE'] ?></label><br ><label class="modal-title currency-value"><?= $currConvArray[0]['key'] . ' : ' . $currConvArray[0]['value']; ?></label>
                    </h4>
                </div>
                <form novalidate="">
                    <input type="hidden" name="currency-rate" id="currency-rate" value="<?= $currConvArray[0]['value']; ?>">
                    <div class="row">
                        <div class="col-md-6 col-xs-7">
                            <div class="form-group">
                                <input class="form-control" id="currency_value" name="currency_value" placeholder="<?= $label_arr['Payments']['CAB_LBL_AMOUNT'] ?>">
                                <span class="currency_value-errMsg errorTxt"></span>
                            </div>
                        </div>
                        <div class="col-md-6 col-xs-5">
                            <div class="form-group">
                                <select class="form-control" id="changeCurrency" name="changeCurrency" >
                                    <?php $j = 0; ?>
                                    <?php
                                    foreach ($currConvArray as $value) {
                                        $currValue = $value['key'] . ' : ' . $value['value'];
                                        $splitCurr = str_split($value['key'], 3);
                                        $arr = array('rate' => $value['value'], 'currValue' => $currValue, 'base' => $splitCurr[1]);
                                        $arr = json_encode($arr);
                                        ?>
                                        <option value='<?= $arr ?>' <?php ($j == 0) ? 'selected' : '' ?>><?= $splitCurr[0] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6 col-xs-7">
                            <div class="form-group">
                                <input class="form-control" id="bank_currency_value" name="bank_currency_value" placeholder="<?= $label_arr['Payments']['CAB_LBL_AMOUNT'] ?>">
                                <span class="bank_currency_value-errMsg errorTxt"></span>
                            </div>
                        </div>
                        <div class="col-md-6 col-xs-5">
                            <div class="form-group">
                                <?php $splitCurr = str_split($currConvArray[0]['key'], 3); ?>
                                <input class="form-control basecurr" id="bank_currency" placeholder="<?= $label_arr['Payments']['CAB_LBL_BANK_CURRENCY'] ?>" name="bank_currency" value="<?= $splitCurr[1] ?>" disabled>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="payment/js/jquery.min.js"></script>
<script src="payment/js/bootstrap.min.js"></script>
<script src="payment/js/otp_input.js"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="payment/js/jquery.validate.min.js"></script>
<script src="payment/js/additional-methods.min.js"></script>

<script type="text/javascript">
    $.getJSON("https://api.ipify.org/?format=json", function(e) {
    $('#ip_address_val').val(e.ip);
});
    //var country_arr = <?php echo json_encode($country_arr); ?>;
    var fair_pay_method_arr = <?php echo json_encode($picklist_arr['fairpay_payment_method']); ?>;
    var help2pay_bank_code_arr = <?php echo json_encode($picklist_arr['help2pay_bank_code']); ?>;
    var payment_operation = "<?= $payment_operation ?>";
    var file_size_msg = "<?= $label_arr['CustomerPortal_Client']['CAB_MSG_FILE_SIZE_SHOULD_NOT_BE_GREATER_THAN_MB'] ?>";
    var file_type_msg = "<?= $label_arr['CustomerPortal_Client']['CAB_MSG_FILE_TYPE_DOES_NOT_ALLOWED'] ?>";
    var resend_otp_time = "<?= $resendOtpDuration ? $resendOtpDuration : 0 ?>";
    $(document).ready(function () {
        var flagW = 1;
        var payment_term_conditions = '';

        $("input[name=payment_type]").change(function () {
            setAccountDropdown(payment_type);
        });
        
        $("input[name=card_expiry_date]").keyup(function () {
            if ($(this).val().length == 2 && $(this).val().indexOf('/') == -1) {
                $(this).val($(this).val() + '/');
            } else {
                $(this).val($(this).val());
            }
        });

        $.validator.addMethod("expValid", function (value, element) {
            return this.optional(element) || /^(0[1-9]|1[0-2])\/?([/][2][0][0-9]{2})$/i.test(value);
        }, "Expiry date is not valid.");

        $.validator.addMethod("expValidYear", function (value, element) {            
            const expiry = value.split("/");
            let exmonth = expiry[0];
            let exyear = expiry[1];
            var date = new Date();
            var currentMonth = date.getMonth() + 1;
            var currentYear = date.getFullYear();
            var validate = true;
            if (exyear < currentYear) {
                validate = false;
            } else if (exyear == currentYear) {
                if (exmonth <= currentMonth) {
                    validate = false;
                }
            }
            return validate;
        }, "Please enter a proper expiry date in mm/yyyy format.");

        $('#submit-form').validate({
            rules: {
                card_number: {
                    required: true,
                    number: true,
                    minlength: 14,
                    maxlength: 16,
                },
                card_expiry_date: {
                    required: true,
                    expValid: true,
                    expValidYear: true,
                },
                card_cvc: {
                    required: true,
                    number: true,
                    minlength: 3,
                    maxlength: 4,
                }
            },
            messages: {
                card_number: {
                    required: "Please enter card number.",
                    number: "Card number should consist of digits only.",
                    minlength: 'Card number should be between 14-16 digits only.',
                    maxlength: 'Card number should be between 14-16 digits only.'
                },
                card_expiry_date: {
                    required: "Please enter card expiry.",
                    expValidYear: "Please enter a proper expiry date in mm/yyyy format."
                },
                card_cvc: {
                    required: "Please enter card cvc.",
                    number: "Card CVC should consist of digits only.",
                    minlength: "Please enter a proper cvc.",
                    maxlength: "Please enter a proper cvc.",
                }
            }, 
            submitHandler: function(form) {
                
                $("#suboperation_submit").addClass('btn_disabled');
                // e.preventDefault(); // avoid to execute the actual submit of the form.

                var formData = new FormData(form);
                //File validation
                if ($('input[type="file"]').get(0) != undefined) {
                    var files = $('input[type="file"]').get(0).files;
                    var type_arr = ['JPEG', 'JPG', 'PNG', 'PDF'];
                    var file_size = $("#filesize").val();
                    $(".errorTxt").html('');
                    for (var i = 0; file = files[i]; i++) {
                        var res = file.type.split("/");
                        file_size = file_size * 1000000;
                        if (file.size > file_size) {
                            $(".errorTxt").html(file_size_msg);
                            $("#suboperation_submit").removeClass('btn_disabled');
                            return false;
                        } else if (!type_arr.includes(res[1].toUpperCase())) {
                            $(".errorTxt").html(res[1].toUpperCase() + file_type_msg);
                            $("#suboperation_submit").removeClass('btn_disabled');
                            return false;
                        }
                    }
                }
                var ipAddress = $('#ip_address_val').val();console.log(ipAddress);
                formData.append("ip_address", ipAddress);
                //End
                $.ajax({
                    type: 'POST',
                    data: formData,
                    async: false,
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $("body").addClass('set_body_opacity');
                        $('.cs-page-loading').addClass('active');
                        $("#suboperation_submit").attr("disabled", true);
                        $("#suboperation_submit").hide();
                    },
                    complete: function () {
                        $("#suboperation_submit").show();
                        $("#suboperation_submit").attr("disabled", false);
                        //$("#suboperation_submit").removeClass('btn_disabled');
                    },
                    success: function (response) {
                        if (response.success) {
                            setTimeout(function () {
                                $("body").removeClass('set_body_opacity');
                                $('.cs-page-loading').removeClass('active');
                                //tab process
                                $('.confirm-tab').trigger("click");
                                $('.transfer-detail-block').hide();
                                $('.confirm-transfer-block').show();
                                $('.tab-link').removeClass('active');
                                $('.confirm-tab').addClass('active');
                                //End
                            }, 3000);
                            $('#confirm_payment_from').html(response.result.payment_from);
                            $('#confirm_payment_currency').html(response.result.payment_currency);
                            $('#confirm_payment_to').html(response.result.payment_to);
                            $('#confirm_payment_amount').html(response.result.amount);
                            $('#confirm_commission').html(response.result.commission);
                            $('#commission_amount').html(response.result.commission_amount);
                            $('#commission_net_amount').html(response.result.net_amount);                                
                            
                            /*OTP related condition*/
                            var isOtpEnable = response.result.is_otp_enable;
                            if(isOtpEnable)
                            {
                                jQuery('div.otp-block').removeClass('hide');
                                jQuery('#resend_otp_link').addClass('disabled');
                                jQuery('span#timer').removeClass('stop');
                                timer(resend_otp_time);
                            }
                            /*OTP related condition*/
                            if (response.result.payment_term_conditions != '' && response.result.payment_operation != 'InternalTransfer') {
                                payment_term_conditions = response.result.payment_term_conditions;
                                $('.payment_term_conditions').show();
                                $('#set_payment_term_and_condition').html(payment_term_conditions);
                                flagW = 1;
                            } else {
                                flagW = 0;
                                payment_term_conditions = '';
                                $('.payment_term_conditions').hide();
                            }
                            //Set custom data
                            var custdata = new Array();
                            var file_data = '';
                            $.each(response.result.custom_data, function (i, item) {

                                if (payment_operation == 'Deposit') {
                                    if (item['type'] == 'file') {
                                        file_data = item['value'];
                                        custdata[i] = '<div class="confirm-box" id="custom_data"><label>' + item['label'] + '</label><p>' + item['filename'] + '</p></div>';
                                    } else {
                                        custdata[i] = '<div class="confirm-box" id="custom_data"><label>' + item['label'] + '</label><p>' + item['value'] + '</p></div>';
                                    }
                                }
                                if (payment_operation == 'Withdrawal') {
                                    if (item['value'] != "" && item['type'] != 'hidden')
                                        custdata[i] = '<div class="confirm-box" id="custom_data"><label>' + item['label'] + '</label><p>' + item['value'] + '</p></div>';
                                }
                            });
                            $('#custom_data').html(custdata);
                            if (file_data != '')
                                $('#file_id').val(file_data);
                        } else {
                            $("#suboperation_submit").removeClass('btn_disabled');
                            $("body").removeClass('set_body_opacity');
                            $('.cs-page-loading').removeClass('active');
                            $('.error_message').removeClass('hide');
                            var msg = response.error.message;
                            msg = label_arr.Payments.msg;
                            if (msg == undefined)
                                msg = response.error.message;
                            $('.error_message').html('<div class="panel-heading"><h4 class="panel-title">' + msg + '</h4></div>');
                            setTimeout(function () {
                                $('.error_message').addClass('hide');
                            }, 5000);
                        }
                    }
                });


            }
        });

        // $("#submit-form").submit(function (e) {

        //     $("#suboperation_submit").addClass('btn_disabled');
        //     e.preventDefault(); // avoid to execute the actual submit of the form.

        //     var formData = new FormData(this);
        //     //File validation
        //     if ($('input[type="file"]').get(0) != undefined) {
        //         var files = $('input[type="file"]').get(0).files;
        //         var type_arr = ['JPEG', 'JPG', 'PNG', 'PDF'];
        //         var file_size = $("#filesize").val();
        //         $(".errorTxt").html('');
        //         for (var i = 0; file = files[i]; i++) {
        //             var res = file.type.split("/");
        //             file_size = file_size * 1000000;
        //             if (file.size > file_size) {
        //                 $(".errorTxt").html(file_size_msg);
        //                 $("#suboperation_submit").removeClass('btn_disabled');
        //                 return false;
        //             } else if (!type_arr.includes(res[1].toUpperCase())) {
        //                 $(".errorTxt").html(res[1].toUpperCase() + file_type_msg);
        //                 $("#suboperation_submit").removeClass('btn_disabled');
        //                 return false;
        //             }
        //         }
        //     }
        //     var ipAddress = $('#ip_address_val').val();console.log(ipAddress);
        //     formData.append("ip_address", ipAddress);
        //     //End
        //     $.ajax({
        //         type: 'POST',
        //         data: formData,
        //         async: false,
        //         cache: false,
        //         contentType: false,
        //         processData: false,
        //         beforeSend: function () {
        //             $("body").addClass('set_body_opacity');
        //             $('.cs-page-loading').addClass('active');
        //             $("#suboperation_submit").attr("disabled", true);
        //             $("#suboperation_submit").hide();
        //         },
        //         complete: function () {
        //             $("#suboperation_submit").show();
        //             $("#suboperation_submit").attr("disabled", false);
        //             //$("#suboperation_submit").removeClass('btn_disabled');
        //         },
        //         success: function (response) {
        //             if (response.success) {
        //                 setTimeout(function () {
        //                     $("body").removeClass('set_body_opacity');
        //                     $('.cs-page-loading').removeClass('active');
        //                     //tab process
        //                     $('.confirm-tab').trigger("click");
        //                     $('.transfer-detail-block').hide();
        //                     $('.confirm-transfer-block').show();
        //                     $('.tab-link').removeClass('active');
        //                     $('.confirm-tab').addClass('active');
        //                     //End
        //                 }, 3000);
        //                 $('#confirm_payment_from').html(response.result.payment_from);
        //                 $('#confirm_payment_currency').html(response.result.payment_currency);
        //                 $('#confirm_payment_to').html(response.result.payment_to);
        //                 $('#confirm_payment_amount').html(response.result.amount);
        //                 $('#confirm_commission').html(response.result.commission);
        //                 $('#commission_amount').html(response.result.commission_amount);
        //                 $('#commission_net_amount').html(response.result.net_amount);                                
                        
        //                 /*OTP related condition*/
        //                 var isOtpEnable = response.result.is_otp_enable;
        //                 if(isOtpEnable)
        //                 {
        //                     jQuery('div.otp-block').removeClass('hide');
        //                     jQuery('#resend_otp_link').addClass('disabled');
        //                     jQuery('span#timer').removeClass('stop');
        //                     timer(resend_otp_time);
        //                 }
        //                 /*OTP related condition*/
        //                 if (response.result.payment_term_conditions != '' && response.result.payment_operation != 'InternalTransfer') {
        //                     payment_term_conditions = response.result.payment_term_conditions;
        //                     $('.payment_term_conditions').show();
        //                     $('#set_payment_term_and_condition').html(payment_term_conditions);
        //                     flagW = 1;
        //                 } else {
        //                     flagW = 0;
        //                     payment_term_conditions = '';
        //                     $('.payment_term_conditions').hide();
        //                 }
        //                 //Set custom data
        //                 var custdata = new Array();
        //                 var file_data = '';
        //                 $.each(response.result.custom_data, function (i, item) {

        //                     if (payment_operation == 'Deposit') {
        //                         if (item['type'] == 'file') {
        //                             file_data = item['value'];
        //                             custdata[i] = '<div class="confirm-box" id="custom_data"><label>' + item['label'] + '</label><p>' + item['filename'] + '</p></div>';
        //                         } else {
        //                             custdata[i] = '<div class="confirm-box" id="custom_data"><label>' + item['label'] + '</label><p>' + item['value'] + '</p></div>';
        //                         }
        //                     }
        //                     if (payment_operation == 'Withdrawal') {
        //                         if (item['value'] != "" && item['type'] != 'hidden')
        //                             custdata[i] = '<div class="confirm-box" id="custom_data"><label>' + item['label'] + '</label><p>' + item['value'] + '</p></div>';
        //                     }
        //                 });
        //                 $('#custom_data').html(custdata);
        //                 if (file_data != '')
        //                     $('#file_id').val(file_data);
        //             } else {
        //                 $("#suboperation_submit").removeClass('btn_disabled');
        //                 $("body").removeClass('set_body_opacity');
        //                 $('.cs-page-loading').removeClass('active');
        //                 $('.error_message').removeClass('hide');
        //                 var msg = response.error.message;
        //                 msg = label_arr.Payments.msg;
        //                 if (msg == undefined)
        //                     msg = response.error.message;
        //                 $('.error_message').html('<div class="panel-heading"><h4 class="panel-title">' + msg + '</h4></div>');
        //                 setTimeout(function () {
        //                     $('.error_message').addClass('hide');
        //                 }, 5000);
        //             }
        //         }
        //     });
        // });
        $("#confirm-form").submit(function (e) {
            e.preventDefault(); // avoid to execute the actual submit of the form.
            var formData = new FormData(this);

            if (payment_term_conditions != '' && flagW == 1) {
                var is_agree_payment_term = $('#is_agree_payment_term').is(":checked");//$('#is_agree_payment_term').val();
                if (is_agree_payment_term) {
                    formData.append("is_agree_payment_term", "1");
                } else {
                    $('.error_message').removeClass('hide');
                    var msg = '';
                    msg = label_arr.Payments['CAB_MSG_PAYMENT_PLEASE_ACCEPT_TERMS_AND_CONDITIONS'];
                    $('.error_message').html('<div class="panel-heading"><h4 class="panel-title">' + msg + '</h4></div>');
                    setTimeout(function () {
                        $('.error_message').addClass('hide');
                    }, 5000);
                    return false;
                }
            }
            
            if(!jQuery('div.otp-block').hasClass('hide'))
            {
                var compiledOtpVal = compiledOtp();
                if(compiledOtpVal != '')
                {
                    formData.append("otp", compiledOtpVal);
                }
                else
                {
                    $('.error_message').removeClass('hide');
                    var msg = '';
                    msg = label_arr.Payments['CAB_MSG_PAYMENT_PLEASE_ENTER_OTP'];
                    $('.error_message').html('<div class="panel-heading"><h4 class="panel-title">' + msg + '</h4></div>');
                    setTimeout(function () {
                        $('.error_message').addClass('hide');
                    }, 5000);
                    return false;
                }
            }
            $('#is_agree_payment_term').attr('disabled', true);
            $.ajax({
                type: 'POST',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    //set Loader
                    jQuery('#resend_otp_link').addClass('disabled');
                    $("body").addClass('set_body_opacity');
                    $('.cs-page-loading').addClass('active');
                    //End

                    $("#suboperation_confirm").attr("disabled", true);
                    $("#confirm_back").attr("disabled", true);

                },
                complete: function () {
                    $("#suboperation_confirm").attr("disabled", false);
                    $("#confirm_back").attr("disabled", false);
                },
                success: function (response) {
                    if (response.success) {
                        $('.set_donnot_refresh_message').html(label_arr.CustomerPortal_Client.CAB_MSG_AUTO_PAYMENT_TRANSACTION);
                        setTimeout(function () {
                            // Set  loader
                            $("body").removeClass('set_body_opacity');
                            $('.cs-page-loading').removeClass('active');
                            //End
                            if (response.result.type == 'Popup' && response.result.service_provider_type == 'RazorPay') {
                                var options = JSON.parse(response.result.order_data);
                                /*options.handler = function (res, error) {
                                    //console.log(res);
                                    //console.log(error);
                                    //if (typeof res.razorpay_payment_id == 'undefined' || res.razorpay_payment_id < 1) {
                                    //                                            window.location.href = options.callback_url;
                                    //                                            //location.href = redirect_url;
                                    //                                            //console.log(res);
                                    //}
                                    }*/
                                options.modal.ondismiss = function () {
                                    window.location.href = options.callback_url + '&status=Failed';
                                }
                                var rzp1 = new Razorpay(options);
                                rzp1.open();
                                e.preventDefault();
                            } else if (response.result.type == 'Form') {
                                var theForm = response.result.result_form;
                                $('#form_deposit').html(theForm);
                                document.forms['payment_form'].submit();
                            } else if (response.result.type == 'ChildWindow') {
                                var theForm = response.result.result_form;
                                $('#form_deposit').html(theForm);
                                document.forms['payment_form'].submit();
                            } else if (response.result.type == 'Redirect') {
                                window.location.href = response.result.url;
                            } else if (response.result.type == 'Manual') {
                                window.location.href = 'payment_success.php?message=' + encodeURIComponent(response.result.message);
                            } else if (response.result.type == 'Other') {
                                window.location.href = 'payment_fail.php?message=' + encodeURIComponent('Something wrong!');
                            } else {
                                window.location.href = 'payment_fail.php?message=' + encodeURIComponent('Something wrong!');
                            }
                        }, 10000);
                    } else {
                        //Remove loader
                        $("body").removeClass('set_body_opacity');
                        $('.cs-page-loading').removeClass('active');
                        jQuery('#resend_otp_link').removeClass('disabled');
                        //End

                        $('.error_message').removeClass('hide');
                        var msg = response.error.message;
                        msg = label_arr.Payments.msg;
                        if (msg == undefined)
                            msg = response.error.message;
                        $('.error_message').html('<div class="panel-heading"><h4 class="panel-title">' + msg + '</h4></div>');
                        setTimeout(function () {
                            $('.error_message').addClass('hide');
                            $("#suboperation_confirm").removeAttr("disabled");
                            $("#confirm_back").removeAttr("disabled");
                            if (msg == 'CAB_MSG_WITHDRAW_NOT_ALLOWED_AT_THIS_MOMENT') {
                                location.reload();
                            }
                        }, 5000);
                        
                        if(!jQuery('div.otp-block').hasClass('hide'))
                        {
                            jQuery('#OTPInput > *[id]').val('');
                        }
                    }
                }
            });
        });

        $('#confirm_back').click(function () {
            $('#suboperation_submit').removeAttr("style");
            $("#suboperation_submit").removeClass('btn_disabled');
            $('.transfer-detail-block').show();
            $('.confirm-transfer-block').hide();
            $('.tab-link').removeClass('active')
            $('.transfer-tab').addClass('active');
            jQuery('span#timer').addClass('stop');
            jQuery('#OTPInput > *[id]').val('');
        });
        if (payment_operation == 'Deposit'){
            var payment_type = $("input[name=payment_type]").val();
            setAccountDropdown(payment_type);
        }
        if (payment_operation == 'Withdrawal')
            setAccountDropdown("A2P");
        $("input:radio[name=payment_type]").change(function () {
            var payment_type = $(this).val();
            setAccountDropdown(payment_type);
        });
        $("input:radio[name=payment_currency]").change(function () {
            var payment_type = $("input[name='payment_type']:checked").val();
            setAccountDropdown(payment_type);
        });
        function setAccountDropdown(payment_type) {
            var accounts = '<?= json_encode($formSchema['paymentdescribe']['liveaccount_data']) ?>';
            accounts = $.parseJSON(accounts);
            var selecte_cur = $("input[name='payment_currency']:checked").val();
            $("#payment_to").html("");
            if (payment_type === 'P2E' || payment_type === 'E2P') {
                $("#set_payment_to .set_label").html('<?= $label_arr["Payments"]["CAB_LBL_WALLET"] ?>' + '<font color="red">*</font>');
                $.each(accounts, function (i, item) {
                    if (item['live_metatrader_type'] === "") {
                        $("#payment_to").append(new Option("Wallet-" + item['account_no'], item['account_no']));
                    }
                });
            }
            if (payment_type === 'P2A' || payment_type === 'A2P') {
                $("#set_payment_to .set_label").html('<?= $label_arr["Payments"]["CAB_LBL_ACCOUNT"] ?>' + '<font color="red">*</font>');
                if (accounts.length > 1) {
                    var flag = '';
                    $.each(accounts, function (i, item) {
                        if (selecte_cur == item['live_currency_code'] && item['live_metatrader_type'] != "") {
                            $("#payment_to").append(new Option(item['account_no'], item['account_no']));
                            flag = 1;
                        } else {
                            if (flag == '' && item['live_metatrader_type'] == "")
                                $("#payment_to").append(new Option('Select An Option', ""));
                        }
                    });
                } else {
                    $("#payment_to").append(new Option('Select An Option', ""));
                }
            }
        }



        // Add minus icon for collapse element which is open by default
        $(".collapse.in").each(function () {
            $(this)
                    .siblings(".panel-heading")
                    .find("img")
                    .addClass("rotate");
        });
        // Toggle plus minus icon on show hide of collapse element
        $(".collapse")
                .on("show.bs.collapse", function () {
                    $(this)
                            .parent()
                            .find("img")
                            .addClass("rotate");
                })
                .on("hide.bs.collapse", function () {
                    $(this)
                            .parent()
                            .find("img")
                            .removeClass("rotate");
                });
                
        $('#resend_otp_link').click(function () {
            
            $.ajax({
                url: 'payment.php?resend_otp=1',
                type: 'GET',
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $("body").addClass('set_body_opacity');
                    $('.cs-page-loading').addClass('active');
                    jQuery('#resend_otp_link').addClass('disabled');
                    jQuery('#OTPInput > *[id]').val('');
                },
                complete: function () {
                    jQuery('span#timer').removeClass('stop');
                    timer(resend_otp_time);
                },
                success: function (response) {
                    if (response.success)
                    {
                        $("body").removeClass('set_body_opacity');
                        $('.cs-page-loading').removeClass('active');
                        $('.success_message').removeClass('hide');
                        var msg = '';
                        msg = response.result;
                        $('.success_message').html('<div class="panel-heading"><h4 class="panel-title">' + msg + '</h4></div>');
                        setTimeout(function () {
                            $('.success_message').addClass('hide');
                        }, 5000);
                    }
                    else
                    {
                        //Remove loader
                        $("body").removeClass('set_body_opacity');
                        $('.cs-page-loading').removeClass('active');
                        //End

                        $('.error_message').removeClass('hide');
                        var msg = response.error.message;
                        msg = label_arr.Payments.msg;
                        if (msg == undefined)
                            msg = response.error.message;
                        $('.error_message').html('<div class="panel-heading"><h4 class="panel-title">' + msg + '</h4></div>');
                        setTimeout(function () {
                            $('.error_message').addClass('hide');
                        }, 5000);
                    }
                }
    });
        });
    });
    (function () {
        window.onload = function () {
            $("body").addClass('set_body_opacity');
            setTimeout(function () {
                $("body").removeClass('set_body_opacity');
                $('.cs-page-loading').removeClass('active');
            }, 2000);
        };
    })();


    $("#changeCurrency").change(function () {
        var cval = JSON.parse($(this).val());
        $('.currency-value').html(cval.currValue);
        $('.basecurr').val(cval.base);
        $('#currency-rate').val(cval.rate);
        $("#currency_value").val('');
        $("#bank_currency_value").val('');
    });

    var regex = new RegExp(/[0-9]*[.]{1}[0-9]{3}/i);
    $("#currency_value").on("input", function () {
        var currencyValue = $(this).val();
        if (currencyValue != '') {
            if (currencyValue.match(regex) || !$.isNumeric(currencyValue)) {
                $('.currency_value' + '-errMsg').html('<?= $label_arr["Payments"]["CAB_LBL_PLEASE_ENTER_VALID_AMOUNT"] ?>');
                return false;
            }
            $('.currency_value' + '-errMsg').html('');
            var rate = $('#currency-rate').val();
            bankCurrencyValue = currencyValue * rate;
            bankCurrencyValueFix = bankCurrencyValue.toFixed(2);
            if (bankCurrencyValue) {
                $("#bank_currency_value").val(bankCurrencyValueFix);
            }
        } else {
            $("#bank_currency_value").val('');
        }
    });
    $("#bank_currency_value").on("input", function () {
        var bankCurrencyValue = $(this).val();
        if (bankCurrencyValue != '') {
            if (bankCurrencyValue.match(regex) || !$.isNumeric(bankCurrencyValue)) {
                $('.bank_currency_value' + '-errMsg').html('<?= $label_arr["Payments"]["CAB_LBL_PLEASE_ENTER_VALID_AMOUNT"] ?>');
                return false;
            }
            $('.bank_currency_value' + '-errMsg').html('');
            var rate = $('#currency-rate').val();
            currencyValue = bankCurrencyValue / rate;
            currencyValueFix = currencyValue.toFixed(2);
            if (currencyValue) {
                $("#currency_value").val(currencyValueFix);
            }
        } else {
            $("#currency_value").val('');
        }
    });

    $('#modelCurrCon').on('hidden.bs.modal', function () {

        $(this).find('form').trigger('reset');
        $('.currency-value').html('<?php echo $currConvArray[0]["key"]; ?>' + ' : ' + '<?php echo $currConvArray[0]["value"]; ?>');
        $('#currency-rate').val('<?php echo $currConvArray[0]["value"]; ?>');
    });
    $("#hidePopupBtn").click(function () {
        $("#modelCurrCon").modal("hide");
    });
    $("#bank_swift_IFSC_code").keyup(function () {
        $(this).val($(this).val().toUpperCase());
    });
    $("#country").change(function () {
        var country_val = $("#country").val();
        var fair_pay_select = '<select class="form-control" name="fairpay_payment_method" id="fairpay_payment_method" class="form-control" required><option value="">Select An Option</option>';
        $.each(fair_pay_method_arr, function (k, v) {
            if (v['value'].split('_')[0] == country_val) {
                fair_pay_select += '<option value=' + v['value'] + '>' + v['label'] + '</option>';
            }
        });
        fair_pay_select += '</select>'
        $('#fairpay_payment_method').html(fair_pay_select);
    });

    $("#bank_currency").change(function () {
        var bank_currency_val = $("#bank_currency").val();
        var help2pay_bank_code_select = '<select class="form-control" name="help2pay_bank_code" id="help2pay_bank_code" class="form-control" required><option value="">Select An Option</option>';
        $.each(help2pay_bank_code_arr, function (k, v) {
            if (v['value'].split('_')[0] == bank_currency_val) {
                help2pay_bank_code_select += '<option value=' + v['value'] + '>' + v['label'] + '</option>';
            }
        });
        help2pay_bank_code_select += '</select>'
        $('#help2pay_bank_code').html(help2pay_bank_code_select);
    });

    $("textarea.form-control").on('change', function(){
        var strng = $(this).val();
        var cleanStr = removeEmojis(strng);
        $(this).val(cleanStr);
    });
    $("input.form-control").on('change', function(){
        var strng = $(this).val();
        var cleanStr = removeEmojis(strng);
        $(this).val(cleanStr);
    });
    function removeEmojis (string) {
      var regex = /(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff]|[\u0023-\u0039]\ufe0f?\u20e3|\u3299|\u3297|\u303d|\u3030|\u24c2|\ud83c[\udd70-\udd71]|\ud83c[\udd7e-\udd7f]|\ud83c\udd8e|\ud83c[\udd91-\udd9a]|\ud83c[\udde6-\uddff]|\ud83c[\ude01-\ude02]|\ud83c\ude1a|\ud83c\ude2f|\ud83c[\ude32-\ude3a]|\ud83c[\ude50-\ude51]|\u203c|\u2049|[\u25aa-\u25ab]|\u25b6|\u25c0|[\u25fb-\u25fe]|\u00a9|\u00ae|\u2122|\u2139|\ud83c\udc04|[\u2600-\u26FF]|\u2b05|\u2b06|\u2b07|\u2b1b|\u2b1c|\u2b50|\u2b55|\u231a|\u231b|\u2328|\u23cf|[\u23e9-\u23f3]|[\u23f8-\u23fa]|\ud83c\udccf|\u2934|\u2935|[\u2190-\u21ff])/g;
      return string.replace(regex, '');
    }

    function compiledOtp()
    {
        const inputs = document.querySelectorAll('#OTPInput > *[id]');
        let compiledOtp = '';
        for (let i = 0; i < inputs.length; i++) {
          compiledOtp += inputs[i].value;
        }
//        document.getElementById('otp').value = compiledOtp;
        return compiledOtp;
    }
</script>
</html>