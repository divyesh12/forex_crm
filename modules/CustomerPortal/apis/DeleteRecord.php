<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_DeleteRecord extends CustomerPortal_FetchRecord {

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        global $current_user;
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $module = $request->get('module');

            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }

            if (in_array($module, array('LeverageHistory', 'ContactChannels', 'DemoAccount', 'LiveAccount', 'ModComments', 'Payments', 'HelpDesk', 'Documents', 'Assets', 'Quotes', 'Contacts', 'Accounts', 'Notifications'))) {
                $recordId = $request->get('recordId');
                if (!empty($recordId)) {
                    //Stop edit record if edit is disabled
                    if (!CustomerPortal_Utils::isModuleRecordEditable($module)) {
                        throw new Exception(vtranslate('CAB_MSG_RECORD_CANNOT_BE_EDITED', $this->translate_module), 1412);
                        exit;
                    }
                }
                try {
                    if (vtws_recordExists($recordId)) {
                        //Delete operation                        
                        include_once 'include/Webservices/Delete.php';
                        try {
                            //$wsid = vtws_getWebserviceEntityId('Paymetns', $recordId); // Module_Webservice_ID x CRM_ID
                            if (vtws_delete($recordId, $current_user)) {
                                $response->addToResult('message', vtranslate('CAB_MSG_RECORD_DELETED', $this->translate_module, $portal_language));
                            }
                        } catch (WebServiceException $ex) {
                            echo $ex->getMessage();
                        }
                    } else {
                        $response->setError("CAB_MSG_RECORD_DOES_NOT_EXIST", vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', $this->translate_module, $portal_language));
                    }
                } catch (Exception $e) {
                    $response->setError($e->getCode(), $e->getMessage());
                }
            } else {
                $response->setError(1404, vtranslate('CAB_MSG_SAVE_OPER_NOT_SUPPORTS_THIS_MODULE', $this->translate_module, $portal_language));
            }
            return $response;
        }
    }

}
