<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_ModuleConfigurationEditor_Module_Model extends Vtiger_Module_Model {

    /**
     * Function that returns all those modules that support Module Sequence Numbering
     * @global PearDatabase $db - database connector
     * @return <Array of Vtiger_Module_Model>
     */
    public static function getFieldHTML($fieldlabel, $fieldname, $fieldtype, $fieldvalue, $view) {
        global $maxNotificationAllow;
        $module = 'Settings:ModuleConfigurationEditor';
        $html = '';
        $blockList = Settings_ModuleConfigurationEditor_Module_Model::getBlockList();
        foreach ($blockList as $block_key => $block_value) {
            $html .= '<div class="fieldBlockContainer">
                        <h4 class="fieldBlockHeader">' . $block_value['module'] . ' ' . vtranslate('LBL_CONFIGURATION', $module) . '</h4>
                        <hr>
                        <table class="table table-borderless">
                            <tbody>';

            $fieldsList = Settings_ModuleConfigurationEditor_Module_Model::getFieldList($block_value['tabid'], $block_value['module']);
            $count = 1;
            foreach ($fieldsList as $field_key => $field_value) {
                if ($count % 2 == 1) {
                    $html .= '<tr>';
                }

                if ($field_value['fieldtype'] == 'picklist') {
                    $array = array('true' => 'Yes', 'false' => 'No');
                    $html .= '<td class="fieldLabel alignMiddle" style="width:30%;">' . vtranslate($field_value['fieldlabel'], $module) . '&nbsp;<i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="' . vtranslate($field_value['fieldsuggestion'], $module) . '"></i></td>';
                    if ($view == 'Detail') {
                        $html .= '<td class="fieldValue" style="width:20%;z-index:0;">' . $array[$field_value['fieldvalue']] . '</td>';
                    } elseif ($view == 'Edit') {

                        $html .= '<td class="fieldValue" style="width:20%;z-index:0;">
                                        <select data-fieldname="' . $field_value['fieldname'] . '" data-fieldtype="picklist" class="inputElement select2  select2-offscreen" type="picklist" name="' . $field_value['fieldname'] . '"  data-selected-value="" data-rule-required="true" tabindex="-1" title="" aria-required="true">';
                        foreach ($array as $key => $value) {
                            $selected = '';
                            if ($field_value['fieldvalue'] == $key) {
                                $selected = 'selected';
                            }
                            $html .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
                        }
                        $html .= '</select></td>';
                    }
                } elseif ($field_value['fieldtype'] == 'custom_picklist') {
                    $array = array('auto_generation' => 'Auto Generation', 'common_series' => 'Common Series', 'group_series' => 'Group Series');
                    $html .= '<td class="fieldLabel alignMiddle" style="width:30%;">' . vtranslate($field_value['fieldlabel'], $module) . '&nbsp;<i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="' . vtranslate($field_value['fieldsuggestion'], $module) . '"></i></td>';
                    if ($view == 'Detail') {
                        $html .= '<td class="fieldValue" style="width:20%;z-index:0;">' . $array[$field_value['fieldvalue']] . '</td>';
                    } elseif ($view == 'Edit') {

                        $html .= '<td class="fieldValue" style="width:20%;z-index:0;">
                                        <select data-fieldname="' . $field_value['fieldname'] . '" data-fieldtype="picklist" class="inputElement select2  select2-offscreen" type="picklist" name="' . $field_value['fieldname'] . '"  data-selected-value="" data-rule-required="true" tabindex="-1" title="" aria-required="true">';
                        foreach ($array as $key => $value) {
                            $selected = '';
                            if ($field_value['fieldvalue'] == $key) {
                                $selected = 'selected';
                            }
                            $html .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
                        }
                        $html .= '</select></td>';
                    }
                } elseif ($field_value['fieldtype'] == 'checkbox') {
                    $html .= '<td class="fieldLabel alignMiddle" style="width:30%;">' . vtranslate($field_value['fieldlabel'], $module) . '&nbsp;<i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="' . vtranslate($field_value['fieldsuggestion'], $module) . '"></i></td>';
                    if ($view == 'Detail') {
                        $value = 'No';
                        if ($field_value['fieldvalue']) {
                            $value = 'Yes';
                        }
                        $html .= '<td class="fieldValue" style="width:20%;z-index:0;">' . $value . '</td>';
                    } elseif ($view == 'Edit') {
                        if ($field_value['fieldvalue']) {
                            $checked = 'checked=""';
                        }
                        $html .= '<td class="fieldValue" style="width: 20%;"><input type="hidden" name="' . $field_value['fieldname'] . '" value="' . $field_value['fieldvalue'] . '"><input  class="inputElement" style="width:15px;height:15px;" data-fieldname="' . $field_value['fieldname'] . '" data-fieldtype="checkbox" type="checkbox" name="' . $field_value['fieldname'] . '" ' . $checked . '></td>';
                    }
                } elseif ($field_value['fieldtype'] == 'file') {
                    $type = $field_value['fieldtype'];
                    $html .= '<td class="fieldLabel alignMiddle" style="width:30%;">' . vtranslate($field_value['fieldlabel'], $module) . '&nbsp;<i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="' . vtranslate($field_value['fieldsuggestion'], $module) . '"></i></td>';
                    
                    if ($view == 'Detail') {
                        if (!empty($field_value['fieldvalue'])) {
                            $imgHtml = '<img src="' . $field_value['fieldvalue'] . '" class="module_config_img" width="150" alt="' . vtranslate($field_value['fieldlabel'], $module) . '">';
                        } else {
                            $imgHtml = 'Image not found';
                        }
                        $html .= '<td class="fieldValue" style="width:20%;z-index:0;">' . $imgHtml . '</td>';
                    } elseif ($view == 'Edit') {
                        $viewImage = '';
                        if (!empty($field_value['fieldvalue'])) {
                            $viewImage = '<img src="' . $field_value['fieldvalue'] . '" class="module_config_img" width="100" height="60" alt="' . vtranslate($field_value['fieldlabel'], $module) . '" style="margin-bottom:10px;">';
                        }
                        $html .= '<td class="fieldValue" style="width: 20%;">' . $viewImage . '<input accept="image/png, image/jpeg" type="' . $type . '" name="' . $field_value['fieldname'] . '" id="' . $field_value['fieldname'] . '" value="' . $field_value['fieldvalue'] . '"  class="fileElement"></td>';
                    }
                } else {
                    $type = $field_value['fieldtype'];
                    $mandatoryString = ' data-rule-required="true" aria-required="true" ';
                    if($type === 'textbox~O')
                    {
                        $mandatoryString = '';
                        $type = 'textbox';
                    }
                    
                    $html .= '<td class="fieldLabel alignMiddle" style="width:30%;">' . vtranslate($field_value['fieldlabel'], $module) . '&nbsp; <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="' . vtranslate($field_value['fieldsuggestion'], $module) . '"></i></td>';
                    if ($view == 'Detail') {
                        $html .= '<td class="fieldValue" style="width:20%;z-index:0;">' . $field_value['fieldvalue'] . '</td>';
                    } elseif ($view == 'Edit') {
                        $min = '';
                        if ($type == 'number') {
                            $min = 'min="0"';
                        }
                        if ($field_value['fieldname'] == 'max_crm_notifications') {
                            $min .= ' max="'.$maxNotificationAllow.'"';
                        }
                        $html .= '<td class="fieldValue" style="width:20%;"><input  type="' . $type . '" data-fieldname="' . $field_value['fieldname'] . '" data-fieldtype="string" class="inputElement " name="' . $field_value['fieldname'] . '" value="' . $field_value['fieldvalue'] . '" '. $mandatoryString . $min . '></td>';
                    }
                }

                if ($count % 2 == 0) {
                    $html .= '</tr>';
                }
                $count++;
            }
            if ($count % 2 != 1) {
                $html .= '</tr>';
            }

            $html .= '</tbody>
                        </table>
                    </div>';
        }
        return $html;
    }

    public function getBlockList() {
        global $adb;
        $sql = "SELECT tabid,module FROM  `vtiger_module_configuration_editor` GROUP BY tabid ORDER BY tabid ASC  ";
        $result = $adb->pquery($sql, array());
        $num_rows = $adb->num_rows($result); // vtiger count row
        $blocks_rows = array();
        while ($row_result = $adb->fetchByAssoc($result)) {
            $blocks_rows[] = array('tabid' => $row_result['tabid'], 'module' => $row_result['module']);
        }
        return $blocks_rows;
    }

    public static function getFieldList($tabid, $module) {
        global $adb;
        $sql = "SELECT * FROM  `vtiger_module_configuration_editor` WHERE tabid=? AND module=? AND presence =? ORDER BY sequence ASC  ";
        $result = $adb->pquery($sql, array($tabid, $module, 0));
        $num_rows = $adb->num_rows($result); // vtiger count row
        $fields_rows = array();
        while ($row_result = $adb->fetchByAssoc($result)) {
            $fields_rows[] = $row_result;
        }
        return $fields_rows;
    }

    public function updateConfigurations($request) {
        global $adb, $site_URL;
        
        $storageFolder = vglobal('root_directory'). 'test/';
        if (!is_dir($storageFolder)) {
            mkdir($storageFolder);
        }
        chmod($storageFolder, 0777);
        
        $imageLinkPath = $site_URL . 'test/banner_qr_code/';
        $uploadDir = vglobal('root_directory'). 'test/banner_qr_code/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir);
        }
        chmod($uploadDir, 0777);

        if (isset($_FILES["cabinet_header_banner_path"]) && $_FILES["cabinet_header_banner_path"]["name"] != '') { 
            $headerExtension = explode(".", $_FILES["cabinet_header_banner_path"]["name"]);
            $headerFileName = 'header_banner_' . time() . '.' . $headerExtension[1];
            $headerName = $uploadDir . $headerFileName;
            move_uploaded_file($_FILES["cabinet_header_banner_path"]["tmp_name"], $headerName);
            chmod($headerName, 0777);
            $request['cabinet_header_banner_path'] = $imageLinkPath . $headerFileName;
        }

        if (isset($_FILES["cabinet_footer_banner_path"]) && $_FILES["cabinet_footer_banner_path"]["name"] != '') { 
            $footerExtension = explode(".", $_FILES["cabinet_footer_banner_path"]["name"]);
            $footerFileName = 'footer_banner_' . time() . '.' . $footerExtension[1];
            $footerName = $uploadDir . $footerFileName;
            move_uploaded_file($_FILES["cabinet_footer_banner_path"]["tmp_name"], $footerName);
            chmod($footerName, 0777);
            $request['cabinet_footer_banner_path'] = $imageLinkPath . $footerFileName;
        }

        if (isset($_FILES["mobile_header_banner_path"]) && $_FILES["mobile_header_banner_path"]["name"] != '') { 
            $mobileHeaderExtension = explode(".", $_FILES["mobile_header_banner_path"]["name"]);
            $mobileHeaderFileName = 'mobile_header_banner_' . time() . '.' . $mobileHeaderExtension[1];
            $mobileHeaderName = $uploadDir . $mobileHeaderFileName;
            move_uploaded_file($_FILES["mobile_header_banner_path"]["tmp_name"], $mobileHeaderName);
            chmod($mobileHeaderName, 0777);
            $request['mobile_header_banner_path'] = $imageLinkPath . $mobileHeaderFileName;
        }

        unset($request['module'], $request['parent'], $request['view'], $request['block'], $request['fieldid'], $request['__vtrftk'], $request['button_submit']);

        $ibcommission_parent_profile = filter_var($request['ibcommission_parent_profile'], FILTER_VALIDATE_BOOLEAN);
        if ($ibcommission_parent_profile) {
            $presence = 2;
        } else {
            $presence = 1;
        }
        $updatequery = "UPDATE `vtiger_field` SET `presence`= ? WHERE `tabid` =? AND `columnname` = ?";
        $adb->pquery($updatequery, array($presence, 4, 'parent_ibcommissionprofileid'));

        foreach ($request as $fieldname => $fieldvalue) {
            $sql = 'UPDATE `vtiger_module_configuration_editor` SET `fieldvalue`=? WHERE fieldname =? AND presence =? ';
            $result = $adb->pquery($sql, array($fieldvalue, $fieldname, 0));
        }
    }

}
