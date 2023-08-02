/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_RelatedList_Js("Campaigns_RelatedList_Js",{

	triggerSendEmail : function(massActionUrl, module){
		var detailInstance = Vtiger_Detail_Js.getInstance();
		var searchParams = JSON.stringify(detailInstance.getRelatedListSearchParams());
		var data = app.convertUrlToDataParams(massActionUrl);
		var selectedIds = jQuery('#selectedIds').data('selected-ids');
		var excludedIds = jQuery('#excludedIds').data('excluded-ids');
		if(selectedIds == "") {
			app.helper.showAlertBox({message:app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD')});
			return false;
		}
		var params = {
			'search_params' : searchParams,
			'nolistcache' : (jQuery('#noFilterCache').val() == 1) ? 1 : 0,
			'selected_ids' : selectedIds,
			'excluded_ids' : excludedIds,
			'sourceModule' : app.getModuleName(),
			'sourceRecord' : jQuery('#recordId').val()
		};
		jQuery.extend(params, data);
		Vtiger_Index_Js.showComposeEmailPopup(params);
	},
        triggerCustomSendEmail : function(massActionUrl, module){
            var self = this;
            var data = app.convertUrlToDataParams(massActionUrl);
            var params = {
            };
            jQuery.extend(params, data);
            
            app.helper.showProgress();
            app.request.post({data:params}).then(function(err,data){
                    app.helper.hideProgress();
                    if(err === null){
                        if(jQuery('body').find('#composeEmailContainer').length > 0)
                        {
                            jQuery('body').find('#composeEmailContainer').remove();
                        }
                        jQuery('body').append(data);
                        
                        
                        jQuery("#composeEmailContainer").on('shown.bs.modal', function(){
                            var sourceContainer = jQuery('form#massEmailForm');
                            var campaign_activity_module = jQuery('input[name="campaign_activity_module"]').val();
                            var campaign_activity_subject = jQuery('input[name="campaign_activity_subject"]').val();
                            var campaign_activity_template = jQuery('input[name="campaign_activity_template"]').val();
                            jQuery('#subject', sourceContainer).val(campaign_activity_subject);
                            jQuery('#campaign_activity_module option[value='+campaign_activity_module+']', sourceContainer).prop('selected', 'selected').change();
                            jQuery('#description', sourceContainer).val(campaign_activity_template).trigger('change');
                        });
                
                        jQuery('#composeEmailContainer').modal('show');
                        
                        var emailEditInstance = new Emails_MassEdit_Js();
                        
                        var isCkeditorApplied = jQuery('#description').data('isCkeditorApplied');
			if(isCkeditorApplied != true && jQuery('#description').length > 0){
				emailEditInstance.loadCkEditor(jQuery('#description').data('isCkeditorApplied',true));
			}
                        
                        self.registerSelectEmailTemplateEvent();
                    }
                });
        },
        /**
	 * Function to register select Email Template click event
	 * @returns {undefined} 
	 */
	registerSelectEmailTemplateEvent : function(){
                var emailEditInstance = new Emails_MassEdit_Js();
		jQuery("#selectEmailTemplate").off('click').on("click",function(e){
                        e.preventDefault();
			var url = "index.php?"+jQuery(e.currentTarget).data('url');
			var postParams = app.convertUrlToDataParams(url);
			var searchModule = jQuery('.emailModulesList option:selected').val();
                        var params = {
                                'search_value' : searchModule
                        };
                        jQuery.extend(params, postParams);
			app.request.post({data:params}).then(function(err,data){
				if(err === null){
					jQuery('.popupModal').remove();
					var ele = jQuery('<div class="modal popupModal" style="z-index:10000"></div>');
					ele.append(data);
					jQuery('body').append(ele);

					emailEditInstance.showpopupModal();
					app.event.trigger("post.Popup.Load",{"eventToTrigger":"post.EmailTemplateListCustom.click"});
				}
			});
		});
                
                jQuery("#saveEmailTemplateButton").off('click').on("click",function(e){
                        e.preventDefault();
                        var sourceContainer = jQuery('form#massEmailForm');
                        if(jQuery('form#QuickCreate').length > 0)
                        {
                            var destinationContainer = jQuery('form#QuickCreate');
                        }
                        else
                        {
                            var destinationContainer = jQuery('form#EditView');
                        }
                        var campaign_activity_module = jQuery('#campaign_activity_module option:selected', sourceContainer).val();
                        var campaign_activity_subject = jQuery('#subject', sourceContainer).val();
                        var campaign_activity_template = CKEDITOR.instances['description'].getData();
                        jQuery('input[name="campaign_activity_module"]', destinationContainer).val(campaign_activity_module);
                        jQuery('input[name="campaign_activity_subject"]', destinationContainer).val(campaign_activity_subject);
                        jQuery('input[name="campaign_activity_template"]', destinationContainer).val(campaign_activity_template);
                        jQuery('.close', sourceContainer).trigger('click');
                });
	}
},{
	selectedRecordIds : false,
	excludedRecordIds : false,

	loadRelatedList : function(params) {
		var aDeferred = jQuery.Deferred();
		var self = this;
		self._super(params).then(function(data) {
			self.registerEvents();
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},

	changeCustomFilterElementView : function() {
		var self = this;
		var filterSelectElement = self.relatedContentContainer.find('#recordsFilter');
		if(filterSelectElement.length > 0){
			vtUtils.showSelect2ElementView(filterSelectElement);
		}
	},

	registerChangeCustomFilterEvent : function() {
		var self = this;
		var filterSelectElement = this.relatedContentContainer.find('#recordsFilter');
		filterSelectElement.change(function(e){
			var element = jQuery(e.currentTarget);
			if (jQuery('.bootbox-confirm .in').length == 0) {
				var message = app.vtranslate('JS_APPENDED_TO_EXISTING_LIST',self.relatedModulename)+'<br><br>'+app.vtranslate('JS_WISH_TO_PROCEED');
				app.helper.showConfirmationBox({'message':message}).then(function(e){
					var cvId = element.find('option:selected').data('id');
					var params = {
						'sourceRecord' : self.parentRecordId,
						'relatedModule' :self.relatedModulename,
						'viewId' : cvId,
						'module' : app.getModuleName(),
						'action': "RelationAjax",
						'mode' : 'addRelationsFromRelatedModuleViewId'
					};
					app.helper.showProgress();
					app.request.post({"data" : params}).then(
						function(responseData) {
							app.helper.hideProgress();
							if(responseData != null){
								app.helper.showErrorNotification({"message": app.vtranslate('JS_NO_RECORDS_RELATED_TO_THIS_FILTER')});
							} else {
								self.loadRelatedList().then(function() {
									self.triggerRelationAdditionalActions();
								});
							}
						},
						function(textStatus, errorThrown){}
					);
				});
			}
		});
	},

	registerEventToEditRelatedStatus : function() {
		var self = this;
		var statusElement = self.relatedContentContainer.find('.currentStatus');
		statusElement.on('click',function(e) {
			e.stopImmediatePropagation();
			var element = jQuery(e.currentTarget);
			element.addClass('open');
		});
		var statusDropdown = statusElement.find('.dropdown-menu');
		statusDropdown.on('click','a',function(e) {
			e.stopImmediatePropagation();
			var element = jQuery(e.currentTarget);
			var liContainer = element.closest('li');
			var currentStatus = element.closest('.currentStatus');
			var selectedStatusId = liContainer.attr('id');
			var selectedStatusValue = liContainer.data('status');
			var relatedRecordId = element.closest('tr').data('id');
			var params = {
				'relatedModule' : self.relatedModulename,
				'relatedRecord' : relatedRecordId,
				'status' : selectedStatusId,
				'module' : app.getModuleName(),
				'action' : 'RelationAjax',
				'sourceRecord' : self.parentRecordId,
				'mode' : 'updateStatus'
			}
			app.helper.showProgress();
			app.request.post({"data" : params}).then(function(error, responseData) {
				if(responseData) {
					app.helper.hideProgress();
					currentStatus.find('.statusValue').text(selectedStatusValue);
					currentStatus.removeClass('open');
				}
			},
			function(textStatus, errorThrown) {}
			);
		});
	},

	writeSelectedIds : function(selectedIds) {
		var self = this;
		var element = self.relatedContentContainer.find('#selectedIds');
		element.data('selected-ids', selectedIds);
		self.selectedRecordIds = selectedIds;
	},

	writeExcludedIds : function(excludedIds) {
		var self = this;
		var element = self.relatedContentContainer.find('#excludedIds');
		element.data('excluded-ids', excludedIds);
		self.excludedRecordIds = excludedIds;
	},

	readSelectedIds : function(decode) {
		var self = this;
		var element = self.relatedContentContainer.find('#selectedIds');
		var selectedIds = element.data('selected-ids');
		if(selectedIds == "") {
			selectedIds = new Array();
			self.writeSelectedIds(selectedIds);
		}
		if(decode && typeof selectedIds == "object") {
			selectedIds = JSON.stringify(selectedIds);
		}
		return selectedIds;
	},

	reladExcludedIds : function(decode) {
		var self = this;
		var element = self.relatedContentContainer.find('#excludedIds');
		var excludedIds = element.data('excluded-ids');
		if(excludedIds == "") {
			excludedIds = new Array();
			self.writeExcludedIds(excludedIds);
		}
		if(decode && typeof excludedIds == "object") {
			excludedIds = JSON.stringify(excludedIds);
		}
		return excludedIds;
	},
	registerPostSelectionRelatedListActions : function(){
		var selectedIds = this.readSelectedIds(false);
		var sendEmailButton = this.relatedContentContainer.find('.relatedHeader').find('.sendEmail');
		if(selectedIds.length > 0){
			sendEmailButton.removeAttr('disabled');
		}else if(selectedIds.length == 0){
			sendEmailButton.attr('disabled', "disabled");
		}
	},

	markSelectedIdsCheckboxes: function (params) {
		var self = this;
		var selectedIds = params.selected;
		var excludedIds = params.excluded;
		var relatedListContainer = self.relatedContentContainer;
		relatedListContainer.find('#selectedIds').data('selected-ids', selectedIds);
		relatedListContainer.find('#excludedIds').data('excluded-ids', excludedIds);
		if ((selectedIds == '' && excludedIds == '')) {
			return;
		}
		relatedListContainer.find('.listViewEntriesCheckBox').each(function (i, ele) {
			var currentRow = jQuery(ele).closest('tr');
			var recordId = currentRow.data('id').toString();
			if (jQuery.inArray(recordId, excludedIds) == '-1' && (jQuery.inArray(recordId, selectedIds) != '-1' || selectedIds == 'all')) {
				jQuery(ele).prop('checked', true);
				currentRow.addClass('listviewhovercolor');
			}
		});
		self.selectMainCheck();
	},

	selectMainCheck: function () {
		var self = this;
		var relatedListContainer = self.relatedContentContainer;
		var mainCheckBox = relatedListContainer.find('#listViewEntriesMainCheckBox');
		if (relatedListContainer.find('.listViewEntriesCheckBox').not(":checked").length == 0) {
			mainCheckBox.prop("checked", true);
		} else {
			mainCheckBox.prop("checked", false);
		}
	},

	registerCheckboxClickEvent : function() {
		var self = this;
		self.relatedContentContainer.off('click','.listViewEntriesCheckBox').on('click','.listViewEntriesCheckBox',function(e) {
			var element = jQuery(e.currentTarget);
			var recordId = element.val();
			var selectedIds = self.readSelectedIds(false);
			var excludedIds = self.reladExcludedIds(false);
			if(element.is(":checked")) {
				if(selectedIds != "all") {
					selectedIds.push(recordId);
				} else {
					excludedIds.splice($.inArray(recordId, excludedIds), 1);
				}
				element.closest('tr').addClass('listviewhovercolor');
				self.registerPostSelectionRelatedListActions();
			} else {
				if(selectedIds != "all") {
					selectedIds.splice($.inArray(recordId, selectedIds), 1);
				} else {
					excludedIds.push(recordId);
				}
				element.closest('tr').removeClass('listviewhovercolor');
				self.registerPostSelectionRelatedListActions();
			}
			self.writeSelectedIds(selectedIds);
			self.writeExcludedIds(excludedIds);
			self.selectMainCheck();
		});
	},

	registerMainCheckboxClickEvent : function() {
		var self = this;
		self.relatedContentContainer.off('click', '#listViewEntriesMainCheckBox').on('click', '#listViewEntriesMainCheckBox', function (e) {
			var element = jQuery(e.currentTarget);
			if(element.is(":checked")) {
				var selectedIds = self.readSelectedIds(false);
				var excludedIds = self.reladExcludedIds(false);
				self.relatedContentContainer.find('.listViewEntriesCheckBox').each(function(i, ele){
					var recordId = jQuery(ele).val();
					if(selectedIds != "all") {
						selectedIds.push(recordId);
					} else {
						excludedIds.splice($.inArray(recordId, excludedIds), 1);
					}
					jQuery(ele).prop('checked', true).closest('tr').addClass('listviewhovercolor');;
				});
				self.writeSelectedIds(selectedIds);
				self.writeExcludedIds(excludedIds);
				self.getRecordsCount().then(function(count){
					self.relatedContentContainer.find('#totalRecordsCount').text(count);
					self.relatedContentContainer.find('#selectAllMsgDiv').closest('div.messageContainer').removeClass('hide');
				});
				self.registerPostSelectionRelatedListActions();
			} else {
				var selectedIds = self.readSelectedIds(false);
				var excludedIds = self.reladExcludedIds(false);
				self.relatedContentContainer.find('.listViewEntriesCheckBox').each(function(i, ele){
					var recordId = jQuery(ele).val();
					if(selectedIds != "all") {
						selectedIds.splice($.inArray(recordId, selectedIds), 1);
					} else {
						excludedIds.push(recordId);
					}
					jQuery(ele).prop('checked', false).closest('tr').removeClass('listviewhovercolor');;
				});
				self.writeSelectedIds(selectedIds);
				self.writeExcludedIds(excludedIds);
				self.relatedContentContainer.find('#selectAllMsgDiv').closest('div.messageContainer').addClass('hide');
				self.registerPostSelectionRelatedListActions();
			}
		});
	},

	getRecordsCount : function() {
		var aDeferred = jQuery.Deferred();
		var self = this;
		var recordCountEle = self.relatedContentContainer.find('#recordsCount');
		var recordsCount = recordCountEle.val();
		if(recordsCount != "") {
			aDeferred.resolve(recordsCount);
		} else {
			var params = {
				'module' : app.getModuleName(),
				'action' : 'DetailAjax',
				'mode' : 'getRecordsCount',
				'relatedModule' : self.relatedModulename,
				'record' : self.parentRecordId,
				'tab_label' : self.relatedContentContainer.find('#tab_label').val()
			};
			app.helper.showProgress();
			app.request.post({"data" : params}).then(function(error, responseData) {
				app.helper.hideProgress();
				var count = responseData.count;
				recordCountEle.val(count);
				aDeferred.resolve(count);
			});
		}
		return aDeferred.promise();
	},

	registerSelectAllClickEvent : function() {
		var self = this;
		var selectAllContainer = self.relatedContentContainer.find('#selectAllMsgDiv');
		selectAllContainer.click(function(){
			self.relatedContentContainer.find('.listViewEntriesCheckBox').each(function(i, ele){
				jQuery(ele).attr('checked', true);
			});
			self.relatedContentContainer.find('#listViewEntriesMainCheckBox').attr('checked', true);
			self.writeSelectedIds("all");
			selectAllContainer.closest('div.messageContainer').addClass('hide');
			self.relatedContentContainer.find('#deSelectAllMsgDiv').closest('div.messageContainer').removeClass('hide');
		});
	},

	registerDeselectAllClickEvent : function() {
		var self = this;
		var deselectAllContainer = self.relatedContentContainer.find('#deSelectAllMsgDiv');
		deselectAllContainer.click(function(){
			self.relatedContentContainer.find('.listViewEntriesCheckBox').each(function(i, ele){
				jQuery(ele).attr('checked', false);
			});
			self.relatedContentContainer.find('#listViewEntriesMainCheckBox').attr('checked', false);
			self.writeSelectedIds('');
			self.writeExcludedIds('');
			deselectAllContainer.closest('div.messageContainer').addClass('hide');
		});
	},

	postLoadRelatedListViewRecords : function(){
		var thisInstance = this;
		app.event.off('Vtiger.RelatedList.PostLoad.Event');
		app.event.on('Vtiger.RelatedList.PostLoad.Event', function(e) {
			var listParams = {
				"selected" : thisInstance.selectedRecordIds,
				"excluded" : thisInstance.excludedRecordIds
			};
			thisInstance.markSelectedIdsCheckboxes(listParams);
		});
	},

	registerEvents : function() {
		this.changeCustomFilterElementView();
		this.registerEventToEditRelatedStatus();
		this.registerChangeCustomFilterEvent();
		this.registerCheckboxClickEvent();
		this.registerMainCheckboxClickEvent();
		this.registerSelectAllClickEvent();
		this.registerDeselectAllClickEvent();
		this.postLoadRelatedListViewRecords();
		if(typeof jQuery.fn.sadropdown === 'function') {
			jQuery('.currentStatus').find('.dropdown-toggle').sadropdown({
				relativeTo: '.listview-table'
			});
		}
                
                app.event.on("post.EmailTemplateListCustom.click",function(event, data){
				var responseData = JSON.parse(data);
				jQuery('.popupModal').modal('hide');
                                var emailEditInstance = new Emails_MassEdit_Js();
				var ckEditorInstance = emailEditInstance.getckEditorInstance();

				for(var id in responseData){
					var data = responseData[id];
                                        ckEditorInstance.element = jQuery('#description');
					ckEditorInstance.loadContentsInCkeditor(data['info']);
					//fill subject
					jQuery('#subject').val(data['name']);
					var selectedTemplateBody = responseData[id].info;
				}
				var sourceModule = jQuery('[name=source_module]').val();
				var tokenDataPair = selectedTemplateBody.split('$');
				var showWarning = false;
				for (var i=0; i<tokenDataPair.length; i++) {
					var module = tokenDataPair[i].split('-');
					var pattern = /^[A-z]+$/;
					if(pattern.test(module[0])) {
						if(!(module[0] == sourceModule.toLowerCase() || module[0] == 'users' || module[0] == 'custom')) {
							showWarning = true;
						}
					}
				}
				if(showWarning) {
					jQuery('#emailTemplateWarning').removeClass('hide');
				} else {
					jQuery('#emailTemplateWarning').addClass('hide');
				}
			});
                        
            jQuery('#campaign_activity_list_of_email').off('change').on('change', function(e){
                jQuery('.tab-item.active').trigger('click');
            });
	},

	init : function(parentId, parentModule, selectedRelatedTabElement, relatedModuleName) {
		this._super(parentId, parentModule, selectedRelatedTabElement, relatedModuleName);
		this.registerEvents();
	}
});