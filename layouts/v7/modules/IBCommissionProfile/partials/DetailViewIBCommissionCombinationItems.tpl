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
    <table class="table table-borderless  IBCommissionItemDetails" id ="IBCommissionItemDetails">
        <tbody>
            <tr>
                <td class="fieldLabel textOverflowEllipsis"> <span class="muted">{vtranslate('LBL_METATRADER_TYPE', $MODULE_NAME)}&nbsp;&nbsp;</span>

                </td>
                <td class="fieldLabel textOverflowEllipsis"> <span class="muted">{vtranslate('LBL_IBCOMMISSION_LEVEL', $MODULE_NAME)}&nbsp;&nbsp;</span>

                </td>
                <td class="fieldLabel textOverflowEllipsis"> <span class="muted">{vtranslate('LBL_SECURITY', $MODULE_NAME)}&nbsp;&nbsp;</span>

                </td>
                <td class="fieldLabel textOverflowEllipsis"> <span class="muted">{vtranslate('LBL_SYMBOL', $MODULE_NAME)}&nbsp;&nbsp;</span>

                </td>
                <td class="fieldLabel textOverflowEllipsis"> <span class="muted">{vtranslate('LBL_LABEL_ACCOUNT_TYPE', $MODULE_NAME)}&nbsp;&nbsp;</span>

                </td>
                <td class="fieldLabel textOverflowEllipsis"> <span class="muted">{vtranslate('LBL_CURRENCY_CODE', $MODULE_NAME)}&nbsp;&nbsp;</span>

                </td>
                <td class="fieldLabel textOverflowEllipsis"> <span class="muted">{vtranslate('LBL_IB_COMMISSION_TYPE', $MODULE_NAME)}&nbsp;&nbsp;</span>

                </td>
                <td class="fieldLabel textOverflowEllipsis"> <span class="muted">{vtranslate('LBL_IB_COMMISSION_VALUE', $MODULE_NAME)}&nbsp;&nbsp;</span>
                </td>
            </tr>
            {foreach item=IBCOMMITEM from=$IBCOMMISSION_ITEMS}
            <input type='hidden' name ="ibcommissionprofileitemsid[]" value="{$IBCOMMITEM['ibcommissionprofileitemsid']}"/>
            <tr>
                <td class="fieldValue">
                    <span data-field-type="string" class="value">
                        <span>{$IBCOMMITEM['live_metatrader_type']}</span>
                    </span>
                </td>
                <td class="fieldValue">
                    <span data-field-type="string" class="value">
                        <span>{$IBCOMMITEM['ibcommission_level']}</span>
                    </span>
                </td>
                <td class="fieldValue">
                    <span data-field-type="string" class="value">
                        <span>{$IBCOMMITEM['security']}</span>
                    </span>
                </td>
                <td class="fieldValue">
                    <span data-field-type="string" class="value">
                        <span>{$IBCOMMITEM['symbol']}</span>
                    </span>
                </td>
                <td class="fieldValue">
                    <span data-field-type="string" class="value">
                        <span>{$IBCOMMITEM['live_label_account_type']}</span>
                    </span>
                </td>
                <td class="fieldValue">
                    <span data-field-type="string" class="value">
                        <span>{$IBCOMMITEM['live_currency_code']}</span>
                    </span>
                </td>
                <td class="fieldValue">
                    <span data-field-type="string" class="value">
                        <span>{$IBCOMMITEM['ib_commission_type']}</span>
                    </span>
                </td>
                <td class="fieldValue">
                    <span data-field-type="string" class="value">
                        <span>{$IBCOMMITEM['ib_commission_value']}</span>
                    </span>
                </td>
            </tr>
        {/foreach}
        <tr>
            <td class="fieldLabel"></td>
            <td class=""></td>
        </tr>
    </tbody>
</table>
{/strip}