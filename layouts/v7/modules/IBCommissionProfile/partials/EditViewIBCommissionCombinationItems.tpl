{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}

{strip}
    {foreach item=IBCOMMITEM from=$IBCOMMISSION_ITEMS}
        <tr id="{$IBCOMMITEM['concat_item_values']}">
        <input type='hidden' name ="ibcommissionprofileitemsid[]" value="{$IBCOMMITEM['ibcommissionprofileitemsid']}" id="{$IBCOMMITEM['concat_item_values']}_id" />
        <td class="fieldValue" >
            <input type="text"  value="{$IBCOMMITEM['live_metatrader_type']}" name="live_metatrader_type[]" class="inputElement nameField" data-fieldtype="string" data-fieldname="live_metatrader_type"  aria-required="false" style="width: 85px;" readonly>
        </td>
        <td class="fieldValue">
            <input type="text" value="{$IBCOMMITEM['ibcommission_level']}" name="ibcommission_level[]" class="inputElement nameField" data-fieldtype="string" data-fieldname="ibcommission_level"  aria-required="false" style="width: 95px;" readonly>
        </td>
        <td class="fieldValue">
            <input type="text"  value="{$IBCOMMITEM['security']}" name="security[]" class="inputElement nameField" data-fieldtype="string" data-fieldname="security"  aria-required="false" style="width: 120px;" readonly>
        </td>
        <td class="fieldValue">
            <input type="text"  value="{$IBCOMMITEM['symbol']}" name="symbol[]" class="inputElement nameField" data-fieldtype="string" data-fieldname="symbol"  aria-required="false" style="width: 120px;" readonly>
        </td>
        <td class="fieldValue">
            <input type="text" value="{$IBCOMMITEM['live_label_account_type']}" name="live_label_account_type[]" class="inputElement nameField" data-fieldtype="string" data-fieldname="live_label_account_type"  aria-required="false" readonly>
        </td>
        <td class="fieldValue">
            <input type="text"   value="{$IBCOMMITEM['live_currency_code']}"  name="live_currency_code[]" class="inputElement nameField" data-fieldtype="string" data-fieldname="live_currency_code"  aria-required="false" readonly style="width: 80px;">
        </td>
        <td class="fieldValue">
            <input type="text"  value="{$IBCOMMITEM['ib_commission_type']}"  name="ib_commission_type[]" class="inputElement nameField" data-fieldtype="string" data-fieldname="ib_commission_type"  aria-required="false" readonly>
        </td>
        <td class="fieldValue">
            <input type="text"  value="{$IBCOMMITEM['ib_commission_value']}" name="ib_commission_value[]" class="inputElement nameField" data-fieldtype="string" data-fieldname="ib_commission_value"   data-rule-required="true" aria-required="true" >
        </td>
    </tr>
{/foreach}
{/strip}