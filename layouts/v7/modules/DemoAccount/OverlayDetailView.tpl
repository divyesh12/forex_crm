{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{foreach key=index item=jsModel from=$SCRIPTS}
    <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}

<input type="hidden" id="recordId" value="{$RECORD->getId()}"/>
{if $FIELDS_INFO neq null}
    <script type="text/javascript">
        var related_uimeta = (function() {
            var fieldInfo = {$FIELDS_INFO};
            return {
                field: {
                    get: function(name, property) {
                        if (name && property === undefined) {
                            return fieldInfo[name];
                        }
                        if (name && property) {
                            return fieldInfo[name][property]
                        }
                    },
                    isMandatory: function(name) {
                        if (fieldInfo[name]) {
                            return fieldInfo[name].mandatory;
                        }
                        return false;
                    },
                    getType: function(name) {
                        if (fieldInfo[name]) {
                            return fieldInfo[name].type
                        }
                        return false;
                    }
                },
            };
        })();
        jQuery(document).ready(function(){
{*            var metatraderType = jQuery('td#DemoAccount_detailView_fieldValue_metatrader_type span.value span').text();*}
            var metatraderType = jQuery('input[name="original_provider_type"]').val();
            if (metatraderType.trim().toLowerCase() == 'vertex') {
                jQuery('td#DemoAccount_detailView_fieldLabel_investor_password').hide();
                jQuery('td#DemoAccount_detailView_fieldValue_investor_password').hide();
                jQuery('td#DemoAccount_detailView_fieldLabel_leverage').hide();
                jQuery('td#DemoAccount_detailView_fieldValue_leverage').hide();
            }
        });
    </script>
{/if}

<div class='fc-overlay-modal overlayDetail'>
    <div class = "modal-content">
        <div class="overlayDetailHeader col-lg-12 col-md-12 col-sm-12" style="z-index:1;">
            <div class="col-lg-10 col-md-10 col-sm-10" style = "padding-left:0px;">
                <input type="hidden" name="custom_module_name" value="{$MODULE_NAME}"/>
                {include file="DetailViewHeaderTitle.tpl"|vtemplate_path:$MODULE_NAME MODULE_MODEL=$MODULE_MODEL RECORD=$RECORD}
            </div>
            <div class = "col-lg-2 col-md-2 col-sm-2">
                <div class="clearfix">
                    <div class = "btn-group">
                        <button class="btn btn-default fullDetailsButton" onclick="window.location.href = '{$RECORD->getFullDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}'">{vtranslate('LBL_DETAILS',$MODULE_NAME)}</button>
						{foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
							{if $DETAIL_VIEW_BASIC_LINK && $DETAIL_VIEW_BASIC_LINK->getLabel() == 'LBL_EDIT'}
								<button class="btn btn-default editRelatedRecord" value = "{$RECORD->getEditViewUrl()}">{vtranslate('LBL_EDIT',$MODULE_NAME)}</button>
							{/if}
						{/foreach}
                    </div> 
                    <div class="pull-right " >
                        <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                            <span aria-hidden="true" class='fa fa-close'></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class='modal-body'>
            <div class = "detailViewContainer">      
                {include file='DetailViewFullContents.tpl'|@vtemplate_path:$MODULE_NAME RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
            </div>
        </div>
    </div>
</div>