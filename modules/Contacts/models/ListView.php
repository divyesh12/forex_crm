<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_ListView_Model extends Vtiger_ListView_Model {
  
	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams) {
		$massActionLinks = parent::getListViewMassActions($linkParams);

		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$emailModuleModel = Vtiger_Module_Model::getInstance('Emails');

		if($currentUserModel->hasModulePermission($emailModuleModel->getId())) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_SEND_EMAIL',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerSendEmail("index.php?module='.$this->getModule()->getName().'&view=MassActionAjax&mode=showComposeEmailForm&step=step1","Emails");',
				'linkicon' => ''
			);
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		$SMSNotifierModuleModel = Vtiger_Module_Model::getInstance('SMSNotifier');
		if($SMSNotifierModuleModel && $currentUserModel->hasModulePermission($SMSNotifierModuleModel->getId())) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_SEND_SMS',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerSendSms("index.php?module='.$this->getModule()->getName().'&view=MassActionAjax&mode=showSendSMSForm","SMSNotifier");',
				'linkicon' => ''
			);
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		
		$moduleModel = $this->getModule();
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerTransferOwnership("index.php?module='.$moduleModel->getName().'&view=MassActionAjax&mode=transferOwnership")',
				'linkicon' => ''
			);
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $massActionLinks;
	}

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	function getListViewLinks($linkParams) {
		$links = parent::getListViewLinks($linkParams);

		$index=0;
		foreach($links['LISTVIEWBASIC'] as $link) {
			if($link->linklabel == 'Send SMS') {
				unset($links['LISTVIEWBASIC'][$index]);
			}
			$index++;
		}
		return $links;
	}

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		$db = PearDatabase::getInstance();

		$moduleName = $this->getModule()->get('name');
		$moduleFocus = CRMEntity::getInstance($moduleName);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$queryGenerator = $this->get('query_generator');
		$listViewContoller = $this->get('listview_controller');

		 $searchParams = $this->get('search_params');
         
		if(empty($searchParams)) {
			$searchParams = array();
		}
		$glue = "";
		if(count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
			$glue = QueryGenerator::$AND;
		}
		$queryGenerator->parseAdvFilterList($searchParams, $glue);

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if(!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}

		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');

		if(!empty($orderBy)){
			$queryGenerator = $this->get('query_generator');
			$fieldModels = $queryGenerator->getModuleFields();
			$orderByFieldModel = $fieldModels[$orderBy];
			if($orderByFieldModel && ($orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE ||
					$orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::OWNER_TYPE)){
				$queryGenerator->addWhereField($orderBy);
			}
		}
       
		$listQuery = $this->getQuery();
		$customReportRequest = $_REQUEST;
        
		$listQuery .= $this->getCustomWhereCondition($customReportRequest); //Add by Divyesh
		//echo "<pre>"; print_r($listQuery); exit;
		$sourceModule = $this->get('src_module');
		if(!empty($sourceModule)) {
			if(method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery,$this->get('relationId'));
				if(!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();
		$paramArray = array();

		if(!empty($orderBy) && $orderByFieldModel) {
			if($orderBy == 'roleid' && $moduleName == 'Users'){
				$listQuery .= ' ORDER BY vtiger_role.rolename '.' '. $sortOrder; 
			} else {
//				$listQuery .= ' ORDER BY ? '.$sortOrder;
//				array_push($paramArray, $queryGenerator->getOrderByColumn($orderBy));
                                $listQuery .= ' ORDER BY ' . $queryGenerator->getOrderByColumn($orderBy) . ' ' . $sortOrder;
			}

			if ($orderBy == 'first_name' && $moduleName == 'Users') {
				$listQuery .= ' , last_name '.' '. $sortOrder .' ,  email1 '. ' '. $sortOrder;
			} 
		} else if(empty($orderBy) && empty($sortOrder) && $moduleName != "Users"){
			//List view will be displayed on recently created/modified records
			$listQuery .= ' ORDER BY vtiger_crmentity.modifiedtime DESC';
		}

		$viewid = ListViewSession::getCurrentView($moduleName);
		if(empty($viewid)) {
			$viewid = $pagingModel->get('viewid');
		}
		$_SESSION['lvs'][$moduleName][$viewid]['start'] = $pagingModel->get('page');

		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

		$listQuery .= " LIMIT ?, ?";
		array_push($paramArray, $startIndex);
		array_push($paramArray, ($pageLimit+1));
		
		$listResult = $db->pquery($listQuery, $paramArray);
		//echo "<pre>"; print_r($listResult); exit;
		$listViewRecordModels = array();
		$listViewEntries =  $listViewContoller->getListViewRecords($moduleFocus,$moduleName, $listResult);

		$pagingModel->calculatePageRange($listViewEntries);

		if($db->num_rows($listResult) > $pageLimit){
			array_pop($listViewEntries);
			$pagingModel->set('nextPageExists', true);
		}else{
			$pagingModel->set('nextPageExists', false);
		}

		$index = 0;
		foreach($listViewEntries as $recordId => $record) {
			$rawData = $db->query_result_rowdata($listResult, $index++);
			$record['id'] = $recordId;
			$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
		}
		return $listViewRecordModels;
	}

    /**
     * @creator: Divyesh Chothani
     * @date: 14-11-2019
     * @comment: Add for get LiveAccount Based on Contacts 
     */
    function getCustomWhereCondition($customReportRequest) {
    	global $adb,$ibStatistics_reportId,$ibSummary_reportId,$associateAccount_reportId,$payment_reportId,$ibCommissionEarn_reportId,$ibCommissionAnalysis_reportId;
    		           
    	$custom_report = $customReportRequest['custom_report'];
    	$sourceModule = $customReportRequest['src_module'];
       	$sourceField = $customReportRequest['src_field'];
        $related_parent_module = $customReportRequest['related_parent_module'];
        $popupModule = $customReportRequest['module'];
        $view = $customReportRequest['view'];
        $reportRecordId = $customReportRequest['src_record'];

        $whereCondition = '';
       	if ($sourceModule == 'CustomReports' && $popupModule == 'Contacts' && $sourceField == 'child_contactid') {
       		$parent_contactid = $customReportRequest['parent_contactid'];
       		if ($parent_contactid) {
                    if($reportRecordId == $associateAccount_reportId)
                    {
                        $child_contactids = array();
                        $childContactIds = fetchChildContactRecordIds($parent_contactid);
                        if(count($childContactIds) > 0) {
                            $child_contactids = $childContactIds;
                        }
                    }
                    else
                    {
                        $childContactIds = getChildContactRecordId($parent_contactid);
                        if(count($childContactIds) > 0) {
                            $child_contactids = $childContactIds;
                        } else {
                            $child_contactids = array($parent_contactid);	
                        }
                    }
	            
	            $string_child_contactids = implode(',', $child_contactids);
	       		$whereCondition = ' AND vtiger_contactdetails.contactid IN ('.$string_child_contactids.') ';
       		} 
       		
        }
        if($sourceModule == 'CustomReports' && $popupModule == 'Contacts' && ($reportRecordId == $ibCommissionEarn_reportId || $reportRecordId == $payment_reportId || $reportRecordId == $ibStatistics_reportId || $reportRecordId == $ibSummary_reportId || $reportRecordId == $associateAccount_reportId || $reportRecordId == $ibCommissionAnalysis_reportId) && $sourceField == 'contactid'){
       		$whereCondition = ' AND vtiger_contactdetails.record_status = "Approved" ';
	    }
		$listQuery .= $whereCondition;
        return $listQuery;
    }

    /**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewCount() {
		global $adb;
		$listQuery = $this->getQuery();
		$customReportRequest = $_REQUEST;
		$listQuery .= $this->getCustomWhereCondition($customReportRequest); //Add by Divyesh
		$res = $adb->pquery($listQuery, array());
 		$num_rows = $adb->num_rows($res);
 		$count = $num_rows;
		return $num_rows;
	}
}
/* END */