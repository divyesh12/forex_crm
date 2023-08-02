<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_SendOtp extends CustomerPortal_API_Abstract {

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        $portal_language = $this->getActiveCustomer()->portal_language;
        $customerId = $this->getActiveCustomer()->id;
        $module = 'Contacts';
        
        $otpTypeString = $request->get('type');
        try
        {
            if(!empty($otpTypeString))
            {
                $otpType = 0;
                if($otpTypeString == 'withdrawal')
                {
                    $otpType = 1;
                }
                /*Generate and Send OTP on email*/
                if(isset($customerId) && !empty($customerId))
                {
                    /*Check OTP already sent!*/
                    $isOtpAlreadySent = CustomerPortal_Utils::isOTPSentAlready($customerId, $otpType);
                    /*Check OTP already sent!*/

                    if(!$isOtpAlreadySent)
                    {
                        /*Generate OTP*/
                        $otp = CustomerPortal_Utils::generateOtp(4);
                        /*Generate OTP*/

                        /*Save OTP*/
                        if(!empty($otp))
                        {
                            $otpSaveResult = CustomerPortal_Utils::saveOtp($otp, $customerId, $otpType);
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
                        if($otpType == 1)
                        {
                            $templName = 'Send Withdrawal OTP on email';
                        }
                        
                        $templsql = "SELECT subject,body FROM vtiger_emailtemplates WHERE templatename LIKE '%$templName%'";
                        $templates = $adb->pquery($templsql);
                        $subject = $adb->query_result($templates, 0, 'subject');
                        $body = $adb->query_result($templates, 0, 'body');
                        $body = str_replace('$custom_OTP$', $otp, $body);
                        $body = str_replace('$contacts-firstname$', $contactDetails['firstname'], $body);
                        $body = str_replace('$contacts-lastname$', $contactDetails['lastname'], $body);
                        $body= decode_html(getMergedDescription($body, $customerId, 'Contacts'));
                        
                        send_mail('Contacts', $contactDetails['email'], $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $body, '', '', '', '', '', true);
                        
                        $response->setResult(vtranslate('CAB_MSG_OTP_SENT_SUCCESS', $module, $portal_language));
                    }
                    else
                    {
                        $response->setError(1501, vtranslate('CAB_ERROR_OTP_NOT_SAVED', $module, $portal_language));
                    }
                    /*Send OTP on email*/
                }
                else
                {
                    $response->setError(1501, vtranslate('CAB_ERROR_CONTACTID_CANNOT_BE_BLANK', $module, $portal_language));
                }
            }
        }
        catch (Exception $e)
        {
            $response->setError($e->getCode(), $e->getMessage());
        }
        return $response;
    }
}
