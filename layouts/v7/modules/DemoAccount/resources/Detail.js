/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Detail_Js("DemoAccount_Detail_Js", {}, {

     registorLoadEvent: function (container) {
           jQuery("#DemoAccount_detailView_fieldLabel_demo_currency_code").addClass('hide');
           jQuery("#DemoAccount_detailView_fieldValue_demo_currency_code").addClass('hide');
     },
     registerCustomEvent: function (container) {
//        var metatraderType = jQuery('td#DemoAccount_detailView_fieldValue_metatrader_type span.value span').text();
        var metatraderType = jQuery('input[name="original_provider_type"]').val();
        if (metatraderType.trim().toLowerCase() == 'vertex')
        {
            jQuery('td#DemoAccount_detailView_fieldLabel_investor_password').hide();
            jQuery('td#DemoAccount_detailView_fieldValue_investor_password').hide();
        }
        else
        {
            jQuery('td#DemoAccount_detailView_fieldLabel_username').hide();
            jQuery('td#DemoAccount_detailView_fieldValue_username').hide();
        }

        var leverageEnable = jQuery('input[name="levereage_hide_provider_type"]').val();
        if (leverageEnable === "false") {
            jQuery('td#DemoAccount_detailView_fieldLabel_leverage').hide();
            jQuery('td#DemoAccount_detailView_fieldValue_leverage').hide();
        }
    },
     /**
	 * To load Related List Contents
	 * @returns {undefined}
	 */
	registerEventForRelatedTabClick : function(){
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

	loadSelectedTabContents: function(tabElement, urlAttributes){
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
//                                    var metatraderType = jQuery('td#DemoAccount_detailView_fieldValue_metatrader_type span.value span').text();
                                        var metatraderType = jQuery('input[name="original_provider_type"]').val();
                                        if (metatraderType.trim().toLowerCase() == 'vertex')
                                        {
                                                jQuery('td#DemoAccount_detailView_fieldLabel_investor_password').hide();
                                                jQuery('td#DemoAccount_detailView_fieldValue_investor_password').hide();
                                        }
                                        else
                                        {
                                                jQuery('td#DemoAccount_detailView_fieldLabel_username').hide();
                                                jQuery('td#DemoAccount_detailView_fieldValue_username').hide();
                                        }
                                        var leverageEnable = jQuery('input[name="levereage_hide_provider_type"]').val();
                                        if (leverageEnable === "false") {
                                                jQuery('td#DemoAccount_detailView_fieldLabel_leverage').hide();
                                                jQuery('td#DemoAccount_detailView_fieldValue_leverage').hide();
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
      registerEvents: function (container) {
           var form = this.getForm();
           this._super();
           this.registorLoadEvent(container);
           this.registerCustomEvent();
      }
})