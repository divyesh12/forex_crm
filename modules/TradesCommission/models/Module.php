<?php

class TradesCommission_Module_Model extends Vtiger_Module_Model {

    public function isCommentEnabled() {
        return true;
    }

    public function checkRecordStatus($record) {
        if (!empty($record)) {
            $module = $this->getModule();
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
            $modelData = $recordModel->getData();
        }
        return false;
    }

    public function getModuleBasicLinks() {
        $basicLinks = parent::getModuleBasicLinks();
        foreach ($basicLinks as $key => $basicLink) {
            if (in_array($basicLink['linklabel'], array('LBL_ADD_RECORD', 'LBL_IMPORT'))) {
                unset($basicLinks[$key]);
            }
        }
        return $basicLinks;
    }

    public function getSettingLinks() {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $settingLinks = parent::getSettingLinks();
        foreach ($settingLinks as $key => $settingLink) {
            if (in_array($settingLink['linklabel'], array('LBL_EDIT_FIELDS', 'LBL_EDIT_WORKFLOWS', 'LBL_EDIT_PICKLIST_VALUES'))) {
                unset($settingLinks[$key]);
            }
        }
        return $settingLinks;
    }

}

?>