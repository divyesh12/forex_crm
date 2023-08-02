<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_FetchKycAnswers extends CustomerPortal_API_Abstract {

    protected $translate_module = 'CustomerPortal_Client';

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();

        if ($current_user) {
            $customerId = $this->getActiveCustomer()->id;
            $module = $request->get('module');
            $response_type = $request->get('response_type');
            $kycRecordModel = new Contacts_KycRecord_Model();
            $kycAnswers = $kycRecordModel->getKycAnswers($customerId);

            if (!empty($response_type) && $response_type == 'List') {
                //Note: In future we need to use this cabinet too, as of now using Mobile App
                $response->addToResult('records', $kycAnswers);
            } else {
                $response->setResult($kycAnswers);
            }
            return $response;
        }
    }

}
