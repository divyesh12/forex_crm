/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("Faq_Edit_Js",{},{
	
	/**
	 * Function to register event for ckeditor for description field
	 */
	registerEventForCkEditor : function(){
		var templateContentElement = jQuery("#Faq_editView_fieldName_faq_answer");
		if(templateContentElement.length > 0) {
			if(jQuery('#EditView').find('.isSystemTemplate').val() == 1) {
				templateContentElement.removeAttr('data-validation-engine').addClass('ckEditorSource');
			}
            var customConfig = {
                "height":"300px"
            }
			var ckEditorInstance = new Vtiger_CkEditor_Js();
			ckEditorInstance.loadCkEditor(templateContentElement,customConfig);
		}
        this.registerFillTemplateContentEvent();
		
	},
	registerFillTemplateContentEvent : function() {
		var thisInstance = this;
		//  CKEDITOR.instances.templatecontent.on('blur', function(){
		// 	 jQuery('#templateFields,#generalFields').off('change');
		// 	 jQuery('#templateFields,#generalFields').on('change',function(e){
		// 		var mergeTag = jQuery(e.currentTarget).val();
		// 		var textarea = CKEDITOR.instances.templatecontent;
		// 		textarea.insertHtml(mergeTag);
		// 	 });
		//  });
		 jQuery('.recordEditView').on('blur','#EmailTemplates_editView_fieldName_subject',function(){
			 jQuery('#templateFields,#generalFields').off('change');
			 jQuery('#templateFields,#generalFields').on('change',function(e){
				var mergeTag = jQuery(e.currentTarget).val();
				thisInstance.insertValueAtCursorPosition();
				jQuery('#EmailTemplates_editView_fieldName_subject').insertAtCaret(mergeTag);
			});
		});
	},
    
	insertValueAtCursorPosition: function() {
		$.fn.extend({
			insertAtCaret: function(myValue) {
				var obj;
				if (typeof this[0].name !== 'undefined'){
					obj = this[0];
                } else {
					obj = this;
                }
                
                // $.browser got deprecated from jQuery 1.9
                // Inorder to know browsername, we are depending on useragent
                var browserInfo  = navigator.userAgent.toLowerCase();
				if (browserInfo.indexOf('msie') !== -1) {
					obj.focus();
					sel = document.selection.createRange();
					sel.text = myValue;
					obj.focus();
				} else if (browserInfo.indexOf('mozilla') !== -1 || browserInfo.indexOf('webkit')!==-1) {
					var startPos = obj.selectionStart;
					var endPos = obj.selectionEnd;
					var scrollTop = obj.scrollTop;
					obj.value = obj.value.substring(0, startPos) + myValue + obj.value.substring(endPos, obj.value.length);
					obj.focus();
					obj.selectionStart = startPos + myValue.length;
					obj.selectionEnd = startPos + myValue.length;
					obj.scrollTop = scrollTop;
				} else {
					obj.value += myValue;
					obj.focus();
				}
			}
		});
	},
	
/**
	 * Registered the events for this page
	 */
	registerEvents : function() {
		this.registerEventForCkEditor();
		this._super();
	}
});
