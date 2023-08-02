/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Contacts_Detail_Js", {

    /*
     * Add by:- Divyesh Chothani
     * Date:- 18-12-2019
     * Comment:- Change Portal Passowrd
     * */
    triggerPortalChangePassword: function (url, module) {
        app.request.get({'url': url}).then(
                function (err, data) {
                    if (err === null) {
                        app.helper.showModal(data);
                        var form = jQuery('#changePortalPassword');

                        form.on('submit', function (e) {
                            e.preventDefault();
                        });

                        var params = {
                            submitHandler: function (form) {
                                form = jQuery(form);

                                var pattern = new RegExp(/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{8,}$/);
                                //var change_action = form.find('#change_action');
                                //var metatrader_type = form.find('[name="metatrader_type"]');
                                //   var change_action_val = change_action.val();

                                var new_password = form.find('[name="new_password"]');
                                var confirm_password = form.find('[name="confirm_password"]');
                                var password_validation = pattern.test(new_password.val());
                                var confirm_password_validation = pattern.test(confirm_password.val());

                                var record = form.find('[name="record"]');
                                if (password_validation == false || confirm_password_validation == false) {
                                    var errorMessage = app.vtranslate('JS_PASSWORD_VALIDATION');
                                    app.helper.showErrorNotification({"message": errorMessage});
                                    return false;
                                } else if (new_password.val() === confirm_password.val()) {
                                    app.helper.showProgress();
                                    jQuery("button[name='saveButton']").attr("disabled", "disabled");
                                    var params = {
                                        'data': {
                                            'module': app.getModuleName(),
                                            'action': "SavePortalPassword",
                                            'mode': 'savePortalPassword',
                                            'confirm_password': confirm_password.val(),
                                            'new_password': new_password.val(),
                                            'record': record.val(),
                                        }
                                    };

                                    app.request.post(params).then(
                                            function (err, data) {
                                                if (err == null) {
                                                    app.helper.hideProgress();
                                                    app.helper.hideModal();
                                                    var successMessage = app.vtranslate(data.message);
                                                    app.helper.showSuccessNotification({"message": successMessage});
                                                    location.reload();
                                                } else {
                                                    app.helper.hideProgress();
                                                    app.helper.hideModal();
                                                    app.helper.showErrorNotification({"message": err});
                                                    return false;
                                                }
                                            }
                                    );
                                } else {
                                    var errorMessage = app.vtranslate('JS_PASSWORD_MISMATCH_ERROR');
                                    app.helper.showErrorNotification({"message": errorMessage});
                                    return false;
                                }
                            }
                        };
                        form.vtValidate(params);
                    } else {
                        app.helper.showErrorNotification({'message': err.message});
                    }
                }
        );
    },
    /*end*/

    resendPortalPassword : function() {
		var params = {
			'module'                : app.getModuleName(),
			'action'		: 'SendPortalPassword',
			'mode'			: 'resendPortalPassword',
			'recordId'		: jQuery('#recordId').val()
		};
                var message = app.vtranslate('JS_CONFIRM_RESEND_PORTAL_PASSWORD');
                app.helper.showConfirmationBox({'message' : message}).then(
                function(e) {
                    app.request.post({data: params}).then(function(err, data) {console.log(data);
                            if(err === null){
                                app.helper.showSuccessNotification({message: data.message});
                                return false;
                            }
                            app.helper.showErrorNotification({message: err.message});
                            return false;
                    });
                });
	},
}, {
    registerAjaxPreSaveEvents: function (container) {
        var thisInstance = this;
        app.event.on(Vtiger_Detail_Js.PreAjaxSaveEvent, function (e) {
            if (!thisInstance.checkForPortalUser(container)) {
                e.preventDefault();
            }
        });
    },
    /**
     * Function to check for Portal User
     */
    checkForPortalUser: function (form) {
        var element = jQuery('[name="portal"]', form);
        var response = element.is(':checked');
        var primaryEmailField = jQuery('[data-name="email"]');
        if (primaryEmailField.length > 0)
            var primaryEmailValue = primaryEmailField["0"].attributes["data-value"].value;
        if (response) {
            if (primaryEmailField.length == 0) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_PRIMARY_EMAIL_FIELD_DOES_NOT_EXISTS')});
                return false;
            }
            if (primaryEmailValue == "") {
                app.helper.showErrorNotification({message: app.vtranslate('JS_PLEASE_ENTER_PRIMARY_EMAIL_VALUE_TO_ENABLE_PORTAL_USER')});
                return false;
            }
        }
        return true;
    },

    /*Add By Divyesh Chothani
     * Date:- 10-12-2019
     * Comment:- Custom validation
     * */
    registerCustomEvent: function (container) {
        
        jQuery('td#Contacts_detailView_fieldLabel_affiliate_code').hide();
        jQuery('td#Contacts_detailView_fieldValue_affiliate_code').hide();
        jQuery('td#Contacts_detailView_fieldLabel_parent_affiliate_code').hide();
        jQuery('td#Contacts_detailView_fieldValue_parent_affiliate_code').hide();
        jQuery('td#Contacts_detailView_fieldLabel_ib_hierarchy').hide();
        jQuery('td#Contacts_detailView_fieldValue_ib_hierarchy').hide();
        jQuery('td#Contacts_detailView_fieldLabel_ib_depth').hide();
        jQuery('td#Contacts_detailView_fieldValue_ib_depth').hide();
        
        var record_status = jQuery('td#Contacts_detailView_fieldValue_record_status span.value span').text();
        if (record_status.trim() == 'Approved') {
            jQuery('td#Contacts_detailView_fieldLabel_affiliate_code').show();
            jQuery('td#Contacts_detailView_fieldValue_affiliate_code').show();
            jQuery('td#Contacts_detailView_fieldLabel_parent_affiliate_code').show();
            jQuery('td#Contacts_detailView_fieldValue_parent_affiliate_code').show();
        }
        /*Widget Filter*/
        jQuery('#contact_currency').on('change', function () {
            var moduleInstance = new Contacts_Detail_Js();
            moduleInstance.contactSummaryCountWidgetLoadEvent(this.value);
        });
        jQuery('#status_filter').on('change', function () {
            var moduleInstance = new Contacts_Detail_Js();
            moduleInstance.top5TransactionsWidgetLoadEvent(this.value);
        });
        jQuery('#live_status_filter').on('change', function () {
            var moduleInstance = new Contacts_Detail_Js();
            moduleInstance.top5LiveAccountsWidgetLoadEvent(this.value);
        });
        jQuery('#ticket_status_filter').on('change', function () {
            var moduleInstance = new Contacts_Detail_Js();
            var dateFilter = jQuery('#summarydaterange').val();
            moduleInstance.top5TicketsWidgetLoadEvent(dateFilter, this.value);
        });
        jQuery('#summarydaterange').on('change', function () {
            var moduleInstance = new Contacts_Detail_Js();
            var dateFilter = this.value;
            if (dateFilter != '' || dateFilter != null || dateFilter != undefined) {
                var ticketStatus = jQuery('#ticket_status_filter').val();
                moduleInstance.top5TicketsWidgetLoadEvent(dateFilter, ticketStatus);
            }
        });
        jQuery('.btn-more-summary').on('click', function () {
            var tabModule = $(this).attr('data-module');
            jQuery('[data-label-key='+tabModule+']').click();
        });
    },
    registerIBCommDistEventDetail: function () {
//        var currentParentCode = jQuery('td#Contacts_detailView_fieldValue_parent_affiliate_code span.value').text();
        var isAllowMaxCommDist = jQuery('#is_allow_max_ib_comm').val();
        if (isAllowMaxCommDist === '')
        {
            jQuery('td#Contacts_detailView_fieldLabel_is_dist_max_comm').hide();
            jQuery('td#Contacts_detailView_fieldValue_is_dist_max_comm').hide();
            jQuery('td#Contacts_detailView_fieldLabel_comm_amount_per_lot').hide();
            jQuery('td#Contacts_detailView_fieldValue_comm_amount_per_lot').hide();
        }
    },
    
    ibCommWidgetLoadEvent : function() {
            app.helper.showProgress();
            var params = {
                    'module'		: app.getModuleName(),
                    'action'		: 'ContactWidgetAction',
                    'mode'		: 'getIBCommWidgetDetail',
                    'recordId'          : jQuery('#recordId').val()
            };

            app.request.post({data: params}).then(function(err, data) {
                    app.helper.hideProgress();
                    if(err == null) {
                            jQuery('#total_comm_earned').text(data.total_comm_earned);
                            jQuery('#total_comm_withdraw').text(data.total_comm_withdraw);
                            jQuery('#available_comm_amount').text(data.available_comm_amount);
                            jQuery('#total_lots').text(data.total_lots);
                            jQuery('#earned_comm_lots').text(data.earned_volume);
                    }
                    else {
                            app.helper.showErrorNotification({"message":err});
                    }
            });
    },

    contactSummaryCountWidgetLoadEvent : function(currency) {
        app.helper.showProgress();
        var params = {
            'module' : app.getModuleName(),
            'action' : 'ContactWidgetAction',
            'mode' : 'getContactSummaryCountWidgetDetail',
            'recordId' : jQuery('#recordId').val(),
            'currency' : currency
        };

        app.request.post({data: params}).then(function(err, data) {
            app.helper.hideProgress();
            if(err == null) {
                jQuery('#total_deposit').text(data.total_deposit);
                jQuery('#total_withdrawal').text(data.total_withdrawal);
                jQuery('#total_live_account').text(data.total_live_account);
                jQuery('#total_demo_account').text(data.total_demo_account);
                jQuery('#total_lots_contact').text(data.total_lots_contact);
            }
            else {
                app.helper.showErrorNotification({"message":err});
            }
        });
    },

    top5TransactionsWidgetLoadEvent : function(status) {
        app.helper.showProgress();
        var params = {
            'module' : app.getModuleName(),
            'action' : 'ContactWidgetAction',
            'mode' : 'getTop5TransactionsWidgetDetail',
            'recordId' : jQuery('#recordId').val(),
            'status' : status,
        };

        app.request.post({data: params}).then(function(err, data) {
            app.helper.hideProgress();
            var transactionTable = '';
            if(err == null) {
                if (data.length != 0) {
                    jQuery.each(data, function(key, value){
                        transactionTable += "<tr><td id='payment_from'><a href="+value.detailViewUrl+">"+value.payment_from+"</a></td>";
                        transactionTable += "<td id='payment_to'><a href="+value.detailViewUrl+">"+value.payment_to+"</a></td>";
                        transactionTable += "<td id='payment_operation'>"+value.payment_operation+"</td>";
                        transactionTable += "<td id='amount'>"+value.amount+"</td>";
                        // transactionTable += "<td id='currency'>"+value.payment_currency+"</td>";
                        transactionTable += "<td id='status'>"+value.payment_status+"</td>";
                        transactionTable += "<td id='failure_reason'>"+value.failure_reason+"</td>";
                        transactionTable += "<td id='created_time'>"+value.created_time+"</td></tr>";
                    });
                } else {
                    transactionTable += "<tr><td colspan='7' align='center'>Record Not Found</td></tr>";
                }
                jQuery('.top_5_transactions_widget table tbody').html(transactionTable);
            } else {
                app.helper.showErrorNotification({"message":err});
            }
        });
    },

    top5LiveAccountsWidgetLoadEvent : function(status) {
        app.helper.showProgress();
        var params = {
            'module' : app.getModuleName(),
            'action' : 'ContactWidgetAction',
            'mode' : 'getTop5LiveAccountsWidgetDetail',
            'recordId' : jQuery('#recordId').val(),
            'status' : status,
        };

        app.request.post({data: params}).then(function(err, data) {
            app.helper.hideProgress();
            var liveAccTable = '';
            if(err == null) {
                if (data.length != 0) {
                    jQuery.each(data, function(key, value){
                        liveAccTable += "<tr><td id='account_no'><a href="+value.detailViewUrl+">"+value.account_no+"</a></td>";
                        liveAccTable += "<td id='live_label_account_type'>"+value.live_label_account_type+"</td>";
                        liveAccTable += "<td id='live_currency_code'>"+value.live_currency_code+"</td>";
                        liveAccTable += "<td id='leverage'>"+value.leverage+"</td>";
                        liveAccTable += "<td id='live_metatrader_type'>"+value.live_metatrader_type+"</td>";
                        liveAccTable += "<td id='record_status'>"+value.record_status+"</td>";
                        liveAccTable += "<td id='created_date'>"+value.created_date+"</td></tr>";
                    });
                } else {
                    liveAccTable += "<tr><td colspan='7' align='center'>Record Not Found</td></tr>";
                }
                jQuery('.top_5_liveaccounts_widget table tbody').html(liveAccTable);
            } else {
                app.helper.showErrorNotification({"message":err});
            }
        });
    },
    
    top5TicketsWidgetLoadEvent : function(dateFilter, status) {
        app.helper.showProgress();
        var params = {
            'module' : app.getModuleName(),
            'action' : 'ContactWidgetAction',
            'mode' : 'getTop5TicketsWidgetDetail',
            'recordId' : jQuery('#recordId').val(),
            'status' : status,
            'dateFilter' : dateFilter
        };
        if (dateFilter != '' || dateFilter != null) {
            app.request.post({data: params}).then(function(err, data) {
                console.log(data);
                app.helper.hideProgress();
                var ticketTable = '';
                if(err == null) {
                    if (data.length != 0) {
                        jQuery.each(data, function(key, value){
                            ticketTable += "<tr><td id='ticket_no'><a href="+value.detailViewUrl+">"+value.ticket_no+"</a></td>";
                            ticketTable += "<td id='title'>"+value.title+"</td>";
                            ticketTable += "<td id='priority'>"+value.priority+"</td>";
                            ticketTable += "<td id='category'>"+value.category+"</td>";
                            ticketTable += "<td id='createdtime'>"+value.createdtime+"</td>";
                            ticketTable += "<td id='modifiedtime'>"+value.modifiedtime+"</td>";
                            ticketTable += "<td id='status'>"+value.status+"</td></tr>";
                        });
                    } else {
                        ticketTable += "<tr><td colspan='7' align='center'>Record Not Found</td></tr>";
                    }
                    jQuery('.top_5_tickets_widget table tbody').html(ticketTable);
                } else {
                    app.helper.showErrorNotification({"message":err});
                }
            });
        }
    },
    /**
     * Function which will register all the events
     */
    registerEvents: function () {
        var self = this;
        var form = this.getForm();
        this._super();
        this.registerAjaxPreSaveEvents(form);
        this.registerCustomEvent();
        this.registerIBCommDistEventDetail();
        app.event.on("post.relatedListLoad.click", function() {
            if(jQuery('.ib_comm_summary_widget').length)
            {
                self.ibCommWidgetLoadEvent();
            }
            if(jQuery('.contact_summary_widget').length)
            {
                self.contactSummaryCountWidgetLoadEvent('USD');
                jQuery('#contact_currency').on('change', function () {
                    var moduleInstance = new Contacts_Detail_Js();
                    moduleInstance.contactSummaryCountWidgetLoadEvent(this.value);
                });
            }
            if(jQuery('.top_5_transactions_widget').length)
            {
                self.top5TransactionsWidgetLoadEvent('All');
                jQuery('#status_filter').on('change', function () {
                    var moduleInstance = new Contacts_Detail_Js();
                    moduleInstance.top5TransactionsWidgetLoadEvent(this.value);
                });
            }
            if(jQuery('.top_5_liveaccounts_widget').length)
            {
                self.top5LiveAccountsWidgetLoadEvent('All');
                jQuery('#live_status_filter').on('change', function () {
                    var moduleInstance = new Contacts_Detail_Js();
                    moduleInstance.top5LiveAccountsWidgetLoadEvent(this.value);
                });
            }
            if(jQuery('.top_5_tickets_widget').length)
            {
                var dateFilter = jQuery('#summarydaterange').val();
                var ticketStatus = jQuery('#ticket_status_filter').val();
                self.top5TicketsWidgetLoadEvent(dateFilter, ticketStatus);
            } 
            
            jQuery('.btn-more-summary').on('click', function () {
                var tabModule = $(this).attr('data-module');
                jQuery('[data-label-key='+tabModule+']').click();
            });
            
            jQuery('#ticket_status_filter').on('change', function () {
                var moduleInstance = new Contacts_Detail_Js();
                var dateFilter = jQuery('#summarydaterange').val();
                moduleInstance.top5TicketsWidgetLoadEvent(dateFilter, this.value);
            });
            jQuery('#summarydaterange').on('change', function () {
                var moduleInstance = new Contacts_Detail_Js();
                var dateFilter = this.value;
                if (dateFilter != '' || dateFilter != null || dateFilter != undefined) {
                    var ticketStatus = jQuery('#ticket_status_filter').val();
                    moduleInstance.top5TicketsWidgetLoadEvent(dateFilter, ticketStatus);
                }
            });
            
            jQuery(function() {
                if(jQuery('.top_5_tickets_widget').length) {
                    var start = moment().subtract(60, 'days');
                    var end = moment();
                    jQuery('#summarydaterange').attr('value', start + ' - ' + end);
                    jQuery('#summarydaterange').daterangepicker({
                        opens: 'left',
                        startDate: start,
                        endDate: end,
                        ranges: {
                            'Today': [moment(), moment()],
                            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        }
                    });
                }
            });
        });
    }
});
jQuery(document).ready(function(){
    var moduleInstance = new Contacts_Detail_Js();
    if(jQuery('.ib_comm_summary_widget').length)
    {
        moduleInstance.ibCommWidgetLoadEvent();
    }
    if(jQuery('.contact_summary_widget').length)
    {
        moduleInstance.contactSummaryCountWidgetLoadEvent('USD');
    }
    if(jQuery('.top_5_transactions_widget').length)
    {
        moduleInstance.top5TransactionsWidgetLoadEvent('All');
    }
    if(jQuery('.top_5_liveaccounts_widget').length)
    {
        moduleInstance.top5LiveAccountsWidgetLoadEvent('All');
    }
    if(jQuery('.top_5_tickets_widget').length)
    {
        var timeF = jQuery('#summarydaterange').val();
        console.log(timeF);
        moduleInstance.top5TicketsWidgetLoadEvent('', 'All');
    }
});