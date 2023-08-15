{strip}
      <div class="col-lg-12 col-md-12 col-sm-12">
            <div>
                  <h3 style="margin-top: 0px;">Module Creation</h3>
            </div>
            <form method="POST" action="" id="module_creation">
                  <div class="blockData">
                        <br>

                        {if $smarty.get.module_status eq 1}
                              <div class="successMessage">
                                    <div class="alert alert-success"><strong>Success : </strong>Module has been Created Successfully</div>
                              </div>
                              <br>
                        {/if}

                        {if $smarty.get.is_module_exists eq 1}
                              <div class="infoMessage">
                                    <div class="alert alert-info"><strong>Info : </strong>Module already present - choose a different name.</div>
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
                                                            <input type="text" value=""  name="module_name" class="inputElement" required="">
                                                      </div>
                                                      <div class=" col-lg-6 col-md-6 col-sm-12">
                                                            <div class="alert alert-info alert-mini">(eg. ModuleName or Modulename)</div>
                                                      </div>
                                                </td>
                                          </tr>
                                          <tr id="parent_module">
                                                <td class=" fieldLabel"><label>Parent</label>&nbsp;<span class="redColor">*</span></td>
                                                <td class=" fieldValue">
                                                      <div class="col-lg-6 col-md-6 col-sm-12">
                                                            <select name="parent_module" required="" class="col-sm-12">
                                                                  <option value="">Select an Option</option>
                                                                  <option value="MARKETING">MARKETING</option>
                                                                  <option value="SALES">SALES</option>
                                                                  <option value="SUPPORT">SUPPORT</option>
                                                                  <option value="INVENTORY">INVENTORY</option>
                                                                  <option value="PROJECT">PROJECT</option>
                                                                  <option value="TOOLS">TOOLS</option>
                                                            </select>
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
                                                            <div class="alert alert-info alert-mini">(eg. modu_fieldname)</div>
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
                                          <button class="btn btn-success saveButton" type="submit" name="button_submit" value="module_creation">Save</button>
                                          &nbsp;&nbsp;<a href="#" data-dismiss="modal" class="cancelLink">Cancel</a>
                                    </div>
                              </div>
                        </div>
                  </div>
            </form>
      </div>
      {*<script type="text/javascript">
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
                        jQuery("#module_creation").on("submit", function () {
                              var module_name = jQuery('input[name="module_name"]').val();
                              var module_name_regex = new RegExp("^[a-zA-Z\s]+$");
                              var field_name = jQuery('input[name="field_name"]').val();
                              var field_name_regex = new RegExp("^[a-z_\s]+$");
                              if (!module_name_regex.test(module_name)) {
                                    jQuery('input[name="module_name"]').val('');
                                    alert("Please enter only letter in Module Name.");
                                    return false;
                              } else if (!field_name_regex.test(field_name)) {
                                    jQuery('input[name="field_name"]').val('');
                                    alert("Please enter only small letter with underscore in Field Name.");
                                    return false;
                              } else {
                                    return true;
                              }
                        });
                  });
            {/literal}
      </script>*}
{/strip}