<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_VerifyOtp extends CustomerPortal_API_Abstract {
    
    function process(CustomerPortal_API_Request $request) {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $customerId = $this->getActiveCustomer()->id;
        $portal_language = $this->getActiveCustomer()->portal_language;
        $module = 'Contacts';
        $otp = $request->get('otp');
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
                if(isset($customerId) && !empty($customerId))
                {
                    if(empty($otp))
                    {
                        $response->setError(1501, vtranslate('CAB_ERROR_OTP_CANNOT_BLANK', $module, $portal_language));
                        return $response;
                    }

                    /*Check for valid OTP*/
                    $isValidOtp = CustomerPortal_Utils::isOTPValid($customerId, $otp, $otpType);
                    /*Check for valid OTP*/

                    if($isValidOtp)
                    {
                        $response->setResult(vtranslate('CAB_MSG_OTP_VERIFIED_SUCCESS', $module, $portal_language));
                    }
                    else
                    {
                        $response->setError(1501, vtranslate('CAB_ERROR_OTP_NOT_VALID', $module, $portal_language));
                    }
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
