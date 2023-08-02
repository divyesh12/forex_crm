<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_Login_Action extends Vtiger_Action_Controller {

        function __construct() {
            $this->exposeMethod('verifyOtpProcess');
            $this->exposeMethod('resendOtpProcess');
        }
        
	function loginRequired() {
		return false;
	}

	function checkPermission(Vtiger_Request $request) {
		return true;
	} 
        
        function process(Vtiger_Request $request) {
            global $crmResendOtpDuration,$adb;
            $mode = $request->get('mode');
            if (!empty($mode))
            {
                $this->invokeExposedMethod($mode, $request);
                return;
            }
            $username = $request->get('username');
            $password = $request->getRaw('password');

            $user = CRMEntity::getInstance('Users');
            $user->column_fields['user_name'] = $username;

            if ($user->doLogin($password))
            {
                $isOTPEnable = configvar('crm_login_otp');
                if($isOTPEnable)
                {
                    $userId = $this->getUserId($username);
                    if($userId)
                    {
                        $sendOTPStatus = sendOtp($userId, true);
                        if($sendOTPStatus)
                        {
                            $request->set('username', $username);
                            header ('Location: index.php?module=Users&view=VerifyOtp&username='.$username);
                            exit;    
                        }
                        else
                        {
                            header ('Location: index.php?module=Users&parent=Settings&view=Login&error=loginOtpError&error_msg=CAB_ERROR_OTP_NOT_SAVED');
                            exit;
                        }
                    }
                    else
                    {
                        header ('Location: index.php?module=Users&parent=Settings&view=Login&error=login');
                        exit;
                    }
                }
                else
                {
                    $this->loginProcess($request);
                }
            }
            else
            {
                    header ('Location: index.php?module=Users&parent=Settings&view=Login&error=login');
                    exit;
            }
        }
        
        function postProcess(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('Footer.tpl', $moduleName);
	}
        
        function verifyOtpProcess(Vtiger_Request $request) {
            try
            {
                $response = new Vtiger_Response();
                $module = $request->getModule();
                $username = $request->get('username');
                $userId = $this->getUserId($username);
                $isVerify = verifyOtp($request, $userId);
                if($isVerify)
                {
                    $redirectUrl = $this->loginProcess($request, true);
                    $response->setResult(array('success' => true,'url' => $redirectUrl));
                }
                else
                {
                    $response->setError(vtranslate('OTP_NOT_VALID', $module));
                }
                $response->emit();
            } catch (AppException $ex) {
                throw new AppException($ex->getMessage(), $ex->getCode());
            }
        }
        
        function resendOtpProcess(Vtiger_Request $request) {
            try
            {
                $response = new Vtiger_Response();
                $username = $request->get('username');
                $userId = $this->getUserId($username);
                sendOtp($userId);
                $response->setResult(array('success' => true,'message' => vtranslate('OTP_SENT_SUCCESS', 'Users')));
                $response->emit();
            } catch (AppException $ex) {
                throw new AppException($ex->getMessage(), $ex->getCode());
            }
        }
        
	function loginProcess(Vtiger_Request $request, $isAjaxRequest = false) {
            $username = $request->get('username');

            $user = CRMEntity::getInstance('Users');
            $user->column_fields['user_name'] = $username;
            
            session_regenerate_id(true); // to overcome session id reuse.

            $userid = $user->retrieve_user_id($username);
            Vtiger_Session::set('AUTHUSERID', $userid);

            // For Backward compatability
            // TODO Remove when switch-to-old look is not needed
            $_SESSION['authenticated_user_id'] = $userid;
            $_SESSION['app_unique_key'] = vglobal('application_unique_key');
            $_SESSION['authenticated_user_language'] = vglobal('default_language');

            //Enabled session variable for KCFINDER 
            $_SESSION['KCFINDER'] = array(); 
            $_SESSION['KCFINDER']['disabled'] = false; 
            $_SESSION['KCFINDER']['uploadURL'] = "test/upload"; 
            $_SESSION['KCFINDER']['uploadDir'] = "../test/upload";
            $deniedExts = implode(" ", vglobal('upload_badext'));
            $_SESSION['KCFINDER']['deniedExts'] = $deniedExts;
            // End

            //Track the login History
            $moduleModel = Users_Module_Model::getInstance('Users');
            $moduleModel->saveLoginHistory($user->column_fields['user_name']);
            //End

            if(isset($_SESSION['return_params'])){
                    $return_params = $_SESSION['return_params'];
            }
            if($isAjaxRequest)
            {
                $returnUrl = 'index.php?module=Users&parent=Settings&view=SystemSetup';
                return $returnUrl;
            }
            header ('Location: index.php?module=Users&parent=Settings&view=SystemSetup');
            exit();
	}
        
        function getUserId($username = '') {
            global $adb;
            $userId = '';
            if(!empty($username))
            {
                $query = "SELECT id FROM vtiger_users WHERE user_name=?";
                $result = $adb->requirePsSingleResult($query, array($username), false);
                $userId = $adb->query_result($result, 0, 'id');   
            }
            return $userId;
        }
}
