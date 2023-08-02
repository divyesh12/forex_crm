<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class LeverageHistory_DetailView_Model extends Vtiger_DetailView_Model {

    /**
     * @creator: Divyesh Chothani
     * @date: 14-10-2019
     * @comment: Add Password and Leverage Button. 
     */
    public function getDetailViewLinks($linkParams) {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $linkModelList = Vtiger_DetailView_Model::getDetailViewLinks($linkParams);
        $recordModel = $this->getRecord();
        $data = $recordModel->getData();
        $moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();
        $recordId = $data['id'];

        //Edit Button  and delete and duplicate link  remove this this condition
        if ($data['record_status'] == "Disapproved" || $data['record_status'] == "Approved" || $data['record_status'] == "Cancelled") {
            unset($linkModelList['DETAILVIEWBASIC'], $linkModelList['DETAILVIEW']);
        }
        return $linkModelList;
    }

    /*  END */
}
