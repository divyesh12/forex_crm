<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Mobile_THIRDPARTYV1_Controller {

	static $opControllers = array(
                'CreateContact'	=> array('file' => '/api/ws/CreateContact.php','class' => 'Mobile_WS_CreateContact'),
                'SaveRecord'	=> array('file' => '/api/ws/SaveRecord.php','class' => 'Mobile_WS_SaveRecord'),
	);

	protected function initSession(Mobile_API_Request $request) {
            include_once dirname(__FILE__) . '/api/ws/Login.php';
            $mobileWsLogin = new Mobile_WS_Login();
            $sessionid = $mobileWsLogin->getCustomSession($request);
            return Mobile_API_Session::init($sessionid);
	}

	protected function getController(Mobile_API_Request $request) {
		$operation = $request->getOperation();
		if(isset(self::$opControllers[$operation])) {
			$operationFile = self::$opControllers[$operation]['file'];
			$operationClass= self::$opControllers[$operation]['class'];

			include_once dirname(__FILE__) . $operationFile;
			$operationController = new $operationClass;
			return $operationController;
		}
	}


	function process(Mobile_API_Request $request) {
//                if(vtlib_isModuleActive('ClientApi') === false) {
//                        $response = new Mobile_API_Response();
//			$response->setError(1501, 'Service not available');
//                        echo $response->emitJSON();exit;
//		}
		$operation = $request->getOperation();

		$response = false;
		$operationController = $this->getController($request);
		if($operationController) {
			$operationSession = false;
			if($operationController->requireLogin()) {
				$operationSession = $this->initSession($request);
				if($operationController->hasActiveUser() === false) {
					$operationSession = false;
				}
				//Mobile_WS_Utils::initAppGlobals();
			} else {
				// By-pass login
				$operationSession = true;
			}

			if($operationSession === false) {
				$response = new Mobile_API_Response();
				$response->setError(1501, 'Login required');
			} else {
//                                $USER_PRIVILEGES_MODEL = Users_Privileges_Model::getCurrentUserPrivilegesModel();
//                                $CLIENTAPI_MODULE_MODEL = Vtiger_Module_Model::getInstance('ClientApi');
//                                if($USER_PRIVILEGES_MODEL->hasModulePermission($CLIENTAPI_MODULE_MODEL->getId()) === false)
//                                {
//                                    $response = new Mobile_API_Response();
//                                    $response->setError(1501, "User doesn't have permission to access this module!");
//                                    echo $response->emitJSON();exit;
//                                }

				try {
					$response = $operationController->process($request);
				} catch(Exception $e) {
					$response = new Mobile_API_Response();
					$response->setError($e->getCode(), $e->getMessage());
				}
			}

		} else {
			$response = new Mobile_API_Response();
			$response->setError(1404, 'Operation not found: ' . $operation);
		}

		if($response !== false) {
			echo $response->emitJSON();
		}
	}

	static function getInstance() {
		$instance = new static();
		return $instance;
	}
}
