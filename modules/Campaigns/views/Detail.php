<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_Detail_View extends Vtiger_Detail_View {

        function preProcess(Vtiger_Request $request) {
            $viewer = $this->getViewer($request);
            $recordId = $request->get('record');
            $supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();
            $moduleList = array_keys($supportedModulesList);
            $viewer->assign('CAMPAIGN_ANALYTIC_WIDGET', false);
            $relatedModuleName = $request->get('relatedModule');
            if (in_array('CampaignActivity', $moduleList) || $relatedModuleName === 'Emails') {
                $campaignAnalyticDataJson = '';
                $viewer->assign('CAMPAIGN_ANALYTIC_WIDGET', true);
                $campaignAnalyticData = CampaignActivity_Module_Model::getCampaignAnalytics($recordId);
                $campaignAnalyticFilterData = array_filter($campaignAnalyticData);
                if(!empty($campaignAnalyticFilterData))
                {
                    $campaignAnalyticDataJson = json_encode($campaignAnalyticData);
                }
                $campaignActivityData = CampaignActivity_Module_Model::getCampaignActivityList($recordId);
                $viewer->assign('CAMPAIGN_ACTIVITY_DATA', $campaignActivityData);
                $viewer->assign('CAMPAIGN_ANALYTIC_DATA_JSON', $campaignAnalyticDataJson);
            }
            parent::preProcess($request);
        }
	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
				'modules.Vtiger.resources.List',
				"modules.$moduleName.resources.List",
				'modules.CustomView.resources.CustomView',
				"modules.$moduleName.resources.CustomView",
				"modules.Emails.resources.MassEdit",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}