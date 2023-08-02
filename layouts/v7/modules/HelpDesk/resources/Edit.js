/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/*Added By :- Reena
 * Date:- 17-12-2019
 * Comment:- field validation
 * */
Vtiger_Edit_Js("HelpDesk_Edit_Js", {

}, {
     registorLoadEvent: function(container) {
          var recordid = jQuery('input[name="record"]').val();
//           if (recordid == '') {
// //            jQuery('textarea[name="solution"]').attr('readonly', true);
//                container.find('[name="solution"]').parents('td').addClass('fieldValue').css('pointer-events', 'none');
//           }

          jQuery('select[name="ticketstatus"]').on('change', function (e) {
            var ticketstatus = jQuery(this).val();
            if (ticketstatus == 'Closed') {
               jQuery('#HelpDesk_editView_fieldName_solution').removeClass('ignore-validation').data('rule-required', true);
            } else {
               jQuery('#HelpDesk_editView_fieldName_solution').removeAttr('aria-required');
               jQuery('#HelpDesk_editView_fieldName_solution').removeClass('input-error');
               jQuery('#HelpDesk_editView_fieldName_solution').addClass('ignore-validation').removeAttr('data-rule-required');
            }
        });
     },

     registerBasicEvents: function(container) {
          this._super(container);
          this.registorLoadEvent(container);
     }
});