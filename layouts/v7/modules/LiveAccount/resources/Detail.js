/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Detail_Js("LiveAccount_Detail_Js", {

    triggerChangePassword: function (url, module) {
        app.request.get({'url': url}).then(
                function (err, data) {
                    if (err === null) {
                        app.helper.showModal(data);
                        var form = jQuery('#changePassword');

                        form.on('submit', function (e) {
                            e.preventDefault();
                        });

                        var params = {
                            submitHandler: function (form) {
                                form = jQuery(form);

                                var pattern = new RegExp(/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{8,}$/);
                                var change_action = form.find('#change_action');
                                var metatrader_type = form.find('[name="metatrader_type"]');
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
                                            'action': "ChangePassword",
                                            'mode': 'ChangePasswordInvestorPassword',
                                            'confirm_password': confirm_password.val(),
                                            'new_password': new_password.val(),
                                            'change_action': change_action.val(),
                                            'metatrader_type': metatrader_type.val(),
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
                                                    setTimeout(function(){
                                                        location.reload();
                                                    },3000);
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

    resendTradingPassword: function (url, module) {
        var params = {
            'module'        : app.getModuleName(),
			'action'		: 'SendTradingPassword',
			'mode'			: 'resendTradingPassword',
			'recordId'		: jQuery('#recordId').val()
		};
        var message = app.vtranslate('JS_CONFIRM_RESEND_LIVE_ACCOUNT_PASSWORD');
        app.helper.showConfirmationBox({'message' : message}).then(
        function(e) {
            app.helper.showProgress();
            app.request.post({data: params}).then(function(err, data) {
                app.helper.hideProgress();
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

    registorLoadEvent: function (container) {
        jQuery("#LiveAccount_detailView_fieldLabel_live_currency_code").addClass('hide');
        jQuery("#LiveAccount_detailView_fieldValue_live_currency_code").addClass('hide');
    },
    
    registerCustomEvent: function (container) {
//        var metatraderType = jQuery('td#LiveAccount_detailView_fieldValue_live_metatrader_type span.value span').text();
        var metatraderType = jQuery('input[name="original_provider_type"]').val();
        if (metatraderType.trim().toLowerCase() == 'vertex')
        {
            jQuery('td#LiveAccount_detailView_fieldLabel_investor_password').hide();
            jQuery('td#LiveAccount_detailView_fieldValue_investor_password').hide();
        }
        else
        {
            jQuery('td#LiveAccount_detailView_fieldLabel_username').hide();
            jQuery('td#LiveAccount_detailView_fieldValue_username').hide();
        }

        var leverageEnable = jQuery('input[name="levereage_hide_provider_type"]').val();
        if (leverageEnable === "false") {
            jQuery('td#LiveAccount_detailView_fieldLabel_leverage').hide();
            jQuery('td#LiveAccount_detailView_fieldValue_leverage').hide();
        }
    },
    /**
	 * To load Related List Contents
	 * @returns {undefined}
	 */
	registerEventForRelatedTabClick : function(){console.log('registerEventForRelatedTabClick1');
		var self = this;
		var detailViewContainer = this.getDetailViewContainer();
		jQuery('.related-tabs', detailViewContainer).on('click', 'li.tab-item a', function(e, urlAttributes) {
			e.preventDefault();
		});
		jQuery('.related-tabs', detailViewContainer).on('click', 'li.more-tab a', function(e, urlAttributes) {
			e.preventDefault();
		});
		jQuery('.related-tabs', detailViewContainer).on('click', 'li.more-tab', function(e,urlAttributes){
			if(jQuery('.moreTabElement').length != 0){
				jQuery('.moreTabElement').remove();
			}
			var moreTabElement = jQuery(e.currentTarget).clone();
			moreTabElement.find('.content').text('');
			moreTabElement.addClass('moreTabElement');
			moreTabElement.addClass('active');
			var moreElementTitle = moreTabElement.find('a').attr('displaylabel')
			moreTabElement.attr('title',moreElementTitle);
			moreTabElement.find('.tab-icon').removeClass('textOverflowEllipsis');
			jQuery('.related-tab-more-element').before(moreTabElement);
			self.loadSelectedTabContents(moreTabElement, urlAttributes);
			self.registerQtipevent(moreTabElement);
		});
		jQuery('.related-tabs', detailViewContainer).on('click', 'li.tab-item', function(e,urlAttributes){
			var tabElement = jQuery(e.currentTarget);
			self.loadSelectedTabContents(tabElement, urlAttributes);
		});
	},

	loadSelectedTabContents: function(tabElement, urlAttributes){console.log('loadSelectedTabContents1');
                    var self = this;
                    var detailViewContainer = this.getDetailViewContainer();
                    var url = tabElement.data('url');
                    self.loadContents(url,urlAttributes).then(function(data){
                            self.deSelectAllrelatedTabs();
                            self.markRelatedTabAsSelected(tabElement);
                            var container = jQuery('.relatedContainer');
                            app.event.trigger("post.relatedListLoad.click",container.find(".searchRow"));
                            // Added this to register pagination events in related list
                            var relatedModuleInstance = self.getRelatedController();
                            //Summary tab is clicked
                            if(tabElement.data('linkKey') == self.detailViewSummaryTabLabel) {
                                    self.registerSummaryViewContainerEvents(detailViewContainer);
                                    self.registerEventForPicklistDependencySetup(self.getForm());
                            }

                            //Detail tab is clicked
                            if(tabElement.data('linkKey') == self.detailViewDetailTabLabel) {
                                    self.registerEventForPicklistDependencySetup(self.getForm());
//                                    var metatraderType = jQuery('td#LiveAccount_detailView_fieldValue_live_metatrader_type span.value span').text();
                                    var metatraderType = jQuery('input[name="original_provider_type"]').val();
                                    if (metatraderType.trim().toLowerCase() == 'vertex')
                                    {
                                        jQuery('td#LiveAccount_detailView_fieldLabel_investor_password').hide();
                                        jQuery('td#LiveAccount_detailView_fieldValue_investor_password').hide();
                                    }
                                    else
                                    {
                                        jQuery('td#LiveAccount_detailView_fieldLabel_username').hide();
                                        jQuery('td#LiveAccount_detailView_fieldValue_username').hide();
                                    }

                                    var leverageEnable = jQuery('input[name="levereage_hide_provider_type"]').val();
                                    if (leverageEnable === "false") {
                                        jQuery('td#LiveAccount_detailView_fieldLabel_leverage').hide();
                                        jQuery('td#LiveAccount_detailView_fieldValue_leverage').hide();
                                    }
                            }

                            // Registering engagement events if clicked tab is History
                            if(tabElement.data('labelKey') == self.detailViewHistoryTabLabel){
                                    var engagementsContainer = jQuery(".engagementsContainer");
                                    if(engagementsContainer.length > 0){
                                            app.event.trigger("post.engagements.load");
                                    }
                            }

                            relatedModuleInstance.initializePaginationEvents();
                            //prevent detail view ajax form submissions
                            jQuery('form#detailView').on('submit', function(e) {
                                    e.preventDefault();
                            });
                    });
    },
    /**
     * Function which will register all the events
     */
    registerEvents: function () {
        var form = this.getForm();
        this._super();
        this.registorLoadEvent();
        this.registerCustomEvent();
    }
})