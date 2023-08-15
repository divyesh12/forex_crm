<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_ModuleBuilder_GetModuleBlocks_Action extends Settings_Vtiger_Index_Action {

      public function checkPermission(Vtiger_Request $request) {
            return;
      }

      public function process(Vtiger_Request $request) {
            global $adb;

            $module_name = $request->get('module_name');

            if ($module_name != '') {
                  $query = "SELECT * FROM vtiger_tab where name='" . $_REQUEST["module_name"] . "' ";
                  $result = $adb->query($query);
                  $tabid = $adb->query_result($result, 0, 'tabid');

                  $block_qry = "SELECT * FROM `vtiger_blocks` where tabid ='" . $tabid . "'  ";
                  $block_result = $adb->query($block_qry);
                  $numrows = $adb->num_rows($block_result);

                  if ($numrows > 0) {
                        $html = '<option value="">Select an Option</option>';
                        for ($i = 0; $i < $numrows; $i++) {
                              $blocklabel = $adb->query_result($block_result, $i, 'blocklabel');
                              $html .= '<option value="' . $blocklabel . '">' . vtranslate($blocklabel, $module_name) . '</option>';
                        }
                  } else {
                        $html .= '<option value="">block not available</option>';
                  }
            }
            $parent_ib_codeData = array($html);
            $response = new Vtiger_Response();
            $response->setResult($parent_ib_codeData);
            $response->emit();
      }

}

/* * *END** */
?>