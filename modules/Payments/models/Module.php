<?php

class Payments_Module_Model extends Vtiger_Module_Model {

    public function isCommentEnabled() {
        return true;
    }

    /**
     * @creator: Divyesh Chothani
     * @date: 11-03-2019
     * @comment: remove editing from listing page
     */
    public function isExcelEditAllowed() {
        return false;
    }

    /**
      @Add_by:-Reena Hingol
      @Date:-25_11_19
      @Comment:-edit and delete link from Payments listing page when payment_status is Completed and payment_process is Finish.
     */
    public function checkRecordStatus($record) {
        if ($record) {
            $module = $this->getModule();
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
            $modelData = $recordModel->getData();
            if (($modelData['payment_status'] == "Completed") || ($modelData['payment_status'] == "Rejected") || ($modelData['payment_status'] == "Cancelled")) {
                return false;
            }
        }
        return true;
    }

    /**
     * @creator: Divyesh Chothani
     * @comment:  return provider form html
     * @date: 17-10-2019
     * */
    function getHtml($module, $getRequiredParams) {
        $html = '<tr class="payment_provider_fields">';
        $COUNTER = 0;
        foreach ($getRequiredParams as $key => $value) {
            if ($COUNTER == 2) {
                $html .= '</tr><tr class="payment_provider_fields">';
                $COUNTER = 1;
            } else {
                $COUNTER = $COUNTER + 1;
            }

            //$required = $value['required'];
            $requiredHtmlSpan = '<span class="redColor">*</span>';
            if ($value['type'] == 'email' || $value['type'] == 'text' || $value['type'] == 'number') {
                $html .= '<td  id="fieldLabel_' . $value['name'] . '" class="fieldLabel alignMiddle ' . $value['name'] . '">
                        ' . vtranslate($value['label'], $module) . '&nbsp;' . $requiredHtmlSpan . '
                    </td>
                   <td id="fieldValue_' . $value['name'] . '" class="fieldValue ' . $value['name'] . '">
                        <input id="Payments_editView_fieldName_' . $value['name'] . '" type="' . $value['type'] . '" data-fieldname="' . $value['name'] . '" data-fieldtype="string" class="inputElement nameField" name="' . $value['name'] . '" value="" data-rule-required="true" aria-required="true">
                  </td>';
            }
            if ($value['type'] == 'dropdown') {
                $html .= '<td  id="fieldLabel_' . $value['name'] . '" class="fieldLabel alignMiddle ' . $value['name'] . '">
                        ' . vtranslate($value['label'], $module) . '&nbsp;' . $requiredHtmlSpan . '
                    </td>
                  <td id="fieldValue_' . $value['name'] . '" class="fieldValue ' . $value['name'] . '">
                        <select id="field_' . $value['name'] . '" data-fieldname="' . $value['name'] . '" data-fieldtype="picklist" class="inputElement select2" type="picklist" name="' . $value['name'] . '" data-selected-value="" data-rule-required="true" title="" tabindex="-1" aria-required="true" aria-invalid="false">
                            <option value="">Select an Option</option>';
                foreach ($value['picklist'] as $key => $picklistValue) {
                    $html .= '<option value="' . $picklistValue['value'] . '">' . vtranslate($picklistValue['label'], $module) . '</option>';
                }
                $html .= '</select>
                  </td>';
            }
            if ($COUNTER == 1) {
                $html .= '<td></td><td></td>';
            }
        }
        $html .= '</tr>';
        return $html;
    }

    /* end */

  /**
  * Function to check if duplicate option is allowed in DetailView
  * @param <string> $action, $recordId 
  * @return <boolean> 
  */
  public function isDuplicateOptionAllowed($action, $recordId) {
    return false;
  }
}

?>