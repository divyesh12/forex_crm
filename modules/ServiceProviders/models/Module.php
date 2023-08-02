<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

//add by divyesh
class ServiceProviders_Module_Model extends Vtiger_Module_Model {

    /**
     * Function to check whether the module is an entity type module or not
     * @return <Boolean> true/false
     */
    public function isQuickCreateSupported() {
        //ServiceProviders module is not enabled for quick create
        return false;
    }

    /**
     * Function to check whether the module is summary view supported
     * @return <Boolean> - true/false
     */
    public function isSummaryViewSupported() {
        return false;
    }

    /**
     * Function to get the module is permitted to specific action
     * @param <String> $actionName
     * @return <boolean>
     */
    public function isPermitted($actionName) {
        if ($actionName === 'EditView' || $actionName === 'CreateView') {
            return false;
        }
        return Users_Privileges_Model::isPermitted($this->getName(), $actionName);
    }

    /**
     * Function to get Settings links
     * @return <Array>
     */
    public function getSettingLinks() {
        vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');

        $editWorkflowsImagePath = Vtiger_Theme::getImagePath('EditWorkflows.png');
        $settingsLinks = array();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if ($currentUserModel->isAdminUser()) {
            if (VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
                $settingsLinks[] = array(
                    'linktype' => 'LISTVIEWSETTING',
                    'linklabel' => 'LBL_EDIT_WORKFLOWS',
                    'linkurl' => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule=' . $this->getName(),
                    'linkicon' => $editWorkflowsImagePath
                );
            }

            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_SERVER_CONFIG', $this->getName()),
                'linkurl' => 'index.php?module=ServiceProviders&parent=Settings&view=List',
                'linkicon' => ''
            );
        }
        return $settingsLinks;
    }

    /**
     * Function to check if duplicate option is allowed in DetailView
     * @param <string> $action, $recordId 
     * @return <boolean> 
     */
    public function isDuplicateOptionAllowed($action, $recordId) {
        return false;
    }

    /**
     * Function is used to give links in the All menu bar
     */
    public function getQuickMenuModels() {
        return;
    }

    /*
     * Function to get supported utility actions for a module
     */

    function getUtilityActionsNames() {
        return array();
    }

    public function getModuleBasicLinks() {
        return array();
    }

    function isStarredEnabled() {
        return false;
    }

    function isTagsEnabled() {
        return false;
    }

    /**
     * @creator: Divyesh Chothani
     * @comment:  return provider form html
     * @date: 17-10-2019
     * */
    function getHtml($getRequiredParams) {
        $html = '<tr class="server_provider_fields">';
        $COUNTER = 0;
        foreach ($getRequiredParams as $key => $value) {
            if ($COUNTER == 2) {
                $html .= '</tr><tr class="server_provider_fields">';
                $COUNTER = 1;
            } else {
                $COUNTER = $COUNTER + 1;
            }
            if ($value['type'] == 'text' || $value['type'] == 'password') {
                $html .= '<td class="fieldLabel col-lg-2">
                        <label class="muted pull-right">' . $value['label'] . '&nbsp;<span class="redColor">*</span> </label>
                    </td>
                  <td class="fieldValue col-lg-4">
                        <input id="OrganizationServers_editView_fieldName_' . $value['name'] . '" type="' . $value['type'] . '" data-fieldname="' . $value['name'] . '" data-fieldtype="string" class="inputElement nameField" name="' . $value['name'] . '" value="" data-rule-required="true" aria-required="true">
                  </td>';
            }
            if ($value['type'] == 'picklist') {
                $html .= '<td class="fieldLabel col-lg-2">
                        <label class="muted pull-right">' . $value['label'] . '&nbsp;<span class="redColor">*</span> </label>
                    </td>
                  <td class="fieldValue col-lg-4">
                        <select id="' . $value['name'] . '" data-fieldname="' . $value['name'] . '" data-fieldtype="' . $value['type'] . '" class="' . $value['name'] . ' inputElement pickliststyle" type="' . $value['type'] . '" name="' . $value['name'] . '" data-selected-value="" data-rule-required="true" title="" tabindex="-1" aria-required="true" aria-invalid="false">
                            <option value="">Select an Option</option>';
                foreach ($value['picklistvalues'] as $picklistValue) {
                    $html .= '<option value="' . $picklistValue . '">' . $picklistValue . '</option>';
                }
                $html .= '</select>
                  </td>';
            }
        }
        $html .= '</tr>';
        return $html;
    }

    /* end */
}

?>
