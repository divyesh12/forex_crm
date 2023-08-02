<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_Detail_View extends Accounts_Detail_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('showDetailViewByMode');
    }

    function preProcess(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $recordId = $request->get('record');
        $supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();
        $moduleList = array_keys($supportedModulesList);
        $viewer->assign('WALLET_BALANCE_WIDGET', false);
        $ticketUrl = "index.php?module=HelpDesk&view=Edit&returnmode=showRelatedList&returntab_label=HelpDesk&returnrecord=".$recordId."&returnmodule=Contacts&returnview=Detail&returnrelatedModuleName=HelpDesk&returnrelationId=21&contact_id=".$recordId."&app=MARKETING";
        $viewer->assign('TICKET_URL', $ticketUrl);
        if (in_array('Ewallet', $moduleList)) {
            $viewer->assign('WALLET_BALANCE_WIDGET', true);
            $walletBalance = getEwalletBalance($recordId);
            $viewer->assign('WALLET_BALANCE_DATA', $walletBalance);
        }
        parent::preProcess($request);
    }

    public function showModuleDetailView(Vtiger_Request $request) {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        $is_allow_max_ib_comm = configvar('is_max_ib_comm_dist_allow');
        // Getting model to reuse it in parent 
        if (!$this->record) {
            $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
        }
        $recordModel = $this->record->getRecord();
        $viewer = $this->getViewer($request);
        $viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());
        $viewer->assign('IS_ALLOW_MAX_IB_COMM', $is_allow_max_ib_comm);
        
        return parent::showModuleDetailView($request);
    }

    /**
     * Function to get Ajax is enabled or not
     * @param Vtiger_Record_Model record model
     * @return <boolean> true/false
     */
    public function isAjaxEnabled($recordModel) {
        return false;
    }
    
    function showDetailViewByMode($request) {
            $requestMode = $request->get('requestMode');
            if($requestMode == 'full') {
                    return $this->showModuleDetailView($request);
            }
            return $this->showModuleBasicView($request);
    }
    
    /**
    * Function shows basic detail for the record
    * @param <type> $request
    */
   function showModuleBasicView($request) {

            $recordId = $request->get('record');
            $moduleName = $request->getModule();

            $paymentSummaryData = Contacts_Record_Model::getPaymentSummaryWidgetData($recordId);
           
            if(!$this->record){
                    $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
            }
            $recordModel = $this->record->getRecord();

            $detailViewLinkParams = array('MODULE'=>$moduleName,'RECORD'=>$recordId);
            $detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);

            $viewer = $this->getViewer($request);
            $viewer->assign('RECORD', $recordModel);
            $viewer->assign('MODULE_SUMMARY', $this->showModuleSummaryView($request));

            $viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
            $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
            $viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
            $viewer->assign('MODULE_NAME', $moduleName);

            $recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
            $structuredValues = $recordStrucure->getStructure();

            $moduleModel = $recordModel->getModule();
            $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
            $viewer->assign('RECORD_STRUCTURE', $structuredValues);
            $viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
           
            $supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();
            $moduleList = array_keys($supportedModulesList);
            $viewer->assign('WALLET_BALANCE_WIDGET', false);
            if (in_array('Ewallet', $moduleList)) {
                $viewer->assign('WALLET_BALANCE_WIDGET', true);
                $walletBalance = getEwalletBalance($recordId);
                $viewer->assign('WALLET_BALANCE_DATA', $walletBalance);
            }
            $ibStatus = $recordModel->get('record_status');
            $res = getPickListValues('live_currency_code', 'H2');
            $viewer->assign('CURRENCY_CODES', $res);
            $viewer->assign('IB_SUMMARY_WIDGET', false);
            $viewer->assign('PAYMENT_SUMMARY_DATA', $paymentSummaryData);
            $viewer->assign('TOP_5_TRANSACTIONS_WIDGET', true);
            $viewer->assign('TOP_5_LIVEACCOUNTS_WIDGET', true);
            $viewer->assign('TOP_5_TICKETS_WIDGET', true);
            $viewer->assign('CONTACT_SUMMARY_WIDGET', false);
            if (!empty($res)) {
                $viewer->assign('CONTACT_SUMMARY_WIDGET', true);
            }
            if($ibStatus === 'Approved')
            {
                $viewer->assign('IB_SUMMARY_WIDGET', true);
            }
           echo $viewer->view('DetailViewSummaryContents.tpl', $moduleName, true);
   }

}
