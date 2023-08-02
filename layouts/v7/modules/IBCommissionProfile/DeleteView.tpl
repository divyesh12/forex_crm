{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="modal-dialog">
        <div class='modal-content'>
            {assign var=HEADER_TITLE value={vtranslate('LBL_DELETE_IB_PROFILE_ITEMS', $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="record_id" value="{$RECORDID}" />
                <div class="modal-body tabbable">
                    <div class="row">
                        <div class="col-sm-3 col-xs-3"><label style="font-size: 14px;">{vtranslate('LBL_REPLACE_IT_WITH',$MODULE)}</label></div>
                        <div class="col-sm-4 col-xs-4">
                            <select class="select2 inputElement" name="replace_profileid">
                                {foreach key=PROFILE_ID item=PROFILE_NAME from=$PROFILE_LIST}
                                    <option value="{$PROFILE_ID}">{$PROFILE_NAME}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="row m-t-10">
                        <div class="col-sm-12 col-xs-6">
                        <p><b>Note: </b>{vtranslate('LBL_NOTE_FOR_PROFILE_DELETE', $MODULE)}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <center>
                        <button class="btn btn-danger deleteButton" ><strong>{vtranslate('LBL_DELETE', $MODULE)}</strong></button>
                        <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </center>
                </div>
        </div>
    </div>
{/strip}
<script type="text/javascript">
    jQuery('.deleteButton').on('click', function (e) {
        var elem = jQuery(e.currentTarget);
        var recordId = jQuery('input[name="record_id"]').val();
        var params = {};
        params['module'] = 'IBCommissionProfile';
        params['ib_profile_id'] = jQuery('select[name="replace_profileid"] option:selected').val();
        var thisInstance = new IBCommissionProfile_List_Js();
        thisInstance._deleteRecord(recordId, params);
    }); 
</script>
