<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_TotalPaymentData_Dashboard extends Vtiger_IndexAjax_View {
	
	public function process(Vtiger_Request $request, $widget=NULL) {
		$current_user = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
        $dateRange = $request->get('date_range');//pr($dateRange);
        if(empty($dateRange))
		{
            if ($current_user->date_format == 'dd-mm-yyyy') {
                $dateRange = date('01-m-Y') . ',' . date('30-m-Y');
            } elseif ($current_user->date_format == 'mm-dd-yyyy') {
                $dateRange = date('m-01-Y') . ',' . date('m-30-Y');
            } elseif ($current_user->date_format == 'yyyy-mm-dd') {
                $dateRange = date('Y-m-01') . ',' . date('Y-m-30');
            }
		}

		/*set start and end time from date range */
        $createdtime = explode(",", $dateRange);
        if ($createdtime[0] != '') {
            // $startdateFormat = DateTime::createFromFormat($date_formate, $createdtime[0]);
            // $startDateTime = $startdateFormat->format('Y-m-d');
			$startDateTime = date('Y-m-d', strtotime($createdtime[0]));
        }
        $startDateTime = $startDateTime . ' 00:00:00';

        if ($createdtime[1] != '') {
            // $enddateFormat = DateTime::createFromFormat($date_formate, $createdtime[1]);
            // $endDateTime = $enddateFormat->format('Y-m-d');
			$endDateTime = date('Y-m-d', strtotime($createdtime[1]));
        }
        $endDateTime = $endDateTime . ' 23:59:59';
        /*set start and end time from date range */

		$widget = Vtiger_Widget_Model::getInstance($linkId, $current_user->getId());
		$widgetData = Vtiger_FetchPaymentData_Model::getTotalRecords($startDateTime, $endDateTime);
        // pr($widgetData);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('TOTALPAYMENTCONTENTS', $widgetData);
		$viewer->assign('DATE_RANGE_VALUE', $dateRange);
		$viewer->assign('USER_DATE_FORMAT', $current_user->date_format);
        
        $content = $request->get('content');
        if(!empty($content)) {
			$viewer->view('dashboards/TotalPaymentContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TotalPayment.tpl', $moduleName);
		}	
	}
	
}