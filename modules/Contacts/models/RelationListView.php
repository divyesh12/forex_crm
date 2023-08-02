<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_RelationListView_Model extends Vtiger_RelationListView_Model {

    public function getEntries($pagingModel) {
        // echo "sadfdsaf"; exit;
        $db = PearDatabase::getInstance();
        $parentModule = $this->getParentRecordModel()->getModule();
        $relationModule = $this->getRelationModel()->getRelationModuleModel();
        $parentModuleName = $parentModule->get('name');
        $relationModuleName = $relationModule->get('name');
        $relatedColumnFields = $relationModule->getConfigureRelatedListFields();
        if (count($relatedColumnFields) <= 0) {
            $relatedColumnFields = $relationModule->getRelatedListFields();
        }

//        echo "<pre>";
//        print_r($relatedColumnFields);
//        echo $fields_implode = implode(",", $relatedColumnFields);
//        exit;
        if ($relationModuleName == 'Calendar') {
            //Adding visibility in the related list, showing records based on the visibility
            $relatedColumnFields['visibility'] = 'visibility';
        }

        if ($relationModuleName == 'PriceBooks') {
            //Adding fields in the related list
            $relatedColumnFields['unit_price'] = 'unit_price';
            $relatedColumnFields['listprice'] = 'listprice';
            $relatedColumnFields['currency_id'] = 'currency_id';
        }

        $query = $this->getRelationQuery();

        if ($this->get('whereCondition') && is_array($this->get('whereCondition'))) {
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $queryGenerator = new QueryGenerator($relationModuleName, $currentUser);
            $queryGenerator->setFields(array_values($relatedColumnFields));
            $whereCondition = $this->get('whereCondition');
            foreach ($whereCondition as $fieldName => $fieldValue) {
                if (is_array($fieldValue)) {
                    $comparator = $fieldValue[1];
                    $searchValue = $fieldValue[2];
                    $type = $fieldValue[3];
                    if ($type == 'time') {
                        $searchValue = Vtiger_Time_UIType::getTimeValueWithSeconds($searchValue);
                    }
                    $queryGenerator->addCondition($fieldName, $searchValue, $comparator, "AND");
                }
            }
            $whereQuerySplit = split("WHERE", $queryGenerator->getWhereClause());
            $query .= " AND " . $whereQuerySplit[1];
        }

        $startIndex = $pagingModel->getStartIndex();
        $pageLimit = $pagingModel->getPageLimit();

        $orderBy = $this->getForSql('orderby');
        $sortOrder = $this->getForSql('sortorder');

        if ($orderBy) {

            $orderByFieldModuleModel = $relationModule->getFieldByColumn($orderBy);
            if ($orderByFieldModuleModel && $orderByFieldModuleModel->isReferenceField()) {
                //If reference field then we need to perform a join with crmentity with the related to field
                $queryComponents = $split = preg_split('/ where /i', $query);
                $selectAndFromClause = $queryComponents[0];
                $whereCondition = $queryComponents[1];
                $qualifiedOrderBy = 'vtiger_crmentity' . $orderByFieldModuleModel->get('column');
                $selectAndFromClause .= ' LEFT JOIN vtiger_crmentity AS ' . $qualifiedOrderBy . ' ON ' .
                        $orderByFieldModuleModel->get('table') . '.' . $orderByFieldModuleModel->get('column') . ' = ' .
                        $qualifiedOrderBy . '.crmid ';
                $query = $selectAndFromClause . ' WHERE ' . $whereCondition;
                $query .= ' ORDER BY ' . $qualifiedOrderBy . '.label ' . $sortOrder;
            } elseif ($orderByFieldModuleModel && $orderByFieldModuleModel->isOwnerField()) {
                $query .= ' ORDER BY COALESCE(CONCAT(vtiger_users.first_name,vtiger_users.last_name),vtiger_groups.groupname) ' . $sortOrder;
            } else {
                // Qualify the the column name with table to remove ambugity
                $qualifiedOrderBy = $orderBy;
                $orderByField = $relationModule->getFieldByColumn($orderBy);
                if ($orderByField) {
                    $qualifiedOrderBy = $relationModule->getOrderBySql($qualifiedOrderBy);
                }
                if ($qualifiedOrderBy == 'vtiger_activity.date_start' && ($relationModuleName == 'Calendar' || $relationModuleName == 'Emails')) {
                    $qualifiedOrderBy = "str_to_date(concat(vtiger_activity.date_start,vtiger_activity.time_start),'%Y-%m-%d %H:%i:%s')";
                }
                $query = "$query ORDER BY $qualifiedOrderBy $sortOrder";
            }
        } else if ($relationModuleName == 'HelpDesk' && empty($orderBy) && empty($sortOrder) && $moduleName != "Users") {
            $query .= ' ORDER BY vtiger_crmentity.modifiedtime DESC';
        } else if (empty($orderBy) && empty($sortOrder) && $moduleName == "TradesCommission") {
            $query .= ' ORDER BY tradescommission.createdtime DESC';
        } else if (empty($orderBy) && empty($sortOrder) && $moduleName != "Users") {
            $query .= ' ORDER BY vtiger_crmentity.crmid DESC';
        }

        if ($parentModuleName == 'Contacts' && $relationModuleName == 'TradesCommission') {
            $pageLimit = 1000;
            $parent_contactid = $this->getParentRecordModel()->getId();
            $query = $this->getTradesCommissionQuery($relatedColumnFields, $parent_contactid, 'query');
            $limitQuery = $query . ' LIMIT ' . $startIndex . ',' . $pageLimit;
        } else {
            $limitQuery = $query . ' LIMIT ' . $startIndex . ',' . $pageLimit;
        }
        $result = $db->pquery($limitQuery, array());

        $relatedRecordList = array();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $groupsIds = Vtiger_Util_Helper::getGroupsIdsForUsers($currentUser->getId());
        $recordsToUnset = array();
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $row = $db->fetch_row($result, $i);
            $newRow = array();
            foreach ($row as $col => $val) {
                if (array_key_exists($col, $relatedColumnFields)) {
                    $newRow[$relatedColumnFields[$col]] = $val;
                }
            }
            //To show the value of "Assigned to"
            $ownerId = $row['smownerid'];
            $newRow['assigned_user_id'] = $row['smownerid'];
            if ($relationModuleName == 'Calendar') {
                $visibleFields = array('activitytype', 'date_start', 'time_start', 'due_date', 'time_end', 'assigned_user_id', 'visibility', 'smownerid', 'parent_id');
                $visibility = true;
                if (in_array($ownerId, $groupsIds)) {
                    $visibility = false;
                } else if ($ownerId == $currentUser->getId()) {
                    $visibility = false;
                }
                if (!$currentUser->isAdminUser() && $newRow['activitytype'] != 'Task' && $newRow['visibility'] == 'Private' && $ownerId && $visibility) {
                    foreach ($newRow as $data => $value) {
                        if (in_array($data, $visibleFields) != -1) {
                            unset($newRow[$data]);
                        }
                    }
                    $newRow['subject'] = vtranslate('Busy', 'Events') . '*';
                }
                if ($newRow['activitytype'] == 'Task') {
                    unset($newRow['visibility']);
                }
            }

            $record = Vtiger_Record_Model::getCleanInstance($relationModule->get('name'));
            $record->setData($newRow)->setModuleFromInstance($relationModule)->setRawData($row);
            if ($parentModuleName == 'Contacts' && $relationModuleName == 'TradesCommission') {
                $relatedRecordList[] = $record;
            } else {
                $record->setId($row['crmid']);
                $relatedRecordList[$row['crmid']] = $record;
            }

            if ($relationModuleName == 'Calendar' && !$currentUser->isAdminUser() && $newRow['activitytype'] == 'Task' && isToDoPermittedBySharing($row['crmid']) == 'no') {
                $recordsToUnset[] = $row['crmid'];
            }
        }
        $pagingModel->calculatePageRange($relatedRecordList);

        $nextLimitQuery = $query . ' LIMIT ' . ($startIndex + $pageLimit) . ' , 1';
        $nextPageLimitResult = $db->pquery($nextLimitQuery, array());
        if ($db->num_rows($nextPageLimitResult) > 0) {
            $pagingModel->set('nextPageExists', true);
        } else {
            $pagingModel->set('nextPageExists', false);
        }
        //setting related list view count before unsetting permission denied records - to make sure paging should not fail
        $pagingModel->set('_relatedlistcount', count($relatedRecordList));
        foreach ($recordsToUnset as $record) {
            unset($relatedRecordList[$record]);
        }

//        echo "<pre>";
//        print_r($relatedRecordList);
//        exit;
        return $relatedRecordList;
    }

    /**
     * Function to get Total number of record in this relation
     * @return <Integer>
     */
    public function getRelatedEntriesCount() {
        $db = PearDatabase::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $realtedModuleModel = $this->getRelatedModuleModel();
        $relatedModuleName = $realtedModuleModel->getName();

        $parentModule = $this->getParentRecordModel()->getModule();
        $relationModule = $this->getRelationModel()->getRelationModuleModel();
        $parentModuleName = $parentModule->get('name');
        $relationModuleName = $relationModule->get('name');

        $relatedColumnFields = $relationModule->getConfigureRelatedListFields();
        if (count($relatedColumnFields) <= 0) {
            $relatedColumnFields = $relationModule->getRelatedListFields();
        }

        $relationQuery = $this->getRelationQuery();
        $relationQuery = preg_replace("/[ \t\n\r]+/", " ", $relationQuery);
        $position = stripos($relationQuery, ' from ');
        if ($position) {
            $split = preg_split('/ FROM /i', $relationQuery);
            $splitCount = count($split);
            if ($relatedModuleName == 'Calendar') {
                $relationQuery = 'SELECT DISTINCT vtiger_crmentity.crmid, vtiger_activity.activitytype ';
            } else {
                $relationQuery = 'SELECT COUNT(DISTINCT vtiger_crmentity.crmid) AS count';
            }
            for ($i = 1; $i < $splitCount; $i++) {
                $relationQuery = $relationQuery . ' FROM ' . $split[$i];
            }
        }
        if (strpos($relationQuery, ' GROUP BY ') !== false) {
            $parts = explode(' GROUP BY ', $relationQuery);
            $relationQuery = $parts[0];
        }

        if ($parentModuleName == 'Contacts' && $relationModuleName == 'TradesCommission') {
            $parent_contactid = $this->getParentRecordModel()->getId();
            $relationQuery = $this->getTradesCommissionQuery($relatedColumnFields, $parent_contactid, 'query');
            $trade_commission_count = $this->getTradesCommissionQuery($relatedColumnFields, $parent_contactid, 'count');
        }

        $result = $db->pquery($relationQuery, array());
        if ($result) {
            if ($relatedModuleName == 'Calendar') {
                $count = 0;
                for ($i = 0; $i < $db->num_rows($result); $i++) {
                    $id = $db->query_result($result, $i, 'crmid');
                    $activityType = $db->query_result($result, $i, 'activitytype');
                    if (!$currentUser->isAdminUser() && $activityType == 'Task' && isToDoPermittedBySharing($id) == 'no') {
                        continue;
                    } else {
                        $count++;
                    }
                }
                return $count;
            } elseif ($parentModuleName == 'Contacts' && $relatedModuleName == 'TradesCommission') {
                return $trade_commission_count;
            } else {
                return $db->query_result($result, 0, 'count');
            }
        } else {
            return 0;
        }
    }

    function getTradesCommissionQuery($columns, $parent_contactid, $return_result) {
        $db = PearDatabase::getInstance();
        $max_ib_level = configvar('max_ib_level');
        $whereCondition = ' WHERE  vtiger_crmentity.deleted =0 AND tradescommission.hierachy_level <= "' . $max_ib_level . '" AND tradescommission.parent_contactid = ' . $parent_contactid;

        if ($return_result == 'query') {
            $query = 'SELECT ' . implode(",", $columns) . '
                    FROM tradescommission 
                    INNER JOIN vtiger_contactdetails AS vtiger_contactdetails ON vtiger_contactdetails.contactid = tradescommission.parent_contactid INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid'.$whereCondition;
            return $query;
        } elseif ($return_result == 'count') {
            return 0;
            $query = 'SELECT count(tradescommission.ticket) AS count
                    FROM tradescommission 
                    INNER JOIN vtiger_contactdetails AS vtiger_contactdetails ON vtiger_contactdetails.contactid = tradescommission.parent_contactid INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid '.$whereCondition;
            $result = $db->pquery($query, array());
            $count = $db->query_result($result, 0, 'count');
            return $count;
        }
    }

}
