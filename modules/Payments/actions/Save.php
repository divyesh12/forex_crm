<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Payments_Save_Action extends Vtiger_Save_Action {

    /**
     * Function to save record
     * @param <Vtiger_Request> $request - values of the record
     * @return <RecordModel> - record Model of saved record
     */
    public function saveRecord($request) {
        /*try {*/
            global $log, $adb;
            $log->debug('Entering into saverecord of paymentss...');
            $log->debug($request);
            $approveUserModel = Users_Record_Model::getCurrentUserModel();
            $approvedBy = $approveUserModel->first_name . ' ' . $approveUserModel->last_name;
            $recordModel = $this->getRecordModelFromRequest($request);
            /*Queue validation*/
                $log->debug('Payment in queue..');
                $paymentData = array();
                $paymentData['payment_type'] = $recordModel->get('payment_type');
                $paymentData['contact_id'] = $recordModel->get('contactid');
                $isPaymentInQueue = isPaymentInQueue($paymentData);

                if($isPaymentInQueue)
                {
                    throw new AppException(vtranslate('PAYMENT_IN_QUEUE_ERROR', 'Payments'));exit;
                }
                else
                {
                    insertPaymentInQueue($paymentData);
                }
            /*Queue validation*/
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

            /*Update approved by*/
            $paymentStatus = $recordModel->get('payment_status');
            if(!empty($this->savedRecordId) && $paymentStatus != 'Pending')
            {
                $updateSql = "UPDATE vtiger_payments SET approved_by = ? WHERE paymentsid = ?";
                $updateResult = $adb->pquery($updateSql, array($approvedBy,$this->savedRecordId));
            }
            /*Update approved by*/
            /*Queue validation*/
            $log->debug('Payment out from queue..');
            completePaymentInQueue($paymentData);
            /*Queue validation*/
        
            return $recordModel;
        /*} catch (AppException $e) {
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
        }*/
    }

}
