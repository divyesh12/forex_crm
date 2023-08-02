/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Added By :- Divyesh Chothani 
 * Date :- 17-12-2019
 * Comment:-Payments module field validation
 * */

/*Account and Wallet Transfer Validation */
jQuery.validator.addMethod("paymentAmountValidation", function (value, element, params) {
    try {
        if (value) {
            var form = jQuery(element).closest('form');
            var paymentOperationElement = form.find('[name="payment_operation"]');
            var payment_operation = paymentOperationElement.val();

            var paymentTypeOperationElement = form.find('[name="payment_type"]');
            var payment_type = paymentTypeOperationElement.val();

            var minAccountTransferOperationElement = form.find('[name="min_account_transfer"]');
            var min_account_transfer = minAccountTransferOperationElement.val();

            var maxAccountTransferOperationElement = form.find('[name="max_account_transfer"]');
            var max_account_transfer = maxAccountTransferOperationElement.val();

            var minWalletTransferOperationElement = form.find('[name="min_wallet_transfer"]');
            var min_wallet_transfer = minWalletTransferOperationElement.val();

            var maxWalletTransferOperationElement = form.find('[name="max_wallet_transfer"]');
            var max_wallet_transfer = maxWalletTransferOperationElement.val();

            if (payment_operation == 'InternalTransfer') {

                if (payment_type == 'A2A') {
                    var amount = value;
                    if (amount < parseInt(min_account_transfer)) {
                        return this.optional(element) || false;
                    } else if (amount > parseInt(max_account_transfer)) {
                        return this.optional(element) || false;
                    }
                } else if (payment_type == 'E2E') {
                    var amount = value;
                    if (amount < parseInt(min_wallet_transfer)) {
                        return this.optional(element) || false;
                    } else if (amount > parseInt(max_wallet_transfer)) {
                        return this.optional(element) || false;
                    }
                }

            }

        }
        return true;
    } catch (err) {
        return false;
    }
}, function (params, element) {
    var form = jQuery(element).closest('form');
    var paymentOperationElement = form.find('[name="payment_operation"]');
    var payment_operation = paymentOperationElement.val();

    var paymentTypeOperationElement = form.find('[name="payment_type"]');
    var payment_type = paymentTypeOperationElement.val();

    var minAccountTransferOperationElement = form.find('[name="min_account_transfer"]');
    var min_account_transfer = minAccountTransferOperationElement.val();

    var maxAccountTransferOperationElement = form.find('[name="max_account_transfer"]');
    var max_account_transfer = maxAccountTransferOperationElement.val();

    var minWalletTransferOperationElement = form.find('[name="min_wallet_transfer"]');
    var min_wallet_transfer = minWalletTransferOperationElement.val();

    var maxWalletTransferOperationElement = form.find('[name="max_wallet_transfer"]');
    var max_wallet_transfer = maxWalletTransferOperationElement.val();


    if (payment_operation == 'InternalTransfer') {
        var value = jQuery(element).val();

        if (payment_type == 'A2A') {
            var amount = value;
            if (amount < parseInt(min_account_transfer)) {
                return app.vtranslate('JS_MIN_ACCOUNT_AMMOUNT') + ' ' + min_account_transfer;
            } else if (amount > parseInt(max_account_transfer)) {
                return app.vtranslate('JS_MAX_ACCOUNT_AMMOUNT') + ' ' + max_account_transfer;
            }
        } else if (payment_type == 'E2E') {
            var amount = value;
            if (amount < parseInt(min_wallet_transfer)) {
                return app.vtranslate('JS_MIN_WALLET_AMMOUNT') + ' ' + min_wallet_transfer;
            } else if (amount > parseInt(max_wallet_transfer)) {
                return app.vtranslate('JS_MAX_WALLET_AMMOUNT') + ' ' + max_wallet_transfer;
            }
        }
    }
}
);
/*Account and Wallet Transfer Validation */





