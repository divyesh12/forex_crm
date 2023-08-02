<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class LiveAccount_ListView_Model extends Vtiger_ListView_Model {
 
    /**
     * Function to get the list of Mass actions for the module
     * @param <Array> $linkParams
     * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
     */
    public function getListViewMassActions($linkParams) {
        $massActionLinks = parent::getListViewMassActions($linkParams);

        //remove Edit and Delete Icon button from listing page
       unset($massActionLinks['LISTVIEWMASSACTION'][0], $massActionLinks['LISTVIEWMASSACTION'][1]);

        return $massActionLinks;
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
        //add By divyesh
        $customReportRequest = $_REQUEST;
        $listQuery .= $this->getCustomWhereCondition($customReportRequest); 
        //end
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
//                $listQuery .= ' ORDER BY ? '.$sortOrder;
//                array_push($paramArray, $queryGenerator->getOrderByColumn($orderBy));
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
        // echo "<pre>"; print_r($customReportRequest); exit;
        $sourceModule = $_REQUEST['src_module'];
        $sourceField = $_REQUEST['src_field'];
        $related_parent_module = $_REQUEST['related_parent_module'];
        $popupModule = $_REQUEST['module'];
        $view = $_REQUEST['view'];
        $reportRecordId = $customReportRequest['src_record'];

        $whereCondition = '';
        if ($sourceModule == 'CustomReports' && $related_parent_module == 'Contacts' && $popupModule == 'LiveAccount' && $sourceField == 'liveaccountid' ) {

            $moduleInstance = Vtiger_Module::getInstance($popupModule);
            $moduleBaseTable = $moduleInstance->basetable;

            $column_pattern = "/column/";
            $value_pattern = "/value/";
            $conditionArray = array();
            $count = 1;
            foreach ($_REQUEST as $column => $value) {
                if (preg_match($column_pattern, $column)) {
                    $conditionArray[$_REQUEST['column' . $count]] = $_REQUEST['value' . $count];
                    $count++;
                }
            }
            foreach ($conditionArray as $columnName => $value) {
                $whereCondition .= ' AND ' . $moduleBaseTable . '.' . $columnName . ' = ' . "'" . $value . "'";
            }
        }

        $listQuery .= $whereCondition;
        return $listQuery;
    }

    /**
     * @creator: Divyesh Chothani
     * @date: 14-11-2019
     * @comment: Add for get LiveAccount Based on Contacts 
     */
    function getQuery() {

        $sourceModule = $_REQUEST['src_module'];
        $sourceField = $_REQUEST['src_field'];
        $related_parent_module = $_REQUEST['related_parent_module'];
        $popupModule = $_REQUEST['module'];
        $view = $_REQUEST['view'];

        $whereCondition = '';
        if ($sourceModule == 'Payments' && $related_parent_module == 'Contacts' && $popupModule == 'LiveAccount' && $sourceField == 'liveaccountid' && $view == 'Popup') {
            $moduleInstance = Vtiger_Module::getInstance($popupModule);
            $moduleBaseTable = $moduleInstance->basetable;

            $column_pattern = "/column/";
            $value_pattern = "/value/";
            $conditionArray = array();
            $count = 1;
            foreach ($_REQUEST as $column => $value) {
                if (preg_match($column_pattern, $column)) {
                    $conditionArray[$_REQUEST['column' . $count]] = $_REQUEST['value' . $count];
                    $count++;
                }
            }
            foreach ($conditionArray as $columnName => $value) {
                $whereCondition .= ' AND ' . $moduleBaseTable . '.' . $columnName . ' = ' . "'" . $value . "'";
            }
        } else if ($sourceModule == 'LeverageHistory' && $related_parent_module == 'Contacts' && $popupModule == 'LiveAccount' && $sourceField == 'liveaccountid' && $view == 'Popup') {

            $moduleInstance = Vtiger_Module::getInstance($popupModule);
            $moduleBaseTable = $moduleInstance->basetable;

            $column_pattern = "/column/";
            $value_pattern = "/value/";
            $conditionArray = array();
            $count = 1;
            foreach ($_REQUEST as $column => $value) {
                if (preg_match($column_pattern, $column)) {
                    $conditionArray[$_REQUEST['column' . $count]] = $_REQUEST['value' . $count];
                    $count++;
                }
            }
            foreach ($conditionArray as $columnName => $value) {
                $whereCondition .= ' AND ' . $moduleBaseTable . '.' . $columnName . ' = ' . "'" . $value . "'";
            }
        }
        /* END */
        $queryGenerator = $this->get('query_generator');
        // Added to remove emails from the calendar list
        //$queryGenerator->addCondition("aaaaaaaaaaaa", "ttttttttt", 'e');
        //$queryGenerator->addCondition("aaaaaaa", "bbbbbbbbbb", 'e', 'AND');
        // $queryGenerator->addCondition('activitytype', 'Emails', 'n', 'AND');

        $listQuery = $queryGenerator->getQuery();
        $listQuery .= $whereCondition;
        //exit;
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
