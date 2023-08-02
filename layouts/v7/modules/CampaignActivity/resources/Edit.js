/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("CampaignActivity_Edit_Js", {
}, {
    registerEventForChangeInScheduledType: function () {
        var thisInstance = this;
        jQuery('#schtypeid').on('change', function (e) {
            var element = jQuery(e.currentTarget);
            var value = element.val();

            thisInstance.showScheduledTime();
            thisInstance.hideScheduledWeekList();
            thisInstance.hideScheduledMonthByDateList();
            thisInstance.hideScheduledSpecificDate();
            thisInstance.hideScheduledAnually();

            if (value == '1') {	//hourly
                thisInstance.hideScheduledTime();
            } else if (value == '3') {	//weekly
                thisInstance.showScheduledWeekList();
            } else if (value == '4') {	//specific date
                thisInstance.showScheduledSpecificDate();
            } else if (value == '5') {	//monthly by day
                thisInstance.showScheduledMonthByDateList();
            } else if (value == '7') {
                thisInstance.showScheduledAnually();
            }
        });
        
        jQuery(".weekDaySelect").bind("mousedown", function(e) {
            e.metaKey = true;
        }).selectable();
        jQuery( ".weekDaySelect" ).on( "selectableselected selectableunselected", function( event, ui ) {
            var inputElement = jQuery('#schdayofweek');
            var weekDaySelect = jQuery('.weekDaySelect');
            var selectedArray = new Array();
            weekDaySelect.find('.ui-selected').each(function(){
                var value = jQuery(this).data('value');
                selectedArray.push(value);
            });
            var selected = selectedArray.join(',');
            inputElement.val(selected);
        });
    },
    registerEventForChangeActivityType: function () {console.log('registerEventForChangeActivityType');
        var thisInstance = this;
        jQuery('select[name="activity_type"]').on('change', function (e) {console.log('activity_type change');
            var element = jQuery(e.currentTarget);
            var value = element.val();
            
            if(value === 'Email')
            {
                jQuery('#compose_activity_email').removeClass('hide');
            }
            else
            {
                jQuery('#compose_activity_email').addClass('hide');
            }
        });
    },
    hideScheduledTime: function () {
        jQuery('#scheduledTime').addClass('hide');
    },

    showScheduledTime: function () {
        jQuery('#scheduledTime').removeClass('hide');
    },

    hideScheduledWeekList: function () {
        jQuery('#scheduledWeekDay').addClass('hide');
    },

    showScheduledWeekList: function () {
        jQuery('#scheduledWeekDay').removeClass('hide');
    },

    hideScheduledMonthByDateList: function () {
        jQuery('#scheduleMonthByDates').addClass('hide');
    },

    showScheduledMonthByDateList: function () {
        jQuery('#scheduleMonthByDates').removeClass('hide');
    },

    hideScheduledSpecificDate: function () {
        jQuery('#scheduleByDate').addClass('hide');
    },

    showScheduledSpecificDate: function () {
        jQuery('#scheduleByDate').removeClass('hide');
    },

    hideScheduledAnually: function () {
        jQuery('#scheduleAnually').addClass('hide');
    },

    showScheduledAnually: function () {
        jQuery('#scheduleAnually').removeClass('hide');
    },
    
    registerBasicEvents: function (container) {
        this._super(container);
        this.registerEventForChangeInScheduledType();
        this.registerEventForChangeActivityType();console.log(container.prop('id'));
        if(container.prop('id') === 'QuickCreate')
        {
            jQuery('select[name="activity_type"]',container).val('Email').trigger('change');
            jQuery('select[name="schtypeid"]',container).val('4').trigger('change');
        }
    }
});
