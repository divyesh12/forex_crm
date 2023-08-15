<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_ModuleBuilder_CheckTableExists_Action extends Settings_Vtiger_Index_Action {

      public function checkPermission(Vtiger_Request $request) {
            return;
      }

      public function process(Vtiger_Request $request) {
            global $adb, $dbconfig;
            $db_name = $dbconfig['db_name'];
            $field_name = $request->get('field_name');
            if ($field_name != '') {
                  $tablename = 'vtiger_' . $field_name;
                  $query = "SELECT * FROM information_schema.tables WHERE table_schema = '" . $db_name . "'  AND table_name = '" . $tablename . "'";
                  $result = $adb->query($query);
                  $numrows = $adb->num_rows($result);

                  if ($numrows > 0) {
                        $num_rows = 1;
                  } else {
                        $num_rows = 0;
                  }
            }
            $parent_ib_codeData = array($num_rows);
            $response = new Vtiger_Response();
            $response->setResult($parent_ib_codeData);
            $response->emit();
      }

}

/* * *END** */
?>