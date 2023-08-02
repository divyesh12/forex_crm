<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class LeverageHistory_Record_Model extends Vtiger_Record_Model {

    /**
     * @creator: Divyesh Chothani
     * @date: 14-10-2019
     * @comment: edit and delete link from liveaccount deposit summary listing page
     */
    public function checkRecordStatus($record) {
        if (!empty($record)) {
            $module = $this->getModule();
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
            $modelData = $recordModel->getData();
            if ($modelData['record_status'] == "Disapproved" || $modelData['record_status'] == "Approved" || $modelData['record_status'] == "Cancelled") {
                return false;
            }
        }
        return true;
    }

    public function checkPendingRequestExist($contactid, $liveaccountid) {
        global $adb;
        $sql = 'SELECT count(vtiger_leveragehistory.leveragehistoryid) as total_count FROM `vtiger_leveragehistory` INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leveragehistory.leveragehistoryid WHERE vtiger_crmentity.deleted = 0 AND vtiger_leveragehistory.`liveaccountid` =? AND vtiger_leveragehistory.`contactid` =? AND vtiger_leveragehistory.`record_status` = ?';
        $result = $adb->pquery($sql, array($liveaccountid, $contactid, 'Pending'));
        $row_result = $adb->fetchByAssoc($result);
        $count = $row_result['total_count'];
        return $count;
    }

}
