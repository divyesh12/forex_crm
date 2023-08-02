<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
require_once('modules/ServiceProviders/ServiceProviders.php'); //add by divyesh
vimport('~~/modules/Reports/Reports.php');
vimport('~~/modules/Reports/ReportRun.php');
require_once('modules/Reports/ReportUtils.php');
require_once('Report.php');

class Reports_Record_Model extends Vtiger_Record_Model {

    /**
     * Function to get the id of the Report
     * @return <Number> - Report Id
     */
    public function getId() {
        return $this->get('reportid');
    }

    /**
     * Function to set the id of the Report
     * @param <type> $value - id value
     * @return <Object> - current instance
     */
    public function setId($value) {
        return $this->set('reportid', $value);
    }

    /**
     * Fuction to get the Name of the Report
     * @return <String>
     */
    function getName() {
        return $this->get('reportname');
    }

    /**
     * Function deletes the Report
     * @return Boolean
     */
    function delete() {
        return $this->getModule()->deleteRecord($this);
    }

    /**
     * Function to existing shared members of a report
     * @return type
     */
    public function getMembers() {
        if ($this->members == false) {
            $this->members = Settings_Groups_Member_Model::getAllByGroup($this, Settings_Groups_Member_Model::REPORTS_VIEW_MODE);
        }
        return $this->members;
    }

    /**
     * Function to get the detail view url
     * @return <String>
     */
    function getDetailViewUrl() {
        $module = $this->getModule();
        $reporttype = $this->get('reporttype');
        if ($reporttype == 'chart') {
            $view = 'ChartDetail';
        } else {
            $view = $module->getDetailViewName();
        }
        return 'index.php?module=' . $this->getModuleName() . '&view=' . $view . '&record=' . $this->getId();
    }

    /**
     * Function to get the edit view url
     * @return <String>
     */
    function getEditViewUrl() {
        $module = $this->getModule();
        $reporttype = $this->get('reporttype');
        if ($reporttype == 'chart') {
            $view = 'ChartEdit';
        } else {
            $view = $module->getEditViewName();
        }
        return 'index.php?module=' . $this->getModuleName() . '&view=' . $view . '&record=' . $this->getId();
    }

    /**
     * Funtion to get Duplicate Record Url
     * @return <String>
     */
    public function getDuplicateRecordUrl() {
        $module = $this->getModule();
        $reporttype = $this->get('reporttype');
        if ($reporttype == 'chart') {
            $view = 'ChartEdit';
        } else {
            $view = $module->getEditViewName();
        }
        return 'index.php?module=' . $this->getModuleName() . '&view=' . $view . '&record=' . $this->getId() . '&isDuplicate=true';
    }

    /**
     * Function returns the url that generates Report in Excel format
     * @return <String>
     */
    function getReportExcelURL() {
        return 'index.php?module=' . $this->getModuleName() . '&view=ExportReport&mode=GetXLS&record=' . $this->getId();
    }

    /**
     * Function returns the url that generates Report in CSV format
     * @return <String>
     */
    function getReportCSVURL() {
        return 'index.php?module=' . $this->getModuleName() . '&view=ExportReport&mode=GetCSV&record=' . $this->getId();
    }

    /**
     * Function returns the url that generates Report in printable format
     * @return <String>
     */
    function getReportPrintURL() {
        return 'index.php?module=' . $this->getModuleName() . '&view=ExportReport&mode=GetPrintReport&record=' . $this->getId();
    }

    /**
     * Function returns the Reports Model instance
     * @param <Number> $recordId
     * @param <String> $module
     * @return <Reports_Record_Model>
     */
	public static function getInstanceById($recordId, $module=null) {
        $db = PearDatabase::getInstance();

        $self = new self();
        $reportResult = $db->pquery('SELECT * FROM vtiger_report WHERE reportid = ?', array($recordId));
        if ($db->num_rows($reportResult)) {
            $values = $db->query_result_rowdata($reportResult, 0);
            $module = Vtiger_Module_Model::getInstance('Reports');
            $self->setData($values)->setId($values['reportid'])->setModuleFromInstance($module);
            $self->initialize();
        }
        return $self;
    }

    /**
     * Function creates Reports_Record_Model
     * @param <Number> $recordId
     * @return <Reports_Record_Model>
     */
    public static function getCleanInstance($recordId = null) {
        if (empty($recordId)) {
            $self = new Reports_Record_Model();
        } else {
            $self = self::getInstanceById($recordId);
        }
        $self->initialize();
        $module = Vtiger_Module_Model::getInstance('Reports');
        $self->setModuleFromInstance($module);
        return $self;
    }

    /**
     * Function initializes Report
     */
    function initialize() {
        $reportId = $this->getId();
        $this->report = Vtiger_Report_Model::getInstance($reportId);
    }

    /**
     * Function returns Primary Module of the Report
     * @return <String>
     */
    function getPrimaryModule() {
        return $this->report->primodule;
    }

    /**
     * Function returns Secondary Module of the Report
     * @return <String>
     */
    function getSecondaryModules() {
        return $this->report->secmodule;
    }

    /**
     * Function sets the Primary Module of the Report
     * @param <String> $module
     */
    function setPrimaryModule($module) {
        $this->report->primodule = $module;
    }

    /**
     * Function sets the Secondary Modules for the Report
     * @param <String> $modules, modules separated with colon(:)
     */
    function setSecondaryModule($modules) {
        $this->report->secmodule = $modules;
    }

    /**
     * Function returns Report Type(Summary/Tabular)
     * @return <String>
     */
    function getReportType() {
        $reportType = $this->get('reporttype');
        if (!empty($reportType)) {
            return $reportType;
        }
        return $this->report->reporttype;
    }

    /**
     * Returns the Reports Owner
     * @return <Number>
     */
    function getOwner() {
        return $this->get('owner');
    }

    /**
     * Function checks if the Report is editable
     * @return boolean
     */
    function isEditable() {
        return ($this->report->isEditable());
    }

    /**
     * Function returns Report enabled Modules
     * @return type
     */
    function getReportRelatedModules() {
        $report = $this->report;
        return $report->related_modules;
    }

    function getModulesList() {
        return $this->report->getModulesList();
    }

    /**
     * Function returns Primary Module Fields
     * @return <Array>
     */
    function getPrimaryModuleFields() {
        $report = $this->report;
        $primaryModule = $this->getPrimaryModule();
        $report->getPriModuleColumnsList($primaryModule);
        //need to add this vtiger_crmentity:crmid:".$module."_ID:crmid:I
        return $report->pri_module_columnslist;
    }

    /**
     * Function returns Secondary Module fields
     * @return <Array>
     */
    function getSecondaryModuleFields() {
        $report = $this->report;
        $secondaryModule = $this->getSecondaryModules();
        $report->getSecModuleColumnsList($secondaryModule);
        return $report->sec_module_columnslist;
    }

    /**
     * Function checks whether a non admin user is having permission to access record
     * and also function returns the list of shared records for a user, it parameter is true
     * @param type $getSharedReport
     * @return type
     */
    function isRecordHasViewAccess($reportType) {
        $db = PearDatabase::getInstance();
        $current_user = vglobal('current_user');
        if(strtolower($current_user->is_admin) == "on") {
            return true;
        }
        $params = array();
        $sql = ' SELECT vtiger_report.reportid,vtiger_report.reportname FROM vtiger_report ';
        require('user_privileges/user_privileges_' . $current_user->id . '.php');
        require_once('include/utils/GetUserGroups.php');
        $userGroups = new GetUserGroups();
        $userGroups->getAllUserGroups($current_user->id);
        $user_groups = $userGroups->user_groups;
        if (!empty($user_groups) && $reportType == 'Private') {
            $user_group_query = " (shareid IN (" . generateQuestionMarks($user_groups) . ") AND setype='groups') OR";
            array_push($params, $user_groups);
        }

        $non_admin_query = " vtiger_report.reportid IN (SELECT reportid FROM vtiger_reportsharing WHERE $user_group_query (shareid=? AND setype='users'))";
        if ($reportType == 'Private') {
            $sql .= " WHERE ( ( (" . $non_admin_query . ") OR vtiger_report.sharingtype='Public' OR "
                . "vtiger_report.owner = ? OR vtiger_report.owner IN (SELECT vtiger_user2role.userid "
                . "FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid "
                . "INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid "
                . "WHERE vtiger_role.parentrole LIKE '" . $current_user_parent_role_seq . "::%'))";
            array_push($params, $current_user->id);
            array_push($params, $current_user->id);
        }

        //Report sharing for vtiger7
        $queryObj = new stdClass();
        $queryObj->query = $sql;
        $queryObj->queryParams = $params;
        $queryObj = Reports::getReportSharingQuery($queryObj, $reportType);
        $sql = $queryObj->query . ' AND vtiger_report.reportid = ' . $this->getId();
        $params = $queryObj->queryParams;
        $result = $db->pquery($sql, $params);
        return $db->num_rows($result) > 0 ? true : false;
    }

    /**
     * Function returns Report Selected Fields
     * @return <Array>
     */
    function getSelectedFields() {
        $db = PearDatabase::getInstance();

        $result = $db->pquery("SELECT vtiger_selectcolumn.columnname FROM vtiger_report
                    INNER JOIN vtiger_selectquery ON vtiger_selectquery.queryid = vtiger_report.queryid
                    INNER JOIN vtiger_selectcolumn ON vtiger_selectcolumn.queryid = vtiger_selectquery.queryid
                    WHERE vtiger_report.reportid = ? ORDER BY vtiger_selectcolumn.columnindex", array($this->getId()));

        $selectedColumns = array();
        $primaryModule = $this->report->primodule;
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $column = $db->query_result($result, $i, 'columnname');
            list($tableName, $columnName, $moduleFieldLabel, $fieldName, $type) = split(':', $column);
            $fieldLabel = explode('_', $moduleFieldLabel);
            $module = $fieldLabel[0];
            $dbFieldLabel = trim(str_replace(array($module, '_'), " ", $moduleFieldLabel));
            $translatedFieldLabel = vtranslate($dbFieldLabel, $module);
            if ($module == 'Calendar') {
                if (CheckFieldPermission($fieldName, $module) == 'true' || CheckFieldPermission($fieldName, 'Events') == 'true') {
                    $selectedColumns[$module . '_' . $translatedFieldLabel] = $column;
                }
            } else if ($primaryModule == 'PriceBooks' && $fieldName == 'listprice' && in_array($module, array('Products', 'Services'))) {
                // to support pricebooks listprice in reports 
                $selectedColumns[$module . '_' . $translatedFieldLabel] = $column;
            } else if (CheckFieldPermission($fieldName, $module) == 'true') {
                // we should affix key with module name to differentiate same labels from diff modules
                $translatedFieldLabel = str_replace('"', "", $translatedFieldLabel);
                $translatedFieldLabel = str_replace("'", "", $translatedFieldLabel);
                $selectedColumns[$module . '_' . $translatedFieldLabel] = $column;
            }
        }
        return $selectedColumns;
    }

    /**
     * Function returns Report Calculation Fields
     * @return type
     */
    function getSelectedCalculationFields() {
        $db = PearDatabase::getInstance();

        $result = $db->pquery('SELECT vtiger_reportsummary.columnname FROM vtiger_reportsummary
                    INNER JOIN vtiger_report ON vtiger_report.reportid = vtiger_reportsummary.reportsummaryid
                    WHERE vtiger_report.reportid=?', array($this->getId()));

        $columns = array();
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $columns[] = $db->query_result($result, $i, 'columnname');
        }
        return $columns;
    }

    /**
     * Function returns Report Sort Fields
     * @return type
     */
    function getSelectedSortFields() {
        $db = PearDatabase::getInstance();

        //TODO : handle date fields with group criteria
        $result = $db->pquery('SELECT vtiger_reportsortcol.* FROM vtiger_report
                    INNER JOIN vtiger_reportsortcol ON vtiger_report.reportid = vtiger_reportsortcol.reportid
                    WHERE vtiger_report.reportid = ? ORDER BY vtiger_reportsortcol.sortcolid', array($this->getId()));

        $sortColumns = array();
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $column = $db->query_result($result, $i, 'columnname');
            $order = $db->query_result($result, $i, 'sortorder');
            $sortColumns[decode_html($column)] = $order;
        }
        return $sortColumns;
    }

