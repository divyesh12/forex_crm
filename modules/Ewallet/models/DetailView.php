<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Ewallet_DetailView_Model extends Vtiger_DetailView_Model {

    public function getDetailViewLinks($linkParams) {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $linkModelList = Vtiger_DetailView_Model::getDetailViewLinks($linkParams);
        $recordModel = $this->getRecord();
        $data = $recordModel->getData();
        $moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();
        $recordId = $data['id'];
        /**
         * @add by:- Reena Hingol
         * @Date:- 25-11-2019
         * @comment:- Remove more button and its option:-delete and duplicate link and Edit Button in Detail page.
         */
        unset($linkModelList['DETAILVIEWBASIC'][0], $linkModelList['DETAILVIEW'][0], $linkModelList['DETAILVIEW'][1]);
        /* END */
        return $linkModelList;
    }

}
