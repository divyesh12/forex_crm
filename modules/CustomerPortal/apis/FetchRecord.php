<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_FetchRecord extends CustomerPortal_API_Abstract {

    protected $translate_module = 'CustomerPortal_Client';
    protected function processRetrieve(CustomerPortal_API_Request $request) {
        global $adb;
        $portal_language = $this->getActiveCustomer()->portal_language;
        $parentId = $request->get('parentId');
        $recordId = $request->get('recordId');
        $module = VtigerWebserviceObject::fromId($adb, $recordId)->getEntityName();

        //Check configuration added by sandeep 20-02-2020
        $current_user = $this->getActiveUser();
        $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
        CustomerPortal_Utils::checkConfiguration($contactId, $current_user, $module, array(), $portal_language);
        //End
        if (!CustomerPortal_Utils::isModuleActive($module)) {
            throw new Exception(vtranslate('CAB_MSG_RECORDS_NOT_ACCESSIBLE_FOR_THIS_MODULE', $this->translate_module, $portal_language), 1412);
            exit;
        }

        if (!empty($parentId)) {
            if (!$this->isRecordAccessible($parentId)) {
                throw new Exception(vtranslate('CAB_MSG_PARENT_RECORD_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }
            $relatedRecordIds = $this->relatedRecordIds($module, CustomerPortal_Utils::getRelatedModuleLabel($module), $parentId);

            if (!in_array($recordId, $relatedRecordIds)) {
                throw new Exception(vtranslate('CAB_MSG_RECORD_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);                
                exit;
            }
        } else {
            if (!$this->isRecordAccessible($recordId, $module)) {
                throw new Exception(vtranslate('CAB_MSG_RECORD_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);                
                exit;
            }
        }

        $fields = implode(',', CustomerPortal_Utils::getActiveFields($module));
        $sql = sprintf('SELECT %s FROM %s WHERE id=\'%s\';', $fields, $module, $recordId);
        $result = vtws_query($sql, $this->getActiveUser());
        return $result[0];
    }

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $record = $this->processRetrieve($request);
            $record = CustomerPortal_Utils::resolveRecordValues($record);

            //Custom: Added for dynamic message setting based on aprroval or peding flow
            $record['message'] = CustomerPortal_Utils::setMessage($request->get('module'), $request->get('action'), '', $portal_language);
            //End
            $response->setResult(array('record' => $record));
        }
        return $response;
    }

}
