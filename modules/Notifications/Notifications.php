<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';

class Notifications extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_notifications';
	var $table_index= 'notificationsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_notificationscf', 'notificationsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_notifications', 'vtiger_notificationscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_notifications' => 'notificationsid',
		'vtiger_notificationscf'=>'notificationsid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Title' => Array('notifications', 'title'),
		'Assigned To' => Array('crmentity','smownerid')
	);
	var $list_fields_name = Array (
		/* Format: Field Label => fieldname */
		'Title' => 'title',
		'Assigned To' => 'assigned_user_id',
	);

	// Make the field link to detail view
	var $list_link_field = 'title';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Title' => Array('notifications', 'title'),
		'Assigned To' => Array('vtiger_crmentity','assigned_user_id'),
	);
	var $search_fields_name = Array (
		/* Format: Field Label => fieldname */
		'Title' => 'title',
		'Assigned To' => 'assigned_user_id',
	);

	// For Popup window record selection
	var $popup_fields = Array ('title');

	// For Alphabetical search
	var $def_basicsearch_col = 'title';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'title';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('title','assigned_user_id');

	var $default_order_by = 'title';
	var $default_sort_order='ASC';
	var $supportedModuleNames = array("Leads", "Contacts", "Documents", "DemoAccount", "LiveAccount", "LeverageHistory", "Payments", "HelpDesk", "ModComments");

