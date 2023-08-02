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

// include_once dirname(__FILE__) . '/models/Alert.php';
// include_once dirname(__FILE__) . '/models/SearchFilter.php';
// include_once dirname(__FILE__) . '/models/Paging.php';

class Mobile_WS_CommentList extends Mobile_WS_Controller {

    // function isCalendarModule($module) {
    //     return ($module == 'Events' || $module == 'Calendar');
    // }

    // function getSearchFilterModel($module, $search) {
    //     return Mobile_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
    // }

    // function getPagingModel(Mobile_API_Request $request) {
    //     $page = $request->get('page', 0);
    //     return Mobile_WS_PagingModel::modelWithPageStart($page);
    // }

    function process(Mobile_API_Request $request) {
        echo "comment list in progress"; exit;
        $current_user = $this->getActiveUser();
        $module = $request->get('module');

        $filterId = $request->get('filterid');

        $page = $request->get('page', '1');
        $orderBy = $request->getForSql('orderBy');
        $sortOrder = $request->getForSql('sortOrder');

        $listViewFields = $this->getHeaderFields($module, $filterId);
        $fieldNameWithUitype = $listViewFields['fieldNameWithUitype'];
       
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $headerFieldModels = $moduleModel->getHeaderViewFieldsList();

        //$headerFields = array();
        //$fields = array();
        $headerFieldColsMap = array();

        //$nameFields = $moduleModel->getNameFields();
        // if (is_string($nameFields)) {
        //     $nameFieldModel = $moduleModel->getField($nameFields);
        //     $headerFields[] = $nameFields;
        //    $fields = array('name' => $nameFieldModel->get('name'), 'label' => vtranslate($nameFieldModel->get('label'), $module), 'fieldType' => $nameFieldModel->getFieldDataType());
        // } else if (is_array($nameFields)) {
        //     foreach ($nameFields as $nameField) {
        //         $nameFieldModel = $moduleModel->getField($nameField);
        //         $headerFields[] = $nameField;
        //         $fields[] = array('name' => $nameFieldModel->get('name'), 'label' => vtranslate($nameFieldModel->get('label'), $module), 'fieldType' => $nameFieldModel->getFieldDataType());
        //     }
        // }

        foreach ($headerFieldModels as $fieldName => $fieldModel) {
            // $headerFields[] = $fieldName;
            // $fields[] = array('name' => $fieldName, 'label' => vtranslate($fieldModel->get('label'), $module), 'fieldType' => $fieldModel->getFieldDataType());
            $headerFieldColsMap[$fieldModel->get('column')] = $fieldName;
        }

        if ($module == 'HelpDesk')
            $headerFieldColsMap['title'] = 'ticket_title';
        if ($module == 'Documents')
            $headerFieldColsMap['title'] = 'notes_title';

        $headerFields = $listViewFields['headerFields'];
        $listViewModel = Vtiger_ListView_Model::getInstance($module, $filterId, $headerFields);

        if (!empty($sortOrder)) {
            $listViewModel->set('orderby', $orderBy);
            $listViewModel->set('sortorder', $sortOrder);
        }

        $pagingModel = new Vtiger_Paging_Model();
        $pageLimit = $pagingModel->getPageLimit();
        $pagingModel->set('page', $page);
        $pagingModel->set('limit', $pageLimit + 1);

        $listViewEntries = $listViewModel->getListViewEntries($pagingModel);

        if (empty($filterId)) {
            $customView = new CustomView($module);
            $filterId = $customView->getViewId($module);
        }
        if ($listViewEntries) {
            foreach ($listViewEntries as $index => $listViewEntryModel) {
                $data = $listViewEntryModel->getRawData();
                $record = array('id' => $listViewEntryModel->getId());
                if ($module == 'Documents') {
                    $documentAttachmentDetails = Mobile_WS_ListModuleRecords::getDocumentAttachmentDetails($listViewEntryModel->getId());
                }
                //echo "<pre>"; print_r($fieldNameWithUitype); exit;
                foreach ($data as $i => $value) {

                    if (is_string($i)) {
                        // Transform header-field (column to fieldname) in response.
                        if (isset($headerFieldColsMap[$i])) {
                            $i = $headerFieldColsMap[$i];
                        }
                        if ($i == 'smownerid') {
                            $i = 'assigned_user_id';
                            $privilegesModel = Users_Privileges_Model::getInstanceById($value);
                            $recordModelData = $privilegesModel->getData();
                            $value = $recordModelData['first_name'] . ' ' . $recordModelData['last_name'];
                        }else if($fieldNameWithUitype[$i]['uitype'] == 10){
                            if($value && isRecordExists($value)){
                                $recordModelResult = Vtiger_Record_Model::getInstanceById($value);
                                $recordModelData = $recordModelResult->getData();
                                $value = $recordModelData['label'];
                            }else{
                                $value = '';
                            }
                        }else if($fieldNameWithUitype[$i]['uitype'] == 56){
                            $value = ($value) ? 'Yes' : 'No';
                        }
                        $record[$i] = decode_html($value);
                        if ($module == 'Documents') {
                            $record['filename'] = $documentAttachmentDetails['filename'];
                            $record['document_url'] = $documentAttachmentDetails['document_url'];
                            $record['attachmentid'] = $documentAttachmentDetails['id'];
                        }
                    }
                }
                $records[] = $record;
            }
        }

        $moreRecords = false;
        if (count($listViewEntries) > $pageLimit) {
            $moreRecords = true;
            array_pop($records);
        }

        $fields = $listViewFields['fields'];
        if ($module == 'Documents') {
            $fields[] = array('name' => 'filename', 'label' => vtranslate('File Name', $module), 'fieldType' => 'string');
            $fields[] = array('name' => 'document_url', 'label' => vtranslate('File Path', $module), 'fieldType' => 'string');
        }

        $response = new Mobile_API_Response();
        $response->setResult(array('records' => $records,
            'headers' => $fields,
            'selectedFilter' => $filterId,
            'nameFields' => $nameFields,
            'moreRecords' => $moreRecords,
            'orderBy' => $orderBy,
            'sortOrder' => $sortOrder,
            'page' => $page));
        return $response;
    }

