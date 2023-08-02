<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

//require_once 'modules/Emails/mail.php';

class ContactsAfterSaveHandler extends VTEventHandler {

    function handleEvent($eventName, $entityData) {
        global $log, $adb;
        $moduleName = $entityData->getModuleName();
        // if ($eventName == 'vtiger.entity.aftersave.final') {
        if ($eventName == 'vtiger.entity.aftersave' && $moduleName == 'Contacts') {
            $log->debug('Entering into after save contact');
            $data = $entityData->getData();
            $recordId = $entityData->getId();
//            $affiliate_code = $entityData->get('affiliate_code');
            $parent_affiliate_code = $entityData->get('parent_affiliate_code');
            $child_ibcomm_profile = $entityData->get('child_ibcommissionprofileid');
            $parent_ibcomm_profile = $entityData->get('parent_ibcommissionprofileid');
            $currentIbHierarchy = $entityData->get('ib_hierarchy');

            $ib_hierarchy = generateHierarchy($parent_affiliate_code, $recordId, $child_ibcomm_profile, $parent_ibcomm_profile);
            $ib_depth = count(explode(":", $ib_hierarchy));
            $ib_level = $ib_depth - 1;

            $update_query = "UPDATE vtiger_contactdetails SET ib_hierarchy=?, ib_depth=? WHERE contactid=?";
            $update_params = array($ib_hierarchy, $ib_level, $recordId);
            $adb->pquery($update_query, $update_params);

            // other related update
            if (!empty($currentIbHierarchy) && $currentIbHierarchy != $ib_hierarchy) {
                $query = "UPDATE vtiger_contactdetails SET ib_hierarchy = REPLACE(ib_hierarchy , ?, ?) WHERE ib_hierarchy LIKE (?)";
                $result = $adb->pquery($query, array($currentIbHierarchy, $ib_hierarchy, $currentIbHierarchy . "%"));
            }
            
            $kycQuestionConfig = configvar('is_kyc_questionnarie_enable');
            if($data['mode'] == 'edit' && $kycQuestionConfig)
            {
                require_once 'data/VTEntityDelta.php';
                $entityDelta = new VTEntityDelta();
				$hasChanged = $entityDelta->hasChanged($entityData->getModuleName(), $recordId, 'kyc_form_edit');
				if($hasChanged)
                {
                    $log->debug('kyc questionary block');
                    $kycFormEdit = $entityData->get('kyc_form_edit');
                    $kycFormStatus = $entityData->get('kyc_form_status');
                    $kycUpdatedStatus = "Sent for edit";
                    if($kycFormEdit == 'on')
                    {
                        $kycUpdatedStatus = "Allow for edit";
                    }
                    if($kycFormStatus != "Allow for edit")
                    {
                        $updateQuery = "UPDATE vtiger_contactdetails SET kyc_form_status=? WHERE contactid=?";
                        $adb->pquery($updateQuery, array($kycUpdatedStatus,$recordId));
                    }
                }
            }
        }
    }

}

?>
