<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_MyIBClients extends CustomerPortal_API_Abstract {

    protected $recordValues = false;
    protected $translate_module = 'CustomerPortal_Client';

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        global $current_user, $adb;
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;
        $activecustomer = $this->getActiveCustomer();
        if ($current_user) {
            $module = $request->get('module');
            $sub_operation = $request->get('sub_operation');
            
            /*if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }*/
            if(configvar('allow_ib_registration'))
            {
                $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
                $IBStatus = CustomerPortal_IBRegister::checkIbApproved($this->getActiveCustomer()->id);
                if($IBStatus)
                {
                    try {
                            switch ($sub_operation)
                            {
                                case "ClientCreate":
                                    $response = CustomerPortal_IBRegister::clientCreateProcess($request, $current_user, $activecustomer);
                                    break;
                                case "ClientList":
                                    $response = CustomerPortal_IBRegister::clientListProcess($request, $current_user, $activecustomer);
                                    break;
                                case "DocumentUpload":
                                    $result = CustomerPortal_IBRegister::documentUploadProcess($request);
                                    $response->setResult(vtranslate('CAB_MSG_DOC_UPLOADED_SUCCESS', 'Documents', $portal_language));
                                    break;
                                case "DocumentList":
                                    $response = CustomerPortal_IBRegister::documentListProcess($request);
                                    break;
                                default :
                                    exit("Invalid Sub Operation!");
                            }
                    }
                    catch (Exception $e) {
                        $response->setError($e->getCode(), $e->getMessage());
                    }
                }
                else
                {
                    $response->setError("CAB_MSG_IB_NOT_APPROVE_VALIDATION", vtranslate('CAB_MSG_IB_NOT_APPROVE_VALIDATION', 'CustomerPortal_Client', $activecustomer->portal_language));
                }
            }
            else
            {
                $response->setError("CAB_MSG_IB_REGISTRATION_NOT_ALLOWED", vtranslate('CAB_MSG_IB_REGISTRATION_NOT_ALLOWED', 'CustomerPortal_Client', $activecustomer->portal_language));
            }
            return $response;
        }
    }

}