    function processSearchRecordLabelForCalendar(Mobile_API_Request $request, $pagingModel = false) {
        $current_user = $this->getActiveUser();

        // Fetch both Calendar (Todo) and Event information
        $moreMetaFields = array('date_start', 'time_start', 'activitytype', 'location');
        $eventsRecords = $this->fetchRecordLabelsForModule('Events', $current_user, $moreMetaFields, false, $pagingModel);
        $calendarRecords = $this->fetchRecordLabelsForModule('Calendar', $current_user, $moreMetaFields, false, $pagingModel);

        // Merge the Calendar & Events information
        $records = array_merge($eventsRecords, $calendarRecords);

        $modifiedRecords = array();
        foreach ($records as $record) {
            $modifiedRecord = array();
            $modifiedRecord['id'] = $record['id'];
            unset($record['id']);
            $modifiedRecord['eventstartdate'] = $record['date_start'];
            unset($record['date_start']);
            $modifiedRecord['eventstarttime'] = $record['time_start'];
            unset($record['time_start']);
            $modifiedRecord['eventtype'] = $record['activitytype'];
            unset($record['activitytype']);
            $modifiedRecord['eventlocation'] = $record['location'];
            unset($record['location']);

            $modifiedRecord['label'] = implode(' ', array_values($record));

            $modifiedRecords[] = $modifiedRecord;
        }

        $response = new Mobile_API_Response();
        $response->setResult(array('records' => $modifiedRecords, 'module' => 'Calendar'));

        return $response;
    }

    function fetchRecordLabelsForModule($module, $user, $morefields = array(), $filterOrAlertInstance = false, $pagingModel = false) {
        if ($this->isCalendarModule($module)) {
            $fieldnames = Mobile_WS_Utils::getEntityFieldnames('Calendar');
        } else {
            $fieldnames = Mobile_WS_Utils::getEntityFieldnames($module);
        }

        if (!empty($morefields)) {
            foreach ($morefields as $fieldname)
                $fieldnames[] = $fieldname;
        }

        if ($filterOrAlertInstance === false) {
            $filterOrAlertInstance = Mobile_WS_SearchFilterModel::modelWithCriterias($module);
            $filterOrAlertInstance->setUser($user);
        }

        return $this->queryToSelectFilteredRecords($module, $fieldnames, $filterOrAlertInstance, $pagingModel);
    }

