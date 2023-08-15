{strip}
      <div class="col-lg-12 col-md-12 col-sm-12">
            <div>
                  <h3 style="margin-top: 0px;">Add Field</h3>
            </div>
            <form method="POST" action="" id="field_creation">
                  <div class="blockData">
                        <br>
                        {if $smarty.get.field_exists eq 1}
                              <div class="successMessage">
                                    <div class="alert alert-danger"><strong>Failed : </strong>Field Name already exists</div>
                              </div>
                              <br>
                        {/if}
                        {if $smarty.get.add_field eq 1}
                              <div class="successMessage">
                                    <div class="alert alert-success"><strong>Success : </strong>Field Created Successfully</div>
                              </div>
                              <br>
                        {/if}
                        <div class="block">
                              <hr>
                              <table class="table editview-table no-border">
                                    <tbody>

                                          <tr id="module_name" class="module_name">
                                                <td class=" fieldLabel"><label>Module Name</label>&nbsp;<span class="redColor">*</span></td>
                                                <td class=" fieldValue">
                                                      <div class=" col-lg-6 col-md-6 col-sm-12">
                                                            <select name="module_name" required="" class="col-sm-12">
                                                                  <option value=''>{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
                                                                  {foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
                                                                        <option value="{$MODULE_NAME}" >
                                                                              {* Calendar needs to be shown as TODO so we are translating using Layout editor specific translations*}
                                                                              {if $MODULE_NAME eq 'Calendar'}
                                                                                    {vtranslate($MODULE_NAME, $QUALIFIED_MODULE)}
                                                                              {else}
                                                                                    {vtranslate($MODULE_NAME, $MODULE_NAME)}
                                                                              {/if}
                                                                        </option>
                                                                  {/foreach}
                                                            </select>
                                                            {*<input type="text" value=""  name="module_name" class="inputElement" required="">*}
                                                      </div>
                                                </td>
                                          </tr>
                                          <tr  class="block_name">
                                                <td class=" fieldLabel"><label>Block Name</label>&nbsp;<span class="redColor">*</span></td>
                                                <td class=" fieldValue">
                                                      <div class=" col-lg-6 col-md-6 col-sm-12">
                                                            <select name="block_name" id="block_name" class="col-sm-12" required="">
                                                                  <option value="">Select Module Name first</option>
                                                            </select>
                                                            {*<input type="text" value=""  name="block_name" class="inputElement" required="">*}
                                                      </div>
                                                </td>
                                          </tr>
                                          <tr id="field_ui_type">
                                                <td class=" fieldLabel"><label>Field Type</label>&nbsp;<span class="redColor">*</span></td>
                                                <td class=" fieldValue">
                                                      <div class=" col-lg-6 col-md-6 col-sm-12">
                                                            <select name="field_ui_type" required="" class="col-sm-12">
                                                                  <option value="">Select an Option</option>
                                                                  <option value="Text_1">Text</option>
                                                                  <option value="Decimal_7">Decimal</option>
                                                                  <option value="Integer_7">Integer</option>
                                                                  <option value="Percent_9">Percent(%)</option>
                                                                  <option value="Currency_71">Currency</option>
                                                                  <option value="Date_5">Date</option>
                                                                  <option value="Email_13" >Email</option>
                                                                  <option value="Phone_11">Phone</option>
                                                                  <option value="PickList_15">Pick List</option>
                                                                  <option value="URL_17">URL</option>
                                                                  <option value="Checkbox_56">Checkbox</option>
                                                                  <option value="TextArea_21">Text Area</option>
                                                                  <option value="MultiSelectComboBox_33">Multi-Select Combo Box</option>
                                                                  <option value="SkyPeID_85">SkyPe ID</option>
                                                                  <option value="Time_14">Time</option>
                                                                  <option value="RelationModule_10">Relation Module</option>
                                                            </select>
                                                      </div>
                                                </td>
                                          </tr>
                                          <tr id="relatedmodule_name">
                                                <td class=" fieldLabel"><label>Related Module Name</label>&nbsp;<span class="redColor">*</span></td>
                                                <td class=" fieldValue">
                                                      <div class=" col-lg-6 col-md-6 col-sm-12">
                                                            <input type="text" value="" name="relatedmodule_name" class="inputElement" required="">
                                                      </div>
                                                </td>
                                          </tr>
                                          <tr id="label_name">
                                                <td class=" fieldLabel"><label>Label Name</label>&nbsp;<span class="redColor">*</span></td>
                                                <td class=" fieldValue">
                                                      <div class=" col-lg-6 col-md-6 col-sm-12">
                                                            <input type="text" value="" name="label_name" class="inputElement" required="">
                                                      </div>
                                                      <div class=" col-lg-6 col-md-6 col-sm-12">
                                                            <div class="alert alert-info alert-mini">(eg. LBL_FIELDNAME)</div>
                                                      </div>
                                                </td>
                                          </tr>
                                          <tr id="field_name">
                                                <td class=" fieldLabel"><label>Field Name</label>&nbsp;<span class="redColor">*</span></td>
                                                <td class=" fieldValue">
                                                      <div class=" col-lg-6 col-md-6 col-sm-12">
                                                            <input type="text" value="" name="field_name" class="inputElement" required="">
                                                      </div>
                                                      <div class=" col-lg-6 col-md-6 col-sm-12">
                                                            <div class="alert alert-info alert-mini">(note! if field type picklist then compalsary add prefix module  eg. modu_fieldname)</div>
                                                      </div>
                                                </td>
                                          </tr>
                                          <tr id="picklist_values">
                                                <td class=" fieldLabel"><label>PickList Value</label>&nbsp;<span class="redColor">*</span></td>
                                                <td class=" fieldValue">
                                                      <div class=" col-lg-6 col-md-6 col-sm-12">
                                                            <textarea type="text" value=""  name="picklist_values" class="inputElement" required="" ></textarea>
                                                            {*<input type="text" value=""  name="picklist_values" class="inputElement" required="">*}
                                                      </div>
                                                      <div class=" col-lg-6 col-md-6 col-sm-12">
                                                            <div class="alert alert-info alert-mini">(eg. a,b,c,d,e)</div>
                                                      </div>
                                                </td>
                                          </tr>
                                    </tbody>
                              </table>
                        </div>
                        <br>
                        <div class="modal-overlay-footer clearfix">
                              <div class="row clearfix">
                                    <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12 ">
                                          <button class="btn btn-success saveButton" type="submit" name="button_submit" value="save_field">Save</button>
                                          &nbsp;&nbsp;<a href="#" data-dismiss="modal" class="cancelLink">Cancel</a>
                                    </div>
                              </div>
                        </div>
                  </div>
            </form>
      </div>
     {* <script type="text/javascript">
            {literal}
                  jQuery(document).ready(function () {

                        jQuery('#picklist_values').hide();
                        jQuery('textarea[name="picklist_values"]').attr('disabled', 'disabled');

                        jQuery('#relatedmodule_name').hide();
                        jQuery('input[name="relatedmodule_name"]').attr('disabled', 'disabled');
                        jQuery('select[name="field_ui_type"]').on('change', function () {
                              var field_ui_type = this.value;
                              if (field_ui_type == 'PickList_16' || field_ui_type == 'MultiSelectComboBox_33') {
                                    jQuery('#picklist_values').show();
                                    jQuery('textarea[name="picklist_values"]').removeAttr('disabled', 'disabled');
                              } else {
                                    jQuery('textarea[name="picklist_values"]').attr('disabled', 'disabled');
                                    jQuery('#picklist_values').hide();
                              }
                              if (field_ui_type == 'RelationModule_10') {
                                    jQuery('#relatedmodule_name').show();
                                    jQuery('input[name="relatedmodule_name"]').removeAttr('disabled', 'disabled');
                              } else {
                                    jQuery('input[name="relatedmodule_name"]').attr('disabled', 'disabled');
                                    jQuery('#relatedmodule_name').hide();
                              }
                        })
                        jQuery("#field_creation").on("submit", function () {
                              var field_name = jQuery('input[name="field_name"]').val();
                              var field_name_regex = new RegExp("^[a-z_\s]+$");
                              if (!field_name_regex.test(field_name)) {
                                    jQuery('input[name="field_name"]').val('');
                                    alert("Please enter only small letter with underscore in Field Name.");
                                    return false;
                              } else {
                                    return true;
                              }
                        });

                        jQuery('select[name="module_name"]').on('change', function () {
                              var module_name = jQuery('select[name="module_name"]').val();
                              $.ajax({
                                    url: 'modules/Settings/ModuleBuilder/actions/CheckFieldExists.php',
                                    type: 'POST',
                                    data: 'module_name=' + module_name,
                                    success: function (data) {
                                          jQuery('#block_name').html(data);
                                    }
                              });
                        })
                  });
            {/literal}
      </script>*}
{/strip}