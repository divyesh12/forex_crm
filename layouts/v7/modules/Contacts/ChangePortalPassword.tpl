{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{** new file
* Add by:- Divyesh Chothani
* Date:- 18-12-2019
* Comment:- Change Portal Passowrd
*}
{strip}
    <div id="massEditContainer" class="modal-dialog modelContainer">
        <div class="modal-header">
            <div class="clearfix">
                <div class="pull-right ">
                    <button type="button" class="close" aria-label="Close" data-dismiss="modal"><span aria-hidden="true" class="fa fa-close"></span></button>
                </div>
                <h4 class="pull-left">{vtranslate('LBL_CHANGE_PORTAL_PASSWORD', $MODULE)}</h4></div>
        </div>
        <div class="modal-content">
            <form class="form-horizontal" id="changePortalPassword" name="changePortalPassword" method="post" action="index.php" novalidate="novalidate">
                <input type="hidden" name="module" value="{$MODULE}">
                <input type="hidden" name="record" value="{$RECORD}">
                <input  type="hidden" name="action" value="SavePortalPassword">
                <input  type="hidden" name="mode" value="savePortalPassword">
                <div name="massEditContent">
                    <div class="modal-body ">
                        <div class="form-group"></div>
                        <div class="form-group">
                            <label class="control-label fieldLabel col-sm-5">{vtranslate('LBL_PASSWORD', $MODULE)}<span class="redColor">*</span></label>
                            <div class="controls col-xs-6">
                                <input type="password" class="form-control inputElement" name="new_password" data-rule-required="true" autofocus="autofocus" aria-required="true" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label fieldLabel col-sm-5">{vtranslate('LBL_CONFIRM_PASSWORD', $MODULE)}<span class="redColor">*</span></label>
                            <div class="controls col-xs-6">
                                <input type="password" class="form-control inputElement" name="confirm_password" data-rule-required="true" aria-required="true"  value="">
                            </div>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}
