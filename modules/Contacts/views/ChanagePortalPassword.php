<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/* new file
 *  Add By Divyesh Chothani
 * Date:- 18-12-2019
 * Commnet:- Change Portal Password when login verified is false 
 */

class Contacts_ChanagePortalPassword_View extends Vtiger_IndexAjax_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('changePortalPassword');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    public function changePortalPassword(Vtiger_Request $request) {
        global $adb;
        $viewer = $this->getViewer($request);
        $module = $request->get('module');
        $recordId = $request->get('recordId');
        $viewer->assign('MODULE', $module);
        $viewer->assign('RECORD', $recordId);
        $viewer->view('ChangePortalPassword.tpl', $module);
    }

    public function getPortalPasswordContent($request) {
        require_once 'config.inc.php';
        global $PORTAL_URL, $HELPDESK_SUPPORT_EMAIL_ID, $site_URL;
        
        $adb = PearDatabase::getInstance();
        $moduleName = $request->get('module');
        $recordId = end(explode('x',$request->get('record')));
        
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
        $modelData = $recordModel->getData();
        $email = $modelData['email'];
        $firstname = $modelData['firstname'];
        $lastname = $modelData['lastname'];
        
        $companyDetails = getCompanyDetails();
        
        $portalURL = vtranslate('Please ', $moduleName) . '<a href="' . $PORTAL_URL . '" style="font-family:Arial, Helvetica, sans-serif;font-size:13px;">' . vtranslate('click here', $moduleName) . '</a>';

        //here id is hardcoded with 17. it is for vtiger_emailtemplates
        $query = 'SELECT vtiger_emailtemplates.subject,vtiger_emailtemplates.body FROM vtiger_emailtemplates WHERE templateid=17';

        $result = $adb->pquery($query, array());
        $body = decode_html($adb->query_result($result, 0, 'body'));
        $contents = $body;
        $contents = str_replace('$contacts-firstname$', $firstname, $contents);
        $contents = str_replace('$contacts-lastname$', $lastname, $contents);
        $contents = str_replace('$portal_new_password$', $request->get('new_password'), $contents);
        //$contents = str_replace('$URL$', $portalURL, $contents);
       // $contents = str_replace('$support_team$', getTranslatedString('Support Team', $moduleName), $contents);
        //$contents = str_replace('$logo$', '<img src="cid:logo" />', $contents);

        //Company Details
        $contents = str_replace('$address$', $companyDetails['address'], $contents);
        $contents = str_replace('$companyname$', $companyDetails['companyname'], $contents);
        $contents = str_replace('$phone$', $companyDetails['phone'], $contents);
        $contents = str_replace('$city$', $companyDetails['city'], $contents);
        $contents = str_replace('$state$', $companyDetails['state'], $contents);
        $contents = str_replace('$country$', $companyDetails['country'], $contents);
        $contents = str_replace('$companywebsite$', $companyDetails['website'], $contents);
        $contents = str_replace('$supportEmailId$', $HELPDESK_SUPPORT_EMAIL_ID, $contents);
        $contents = str_replace('$siteurl$', $site_URL, $contents);
        $contents = str_replace('$year$', date('Y'), $contents);

        $contents = getMergedDescription($contents, $recordModel->getId(), 'Contacts');
        $temp = $contents;
        $value["subject"] = decode_html($adb->query_result($result, 0, 'subject'));
        $value["body"] = $temp;
        
        return $value;
    }

}

/*  end */
