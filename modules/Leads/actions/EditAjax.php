<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

Class Leads_EditAjax_Action extends Vtiger_IndexAjax_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('checkParentAffiliateCode');
        // $this->exposeMethod('checkFirstNameLastNameMobileNo');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    public function checkParentAffiliateCode(Vtiger_Request $request) {
        global $adb;
        $response = new Vtiger_Response();
        $parent_affiliate_code = $request->get('parent_affiliate_code');

        if ($parent_affiliate_code) {
            $query = "SELECT count(vtiger_contactdetails.contactid) as parent_code_exist FROM vtiger_contactdetails INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_contactdetails.contactid WHERE vtiger_crmentity.deleted = 0 AND vtiger_contactdetails.record_status = ?  AND vtiger_contactdetails.affiliate_code=?  LIMIT 1";
            $result = $adb->pquery($query, array('Approved', $parent_affiliate_code));
            $row_result = $adb->fetchByAssoc($result);
            $parent_code_exist = $row_result['parent_code_exist'];
            if ($parent_code_exist == 0) {
                $response->setResult(array('success' => true, 'message' => vtranslate('LBL_PLEASE_ADD_APPROVED_AFFILIATE_CODE', 'Contacts')));
            } else {
                $response->setResult(array('success' => false));
            }
        } else {
            $response->setResult(array('success' => false));
        }
        $response->emit();
    }

}
