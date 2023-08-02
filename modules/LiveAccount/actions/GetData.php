<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class LiveAccount_GetData_Action extends Vtiger_Action_Controller {

	function process(Vtiger_Request $request) {
		$metaType = $request->get('meta_type');
		$response = new Vtiger_Response();
		$result['leverage_enable'] = getProviderLeverageEnable($metaType);
		$response->setResult($result);
		$response->emit();
	}
}
