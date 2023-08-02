<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
class Contacts_KycApprove_Action extends Vtiger_Action_Controller {

    public function process(Vtiger_Request $request) {
        $response = new Vtiger_Response();
        $kycStatus = $request->get('kyc_status');
        $contactId = $request->get('contact_id');
        if($kycStatus && !empty($contactId))
        {
            $contactRecordModel = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
            $existingKycStatus = $contactRecordModel->get('record_status');
            if($existingKycStatus != '1')
            {
                $contactRecordModel->set('is_document_verified', '1');
                $contactRecordModel->set('kyc_form_status', 'Approved');
                $contactRecordModel->set('mode', 'edit');
                $contactRecordModel->save();
                $result = array('success' => true, 'message' => 'Kyc Approved');
            }
            else
            {
                $result = array('success' => false, 'message' => 'KYC already approved');
            }
        }
        else
        {
            $result = array('success' => false, 'message' => 'Parameter missing!');
        }
        if($result['success'])
        {
            $response->setResult($result);
        }
        else
        {
            $response->setResult($result);
        }
        $response->emit();
    }

}
