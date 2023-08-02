{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Vtiger/views/QuickCreateAjax.php *}
{strip}
    {foreach key=index item=jsModel from=$SCRIPTS}
        <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}">
        </script>
    {/foreach}

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form class="form-horizontal recordEditView" id="QuickCreate" name="QuickCreate" method="post"
                action="">
                {assign var=HEADER_TITLE value="KYC Answers"}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                <input type="hidden" name="contact_id" value="{$CONTACT_ID}" />
                <div class="modal-body">
                    <input type="hidden" name="action" value="SaveAjax">
                    <div class="quickCreateContent">
                    <table class="table table-borderless">
                    <thead>
                        <tr>
                        <th>Question</th>
                        <th>Answer</th>
                        </tr>
                    </thead>
                        <tbody>
                        {foreach item=QUESTION_ANSWERS from=$KYC_ANSWERS}
                            <tr>
                                <td style="width:60%">
                                <span>{$QUESTION_ANSWERS.question}</span>
                                </td>
                                <td class="text-center">
                                {* <input class="inputElement disabled" name="email" type="text" value="{$QUESTION_ANSWERS.answer}" disabled="disabled"> *}
                                <textarea name="text" rows="3" cols="40" class="disabled" disabled="disabled">{$QUESTION_ANSWERS.answer}</textarea>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <center>
                    {if $KYC_BTN_ENABLE eq true}
                        {assign var=BUTTON_LABEL value=$BUTTON_NAME}
                        <button class="btn btn-success" id="kyc_approve" name="kycApproved" ><strong>{$BUTTON_LABEL}</strong></button>
                    {/if}
                        <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </center>
                </div>
            </form>
        </div>
    </div>
{/strip}
{literal}
    <script type="text/javascript">
        jQuery(function () {
            jQuery('#kyc_approve').on('click', function (e) {
                e.preventDefault();
                var element = jQuery(e.currentTarget);
                element.addClass('disabled');
                var contactid = jQuery('input[name="contact_id"]').val();
                app.helper.showProgress();
                kycApprove(contactid).then(
                        function (data, err) {
                            app.helper.hideProgress();
                            app.helper.hideModal();
                            if(data.success)
                            {
                                app.helper.showSuccessNotification({"message": data.message});
                            }
                            else
                            {
                                app.helper.showErrorNotification({"message": data.message});
                            }  
                        }
                    );
            });
        });

        function kycApprove(contactid) {
                var aDeferred = jQuery.Deferred();
                var params = {
                    'module': 'Contacts',
                    'action': 'KycApprove',
                    'contact_id': contactid,
                    'kyc_status': true,
                };
                app.request.post({'data': params}).then(
                        function (err, data) {
                            if (err === null) {
                                aDeferred.resolve(data);
                            }
                        });
                return aDeferred.promise();
            }
    </script>
{/literal}