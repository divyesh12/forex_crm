<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
// include_once dirname(__FILE__) . '/models/Alert.php';
// include_once dirname(__FILE__) . '/models/SearchFilter.php';
// include_once dirname(__FILE__) . '/models/Paging.php';

require_once('modules/ServiceProviders/ServiceProviders.php');
class Mobile_WS_FetchForexCRMData extends Mobile_WS_Controller {

	function requireLogin() {
		return false;
	}
	function process(Mobile_API_Request $request) {
		global $adb;
		$current_user = $this->getActiveUser();
		$response = new Mobile_API_Response();
		$countsModuleRecords = getCountsOfModuleRecords();
		$resultResponse = $countsModuleRecords;
		$response->setResult($resultResponse);
		return $response;
	}
}