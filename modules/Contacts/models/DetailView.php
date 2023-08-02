<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

//Same as Accounts Detail View
class Contacts_DetailView_Model extends Accounts_DetailView_Model {

    /**
     * @creator: Divyesh Chothani
     * @date: 18-12-2016
     * @comment: Change Poratal Password  Button
     */
    public function getDetailViewLinks($linkParams) {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $linkModelList = Vtiger_DetailView_Model::getDetailViewLinks($linkParams);
        $recordModel = $this->getRecord();
        $modelData = $recordModel->getData();
        $moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();
        $recordId = $modelData['id'];

        /*if IB parent affiliate code exist then add Parent Tree button next to edit button*/
        if (!empty($modelData['parent_affiliate_code'])) {
            $encRecordId = base64_encode($recordId);
            $url = "window.open('ib_parent_hierarchy.php?record_id=$encRecordId', '_blank')";
            $basicActionLink = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => vtranslate('LBL_IB_PARENT_TREE', $moduleName),
                'linkurl' => $url,
                'linkicon' => ''
            );
            $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }
        
        if ($modelData['record_status'] == 'Approved') {
            $url = "window.open('Orgchart/ib_hierarchy.php?recordId=$recordId', '_blank')";
            $basicActionLink = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => vtranslate('LBL_IB_GRAPH', $moduleName),
                'linkurl' => $url,
                'linkicon' => ''
            );
            $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }
        //if ($modelData['is_login_varified'] && $modelData['portal']) {
        if ($modelData['portal']) {
            $isPortalPasswordEditable = configvar('is_portal_password_editable');
            if ($isPortalPasswordEditable)
            {
                $basicActionLink = array(
                    'linktype' => 'DETAILVIEWBASIC',
                    'linklabel' => vtranslate('LBL_CHANGE_PORTAL_PASSWORD', $moduleName),
                    'linkurl' => "Javascript:Contacts_Detail_Js.triggerPortalChangePassword('index.php?module=" . $moduleName . "&view=ChanagePortalPassword&mode=changePortalPassword&recordId=$recordId','" . $moduleName . "')",
                    'linkicon' => ''
                );
                $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
            }
            /* END */
        
        /* Button that used to resend portal password to customer */
            $encRecordId = base64_encode($recordId);
            $url = "Javascript:Contacts_Detail_Js.resendPortalPassword()";
            $basicActionLink = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => vtranslate('LBL_RESEND_PORTAL_PASSWORD', $moduleName),
                'linkurl' => $url,
                'linkicon' => ''
            );
            $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }


        return $linkModelList;
    }

    /*  END */
}
