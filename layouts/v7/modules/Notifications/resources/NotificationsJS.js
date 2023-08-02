/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger.Class("Notifications_NotificationsJS_Js",{}, {
        
        /**
	 * Function registers event for Notification Reminder
	 */
	registerActivityReminder : function() {
            isAjaxRequest = false;
            notificationIdList = [];
            var self = this;
		var activityReminderInterval = 10;
		if(activityReminderInterval != '') {
			var cacheActivityReminder = app.storage.get('activityReminder', 0);
			var currentTime = new Date().getTime()/1000;
			var nextActivityReminderCheck = app.storage.get('nextActivityReminderCheckTime', 0);
			//If activity Reminder Changed, nextActivityReminderCheck should reset
			if(activityReminderInterval != cacheActivityReminder) {
				nextActivityReminderCheck = 0;
			}
			if(currentTime >= nextActivityReminderCheck) {
				self.requestReminder();
			}
//                        else {
//				var nextInterval = nextActivityReminderCheck - currentTime;
//				setTimeout(function() {self.requestReminder()}, nextInterval*1000);
//			}
		}
	},
        
        /**
	 * Function request for notification reminders
	 */
	requestReminder : function() {
            var self = this;
		var activityReminder = 10;
		if(!activityReminder) {
			return;
		}
		/*var currentTime = new Date().getTime()/1000;
		//requestReminder function should call after notificationreminder popup interval
		setTimeout(function() {self.requestReminder()}, activityReminder*1000);
		app.storage.set('activityReminder', activityReminder);
		//setting next notification reminder check time
		app.storage.set('nextActivityReminderCheckTime', currentTime + parseInt(activityReminder));*/
                if(notificationIdList.length)
                {
                    var maxNotificationId = Math.max.apply(Math,notificationIdList);
                }
		app.request.post({
			'data' : {
				'module' : 'Notifications',
				'action' : 'NotificationReminder',
				'mode'   : 'getNotifications',
                                'isAjaxRequest'  :  isAjaxRequest,
                                'maxNotificationId'  :  maxNotificationId,
			}
		}).then(function(e, res) {
			if(!res.hasOwnProperty('result')) {
				for(i=0; i< res.length; i++) {
					var record = res[i];
					if(typeof record == 'object') {
                                                notificationIdList[i] = record.record_id;
						self.showReminderPopup(record);
                                                
					}
				}
			}
                        self.updateNotificationStatus();
                        self.updateNotificationCount();
                        isAjaxRequest = true;
		});
	},
        updateNotificationStatus: function() {
            var self = this;
            if(!notificationIdList.length)
            {
                return;
            }
            var maxNotificationId = Math.max.apply(Math,notificationIdList);
            app.storage.set('globalMaxNotificationId', maxNotificationId);
//            var globalMaxNotificationId = app.storage.get('globalMaxNotificationId');
//            if(globalMaxNotificationId === maxNotificationId)
//            {
//                return;
//            }
            
//            app.request.post({
//                    'data' : {
//                            'module' : 'Notifications',
//                            'action' : 'NotificationReminder',
//                            'mode'   : 'readNotification',
//                            'max_notification_id'   :  maxNotificationId,
//                            'is_received'   :  true,
//                    }
//            }).then(function(e, res) {
//                    if(res.status)
//                    {
//                        app.storage.set('globalMaxNotificationId', maxNotificationId);
//                    }
//            });
        },
        /**
	 * Function display the Reminder popup
	 */
	showReminderPopup : function(record) {
                var self = this;
                var content = '';
                var more_info_link = '';
                var globalMaxNotificationId = app.storage.get('globalMaxNotificationId');
                
                if(record.link !== '')
                {
                    more_info_link = '<a class="notification-link" href="' + record.link + '"> More Info</a>';
                }
                
                if(globalMaxNotificationId < record.record_id)
                {
                    if(self.getMessageSetting())
                    {
                        var notifyParams = {
                                'title' : record.title + more_info_link,
                                'message' : ''
                        };
                        var settings = {
                                'element' : 'body', 
                                'type' : 'danger', 
                                'delay' : 0
                        };
                        jQuery.notify(notifyParams, settings);
                    }
                    
                    if(self.getSoundSetting())
                    {
                        var audio = jQuery('audio.notification_sound')[0];
                        audio.play();
                    }
                }
                content = '<li class="notification_inner_container_' + record.record_id + '">'+
                    '<div class="col-md-10 col-sm-10 col-xs-12 pd-l0">'+
                        '<p>' + record.title + more_info_link + '</p>'+
                    '</div>'+
                    '<div class="col-md-2 col-sm-2 col-xs-12">'+
                        '<a href="javascript:void(0);" class="notification_read">'+
                        '<i class="fa fa-trash"></i>'+
                        '<input type="hidden" name="notification_read_id" value="' + record.record_id + '"/>'+
                        '</a>'+
                    '</div>'+
                '</li>';
                var notificationContainer = jQuery('.notification-drop-content');
                notificationContainer.append(content);
                
                
            
	},
        readNotification: function(notificationId) {
            var self = this;
            app.request.post({
                    'data' : {
                            'module' : 'Notifications',
                            'action' : 'NotificationReminder',
                            'mode'   : 'readNotification',
                            'notification_id'   :  notificationId,
                    }
            }).then(function(e, res) {
                    if(res.status)
                    {
                        self.cuteHide(jQuery('.notification_inner_container_' + notificationId));
                    }
            });
            setTimeout(function(){
                self.updateNotificationCount();
            },500);
            
        },
        readAllNotifications: function(maxNotificationId) {
            var self = this;
            app.request.post({
                    'data' : {
                            'module' : 'Notifications',
                            'action' : 'NotificationReminder',
                            'mode'   : 'readNotification',
                            'max_notification_id'   :  maxNotificationId,
                    }
            }).then(function(e, res) {
                    if(res.status)
                    {
                        self.cuteHide(jQuery('.notification-drop-content li'));
                        jQuery('.notificationHeader').find('[data-notify="dismiss"]').trigger('click');
                    }
                    
            });
            setTimeout(function(){
                self.updateNotificationCount();
            },1000);
        },
        updateNotificationCount: function() {
            var count = jQuery('.notification-drop-content > li').length;
            jQuery('.notification-container .notification-badge').text(count);
        },
        cuteHide: function(el) {
            el.animate({opacity: '0'}, 150, function(){
              el.animate({height: '0px'}, 150, function(){
                el.remove();
              });
            });
        },
        setNotificationSetting: function(settingVal, settingType) {
            var self = this;
            app.request.post({
                    'data' : {
                            'module' : 'Notifications',
                            'action' : 'NotificationReminder',
                            'mode'   : 'setNotificationSetting',
                            'setting_value'   :  settingVal,
                            'setting_type'   :  settingType,
                    }
            }).then(function(e, res) {
                    if(res.status)
                    {
                        var message = settingType + " setting saved";
                        app.helper.showSuccessNotification({"message":message});
                    }
                    
            });
        },
        getMessageSetting : function() {
            return jQuery('input[name="message_setting"]').prop('checked');
        },
        getSoundSetting : function() {
            return jQuery('input[name="sound_setting"]').prop('checked');
        },
        registerEvents: function() {
            var self = this;
            self.registerActivityReminder();
            
            jQuery(document).on('click', '.notification-container .dropdown-menu', function (e) {
                e.stopPropagation();
            });
            
            jQuery(document).on('click', '.notification-container .notification_read', function (e) {
                var element = jQuery(e.currentTarget);
                var notificationId = element.find('input[name="notification_read_id"]').val();
                self.readNotification(notificationId);
            });
            
            jQuery(document).on('click', '.notification-container .clear_all_notifications', function (e) {
                var element = jQuery(e.currentTarget);
                if(notificationIdList.length)
                {
                    var maxNotificationId = Math.max.apply(Math,notificationIdList);
                    self.readAllNotifications(maxNotificationId);
                }
                else
                {
                    var message = "Notifications not found!";
                    app.helper.showErrorNotification({"message":message});
                }
            });
            
            jQuery(document).on('change', 'input[name="sound_setting"]', function (e) {
                var element = jQuery(e.currentTarget);
                var elementVal = '';
                if(element.prop('checked'))
                {
                    elementVal = 1;
                }
                else
                {
                    elementVal = 0;
                }
                self.setNotificationSetting(elementVal, 'sound');
            });
            jQuery(document).on('change', 'input[name="message_setting"]', function (e) {
                var element = jQuery(e.currentTarget);
                var elementVal = '';
                if(element.prop('checked'))
                {
                    elementVal = 1;
                }
                else
                {
                    elementVal = 0;
                }
                self.setNotificationSetting(elementVal, 'message');
            });
        }
        
});
jQuery(document).ready(function(){
    if(jQuery('.notification-container').length)
    {
        var moduleInstance = new Notifications_NotificationsJS_Js();
        moduleInstance.registerEvents();
    }
});
