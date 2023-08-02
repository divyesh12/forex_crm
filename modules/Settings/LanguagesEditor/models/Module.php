<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_LanguagesEditor_Module_Model extends Vtiger_Module_Model {

    public function getLanguages() {
        global $adb;
        $sql = "SELECT * FROM `vtiger_language` ORDER BY name";
        $result = $adb->pquery($sql, array());
        $num_rows = $adb->num_rows($result); // vtiger count row
        $languages_list_result = array();
        while ($row_result = $adb->fetchByAssoc($result)) {
            $label = $row_result['label'];
            $prefix = $row_result['prefix'];
            $languages_list_result[$prefix] = $label;
        }
        return $languages_list_result;
    }

}
