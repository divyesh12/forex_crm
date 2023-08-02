<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class CampaignActivity_SaveAjax_Action extends Vtiger_SaveAjax_Action {
        
    public function process(Vtiger_Request $request) {
            global $log;
		
		$response = new Vtiger_Response();
		try {
			$recordModel = $this->saveRecord($request);
                        
			// removed decode_html to eliminate XSS vulnerability
			$result['_recordLabel'] = decode_html($recordModel->getName());
			$result['_recordId'] = $recordModel->getId();
			$response->setEmitType(Vtiger_Response::$EMIT_JSON);
			$response->setResult($result);
		} catch (DuplicateException $e) {
			$response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
		} catch (Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}
        
        
    public function getRecordModelFromRequest(Vtiger_Request $request) {
            global $log;
            $recordModel = parent::getRecordModelFromRequest($request);
            $CampaignActivityInstance = CRMEntity::getInstance('CampaignActivity');
            $schType = $request->get('schtypeid');
            $nextTriggerTime = CampaignActivity_Module_Model::getNextTriggerTime($schType, $request, true);
            $request->set('nexttrigger_time', $nextTriggerTime);

            $schtime = $request->get("schtime");
            if(!preg_match('/^[0-2]\d(:[0-5]\d){1,2}$/', $schtime) or substr($schtime,0,2)>23) {  // invalid time format
                    $schtime='00:00';
            }
            $schtime .=':00';

            $request->set('schtime', $schtime);

            $workflowScheduleType = $request->get('schtypeid');
            $request->set('schtypeid', $workflowScheduleType);

            $dayOfMonth = null; $dayOfWeek = null; $month = null; $annualDates =null;

            if($workflowScheduleType == $CampaignActivityInstance->SCHEDULED_WEEKLY) {
                    $weekDays = $request->get('schdayofweek');
                    if(is_string($weekDays)) {	// need to save these as json data
                        $weekDaysArr = explode(",", $weekDays);
                    }
                    $dayOfWeek = Zend_Json::encode($weekDaysArr);
            } else if($workflowScheduleType == $CampaignActivityInstance->SCHEDULED_MONTHLY_BY_DATE) {
                    $dayOfMonth = Zend_Json::encode($request->get('schdayofmonth'));
            } else if($workflowScheduleType == $CampaignActivityInstance->SCHEDULED_ON_SPECIFIC_DATE) {
                    $date = $request->get('schdate');
                    $dateDBFormat = DateTimeField::convertToDBFormat($date);
                    $nextTriggerTime = $dateDBFormat.' '.$schtime;
                    $currentTime = Vtiger_Util_Helper::getActiveAdminCurrentDateTime();
                    if($nextTriggerTime > $currentTime) {
                            $request->set('nexttrigger_time', $nextTriggerTime);
                    } else {
                            $request->set('nexttrigger_time', date('Y-m-d H:i:s', strtotime('+10 year')));
                    }
                    $annualDates = Zend_Json::encode(array($dateDBFormat));
            } else if($workflowScheduleType == $CampaignActivityInstance->SCHEDULED_ANNUALLY) {
                    $annualDates = Zend_Json::encode($request->get('schannualdates'));
            }
            
            $recordModel->set('nexttrigger_time', $nextTriggerTime);
            $recordModel->set('schdayofmonth', $dayOfMonth);
            $recordModel->set('schdayofweek', $dayOfWeek);
            $recordModel->set('schannualdates', $annualDates);
            $recordModel->set('schtypeid', $schType);
            $recordModel->set('schtime', $schtime);
            $recordModel->set('campaign_activity_template', $request->getRaw('campaign_activity_template'));
            $recordModel->set('nexttrigger_time', $request->get('nexttrigger_time'));
            return $recordModel;
    }
}