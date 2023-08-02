{strip}
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="clearfix">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><h3 style="margin-top: 0px;">{vtranslate('LBL_MODULE_CONFIGURATION_EDITOR', 'Settings:ModuleConfigurationEditor')}</h3>
            </div>
            {if $MODEVIEW eq 'Detail'}
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><div class="btn-group pull-right">
                        <button class="btn btn-default editButton"type="button" title="Edit"><a href="index.php?module=ModuleConfigurationEditor&parent=Settings&view=Index&modeview=Edit&block=8&fieldid=40">{vtranslate('LBL_EDIT', 'Settings:ModuleConfigurationEditor')}</a></button>
                    </div>
                </div>
            {/if}

        </div>

        {*<div>
        <h3 style="margin-top: 0px;"></h3>
        </div>*}
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
        <form method="POST" action="" name="module_configrution_editor" id="module_configrution_editor" enctype="multipart/form-data">
            <div class="blockData">
                <br>

                <div name="editContent">
                    {$FORM_HTML}
                    <br>
                    <br>
                    <br>
                </div>
                <br>
                {if $MODEVIEW eq 'Edit'}
                    <div class="modal-overlay-footer clearfix">
                        <div class="row clearfix">
                            <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12 ">
                                <button class="btn btn-success saveButton" type="submit" name="button_submit" value="saveFormData">{vtranslate('LBL_SAVE', 'Settings:ModuleConfigurationEditor')}</button>
                                &nbsp;&nbsp;<a href="#" data-dismiss="modal" class="cancelLink">{vtranslate('LBL_CANCLE', 'Settings:ModuleConfigurationEditor')}</a>
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
        </form>
    </div>
{/strip}