{************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************}
<style>
.m-t-1 { margin-top:8px;}
.p-y-2 { padding:8px;}
.statistic-counter{ font-size: 10px;}
.main_counter_content { color: #ffffff;}
.main_counter_content  .fa{ font-size: 20px;}
.single_counter{ background-color: #6a7580;}
.p-r-0{ padding-right: 0px;}
.p-t-3{ padding-top: 3px;}
</style>
 <div class="dashboardWidgetHeader">
	{include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
<div class="date_container" style="padding-bottom:6px;">
    <label>Date:</label>
    <div class="fieldUiHolder">
    <input class="inputElement input-daterange widgetFilter" style="width:261px;" data-calendar-type="range" name="date_range" data-date-format="dd-mm-yyyy" type="text" value="{$DATE_RANGE_VALUE}" data-value="value">
    <span class="error-text" style="display: inline-block;"></span>
    </div>
</div>

<div class="dashboardWidgetContent">
	{include file="dashboards/TotalPaymentContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>

<div class="widgeticons dashBoardWidgetFooter total_payment_widget_footer">
    <div class="footerIcons pull-right">
        {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME}
    </div>
</div>