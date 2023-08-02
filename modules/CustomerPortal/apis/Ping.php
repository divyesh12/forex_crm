<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class CustomerPortal_Ping extends CustomerPortal_API_Abstract {

	function process(CustomerPortal_API_Request $request) {
                $customerId = $this->getActiveCustomer()->id;
		//For mobile use
		$device_id = $request->get('device_id');
		$fcm = $request->get('fcm');
		$os = $request->get('os');
                global $resendOtpDuration,$adb; 
                $resendOtpDurationVal = $resendOtpDuration;
		if(!empty($device_id) && !empty($fcm) && !empty($os)){
                    $isLoginOtpEnable = configvar('mobile_login_otp');
                    $current_user = $this->getActiveUser();
                    if ($current_user) {
                            //Check device id is exist
                                    $sql = "SELECT * FROM `device_master` WHERE `device_id` = ?";
                                    $sqlResult = $adb->pquery($sql, array($device_id));
                        $numRow = $adb->num_rows($sqlResult);                
                        if($numRow > 0){
                                $updateSql = "UPDATE `device_master` SET `user_id` = ?, `fcm` = ?, `os` = ?, `modifiedtime` = UTC_TIMESTAMP() WHERE `device_id` = ? ";
                                                $sqlResult = $adb->pquery($updateSql, array($customerId, $fcm, $os, $device_id));
                        }else{
                                //Insert new user record
                                $sql = "INSERT INTO `device_master` (`user_id`, `device_id`, `fcm`, `os`, `modifiedtime`) 
                                VALUES (?,?,?,?,UTC_TIMESTAMP())";
                                                $sqlResult = $adb->pquery($sql, array($customerId, $device_id, $fcm, $os));
                        }                    
                    }
		}
                else
                {
                    $isLoginOtpEnable = configvar('cabinet_login_otp');
                    list($clientIp, $otherIp) = explode(',', $_SERVER['HTTP_CLIENTIP']);
                    if(isset($clientIp) && !empty($clientIp))
                    {
                        $updateIpSql = "UPDATE vtiger_contactdetails SET login_ip_address = ? WHERE contactid = ?;";
                        $updateIPResult = $adb->pquery($updateIpSql, array($clientIp, $customerId));
                    }
                }
                
                if($isLoginOtpEnable)
                {
                    /*Generate and Send OTP on email*/
                    if(isset($customerId) && !empty($customerId))
                    {
                        /*Check OTP already sent!*/
                        $isOtpAlreadySent = CustomerPortal_Utils::isOTPSentAlready($customerId, 0);
                        /*Check OTP already sent!*/

                        if(!$isOtpAlreadySent)
                        {
                            /*Generate OTP*/
                            $otp = CustomerPortal_Utils::generateOtp(4);
                            /*Generate OTP*/

                            /*Save OTP*/
                            if(!empty($otp))
                            {
                                $otpSaveResult = CustomerPortal_Utils::saveOtp($otp, $customerId, 0);
                            }
                            /*Save OTP*/
                        }
                        else
                        {
                            $otpSaveResult = true;
                            $otp = $isOtpAlreadySent;
                        }
                        /*Send OTP on email*/
                        if($otpSaveResult)
                        {
                            $recordModel = Vtiger_Record_Model::getInstanceById($customerId, 'Contacts');
                            $contactDetails = $recordModel->getData();

                            global $adb,$HELPDESK_SUPPORT_NAME,$HELPDESK_SUPPORT_EMAIL_ID;
                            $templName = 'Send Login OTP on email';
                            $templsql = "SELECT subject,body FROM vtiger_emailtemplates WHERE templatename LIKE '%$templName%'";
                            $templates = $adb->pquery($templsql);
                            $subject = $adb->query_result($templates, 0, 'subject');
                            $body = $adb->query_result($templates, 0, 'body');
                            $body = str_replace('$custom_OTP$', $otp, $body);
                            $body = str_replace('$contacts-firstname$', $contactDetails['firstname'], $body);
                            $body = str_replace('$contacts-lastname$', $contactDetails['lastname'], $body);
                            $body= decode_html(getMergedDescription($body, $customerId, 'Contacts'));
                            
                            send_mail('Contacts', $contactDetails['email'], $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $body, '', '', '', '', '', true);
                        }
                    }
                    /*Send OTP on email*/
                    /*Generate and Send OTP on email*/
                }
		//End
                $result = array('message' => vtranslate('login success', 'Contacts'), 'is_otp_enable' => $isLoginOtpEnable, 'resend_otp_duration' => $resendOtpDurationVal);
		$response = new CustomerPortal_API_Response();
		$response->setResult($result);
		return $response;
	}

}
