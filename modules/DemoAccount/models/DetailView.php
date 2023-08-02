<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class DemoAccount_DetailView_Model extends Vtiger_DetailView_Model {

    /**
     * Function to get the detail view links (links and widgets)
     * @param <array> $linkParams - parameters which will be used to calicaulate the params
     * @return <array> - array of link models in the format as below
     * @array('linktype'=>list of link models);
     */
    public function getDetailViewLinks($linkParams) {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        $linkModelList = parent::getDetailViewLinks($linkParams);
        $recordModel = $this->getRecord();

        /* Add By Divyesh - 13-09-2019
         * Edit Button ,delete and duplicate link  remove this this condition
         */
        if ($recordModel->get('account_no')) {
            unset($linkModelList['DETAILVIEW']);
        }
        return $linkModelList;
    }

}
