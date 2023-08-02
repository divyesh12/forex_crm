<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_Save_Action extends Vtiger_Save_Action {

    public function process(Vtiger_Request $request) {
        try {
            //To stop saveing the value of salutation as '--None--'
            $salutationType = $request->get('salutationtype');
            if ($salutationType === '--None--') {
                $request->set('salutationtype', '');
            }

            parent::process($request);
        } catch (AppException $e) {
            $requestData = $request->getAll();
            $moduleName = $request->getModule();
            unset($requestData['action']);
            unset($requestData['__vtrftk']);

            if ($request->isAjax()) {
                $response = new Vtiger_Response();
                $response->setError($e->getMessage(), $e->getMessage(), $e->getMessage());
                $response->emit();
            } else {
                $requestData['view'] = 'Edit';
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

                global $vtiger_current_version;
                $viewer = new Vtiger_Viewer();
                
                $viewer->assign('ERROR_DATA', $e->getMessage());
                $viewer->assign('REQUEST_DATA', $requestData);
                $viewer->assign('REQUEST_URL', $moduleModel->getCreateRecordUrl() . '&record=' . $request->get('record'));
                $viewer->view('RedirectToEditView.tpl', 'Vtiger');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Function to get the record model based on the request parameters
     * @param Vtiger_Request $request
     * @return Vtiger_Record_Model or Module specific Record Model instance
     */
    protected function getRecordModelFromRequest(Vtiger_Request $request) {

        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);



        if (!empty($recordId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $recordModel->set('id', $recordId);
            $recordModel->set('mode', 'edit');
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            $recordModel->set('mode', '');
        }

        $fieldModelList = $moduleModel->getFields();
        foreach ($fieldModelList as $fieldName => $fieldModel) {
            $fieldValue = $request->get($fieldName, null);
            $fieldDataType = $fieldModel->getFieldDataType();
            if ($fieldDataType == 'time' && $fieldValue !== null) {
                $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
            }
            if ($fieldValue !== null) {
                if (!is_array($fieldValue) && $fieldDataType != 'currency') {
                    $fieldValue = trim($fieldValue);
                }

                /**
                 *  Add By:- Divyesh Chothani
                 * Date:-12-12-2019
                 * Comment:- Unset fields when create record and edit record
                 */
                $readonlyFields = Contacts_Record_Model::get_readonlyFields('CRM');
                $createRecordReadonlyFields = $readonlyFields['create_fields'];
                $updateRecordReadonlyFields = $readonlyFields['edit_fields'];
                if ($recordId) {
                    if (count($createRecordReadonlyFields) > 0) {
                        $updateRecordReadonlyFields = array_merge($updateRecordReadonlyFields, $createRecordReadonlyFields);
                    }
                    if (!in_array($fieldName, $updateRecordReadonlyFields)) {
                        $recordModel->set($fieldName, $fieldValue);
                    }
                } else {
                    if (!in_array($fieldName, $createRecordReadonlyFields)) {
                        $recordModel->set($fieldName, $fieldValue);
                    }
                }
                // $recordModel->set($fieldName, $fieldValue);
            }
        }
        return $recordModel;
    }

}
