<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class CampaignActivity_Record_Model extends Vtiger_Record_Model {

	public static function getInstance($campaignActivityId) {
                $moduleName = 'CampaignActivity';
                $recordModel = Vtiger_Record_Model::getInstanceById($campaignActivityId, $moduleName);
		return self::getInstanceFromTimeTriggerObject($recordModel);
	}
        
        public static function getInstanceFromTimeTriggerObject($recordModel) {
		$wf = new self();
                $wf->schtypeid = $recordModel->get('schtypeid');
		$wf->schtime = $recordModel->get('schtime');
                $schDayofMonth = str_replace('&quot;', '"', $recordModel->get('schdayofmonth'));
		$wf->schdayofmonth = $schDayofMonth;
                $schdayofweek = str_replace('&quot;', '"', $recordModel->get('schdayofweek'));
		$wf->schdayofweek = $schdayofweek;
                $schmonth = str_replace('&quot;', '"', $recordModel->get('schmonth'));
		$wf->schmonth = $schmonth;
                $schannualdates = str_replace('&quot;', '"', $recordModel->get('schannualdates'));
		$wf->schannualdates = $schannualdates;
		$wf->nexttrigger_time = $recordModel->get('nexttrigger_time');

		return $wf;
	}
        
        public function checkCampaignActivityStatus($record) {
            global $adb, $log;
            $status = true;
            if (!empty($record)) {
                $query = "SELECT campaign_activity_status FROM vtiger_campaignactivity WHERE campaignactivityid = ?";
                $result = $adb->pquery($query, array($record));
		$campaignActivityStatus = $adb->query_result($result, 0, 'campaign_activity_status');
                if(!empty($campaignActivityStatus) && in_array($campaignActivityStatus, array('Completed', 'Cancelled')))
                {
                    $status = false;
                }
            }
            return $status;
        }
}

