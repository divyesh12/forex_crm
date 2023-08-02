<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_FetchEwalletBalance extends CustomerPortal_API_Abstract {

    protected $translate_module = 'CustomerPortal_Client';
    protected function processRetrieve(CustomerPortal_API_Request $request) {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;
        if ($current_user) {
            $customerId = $this->getActiveCustomer()->id;
            $module = $request->get('module');

            //Check configuration added by sandeep 20-02-2020
            $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
            CustomerPortal_Utils::checkConfiguration($contactId, $current_user, $module, array(), $portal_language);
            //End

            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_RECORDS_NOT_ACCESSIBLE_FOR_THIS_MODULE', $this->translate_module, $portal_language), 1412);
                exit;
            }
            return CustomerPortal_Utils::getEwalletBalance($customerId);
            exit;
        }
    }

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();

        if ($current_user) {
            $record = $this->processRetrieve($request);

            $record = CustomerPortal_Utils::resolveRecordValues($record);
            $response->setResult(array('ewallet_balance' => $record));
        }
        return $response;
    }

}
