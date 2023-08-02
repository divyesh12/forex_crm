<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

vimport('~~/vtlib/Vtiger/Module.php');

/**
 * Notifications Module Model Class
 */
class Notifications_Module_Model extends Vtiger_Module_Model {
    
        /**
	 * Function returns notification Reminder record models
	 * @return <Array of Notifications_Module_Model>
	 */
	public static function getNotificationReminder($request) {
                $isAjaxRequest = $request->get('isAjaxRequest');
                $maxNotificationId = $request->get('maxNotificationId');
                $notificationLimit = configvar('max_crm_notifications');
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$notificationReminder = configvar('is_crm_notification_allow');
		$recordModels = $params = array();
                
                array_push($params, $currentUserModel->getId());
                $whereQuery = " AND vtiger_crmentity.smownerid = ? ";
                if($currentUserModel->isAdminUser())
                {
                    $whereQuery = " AND (vtiger_crmentity.smownerid = ?) ";
                }
                
		if($notificationReminder) {
                        $where = $limit = "";
                        if($isAjaxRequest && !empty($maxNotificationId))
                        {
                            $where = " AND vtiger_notifications.notificationsid > ? ";
                            array_push($params, $maxNotificationId);
                        }
                        if(!empty($notificationLimit))
                        {
                            $limit = " LIMIT ?";
                            $notificationIntLimit = (int) $notificationLimit;
                            array_push($params, $notificationIntLimit);
                        }
                        
                        
			$reminderNotificationResult = "SELECT vtiger_notifications.notificationsid FROM vtiger_notifications
                                                    INNER JOIN vtiger_crmentity ON vtiger_notifications.notificationsid = vtiger_crmentity.crmid
                                                    WHERE vtiger_notifications.status = 0
                                                    " . $whereQuery . " AND vtiger_crmentity.deleted = 0 AND vtiger_notifications.notification_type != 'Cabinet' " . $where . " ORDER BY vtiger_notifications.notificationsid DESC " . $limit;
			$result = $db->pquery($reminderNotificationResult, $params);
			$rows = $db->num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$recordId = $db->query_result($result, $i, 'notificationsid');
				$recordModels[] = Vtiger_Record_Model::getInstanceById($recordId, 'Notifications');
			}
		}
		return $recordModels;
	}
        
        public static function readNotification($id = '', $isAll = false) {
            $db = PearDatabase::getInstance();
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            $param = array();
            $where = $join = $whereQuery = "";

            if(!$isAll)
            {
                $where = " WHERE notificationsid = ? ";
                array_push($param,$id);
            }
            else
            {
                if($currentUserModel->isAdminUser())
                {
                    $whereQuery = " (vtiger_crmentity.smownerid = ?) AND ";
                    array_push($param, $currentUserModel->getId());
                }
                $join = " INNER JOIN vtiger_crmentity ON vtiger_notifications.notificationsid = vtiger_crmentity.crmid ";
                $where = " WHERE " . $whereQuery . " vtiger_notifications.notificationsid <= ?";
                array_push($param,$id);
            }
            
            $updateNotificationQuery = "UPDATE vtiger_notifications " . $join . " SET vtiger_notifications.status = 1 " . $where;
            $result = $db->pquery($updateNotificationQuery, $param);
            if($result)
            {
                return true;
            }
            return false;
        }
        
        public static function setSettings($value = '', $field = '') {
            $db = PearDatabase::getInstance();
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            $currentUserID = $currentUserModel->getId();
            if(!empty($field))
            {
                $checkpresence = '';
                $checkpresence = $db->pquery("SELECT id FROM notification_user_settings WHERE user_id = ?", array($currentUserID));
                // Relation already exists? No need to add again
                if ($checkpresence && $db->num_rows($checkpresence))
                {
                    $updateNotificationSettingQuery = "UPDATE notification_user_settings SET $field = ? WHERE user_id = ?";
                    $updateResult = $db->pquery($updateNotificationSettingQuery, array($value, $currentUserID));
                }
                else
                {
                    $insertNotificationSettingQuery = "insert into notification_user_settings(user_id,$field) values (?,?)";
                    $updateResult = $db->pquery($insertNotificationSettingQuery, array($currentUserID, $value));
                }
                if($updateResult)
                {
                    return true;
                }
            }
            return false;
        }
        
        public static function getSettings() {
            $db = PearDatabase::getInstance();
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            $currentUserID = $currentUserModel->getId();
            $userSettingResult = $db->pquery("SELECT * FROM notification_user_settings WHERE user_id = ?", array($currentUserID));
            $rows = $db->num_rows($userSettingResult);
            $setting = array();
            for($i=0; $i<$rows; $i++) {
                    $setting['sound'] = $db->query_result($userSettingResult, $i, 'sound');
                    $setting['message'] = $db->query_result($userSettingResult, $i, 'message');
            }
            return $setting;
        }
        
}
