{************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************}
{strip}
<div>
<input type="hidden" name="date_format" id="date_format" value="{{$USER_DATE_FORMAT}}"/>
    <div class="row">
        <div class="main_counter_content text-center white-text">
            <div class="col-md-6 p-r-0">
                <div class="single_counter p-y-2 m-t-1">
                    <p class="statistic-counter">{$TOTALPAYMENTCONTENTS.deposit_count}</p>
                    <span></span>
                    <p>Deposit</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="single_counter p-y-2 m-t-1">
                    <p class="statistic-counter">{$TOTALPAYMENTCONTENTS.withdrawal_count}</p>
                    <p>Withdrawal</p>
                </div>
            </div>
            <div class="col-md-6 p-r-0">
                <div class="single_counter p-y-2 m-t-1">
                    <p class="statistic-counter">{$TOTALPAYMENTCONTENTS.commission_count}</p>
                    <p>IB Commission </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="single_counter p-y-2 m-t-1">
                    <p class="statistic-counter">{$TOTALPAYMENTCONTENTS.lots_count}</p>
                    <p>Lots</p>
                </div>
            </div>
        </div>
    </div>
</div>
{literal}
	<script>
		function checkDateRangeValidation(element){
            var dateRangeVal = element.val();
            var dateVal = dateRangeVal.split(',');
            var mydate1 = convertDateFormat(dateVal[0]);console.log(mydate1);
            var mydate2 = convertDateFormat(dateVal[1]);console.log(mydate2);
            var date1 = new Date(mydate1);
            var date2 = new Date(mydate2);
            var timeDiff = Math.abs(date2.getTime() - date1.getTime());
            var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));console.log(diffDays);
            if(element.closest('div.fieldUiHolder').find('span.error-text').length > 0)
            {
                element.closest('div.fieldUiHolder').find('span.error-text').remove();
            }
            if(diffDays > 30)
            {
                var msg = app.vtranslate('JS_DATE_RANGE_EXCEED_ERROR');
                var errorHtml = '<span class="error-text" style="display: inline-block;">'+msg+'</span>';
                
                element.closest('div.fieldUiHolder').append(errorHtml);
                element.focus();
                return false;
            }
            return true;
        }

		function convertDateFormat(userDate){
            var userDateFormat = jQuery('#date_format').val();
            var date    = userDate.split('-');
            if(userDateFormat === 'mm-dd-yyyy')
            {
                var yr      = date[2];
                var month   = date[0];
                var day     = date[1];
                var newDate = yr + '-' + month + '-' + day;
            }
            else if(userDateFormat === 'yyyy-mm-dd')
            {
                var yr      = date[0];
                var month   = date[1];
                var day     = date[2];
                var newDate = yr + '-' + month + '-' + day;
            }
            else
            {
                var yr      = date[2];
                var month   = date[1];
                var day     = date[0];
                var newDate = yr + '-' + month + '-' + day;
            }
			return newDate;
		}

		jQuery(document).ready(function() {
			var dateContainer = jQuery('.date_container');
			dateContainer.off('datepicker-change', '.dateField').on('datepicker-change', '.dateField', function(e){
				var element = jQuery(e.currentTarget);
				var validDateRange = checkDateRangeValidation(element);
				if(!validDateRange)
                {
                    return false;
                }
                jQuery('.total_payment_widget_footer').find('a[name="drefresh"]').trigger('click');
			});
		});
	</script>
{/literal}
{/strip}