<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class LiveAccount_DetailView_Model extends Vtiger_DetailView_Model {

    /**
     * @creator: Divyesh Chothani
     * @date: 10-11-2016
     * @comment: Add Password and Leverage Button.
     */
    public function getDetailViewLinks($linkParams) {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $linkModelList = Vtiger_DetailView_Model::getDetailViewLinks($linkParams);
        $recordModel = $this->getRecord();
        $modelData = $recordModel->getData();
        $moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();
        $recordId = $modelData['id'];
        $provider = ServiceProvidersManager::getInstanceByProvider($modelData['live_metatrader_type']);
        $isInvestorButtonActive = $provider->isInvestorButtonActive();
        // echo "<pre>"; print_r($linkModelList); exit;
        //Edit Button  and delete and duplicate link  remove this this condition
        
        if ($modelData['record_status'] == "Disapproved") {
            unset($linkModelList['DETAILVIEWBASIC']);
        } elseif ($modelData['record_status'] == "Approved") {
            unset($linkModelList['DETAILVIEW']);
        }


        if ($modelData['record_status'] == "Approved") {
            if (Users_Privileges_Model::isPermitted($moduleModel->getName(), 'PasswordLeverage', $recordModel->getId()) && Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView', $recordModel->getId())) {
                $basicActionLink = array(
                    'linktype' => 'DETAILVIEWBASIC',
                    'linklabel' => vtranslate('LBL_CHANGE_PASSWORD', $moduleName),
                    'linkurl' => "Javascript:LiveAccount_Detail_Js.triggerChangePassword('index.php?module=" . $moduleName . "&view=PasswordLeverage&mode=changePassword&recordId=$recordId','" . $moduleName . "')",
                    'linkicon' => ''
                );
                $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
            }

            if (Users_Privileges_Model::isPermitted($moduleModel->getName(), 'PasswordLeverage', $recordModel->getId()) && Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView', $recordModel->getId()) && $isInvestorButtonActive) {
                $basicActionLink = array(
                    'linktype' => 'DETAILVIEWBASIC',
                    // 'linklabel' => 'LBL_Change  ' . $modelData['metatrader_type'] . ' LBL_Investor_Password',
                    'linklabel' => vtranslate('LBL_CHANGE_INVESTOR_PASSWORD', $moduleName),
                    'linkurl' => "Javascript:LiveAccount_Detail_Js.triggerChangePassword('index.php?module=" . $moduleName . "&view=PasswordLeverage&mode=changeInvestorPassword&recordId=$recordId','" . $moduleName . "')",
                    'linkicon' => ''
                );
                $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
            }

            if (Users_Privileges_Model::isPermitted($moduleModel->getName(), 'PasswordLeverage', $recordModel->getId()) && Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView', $recordModel->getId())) {
                $basicActionLink = array(
                    'linktype' => 'DETAILVIEWBASIC',
                    // 'linklabel' => 'LBL_Change  ' . $modelData['metatrader_type'] . ' LBL_Investor_Password',
                    'linklabel' => vtranslate('LBL_RESEND_TRADING_PASSWORD', $moduleName),
                    'linkurl' => "Javascript:LiveAccount_Detail_Js.resendTradingPassword('index.php?module=" . $moduleName . "&view=PasswordLeverage&mode=changeInvestorPassword&recordId=$recordId','" . $moduleName . "')",
                    'linkicon' => ''
                );
                $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
            }
            /* END */
        }
        return $linkModelList;
    }

    /*  END */
}
