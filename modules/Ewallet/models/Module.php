<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Ewallet_Module_Model extends Vtiger_Module_Model {

    /**
     * @creator: Divyesh Chothani
     * @date: 02-02-2019
     * @comment: Enabled Comment Widget
     */
    public function isCommentEnabled() {
        return true;
    }

    /**
      @Add_by:-Reena Hingol
      @Date:-25_11_19
      @Comment:-edit and delete link from Ewallet listing page.
     */
    public function checkRecordStatus($record) {
        if (!empty($record)) {
            $module = $this->getModule();
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
            $modelData = $recordModel->getData();
//            if ($modelData['record_status'] == "Disapproved") {
//                return false;
//            }
            return false;
        }
    }

    /**
      @Add_by:-Reena Hingol
      @Date:-25_11_19
      @Comment:-remove editing from listing page.
     */
    public function isExcelEditAllowed() {
        return false;
    }

    /** Add By:- Divyesh Chothani
     * Date:- 27-12-2019
     * Comment:- Remove Add Record Button and Import Button from wallet listing header
     */
    public function getModuleBasicLinks() {
        return array();
    }

    /* END */
}
