<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_SaveKycAnswers extends CustomerPortal_FetchRecord
{

    protected $recordValues = false;
    protected $mode = 'edit';

    function process(CustomerPortal_API_Request $request)
    {
        $response = new CustomerPortal_API_Response();
        global $current_user;
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $contactId = $this->getActiveCustomer()->id;
            $module = $request->get('module');
            $recordId = $request->get('recordId');
            $kycFormRequest = $request->get('form_status');
            $valuesJSONString = $request->get('values', '', false);
            $values = "";

            if (!empty($valuesJSONString) && is_string($valuesJSONString)) {
                $values = Zend_Json::decode($valuesJSONString);
            } else {
                $values = $valuesJSONString; // Either empty or already decoded.
            }
            $kycFormStatus = 'Pending';
            if(!empty($kycFormRequest))
            {
                $kycFormStatus = $kycFormRequest;
            }

            $recordModel = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
            $contactDetails = $recordModel->getData();
            $existingKycFormStatus = $contactDetails['kyc_form_status'];
            try {
                $kycRecordModel = new Contacts_KycRecord_Model();
                if($existingKycFormStatus == '' || $existingKycFormStatus === 'Pending' || $existingKycFormStatus === 'Allow for edit')
                {
                    $kycRecordModel->saveKycAnswers($values['answers']);
                }
                $kycRecordModel->updateKycFormStatus($contactId, $kycFormStatus);
                $response->setResult(array('message' => 'KYC answered saved successfully'));
            } catch (Exception $e) {
                $response->setError($e->getCode(), $e->getMessage());
            }
            return $response;
        }
    }
}
