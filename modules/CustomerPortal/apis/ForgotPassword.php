<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_ForgotPassword extends CustomerPortal_API_Abstract {

    function process(CustomerPortal_API_Request $request) {
        global $adb, $PORTAL_URL, $current_user;
        $userId = $this->getCurrentPortalUser();
        $user = new Users();
        $current_user = $user->retrieveCurrentUserInfoFromFile($userId);
        $portal_language = 'en_us';

        $response = new CustomerPortal_API_Response();
        $mailid = $request->get('email');
        $current_date = date("Y-m-d");
        // $sql = 'SELECT * FROM vtiger_portalinfo
        // 			INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid=vtiger_portalinfo.id
        // 			INNER JOIN vtiger_customerdetails ON vtiger_customerdetails.customerid=vtiger_portalinfo.id
        // 			INNER JOIN vtiger_crmentity ON vtiger_portalinfo.id=vtiger_crmentity.crmid 
        // 				WHERE vtiger_portalinfo.user_name = ? AND vtiger_crmentity.deleted= ?
        // 				AND vtiger_customerdetails.support_start_date <= ?';
        // $res = $adb->pquery($sql, array($mailid, '0', $current_date));

        $sql = 'SELECT * FROM vtiger_portalinfo
					INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid=vtiger_portalinfo.id
					INNER JOIN vtiger_customerdetails ON vtiger_customerdetails.customerid=vtiger_portalinfo.id
					INNER JOIN vtiger_crmentity ON vtiger_portalinfo.id=vtiger_crmentity.crmid 
						WHERE vtiger_portalinfo.user_name = ? AND vtiger_crmentity.deleted= ?';

        $res = $adb->pquery($sql, array($mailid, '0'));
        $num_rows = $adb->num_rows($res);

        if ($num_rows > 0) {
            $isActive = $adb->query_result($res, 0, 'isactive');
            $support_end_date = $adb->query_result($res, 0, 'support_end_date');

            //if ($isActive && ($support_end_date >= $current_date || $support_end_date == null )) {
            if ($isActive) {
                $moduleName = 'Contacts';
                global $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
                $user_name = $adb->query_result($res, 0, 'user_name');
                $contactId = $adb->query_result($res, 0, 'id');


                if (!empty($adb->query_result($res, 0, 'cryptmode'))) {
                    $password = makeRandomPassword();
                    $enc_password = Vtiger_Functions::generateEncryptedPassword($password);

                    //***********   Plain password update to contactdetails *******************
                    if (configvar('is_portal_password_enable')) {
                        try {
                            $recordId = '12x' . $contactId;
                            if (vtws_recordExists($recordId)) {
                                $this->recordValues = vtws_retrieve($recordId, $current_user);

                                // Setting missing mandatory fields for record.
                                $describe = vtws_describe($moduleName, $current_user);
                                $mandatoryFields = CustomerPortal_Utils:: getMandatoryFields($describe);
                                foreach ($mandatoryFields as $fieldName => $type) {
                                    if (!isset($this->recordValues[$fieldName])) {
                                        if ($type['name'] == 'reference') {
                                            $crmId = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $module);
                                            $wsId = vtws_getWebserviceEntityId($type['refersTo'][0], $crmId);
                                            $this->recordValues[$fieldName] = $wsId;
                                        } else {
                                            $this->recordValues[$fieldName] = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $module);
                                        }
                                    }
                                }
                                if (isset($this->recordValues['id'])) {
                                    if ($moduleName == 'Contacts') {
                                        $this->recordValues['plain_password'] = $password;                                        
                                        $updatedStatus = vtws_update($this->recordValues, $current_user);                                        
                                        if ($updatedStatus['id'] == $recordId) {
                                            $sql = 'UPDATE vtiger_portalinfo SET user_password=?, cryptmode=? WHERE id=?';
                                            $params = array($enc_password, 'CRYPT', $contactId);
                                            $adb->pquery($sql, $params);
                                        } else {
                                            $response->setError("CAB_MSG_RECORD_DOES_NOT_EXIST", vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', 'Contacts', $portal_language));
                                        }
                                    }
                                }
                            } else {
                                $response->setError("CAB_MSG_RECORD_DOES_NOT_EXIST", vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', 'Contacts', $portal_language));
                            }
                        } catch (Exception $e) {
                            $response->setError($e->getCode(), $e->getMessage());
                        }
                    } else {
                        $sql = 'UPDATE vtiger_portalinfo SET user_password=?, cryptmode=? WHERE id=?';
                        $params = array($enc_password, 'CRYPT', $contactId);
                        $adb->pquery($sql, $params);
                    }
                    //********************   End  **********************
                }

                /* $portalURL = vtranslate('Please ', $moduleName).'<a href="'.$PORTAL_URL.'" style="font-family:Arial, Helvetica, sans-serif;font-size:13px;">'.vtranslate('click here', $moduleName).'</a>';
                  $contents = '<table><tr><td>
                  <strong>Dear '.$adb->query_result($res, 0, 'firstname')." ".$adb->query_result($res, 0, 'lastname').'</strong><br></td></tr><tr>
                  <td>'.vtranslate('Here is your self service portal login details:', $moduleName).'</td></tr><tr><td align="center"><br><table style="border:2px solid rgb(180,180,179);background-color:rgb(226,226,225);" cellspacing="0" cellpadding="10" border="0" width="75%"><tr>
                  <td><br>'.vtranslate('User ID').' : <font color="#990000"><strong><a target="_blank">'.$user_name.'</a></strong></font></td></tr><tr>
                  <td>'.vtranslate('Password').' : <font color="#990000"><strong>'.$password.'</strong></font></td></tr><tr>
                  <td align="center"><strong>'.$portalURL.'</strong></td>
                  </tr></table><br></td></tr><tr><td><strong>NOTE: </strong>'.vtranslate('We suggest you to change your password after logging in first time').'.<br>
                  </td></tr></table>';
                 */
                //Custom Template
                $company_id = vtws_getCompanyId();
                $companyDetails = vtws_retrieve($company_id, $current_user);
                $contact_details = array(
                    'firstname' => $adb->query_result($res, 0, 'firstname'),
                    'lastname' => $adb->query_result($res, 0, 'lastname'),
                    'portalURL' => $portalURL,
                    'user_name' => $user_name,
                    'password' => $password
                );

                $contents = $this->getForgotPassTemplate($companyDetails, $moduleName, $contact_details);
                //END			
                //$subject = 'Customer Portal Login Details';
                $subject = 'Cabinet Login Details';
                $subject = decode_html(getMergedDescription($subject, $contactId, $moduleName));


                $mailStatus = send_mail($moduleName, $user_name, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $contents, '', '', '', '', '', true);
                $ret_msg = vtranslate('LBL_MAIL_COULDNOT_SENT', 'HelpDesk', $portal_language);

                if ($mailStatus) {
                    $ret_msg = vtranslate('LBL_MAIL_SENT', 'HelpDesk', $portal_language);
                }
                $response->setResult($ret_msg);
            } else if ($isActive && $support_end_date <= $current_date) {
                throw new Exception('Access to the portal was disabled on ' . $support_end_date, 1413);
            } else if ($isActive == 0) {
                throw new Exception('Portal access has not been enabled for this account.', 1414);
            }
        } else {
            $response->setError('1412', 'Email id does not exist in our system.');
        }
        return $response;
    }

    function authenticatePortalUser($username, $password) {
        // always return true
        return true;
    }

    public function getCurrentPortalUser() {
        $db = PearDatabase::getInstance();

        $result = $db->pquery("SELECT prefvalue FROM vtiger_customerportal_prefs WHERE prefkey = 'userid' AND tabid = 0", array());
        if ($db->num_rows($result)) {
            return $db->query_result($result, 0, 'prefvalue');
        }
        return false;
    }

    function getForgotPassTemplate($companyDetails, $moduleName, $contact_details) {
        global $adb,$site_URL, $HELPDESK_SUPPORT_EMAIL_ID;

        $currentYear = date('Y');

        $address = '';
        $city = '';
        $state = '';
        $country = '';
        $pincode = '';
        $phone = '';
        $fax = '';
        $website = '';
        $logoname = '';
        $company_name = '';
        $website = '';
        $firstname = '';
        $lastname = '';
        $portalURL = '';
        $password = '';

        if (!empty($companyDetails) && !empty($contact_details)) {
            $address = $companyDetails['address'];
            $city = $companyDetails['city'];
            $state = $companyDetails['state'];
            $country = $companyDetails['country'];
            $pincode = $companyDetails['pincode'];
            $phone = $companyDetails['phone'];
            $fax = $companyDetails['fax'];
            $website = $companyDetails['website'];
            $logoname = $companyDetails['logoname'];
            $company_name = $companyDetails['organizationname'];

            $firstname = $contact_details['firstname'];
            $lastname = $contact_details['lastname'];
            $portalURL = $contact_details['portalURL'];
            $password = $contact_details['password'];
        }

        
        $templName = 'Cabinet Forgot Password EmailTemplate';
        $templsql = "SELECT subject,body FROM vtiger_emailtemplates WHERE templatename LIKE '%$templName%'";
        $templates = $adb->pquery($templsql);
        //$subject = $adb->query_result($templates, 0, 'subject');
        $body = $adb->query_result($templates, 0, 'body');
        $body = str_replace('$site_URL$', $site_URL, $body);
        $body = str_replace('$firstname$', $firstname, $body);
        $body = str_replace('$lastname$', $lastname, $body);
        $body = str_replace('$password$', $password, $body);
        $body = str_replace('$supportEmailId$', $supportEmailId, $body);
        $body = str_replace('$company_name$', $company_name, $body);
        $body = str_replace('$address$', $address, $body);
        $body = str_replace('$city$', $city, $body);
        $body = str_replace('$state$', $state, $body);
        $body = str_replace('$country$', $country, $body);
        $body = str_replace('$phone$', $phone, $body);
        $body = str_replace('$country$', $country, $body);
        $body = str_replace('$HELPDESK_SUPPORT_EMAIL_ID$', $HELPDESK_SUPPORT_EMAIL_ID, $body);
        $body = str_replace('$website$', $website, $body);
        $body = str_replace('$currentYear$', $currentYear, $body);
        $body = getReplacedDescription($body);
        $body= decode_html($body);

        //echo $body;  exit;
        return $body;
    }

    /**
    * This function is used to replace reset password email template content
    * @param string $description
    * @return string $description
    */
   /*function getReplacedDescription($description)
   {
       $token_data_pair = $tokenDataPair = explode('$', $description);
       $fields = Array();
       for ($i = 1; $i < count($token_data_pair); $i++) {
               $module = explode('-', $tokenDataPair[$i]);
               $fields[$module[0]][] = $module[1];
    }
       if (is_array($fields['custom']) && count($fields['custom']) > 0) {
               $description = Vtiger_Functions::getMergedDescriptionCustomVars($fields, $description);
       }
       if(is_array($fields['companydetails']) && count($fields['companydetails']) > 0){
               $description = Vtiger_Functions::getMergedDescriptionCompanyDetails($fields,$description);
       }
       return $description;
   }*/

}
