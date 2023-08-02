<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Payments_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	public function process(Vtiger_Request $request) {
		global $adb;
		$fieldToBeSaved = $request->get('field');
		$response = new Vtiger_Response();
		try {
			$approveUserModel = Users_Record_Model::getCurrentUserModel();
			$approvedBy = $approveUserModel->first_name . ' ' . $approveUserModel->last_name;
			$recordModel = $this->getRecordModelFromRequest($request);
			/*Queue validation*/
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
			vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', $request->get('_timeStampNoChangeMode',false));
			$recordModel = $this->saveRecord($request);
			vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', false);

			$fieldModelList = $recordModel->getModule()->getFields();
			$result = array();
			$picklistColorMap = array();
			foreach ($fieldModelList as $fieldName => $fieldModel) {
				if($fieldModel->isViewable()){
					$recordFieldValue = $recordModel->get($fieldName);
					if(is_array($recordFieldValue) && $fieldModel->getFieldDataType() == 'multipicklist') {
						foreach ($recordFieldValue as $picklistValue) {
							$picklistColorMap[$picklistValue] = Settings_Picklist_Module_Model::getPicklistColorByValue($fieldName, $picklistValue);
						}
						$recordFieldValue = implode(' |##| ', $recordFieldValue);     
					}
					if($fieldModel->getFieldDataType() == 'picklist') {
						$picklistColorMap[$recordFieldValue] = Settings_Picklist_Module_Model::getPicklistColorByValue($fieldName, $recordFieldValue);
					}
					$fieldValue = $displayValue = Vtiger_Util_Helper::toSafeHTML($recordFieldValue);
					if ($fieldModel->getFieldDataType() !== 'currency' && $fieldModel->getFieldDataType() !== 'datetime' && $fieldModel->getFieldDataType() !== 'date' && $fieldModel->getFieldDataType() !== 'double') { 
						$displayValue = $fieldModel->getDisplayValue($fieldValue, $recordModel->getId()); 
					}
					if ($fieldModel->getFieldDataType() == 'currency') {
						$displayValue = Vtiger_Currency_UIType::transformDisplayValue($fieldValue);
					}
					if(!empty($picklistColorMap)) {
						$result[$fieldName] = array('value' => $fieldValue, 'display_value' => $displayValue, 'colormap' => $picklistColorMap);
					} else {
						$result[$fieldName] = array('value' => $fieldValue, 'display_value' => $displayValue);
					}
				}
			}

			//Handling salutation type
			if ($request->get('field') === 'firstname' && in_array($request->getModule(), array('Contacts', 'Leads'))) {
				$salutationType = $recordModel->getDisplayValue('salutationtype');
				$firstNameDetails = $result['firstname'];
				$firstNameDetails['display_value'] = $salutationType. " " .$firstNameDetails['display_value'];
				if ($salutationType != '--None--') $result['firstname'] = $firstNameDetails;
			}

			$paymentStatus = $recordModel->get('payment_status');
			if(!empty($result['_recordId']) && $paymentStatus != 'Pending')
			{
				$updateSql = "UPDATE vtiger_payments SET approved_by = ? WHERE paymentsid = ?";
				$updateResult = $adb->pquery($updateSql, array($approvedBy,$result['_recordId']));
			}
			/*Queue validation*/
			completePaymentInQueue($paymentData);
			/*Queue validation*/

			// removed decode_html to eliminate XSS vulnerability
			$result['_recordLabel'] = decode_html($recordModel->getName());
			$result['_recordId'] = $recordModel->getId();
			$response->setEmitType(Vtiger_Response::$EMIT_JSON);
			$response->setResult($result);
		} catch (DuplicateException $e) {
			$response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
		} catch (Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}
}
