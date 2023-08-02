<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

/*
Added By:-  DivyeshChothani
Comment:- MobileAPI Changes For Add Headers base on crm filter
*/

class Mobile_WS_ListChildIBCommProfile extends Mobile_WS_Controller {

    function isCalendarModule($module) {
        return ($module == 'Events' || $module == 'Calendar');
    }

    function getSearchFilterModel($module, $search) {
        return Mobile_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
    }

    function getPagingModel(Mobile_API_Request $request) {
        $page = $request->get('page', 0);
        return Mobile_WS_PagingModel::modelWithPageStart($page);
    }

    function process(Mobile_API_Request $request) {
        $current_user = $this->getActiveUser();
        $module = $request->get('module');
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $records = Mobile_WS_ListChildIBCommProfile::getIBCommProfileRecords();
        $response = new Mobile_API_Response();
        $response->setResult(array('records' => $records));
        return $response;
    }

    function getIBCommProfileRecords() {
        global $adb;
        
        $query = "SELECT vtiger_ibcommissionprofile.profile_name, vtiger_ibcommissionprofile.ibcomm_status, vtiger_ibcommissionprofile.ibcommissionprofileid FROM vtiger_ibcommissionprofile INNER JOIN vtiger_crmentity ON vtiger_ibcommissionprofile.ibcommissionprofileid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted=0 AND vtiger_ibcommissionprofile.ibcomm_status = 'Published' AND vtiger_ibcommissionprofile.ibcommissionprofileid > 0 ORDER BY vtiger_crmentity.modifiedtime";
        $result = $adb->pquery($query, array());
        $data = array();
        while ($result_row = $adb->fetchByAssoc($result)) {
            $data[] = $result_row;
        }
        return $data;
    }
}
