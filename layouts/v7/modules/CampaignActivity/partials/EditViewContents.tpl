{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
    {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
        <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
    {/if}

    <div name='editContent'>
        {if $DUPLICATE_RECORDS}
            <div class="fieldBlockContainer duplicationMessageContainer">
                <div class="duplicationMessageHeader"><b>{vtranslate('LBL_DUPLICATES_DETECTED', $MODULE)}</b></div>
                <div>{getDuplicatesPreventionMessage($MODULE, $DUPLICATE_RECORDS)}</div>
            </div>
        {/if}
        <input type="hidden" name="campaign_id" value="{$PARENT_RECORD}">
        
        {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
            {if $BLOCK_FIELDS|@count gt 0}
                <div class='fieldBlockContainer' data-block="{$BLOCK_LABEL}">
                    <h4 class='fieldBlockHeader'>{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
                    <hr>
                    <table class="table table-borderless">
                        <tr>
                            {assign var=COUNTER value=0}
                            {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
                                {if in_array($FIELD_NAME, array('schtypeid', 'schdayofmonth', 'schdayofweek', 'schtime', 'nexttrigger_time', 'schannualdates', 'campaign_id'))} {continue} {/if}
                                {assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
                                {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
                                {assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
                                {assign var="refrenceListCount" value=count($refrenceList)}
                                {if $FIELD_MODEL->isEditable() eq true}
                                    {if $FIELD_NAME eq 'campaign_activity_module'}
                                        <input type="hidden" name="campaign_activity_module" value="{$FIELD_MODEL->get('fieldvalue')}" />
                                        {continue}
                                    {else if $FIELD_NAME eq 'campaign_activity_subject'}
                                        <input type="hidden" name="campaign_activity_subject" value="{$FIELD_MODEL->get('fieldvalue')}" />
                                        {continue}
                                    {else if $FIELD_NAME eq 'campaign_activity_template'}
                                        <input type="hidden" name="campaign_activity_template" value="{$FIELD_MODEL->get('fieldvalue')}" />
                                        {continue}
                                    {/if}
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
                                    <td class="fieldLabel alignMiddle">
                                        {if $MASS_EDITION_MODE}
                                                <input class="inputElement hide" id="include_in_mass_edit_{$FIELD_MODEL->getFieldName()}" data-update-field="{$FIELD_MODEL->getFieldName()}" type="checkbox">&nbsp;
                                        {/if}
                                        {if $isReferenceField eq "reference"}
                                            {if $refrenceListCount > 1}
                                                {assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
                                                {assign var="REFERENCED_MODULE_STRUCTURE" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
                                                {if !empty($REFERENCED_MODULE_STRUCTURE)}
                                                    {assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCTURE->get('name')}
                                                {/if}
                                                <select style="width: 140px;" class="select2 referenceModulesList">
                                                    {foreach key=index item=value from=$refrenceList}
                                                        <option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $value)}</option>
                                                    {/foreach}
                                                </select>
                                            {else}
                                                {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                            {/if}
                                        {else if $FIELD_MODEL->get('uitype') eq "83"}
                                            {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}
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
                                            {if $MODULE eq 'Documents' && $FIELD_MODEL->get('label') eq 'File Name'}
                                                {assign var=FILE_LOCATION_TYPE_FIELD value=$RECORD_STRUCTURE['LBL_FILE_INFORMATION']['filelocationtype']}
                                                {if $FILE_LOCATION_TYPE_FIELD}
                                                    {if $FILE_LOCATION_TYPE_FIELD->get('fieldvalue') eq 'E'}
                                                        {vtranslate("LBL_FILE_URL", $MODULE)}&nbsp;<span class="redColor">*</span>
                                                    {else}
                                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                                    {/if}
                                                {else}
                                                    {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                                {/if}
                                            {else}
                                                {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                            {/if}
                                        {/if}
                                        &nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
                                    </td>
                                    {if $FIELD_MODEL->get('uitype') neq '83'}
                                        <td id="fieldValue_{$FIELD_NAME}" {if in_array($FIELD_MODEL->get('uitype'),array('19','69')) || $FIELD_NAME eq 'description' ||  (($FIELD_NAME eq 'recurringtype' or $FIELD_NAME eq 'reminder_time')  && in_array({$MODULE},array('Events','Calendar')))} class="fieldValue fieldValueWidth80"  colspan="3" {assign var=COUNTER value=$COUNTER+1} {elseif $FIELD_MODEL->get('uitype') eq '56'} class="fieldValue checkBoxType" {elseif $isReferenceField eq 'reference' or $isReferenceField eq 'multireference' } class="fieldValue p-t-8" {else}class="fieldValue" {/if}>
                                            {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                            {if $FIELD_NAME eq 'activity_type'}
                                                <button type="button" id="compose_activity_email" module="Contacts" class="btn addButton btn-primary  m-l-10 {if $FIELD_MODEL->get('fieldvalue') neq 'Email'}hide{/if}" onclick="javascript:Campaigns_RelatedList_Js.triggerCustomSendEmail('index.php?module=CampaignActivity&view=ComposeEmail','CampaignActivity');">&nbsp;&nbsp;Compose Email</button>
                                            {/if}
                                        </td>
                                    {/if}
                                {/if}
                                {if $FIELD_NAME eq 'subject'}
                                    {include file="partials/triggerConditionsView.tpl"|vtemplate_path:'CampaignActivity'}
                                {/if}
                            {/foreach}
                            {*If their are odd number of fields in edit then border top is missing so adding the check*}
                            {if $COUNTER is odd}
                                <td></td>
                                <td></td>
                            {/if}
                        </tr>
                        
                    </table>
                </div>
            {/if}
        {/foreach}
    </div>
{/strip}
