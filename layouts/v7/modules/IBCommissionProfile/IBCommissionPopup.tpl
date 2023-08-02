{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Vtiger/views/Popup.php *}
{strip}
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="clearfix">
                    <div class="pull-right ">
                        <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                            <span aria-hidden="true" class="fa fa-close"></span>
                        </button>
                    </div>
                    <h4 class="pull-left" id="ibcommitem_header"></h4>
                </div>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="BulkIBCommissionForm" name="BulkIBCommissionForm" method="POST" >
                    {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
                        <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
                    {/if}
                    <input type="hidden" name="securitySymbolDependency" value='{$SECURITY_SYMBOL_MAPPING}' />
                    <input type="hidden" name="module" value="{$MODULE}">
                    <table class="massEditTable table no-border">
                        <tr>
                            {assign var=COUNTER value=0}
                            {foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
                                {if $FIELD_NAME neq 'ibcommissionprofileid' and $FIELD_NAME neq 'assigned_user_id'}
                                    {assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
                                    {assign var="referenceList" value=$FIELD_MODEL->getReferenceList()}
                                    {assign var="referenceListCount" value=count($referenceList)}
                                    {if $FIELD_MODEL->get('uitype') eq "19"}
                                        {if $COUNTER eq '1'}
                                            <td></td><td></td></tr><tr>
                                            {assign var=COUNTER value=0}
                                        {/if}
                                    {/if}
                                    {if $COUNTER eq 2}
                                    </tr><tr>
                                        {assign var=COUNTER value=1}
                                    {else}
                                        {assign var=COUNTER value=$COUNTER+1}
                                    {/if}
                                    <td class='fieldLabel col-lg-2' id="fieldLabel_{$FIELD_NAME}">
                                        {if $isReferenceField neq "reference"}<label class="muted pull-right">{/if}
                                            {if $isReferenceField eq "reference"}
                                                {if $referenceListCount > 1}
                                                    {assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
                                                    {assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
                                                    {if !empty($REFERENCED_MODULE_STRUCT)}
                                                        {assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
                                                    {/if}
                                                    <span class="pull-right">
                                                        <select style="width:150px;" class="select2 referenceModulesList {if $FIELD_MODEL->isMandatory() eq true}reference-mandatory{/if}">
                                                            {foreach key=index item=value from=$referenceList}
                                                                <option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if} >{vtranslate($value, $value)}</option>
                                                            {/foreach}
                                                        </select>
                                                    </span>
                                                {else}
                                                    <label class="muted pull-right">{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}</label>
                                                {/if}
                                            {else if $FIELD_MODEL->get('uitype') eq '83'}
                                                {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE PULL_RIGHT=true}
                                                {if $TAXCLASS_DETAILS}
                                                    {assign 'taxCount' count($TAXCLASS_DETAILS)%2}
                                                    {if $taxCount eq 0}
                                                        {if $COUNTER eq 2}
                                                            {assign var=COUNTER value=1}
                                                        {else}
                                                            {assign var=COUNTER value=2}
                                                        {/if}
                                                    {/if}
                                                {/if}
                                            {else}
                                                {vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
                                            {/if}
                                            {if $isReferenceField neq "reference"}</label>{/if}
                                    </td>
                                    {if $FIELD_MODEL->get('uitype') neq '83'}
                                        <td id="fieldValue_{$FIELD_NAME}" class="fieldValue col-lg-4" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
                                            {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                        </td>
                                    {/if}
                                {/if}
                            {/foreach}
                        </tr>
                    </table>
                    {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
                </form>
            </div>
        </div>
    </div>
{/strip}
