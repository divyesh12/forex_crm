<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
/*
  Added By:-  DivyeshChothani
  Comment:- MobileAPI Changes For Change User Password
 */
include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

vimport('~~/include/Webservices/Custom/ChangePassword.php');

class Mobile_WS_ChangeUserPassword extends Mobile_WS_FetchRecordWithGrouping {

    protected $recordValues = false;

    // Avoid retrieve and return the value obtained after Create or Update
    protected function processRetrieve(Mobile_API_Request $request) {
        return $this->recordValues;
    }

    function process(Mobile_API_Request $request) {
        global $current_user; // Required for vtws_update API
        $current_user = $this->getActiveUser();
        
        $module = $request->get('module');
        $recordId = end(explode('x', $request->get('record')));

        $newPassword = $request->get('new_password');
        $confirmPassword = $request->get('confirm_password');

        $response = new Mobile_API_Response();

        try {
            if (vtws_recordExists($request->get('record'))) {

                if ($newPassword === $confirmPassword) {
                    $wsUserId = vtws_getWebserviceEntityId($module, $recordId);
                    $wsStatus = vtws_changePassword($wsUserId, '', $newPassword, $confirmPassword, $current_user);
                    if ($wsStatus['message']) {
                        $response->setResult(array('code' => 200, 'message' => $wsStatus['message']));
                    } else {
                        $response->setError(201, 'Password not changed. something went to wrong');
                    }
                } else {
                    $response->setError(201, vtranslate('LBL_REENTER_PASSWORDS', $module));
                }
                return $response;
            } else {
                $response->setError(201, "Record does not exist");
                return $response;
            }
        } catch (DuplicateException $e) {
            $response->setError($e->getCode(), $e->getMessage());
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        return $response;
    }

}