    function queryToSelectFilteredRecords($module, $fieldnames, $filterOrAlertInstance, $pagingModel) {

        if ($filterOrAlertInstance instanceof Mobile_WS_SearchFilterModel) {
            return $filterOrAlertInstance->execute($fieldnames, $pagingModel);
        }

        global $adb;

        $moduleWSId = Mobile_WS_Utils::getEntityModuleWSId($module);
        $columnByFieldNames = Mobile_WS_Utils::getModuleColumnTableByFieldNames($module, $fieldnames);

        // Build select clause similar to Webservice query
        $selectColumnClause = "CONCAT('{$moduleWSId}','x',vtiger_crmentity.crmid) as id,";
        foreach ($columnByFieldNames as $fieldname => $fieldinfo) {
            $selectColumnClause .= sprintf("%s.%s as %s,", $fieldinfo['table'], $fieldinfo['column'], $fieldname);
        }
        $selectColumnClause = rtrim($selectColumnClause, ',');

        $query = $filterOrAlertInstance->query();
        $query = preg_replace("/SELECT.*FROM(.*)/i", "SELECT $selectColumnClause FROM $1", $query);

        if ($pagingModel !== false) {
            $query .= sprintf(" LIMIT %s, %s", $pagingModel->currentCount(), $pagingModel->limit());
        }

        $prequeryResult = $adb->pquery($query, $filterOrAlertInstance->queryParameters());
        return new SqlResultIterator($adb, $prequeryResult);
    }

    /**
     * Function to get Image Details
     * @return <array> Image Details List
     */
    public function getDocumentAttachmentDetails($recordId) {
        global $site_URL;
        $db = PearDatabase::getInstance();
        $imageDetails = array();
        if ($recordId) {
            $sql = "SELECT vtiger_attachments.attachmentsid,vtiger_attachments.path,vtiger_attachments.name,vtiger_attachments.storedname, vtiger_crmentity.setype FROM vtiger_attachments INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid WHERE vtiger_crmentity.setype = ? and vtiger_seattachmentsrel.crmid = ? ORDER BY vtiger_crmentity.modifiedtime DESC LIMIT 1";

            $result = $db->pquery($sql, array('Documents Attachment', $recordId));
            $imageId = $db->query_result($result, 0, 'attachmentsid');
            $imagePath = $db->query_result($result, 0, 'path');
            $imageName = $db->query_result($result, 0, 'name');
            $storedName = $db->query_result($result, 0, 'storedname');
            $documentFolderPath = $imagePath . $imageId . '_' . $storedName;
            if (file_exists($documentFolderPath)) {
                $imageOriginalName = urlencode(decode_html($imageName));
                $documentPath = $site_URL . $documentFolderPath;
                if (!empty($imageName)) {
                    $imageDetails = array(
                        'id' => $imageId,
                        'filename' => $imageName,
                        'document_url' => $documentPath
                    );
                }
            }
        }
        return $imageDetails;
    }

    function getHeaderFields($moduleName, $filterId) {
        global $adb;
        $tabId = getTabid($moduleName);
        if (!empty($filterId)) {
            $whereCondition = "vtiger_cvcolumnlist.`cvid` = $filterId AND ";
        } else {
            $whereCondition = "vtiger_customview.`viewname` = 'All' AND ";
        }
        $query = "SELECT vtiger_cvcolumnlist.columnname	FROM `vtiger_cvcolumnlist` INNER JOIN vtiger_customview ON vtiger_customview.cvid =vtiger_cvcolumnlist.cvid WHERE " . $whereCondition . " vtiger_customview.entitytype = '$moduleName' ORDER BY vtiger_cvcolumnlist.columnindex ASC LIMIT 5 ";
        $result = $adb->pquery($query, array());
        $headerFields = array();
        $fieldNameWithUitype = array();
        while ($result_row = $adb->fetchByAssoc($result)) {
            $columnnameArray = explode(":", $result_row['columnname']);
            $fieldName = $columnnameArray[2];
            $headerFields[] = $fieldName;

            $query1 = "SELECT vtiger_field.fieldlabel,vtiger_field.uitype FROM vtiger_field WHERE vtiger_field.fieldname = '$fieldName' AND tabid = $tabId LIMIT 1";
            $result1 = $adb->pquery($query1, array());
            $result_row1 = $adb->fetchByAssoc($result1);
            $fieldLabel = $result_row1['fieldlabel'];
            $uiType = $result_row1['uitype'];

            $fieldNameWithUitype[$fieldName] = array('uitype' => $uiType);

            $fields[] = array('name' => $fieldName, 'label' => vtranslate($fieldLabel, $moduleName), 'fieldType' => 'string');
        }
        $finalData = array('headerFields' => $headerFields, 'fields' => $fields, 'fieldNameWithUitype' => $fieldNameWithUitype);
        return $finalData;
    }

}
