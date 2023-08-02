<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class ContactsBeforeSaveHandler extends VTEventHandler {

    function handleEvent($eventName, $entityData) {
        global $log, $adb;
        
        $moduleName = $entityData->getModuleName();
        if ($eventName == 'vtiger.entity.beforesave' && $moduleName == 'Contacts') {
            
            $affiliate_code = $entityData->get('affiliate_code');
            $ibHierarchy = $entityData->get('ib_hierarchy');
            $recordId = $entityData->get('record_id');
            $parentAffiliateCode = $entityData->get('parent_affiliate_code');
            $isDisMaxCommAllow = $entityData->get('is_dist_max_comm');

            /* Add By Divyesh 
             * Comment:- Contact Creation restriction base on subscription package contact
            */
            if (empty($recordId)) {
                $errorMessage = "LBL_CONTACT_CREATION_EXCEEDED_ERROR";
                $contactCreationResult = contactCreationRestrictBaseOnPkg($errorMessage);
                $isEnableContactCreation = $contactCreationResult['success'];
                $message = $contactCreationResult['message'];
                if (!$isEnableContactCreation) {
                    throw new Exception($message);
                }
            }
            /*End*/

            if($isDisMaxCommAllow === 'Yes' && !empty($parentAffiliateCode))
            {
                $message = vtranslate('MAX_COMM_AND_PARENT_NOT_ALLOWED', $moduleName);
                throw new AppException($message);
            }
            
            /* Allow IB status changes */
            if (!empty($recordId)) {
                $statusChanged = $entityData->get('record_status');
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
                $currentStatus = $recordModel->get('record_status');
                if ($currentStatus == 'Approved' && ($statusChanged == 'Disapproved' || $statusChanged == 'Pending')) {
                    isAllowChangeInRecordStatus($affiliate_code, $recordId, $moduleName);
                }
            }
            /* Allow IB status changes */
            
            $country_code = str_replace('+', '', $entityData->get('country_code'));
            $country_code = trim($country_code);
            $entityData->set('country_code', '+' . $country_code);
            
            /*Validation: Parent Code should not form an existing downline Hierarchy*/
            if(!empty($recordId) && !empty($parentAffiliateCode))
            {
                $childContactids = fetchChildContactRecordIds($recordId);
                $parentContactId = getparentIdFromAffiliateCode($parentAffiliateCode);
                if(in_array($parentContactId, $childContactids))
                {
                    $message = vtranslate('PARENT_CODE_NOT_ALLOWED_FOR_CHILD', $moduleName);
                    throw new AppException($message);
                }
            }
            /*Validation: Parent Code should not form an existing downline Hierarchy*/
            if (empty($recordId)) {
                $affiliate_code = get_affiliate_code();
                $entityData->set('affiliate_code', $affiliate_code);
                // $entityData->set('record_status', 'Pending');
            }
        }
    }

}

?>
