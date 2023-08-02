/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
 
Vtiger_Detail_Js("Reports_Detail_Js",{},{
	advanceFilterInstance : false,
	detailViewContentHolder : false,
	HeaderContentsHolder : false, 
	
	detailViewForm : false,
	getForm : function() {
		if(this.detailViewForm == false) {
			this.detailViewForm = jQuery('form#detailView');
		}
	},
	
	getRecordId : function(){
		return app.getRecordId();
	},
	
	getContentHolder : function() {
		if(this.detailViewContentHolder == false) {
			this.detailViewContentHolder = jQuery('div.editViewPageDiv');
		}
		return this.detailViewContentHolder;
	},
	
	getHeaderContentsHolder : function(){
		if(this.HeaderContentsHolder == false) {
			this.HeaderContentsHolder = jQuery('div.reportsDetailHeader ');
		}
		return this.HeaderContentsHolder;
	},
	
	calculateValues : function(){
		//handled advanced filters saved values.
		var advfilterlist = this.advanceFilterInstance.getValues();
		return JSON.stringify(advfilterlist);
	},
        checkDateRangeValidation : function(){
            var thisInstance = this;
            var dateRangeVal = jQuery('input[name="createdtime"]').val();
            var dateVal = dateRangeVal.split(',');
            var mydate1 = thisInstance.convertDateFormat(dateVal[0]);
            var mydate2 = thisInstance.convertDateFormat(dateVal[1]);
            var date1 = new Date(mydate1);
            var date2 = new Date(mydate2);
            var timeDiff = Math.abs(date2.getTime() - date1.getTime());
            var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            if(jQuery('input[name="createdtime"]').closest('div.fieldUiHolder').find('span.error-text').length > 0)
            {
                jQuery('input[name="createdtime"]').closest('div.fieldUiHolder').find('span.error-text').remove();
            }
            
            if(diffDays > 30)
            {
                var msg = app.vtranslate('JS_DATE_RANGE_EXCEED_ERROR');
                var errorHtml = '<span class="error-text">'+msg+'</span>';
                
                jQuery('input[name="createdtime"]').closest('div.fieldUiHolder').append(errorHtml);
                jQuery('input[name="createdtime"]').focus();
                return false;
            }
            return true;
        },

        IBCommissionDateValidation : function(){
            var thisInstance = this;
            var dateRangeVal = jQuery('input[name="ibcomm_dateselection"]').val();
            var dateVal = dateRangeVal.split(',');
            var mydate1 = thisInstance.convertDateFormat(dateVal[0]);
            var mydate2 = thisInstance.convertDateFormat(dateVal[1]);
            var date1 = new Date(mydate1);
            var date2 = new Date(mydate2);
            var timeDiff = Math.abs(date2.getTime() - date1.getTime());
            var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
            var recordId = jQuery('input[id="recordId"]').val();
            
            if(jQuery('input[name="ibcomm_dateselection"]').closest('div.fieldUiHolder').find('span.error-text').length > 0){
                jQuery('input[name="ibcomm_dateselection"]').closest('div.fieldUiHolder').find('span.error-text').remove();
            }
            
            if(diffDays > 31){
                var msg = app.vtranslate('JS_DATE_RANGE_EXCEED_ERROR');
                var errorHtml = '<span class="error-text">'+msg+'</span>';
                
                jQuery('input[name="ibcomm_dateselection"]').closest('div.fieldUiHolder').append(errorHtml);
                jQuery('input[name="ibcomm_dateselection"]').focus();
                return false;
            }
            return true;
        },
	convertDateFormat : function(userDate){
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
	},
		
	registerSaveOrGenerateReportEvent : function(){
		var thisInstance = this;
		jQuery('.generateReport').on('click',function(e){
            e.preventDefault();
			var advFilterCondition = thisInstance.calculateValues();
            var recordId = thisInstance.getRecordId();
            var currentMode = jQuery(e.currentTarget).data('mode');
            if(jQuery('input[id="recordId"]').val() === '52' && jQuery('input[name="createdtime"]').length)
            {
                var validDateRange = thisInstance.checkDateRangeValidation();
                if(!validDateRange)
                {
                    return false;
                }
            }else if(jQuery('input[name="ibcomm_dateselection"]').length){
            	var validDateRange = thisInstance.IBCommissionDateValidation();
                if(!validDateRange)
                {
                    return false;
                }
            }
            
            var postData = {
                'advanced_filter': advFilterCondition,
                'record' : recordId,
                'view' : "SaveAjax",
                'module' : app.getModuleName(),
                'mode' : currentMode
            };
			app.helper.showProgress();
			app.request.post({data:postData}).then(
				function(error,data){
					app.helper.hideProgress();
					thisInstance.getContentHolder().find('#reportContentsDiv').html(data);
					if(currentMode == 'save') 
						//jQuery('.reportActionButtons').addClass('hide'); //add by Divyesh

						// app.helper.showHorizontalScroll(jQuery('#reportDetails'));
					// To get total records count
					var count  = parseInt(jQuery('#updatedCount').val());
					thisInstance.generateReportCount(count);
				}
			);
		});
	},

	registerEmailReportEvent: function() {
		var thisInstance = this;
		jQuery('.emailReport').on('click', function(e) {
			if(jQuery('input[id="recordId"]').val() === '52') {
				e.preventDefault();
				var advFilterCondition = thisInstance.calculateValues();
				var recordId = thisInstance.getRecordId();
				if (jQuery('input[name="createdtime"]').length) {
					var validDateRange = thisInstance.checkDateRangeValidation();
					if (!validDateRange) {
						return false;
					}
				}

				var postData = {
					'advanced_filter': advFilterCondition,
					'record' : recordId,
					'view' : "SaveAjax",
					'module' : app.getModuleName(),
					'mode' : 'sendemail',
				};
				app.helper.showProgress();
				app.request.post({data:postData}).then(
					function(error,data){
						app.helper.hideProgress();
						var message = app.vtranslate('JS_REQUEST_SENT_FOR_IB_STATISTICS_REPORT_EMAIL', 'Reports');
						app.helper.showSuccessNotification({message:message});
					}
				);
			}
		});
	},
	
    registerEventsForActions : function() {
      var thisInstance = this;
      jQuery('.reportActions').click(function(e){
        var element = jQuery(e.currentTarget); 
        var href = element.data('href');
        var type = element.attr("name");
        var advFilterCondition = thisInstance.calculateValues();
        var headerContainer = thisInstance.getHeaderContentsHolder();
        if(type.indexOf("Print") != -1){
            var newEle = '<form action='+href+' method="POST" target="_blank">\n\
                    <input type = "hidden" name ="'+csrfMagicName+'"  value=\''+csrfMagicToken+'\'>\n\
                    <input type="hidden" value="" name="advanced_filter" id="advanced_filter" /></form>';
        }else{
            newEle = '<form action='+href+' method="POST">\n\
                    <input type = "hidden" name ="'+csrfMagicName+'"  value=\''+csrfMagicToken+'\'>\n\
                    <input type="hidden" value="" name="advanced_filter" id="advanced_filter" /></form>';
        }
        var ele = jQuery(newEle); 
        var form = ele.appendTo(headerContainer);
        form.find('#advanced_filter').val(advFilterCondition);
        form.submit();
      })  
    },
    
    generateReportCount : function(count){
      var thisInstance = this;  
      var advFilterCondition = thisInstance.calculateValues();
      var recordId = thisInstance.getRecordId();
      
      var reportLimit = parseInt(jQuery("#reportLimit").val());
      
        if(count < reportLimit){
            jQuery('#countValue').text(count);
            jQuery('#moreRecordsText').addClass('hide');
        }else{        
            jQuery('#countValue').html('<img src="layouts/v7/skins/images/loading.gif">');
            var params = {
                'module' : app.getModuleName(),
                'advanced_filter': advFilterCondition,
                'record' : recordId,
                'action' : "DetailAjax",
                'mode': "getRecordsCount"
            };
            jQuery('.generateReport').attr("disabled","disabled");
            app.request.post({data:params}).then(
                function(error,data){
                	// console.log(data)
                	// alert(data['result']);
                	var count = parseInt(data);
                	if(data['result'] == 0){
                		var count = parseInt(data['result']);
                	}
                    jQuery('.generateReport').removeAttr("disabled");
                    var count = parseInt(count);
                    //alert(count)
                    jQuery('#countValue').text(count);
                    if(count > reportLimit)
                        jQuery('#moreRecordsText').removeClass('hide');
                    else
                        jQuery('#moreRecordsText').addClass('hide');
                }
            );
        }
      
    },
	
	registerConditionBlockChangeEvent : function() {
		jQuery('.reportsDetailHeader').find('#groupbyfield,#datafields,[name="columnname"],[name="comparator"]').on('change', function() {
			jQuery('.reportActionButtons').removeClass('hide');
		});
		jQuery('.fieldUiHolder').find('[data-value="value"]').on('change input', function() {
			jQuery('.reportActionButtons').removeClass('hide');
		});
		jQuery('.deleteCondition').on('click', function() {
			jQuery('.reportActionButtons').removeClass('hide');
		});
		jQuery(document).on('datepicker-change', function() {
			jQuery('.reportActionButtons').removeClass('hide');
		});
	},
	
	registerEventForModifyCondition : function() {
		jQuery('button[name=modify_condition]').on('click', function(e) {
			var icon =  jQuery(e.currentTarget).find('i');
			var isClassExist = jQuery(icon).hasClass('fa-chevron-right');
			if(isClassExist) {
				jQuery(e.currentTarget).find('i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
				jQuery('#filterContainer').removeClass('hide').show('slow');
			} else {
				jQuery(e.currentTarget).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
				jQuery('#filterContainer').addClass('hide').hide('slow');
			}
			return false;
		});
	},


	//add by divyesh chothani
	referenceModulePopupRegisterEvent : function(container) {
		var thisInstance = this;
		container.on("click",'.contactid_relatedPopup',function(e) {
			var popupReferenceModule = jQuery('input[name="Contacts_popupReferenceModule"]').val();
			var popupReferenceModuleField = jQuery('input[name="Contacts_popupReferenceModuleField"]').val();
			thisInstance.openPopUp(e,popupReferenceModule,popupReferenceModuleField);
		});
		container.on("click",'.liveaccountid_relatedPopup',function(e) {
			var popupReferenceModule = jQuery('input[name="LiveAccount_popupReferenceModule"]').val();
			var popupReferenceModuleField = jQuery('input[name="LiveAccount_popupReferenceModuleField"]').val();
			thisInstance.openPopUp(e,popupReferenceModule,popupReferenceModuleField);
		});
		container.on("click",'.child_contactid_relatedPopup',function(e) {
			var popupReferenceModule = jQuery('input[name="ChildContacts_popupReferenceModule"]').val();
			var popupReferenceModuleField = jQuery('input[name="ChildContacts_popupReferenceModuleField"]').val();
			thisInstance.openPopUp(e,popupReferenceModule,popupReferenceModuleField);
		});
		
	},

	/*
	 * Function to get reference select popup parameters
	 */
	getPopUpParams : function(container,popupReferenceModule,popupReferenceModuleField) {
		var params = {};
		var sourceModule = app.getModuleName();
		var editTaskContainer = jQuery('[name="editTask"]');
		if(editTaskContainer.length > 0){
				sourceModule = editTaskContainer.find('#sourceModule').val();
		}
		var quickCreateConatiner = jQuery('[name="QuickCreate"]');
		if(quickCreateConatiner.length!=0){
				sourceModule = quickCreateConatiner.find('input[name="module"]').val();
		}
		var searchResultContainer = jQuery('#searchResults-container');
		if(searchResultContainer.length) {
			sourceModule = jQuery('select#searchModuleList').val();
		}
		// var popupReferenceModuleElement = jQuery('input[name="popupReferenceModule"]',container).length ? 
		// jQuery('input[name="popupReferenceModule"]',container) : jQuery('input.popupReferenceModule',container);
		// var popupReferenceModule = popupReferenceModuleElement.val();

		// var popupReferenceModule = jQuery('input[name="popupReferenceModule"]').val();
		// var popupReferenceModuleField = jQuery('input[name="popupReferenceModuleField"]').val();
		// alert(popupReferenceModule)
		// alert(popupReferenceModuleField)
		var sourceFieldElement = jQuery('input[class="sourceField"]',container);
		if(!sourceFieldElement.length) {
			sourceFieldElement = jQuery('input.sourceField',container);
		}
		var sourceField = sourceFieldElement.attr('name');
		var sourceRecordElement = jQuery('input[name="record"]');
		var sourceRecordId = '';
		var recordId = app.getRecordId();
		if(sourceRecordElement.length > 0) {
			sourceRecordId = sourceRecordElement.val();
		} else if(recordId) {
			sourceRecordId = recordId;
		} else if(app.view() == 'List') {
			var editRecordId = jQuery('#listview-table').find('tr.listViewEntries.edited').data('id');
			if(editRecordId) {
				sourceRecordId = editRecordId;
			}
		}

		if(searchResultContainer.length) {
			sourceRecordId = searchResultContainer.find('tr.listViewEntries.edited').data('id')
		}

		var isMultiple = false;
		if(sourceFieldElement.data('multiple') == true) {
			isMultiple = true;
		}

		// TODO : Need to recheck. We don't have reference field module name if that module is disabled
		if(typeof popupReferenceModule == "undefined"){
			popupReferenceModule = "undefined";
		}
		var params = {
			'module' : popupReferenceModule,
			//'src_module' : sourceModule,
			//'src_module' : 'CustomReports',
			'src_module' : 'CustomReports',
			'src_field' : popupReferenceModuleField,
			'src_record' : sourceRecordId,
			//'custom_report':true
		}
		var contact_recordid = jQuery('input[name="contactid"]').val();
		var child_contact_recordid = jQuery('input[name="child_contactid"]').val();
		if(popupReferenceModuleField == 'liveaccountid' && contact_recordid){
			params['column1'] = 'contactid';
			params['value1'] =  contact_recordid;
			params['column2'] =  'record_status';
			params['value2'] =  'Approved';
			params['related_parent_module'] = 'Contacts';
		}
		if(popupReferenceModuleField == 'liveaccountid' && contact_recordid && child_contact_recordid){
			params['column1'] = 'contactid';
			params['value1'] =  child_contact_recordid;
			params['column2'] =  'record_status';
			params['value2'] =  'Approved';
			params['related_parent_module'] = 'Contacts';
		}
		if(popupReferenceModuleField == 'child_contactid' && contact_recordid){
			params['parent_contactid'] =  contact_recordid;
			params['related_parent_module'] = 'Contacts';
		}
		
		if(isMultiple) {
			params.multi_select = true ;
		}
		return params;
	},
	/*
	 * Function to get Field parent element
	 */
	getParentElement : function(element) {
		var parent = element.closest('td');
		// added to support from all views which may not be table format
		if(parent.length === 0) {
			parent = element.closest('.td').length ? 
			element.closest('.td') : element.closest('.fieldValue');
		}
		return parent;
	},
	/**
	 * Function to open popup list modal
	 */
	openPopUp : function(e,popupReferenceModule,popupReferenceModuleField) {
		var thisInstance = this;
		var parentElem = thisInstance.getParentElement(jQuery(e.target));

		var params = this.getPopUpParams(parentElem,popupReferenceModule,popupReferenceModuleField);
		//console.log(params);
		params.view = 'Popup';

		var isMultiple = false;
		if(params.multi_select) {
				isMultiple = true;
		}

		var sourceFieldElement = jQuery('input[class="sourceField"]',parentElem);

		var prePopupOpenEvent = jQuery.Event(Vtiger_Edit_Js.preReferencePopUpOpenEvent);
		sourceFieldElement.trigger(prePopupOpenEvent);

		if(prePopupOpenEvent.isDefaultPrevented()) {
				return ;
		}
		var popupInstance = Vtiger_Popup_Js.getInstance();

		app.event.off(Vtiger_Edit_Js.popupSelectionEvent);
		app.event.one(Vtiger_Edit_Js.popupSelectionEvent,function(e,data) {
			var responseData = JSON.parse(data);
			var dataList = new Array();
			jQuery.each(responseData, function(key, value){
				var counter = 0;
				for(var valuekey in value){
					if(valuekey == 'name') continue;
					if(typeof valuekey == 'object') continue;
//					var referenceModule = value[valuekey].module;
//					if(typeof referenceModule == "undefined") {
//						referenceModule = value.module;
//					}
//					if(parentElem.find('[name="popupReferenceModule"]').val() != referenceModule) continue;
//					
					var data = {
						'name' : value.name,
						'id' : key,
					}
					data['popupReferenceModuleField'] = popupReferenceModuleField;
					if(valuekey == 'info') {
						data['name'] = value.name;
					}
					dataList.push(data);
					if(!isMultiple && counter === 0) {
						counter++;
						thisInstance.setReferenceFieldValue(parentElem, data);
					}
				}
			});

			if(isMultiple) {
				sourceFieldElement.trigger(Vtiger_Edit_Js.refrenceMultiSelectionEvent,{'data':dataList});
			}
			sourceFieldElement.trigger(Vtiger_Edit_Js.postReferenceSelectionEvent,{'data':responseData});
		});
		popupInstance.showPopup(params,Vtiger_Edit_Js.popupSelectionEvent,function() {});
	},

	/*
	 * Function to set reference field value
	 */
	setReferenceFieldValue : function(container, params) {

		var sourceField = container.find('input.sourceField').attr('name');
		var fieldElement = container.find('input[name="'+sourceField+'"]');
		var sourceFieldDisplay = sourceField+"_display";
		var fieldDisplayElement = container.find('input[name="'+sourceFieldDisplay+'"]');
		var popupReferenceModuleElement = container.find('input[name="popupReferenceModule"]').length ? container.find('input[name="popupReferenceModule"]') : container.find('input.popupReferenceModule');
		var popupReferenceModule = popupReferenceModuleElement.val();
		var selectedName = params.name;
		var id = params.id;
		var popupReferenceModuleField = params.popupReferenceModuleField;
		
		if(popupReferenceModuleField == 'contactid'){
			jQuery('span#'+popupReferenceModuleField+'_display').text(selectedName);
			jQuery('input[name="contactid"]').val(id);
                        jQuery('span#child_contactid_display').text('');
                        jQuery('input[name="child_contactid"]').val('');
                        jQuery('span#liveaccountid_display').text('');
			jQuery('input[name="liveaccountid"]').val('');
                        jQuery('select[name="server_type[]"]').val('').trigger('change');
			jQuery('select[name="custom_commission_type[]"]').val('').trigger('change');
		} else if(popupReferenceModuleField == 'liveaccountid'){
			//jQuery('input[name="'+popupReferenceModuleField+'_display"]').val(selectedName);
			jQuery('span#'+popupReferenceModuleField+'_display').text(selectedName);
			jQuery('input[name="liveaccountid"]').val(id);
		} else if(popupReferenceModuleField == 'child_contactid'){
			//jQuery('input[name="'+popupReferenceModuleField+'_display"]').val(selectedName);
			jQuery('span#'+popupReferenceModuleField+'_display').text(selectedName);
			jQuery('input[name="child_contactid"]').val(id);
                        jQuery('span#liveaccountid_display').text('');
		}
		
		
		// if (id && selectedName) {
		// 	if(!fieldDisplayElement.length) {
		// 		fieldElement.attr('value',id);
		// 		fieldElement.data('value', id);
		// 		fieldElement.val(selectedName);
		// 	} else {
		// 		fieldElement.val(id);
		// 		fieldElement.data('value', id);
		// 		fieldDisplayElement.val(selectedName);
		// 		if(selectedName) {
		// 			fieldDisplayElement.attr('readonly', 'readonly');
		// 		} else {
		// 			fieldDisplayElement.removeAttr("readonly");
		// 		}
		// 	}

		// 	if(selectedName) {
		// 		fieldElement.parent().find('.clearReferenceSelection').removeClass('hide');
		// 		fieldElement.parent().find('.referencefield-wrapper').addClass('selected');
		// 	}else {
		// 		fieldElement.parent().find('.clearReferenceSelection').addClass('hide');
		// 		fieldElement.parent().find('.referencefield-wrapper').removeClass('selected');
		// 	}
		// 	fieldElement.trigger(Vtiger_Edit_Js.referenceSelectionEvent, {'source_module' : popupReferenceModule, 'record' : id, 'selectedName' : selectedName});
		// }
	},
	//end
	
	registerEvents : function(container){
		this.registerSaveOrGenerateReportEvent();
        this.registerEventsForActions();
		var container = this.getContentHolder();
		this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer',container));
        this.generateReportCount(parseInt(jQuery("#countValue").text()));
		this.registerConditionBlockChangeEvent();
		this.referenceModulePopupRegisterEvent(container); //add by divyesh
		this.registerEventForModifyCondition();
		this.registerEmailReportEvent();
	}
});