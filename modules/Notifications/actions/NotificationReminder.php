<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Notifications_NotificationReminder_Action extends Vtiger_Action_Controller {

	function __construct() {
		$this->exposeMethod('getNotifications');
		$this->exposeMethod('readNotification');
		$this->exposeMethod('setNotificationSetting');
	}

	public function requiresPermission(Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

	}

	function getNotifications(Vtiger_Request $request) {
                $isAjaxRequest = $request->get('isAjaxRequest');
		$recordModels = Notifications_Module_Model::getNotificationReminder($request);
		foreach($recordModels as $record) {
			$records[] = $record->getDisplayableValues();
		}

		$response = new Vtiger_Response();
		$response->setResult($records);
		$response->emit();
	}
        
	function readNotification(Vtiger_Request $request) {
                $notificationId = $request->get('notification_id');
                $isAllRead = false;
                $maxNotificationId = $request->get('max_notification_id');
                if(!empty($maxNotificationId))
                {
                    $notificationId = $maxNotificationId;
                    $isAllRead = true;
                }
		$updateResult = Notifications_Module_Model::readNotification($notificationId, $isAllRead);
		$resposeArr['status'] = $updateResult;
		$response = new Vtiger_Response();
		$response->setResult($resposeArr);
		$response->emit();
	}
        
        function setNotificationSetting(Vtiger_Request $request) {
            $settingValue = $request->get('setting_value');
            $settingType = $request->get('setting_type');
            $settingResult = Notifications_Module_Model::setSettings($settingValue, $settingType);
            $resposeArr['status'] = $settingResult;
            $response = new Vtiger_Response();
            $response->setResult($resposeArr);
            $response->emit();
        }
}
