<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class CampaignActivity_Module_Model extends Vtiger_Module_Model {

    /**
     * Update the Next trigger timestamp for a workflow
     */
    public function updateNexTriggerTime($campaignActivityId, $request) {
        global $log;
        $nextTriggerTime = CampaignActivity_Module_Model::getNextTriggerTime($request['schtypeid'], $request);
        CampaignActivity_Module_Model::setNextTriggerTime($nextTriggerTime, $campaignActivityId);
    }

    public function setNextTriggerTime($time, $campaignActivityId) {
        if ($time) {
            $db = PearDatabase::getInstance();
            $db->pquery("UPDATE vtiger_campaignactivity SET nexttrigger_time=? WHERE campaignactivityid=?", array($time, $campaignActivityId));
        }
    }

    public function getNextTriggerTime($scheduleType, $request, $isRequestFromSave = false) {
        global $default_timezone, $log;
        $log->debug('Entering into getNextTriggerTime..');
        $log->debug($scheduleType);
        $log->debug($request);
        $CampaignActivityInstance = CRMEntity::getInstance('CampaignActivity');
        $admin = Users::getActiveAdminUser();
        $adminTimeZone = $admin->time_zone;
        @date_default_timezone_set($adminTimeZone);

        $nextTime = null;
        if(is_array($request))
        {
            $schdate = $request['schdate'];
            $schtime = $request['schtime'];
            $schdayofweek = $request['schdayofweek'];
            $schdayofmonth = $request['schdayofmonth'];
        }
        else
        {
            $schdate = $request->get('schdate');
            $schtime = $request->get('schtime');
            $schdayofweek = $request->get('schdayofweek');
            $schdayofmonth = $request->get('schdayofmonth');
        }

        if ($scheduleType == $CampaignActivityInstance->SCHEDULED_HOURLY) {
            $nextTime = date("Y-m-d H:i:s", strtotime("+1 hour"));
        }

        if ($scheduleType == $CampaignActivityInstance->SCHEDULED_DAILY) {
            $nextTime = CampaignActivity_Module_Model::getNextTriggerTimeForDaily($schtime);
        }

        if ($scheduleType == $CampaignActivityInstance->SCHEDULED_WEEKLY) {
            $nextTime = CampaignActivity_Module_Model::getNextTriggerTimeForWeekly($schdayofweek, $schtime, $isRequestFromSave);
        }

        if ($scheduleType == $CampaignActivityInstance->SCHEDULED_ON_SPECIFIC_DATE) {
            $nextTime = date('Y-m-d H:i:s', strtotime('+10 year'));
        }

        if ($scheduleType == $CampaignActivityInstance->SCHEDULED_MONTHLY_BY_DATE) {
            $nextTime = CampaignActivity_Module_Model::getNextTriggerTimeForMonthlyByDate($schdayofmonth, $schtime);
        }

//		if($scheduleType == $CampaignActivityInstance->SCHEDULED_MONTHLY_BY_WEEKDAY) {
//			$nextTime = $this->getNextTriggerTimeForMonthlyByWeekDay($this->getWFScheduleDay(), $schtime);
//		}
//		if($scheduleType == $CampaignActivityInstance->SCHEDULED_ANNUALLY) {
//			$nextTime = $this->getNextTriggerTimeForAnnualDates($this->getWFScheduleAnnualDates(), $schtime);
//		}
        @date_default_timezone_set($default_timezone);
        return $nextTime;
    }

    /**
     * get next trigger time for daily
     * @param type $schTime
     * @return time
     */
    public function getNextTriggerTimeForDaily($scheduledTime) {
        global $log;
        $now = strtotime(date("Y-m-d H:i:s"));
        $todayScheduledTime = strtotime(date("Y-m-d H:i:s", strtotime($scheduledTime)));
        if ($now > $todayScheduledTime) {
            $nextTime = date("Y-m-d H:i:s", strtotime('+1 day ' . $scheduledTime));
        } else {
            $nextTime = date("Y-m-d H:i:s", $todayScheduledTime);
        }
        return $nextTime;
    }

    /**
     * get next trigger Time For weekly
     * @param type $scheduledDaysOfWeek
     * @param type $scheduledTime
     * @return <time>
     */
    public function getNextTriggerTimeForWeekly($scheduledDaysOfWeek, $scheduledTime, $isRequestFromSave = false) {
        $weekDays = array('1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday');
        $currentTime = time();
        $currentWeekDay = date('N', $currentTime);
        if ($scheduledDaysOfWeek) {
            if($isRequestFromSave)
            {
                $scheduledDaysOfWeek = explode(',', $scheduledDaysOfWeek);
            }
            else
            {
                $scheduledDaysOfWeek = Zend_Json::decode($scheduledDaysOfWeek);
            }
            
            if (is_array($scheduledDaysOfWeek)) {
                // algorithm :
                //1. First sort all the weekdays(stored as 0,1,2,3 etc in db) and find the closest weekday which is greater than currentWeekDay
                //2. If found, set the next trigger date to the next weekday value in the same week.
                //3. If not found, set the trigger date to the next first value.
                $nextTriggerWeekDay = null;
                sort($scheduledDaysOfWeek);
                foreach ($scheduledDaysOfWeek as $index => $weekDay) {
                    if ($weekDay == $currentWeekDay) { //if today is the weekday selected
                        $scheduleWeekDayInTime = strtotime(date('Y-m-d', strtotime($weekDays[$currentWeekDay])) . ' ' . $scheduledTime);
                        if ($currentTime < $scheduleWeekDayInTime) { //if the scheduled time is greater than current time, selected today
                            $nextTriggerWeekDay = $weekDay;
                            break;
                        } else {
                            //current time greater than scheduled time, get the next weekday
                            if (count($scheduledDaysOfWeek) == 1) { //if only one weekday selected, then get next week
                                $nextTime = date('Y-m-d', strtotime('next ' . $weekDays[$weekDay])) . ' ' . $scheduledTime;
                            } else {
                                $nextWeekDay = $scheduledDaysOfWeek[$index + 1]; // its the last day of the week i.e. sunday
                                if (empty($nextWeekDay)) {
                                    $nextWeekDay = $scheduledDaysOfWeek[0];
                                }
                                $nextTime = date('Y-m-d', strtotime('next ' . $weekDays[$nextWeekDay])) . ' ' . $scheduledTime;
                            }
                        }
                    } else if ($weekDay > $currentWeekDay) {
                        $nextTriggerWeekDay = $weekDay;
                        break;
                    }
                }

                if ($nextTime == null) {
                    if (!empty($nextTriggerWeekDay)) {
                        $nextTime = date("Y-m-d H:i:s", strtotime($weekDays[$nextTriggerWeekDay] . ' ' . $scheduledTime));
                    } else {
                        $nextTime = date("Y-m-d H:i:s", strtotime($weekDays[$scheduledDaysOfWeek[0]] . ' ' . $scheduledTime));
                    }
                }
            }
        }
        return $nextTime;
    }

    /**
     * get next triggertime for monthly
     * @param type $scheduledDayOfMonth
     * @param type $scheduledTime
     * @return <time>
     */
    public function getNextTriggerTimeForMonthlyByDate($scheduledDayOfMonth, $scheduledTime) {
        global $log;
        $currentDayOfMonth = date('j', time());
        if ($scheduledDayOfMonth) {
            $scheduledDaysOfMonth = $scheduledDayOfMonth;
            if (is_array($scheduledDaysOfMonth)) {
                // algorithm :
                //1. First sort all the days in ascending order and find the closest day which is greater than currentDayOfMonth
                //2. If found, set the next trigger date to the found value which is in the same month.
                //3. If not found, set the trigger date to the next month's first selected value.
                $nextTriggerDay = null;
                sort($scheduledDaysOfMonth);
                foreach ($scheduledDaysOfMonth as $day) {
                    if ($day == $currentDayOfMonth) {
                        $currentTime = time();
                        $schTime = strtotime($date = date('Y') . '-' . date('m') . '-' . $day . ' ' . $scheduledTime);
                        if ($schTime > $currentTime) {
                            $nextTriggerDay = $day;
                            break;
                        }
                    } elseif ($day > $currentDayOfMonth) {
                        $nextTriggerDay = $day;
                        break;
                    }
                }
                if (!empty($nextTriggerDay)) {
                    $firstDayofNextMonth = date('Y-m-d H:i:s', strtotime('first day of this month'));
                    $nextTime = date('Y-m-d', strtotime($firstDayofNextMonth . ' + ' . ($nextTriggerDay - 1) . ' days'));
                    $nextTime = $nextTime . ' ' . $scheduledTime;
                } else {
                    $firstDayofNextMonth = date('Y-m-d H:i:s', strtotime('first day of next month'));
                    $nextTime = date('Y-m-d', strtotime($firstDayofNextMonth . ' + ' . ($scheduledDaysOfMonth[0] - 1) . ' days'));
                    $nextTime = $nextTime . ' ' . $scheduledTime;
                }
            }
        }
        return $nextTime;
    }

    /**
     * to get next trigger time for weekday of the month
     * @param type $scheduledWeekDayOfMonth
     * @param type $scheduledTime
     * @return <time>
     */
    public function getNextTriggerTimeForMonthlyByWeekDay($scheduledWeekDayOfMonth, $scheduledTime) {
        $currentTime = time();
        $currentDayOfMonth = date('j', $currentTime);
        $scheduledTime = $this->getWFScheduleTime();
        if ($scheduledWeekDayOfMonth == $currentDayOfMonth) {
            $nextTime = date("Y-m-d H:i:s", strtotime('+1 month ' . $scheduledTime));
        } else {
            $monthInFullText = date('F', $currentTime);
            $yearFullNumberic = date('Y', $currentTime);
            if ($scheduledWeekDayOfMonth < $currentDayOfMonth) {
                $nextMonth = date("Y-m-d H:i:s", strtotime('next month'));
                $monthInFullText = date('F', strtotime($nextMonth));
            }
            $nextTime = date("Y-m-d H:i:s", strtotime($scheduledWeekDayOfMonth . ' ' . $monthInFullText . ' ' . $yearFullNumberic . ' ' . $scheduledTime));
        }
        return $nextTime;
    }

    /**
     * to get next trigger time
     * @param type $annualDates
     * @param type $scheduledTime
     * @return <time>
     */
    public function getNextTriggerTimeForAnnualDates($annualDates, $scheduledTime) {
        if ($annualDates) {
            $today = date('Y-m-d');
            $annualDates = Zend_Json::decode($annualDates);
            $nextTriggerDay = null;
            // sort the dates
            sort($annualDates);
            $currentTime = time();
            $currentDayOfMonth = date('Y-m-d', $currentTime);
            foreach ($annualDates as $day) {
                if ($day == $currentDayOfMonth) {
                    $schTime = strtotime($day . ' ' . $scheduledTime);
                    if ($schTime > $currentTime) {
                        $nextTriggerDay = $day;
                        break;
                    }
                } else if ($day > $today) {
                    $nextTriggerDay = $day;
                    break;
                }
            }
            if (!empty($nextTriggerDay)) {
                $nextTime = date('Y:m:d H:i:s', strtotime($nextTriggerDay . ' ' . $scheduledTime));
            } else {
                $j = 1;
                $currentDateTime = date('Y:m:d H:i:s', $currentTime);
                do {
                    for ($i = 0; $i < count($annualDates); $i++) {
                        $nextTriggerDay = $annualDates[$i];
                        $nextTime = date('Y:m:d H:i:s', strtotime($nextTriggerDay . ' ' . $scheduledTime . '+' . $j . ' year'));
                        if ($nextTime > $currentDateTime) {
                            break;
                        }
                    }
                    $j++;
                } while ($nextTime < $currentDateTime);
            }
        }
        return $nextTime;
    }

    /**
     * Function returns scheduled workflows
     * @param DateTime $referenceTime
     * @return Workflow
     */
    function getScheduledCampaignActivity($referenceTime = '') {
        global $adb, $log;
        $query = 'SELECT vtiger_campaignactivity.* FROM vtiger_campaignactivity'
                . ' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_campaignactivity.campaignactivityid'
                . ' INNER JOIN vtiger_campaign ON vtiger_campaign.campaignid = vtiger_campaignactivity.campaign_id'
                . ' INNER JOIN vtiger_crmentity vtiger_crmentity_campaign ON vtiger_crmentity_campaign.crmid = vtiger_campaign.campaignid'
                . ' WHERE vtiger_crmentity_campaign.deleted=0 AND vtiger_crmentity.deleted=0 AND vtiger_campaignactivity.campaign_activity_status = "Active" AND vtiger_campaignactivity.campaign_id != "" AND vtiger_campaignactivity.campaign_id IS NOT NULL';
        $params = array();
        if ($referenceTime != '') {
            $query .= " AND (vtiger_campaignactivity.nexttrigger_time IS NULL OR vtiger_campaignactivity.nexttrigger_time <= ?)";
            array_push($params, $referenceTime);
        }
        $campaignActivity = array();
        $campaignActivityResult = $adb->pquery($query, $params);
        $noOfcampaignActivity = $adb->num_rows($campaignActivityResult);
        for ($i = 0; $i < $noOfcampaignActivity; $i++) {
            $campaignActivity[$i]['campaignactivityid'] = $adb->query_result($campaignActivityResult, $i, 'campaignactivityid');
            $campaignActivity[$i]['subject'] = $adb->query_result($campaignActivityResult, $i, 'subject');
            $campaignActivity[$i]['activity_type'] = $adb->query_result($campaignActivityResult, $i, 'activity_type');
            $campaignActivity[$i]['campaign_activity_status'] = $adb->query_result($campaignActivityResult, $i, 'campaign_activity_status');
            $campaignActivity[$i]['description'] = $adb->query_result($campaignActivityResult, $i, 'description');
            $campaignActivity[$i]['nexttrigger_time'] = $adb->query_result($campaignActivityResult, $i, 'nexttrigger_time');
            $campaignActivity[$i]['campaign_id'] = $adb->query_result($campaignActivityResult, $i, 'campaign_id');
            $campaignActivity[$i]['campaign_activity_module'] = $adb->query_result($campaignActivityResult, $i, 'campaign_activity_module');
            $campaignActivity[$i]['campaign_activity_subject'] = $adb->query_result($campaignActivityResult, $i, 'campaign_activity_subject');
            $campaignActivity[$i]['campaign_activity_template'] = $adb->query_result($campaignActivityResult, $i, 'campaign_activity_template');
            
            $campaignActivity[$i]['schdate'] = $adb->query_result($campaignActivityResult, $i, 'schannualdates');
            $campaignActivity[$i]['schdate'] = str_replace('&quot;', '"', $campaignActivity[$i]['schdate']);
            
            $campaignActivity[$i]['schdayofmonth'] = $adb->query_result($campaignActivityResult, $i, 'schdayofmonth');
            $campaignActivity[$i]['schdayofmonth'] = str_replace('&quot;', '"', $campaignActivity[$i]['schdayofmonth']);
            $campaignActivity[$i]['schdayofmonth'] = Zend_Json::decode($campaignActivity[$i]['schdayofmonth']);
            $campaignActivity[$i]['schtime'] = $adb->query_result($campaignActivityResult, $i, 'schtime');
            
            $campaignActivity[$i]['schdayofweek'] = $adb->query_result($campaignActivityResult, $i, 'schdayofweek');
            $campaignActivity[$i]['schdayofweek'] = str_replace('&quot;', '"', $campaignActivity[$i]['schdayofweek']);
            $campaignActivity[$i]['schtypeid'] = $adb->query_result($campaignActivityResult, $i, 'schtypeid');
        }
        return $campaignActivity;
    }

    function getLeadData($campaignId = '') {
        global $adb, $log;
        $leadQuery = 'SELECT vtiger_leaddetails.leadid, vtiger_leaddetails.email FROM vtiger_leaddetails'
                . ' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid'
                . ' LEFT JOIN vtiger_campaignleadrel ON vtiger_campaignleadrel.leadid = vtiger_leaddetails.leadid'
                . ' WHERE vtiger_crmentity.deleted=0 AND vtiger_campaignleadrel.campaignid = ? AND vtiger_campaignleadrel.campaignrelstatusid = 1';
        $leadResult = $adb->pquery($leadQuery, array($campaignId));
        $noOfleads = $adb->num_rows($leadResult);
        $emailDetails = array();
        for ($i = 0; $i < $noOfleads; $i++) {
            $leadId = $adb->query_result($leadResult, $i, 'leadid');
            $email = $adb->query_result($leadResult, $i, 'email');
            $emailDetails[$leadId] = $email;
        }
        return $emailDetails;
    }

    function getContactData($campaignId = '') {
        global $adb, $log;
        $contactQuery = 'SELECT vtiger_contactdetails.contactid, vtiger_contactdetails.email FROM vtiger_contactdetails'
                . ' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid'
                . ' LEFT JOIN vtiger_campaigncontrel ON vtiger_campaigncontrel.contactid = vtiger_contactdetails.contactid'
                . ' WHERE vtiger_crmentity.deleted=0 AND vtiger_campaigncontrel.campaignid = ? AND vtiger_campaigncontrel.campaignrelstatusid = 1';
        $contactResult = $adb->pquery($contactQuery, array($campaignId));
        $noOfContacts = $adb->num_rows($contactResult);
        $emailDetails = array();
        for ($i = 0; $i < $noOfContacts; $i++) {
            $leadId = $adb->query_result($contactResult, $i, 'contactid');
            $email = $adb->query_result($contactResult, $i, 'email');
            $emailDetails[$leadId] = $email;
        }
        return $emailDetails;
    }

    function sendCampaignActivityEmail($emailData = array()) {
        require_once('modules/Emails/mail.php');
        require_once('modules/Emails/models/Mailer.php'); 
        global $log,$current_user,$HELPDESK_SUPPORT_EMAIL_ID,$HELPDESK_SUPPORT_NAME;
        $module = 'CampaignActivity';
        $from_email = $HELPDESK_SUPPORT_EMAIL_ID;
        $from_name = $HELPDESK_SUPPORT_NAME;
        $replyTo = $HELPDESK_SUPPORT_EMAIL_ID;
        $subject = $emailData['subject'];
        $content = $emailData['body'];
        $to_email = $emailData['to_email'];
        $entityId = $emailData['campaignactivityid'];
        $cc = '';
        $bcc = '';
        if (!empty($to_email)) {
            $moduleName = 'Emails';
            $userId = $current_user->id;
            $emailFocus = CRMEntity::getInstance($moduleName);
            $processedContent = Emails_Mailer_Model::getProcessedContent($content); // To remove script tags
            $mailerInstance = Emails_Mailer_Model::getInstance();
            $mailerInstance->isHTML(true);
            $processedContentWithURLS = $mailerInstance->convertToValidURL($processedContent);

            $emailFocus->column_fields['assigned_user_id'] = $userId;
            $emailFocus->column_fields['subject'] = $subject;
            $emailFocus->column_fields['description'] = decode_html($processedContentWithURLS);
            $emailFocus->column_fields['from_email'] = $from_email;
            $emailFocus->column_fields['saved_toid'] = $to_email;
            $emailFocus->column_fields['ccmail'] = $cc;
            $emailFocus->column_fields['bccmail'] = $bcc;
            $emailFocus->column_fields['parent_id'] = $entityId . "@$userId|";
            $emailFocus->column_fields['email_flag'] = 'SENT';
            $emailFocus->column_fields['activitytype'] = $moduleName;
            $emailFocus->column_fields['date_start'] = date('Y-m-d');
            $emailFocus->column_fields['time_start'] = date('H:i:s');
            $emailFocus->column_fields['mode'] = '';
            $emailFocus->column_fields['id'] = '';
            $emailFocus->save($moduleName);
            // To add entry in ModTracker
            $entityFocus = CRMEntity::getInstance($module);
            $entityFocus->retrieve_entity_info($entityId, $module);
            relateEntities($entityFocus, $module, $entityId, 'Emails', $emailFocus->id);

            //Including email tracking details
            $emailId = $emailFocus->id;
            $imageDetails = Vtiger_Functions::getTrackImageContent($emailId, $entityId);
            $content = $content . $imageDetails;

            if (stripos($content, '<img src="cid:logo" />')) {
                $mailerInstance->AddEmbeddedImage('layouts/v7/skins/images/logo_mail.jpg', 'logo', 'logo.jpg', "base64", "image/jpg");
            }

            //set properties
            $toEmail = trim($to_email, ',');
            if (!empty($toEmail)) {
                if (is_array($toEmail)) {
                    foreach ($toEmail as $email) {
                        $mailerInstance->AddAddress($email);
                    }
                } else {
                    $toEmails = explode(',', $toEmail);
                    foreach ($toEmails as $email) {
                        $mailerInstance->AddAddress($email);
                    }
                }
            }
            $mailerInstance->From = $from_email;
            $mailerInstance->FromName = decode_html($from_name);
            $mailerInstance->AddReplyTo($replyTo);
            $mailerInstance->Subject = strip_tags(decode_html($subject));
            $mailerInstance->Body = decode_emptyspace_html($content);
            $mailerInstance->Body = Emails_Mailer_Model::convertCssToInline($mailerInstance->Body);
            $mailerInstance->Body = Emails_Mailer_Model::makeImageURLValid($mailerInstance->Body);
            $emailRecord = Emails_Record_Model::getInstanceById($emailId);
            $mailerInstance->Body = $emailRecord->convertUrlsToTrackUrls($mailerInstance->Body, $entityId);
            $plainBody = decode_html($content);
            $plainBody = preg_replace(array("/<p>/i", "/<br>/i", "/<br \/>/i"), array("\n", "\n", "\n"), $plainBody);
            $plainBody = strip_tags($plainBody);
            $plainBody = Emails_Mailer_Model::convertToAscii($plainBody);
            $plainBody = $emailRecord->convertUrlsToTrackUrls($plainBody, $entityId, 'plain');
            $mailerInstance->AltBody = $plainBody;
            $mailerInstance->send(true);
            $error = $mailerInstance->getError();
            if (!empty($emailId)) {
                $emailFocus->setEmailAccessCountValue($emailId);
            }
            if ($error) {
                //If mail is not sent then removing the details about email
                $emailFocus->trash($moduleName, $emailId);
            }
        }
    }
    
    function updateCampaignActivityCronStatus($status = '', $campaignActivityId = '', $campaignId = '') {
        global $adb, $log;
        $params = array();
        if(!empty($status))
        {
            array_push($params, $status);
            if(!empty($campaignActivityId))
            {
                $where = ' campaignactivityid = ?';
                array_push($params, $campaignActivityId);
            }
            else if(!empty($campaignId))
            {
                $where = ' campaign_id = ?';
                array_push($params, $campaignId);
            }
            $cronQuery = 'UPDATE vtiger_campaignactivity SET campaign_activity_status = ? WHERE '. $where;
            $cronResult = $adb->pquery($cronQuery, $params);
        }
    }
    
    function getCampaignAnalytics($campaignId = '', $campaignActivityId = '') {
        global $adb, $log;
        $campaignAnalyticData = array();
        if(!empty($campaignId))
        {
            $extraWhere = '';
            $params = array($campaignId);
            if(!empty($campaignActivityId))
            {
               $extraWhere = ' AND vtiger_campaignactivity.campaignactivityid = ? ';
               array_push($params, $campaignActivityId);
            }
            $campaignQuery = 'select COUNT(vtiger_activity.activityid) total_email_sent, SUM(IF(vtiger_email_track.access_count>0,1,0)) opened_email, SUM(vtiger_email_track.click_count) clicked_email, (COUNT(vtiger_activity.activityid) - SUM(IF(vtiger_email_track.access_count>0,1,0))) unopened_email'
                    . ' FROM vtiger_activity'
                    . ' INNER JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid'
                    . ' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid'
                    . ' INNER JOIN vtiger_campaignactivity ON vtiger_campaignactivity.campaignactivityid = vtiger_seactivityrel.crmid'
                    . ' INNER JOIN vtiger_campaign ON vtiger_campaign.campaignid = vtiger_campaignactivity.campaign_id'
                    . ' INNER JOIN vtiger_email_track ON vtiger_email_track.mailid = vtiger_activity.activityid'
                    . ' WHERE vtiger_campaignactivity.campaign_id = ? ' . $extraWhere . 'AND vtiger_activity.activitytype = "Emails" AND vtiger_crmentity.deleted = 0';
            $campaignResult = $adb->pquery($campaignQuery, $params);
            $noOfCampaignEmail = $adb->num_rows($campaignResult);
            
            for ($i = 0; $i < $noOfCampaignEmail; $i++) {
                $totalEmailSent = $adb->query_result($campaignResult, 0, 'total_email_sent');
                $openedEmail = $adb->query_result($campaignResult, 0, 'opened_email');
                $clickedEmail = $adb->query_result($campaignResult, 0, 'clicked_email');
                $unOpenedEmail = $adb->query_result($campaignResult, 0, 'unopened_email');
                $campaignAnalyticData[] = $totalEmailSent;//total email sent
                $campaignAnalyticData[] = $openedEmail;//opened email
                $campaignAnalyticData[] = $clickedEmail;//click count
                $campaignAnalyticData[] = $unOpenedEmail;//unopened email
            }
        }
        return $campaignAnalyticData;
    }
    
    function getCampaignActivityList($campaignId = '') {
        global $adb, $log;
        $campaignActivityData = array();
        if(!empty($campaignId))
        {
            $campaignQuery = "SELECT vtiger_campaignactivity.*
			FROM vtiger_campaignactivity
			INNER JOIN vtiger_campaignactivitycf
				ON vtiger_campaignactivitycf.campaignactivityid = vtiger_campaignactivity.campaignactivityid
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid=vtiger_campaignactivity.campaignactivityid
			WHERE vtiger_campaignactivity.campaign_id=?
			AND vtiger_crmentity.deleted = 0";
            $campaignResult = $adb->pquery($campaignQuery, array($campaignId));
            $noOfCampaignEmail = $adb->num_rows($campaignResult);
            
            for ($i = 0; $i < $noOfCampaignEmail; $i++) {
                $campaignactivityid = $adb->query_result($campaignResult, $i, 'campaignactivityid');
                $subject = $adb->query_result($campaignResult, $i, 'subject');
                $campaignActivityData[$campaignactivityid] = $subject;
            }
        }
        return $campaignActivityData;
    }
}