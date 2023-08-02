<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class IBCommissionProfile_Save_Action extends Vtiger_Save_Action {

    /**
     * Function to save record
     * @param <Vtiger_Request> $request - values of the record
     * @return <RecordModel> - record Model of saved record
     */
    public function saveRecord($request) {
        $recordModel = $this->getRecordModelFromRequest($request);
        if ($request->get('imgDeleted')) {
            $imageIds = $request->get('imageid');
            foreach ($imageIds as $imageId) {
                $status = $recordModel->deleteImage($imageId);
            }
        }
        $recordModel->save();
        if ($request->get('relationOperation')) {
            $parentModuleName = $request->get('sourceModule');
            $parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
            $parentRecordId = $request->get('sourceRecord');
            $relatedModule = $recordModel->getModule();
            $relatedRecordId = $recordModel->getId();
            if ($relatedModule->getName() == 'Events') {
                $relatedModule = Vtiger_Module_Model::getInstance('Calendar');
            }
            $relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
            $relationModel->addRelation($parentRecordId, $relatedRecordId);
        }
        $this->savedRecordId = $recordModel->getId();
        $this->saveIBCommissionItems($request, $recordModel->getId());
        return $recordModel;
    }

    protected function saveIBCommissionItems($request, $ibcommissionprofileid) {
        //  echo "<pre>"; print_r($request); exit;
        $moduleName = 'IBCommissionProfileItems';
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $fieldModelList = $moduleModel->getFields();
        for ($i = 0; $i < count($request->get("ibcommissionprofileitemsid")); $i++) {
            $recordId = $request->get("ibcommissionprofileitemsid");
            $recordId = $recordId[$i];
            if (isset($recordId) && !empty($recordId)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
                $recordModel->set('id', $recordId);
                $recordModel->set('mode', 'edit');
            } else {
                $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
                $recordModel->set('mode', '');
            }
            foreach ($fieldModelList as $fieldName => $fieldModel) {
                $fieldValue = $request->get($fieldName)[$i];
                $recordModel->set($fieldName, trim($fieldValue));
            }
            $recordModel->set('ibcommissionprofileid', $ibcommissionprofileid);
            $recordModel->save();
        }
    }

}
