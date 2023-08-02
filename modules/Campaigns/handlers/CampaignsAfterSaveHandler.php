<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class CampaignsAfterSaveHandler extends VTEventHandler {

    function handleEvent($eventName, $entityData) {
        global $log, $adb;
        $moduleName = $entityData->getModuleName();
        if ($eventName == 'vtiger.entity.aftersave' && $moduleName == 'Campaigns') {
            $recordId = $entityData->getId();
            $campaignStatus = $entityData->get('campaignstatus');
            if(!empty($campaignStatus) && in_array($campaignStatus, array('Cancelled', 'Inactive', 'Completed')))
            {
                CampaignActivity_Module_Model::updateCampaignActivityCronStatus($campaignStatus, '', $recordId);
            }
        }
    }

}

?>
