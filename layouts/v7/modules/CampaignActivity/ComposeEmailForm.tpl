{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Vtiger/views/ComposeEmail.php *}

{strip}
    <div class="SendEmailFormStep2 modal-dialog modal-lg" id="composeEmailContainer">
        <div class="modal-content">
            <form class="form-horizontal" id="massEmailForm" method="post" action="index.php" enctype="multipart/form-data" name="massEmailForm">
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate('LBL_COMPOSE_EMAIL', $MODULE)}}
                <div class="modal-body">

                    <input type="hidden" name="viewname" value="{$VIEWNAME}" />
                    <input type="hidden" name="module" value="{$MODULE}"/>

                    <input type="hidden" name="view" value="MassSaveAjax" />

                    <input type="hidden" name="emailMode" value="{$EMAIL_MODE}" />
                    <input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />

                    <div class="row toEmailField">
                        <div class="col-lg-12">
                            <div class="col-lg-2">
                                <span class="pull-right">Module&nbsp;<span class="redColor">*</span></span>
                            </div>
                            <div class="col-lg-4 input-group">
                                <select class="select2 emailModulesList form-control" id="campaign_activity_module">
                                    {foreach item=MODULE_NAME from=$RELATED_MODULES}
                                        {if !in_array($MODULE_NAME,array('Contacts','Leads'))} {continue} {/if}
                                        <option value="{$MODULE_NAME}" {if $MODULE_NAME eq $FIELD_MODULE} selected {/if}>{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="row subjectField">
                        <div class="col-lg-12">
                            <div class="col-lg-2">
                                <span class="pull-right">{vtranslate('LBL_SUBJECT',$MODULE)}&nbsp;<span class="redColor">*</span></span>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" name="subject" value="{$SUBJECT}" data-rule-required="true" id="subject" spellcheck="true" class="inputElement"/>
                            </div>
                            <div class="col-lg-4"></div>
                        </div>
                    </div>
                    <div class="row attachment">
                        <div class="col-lg-12">
                            <div class="col-lg-9">
                                <div class="row">
                                    <div class="col-lg-4 insertTemplate">
                                        <button id="selectEmailTemplate" class="btn btn-success pull-right" data-url="module=EmailTemplates&view=Popup&search_key=module">{vtranslate('LBL_SELECT_EMAIL_TEMPLATE',$MODULE)}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {*<div class="container-fluid hide" id='emailTemplateWarning'>
                        <div class="alert alert-warning fade in">
                            <a href="#" class="close" data-dismiss="alert">&times;</a>
                            <p>{vtranslate('LBL_EMAILTEMPLATE_WARNING_CONTENT',$MODULE)}</p>
                        </div>
                    </div> *}        
                    <div class="row templateContent">
                        <div class="col-lg-12">
                            <textarea style="width:390px;height:200px;" id="description" name="description">{$DESCRIPTION}</textarea>
                        </div>
                    </div>

                    {if $RELATED_LOAD eq true}
                        <input type="hidden" name="related_load" value={$RELATED_LOAD} />
                    {/if}
                    <input type="hidden" name="attachments" value='{ZEND_JSON::encode($ATTACHMENTS)}' />
                    <div id="emailTemplateWarningContent" style="display: none;">
                        {vtranslate('LBL_EMAILTEMPLATE_WARNING_CONTENT',$MODULE)}
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="pull-right cancelLinkContainer">
                        <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </div>
                    <button id="saveEmailTemplateButton" name="saveEmailTemplateButton" class="btn btn-success" title="Save" type="submit"><strong>Save</strong></button>
{*                    <button id="saveDraft" name="savedraft" class="btn btn-default" title="{vtranslate('LBL_SAVE_AS_DRAFT',$MODULE)}" type="submit"><strong>{vtranslate('LBL_SAVE_AS_DRAFT',$MODULE)}</strong></button>*}
                </div>
            </form>
        </div>
    </div>
{/strip}