    /**
     * Function returns Reports Standard Filters
     * @return type
     */
    function getSelectedStandardFilter() {
        $db = PearDatabase::getInstance();

        $result = $db->pquery('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid = ? AND startdate != ? AND enddate != ?', array($this->getId(), '0000-00-00', '0000-00-00'));
        $standardFieldInfo = array();
        if ($db->num_rows($result)) {
            $standardFieldInfo['columnname'] = $db->query_result($result, 0, 'datecolumnname');
            $standardFieldInfo['type'] = $db->query_result($result, 0, 'datefilter');
            $standardFieldInfo['startdate'] = $db->query_result($result, 0, 'startdate');
            $standardFieldInfo['enddate'] = $db->query_result($result, 0, 'enddate');

            if ($standardFieldInfo['type'] == "custom" || $standardFieldInfo['type'] == "") {
                if ($standardFieldInfo["startdate"] != "0000-00-00" && $standardFieldInfo["startdate"] != "") {
                    $startDateTime = new DateTimeField($standardFieldInfo["startdate"] . ' ' . date('H:i:s'));
                    $standardFieldInfo["startdate"] = $startDateTime->getDisplayDate();
                }
                if ($standardFieldInfo["enddate"] != "0000-00-00" && $standardFieldInfo["enddate"] != "") {
                    $endDateTime = new DateTimeField($standardFieldInfo["enddate"] . ' ' . date('H:i:s'));
                    $standardFieldInfo["enddate"] = $endDateTime->getDisplayDate();
                }
            } else {
                $startDateTime = new DateTimeField($standardFieldInfo["startdate"] . ' ' . date('H:i:s'));
                $standardFieldInfo["startdate"] = $startDateTime->getDisplayDate();
                $endDateTime = new DateTimeField($standardFieldInfo["enddate"] . ' ' . date('H:i:s'));
                $standardFieldInfo["enddate"] = $endDateTime->getDisplayDate();
            }
        }

        return $standardFieldInfo;
    }

    /**
     * Function returns Reports Advanced Filters
     * @return type
     */
    function getSelectedAdvancedFilter() {
        $report = $this->report;
        $report->getAdvancedFilterList($this->getId());
        return $report->advft_criteria;
    }

