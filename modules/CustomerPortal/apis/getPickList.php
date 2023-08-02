<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_getPickList extends CustomerPortal_API_Abstract {

    function process(CustomerPortal_API_Request $request) {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();

        if ($current_user) {
            $picklist_name = $request->get('picklist_name');
            if ($picklist_name == 'language') {    
                $languages = [];
                $sql = 'SELECT `prefix`, `label` FROM `vtiger_language` WHERE `active` = ? ORDER BY `sequence` ASC';
                $res = $adb->pquery($sql, array('1'));
                $num_rows = $adb->num_rows($res);
                if ($num_rows > 0) {
                    for ($i=0; $i < $num_rows; $i++) {
                        $languagesPrefix = $adb->query_result($res, $i, 'prefix');
                        $languages[$languagesPrefix] = $adb->query_result($res, $i, 'label');
                    }
                }
                $response->addToResult($picklist_name, $languages);
            } else {
                $response = CustomerPortal_Utils::getPicklist($picklist_name);
            }
        }
        return $response;
    }

}
