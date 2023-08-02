<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class CampaignActivity_ComposeEmail_View extends Vtiger_ComposeEmail_View {

	function __construct() {
		parent::__construct();
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
                $moduleName = $request->get('module');
		$this->composeMailData($request);
		$viewer = $this->getViewer($request);
		echo $viewer->view('ComposeEmailForm.tpl', $moduleName, true);
	}

	function postProcess(Vtiger_Request $request) {
		return;
	}
}
