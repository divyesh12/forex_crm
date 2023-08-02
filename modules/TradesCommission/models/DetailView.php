<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class TradesCommission_DetailView_Model extends Vtiger_DetailView_Model {

    /**
     * @creator: Reena Hingol
     * @date: 05_03_2020
     * @comment: remove edit button and delete(From more button) and duplicate link 
     */
    public function getDetailViewLinks($linkParams) {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $linkModelList = Vtiger_DetailView_Model::getDetailViewLinks($linkParams);
        $recordModel = $this->getRecord();
        $data = $recordModel->getData();
        $moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();
        $recordId = $data['id'];
        /* @Comment:-remove Delete option from more Button and more button in Detail page */
        $linkModelList = parent::getDetailViewLinks($linkParams);
//        unset($linkModelList['DETAILVIEW'][0], $linkModelList['DETAILVIEW'][1]);
        unset($linkModelList['DETAILVIEW'][0], $linkModelList['DETAILVIEW'][1]);

        /* @comment: remove Edit button from detail page */
//            if ($data['transaction_status'] == "Disapproved" || $data['transaction_status'] == "Approved") {
        unset($linkModelList['DETAILVIEWBASIC']);
//            }
        return $linkModelList;
    }

}

/* END */
