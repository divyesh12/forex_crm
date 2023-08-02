<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_KycAnswer_View extends Vtiger_IndexAjax_View {

    public function preProcess(Vtiger_Request $request, $display = true) {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        parent::preProcess($request, $display);
    }

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $contactId = $request->get('contact_id');
        $kycRecordModel = new Contacts_KycRecord_Model();
        $kycAnswers = $kycRecordModel->getKycAnswers($contactId);
        $kycApprovedDocCount = $kycRecordModel->getTotalKycApprovedDocCount($contactId);
        $contactRecordModel = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
        $kycStatus = $contactRecordModel->get('is_document_verified');
        $kycApproveBtnEnable = true;
        if($kycStatus == '1' || $kycApprovedDocCount <= 0)
        {
            $kycApproveBtnEnable = false;
        }
        $viewer = $this->getViewer($request);
        $viewer->assign('SCRIPTS',$this->getHeaderScripts($request));
        $viewer->assign('KYC_ANSWERS',$kycAnswers);
        $viewer->assign('CONTACT_ID',$contactId);
        $viewer->assign('BUTTON_NAME','KYC Approved');
        $viewer->assign('KYC_BTN_ENABLE',$kycApproveBtnEnable);
        $viewer->assign('MODULE','Documents');
        $viewer->view('KycAnswers.tpl', $moduleName);
    }

    public function getHeaderScripts(Vtiger_Request $request) {
		
		$moduleName = $request->getModule();
		
		$jsFileNames = array(
			"modules.$moduleName.resources.Edit"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}

    /* End */
}