//        public function __construct() {
//            parent::__construct();
//            $this->createRelatedModules('Notifications', $this->supportedModuleNames);
//        }
	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
            
		global $adb;
 		if($eventType == 'module.postinstall')
                {
                    // TODO Handle actions after this module is installed.
                    self::addWidgetTo($moduleName);
                    $this->createRelatedModules($moduleName, $this->supportedModuleNames);
                    $this->createHandle($moduleName);
                    $this->setConfiguration($moduleName);
                    $this->setWorkflows($moduleName);
                    $this->setCron($moduleName);
		}
                else if($eventType == 'module.enabled')
                {
                    // TODO Handle actions before this module is being uninstalled.
                    self::addWidgetTo($moduleName);
                    $this->createRelatedModules($moduleName, $this->supportedModuleNames);
                    $this->createHandle($moduleName);
                    $this->setConfiguration($moduleName);
                    $this->setWorkflows($moduleName);
                    $this->activateCron($moduleName);
		}
                else if($eventType == 'module.disabled')
                {
                    // TODO Handle actions before this module is being uninstalled.
                    self::removeWidgetTo($moduleName);
                    $this->removeRelatedModules($moduleName, $this->supportedModuleNames);
                    $this->removeHandle($moduleName);
                    $this->resetConfiguration($moduleName);
                    $this->resetWorkflows($moduleName);
                    $this->deactivateCron($moduleName);
		}
                else if($eventType == 'module.preuninstall')
                {
                    // TODO Handle actions when this module is about to be deleted.
                    self::removeWidgetTo($moduleName);
                    $this->removeRelatedModules($moduleName, $this->supportedModuleNames);
                    $this->removeHandle($moduleName);
                    $this->resetConfiguration($moduleName);
                    $this->resetWorkflows($moduleName);
                    $this->resetCron($moduleName);
		}
                else if($eventType == 'module.preupdate')
                {
                    // TODO Handle actions before this module is updated.
                    self::removeWidgetTo($moduleName);
                    $this->removeRelatedModules($moduleName, $this->supportedModuleNames);
                    $this->removeHandle($moduleName);
                    $this->resetConfiguration($moduleName);
                    $this->resetWorkflows($moduleName);
                    $this->resetCron($moduleName);
		} 
                else if($eventType == 'module.postupdate')
                {
                    // TODO Handle actions after this module is updated.
                    self::addWidgetTo($moduleName);
                    $this->createRelatedModules($moduleName, $this->supportedModuleNames);
                    $this->createHandle($moduleName);
                    $this->setConfiguration($moduleName);
                    $this->setWorkflows($moduleName);
                    $this->setCron($moduleName);
		}
 	}
        
        /**
        * This function is used to add css and js link
        * @param $moduleName
        */
       public static function addWidgetTo($moduleName)
       {
            global $adb;
            $tempDir = "v7";
            $cssWidgetType = "HEADERCSS";
            $cssWidgetName = "Notifications";
            $cssLink = "layouts/" . $tempDir . "/modules/" . $moduleName . "/resources/" . $moduleName . "CSS.css";
            $jsWidgetType = "HEADERSCRIPT";
            $jsWidgetName = "Notifications";
            $jsLink = "layouts/" . $tempDir . "/modules/" . $moduleName . "/resources/" . $moduleName . "JS.js";
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module)
            {
                $module->addLink($cssWidgetType, $cssWidgetName, $cssLink);
                $module->addLink($jsWidgetType, $jsWidgetName, $jsLink);
            }
            $rs = $adb->pquery("SELECT * FROM `vtiger_ws_entity` WHERE `name` = ?", array($moduleName));
            if ($adb->num_rows($rs) == 0)
            {
                $adb->pquery("INSERT INTO `vtiger_ws_entity` (`name`, `handler_path`, `handler_class`, `ismodule`)\r\n VALUES (?, 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', '1');", array($moduleName));
            }
       }
    
       /**
        * This function is used to remove widget css and js
        * @param $moduleName
        */
       public static function removeWidgetTo($moduleName)
       {
            global $adb;
            $tempDir = "v7";
            $cssWidgetType = "HEADERCSS";
            $cssWidgetName = "Notifications";
            $cssLink = "layouts/" . $tempDir . "/modules/" . $moduleName . "/resources/" . $moduleName . "CSS.css";
            $jsWidgetType = "HEADERSCRIPT";
            $jsWidgetName = "Notifications";
            $jsLink = "layouts/" . $tempDir . "/modules/" . $moduleName . "/resources/" . $moduleName . "JS.js";
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module)
            {
                $module->deleteLink($cssWidgetType, $cssWidgetName, $cssLink);
                $module->deleteLink($jsWidgetType, $jsWidgetName, $jsLink);
            }
            $adb->pquery("DELETE FROM `vtiger_ws_entity` WHERE `name` = ?", array($moduleName));
       }
    
        /**
         * This function is used to create related module entry for notification supported modules
         * @param string $modulename
         * @param array $moduleNames
         * @return boolean
         */
        public function createRelatedModules($modulename, $moduleNames)
        {
            $moduleInstance = Vtiger_Module::getInstance($modulename);
            $fieldModel = Vtiger_Field::getInstance("related_to", $moduleInstance);
            $result = $fieldModel->setRelatedModules($moduleNames);
            return $result;
        }
        
        /**
        * @param string $modulename
        * @param array $moduleNames
        * @return boolean
        */
        public function removeRelatedModules($modulename, $moduleNames)
        {
            $moduleInstance = Vtiger_Module::getInstance($modulename);
            $fieldModel = Vtiger_Field::getInstance("related_to", $moduleInstance);
            $result = $fieldModel->unsetRelatedModules($moduleNames);
            return $result;
        }
    
        /**
        * @param string $moduleName
        */
       private function createHandle($moduleName)
       {
            global $adb;
            $em = new VTEventsManager($adb);
            $em->registerHandler("vtiger.entity.beforedelete", "modules/" . $moduleName . "/" . $moduleName . "Handler.php", (string) $moduleName . "Handler");
            $em->registerHandler("vtiger.entity.aftersave", "modules/" . $moduleName . "/" . $moduleName . "Handler.php", (string) $moduleName . "Handler");
            $em->registerHandler("vtiger.entity.beforesave", "modules/" . $moduleName . "/" . $moduleName . "Handler.php", (string) $moduleName . "Handler");
            $em->registerHandler("vtiger.entity.aftersave.final", "modules/" . $moduleName . "/" . $moduleName . "Handler.php", (string) $moduleName . "Handler");
       }
       /**
        * @param string $moduleName
        */
       private function removeHandle($moduleName)
       {
           global $adb;
           $em = new VTEventsManager($adb);
           $em->unregisterHandler((string) $moduleName . "Handler");
       }
       
       private function setConfiguration($moduleName)
       {
            global $adb;
            $tabId = getTabid($moduleName);
            $isNotificationAllowSql = "INSERT INTO `vtiger_module_configuration_editor` (`id`, `tabid`, `module`, `fieldlabel`, `fieldsuggestion`, `fieldname`, `fieldtype`, `fieldvalue`, `sequence`, `presence`) VALUES (NULL, ?, ?, 'LBL_CRM_NOTIFICATIONS', 'INFO_CRM_NOTIFICATIONS', 'is_crm_notification_allow', 'picklist', 'true', '1', '0');";
            $adb->pquery($isNotificationAllowSql, array($tabId, $moduleName));

            $maxNotificationSql = "INSERT INTO `vtiger_module_configuration_editor` (`id`, `tabid`, `module`, `fieldlabel`, `fieldsuggestion`, `fieldname`, `fieldtype`, `fieldvalue`, `sequence`, `presence`) VALUES (NULL, ?, ?, 'LBL_MAX_CRM_NOTIFICATIONS', 'INFO_MAX_CRM_NOTIFICATIONS', 'max_crm_notifications', 'number', '100', '2', '0');";
            $adb->pquery($maxNotificationSql, array($tabId, $moduleName));
            
            $cabinetNotificationSql = "INSERT INTO `vtiger_module_configuration_editor` (`tabid`, `module`, `fieldlabel`, `fieldsuggestion`, `fieldname`, `fieldtype`, `fieldvalue`, `sequence`, `presence`) VALUES (?, ?, 'LBL_CABINET_NOTIFICATIONS', 'INFO_CABINET_NOTIFICATIONS', 'is_cabinet_notification_allow', 'picklist', 'true', '3', '0');";
            $adb->pquery($cabinetNotificationSql, array($tabId, $moduleName));

            $cabinetMaxNotificationSql = "INSERT INTO `vtiger_module_configuration_editor` (`tabid`, `module`, `fieldlabel`, `fieldsuggestion`, `fieldname`, `fieldtype`, `fieldvalue`, `sequence`, `presence`) VALUES (?, ?, 'LBL_MAX_CABINET_NOTIFICATIONS', 'INFO_MAX_CABINET_NOTIFICATIONS', 'max_cabinet_notifications', 'number', '20', '4', '0');";
            $adb->pquery($cabinetMaxNotificationSql, array($tabId, $moduleName));
       }
       
       private function resetConfiguration($moduleName)
       {
            global $adb;
            $tabId = getTabid($moduleName);
            $isNotificationAllowSql = "DELETE FROM `vtiger_module_configuration_editor` WHERE `tabid` = ?";
            $adb->pquery($isNotificationAllowSql, array($tabId));
       }
       
       private function setWorkflows($moduleName)
       {
            global $adb;
            $notificationWorkflowSql = "UPDATE com_vtiger_workflows SET status = ? WHERE workflowname LIKE ?";
            $adb->pquery($notificationWorkflowSql, array(1, 'notification%'));
       }
       
       private function resetWorkflows($moduleName)
       {
            global $adb;
            $notificationWorkflowSql = "UPDATE com_vtiger_workflows SET status = ? WHERE workflowname LIKE ?";
            $adb->pquery($notificationWorkflowSql, array(0, 'notification%'));
       }
       
       private function setCron($moduleName)
       {
            global $adb;
            $sequenceSql = "select max(sequence) max_sequence_no from vtiger_cron_task";
            $sequenceResult = $adb->pquery($sequenceSql, array());
            $maxSequenceNo = $adb->query_result($sequenceResult,0,'max_sequence_no');
            $nextSeqNo = ($maxSequenceNo + 1);
            
            $notificationCronSql = "INSERT INTO `vtiger_cron_task` (`id`, `name`, `handler_file`, `frequency`, `laststart`, `lastend`, `status`, `module`, `sequence`, `description`) VALUES (NULL, 'Delete Old Notifications', 'cron/modules/Notifications/DeleteOldNotifications.service', '86400', NULL, NULL, '1', 'Notifications', '$nextSeqNo', 'Delete one week old notifications, run cron daily');";
            $adb->pquery($notificationCronSql, array());
       }
       
       private function resetCron($moduleName)
       {
            global $adb;
            $notificationCronSql = "DELETE FROM vtiger_cron_task WHERE module = ?;";
            $adb->pquery($notificationCronSql, array($moduleName));
       }
       
       private function activateCron($moduleName)
       {
            global $adb;
            $notificationCronSql = "UPDATE vtiger_cron_task SET status = ? WHERE module = ?;";
            $adb->pquery($notificationCronSql, array(1, $moduleName));
       }
       
       private function deactivateCron($moduleName)
       {
            global $adb;
            $notificationCronSql = "UPDATE vtiger_cron_task SET status = ? WHERE module = ?;";
            $adb->pquery($notificationCronSql, array(0, $moduleName));
       }
}