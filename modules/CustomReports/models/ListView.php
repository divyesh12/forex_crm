<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class CustomReports_ListView_Model extends Vtiger_ListView_Model {

    /**
     * @creator: Divyesh Chothani
     * @date: 14-11-2019
     * @comment: Add for get CustomReports Based on Contacts 
     */
    function getQuery() {
        $sourceModule = $_REQUEST['src_module'];
        $sourceField = $_REQUEST['src_field'];
        $related_parent_module = $_REQUEST['related_parent_module'];
        $popupModule = $_REQUEST['module'];
        $view = $_REQUEST['view'];

        $whereCondition = '';
        if ($sourceModule == 'Payments' && $related_parent_module == 'Contacts' && $popupModule == 'CustomReports' && $sourceField == 'liveaccountid' && $view == 'Popup') {
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
        } else if ($sourceModule == 'LeverageHistory' && $related_parent_module == 'Contacts' && $popupModule == 'CustomReports' && $sourceField == 'liveaccountid' && $view == 'Popup') {

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

}
