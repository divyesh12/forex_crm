<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_ServiceProviders_Module_Model extends Settings_Vtiger_Module_Model {

    var $baseTable = 'vtiger_serviceproviders_servers';
    var $nameFields = array();
    var $listFields = array('providertype' => 'Provider', 'title' => 'Title', 'isactive' => 'Active', 'sequence_number' => 'Sequence Number');
    var $name = 'ServiceProviders';

    /**
     * Function to get editable fields from this module
     * @return <Array> list of editable fields
     */
    public function getEditableFields() {
        $fieldsList = array(
            array('name' => 'providertype', 'label' => 'Provider', 'type' => 'picklist'),
            array('name' => 'isactive', 'label' => 'Active', 'type' => 'radio'),
            array('name' => 'title', 'label' => 'Title', 'type' => 'text')
        );

        $fieldModelsList = array();
        foreach ($fieldsList as $fieldInfo) {
            $fieldModelsList[$fieldInfo['name']] = Settings_ServiceProviders_Field_Model::getInstanceByRow($fieldInfo);
        }
        return $fieldModelsList;
    }

    /**
     * Function to get Create view url
     * @return <String> Url
     */
    public function getCreateRecordUrl() {
        return 'javascript:Settings_ServiceProviders_List_Js.triggerEdit(event, "index.php?module=' . $this->getName() . '&parent=' . $this->getParentName() . '&view=Edit")';
    }

    /**
     * Function to get List view url
     * @return <String> Url
     */
    public function getListViewUrl() {
        return "index.php?module=" . $this->getName() . "&parent=" . $this->getParentName() . "&view=List";
    }

    /**
     * Function to get list of all providers
     * @return <Array> list of all providers <ServiceProviders_Provider_Model>
     */
    public function getAllProviders() {
        if (!$this->allProviders) {
            $this->allProviders = ServiceProviders_Provider_Model::getAll();
        }
        return $this->allProviders;
    }

    /**
     * Function to delete records
     * @param <Array> $recordIdsList
     * @return <Boolean> true/false
     */
    public static function deleteRecords($recordIdsList = array()) {
        if ($recordIdsList) {
            $db = PearDatabase::getInstance();
            $query = 'DELETE FROM vtiger_serviceproviders_servers WHERE id IN (' . generateQuestionMarks($recordIdsList) . ')';
            $db->pquery($query, $recordIdsList);
            return true;
        }
        return false;
    }

}
