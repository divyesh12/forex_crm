<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/** add by Divyesh 01-03-2019
 * Ewallet Entity Record Model Class
 */
class Ewallet_Record_Model extends Vtiger_Record_Model {

    /**
     * @Add by :- Reena 25-11-2019
     * @comment:- edit and delete link from Ewallet deposit summary listing page
     */
    public function checkRecordStatus($record) {
        if (!empty($record)) {
            $module = $this->getModule();
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
            $modelData = $recordModel->getData();
//            if ($modelData['record_status'] == "Disapproved") {
//                return false;
//            }
        }
        return false;
    }

    /* END */
}
