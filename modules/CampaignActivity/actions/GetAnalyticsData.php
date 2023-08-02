<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
class CampaignActivity_GetAnalyticsData_Action extends Vtiger_Save_Action {
    public function process(Vtiger_Request $request)
    {
        global $log,$adb;
        $log->debug('Entering into CampaignActivity_GetAnalyticsData_Action function..');
        $log->debug($request);
        $campaignAnalyticsData = array();
        $campaignAnalyticsData['analytics_data'] = '';
        $campaignId = $request->get('campaign_id');
        $campaignActivityId = $request->get('campaign_activity_id');
        $campaignAnalyticData = CampaignActivity_Module_Model::getCampaignAnalytics($campaignId,$campaignActivityId);
        $campaignAnalyticFilterData = array_filter($campaignAnalyticData);
        if(!empty($campaignAnalyticFilterData))
        {
            $campaignAnalyticsData['analytics_data'] = json_encode($campaignAnalyticData);
        }
        
        $log->debug($campaignAnalyticsData);
        $response = new Vtiger_Response();
        $response->setResult($campaignAnalyticsData);
        $response->emit();
    }
}
