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
Comment:- MobileAPI Changes For Add Modules Action Permission
*/
include_once dirname(__FILE__) . '/Login.php';

class Mobile_WS_LoginAndFetchModules extends Mobile_WS_Login {

    function postProcess(Mobile_API_Response $response) {
        global $adb;
        $current_user = $this->getActiveUser();

        if ($current_user) {
            $result = $response->getResult();
            $userId = $current_user->id;
            $roleId = $current_user->roleid;

            $role2ProfileQuery = "SELECT profileid FROM `vtiger_role2profile`  WHERE `roleid` = ?";
            $role2ProfileResult = $adb->pquery($role2ProfileQuery, array($roleId));
            $role2ProfileRow = $adb->fetchByAssoc($role2ProfileResult);
            $profileId = $role2ProfileRow['profileid'];

            $result['modules'] = $this->getListing($current_user, $profileId);
            $response->setResult($result);
        }
    }

    function getListing($user, $profileId) {
        $modulewsids = Mobile_WS_Utils::getEntityModuleWSIds();

        // Disallow modules
        unset($modulewsids['Users']);

        // Calendar & Events module will be merged
        unset($modulewsids['Events']);

        $listresult = vtws_listtypes(null, $user);

        $listing = array();
        foreach ($listresult['types'] as $index => $modulename) {
            if (!isset($modulewsids[$modulename]))
                continue;
            $tabId = getTabid($modulename);
            $moduleActionPermission = $this->getModuleActionsAccess($profileId, $tabId);
            $listing[] = array(
                'id' => $modulewsids[$modulename],
                'name' => $modulename,
                'isEntity' => $listresult['information'][$modulename]['isEntity'],
                'label' => $listresult['information'][$modulename]['label'],
                'singular' => $listresult['information'][$modulename]['singular'],
                'actionsPermission' => $moduleActionPermission,
            );
        }

        return $listing;
    }

    function getModuleActionsAccess($profileId, $tabId) {
        global $adb,$log;$log->debug('Entering into getModuleActionsAccess..');
        $moduleActionsPermission = null;
        if (!empty($profileId)) {
            $profile_std_prmsion_qry = "SELECT * FROM `vtiger_profile2standardpermissions` WHERE profileid = ? AND tabid = ?";
            $profile_std_prmsion_result = $adb->pquery($profile_std_prmsion_qry, array($profileId, $tabId));

            /*Check sales subscription status*/
            $isLimitExceededStatus = false;
            $subscriptionData = getSubscriptionStatus();$log->debug('Entering into getSubscriptionStatus..');$log->debug($subscriptionData);
            if(is_array($subscriptionData) && !empty($subscriptionData))
            {
                $salesCrmStatusData = CheckSalesSubStatus($subscriptionData['subscription_key']);$log->debug($salesCrmStatusData);
                $saleCrmStatus = $salesCrmStatusData['result']['subscription_status'];
                if($saleCrmStatus === "Limit exceeded")
                {
                    $isLimitExceededStatus = true;
                }
            }
            /*Check sales subscription status*/

            while ($profile_std_prmsion_result_row = $adb->fetchByAssoc($profile_std_prmsion_result)) {
                $permission = true;
                if($profile_std_prmsion_result_row['permissions']){
                    $permission = false;
                }
                if ($profile_std_prmsion_result_row['operation'] == 1) {
                    $edit_permission = $permission;
                    $moduleActionsPermission['edit'] = $edit_permission;
                    if ($profile_std_prmsion_result_row['permissions'] == 0 && $tabId == 4) {
                        $moduleActionsPermission['changePortalPassword'] = $permission;
                        $moduleActionsPermission['resendPortalPassword'] = $permission;
                    } elseif ($profile_std_prmsion_result_row['permissions'] == 0 && $tabId == 51) {
                        $moduleActionsPermission['changePassword'] = $permission;
                        $moduleActionsPermission['changeInvestorPassword'] = $permission;
                    }
                } elseif ($profile_std_prmsion_result_row['operation'] == 2) {
                    $delete_permission = $permission;
                    $moduleActionsPermission['delete'] = $delete_permission;
                } elseif ($profile_std_prmsion_result_row['operation'] == 4) {
                    $view_permission = $permission;
                    $moduleActionsPermission['view'] = $view_permission;
                } elseif ($profile_std_prmsion_result_row['operation'] == 7) {
                    $create_permission = $permission;
                    $moduleActionsPermission['create'] = $create_permission;
                }
            }

            $profile_utlty_prmsion_qry = "SELECT * FROM `vtiger_profile2utility` WHERE profileid = ? AND tabid = ?";
            $profile_utlty_prmsion_result = $adb->pquery($profile_utlty_prmsion_qry, array($profileId, $tabId));
            while ($profile_utlty_prmsion_result_row = $adb->fetchByAssoc($profile_utlty_prmsion_result)) {
                $utility_permission = true;
                if($profile_utlty_prmsion_result_row['permissions']){
                    $utility_permission = false;
                }
                if ($profile_utlty_prmsion_result_row['activityid'] == 5) {
                    $import_permission = $utility_permission;
                    $moduleActionsPermission['import'] = $import_permission;
                } elseif ($profile_utlty_prmsion_result_row['activityid'] == 6) {
                    $export_permission = $utility_permission;
                    $moduleActionsPermission['export'] = $export_permission;
                } elseif ($profile_utlty_prmsion_result_row['activityid'] == 8) {
                    $merge_permission = $utility_permission;
                    $moduleActionsPermission['merge'] = $merge_permission;
                } elseif ($profile_utlty_prmsion_result_row['activityid'] == 10) {
                    $duplicate_permission = $utility_permission;
                    $moduleActionsPermission['duplicate'] = $duplicate_permission;
                } elseif ($profile_utlty_prmsion_result_row['activityid'] == 9) {
                    $leadconvert_permission = $utility_permission;
                    $moduleActionsPermission['leadconvert'] = $leadconvert_permission;
                    if($isLimitExceededStatus)
                    {
                        $moduleActionsPermission['leadconvert'] = false;
                    }
                }
            }
        }

        return $moduleActionsPermission;
    }

}
