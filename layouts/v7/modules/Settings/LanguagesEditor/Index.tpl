{strip}
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="clearfix">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                <h3 style="margin-top: 0px;">{vtranslate('LBL_LANGUAGES_EDITOR', 'Settings:LanguagesEditor')}</h3>
            </div>
            <!-- {if $MODEVIEW eq 'Detail'}
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                    <div class="btn-group pull-right">
                        <button class="btn btn-default editButton"type="button" title="Edit"><a href="index.php?module=LanguagesEditor&parent=Settings&view=Index&modeview=Edit&block=8&fieldid=40">{vtranslate('LBL_EDIT', 'Settings:LanguagesEditor')}</a></button>
                    </div>
                </div>
            {/if} -->
        </div>
        <style>
            div.tooltip {
                margin-left: 100px;
            }
            /* Chrome, Safari, Edge, Opera */
            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
                padding: 3px 8px;
            }
            /* Firefox */
            input[type=number] {
                -moz-appearance:textfield;
                padding: 3px 8px;
            }
        </style>

        <div class="blockData">
            <br>
            <form method="GET" action="" name="searchModuleFiles">
                <input type="hidden" name="module" value="LanguagesEditor">
                <input type="hidden" name="parent" value="Settings">
                <input type="hidden" name="view" value="Index">
                <input type="hidden" name="modeview" value="Detail">
                <div class="listViewPageDiv detailViewContainer " id="listViewContent">
                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8 form-horizontal">
                        <br>
                        <div class="detailViewInfo">
                            <div class="row form-group">
                                <div class="col-lg-3 col-md-3 col-sm-3 control-label fieldLabel"><label class="fieldLabel"><strong>{vtranslate('LBL_SELECT_LANGUAGE', 'Settings:LanguagesEditor')}</strong></label></div>
                                <div class="fieldValue col-sm-6 col-xs-6">
                                    <select class="select2 inputElement select2-offscreen"  name="languageFolderName" tabindex="-1" title="">
                                        {foreach from=$LANGUAGES key=LANGUAGE_PREFIX item=LANGUAGE_NAME}
                                            {assign var=SELECTED_LANGUAGE value=""}
                                            {if $CURRENT_LANGUAGE  eq  $LANGUAGE_PREFIX}
                                                {assign var=SELECTED_LANGUAGE value="selected=selected"}
                                            {else if $SELECTED_LANG_FOLDER_NAME eq  $LANGUAGE_PREFIX}
                                                {assign var=SELECTED_LANGUAGE value="selected=selected"}
                                            {/if}
                                            <option value="{$LANGUAGE_PREFIX}" {$SELECTED_LANGUAGE}>{$LANGUAGE_NAME}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="modulePickListContainer">
                            <div class="row form-group">
                                <div class="col-lg-3 col-md-3 col-sm-3 control-label fieldLabel"><label class="fieldLabel"><strong>{vtranslate('LBL_SELECT_MODULE', 'Settings:LanguagesEditor')}</strong></label></div>
                                <div class="col-sm-6 col-xs-6 fieldValue">
                                    <select class="select2 inputElement select2-offscreen"  name="languageFileName" tabindex="-1" title="">
                                        {foreach from=$FILE_LIST key=MODULE_NAME item=languageFileName}
                                            {assign var=JS_SELECTED_LANGUAGE value=""}
                                            {if $SELECTED_LANG_FILE_NAME  eq  $MODULE_NAME}
                                                {assign var=JS_SELECTED_LANGUAGE value="selected=selected"}
                                            {/if}
                                            <option value="{$MODULE_NAME}" {$JS_SELECTED_LANGUAGE}>{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <br>
                        </div>
                        <br>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 form-horizontal" style="margin-top: 25px;">
                        <button class="btn btn-success saveButton" type="submit" name="button_submit" value="saveFormData">
                            {vtranslate('LBL_SEARCH', 'Settings:LanguagesEditor')}
                        </button>
                    </div>
                </div>
            </form>

            {if count($LANGUAGE_STRING) > 0}
                <div class="floatThead-wrapper">
                    <form method="POST" action="" name="EditFieldLabels">
                        <table id="listview-table" class="table listview-table" style="boder:1">
                            <thead>
                                <tr class="size-row">
                                    <th>{vtranslate('LBL_FIELD_LABEL', 'Settings:LanguagesEditor')}</th>
                                    <th>{vtranslate('LBL_FIELD_VALUE', 'Settings:LanguagesEditor')}</th>
                                </tr>
                            </thead>
                            <tbody>  
                                {foreach from=$LANGUAGE_STRING  key=FIELD_LABLE item=FIELD_VALUE}
                                    <tr class="listViewEntries">
                                        <td>{$FIELD_LABLE}</td>
                                        <td><input type="text" class="inputElement" name="{$FIELD_LABLE}" value="{$FIELD_VALUE}"></td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                        <br>
                        <div class="row clearfix">
                            <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12 ">
                                <button class="btn btn-success saveButton" type="submit" name="SubmitFieldLabel" value="saveFieldLabel">{vtranslate('LBL_Update_Field_Labels', 'Settings:LanguagesEditor')}</button>
                            </div>
                        </div>
                    </form>
                </div>
            {/if}

            <br>
            <br>
            {if count($JS_LANGUAGE_STRING) > 0}
                <div class="floatThead-wrapper">
                    <form method="POST" action="" name="EditJQeryLabels">
                        <table id="listview-table" class="table listview-table" style="boder:1">
                            <thead>
                                <tr class="size-row">
                                    <th>{vtranslate('LBL_Jquery_Label', 'Settings:LanguagesEditor')}</th>
                                    <th>{vtranslate('LBL_Jquery_Label_Value', 'Settings:LanguagesEditor')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$JS_LANGUAGE_STRING  key=JS_LABLE item=JS_VALUE}
                                    <tr class="listViewEntries">
                                        <td>{$JS_LABLE}</td>
                                        <td><input type="text" class="inputElement" name="{$JS_LABLE}" value="{$JS_VALUE}"></td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                        <br>
                        <div class="row clearfix">
                            <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12 ">
                                <button class="btn btn-success saveButton" type="submit" name="SubmitJqueryLabel" value="saveJqueryLabel">{vtranslate('LBL_Update_Jquery_Labels', 'Settings:LanguagesEditor')}</button>
                            </div>
                        </div>
                    </form>
                </div>
            {/if}
            <br>
        </div>
    </div>
{/strip}