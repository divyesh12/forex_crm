<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/** add file by Reena 06_03_2020 */
class TradesCommission_Record_Model extends Vtiger_Record_Model {
    /* comment:- edit and delete link from summary listing page */

    public function checkRecordStatus($record) {
        if (!empty($record)) {
            $module = $this->getModule();
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
            $modelData = $recordModel->getData();
        }
        return false;
    }

}
