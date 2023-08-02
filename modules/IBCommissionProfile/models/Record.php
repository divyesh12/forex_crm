<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class IBCommissionProfile_Record_Model extends Vtiger_Record_Model {

    function getIBCommCombinationData($recordId) {
        global $adb;

        $query = "SELECT  vtiger_ibcommissionprofileitems.*
                FROM  `vtiger_ibcommissionprofileitems` INNER JOIN  vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ibcommissionprofileitems.ibcommissionprofileitemsid WHERE  vtiger_crmentity.deleted = 0 AND vtiger_ibcommissionprofileitems.ibcommissionprofileid = ? ORDER BY (0 + ibcommission_level) ASC";
        $result = $adb->pquery($query, array($recordId));
        $num_rows = $adb->num_rows($result);
        $row_result = array();
        if ($num_rows > 0) {
            while ($row = $adb->fetchByAssoc($result)) {
                $concat_item_values = $row['live_metatrader_type'] . "-" . $row['ibcommission_level'] . "-" . $row['security'] . "-" . $row['symbol'] . "-" . $row['live_label_account_type'] . "-" . $row['live_currency_code'];
                $row['concat_item_values'] = str_replace(' ', '', $concat_item_values);
                $row_result[] = $row;
            }
        }
        return $row_result;
    }
    
    /**
     * This function is used to get profile name list
     * @global obj $adb
     * @param string $recordId
     * @return array $ibCommProfileData
     */
    function getIBCommProfileList($recordId = '')
    {
        global $adb;
        $query = "SELECT vtiger_ibcommissionprofile.profile_name, vtiger_ibcommissionprofile.ibcommissionprofileid
                FROM `vtiger_ibcommissionprofile` 
                INNER JOIN  vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ibcommissionprofile.ibcommissionprofileid
                WHERE vtiger_crmentity.deleted = 0 ORDER BY vtiger_ibcommissionprofile.profile_name";
        $result = $adb->pquery($query, array());
        $num_rows = $adb->num_rows($result);
        $ibCommProfileData = array();
        if ($num_rows > 0)
        {
            while ($row = $adb->fetchByAssoc($result))
            {
                $ibCommProfileData[$row['ibcommissionprofileid']] = $row['profile_name'];
            }
            if(!empty($recordId))
            {
                unset($ibCommProfileData[$recordId]);
            }
        }
        return $ibCommProfileData;
    }

}
