{************************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
    {foreach key=FIELD_NAME item=FIELD_MODEL from=$PROVIDER_MODEL}
        {assign var="MANDATORY" value=$FIELD_MODEL->get('mandatory')}
        <div class="col-lg-12">
            <div class="form-group">
                {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
                <div class = "col-lg-4">
                    <label for="{$FIELD_NAME}">{vtranslate($FIELD_MODEL->get('label') , $QUALIFIED_MODULE_NAME)}&nbsp; {if $MANDATORY eq true}<span class="redColor">*</span>{/if}</label>
                </div>
                <div class = "col-lg-6">
                    {assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()}
                    {assign var=FIELD_VALUE value=$RECORD_MODEL->get($FIELD_NAME)}

                    {if $FIELD_TYPE == 'picklist'}
                        <select class="select2 form-control" id="{$FIELD_NAME}" 
                                {if $MANDATORY eq true} data-rule-required="true" {/if}
                                name="{$FIELD_NAME}" placeholder="{vtranslate('LBL_SELECT_ONE', $QUALIFIED_MODULE_NAME)}">
                            <option></option>
                            {assign var=PICKLIST_VALUES value=$FIELD_MODEL->get('picklistvalues')}
                            {foreach item=PICKLIST_VALUE key=PICKLIST_KEY from=$PICKLIST_VALUES}
                                <option value="{$PICKLIST_KEY}" {if $FIELD_VALUE eq $PICKLIST_KEY} selected {/if}>
                                    {vtranslate($PICKLIST_VALUE, $QUALIFIED_MODULE_NAME)}
                                </option>
                            {/foreach}
                        </select>
                    {else if $FIELD_TYPE == 'radio'}
                        <input type="radio" name="{$FIELD_NAME}" value='1' id="{$FIELD_NAME}" {if $FIELD_VALUE} checked="checked" {/if} />&nbsp;{vtranslate('LBL_YES', $QUALIFIED_MODULE_NAME)}&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="{$FIELD_NAME}" value='0' id="{$FIELD_NAME}" {if !$FIELD_VALUE} checked="checked" {/if}/>&nbsp;{vtranslate('LBL_NO', $QUALIFIED_MODULE_NAME)}
                    {else if $FIELD_TYPE == 'password'}
                        <input type="password" id="{$FIELD_NAME}" class="form-control" data-rule-required="{$IS_REQUIRED}" name="{$FIELD_NAME}" value="{$FIELD_VALUE}" />
                    {else if $FIELD_TYPE == 'textarea'}
                        <textarea id="{$FIELD_NAME}" 
                                  {if $MANDATORY eq true} data-rule-required="true" {/if} name="{$FIELD_NAME}" rows="5" cols="33" >{$FIELD_VALUE}</textarea>
                    {else if $FIELD_TYPE == 'date'}
                        <input type="text" id="{$FIELD_NAME}" class="dateField form-control" {if $MANDATORY eq true} data-rule-required="true" {/if} name="{$FIELD_NAME}" value="{$FIELD_VALUE}" />
                    {else if $FIELD_TYPE == 'url'}
                        <input type="text" 
                        {if $MANDATORY eq true} data-rule-required="true" {/if}
                        name="{$FIELD_NAME}"  id="{$FIELD_NAME}" class="form-control" value="{$FIELD_VALUE}" />
                    {else if $FIELD_TYPE == 'file'}
                        <input type="file" name="{$FIELD_NAME}" class="form-control" id="{$FIELD_NAME}" value="{$FIELD_VALUE}" data-value="{$FIELD_VALUE}"/>
                        {if $FIELD_VALUE}
                            <img src="{$FIELD_VALUE}" alt="Crypto Image" width="100" height="100">
                        {/if}
                    {else}
                        <input type="{$FIELD_TYPE}" 
                        {if $MANDATORY eq true} data-rule-required="true" {/if}
                        name="{$FIELD_NAME}"  id="{$FIELD_NAME}" class="form-control" {if $FIELD_NAME == 'username'} {/if} value="{$FIELD_VALUE}" />
                    {/if}
                </div>
            </div>
        </div>
    {/foreach}	
{/strip}