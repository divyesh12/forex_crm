<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/*
Added By:-  DivyeshChothani
Comment:- MobileAPI Changes For Change Portal Password
*/
include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

class Mobile_WS_ChangePortalPassword extends Mobile_WS_FetchRecordWithGrouping {
	protected $recordValues = false;
	
	// Avoid retrieve and return the value obtained after Create or Update
	protected function processRetrieve(Mobile_API_Request $request) {
		return $this->recordValues;
	}
	
	function process(Mobile_API_Request $request) {
		global $current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();
		
		$module = $request->get('module');
		$recordid = $request->get('record');
		$response = new Mobile_API_Response();
		
		try {
			if (vtws_recordExists($recordid)) {
				$result = changePortalPassword($request);
				$success = $result['success'];
				if($success){
					$response->setResult(array('code'=>200, 'message'=>$result['message']));
				}else{
					$response->setError(201, $result['message']);
				}
				return $response;
            } else {
                $response->setError(201, "Record does not exist");
                return $response;
			}
		} catch (DuplicateException $e) {
			$response->setError($e->getCode(), $e->getMessage());
        } catch(Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
		return $response;
	}

}