    /**
     * Function saves a Report
     */
    function save() {
        $db = PearDatabase::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();

        $reportId = $this->getId();

		//Newly created records are always as Private, only shared users can see report
		$sharingType = 'Private';
		
		$members = $this->get('members',array());
		
		if($members && count($members) == 1){
			if($members[0] == 'All::Users'){
        $sharingType = 'Public';
        }
		}

        if (empty($reportId)) {
            $reportId = $db->getUniqueID("vtiger_selectquery");
            $this->setId($reportId);

			$db->pquery('INSERT INTO vtiger_selectquery(queryid, startindex, numofobjects) VALUES(?,?,?)',
					array($reportId, 0, 0));

            $reportParams = array($reportId, $this->get('folderid'), $this->get('reportname'), $this->get('description'),
                $this->get('reporttype', 'tabular'), $reportId, 'CUSTOM', $currentUser->id, $sharingType);
            $db->pquery('INSERT INTO vtiger_report(reportid, folderid, reportname, description,
                                reporttype, queryid, state, owner, sharingtype) VALUES(?,?,?,?,?,?,?,?,?)', $reportParams);


            $secondaryModule = $this->getSecondaryModules();
			$db->pquery('INSERT INTO vtiger_reportmodules(reportmodulesid, primarymodule, secondarymodules) VALUES(?,?,?)',
					array($reportId, $this->getPrimaryModule(), $secondaryModule));

            $this->saveSelectedFields();

            $this->saveSortFields();

            $this->saveCalculationFields();

            $this->saveStandardFilter();

            $this->saveAdvancedFilters();

            $this->saveReportType();

            $this->saveSharingInformation();
        } else {

            $reportId = $this->getId();
            $db->pquery('DELETE FROM vtiger_selectcolumn WHERE queryid = ?', array($reportId));
            $this->saveSelectedFields();

            $db->pquery("DELETE FROM vtiger_reportsharing WHERE reportid = ?", array($reportId));
            $this->saveSharingInformation();


			$db->pquery('UPDATE vtiger_reportmodules SET primarymodule = ?,secondarymodules = ? WHERE reportmodulesid = ?',
					array($this->getPrimaryModule(), $this->getSecondaryModules(), $reportId));

            $db->pquery('UPDATE vtiger_report SET reportname = ?, description = ?, reporttype = ?, folderid = ?,sharingtype = ? WHERE
                reportid = ?', array(decode_html($this->get('reportname')), decode_html($this->get('description')),
					$this->get('reporttype'), $this->get('folderid'),$sharingType, $reportId));


            $db->pquery('DELETE FROM vtiger_reportsortcol WHERE reportid = ?', array($reportId));
			$db->pquery('DELETE FROM vtiger_reportgroupbycolumn WHERE reportid = ?',array($reportId));
            $this->saveSortFields();

            $db->pquery('DELETE FROM vtiger_reportsummary WHERE reportsummaryid = ?', array($reportId));
            $this->saveCalculationFields();

            $db->pquery('DELETE FROM vtiger_reportdatefilter WHERE datefilterid = ?', array($reportId));
            $this->saveStandardFilter();

            $this->saveReportType();

            $this->saveAdvancedFilters();
        }
    }

    /**
     * Function saves Reports Sorting Fields
     */
    function saveSortFields() {
        $db = PearDatabase::getInstance();

        $sortFields = $this->get('sortFields');

		if(!empty($sortFields)){
            $i = 0;
			foreach($sortFields as $fieldInfo) {
				$db->pquery('INSERT INTO vtiger_reportsortcol(sortcolid, reportid, columnname, sortorder) VALUES (?,?,?,?)',
						array($i, $this->getId(), $fieldInfo[0], $fieldInfo[1]));
				if(IsDateField($fieldInfo[0])) {
					if(empty($fieldInfo[2])){
                        $fieldInfo[2] = 'None';
                    }
                    $db->pquery("INSERT INTO vtiger_reportgroupbycolumn(reportid, sortid, sortcolname, dategroupbycriteria)
                        VALUES(?,?,?,?)", array($this->getId(), $i, $fieldInfo[0], $fieldInfo[2]));
                }
                $i++;
            }
        }
    }

    /**
     * Function saves Reports Calculation Fields information
     */
    function saveCalculationFields() {
        $db = PearDatabase::getInstance();

        $calculationFields = $this->get('calculationFields');
		for ($i=0; $i<count($calculationFields); $i++) {
			$db->pquery('INSERT INTO vtiger_reportsummary (reportsummaryid, summarytype, columnname) VALUES (?,?,?)',
					array($this->getId(), $i, $calculationFields[$i]));
        }
    }

    /**
     * Function saves Reports Standard Filter information
     */
    function saveStandardFilter() {
        $db = PearDatabase::getInstance();

        $standardFilter = $this->get('standardFilter');
        if (!empty($standardFilter)) {
            $db->pquery('INSERT INTO vtiger_reportdatefilter (datefilterid, datecolumnname, datefilter, startdate, enddate)
                            VALUES (?,?,?,?,?)', array($this->getId(), $standardFilter['field'], $standardFilter['type'],
                $standardFilter['start'], $standardFilter['end']));
        }
    }

    /**
     * Function saves Reports Sharing information
     */
    function saveSharingInformation() {
        $db = PearDatabase::getInstance();

        $reportId = $this->getId();
        $sharingInfo = $this->get('sharingInfo');
		for($i=0; $i<count($sharingInfo); $i++) {
			$db->pquery('INSERT INTO vtiger_reportsharing(reportid, shareid, setype) VALUES (?,?,?)',
					array($reportId, $sharingInfo[$i]['id'], $sharingInfo[$i]['type']));
        }

        //On every report save delete information from below tables and insert new to avoid 
        // confusion in updating
        $db->pquery('DELETE FROM vtiger_report_shareusers WHERE reportid=?', array($reportId));
        $db->pquery('DELETE FROM vtiger_report_sharegroups WHERE reportid=?', array($reportId));
        $db->pquery('DELETE FROM vtiger_report_sharerole WHERE reportid=?', array($reportId));
        $db->pquery('DELETE FROM vtiger_report_sharers WHERE reportid=?', array($reportId));

        $members = $this->get('members', array());
        if (!empty($members)) {
            $noOfMembers = count($members);
            for ($i = 0; $i < $noOfMembers; ++$i) {
                $id = $members[$i];
                $idComponents = Settings_Groups_Member_Model::getIdComponentsFromQualifiedId($id);
                if ($idComponents && count($idComponents) == 2) {
                    $memberType = $idComponents[0];
                    $memberId = $idComponents[1];

                    if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_USERS) {
                        $db->pquery('INSERT INTO vtiger_report_shareusers(userid, reportid) VALUES (?,?)', array($memberId, $reportId));
                    }
                    if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_GROUPS) {
                        $db->pquery('INSERT INTO vtiger_report_sharegroups(groupid, reportid) VALUES (?,?)', array($memberId, $reportId));
                    }
                    if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_ROLES) {
                        $db->pquery('INSERT INTO vtiger_report_sharerole(roleid, reportid) VALUES (?,?)', array($memberId, $reportId));
                    }
                    if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_ROLE_AND_SUBORDINATES) {
                        $db->pquery('INSERT INTO vtiger_report_sharers(rsid, reportid) VALUES (?,?)', array($memberId, $reportId));
                    }
                }
            }
        }
    }

    /**
     * Functions saves Reports selected fields
     */
    function saveSelectedFields() {
        $db = PearDatabase::getInstance();

        $selectedFields = $this->get('selectedFields');

		if(!empty($selectedFields)){
		   for($i=0 ;$i<count($selectedFields);$i++) {
				if(!empty($selectedFields[$i])) {
					$db->pquery("INSERT INTO vtiger_selectcolumn(queryid, columnindex, columnname) VALUES (?,?,?)",
							array($this->getId(), $i, decode_html($selectedFields[$i])));
                }
            }
        }
    }

    /**
     * Function saves Reports Filter information
     */
    function saveAdvancedFilters() {
        $db = PearDatabase::getInstance();

        $reportId = $this->getId();
        $advancedFilter = $this->get('advancedFilter');
        if (!empty($advancedFilter)) {

            $db->pquery('DELETE FROM vtiger_relcriteria WHERE queryid = ?', array($reportId));
            $db->pquery('DELETE FROM vtiger_relcriteria_grouping WHERE queryid = ?', array($reportId));

			foreach($advancedFilter as $groupIndex => $groupInfo) {
				if(empty($groupInfo)) continue;

                $groupColumns = $groupInfo['columns'];
                $groupCondition = $groupInfo['condition'];

				foreach($groupColumns as $columnIndex => $columnCondition) {
					if(empty($columnCondition)) continue;

                    $advFilterColumn = $columnCondition["columnname"];
                    $advFilterComparator = $columnCondition["comparator"];
                    $advFilterValue = $columnCondition["value"];
                    $advFilterColumnCondition = $columnCondition["column_condition"];

                    $columnInfo = explode(":", $advFilterColumn);
                    $moduleFieldLabel = $columnInfo[2];

                    list($module, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
                    $fieldInfo = getFieldByReportLabel($module, $fieldLabel);
                    $fieldType = null;
                    if (!empty($fieldInfo)) {
                        $field = WebserviceField::fromArray($db, $fieldInfo);
                        $fieldType = $field->getFieldDataType();
                    }

                    if ($fieldType == 'currency') {
                        if ($field->getUIType() == '72') {
                            // Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
                            $advFilterValue = Vtiger_Currency_UIType::convertToDBFormat($advFilterValue, null, true);
                        } else {
                            $advFilterValue = Vtiger_Currency_UIType::convertToDBFormat($advFilterValue);
                        }
                    }

                    $specialDateConditions = Vtiger_Functions::getSpecialDateTimeCondtions();
                    $tempVal = explode(",", $advFilterValue);
                    if (($columnInfo[4] == 'D' || ($columnInfo[4] == 'T' && $columnInfo[1] != 'time_start' && $columnInfo[1] != 'time_end') ||
                        ($columnInfo[4] == 'DT')) && ($columnInfo[4] != '' && $advFilterValue != '' ) && !in_array($advFilterComparator, $specialDateConditions)) {
                        $val = Array();
                        for ($i = 0; $i < count($tempVal); $i++) {
                            if (trim($tempVal[$i]) != '') {
                                $date = new DateTimeField(trim($tempVal[$i]));
                                if ($columnInfo[4] == 'D') {
                                    $val[$i] = DateTimeField::convertToDBFormat(trim($tempVal[$i]));
                                } elseif ($columnInfo[4] == 'DT') {
                                    /**
                                     * While generating query to retrieve report, for date time fields we are only taking
                                     * date field and appending '00:00:00' for correct results depending on time zone.
                                     * If you save the time also here by converting to db format, while showing in edit
                                     * view it was changing the date selected.
                                     */
                                    $values = explode(' ', $tempVal[$i]);
                                    $date = new DateTimeField($values[0]);
                                    $val[$i] = $date->getDBInsertDateValue();
                                } elseif ($fieldType == 'time') {
                                    $val[$i] = Vtiger_Time_UIType::getTimeValueWithSeconds($tempVal[$i]);
                                } else {
                                    $val[$i] = $date->getDBInsertTimeValue();
                                }
                            }
                        }
                        $advFilterValue = implode(",", $val);
                    }

                    $db->pquery('INSERT INTO vtiger_relcriteria (queryid, columnindex, columnname, comparator, value,
                        groupid, column_condition) VALUES (?,?,?,?,?,?,?)', array($reportId, $columnIndex, $advFilterColumn,
                        $advFilterComparator, $advFilterValue, $groupIndex, $advFilterColumnCondition));

                    // Update the condition expression for the group to which the condition column belongs
                    $groupConditionExpression = '';
                    if (!empty($advancedFilter[$groupIndex]["conditionexpression"])) {
                        $groupConditionExpression = $advancedFilter[$groupIndex]["conditionexpression"];
                    }
                    $groupConditionExpression = $groupConditionExpression . ' ' . $columnIndex . ' ' . $advFilterColumnCondition;
                    $advancedFilter[$groupIndex]["conditionexpression"] = $groupConditionExpression;
                }

                $groupConditionExpression = $advancedFilter[$groupIndex]["conditionexpression"];
				if(empty($groupConditionExpression)) continue; // Case when the group doesn't have any column criteria

				$db->pquery("INSERT INTO vtiger_relcriteria_grouping(groupid, queryid, group_condition, condition_expression) VALUES (?,?,?,?)",
						array($groupIndex, $reportId, $groupCondition, $groupConditionExpression));
            }
        }
    }

    /**
     * Function saves Reports Scheduling information
     */
    function saveScheduleInformation() {
        $db = PearDatabase::getInstance();

        $selectedRecipients = $this->get('selectedRecipients');
        $scheduledInterval = $this->get('scheduledInterval');
        $scheduledFormat = $this->get('scheduledFormat');

        $db->pquery('INSERT INTO vtiger_scheduled_reports(reportid, recipients, schedule, format, next_trigger_time) VALUES
            (?,?,?,?,?)', array($this->getId(), $selectedRecipients, $scheduledInterval, $scheduledFormat, date("Y-m-d H:i:s")));
    }

    /**
     * Function deletes report scheduling information
     */
    function deleteScheduling() {
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM vtiger_scheduled_reports WHERE reportid = ?', array($this->getId()));
    }

    /**
     * Function returns sql for the report
     * @param <String> $advancedFilterSQL
     * @param <String> $format
     * @return <String>
     */
    function getReportSQL($advancedFilterSQL = false, $format = false) {
        $reportRun = ReportRun::getInstance($this->getId());
        $sql = $reportRun->sGetSQLforReport($this->getId(), $advancedFilterSQL, $format);
        return $sql;
    }

    /**
     * Function returns sql for count query which don't need any fields
     * @param <String> $query (with all columns)
     * @return <String> $query (by removing all columns)
     */
    function generateCountQuery($query) {
        $from = preg_split("/ from /i", $query, 2);
        //If we select the same field in select and grouping/soring then it will include order by and query failure will happen
        $fromAndWhereQuery = preg_split('/ order by /i', $from[1]);
        $sql = "SELECT count(*) AS count FROM " . $fromAndWhereQuery[0];
        return $sql;
    }

    /**
     * Function returns report's data
     * @param <Vtiger_Paging_Model> $pagingModel
     * @param <String> $filterQuery
     * @return <Array>
     */
    function getReportData($pagingModel = false, $filterQuery = false) {
        $reportRun = ReportRun::getInstance($this->getId());
        $data = $reportRun->GenerateReport('PDF', $filterQuery, true, $pagingModel->getStartIndex(), $pagingModel->getPageLimit());
        return $data;
    }

    function getReportsCount($query = null) {
        if ($query == null)
            $query = $this->get('recordCountQuery');
        global $adb;
        $count = 0;
        $result = $adb->pquery($query, array());
        if ($adb->num_rows($result) > 0) {
            $count = $adb->query_result($result, 0, 'count');
        }
        return $count;
    }

    function getReportCalulationData($filterQuery = false) {
        $reportRun = ReportRun::getInstance($this->getId());
        $data = $reportRun->GenerateReport('TOTALXLS', $filterQuery, true);
        return $data;
    }

    /**
     * Function exports reports data into a Excel file
     */
    function getReportXLS($type = false) {

        $reportRun = ReportRun::getInstance($this->getId());
        $advanceFilterSql = $this->getAdvancedFilterSQL();

        $rootDirectory = vglobal('root_directory');
        $tmpDir = vglobal('tmp_dir');

        $tempFileName = tempnam($rootDirectory . $tmpDir, 'xls');
        $fileName = decode_html($this->getName()) . '.xls';
        $reportRun->writeReportToExcelFile($tempFileName, $advanceFilterSql);

        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
            header('Pragma: public');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        }

        // header('Content-Type: application/x-msexcel');
        // header('Content-Length: ' . @filesize($tempFileName));
        // header('Content-disposition: attachment; filename="' . $fileName . '"');

        ob_end_clean();
        header("Content-type: application/vnd.ms-excel");
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header("Pragma: no-cache");
        header("Expires: 0");
        ob_end_clean();

        $fp = fopen($tempFileName, 'rb');
        fpassthru($fp);
        fclose($fp);
        @unlink($tempFileName);
    }

    /**
     * Function exports reports data into a csv file
     */
    function getReportCSV($type = false) {
        $reportRun = ReportRun::getInstance($this->getId());
        $advanceFilterSql = $this->getAdvancedFilterSQL();
        $rootDirectory = vglobal('root_directory');
        $tmpDir = vglobal('tmp_dir');

        $tempFileName = tempnam($rootDirectory . $tmpDir, 'csv');
        $reportRun->writeReportToCSVFile($tempFileName, $advanceFilterSql);
        $fileName = decode_html($this->getName()) . '.csv';

        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
            header('Pragma: public');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        }

        // we are adding UTF-8 Byte Order Mark - BOM at the bottom so the size should be + 8 of the file size
        $fileSize = @filesize($tempFileName) + 8;
        ob_end_clean();
        // header('Content-Encoding: UTF-8');
        // header('Content-type: text/csv; charset=UTF-8');
        // header('Content-Length: ' . $fileSize);
        // header('Content-disposition: attachment; filename="' . $fileName . '"');

        header("Content-type: application/csv");
        header('Content-disposition: attachment; filename="' . $fileName . '"');
        header("Pragma: no-cache");
        header("Expires: 0");
        ob_end_clean();
        // UTF-8 Byte Order Mark - BOM (Source : http://stackoverflow.com/questions/4348802/how-can-i-output-a-utf-8-csv-in-php-that-excel-will-read-properly)
        echo "\xEF\xBB\xBF";

        $fp = fopen($tempFileName, 'rb');
        fpassthru($fp);
        fclose($fp);
        @unlink($tempFileName);
    }

    /**
     * Function returns data in printable format
     * @return <Array>
     */
    function getReportPrint() {
        $reportRun = ReportRun::getInstance($this->getId());
        $advanceFilterSql = $this->getAdvancedFilterSQL();
        $data = array();
        $data['data'] = $reportRun->GenerateReport('PRINT', $advanceFilterSql);
        $data['total'] = $reportRun->GenerateReport('PRINT_TOTAL', $advanceFilterSql);
        return $data;
    }

    /**
     * Function returns reports is default or not
     * @return <boolean>
     */
    function isDefault() {
        if ($this->get('state') == 'SAVED') {
            return true;
        }
        return false;
    }

    /**
     * Function to check whether report is custom report or not
     * @return boolean
     */
    function isCustom() {
        $handlerClass = $this->get('handler_class');
        if (!empty($handlerClass)) {
            return true;
        }
        return false;
    }

    /**
     * Function move report to another specified folder
     * @param folderid
     */
    function move($folderId) {
        $db = PearDatabase::getInstance();

        $db->pquery("UPDATE vtiger_report SET folderid = ? WHERE reportid = ?", array($folderId, $this->getId()));
    }

    /**
     * Function to get Calculation fields for Primary module
     * @return <Array> Primary module calculation fields
     */
    function getPrimaryModuleCalculationFields() {
        $primaryModule = $this->getPrimaryModule();
        $primaryModuleFields = $this->getPrimaryModuleFields();
        $calculationFields = array();
        foreach ($primaryModuleFields[$primaryModule] as $blocks) {
            if (!empty($blocks)) {
                foreach ($blocks as $fieldType => $fieldName) {
                    $fieldDetails = explode(':', $fieldType);
					if($fieldName == 'Send Reminder' && $primaryModule == 'Calendar') continue;
					if($primaryModule == 'ModComments' && ($fieldName == 'Integer' || $fieldName == 'Is Private')) continue;
                    if ($fieldDetails[4] === "I" || $fieldDetails[4] === "N" || $fieldDetails[4] === "NN") {
                        $calculationFields[$fieldType] = $fieldName;
                    }
                }
            }
        }
        $primaryModuleCalculationFields[$primaryModule] = $calculationFields;
        return $primaryModuleCalculationFields;
    }

    /**
     * Function to get Calculation fields for Secondary modules
     * @return <Array> Secondary modules calculation fields
     */
    function getSecondaryModuleCalculationFields() {
        $secondaryModuleCalculationFields = array();
        $secondaryModules = $this->getSecondaryModules();
        if (!empty($secondaryModules)) {
            $secondaryModulesList = explode(':', $secondaryModules);
            $count = count($secondaryModulesList);

            $secondaryModuleFields = $this->getSecondaryModuleFields();

            for ($i = 0; $i < $count; $i++) {
                $calculationFields = array();
                $secondaryModule = $secondaryModulesList[$i];
                if ($secondaryModuleFields[$secondaryModule]) {
                    foreach ($secondaryModuleFields[$secondaryModule] as $blocks) {
                        if (!empty($blocks)) {
                            foreach ($blocks as $fieldType => $fieldName) {
                                $fieldDetails = explode(':', $fieldType);
                                if ($fieldName == 'Send Reminder' && $secondaryModule == 'Calendar')
                                    continue;
                                if ($secondaryModule == 'ModComments' && ($fieldName == 'Integer' || $fieldName == 'Is Private'))
                                    continue;
                                if ($fieldDetails[4] === "I" || $fieldDetails[4] === "N" || $fieldDetails[4] === "NN") {
                                    $calculationFields[$fieldType] = $fieldName;
                                }
                            }
                        }
                    }
                }
                $secondaryModuleCalculationFields[$secondaryModule] = $calculationFields;
            }
        }
        return $secondaryModuleCalculationFields;
    }

    /**
     * Function to get Calculation fields for entire Report
     * @return <Array> report calculation fields
     */
    function getCalculationFields() {
        $primaryModuleCalculationFields = $this->getPrimaryModuleCalculationFields();
        $secondaryModuleCalculationFields = $this->getSecondaryModuleCalculationFields();

        return array_merge($primaryModuleCalculationFields, $secondaryModuleCalculationFields);
    }

    /**
     * Function used to transform the older filter condition to suit newer filters.
     * The newer filters have only two groups one with ALL(AND) condition between each
     * filter and other with ANY(OR) condition, this functions tranforms the older
     * filter with 'AND' condition between filters of a group and will be placed under
     * match ALL conditions group and the rest of it will be placed under match Any group.
     * @return <Array>
     */
    function transformToNewAdvancedFilter() {
        $standardFilter = $this->transformStandardFilter();
        $advancedFilter = $this->getSelectedAdvancedFilter();
        $allGroupColumns = $anyGroupColumns = array();
        foreach ($advancedFilter as $index => $group) {
            $columns = $group['columns'];
            $and = $or = 0;
            $block = $group['condition'];
            if (count($columns) != 1) {
                foreach ($columns as $column) {
                    if ($column['column_condition'] == 'and') {
                        ++$and;
                    } else {
                        ++$or;
                    }
                }
                if ($and == count($columns) - 1 && count($columns) != 1) {
                    $allGroupColumns = array_merge($allGroupColumns, $group['columns']);
                } else {
                    $anyGroupColumns = array_merge($anyGroupColumns, $group['columns']);
                }
            } else if ($block == 'and' || $index == 1) {
                $allGroupColumns = array_merge($allGroupColumns, $group['columns']);
            } else {
                $anyGroupColumns = array_merge($anyGroupColumns, $group['columns']);
            }
        }
        if ($standardFilter) {
            $allGroupColumns = array_merge($allGroupColumns, $standardFilter);
        }
        $transformedAdvancedCondition = array();
        $transformedAdvancedCondition[1] = array('columns' => $allGroupColumns, 'condition' => 'and');
        $transformedAdvancedCondition[2] = array('columns' => $anyGroupColumns, 'condition' => '');

        return $transformedAdvancedCondition;
    }

    /*
     *  Function used to tranform the standard filter as like as advanced filter format
     *  @returns array of tranformed standard filter
     */
	public function transformStandardFilter(){
        $standardFilter = $this->getSelectedStandardFilter();
		if(!empty($standardFilter)){
            $tranformedStandardFilter = array();
            $tranformedStandardFilter['comparator'] = 'bw';

			$fields = explode(':',$standardFilter['columnname']);
            $standardReports = array('Last Month Activities', 'This Month Activities');
			if($fields[1] == 'createdtime' || $fields[1] == 'modifiedtime' ||($fields[0] == 'vtiger_activity' && $fields[1] == 'date_start')){
                if(in_array($this->get('reportname'), $standardReports)){
                    $tranformedStandardFilter['columnname'] = "$fields[0]Calendar:$fields[1]:$fields[3]:$fields[2]:DT";
                    $tranformedStandardFilter['comparator'] = $standardFilter['type'];
                }else{
                $tranformedStandardFilter['columnname'] = "$fields[0]:$fields[1]:$fields[3]:$fields[2]:DT";
                    $date[] = $standardFilter['startdate'].' 00:00:00';
                    $date[] = $standardFilter['enddate'].' 00:00:00';
                    $tranformedStandardFilter['value'] =  implode(',',$date);
                }
			} else{
                $tranformedStandardFilter['columnname'] = "$fields[0]:$fields[1]:$fields[3]:$fields[2]:D";
				$tranformedStandardFilter['value'] = $standardFilter['startdate'].','.$standardFilter['enddate'];
            }
            return array($tranformedStandardFilter);
		} else{
            return false;
        }
    }

    /**
     * Function returns the Advanced filter SQL
     * @return <String>
     */
    function getAdvancedFilterSQL() {
        global $customReportids, $walletSummary_reportId, $openTrade_reportId, $closeTrade_reportId, $accoutTransaction_reportId, $ibStatistics_reportId, $ibSummary_reportId; //add by divyesh
        $advancedFilter = $this->get('advancedFilter');
        $advancedFilterCriteria = array();
        $advancedFilterCriteriaGroup = array();
        if (is_array($advancedFilter)) {
            foreach ($advancedFilter as $groupIndex => $groupInfo) {
                $groupColumns = $groupInfo['columns'];
                $groupCondition = $groupInfo['condition'];

                if (empty($groupColumns)) {
                    unset($advancedFilter[1]['condition']);
                } else {
                    if (!empty($groupCondition)) {
                        $advancedFilterCriteriaGroup[$groupIndex] = array('groupcondition' => $groupCondition);
                    }
                }

                foreach ($groupColumns as $groupColumn) {
                    $groupColumn['groupid'] = $groupIndex;
                    $groupColumn['columncondition'] = $groupColumn['column_condition'];
                    unset($groupColumn['column_condition']);
                    $advancedFilterCriteria[] = $groupColumn;
                }
            }
        }

        //add by Divyesh chothani
        if (in_array($this->getId(), $customReportids)) {
            return $advancedFilterCriteria;
        } else {
            $this->reportRun = ReportRun::getInstance($this->getId());
            $filterQuery = $this->reportRun->RunTimeAdvFilter($advancedFilterCriteria, $advancedFilterCriteriaGroup);
            return $filterQuery;
        }
        //end
    }

    /**
     * Function to generate data for advanced filter conditions
     * @param Vtiger_Paging_Model $pagingModel
     * @return <Array>
     */
    public function generateData($pagingModel = false) {
        $filterQuery = $this->getAdvancedFilterSQL();
        if (!$filterQuery) {
            $filterQuery = true;
        }
        return $this->getReportData($pagingModel, $filterQuery);
    }

    /**
     * Function to get query filter data of IB statistics report
     * @param Vtiger_Paging_Model $pagingModel
     * @return <Array>
     */
    public function insertCustomReportFilter($pagingModel = false) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $filterQuery = $this->getAdvancedFilterSQL();
        $filterQuery = json_encode($filterQuery);
        if (empty($filterQuery)) {
            $filterQuery = false;
        } else {            
            $db = PearDatabase::getInstance();
            $time = date('Y-m-d H:i:s');
            if (!empty($filterQuery)) {
                $db->pquery("INSERT INTO vtiger_reportfilterdata(reportid, userid, filterdata, status, createdtime, modifiedtime) VALUES (?,?,?,?,?,?)", array($this->getId(), $currentUser->getId(), $filterQuery, 0, $time, $time));
            }
            $filterQuery = true;
        }
        return $filterQuery;
    }

    /**
     * Function to generate data for advanced filter conditions
     * @param Vtiger_Paging_Model $pagingModel
     * @return <Array>
     */
    public function generateCalculationData() {
        $filterQuery = $this->getAdvancedFilterSQL();
        return $this->getReportCalulationData($filterQuery);
    }

    /**
     * Function to check duplicate exists or not
     * @return <boolean>
     */
    public function checkDuplicate() {
        $db = PearDatabase::getInstance();

        $query = "SELECT 1 FROM vtiger_report WHERE reportname = ?";
        $params = array($this->getName());

        $record = $this->getId();
        if ($record && !$this->get('isDuplicate')) {
            $query .= " AND reportid != ?";
            array_push($params, $record);
        }

        $result = $db->pquery($query, $params);
        if ($db->num_rows($result)) {
            return true;
        }
        return false;
    }

    /**
     * Function is used for Inventory reports, filters should show line items fields only if they are selected in
     * calculation otherwise it should not be shown
     * @return boolean
     */
    function showLineItemFieldsInFilter($calculationFields = false) {
        if ($calculationFields == false)
            $calculationFields = $this->getSelectedCalculationFields();

        $primaryModule = $this->getPrimaryModule();
        $inventoryModules = array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder');
        if (!in_array($primaryModule, $inventoryModules))
            return false;
        if (!empty($calculationFields)) {
            foreach ($calculationFields as $field) {
                if (stripos($field, 'cb:vtiger_inventoryproductrel') !== false) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    public function getScheduledReport() {
        return Reports_ScheduleReports_Model::getInstanceById($this->getId());
    }

    public function getRecordsListFromRequest(Vtiger_Request $request) {
        $folderId = $request->get('viewname');
        $module = $request->get('module');
        $selectedIds = $request->get('selected_ids');
        $excludedIds = $request->get('excluded_ids');
        $searchParams = $request->get('search_params');
        $searchParams = $searchParams[0];

        if (!empty($selectedIds) && $selectedIds != 'all') {
            if (!empty($selectedIds) && count($selectedIds) > 0) {
                return $selectedIds;
            }
        }

        $reportFolderModel = Reports_Folder_Model::getInstance();
        $reportFolderModel->set('folderid', $folderId);
        if ($reportFolderModel) {
            return $reportFolderModel->getRecordIds($excludedIds, $module, $searchParams);
        }
    }

    function getModuleCalculationFieldsForReport() {
        $aggregateFunctions = $this->getAggregateFunctions();
        $moduleFields = array();
        $primaryModuleFields = $this->getPrimaryModuleCalculationFields();
        $secondaryModuleFields = $this->getSecondaryModuleCalculationFields();
        $moduleFields = array_merge($primaryModuleFields, $secondaryModuleFields);
        foreach ($moduleFields as $moduleName => $fieldList) {
            $fields = array();
            if (!empty($fieldList)) {
                foreach ($fieldList as $column => $label) {
                    foreach ($aggregateFunctions as $function) {
                        $fLabel = vtranslate($label, $moduleName) . ' (' . vtranslate('LBL_' . $function, 'Reports') . ')';
                        $fColumn = $column . ':' . $function;
                        $fields[$fColumn] = $fLabel;
                    }
                }
            }
            $moduleFields[$moduleName] = $fields;
        }
        return $moduleFields;
    }

    function getAggregateFunctions() {
        $functions = array('SUM', 'AVG', 'MIN', 'MAX');
        return $functions;
    }

    /**
     * Function to save reprot tyep data
     */
    function saveReportType() {
        $db = PearDatabase::getInstance();
        $data = $this->get('reporttypedata');
        if (!empty($data)) {
            $db->pquery('DELETE FROM vtiger_reporttype WHERE reportid = ?', array($this->getId()));
            $db->pquery("INSERT INTO vtiger_reporttype(reportid, data) VALUES (?,?)", array($this->getId(), $data));
        }
    }

    function getReportTypeInfo() {
        $db = PearDatabase::getInstance();

        $result = $db->pquery("SELECT data FROM vtiger_reporttype WHERE reportid = ?", array($this->getId()));

        $dataFields = '';
        if ($db->num_rows($result) > 0) {
            $dataFields = $db->query_result($result, 0, 'data');
        }
        return $dataFields;
    }

    /**
     * Function is used in Charts and Pivots to remove fields like email, phone, descriptions etc
     * as these fields are not generally used for grouping records
     * @return $fields - array of report field columns
     */
    function getPrimaryModuleFieldsForAdvancedReporting() {
        $fields = $this->getPrimaryModuleFields();
        $primaryModule = $this->getPrimaryModule();
		if($primaryModule == "Calendar"){
            $eventModuleModel = Vtiger_Module_Model::getInstance('Events');
            $eventModuleFieldInstances = $eventModuleModel->getFields();
        }
        $primaryModuleModel = Vtiger_Module_Model::getInstance($primaryModule);
        $primaryModuleFieldInstances = $primaryModuleModel->getFields();

		if(is_array($fields)) foreach($fields as $module => $blocks) {
			if(is_array($blocks)) foreach($blocks as $blockLabel => $blockFields) {
				if(is_array($blockFields)) foreach($blockFields as $reportFieldInfo => $fieldLabel) {
					$fieldInfo = explode(':',$reportFieldInfo);

                                $fieldInstance = $primaryModuleFieldInstances[$fieldInfo[3]];
					if(!$fieldInstance && $eventModuleFieldInstances){
                                    $fieldInstance = $eventModuleFieldInstances[$fieldInfo[3]];
                                }
					if(empty($fieldInstance) || $fieldInfo[0] == 'vtiger_inventoryproductrel' || $fieldInstance->getFieldDataType() == 'email'
							|| $fieldInstance->getFieldDataType() == 'phone' || $fieldInstance->getFieldDataType() == 'image'
							|| $fieldInstance->get('uitype') == '4') {
                                    unset($fields[$module][$blockLabel][$reportFieldInfo]);
                                }
                            }
                    }
            }
        return $fields;
    }

    /**
     * Function is used in Charts and Pivots to remove fields like email, phone, descriptions etc
     * as these fields are not generally used for grouping records
     * @return $fields - array of report field columns
     */
    function getSecondaryModuleFieldsForAdvancedReporting() {
        $fields = $this->getSecondaryModuleFields();
        $secondaryModules = $this->getSecondaryModules();

        $secondaryModules = @explode(':', $secondaryModules);
        if (is_array($secondaryModules)) {
            $secondaryModuleFieldInstances = array();
            foreach ($secondaryModules as $secondaryModule) {
                if (!empty($secondaryModule)) {
                    if ($secondaryModule == "Calendar") {
                        $eventModuleModel = Vtiger_Module_Model::getInstance('Events');
                        $eventModuleFieldInstances['Events'] = $eventModuleModel->getFields();
                    }
                    $secondaryModuleModel = Vtiger_Module_Model::getInstance($secondaryModule);
                    $secondaryModuleFieldInstances[$secondaryModule] = $secondaryModuleModel->getFields();
                }
            }
        }
		if(is_array($fields)) foreach($fields as $module => $blocks) {
			if(is_array($blocks)) foreach($blocks as $blockLabel => $blockFields) {
				if(is_array($blockFields)) foreach($blockFields as $reportFieldInfo => $fieldLabel) {
					$fieldInfo = explode(':',$reportFieldInfo);
                                $fieldInstance = $secondaryModuleFieldInstances[$module][$fieldInfo[3]];
					if(!$fieldInstance && $eventModuleFieldInstances['Events']){
                                    $fieldInstance = $eventModuleFieldInstances['Events'][$fieldInfo[3]];
                                }
					if(empty($fieldInstance) || $fieldInfo[0] == 'vtiger_inventoryproductrel'
							|| $fieldInstance->getFieldDataType() == 'email' || $fieldInstance->getFieldDataType() == 'phone'
								|| $fieldInstance->getFieldDataType() == 'image' || $fieldInstance->get('uitype') == '4') {
                                    unset($fields[$module][$blockLabel][$reportFieldInfo]);
                                }
                            }
                    }
            }

        return $fields;
    }

    function isInventoryModuleSelected() {
        $inventoryModules = getInventoryModules();
        $primaryModule = $this->getPrimaryModule();
        $secondaryModules = explode(':', $this->getSecondaryModules());
        $selectedModules = array_merge(array($primaryModule), $secondaryModules);
        foreach ($selectedModules as $module) {
            if (in_array($module, $inventoryModules)) {
                return true;
            }
        }
        return false;
    }

    public function isPinnedToDashboard() {
        $db = PearDatabase::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $result = $db->pquery("SELECT 1 FROM vtiger_module_dashboard_widgets WHERE reportid = ? AND userid = ?", array($this->getId(), $currentUser->getId()));
        if ($db->num_rows($result)) {
            return true;
        }
        return false;
    }

    function isEditableBySharing() {
        $db = PearDatabase::getInstance();
        $currentUserId = Users_Record_Model::getCurrentUserModel()->getId();
        $ownerResult = $db->pquery("SELECT owner FROM vtiger_report WHERE reportid = ?", array($this->getId()));
        $reportOnwer = $db->query_result($ownerResult, 0, 'owner');

        if ($currentUserId == $reportOnwer) {
            return true;
        } else {
            $reportId = $this->getId();
            $query = "SELECT 1 FROM vtiger_report_sharegroups WHERE reportid = ? "
                . "UNION SELECT 1 FROM vtiger_report_sharerole WHERE reportid = ? "
                . "UNION SELECT 1 FROM vtiger_report_sharers WHERE reportid = ? "
                . "UNION SELECT 1 FROM vtiger_report_shareusers WHERE reportid = ?";
            $result = $db->pquery($query, array($reportId, $reportId, $reportId, $reportId));
            if ($db->num_rows($result)) {
                return false;
            } else {
                return true;
            }
        }
    }

    public static function isReportExists($recordId) {
        $db = PearDatabase::getInstance();
        $reportResult = $db->pquery('SELECT * FROM vtiger_report WHERE reportid = ?', array($recordId));
        if ($db->num_rows($reportResult) > 0) {
            return true;
        }

        return false;
    }

    //Add By Divyesh start
    public function getWalletAllTransactionsCabinetUser($outputformat, $operation) {
        global $adb;
        $live_currency_code = Vtiger_Util_Helper::getPickListValues('live_currency_code');
        $reportquery = "SELECT vtiger_ewallet.contactid ,(SELECT CONCAT(vtiger_contactdetails.firstname,' ',vtiger_contactdetails.lastname) FROM vtiger_contactdetails WHERE vtiger_contactdetails.contactid = vtiger_ewallet.contactid)  AS 'contact_name', 
    (SELECT email FROM vtiger_contactdetails WHERE vtiger_contactdetails.contactid = vtiger_ewallet.contactid) AS  'email',(SELECT contact_no FROM vtiger_contactdetails WHERE vtiger_contactdetails.contactid = vtiger_ewallet.contactid) AS  'contact_no' 
    FROM vtiger_ewallet
    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ewallet.ewalletid
    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
    LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
    INNER JOIN vtiger_crmentity AS vtiger_crmentityRelEwallet955 ON vtiger_crmentityRelEwallet955.crmid = vtiger_ewallet.contactid AND vtiger_crmentityRelEwallet955.deleted =0
    INNER JOIN vtiger_contactdetails AS vtiger_contactdetailsRelEwallet955 ON vtiger_contactdetailsRelEwallet955.contactid = vtiger_crmentityRelEwallet955.crmid
    WHERE vtiger_ewallet.ewalletid > 0 AND vtiger_crmentity.deleted =0 GROUP BY vtiger_ewallet.contactid ";

        $result = $adb->pquery($reportquery, array());
        $num_rows = $adb->num_rows($result);
        $walletResult = array();
        if ($num_rows > 0) {
            while ($row_result = $adb->fetchByAssoc($result)) {
                $contactid = $row_result['contactid'];
                $contact_name = $row_result['contact_name'];
                $email = $row_result['email'];
                $contactid = $row_result['contactid'];
                $wallet_no = $row_result['contact_no'];
                $walletResult = array('Contact Name' => $contact_name, 'Email' => $email, 'Wallet ID' => $wallet_no);
                foreach ($live_currency_code as $currency) {
                    $currecnydata = getEwalletBalanceBaseOnCurrency($contactid);
                    if ($currecnydata[$currency]) {
                        $walletResult[$currency] = number_format($currecnydata[$currency], 2);
                    } else {
                        $walletResult[$currency] = 0;
                    }
                }
                $data[] = $walletResult;
            }
        }

        if ($operation == 'ExcelExport' && $outputformat == 'PDF') {
            $excelSheetFormateData = array();
            foreach ($data as $data_value) {
                foreach ($data_value as $columnName => $value) {
                    $values = array('value' => $value, 'type' => "string");
                    $ExcelExportResult[$columnName] = $values;
                }
                $excelSheetFormateData[] = $ExcelExportResult;
            }
            return $excelSheetFormateData;
        } else {
            return $data;
        }
    }

    public function getTradingReports($outputformat, $operation, $filtersql, $trade_type) {
        global $adb, $current_user;

        $createdtime = explode(",", $filtersql[0]['value']);

        if ($current_user->date_format == 'dd-mm-yyyy') {
            $date_formate = 'd-m-Y';
        } elseif ($current_user->date_format == 'mm-dd-yyyy') {
            $date_formate = 'm-d-Y';
        } elseif ($current_user->date_format == 'yyyy-mm-dd') {
            $date_formate = 'Y-m-d';
        }
        if ($createdtime[0] != '') {
            $startdateFormat = DateTime::createFromFormat($date_formate, $createdtime[0]);
            $startDateTime = $startdateFormat->format('Y-m-d');
        }
        $startDateTime = $startDateTime . ' 00:00:00';

        if ($createdtime[1] != '') {
            $enddateFormat = DateTime::createFromFormat($date_formate, $createdtime[1]);
            $endDateTime = $enddateFormat->format('Y-m-d');
        }
        $endDateTime = $endDateTime . ' 23:59:59';
        $contactid = $filtersql[1]['value'];
        $liveaccountid = $filtersql[2]['value'];

        $reportResult = array();

        if ($contactid) {
            $contactRecordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
            $contactData = $contactRecordModel->getData();
            $contactName = $contactData['firstname'] . " " . $contactData['lastname'];
            $contactEmail = $contactData['email'];
        }

        if ($liveaccountid) {
            $liveAccountRecordModel = Vtiger_Record_Model::getInstanceById($liveaccountid, 'LiveAccount');
            $LiveAccData = $liveAccountRecordModel->getData();
            $account_no = $LiveAccData['account_no'];
            $metatrader_type = $LiveAccData['live_metatrader_type'];

            $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
            if (empty($provider)) {
                echo "provider issue";
                exit;
            }

            $AND = $provider->getTradingTimeConditions($trade_type, $startDateTime, $endDateTime);
            $ORDERBY = $provider->getTradingOrderByConditions($trade_type);

            $tradeQuery = $provider->getTradesForReport($trade_type, $account_no);
            $TradeResult = array();

            $AND = $AND . " AND l.account_no=" . $account_no . " AND c.contactid=" . $contactid . " AND e.deleted = 0";

            $query = "SELECT '" . $contactName . "' AS contact_name,'" . $contactEmail . "' AS contact_email, trades.* FROM (" . $tradeQuery . ") AS trades  
            INNER JOIN `vtiger_liveaccount` AS `l` ON  `trades`.`LOGIN` = `l`.`account_no`
            INNER JOIN `vtiger_crmentity` AS `e` ON  `e`.`crmid` = `l`.`liveaccountid`
            INNER JOIN `vtiger_contactdetails` AS `c` ON `l`.`contactid` = `c`.`contactid`" . $AND . $ORDERBY;
            //echo $query; exit;
            $result = $adb->pquery($query, array());
            $num_rows = $adb->num_rows($result);
            if ($num_rows > 0) {
                while ($row_result = $adb->fetchByAssoc($result)) {
                    $contact_name = $row_result['contact_name'];
                    $contact_email = $row_result['contact_email'];
                    
                    $providerData = $provider->getProviderSpecificTradeData($row_result, $trade_type);
                    $login = $providerData['login'];
                    $take_profit = $providerData['take_profit'];
                    $stop_loss = $providerData['stop_loss'];
                    $commission = number_format($providerData['commission'], 4);
                    $swaps = $providerData['swaps'];
                    $profit = $providerData['profit'];
                    $symbol = $providerData['symbol'];
                    $volume = floatval($providerData['volume']);
                    $type = $providerData['type'];
                    $open_time = $providerData['open_time']; //open_time
                    $open_price = floatval($providerData['open_price']); //open_price
                    $close_time = $providerData['close_time']; // close_time
                    $close_price = floatval($providerData['close_price']); //close_price
                    $ticket = $providerData['ticket'];
                    
                    if ($trade_type == 'open') {
                        $TradeResult[] = array('Contact Name' => $contact_name, 'Email' => $contact_email, 'Account Number' => $login, 'Ticket Number' => $ticket, 'Symbol' => $symbol, 'Volume' => $volume, 'Type' => $type, 'Open Time' => $open_time, 'Open Price' => $open_price, 'Take Profit' => $take_profit, 'Stop Loss' => $stop_loss, 'Commission' => $commission, 'Swap Value' => $swaps, 'Profit' => $profit);
                    } else if ($trade_type == 'close') {
                        $TradeResult[] = array('Contact Name' => $contact_name, 'Email' => $contact_email, 'Account Number' => $login, 'Ticket Number' => $ticket, 'Symbol' => $symbol, 'Volume' => $volume, 'Type' => $type, 'Open Time' => $open_time, 'Open Price' => $open_price, 'Close Time' => $close_time, 'Close Price' => $close_price, 'Take Profit' => $take_profit, 'Stop Loss' => $stop_loss, 'Commission' => $commission, 'Swap Value' => $swaps, 'Profit' => $profit);
                    }
                }
            }

            if ($operation == 'ExcelExport' && $outputformat == 'PDF') {
                $excelSheetFormateData = array();
                foreach ($TradeResult as $data_value) {
                    foreach ($data_value as $columnName => $value) {
                        $values = array('value' => $value, 'type' => "string");
                        $ExcelExportResult[$columnName] = $values;
                    }
                    $excelSheetFormateData[] = $ExcelExportResult;
                }
                $reportResult = $excelSheetFormateData;
            } else {
                $reportResult = $TradeResult;
            }
        }
        return $reportResult;
    }

    public function getTradingTransactions($outputformat, $operation, $filtersql) {
        global $adb, $current_user;

        $createdtime = explode(",", $filtersql[0]['value']);

        if ($current_user->date_format == 'dd-mm-yyyy') {
            $date_formate = 'd-m-Y';
        } elseif ($current_user->date_format == 'mm-dd-yyyy') {
            $date_formate = 'm-d-Y';
        } elseif ($current_user->date_format == 'yyyy-mm-dd') {
            $date_formate = 'Y-m-d';
        }
        if ($createdtime[0] != '') {
            $startdateFormat = DateTime::createFromFormat($date_formate, $createdtime[0]);
            $startDateTime = $startdateFormat->format('Y-m-d');
        }
        $startDateTime = $startDateTime . ' 00:00:00';

        if ($createdtime[1] != '') {
            $enddateFormat = DateTime::createFromFormat($date_formate, $createdtime[1]);
            $endDateTime = $enddateFormat->format('Y-m-d');
        }
        $endDateTime = $endDateTime . ' 23:59:59';
        $contactid = $filtersql[1]['value'];
        $liveaccountid = $filtersql[2]['value'];

        if ($contactid) {
            $contactRecordModel = Vtiger_Record_Model::getInstanceById($contactid, 'Contacts');
            $contactData = $contactRecordModel->getData();
            $contactName = $contactData['firstname'] . " " . $contactData['lastname'];
            $contactEmail = $contactData['email'];
        }

        if ($liveaccountid) {
            $liveAccountRecordModel = Vtiger_Record_Model::getInstanceById($liveaccountid, 'LiveAccount');
            $LiveAccData = $liveAccountRecordModel->getData();
            $account_no = $LiveAccData['account_no'];
            $metatrader_type = $LiveAccData['live_metatrader_type'];

            $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
            if (empty($provider)) {
                echo "provider issue";
                exit;
            }

            $AND = $provider->getTranTimeConditions($startDateTime, $endDateTime);
            $ORDERBY = $provider->getTranOrderByConditions();

            $TransactionsQuery = $provider->getTransactionsForReport($account_no, $startDateTime, $endDateTime);

            $AND = $AND . " AND l.account_no=" . $account_no . " AND c.contactid=" . $contactid . " AND e.deleted = 0 ";
            $query = "SELECT trades.* FROM (" . $TransactionsQuery . ") AS trades  
                INNER JOIN `vtiger_liveaccount` AS `l` ON  `trades`.`LOGIN` = `l`.`account_no`
                INNER JOIN `vtiger_crmentity` AS `e` ON  `e`.`crmid` = `l`.`liveaccountid`
                INNER JOIN `vtiger_contactdetails` AS `c` ON `l`.`contactid` = `c`.`contactid`" . $AND . $ORDERBY;
            //echo $query; exit;
            $result = $adb->pquery($query, array());
            $num_rows = $adb->num_rows($result);
            $TransationResult = array();
            if ($num_rows > 0) {
                while ($row_result = $adb->fetchByAssoc($result)) {
                    $login = $row_result['login'];
                    $providerDetails = $provider->getProviderSpecificData($row_result);
                    $ticket_no = $providerDetails['ticket_no'];
                    $close_time = $providerDetails['close_time'];
                    $profit = $row_result['profit'];
                    $comment = $row_result['comment'];

                    $TransationResult[] = array('Contact Name' => $contactName, 'Email' => $contactEmail, 'Account Number' => $login, 'Ticket Number' => $ticket_no, 'Date Time' => $close_time, 'Amount' => $profit, 'Comment' => $comment);
                }
            }
            if ($operation == 'ExcelExport' && $outputformat == 'PDF') {
                $excelSheetFormateData = array();
                foreach ($TransationResult as $data_value) {
                    foreach ($data_value as $columnName => $value) {
                        $values = array('value' => $value, 'type' => "string");
                        $ExcelExportResult[$columnName] = $values;
                    }
                    $excelSheetFormateData[] = $ExcelExportResult;
                }
                $reportResult = $excelSheetFormateData;
            } else {
                $reportResult = $TransationResult;
            }
            return $reportResult;
        }
    }

    function getIBStatistics($outputformat, $operation, $filtersql, $startLimit = 0, $endLimit = 0) {
        global $adb, $current_user;
        $max_ib_level = configvar('max_ib_level');
        //echo "<pre>"; print_r($filtersql); exit;

        $createdtime = explode(",", $filtersql[0]['value']);

        if ($current_user->date_format == 'dd-mm-yyyy') {
            $date_formate = 'd-m-Y';
        } elseif ($current_user->date_format == 'mm-dd-yyyy') {
            $date_formate = 'm-d-Y';
        } elseif ($current_user->date_format == 'yyyy-mm-dd') {
            $date_formate = 'Y-m-d';
        }
        if ($createdtime[0] != '') {
            $startdateFormat = DateTime::createFromFormat($date_formate, $createdtime[0]);
            $startDateTime = $startdateFormat->format('Y-m-d');
        }
        $startDateTime = $startDateTime . ' 00:00:00';

        if ($createdtime[1] != '') {
            $enddateFormat = DateTime::createFromFormat($date_formate, $createdtime[1]);
            $endDateTime = $enddateFormat->format('Y-m-d');
        }
        $endDateTime = $endDateTime . ' 23:59:59';

        $contactid = $filtersql[1]['value'];
        $child_contactid = $filtersql[2]['value'];
        $include_child = $filtersql[3]['value'];
        $hide_zero_commission = $filtersql[4]['value'];
        $server_type = $filtersql[5]['value'];
        $commission_type = $filtersql[6]['value'];

        if ($contactid == $child_contactid) {
            $whereCondition = " AND t.parent_contactid = " . $contactid . "  ";
        }else{
            if ($include_child) {
                $child_contactids = getChildContactRecordId($child_contactid);
                $string_child_contactids = implode(",", $child_contactids);
                $whereCondition = ' AND t.parent_contactid = ' . $contactid . ' AND t.child_contactid IN (' . $string_child_contactids . ') ';
            } else {
                $whereCondition = ' AND t.parent_contactid = ' . $contactid;
                if (!empty($child_contactid))
                {
                    $whereCondition .= ' AND t.child_contactid = ' . $child_contactid;
                }
                //$whereCondition = ' AND t.parent_contactid = ' . $contactid;
            } 
        }

        if ($hide_zero_commission) {
            $whereCondition .= ' AND t.commission_amount != 0';
        }
        if ($server_type) {
            $serverTypeArr = explode(',', $server_type);
            if(count($serverTypeArr) <= 1)
            {
                $whereCondition .= ' AND t.server_type = "' . $server_type . '"';
            }
            else
            {
                $serverTypeArrImploded = implode('","', $serverTypeArr);
                $whereCondition .= ' AND t.server_type IN("' . $serverTypeArrImploded . '")';
            }
        }
        if ($commission_type) {
            $commissionTypeArr = explode(',', $commission_type);
            if(count($commissionTypeArr) <= 1)
            {
                $whereCondition .= ' AND t.type = "' . $commission_type . '"';
            }
            else
            {
                $commissionTypeArrImploded = implode('","', $commissionTypeArr);
                $whereCondition .= ' AND t.type IN("' . $commissionTypeArrImploded . '")';
            }
        }
        
        

        $query = "SELECT t.`login`, t.`ticket`, t.`volume`, t.`symbol`, t.`open_price`, t.`close_price`, t.`close_time`, t.`profit`, t.`commission_amount`, t.`commission_comment`, t.`parent_contactid`, t.`child_contactid`,t.hierachy_level, child.firstname, child.lastname, child.email, child.affiliate_code,child.ib_hierarchy,child.record_status, t.server_type, t.type, t.pip, t.brokerage_commission FROM `tradescommission` AS t   
                INNER JOIN vtiger_contactdetails AS child ON t.child_contactid=child.contactid  
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = child.contactid
                WHERE vtiger_crmentity.deleted =0  AND t.close_time >= '" . $startDateTime . "' AND t.close_time <= '" . $endDateTime . "' " . $whereCondition . " ORDER BY t.hierachy_level ASC";
        //echo $query; exit;
        $result = $adb->pquery($query, array());
        $num_rows = $adb->num_rows($result);
        $IBStatisticsResult = array();
        $summary['profit_total'] = $summary['commission_total'] = $summary['lot_total'] = 0;
        if ($num_rows > 0) {
            while ($row_result = $adb->fetchByAssoc($result)) {
                $affiliate_code = '';
                if ($row_result['record_status'] == 'Approved') {
                    $affiliate_code = $row_result['affiliate_code'];
                }
                $IBStatisticsResult[] = array(
                    'Contact Name' => $row_result['firstname'] . ' ' . $row_result['lastname'],
                    'Email' => $row_result['email'],
                    'Affiliate ID' => $affiliate_code,
                    'Level' => $row_result['hierachy_level'],
                    'Server Type' => $row_result['server_type'],
                    'Account Number' => $row_result['login'],
                    'Ticket Number' => $row_result['ticket'],
                    'Volume' => floatval($row_result['volume']),
                    'Symbol' => $row_result['symbol'],
                    'Open Price' => floatval($row_result['open_price']),
                    'Close Price' => floatval($row_result['close_price']),
                    //'Open time' => $row_result['open_time'],
                    'Close Time' => $row_result['close_time'],
                    'Profit' => number_format($row_result['profit'], 3),
                    'Commission' => number_format($row_result['commission_amount'], 4),
                    'Comments' => $row_result['commission_comment'],
                    'Commission Type' => $row_result['type'],
                    'PIP Value' => $row_result['pip'],
                    'Brokerage' => $row_result['brokerage_commission']
                );
                $summary['profit_total'] += $row_result['profit'];
                $summary['commission_total'] += $row_result['commission_amount'];
                $summary['lot_total'] += floatval($row_result['volume']);
            }
        }

        if ($operation == 'ExcelExport' && $outputformat == 'PDF') {
            $excelSheetFormateData = array();
            foreach ($IBStatisticsResult as $data_value) {
                foreach ($data_value as $columnName => $value) {
                    $values = array('value' => $value, 'type' => "string");
                    $ExcelExportResult[$columnName] = $values;
                }
                $excelSheetFormateData[] = $ExcelExportResult;
            }
            $reportResult = $excelSheetFormateData;
        } else if ($operation == 'Email' && $outputformat == 'PDF') {
            global $root_directory;
            $emailData = array();
            if(!empty($IBStatisticsResult))
            {
                $uploadPath = decideFilePath();
                $time = strtotime(date('d-m-y h:i:s'));
                $emailData['filePath'] = $filePath = $root_directory.$uploadPath;
                $emailData['fileName'] = $fileName = 'IB_Statistics_Report_'.$time.'.csv';
                $fp = fopen($filePath.$fileName, 'wb');
                fputcsv($fp, array('Contact Name', 'Email', 'Affiliate ID', 'Level', 'Server Type', 'Account Number', 'Ticket Number', 'Volume', 'Symbol', 'Open Price', 'Close Price', 'Close Time', 'Profit', 'Commission', 'Comments', 'Commission Type', 'PIP Value'));
                foreach ($IBStatisticsResult as $row) {
                    fputcsv($fp, $row);
                }
            }
            return $emailData;
        } else {
            if(isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'GetCSV')
            {
                $reportResult = $IBStatisticsResult;
            }
            else
            {
                $IBStatisticsSliceResult = array_slice($IBStatisticsResult, 0, $endLimit);
                $reportResult = $IBStatisticsSliceResult;
            }
        }
        $reportResult['summary'] = $summary;
        return $reportResult;
    }

    function getIBSummary($outputformat, $operation, $filtersql) {
        global $adb;
        $max_ib_level = configvar('max_ib_level');
        $contactid = $filtersql[0]['value'];
        $child_contactid = $filtersql[1]['value'];
        $include_child = $filtersql[2]['value'];

        // if ($include_child) {
        //     $child_contactids = getChildContactRecordId($child_contactid);
        //     $string_child_contactids = implode(",", $child_contactids);
        //     $AND = ' AND t.parent_contactid = ' . $contactid . ' AND t.child_contactid IN (' . $string_child_contactids . ') ';
        // } else {
        //     $AND = ' AND t.parent_contactid = ' . $contactid . ' AND t.child_contactid = ' . $child_contactid;
        //     //$AND = ' AND t.parent_contactid = ' . $contactid ;
        // }
        // if ($contactid != $child_contactid) {
        //     $AND .= " AND t.hierachy_level <= " . $max_ib_level . " ";
        // }

        if ($contactid == $child_contactid) {
            // $AND = " AND t.parent_contactid <= " . $contactid . " AND t.child_contactid IN (SELECT child_contactid FROM `tradescommission` WHERE `parent_contactid` = '".$contactid."' GROUP BY child_contactid)  ";
            $AND = " AND t.parent_contactid = " . $contactid . "  ";
        }else{
            if ($include_child) {
                    $child_contactids = getChildContactRecordId($child_contactid);
                    $string_child_contactids = implode(",", $child_contactids);
                    $AND = ' AND t.parent_contactid = ' . $contactid . ' AND t.child_contactid IN (' . $string_child_contactids . ') ';
            } else {
                $AND = ' AND t.parent_contactid = ' . $contactid . ' AND t.child_contactid = ' . $child_contactid;
                //$AND = ' AND t.parent_contactid = ' . $contactid ;
            }
            $AND .= " AND t.hierachy_level <= " . $max_ib_level . " ";
        }

        $query = "SELECT sum(IF(commission_amount>0,IF(commission_comment='',t.volume,0),0)) AS volume, SUM(t.commission_amount) AS commission_amount, child.firstname, child.lastname, child.email, child.affiliate_code, t.hierachy_level,child.record_status FROM `tradescommission` AS t 
            INNER JOIN vtiger_contactdetails AS child ON t.child_contactid=child.contactid  
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = child.contactid WHERE vtiger_crmentity.deleted =0 " . $AND . "  GROUP BY t.child_contactid, t.hierachy_level ";
        //echo $query; exit;
        $result = $adb->pquery($query, array());
        $num_rows = $adb->num_rows($result);
        $IBSummaryResult = array();
        if ($num_rows > 0) {
            while ($row_result = $adb->fetchByAssoc($result)) {
                $affiliate_code = '';
                if ($row_result['record_status'] == 'Approved') {
                    $affiliate_code = $row_result['affiliate_code'];
                }
                $IBSummaryResult[] = array(
                    'Name' => $row_result['firstname'] . ' ' . $row_result['lastname'],
                    'Email' => $row_result['email'],
                    'Level' => $row_result['hierachy_level'],
                    'Affilate' => $affiliate_code,
                    'Volume' => floatval($row_result['volume']),
                    'Commission' => number_format($row_result['commission_amount'], 4),
                );
            }
        }
        if ($operation == 'ExcelExport' && $outputformat == 'PDF') {
            $excelSheetFormateData = array();
            foreach ($IBSummaryResult as $data_value) {
                foreach ($data_value as $columnName => $value) {
                    $values = array('value' => $value, 'type' => "string");
                    $ExcelExportResult[$columnName] = $values;
                }
                $excelSheetFormateData[] = $ExcelExportResult;
            }
            $reportResult = $excelSheetFormateData;
        } else {
            $reportResult = $IBSummaryResult;
        }

        return $reportResult;
    }
    
    public function getAssociatedAccounts($outputformat, $operation, $filtersql) {
         global $adb;
        $max_ib_level = configvar('max_ib_level');
        $contactid = $filtersql[0]['value'];
        $child_contactid = $filtersql[1]['value'];
        $include_child = $filtersql[2]['value'];

        if(!empty($contactid))
        {
            $whereEnd = $havingEnd = '';
            $havingEnd .= " AND ib_level > '0' ";
            
            $ibHierarchy = getIBHierarchyFromContactId($contactid);
            $currentIBLevel = substr_count($ibHierarchy, ":");
            $whereEnd .= " AND vtiger_contactdetails.`ib_hierarchy`  LIKE  '" . $ibHierarchy . "%'";
            if(!empty($child_contactid))
            {
                $whereEnd = '';
                
                $childIbHierarchy = getIBHierarchyFromContactId($child_contactid);
                $whereEnd .= " AND vtiger_contactdetails.`ib_hierarchy` LIKE '" . $childIbHierarchy . "%'";
            }
            

            $query = "SELECT vtiger_contactdetails.affiliate_code,vtiger_contactdetails.`record_status`,contactid as contact_id,vtiger_contactdetails.email,firstname,lastname,parent_affiliate_code,ib_hierarchy,
    ((CHAR_LENGTH(ib_hierarchy) - CHAR_LENGTH(REPLACE(ib_hierarchy, ':', ''))) - " . $currentIBLevel . ") as ib_level
    FROM vtiger_contactdetails INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid WHERE vtiger_crmentity.deleted = 0  ".$whereEnd." HAVING ib_level <= " . $max_ib_level . $havingEnd;

            $result = $adb->pquery($query, array());
            $num_rows = $adb->num_rows($result);
            $IBSummaryResult = array();
            if ($num_rows > 0) {
                while ($row_result = $adb->fetchByAssoc($result)) {
                    $affiliate_code = '';
                    if ($row_result['record_status'] === 'Approved') {
                        $affiliate_code = $row_result['affiliate_code'];
                    }
                    $IBSummaryResult[] = array(
                        'Contact Name' => $row_result['firstname'] . ' ' . $row_result['lastname'],
                        'Email' => $row_result['email'],
                        'Level' => $row_result['ib_level'],
                        'Affiliate Code' => $affiliate_code,
                        'Parent Affiliate Code' => $row_result['parent_affiliate_code'],
                    );
                }
            }
        }
        else
        {
            $IBSummaryResult = array();
        }
        if ($operation == 'ExcelExport' && $outputformat == 'PDF') {
            $excelSheetFormateData = array();
            foreach ($IBSummaryResult as $data_value) {
                foreach ($data_value as $columnName => $value) {
                    $values = array('value' => $value, 'type' => "string");
                    $ExcelExportResult[$columnName] = $values;
                }
                $excelSheetFormateData[] = $ExcelExportResult;
            }
            $reportResult = $excelSheetFormateData;
        } else {
            $reportResult = $IBSummaryResult;
        }

        return $reportResult;
    }

    public function getPaymentData($outputformat, $operation, $filtersql) {
         global $adb,$current_user;
        $max_ib_level = configvar('max_ib_level');
        $dateRange = $filtersql[0]['value'];
        $contactid = $filtersql[1]['value'];
        $child_contactid = $filtersql[2]['value'];
        $include_child = $filtersql[3]['value'];
        $liveaccountid = $filtersql[4]['value'];

        if(!empty($contactid))
        {
                $whereEnd = $havingEnd = '';

                $createdtime = explode(",", $filtersql[0]['value']);

                if ($current_user->date_format == 'dd-mm-yyyy') {
                    $date_formate = 'd-m-Y';
                } elseif ($current_user->date_format == 'mm-dd-yyyy') {
                    $date_formate = 'm-d-Y';
                } elseif ($current_user->date_format == 'yyyy-mm-dd') {
                    $date_formate = 'Y-m-d';
                }
                if ($createdtime[0] != '') {
                    $startdateFormat = DateTime::createFromFormat($date_formate, $createdtime[0]);
                    $startDateTime = $startdateFormat->format('Y-m-d');
                }
                $startDateTime = $startDateTime . ' 00:00:00';

                if ($createdtime[1] != '') {
                    $enddateFormat = DateTime::createFromFormat($date_formate, $createdtime[1]);
                    $endDateTime = $enddateFormat->format('Y-m-d');
                }
                $endDateTime = $endDateTime . ' 23:59:59';
            
            $whereEnd .= " AND `trades`.`close_time` >= '" . $startDateTime . "' AND `trades`.`close_time` <= '" . $endDateTime . "'";
 
            if ($child_contactid != '' && $include_child == '1') {
                $childIbHierarchy = getIBHierarchyFromContactId($child_contactid);
                $whereEnd .= " AND c.`ib_hierarchy` LIKE '" . $childIbHierarchy . "%'";
            } else if ($child_contactid != '') {
                $whereEnd .= " AND c.`contactid` = " . $child_contactid;
            } else {
                $whereEnd .= " AND c.`contactid` = " . $contactid;
            }

//Taking column name from fron request so need to replace alias with respective table name in filter.                 
            
            /*get account no from live account id*/
            $liveAccountRecordModel = Vtiger_Record_Model::getInstanceById($liveaccountid, 'LiveAccount');
            $LiveAccData = $liveAccountRecordModel->getData();
            $account_no = $LiveAccData['account_no'];
            /*get account no from live account id*/
            
            $service_provider_list = getListOfServiceProviders(1, 'title');
            $meta_service_provider_query = array();
            
            if (!empty($service_provider_list))
            {
                for ($i = 0; $i < count($service_provider_list); $i++)
                {
                    $provider = ServiceProvidersManager::getActiveInstanceByProvider($service_provider_list[$i]);
                    $meta_service_provider_query[] = $provider->getTransactionsForReport($account_no, $startDateTime, $endDateTime);
                }
                $meta_service_provider_query = implode(" UNION ", $meta_service_provider_query);
            }
            
            
            $ibHierarchy = getIBHierarchyFromContactId($contactid);
            $sql_get_level = "SELECT findIBLevel(REPLACE(`c`.`ib_hierarchy`, '" . $ibHierarchy . "', ''))";
            
            $query = "SELECT `c`.`firstname`, `c`.`lastname`, `c`.`email`, `c`.`parent_affiliate_code`, (" . $sql_get_level . ") AS `level`, trades.*"
                    . " FROM `vtiger_contactdetails` AS `c`"
                    . " INNER JOIN `vtiger_liveaccount` AS `l` ON `c`.`contactid` = `l`.`contactid`"
                    . " INNER JOIN `vtiger_crmentity` AS `e` ON `e`.`crmid` = `l`.`liveaccountid`"
                    . " LEFT JOIN (" . $meta_service_provider_query . ") AS trades ON trades.login=l.account_no"
                    . " WHERE e.deleted = 0" . $whereEnd;
//            echo $query; exit;
            $result = $adb->pquery($query, array());
            $num_rows = $adb->num_rows($result);
            $paymentDataResult = array();
            $summary['profit_total'] = $summary['commission_total'] = $summary['lot_total'] = 0;
            if ($num_rows > 0) {
                while ($row_result = $adb->fetchByAssoc($result)) {
                    $affiliate_code = '';
                    if ($row_result['record_status'] === 'Approved') {
                        $affiliate_code = $row_result['affiliate_code'];
                    }
                    
                    $transactionDateTime = $row_result['close_time'];
                    $paymentDataResult[] = array(
                        'Contact Name' => $row_result['firstname'] . ' ' . $row_result['lastname'],
                        'Email' => $row_result['email'],
                        'Level' => $row_result['level'],
                        'Parent IB' => $row_result['parent_affiliate_code'],
                        'Account Number' => $row_result['login'],
                        'Transaction date time' => $transactionDateTime,
                        'Amount' => $row_result['profit'],
                        'Comment' => $row_result['comment'],
                    );
                    if($row_result['profit'] > 0)
                    {
                        $summary['total_in_amount'] += $row_result['profit'];
                    }
                    else
                    {
                        $summary['total_out_amount'] += $row_result['profit'];
                    }
                    
                }
            }
        }
        else
        {
            $paymentDataResult = array();
        }
        if ($operation == 'ExcelExport' && $outputformat == 'PDF') {
            $excelSheetFormateData = array();
            foreach ($paymentDataResult as $data_value) {
                foreach ($data_value as $columnName => $value) {
                    $values = array('value' => $value, 'type' => "string");
                    $ExcelExportResult[$columnName] = $values;
                }
                $excelSheetFormateData[] = $ExcelExportResult;
            }
            $reportResult = $excelSheetFormateData;
        } else {
            $reportResult = $paymentDataResult;
        }
        $reportResult['summary'] = $summary;
        return $reportResult;
    }
    
    
    public function getIbCommissionEarnedData($outputformat, $operation, $filtersql) {
        global $adb,$current_user;
        $max_ib_level = configvar('max_ib_level');
        $dateRange = $filtersql[0]['value'];
        $contactid = $filtersql[1]['value'];

        if(!empty($dateRange))
        {
            if ($current_user->date_format == 'dd-mm-yyyy') {
                $date_formate = 'd-m-Y';
            } elseif ($current_user->date_format == 'mm-dd-yyyy') {
                $date_formate = 'm-d-Y';
            } elseif ($current_user->date_format == 'yyyy-mm-dd') {
                $date_formate = 'Y-m-d';
            }
            if ($dateRange != '') {
                $startdateFormat = DateTime::createFromFormat($date_formate, $dateRange);
                $startDateTime = $startdateFormat->format('Y-m-d');
            }
            $whereEnd = $groupBy = $sortBy = '';

            $sortBy .= " ORDER BY commission_amount DESC";
            $groupBy .= " GROUP BY `t`.`parent_contactid`";
            $whereEnd .= " AND DATE_FORMAT(t.`close_time`, '%Y-%m-%d')  = '" . $startDateTime . "'";
 
            if(!empty($contactid))
            {
                $childIbHierarchy = getIBHierarchyFromContactId($contactid);
                $whereEnd .= " AND child.`ib_hierarchy` LIKE '" . $childIbHierarchy . "%'";
            }
            
            $ibHierarchy = getIBHierarchyFromContactId($contactid);
            $sql_get_level = "(SELECT findIBLevel(REPLACE(child.`ib_hierarchy`, '" . $ibHierarchy . "', '')))";
            
            $query = "SELECT sum(t.volume) as volume, sum(t.commission_amount) as commission_amount, child.firstname,"
                        . " child.lastname, child.email, child.affiliate_code, child.parent_affiliate_code, child.record_status, " . $sql_get_level . " AS ib_depth"
                        . " FROM anl_comm_child AS t"
                        . " INNER JOIN vtiger_contactdetails AS child ON child.`contactid` = t.parent_contactid"
                        . " INNER JOIN `vtiger_crmentity` AS ce ON ce.`crmid` = child.`contactid`"
                        . " WHERE ce.`deleted` = 0 " . $whereEnd . $groupBy . $sortBy;
//            echo $query; exit;
            $result = $adb->pquery($query, array());
            $num_rows = $adb->num_rows($result);
            $ibCommDataResult = array();
            
            if ($num_rows > 0) {
                while ($row_result = $adb->fetchByAssoc($result)) {
                    $affiliate_code = '';
                    if ($row_result['record_status'] === 'Approved') {
                        $affiliate_code = $row_result['affiliate_code'];
                    }
                    
                    $ibCommDataResult[] = array(
                        'Contact Name' => $row_result['firstname'] . ' ' . $row_result['lastname'],
                        'Email' => $row_result['email'],
                        'Parent IB Code' => $row_result['parent_affiliate_code'],
                        'Affiliate Code' => $affiliate_code,
                        'Total Volume' => $row_result['volume'],
                        'Total earned commission' => $row_result['commission_amount'],
                    );
                }
            }
        }
        else
        {
            $ibCommDataResult = array();
        }
        if ($operation == 'ExcelExport' && $outputformat == 'PDF') {
            $excelSheetFormateData = array();
            foreach ($ibCommDataResult as $data_value) {
                foreach ($data_value as $columnName => $value) {
                    $values = array('value' => $value, 'type' => "string");
                    $ExcelExportResult[$columnName] = $values;
                }
                $excelSheetFormateData[] = $ExcelExportResult;
            }
            $reportResult = $excelSheetFormateData;
        } else {
            $reportResult = $ibCommDataResult;
        }
        return $reportResult;
    }
    
    
    public function getIbCommissionAnalyzedData($outputformat, $operation, $filtersql) {
        global $adb,$current_user;
        $max_ib_level = configvar('max_ib_level');
        $dateRange = $filtersql[0]['value'];
        $contactid = $filtersql[1]['value'];
        $child_contactid = $filtersql[2]['value'];
        $include_child = $filtersql[3]['value'];

        if(!empty($contactid))
        {
                $whereEnd = $groupBy = '';

                $createdtime = explode(",", $filtersql[0]['value']);

                if ($current_user->date_format == 'dd-mm-yyyy') {
                    $date_formate = 'd-m-Y';
                } elseif ($current_user->date_format == 'mm-dd-yyyy') {
                    $date_formate = 'm-d-Y';
                } elseif ($current_user->date_format == 'yyyy-mm-dd') {
                    $date_formate = 'Y-m-d';
                }
                if ($createdtime[0] != '') {
                    $startdateFormat = DateTime::createFromFormat($date_formate, $createdtime[0]);
                    $startDateTime = $startdateFormat->format('Y-m-d');
                }
                $startDateTime = $startDateTime . ' 00:00:00';

                if ($createdtime[1] != '') {
                    $enddateFormat = DateTime::createFromFormat($date_formate, $createdtime[1]);
                    $endDateTime = $enddateFormat->format('Y-m-d');
                }
                $endDateTime = $endDateTime . ' 23:59:59';
            
            $groupBy .= " GROUP BY t.parent_contactid";
            $whereEnd .= " AND t.`close_time` >= '" . $startDateTime . "' AND t.`close_time` <= '" . $endDateTime . "'";
 
            if ($child_contactid != '' && $include_child == '1') {
                $childIbHierarchy = getIBHierarchyFromContactId($child_contactid);
                $whereEnd .= " AND c.`ib_hierarchy` LIKE '" . $childIbHierarchy . "%'";
            } else if ($child_contactid != '') {
                $whereEnd .= " AND c.`contactid` = " . $child_contactid;
            } else {
                $whereEnd .= " AND c.`contactid` = " . $contactid;
            }
            
            $ibHierarchy = getIBHierarchyFromContactId($contactid);
            $sql_get_level = "(SELECT findIBLevel(REPLACE(`c`.`ib_hierarchy`, '" . $ibHierarchy . "', ''))) ";
            
            $query = "SELECT sum(t.volume) as volume, sum(t.commission_amount) as commission_amount, c.firstname, c.parent_affiliate_code,"
                        . " sum(IF(t.commission_withdraw_status = 1, t.commission_amount, 0)) as withdrawal_completed, "
                        . " sum(IF(t.commission_withdraw_status = 0, t.commission_amount, 0)) as withdrawal_pending, "
                        . " c.lastname, c.email, c.affiliate_code, c.record_status, " . $sql_get_level . " AS level"
                        . " FROM tradescommission AS t"
                        . " INNER JOIN vtiger_contactdetails AS c ON c.`contactid` = t.parent_contactid"
                        . " INNER JOIN `vtiger_crmentity` AS ce ON ce.`crmid` = c.`contactid`"
                        . " WHERE ce.`deleted` = 0 " . $whereEnd . $groupBy;
//            echo $query; exit;
            $result = $adb->pquery($query, array());
            $num_rows = $adb->num_rows($result);
            $paymentDataResult = array();
            
            if ($num_rows > 0) {
                while ($row_result = $adb->fetchByAssoc($result)) {
                    $affiliate_code = '';
                    if ($row_result['record_status'] === 'Approved') {
                        $affiliate_code = $row_result['affiliate_code'];
                    }
                    
                    $transactionDateTime = $row_result['close_time'];
                    $paymentDataResult[] = array(
                        'Contact Name' => $row_result['firstname'] . ' ' . $row_result['lastname'],
                        'Email' => $row_result['email'],
                        'Level' => $row_result['level'],
                        'Parent IB' => $row_result['parent_affiliate_code'],
                        'Affiliate Code' => $affiliate_code,
                        'Total Volume' => $row_result['volume'],
                        'Total Earned Commission' => $row_result['commission_amount'],
                        'Withdrawal Completed' => $row_result['withdrawal_completed'],
                        'Withdrawal Pending' => $row_result['withdrawal_pending'],
                    );
                    
                }
            }
        }
        else
        {
            $paymentDataResult = array();
        }
        if ($operation == 'ExcelExport' && $outputformat == 'PDF') {
            $excelSheetFormateData = array();
            foreach ($paymentDataResult as $data_value) {
                foreach ($data_value as $columnName => $value) {
                    $values = array('value' => $value, 'type' => "string");
                    $ExcelExportResult[$columnName] = $values;
                }
                $excelSheetFormateData[] = $ExcelExportResult;
            }
            $reportResult = $excelSheetFormateData;
        } else {
            $reportResult = $paymentDataResult;
        }
        return $reportResult;
    }
    // End

    public function getIBCommAmountCalculation($contactId) {
        global $adb;
        
        $query = "SELECT  SUM(IF(t.commission_withdraw_status = 0, t.commission_amount, 0)) as withdrawal_pending FROM tradescommission AS t INNER JOIN vtiger_contactdetails AS c ON c.`contactid` = t.parent_contactid WHERE c.`contactid` = ?";
        $result = $adb->pquery($query, array($contactId));
        $available_comm_amount = 0;
        if(!$available_comm_amount){
            $available_comm_amount = $adb->query_result($result, 0, 'withdrawal_pending');
        }
        return $available_comm_amount;
    }
    
    public function getIbPendingCommissionData($outputformat, $operation, $filtersql) {
        global $adb,$current_user;
        $max_ib_level = configvar('max_ib_level');
        $dateRange = $filtersql[0]['value'];

        if(!empty($dateRange))
        {
                $whereEnd = $groupBy = '';

                $createdtime = explode(",", $filtersql[0]['value']);

                if ($current_user->date_format == 'dd-mm-yyyy') {
                    $date_formate = 'd-m-Y';
                } elseif ($current_user->date_format == 'mm-dd-yyyy') {
                    $date_formate = 'm-d-Y';
                } elseif ($current_user->date_format == 'yyyy-mm-dd') {
                    $date_formate = 'Y-m-d';
                }
                if ($createdtime[0] != '') {
                    $startdateFormat = DateTime::createFromFormat($date_formate, $createdtime[0]);
                    $startDateTime = $startdateFormat->format('Y-m-d');
                }
                $startDateTime = $startDateTime . ' 00:00:00';

                if ($createdtime[1] != '') {
                    $enddateFormat = DateTime::createFromFormat($date_formate, $createdtime[1]);
                    $endDateTime = $enddateFormat->format('Y-m-d');
                }
                $endDateTime = $endDateTime . ' 23:59:59';
            
            $groupBy .= " GROUP BY t.parent_contactid";
            $whereEnd .= " AND t.`close_time` >= '" . $startDateTime . "' AND t.`close_time` <= '" . $endDateTime . "'";
            $query = "SELECT sum(t.volume) as volume, sum(t.commission_amount) as commission_amount, c.firstname, c.parent_affiliate_code,"
                        . " sum(IF(t.commission_withdraw_status = 1, t.commission_amount, 0)) as withdrawal_completed, "
                        . " sum(IF(t.commission_withdraw_status = 0, t.commission_amount, 0)) as withdrawal_pending, "
                        . " c.lastname, c.email, c.affiliate_code, c.record_status"
                        . " FROM tradescommission AS t"
                        . " INNER JOIN vtiger_contactdetails AS c ON c.`contactid` = t.parent_contactid"
                        . " INNER JOIN `vtiger_crmentity` AS ce ON ce.`crmid` = c.`contactid`"
                        . " WHERE ce.`deleted` = 0 AND t.commission_withdraw_status = 0 AND c.record_status = 'Approved' " . $whereEnd . $groupBy;
//            echo $query; exit;
            $result = $adb->pquery($query, array());
            $num_rows = $adb->num_rows($result);
            $paymentDataResult = array();
            
            if ($num_rows > 0) {
                while ($row_result = $adb->fetchByAssoc($result)) {
                    $affiliate_code = $row_result['affiliate_code'];
                    $paymentDataResult[] = array(
                        'Contact Name' => $row_result['firstname'] . ' ' . $row_result['lastname'],
                        'Email' => $row_result['email'],
                        'Parent IB' => $row_result['parent_affiliate_code'],
                        'Affiliate Code' => $affiliate_code,
                        'Total Volume' => $row_result['volume'],
                        'Total Earned Commission' => $row_result['commission_amount'],
                        'Withdrawal Pending' => $row_result['withdrawal_pending'],
                    );
                }
            }
        }
        else
        {
            $paymentDataResult = array();
        }
        if ($operation == 'ExcelExport' && $outputformat == 'PDF') {
            $excelSheetFormateData = array();
            foreach ($paymentDataResult as $data_value) {
                foreach ($data_value as $columnName => $value) {
                    $values = array('value' => $value, 'type' => "string");
                    $ExcelExportResult[$columnName] = $values;
                }
                $excelSheetFormateData[] = $ExcelExportResult;
            }
            $reportResult = $excelSheetFormateData;
        } else {
            $reportResult = $paymentDataResult;
        }
        return $reportResult;
    }
}
