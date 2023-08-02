<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Payments_DetailView_Model extends Vtiger_DetailView_Model {

    public function getDetailViewLinks($linkParams) {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $linkModelList = Vtiger_DetailView_Model::getDetailViewLinks($linkParams);

        $moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();
        $recordModel = $this->getRecord();
        $recordData = $recordModel->getData();
        $recordId = $recordData['id'];
        /**
         * @add by:- Reena Hingol
         * @Date:- 26-11-2019
         * @comment:- Remove more button and its option:-delete and duplicate link and Edit Button in Detail page.
         */
        if ($recordId) {
            if (($recordData['payment_status'] == "Completed") || ($recordData['payment_status'] == "Rejected") || ($recordData['payment_status'] == "Cancelled")) {
                $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED') . ' because of ' . $moduleName . ' Status are ' . $recordData['payment_status'];
                unset($linkModelList['DETAILVIEWBASIC'][0], $linkModelList['DETAILVIEW'][0], $linkModelList['DETAILVIEW'][1]);
            }

//            if ($recordData['payment_operation'] == "Deposit") {
//                if ($recordData['payment_type'] == "P2A") {
//                    if (($recordData['payment_status'] == "Completed" && $recordData['payment_process'] == "Finish") || ($recordData['payment_status'] == "Failed" && $recordData['payment_process'] == "PSP") || ($recordData['payment_status'] == "Rejected" && $recordData['payment_process'] == "PSP")) {
//                        unset($linkModelList['DETAILVIEWBASIC'][0], $linkModelList['DETAILVIEW'][0], $linkModelList['DETAILVIEW'][1]);
//                    }
//                } else if ($recordData['payment_type'] == "P2E") {
//                    if (($recordData['payment_status'] == "Completed" && $recordData['payment_process'] == "Finish") || ($recordData['payment_status'] == "Failed" && $recordData['payment_process'] == "PSP") || ($recordData['payment_status'] == "Rejected" && $recordData['payment_process'] == "PSP")) {
//                        unset($linkModelList['DETAILVIEWBASIC'][0], $linkModelList['DETAILVIEW'][0], $linkModelList['DETAILVIEW'][1]);
//                    }
//                }
//            } else if ($recordData['payment_operation'] == "Withdrawal") {
//                if ($recordData['payment_type'] == "A2P") {
//                    if (($recordData['payment_status'] == "Completed" && $recordData['payment_process'] == "Finish") || ($recordData['payment_status'] == "Rejected" && $recordData['payment_process'] == "Account")) {
//                        unset($linkModelList['DETAILVIEWBASIC'][0], $linkModelList['DETAILVIEW'][0], $linkModelList['DETAILVIEW'][1]);
//                    }
//                } else if ($recordData['payment_type'] == "E2P") {
//                    if (($recordData['payment_status'] == "Completed" && $recordData['payment_process'] == "Finish") || ($recordData['payment_status'] == "Rejected" && $recordData['payment_process'] == "Wallet")) {
//                        unset($linkModelList['DETAILVIEWBASIC'][0], $linkModelList['DETAILVIEW'][0], $linkModelList['DETAILVIEW'][1]);
//                    }
//                }
//            } else if ($recordData['payment_operation'] == "InternalTransfer") {
//                if ($recordData['payment_type'] == "E2E") {
//                    if (($recordData['payment_status'] == "Completed" && $recordData['payment_process'] == "Finish") || ($recordData['payment_status'] == "Rejected" && $recordData['payment_process'] == "Wallet Withdrawal")) {
//                        unset($linkModelList['DETAILVIEWBASIC'][0], $linkModelList['DETAILVIEW'][0], $linkModelList['DETAILVIEW'][1]);
//                    }
//                } else if ($recordData['payment_type'] == "A2A") {
//                    if (($recordData['payment_status'] == "Completed" && $recordData['payment_process'] == "Finish") || ($recordData['payment_status'] == "Rejected" && $recordData['payment_process'] == "Account Withdrawal")) {
//                        unset($linkModelList['DETAILVIEWBASIC'][0], $linkModelList['DETAILVIEW'][0], $linkModelList['DETAILVIEW'][1]);
//                    }
//                }
//            }
        }
        return $linkModelList;
    }

    /*  END */
}
