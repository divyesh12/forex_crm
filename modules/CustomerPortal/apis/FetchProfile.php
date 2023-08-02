<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_FetchProfile extends CustomerPortal_API_Abstract {

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
            $encodedContactImage = CustomerPortal_Utils::getImageDetails($this->getActiveCustomer()->id, 'Contacts');
            $accountId = $this->getParent($contactId);

            $contact = vtws_retrieve($contactId, $current_user);
            $contact = CustomerPortal_Utils::resolveRecordValues($contact);
            $contact['imagedata'] = $encodedContactImage['imagedata'];
            $contact['imagetype'] = $encodedContactImage['imagetype'];
            if ($contact['record_status'] == '') {
                $contact['ib_message'] = vtranslate('CAB_MSG_ARE_YOU_INTRESTED_IN_BECOMING_IB', 'Contacts', $portal_language);
            } else if ($contact['record_status'] == 'Pending') {
                $contact['ib_message'] = vtranslate('CAB_MSG_YOUR_IB_REQUEST_HAS_BEEN_PROCESSED', 'Contacts', $portal_language);
                            } else if ($contact['record_status'] == 'Disapproved') {
                $contact['ib_message'] = vtranslate('CAB_MSG_SORRY_YOUR_IB_ACCOUNT_REQUEST_HAS_BEEN_DISAPPROVED', 'Contacts', $portal_language);                
            } else if ($contact['record_status'] == 'Cancelled') {
                $contact['ib_message'] = vtranslate('CAB_MSG_SORRY_YOUR_IB_ACCOUNT_REQUEST_HAS_BEEN_CANCELLED', 'Contacts', $portal_language);                
            }
            
            $response->addToResult('customer_details', $contact);

            if (!empty($accountId)) {
                $idComponents = explode('x', $accountId);
                $encodedAccountImage = CustomerPortal_Utils::getImageDetails($idComponents[1], 'Accounts');
                $account = vtws_retrieve($accountId, $current_user);
                $account = CustomerPortal_Utils::resolveRecordValues($account);
                $account['imagedata'] = $encodedAccountImage['imagedata'];
                $account['imagetype'] = $encodedAccountImage['imagetype'];
                $response->addToResult('company_details', $account);
            }

            $companyDetails = CustomerPortal_Utils::getCompanyDetails();
            $response->addToResult('company_profile', $companyDetails);
        }
        return $response;
    }

}
