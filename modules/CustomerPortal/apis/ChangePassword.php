<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_ChangePassword extends CustomerPortal_API_Abstract {

    protected $recordValues = false;

    function process(CustomerPortal_API_Request $request) {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $current_customer = $this->getActiveCustomer();
            $username = $this->getActiveCustomer()->username;
            $password = $request->get('password');
            $newPassword = $request->get('newPassword');
            $module = 'Contacts';
            $recordId = vtws_getWebserviceEntityId($module, $this->getActiveCustomer()->id);

            if (!$this->authenticatePortalUser($username, $password, true)) {
                //throw new Exception("Wrong password.Please try again", 1412);
                throw new Exception(vtranslate('CAB_MSG_CURRENT_PASS_DOES_NOT_MATCH', $module, $portal_language), 1412);
                exit;
            }

            if (configvar('is_portal_password_enable')) {
                try {
                    if (vtws_recordExists($recordId)) {
                        $this->recordValues = vtws_retrieve($recordId, $current_user);

                        // Setting missing mandatory fields for record.
                        $describe = vtws_describe($module, $current_user);
                        $mandatoryFields = CustomerPortal_Utils:: getMandatoryFields($describe);
                        foreach ($mandatoryFields as $fieldName => $type) {
                            if (!isset($this->recordValues[$fieldName])) {
                                if ($type['name'] == 'reference') {
                                    $crmId = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $module);
                                    $wsId = vtws_getWebserviceEntityId($type['refersTo'][0], $crmId);
                                    $this->recordValues[$fieldName] = $wsId;
                                } else {
                                    $this->recordValues[$fieldName] = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $module);
                                }
                            }
                        }
                        if (isset($this->recordValues['id'])) {
                            if ($module == 'Contacts') {
                                $this->recordValues['plain_password'] = $newPassword;
                                $updatedStatus = vtws_update($this->recordValues, $current_user);
                                if ($updatedStatus['id'] == $recordId) {
                                    $sql = "UPDATE vtiger_portalinfo SET user_password=? WHERE id=? AND user_name=?";
                                    $adb->pquery($sql, array(Vtiger_Functions::generateEncryptedPassword($newPassword), $current_customer->id, $username));
                                    $response->setResult(vtranslate('CAB_MSG_PASS_CHANGED_SUCCESS', $module, $portal_language));
                                } else {
                                    $response->setError("CAB_MSG_RECORD_DOES_NOT_EXIST", vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', $this->translate_module, $portal_language));
                                }
                            }
                        }
                    } else {
                        $response->setError("CAB_MSG_RECORD_DOES_NOT_EXIST", vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', $this->translate_module, $portal_language));
                    }
                } catch (Exception $e) {
                    $response->setError($e->getCode(), $e->getMessage());
                }
            } else {
                $sql = "UPDATE vtiger_portalinfo SET user_password=? WHERE id=? AND user_name=?";
                $adb->pquery($sql, array(Vtiger_Functions::generateEncryptedPassword($newPassword), $current_customer->id, $username));
                $response->setResult(vtranslate('CAB_MSG_PASS_CHANGED_SUCCESS', $module, $portal_language));
            }
        }
        return $response;
    }

}
