{*
Add By Divyesh Chothani
Date:- 06-01-2019
Comment:- Add Custom Hidden input tage using for loop
*}
{strip}
    {if count($INPUT_HIDDEN_DATA) > 0 }
        {foreach key=fieldname item=fieldvalue from=$INPUT_HIDDEN_DATA}
            <input type="hidden" name="{$fieldname}" value="{$fieldvalue}">
        {/foreach}
    {/if}
{/strip}