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

class CampaignActivity extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_campaignactivity';
	var $table_index= 'campaignactivityid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_campaignactivitycf', 'campaignactivityid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_campaignactivity', 'vtiger_campaignactivitycf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_campaignactivity' => 'campaignactivityid',
		'vtiger_campaignactivitycf'=>'campaignactivityid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Module' => Array('campaignactivity', 'campaign_activity_module'),
		'Subject' => Array('campaignactivity', 'subject'),
		'Status' => Array('campaignactivity', 'campaign_activity_status'),
		'Type' => Array('campaignactivity', 'activity_type'),
		'Assigned To' => Array('crmentity','smownerid')
	);
	var $list_fields_name = Array (
		/* Format: Field Label => fieldname */
		'Module' => 'campaign_activity_module',
		'Subject' => 'subject',
		'Status' => 'campaign_activity_status',
		'Type' => 'activity_type',
		'Assigned To' => 'assigned_user_id',
	);

	// Make the field link to detail view
	var $list_link_field = 'subject';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Subject' => Array('campaignactivity', 'subject'),
		'Assigned To' => Array('vtiger_crmentity','assigned_user_id'),
	);
	var $search_fields_name = Array (
		/* Format: Field Label => fieldname */
		'Subject' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);

	// For Popup window record selection
	var $popup_fields = Array ('subject');

	// For Alphabetical search
	var $def_basicsearch_col = 'subject';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'subject';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('subject','assigned_user_id');

	var $default_order_by = 'subject';
	var $default_sort_order='ASC';
        var $relatedModuleNames = array("Campaigns");
        
        var $SCHEDULED_HOURLY = 1;
        var $SCHEDULED_DAILY = 2;
        var $SCHEDULED_WEEKLY = 3;
        var $SCHEDULED_ON_SPECIFIC_DATE = 4;
        var $SCHEDULED_MONTHLY_BY_DATE = 5;
        var $SCHEDULED_MONTHLY_BY_WEEKDAY = 6;
        var $SCHEDULED_ANNUALLY = 7;

        public function __construct() {
            parent::__construct();
            
        }
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
                    $this->setCron($moduleName);
//                    $this->createHandle($moduleName);
                    $this->createRelatedTo($moduleName);
		}
                else if($eventType == 'module.enabled')
                {
                    // TODO Handle actions before this module is being uninstalled.
                    $this->activateCron($moduleName);
//                    $this->createHandle($moduleName);
                    $this->createRelatedTo($moduleName);
                }
                else if($eventType == 'module.disabled')
                {
                    // TODO Handle actions before this module is being uninstalled.
                    $this->deactivateCron($moduleName);
//                    $this->removeHandle($moduleName);
                    $this->removeRelatedTo($moduleName);
                    
		}
                else if($eventType == 'module.preuninstall')
                {
                    // TODO Handle actions when this module is about to be deleted.
                    $this->resetCron($moduleName);
//                    $this->removeHandle($moduleName);
		}
                else if($eventType == 'module.preupdate') 
                {
                    // TODO Handle actions before this module is updated.
                    $this->resetCron($moduleName);
//                    $this->removeHandle($moduleName);
                    $this->removeRelatedTo($moduleName);
		}
                else if($eventType == 'module.postupdate')
                {
                    // TODO Handle actions after this module is updated.
                    $this->setCron($moduleName);
//                    $this->createHandle($moduleName);
                    $this->createRelatedTo($moduleName);
		}
 	}
        
        public function createRelatedTo($moduleName)
        {
            include_once "vtlib/Vtiger/Module.php";
            global $adb;
            $campaignInstance = Vtiger_Module::getInstance('Campaigns');
            $CampaignActivityModule = Vtiger_Module::getInstance($moduleName);
            $campaignInstance->setrelatedlist($CampaignActivityModule, 'Campaign Activity', array('ADD'), 'get_campaign_activity');
            
            $emailModule = Vtiger_Module::getInstance('Emails');
            $campaignInstance->setrelatedlist($emailModule, 'Emails', array(), 'get_emails');
        }
        
        public function removeRelatedTo($moduleName)
        {
            include_once "vtlib/Vtiger/Module.php";
            $campaignInstance = Vtiger_Module::getInstance('Campaigns');
            $CampaignActivityModule = Vtiger_Module::getInstance($moduleName);
            $campaignInstance->unsetRelatedList($CampaignActivityModule, 'Campaign Activity', 'get_campaign_activity');
            
            $emailModule = Vtiger_Module::getInstance('Emails');
            $campaignInstance->unsetRelatedList($emailModule, 'Emails', 'get_emails');
        }
        /**
        * @param string $moduleName
        */
        private function createHandle($moduleName)
        {
             global $adb;
             $em = new VTEventsManager($adb);
             $em->registerHandler("vtiger.entity.aftersave", "modules/" . $moduleName . "/handlers/" . "CampaignsAfterSaveHandler.php", (string) $moduleName . "AfterSaveHandler");
        }
        /**
         * @param string $moduleName
         */
        private function removeHandle($moduleName)
        {
            global $adb;
            $em = new VTEventsManager($adb);
            $em->unregisterHandler((string) $moduleName . "AfterSaveHandler");
        }
       
        private function setCron($moduleName)
        {
             global $adb;
             $sequenceSql = "select max(sequence) max_sequence_no from vtiger_cron_task";
             $sequenceResult = $adb->pquery($sequenceSql, array());
             $maxSequenceNo = $adb->query_result($sequenceResult,0,'max_sequence_no');
             $nextSeqNo = ($maxSequenceNo + 1);

             $campaignActivityCronSql = "INSERT INTO `vtiger_cron_task` (`id`, `name`, `handler_file`, `frequency`, `laststart`, `lastend`, `status`, `module`, `sequence`, `description`) VALUES (NULL, 'Campaign Activity Cron', 'cron/modules/CampaignActivity/CampaignActivityCron.service', '60', NULL, NULL, '1', 'CampaignActivity', '$nextSeqNo', 'Run cron to check campaign activities and execute it');";
             $adb->pquery($campaignActivityCronSql, array());
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