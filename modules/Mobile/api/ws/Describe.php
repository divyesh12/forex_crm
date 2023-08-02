<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once 'include/Webservices/DescribeObject.php';

class Mobile_WS_Describe extends Mobile_WS_Controller {
	
	function process(Mobile_API_Request $request) {
		$current_user = $this->getActiveUser();
		$module = $request->get('module');
		$describeInfo = vtws_describe($module, $current_user);

		$fields = $describeInfo['fields'];
		
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$fieldModels = $moduleModel->getFields();
		foreach($fields as $index=>$field) {
			$fieldModel = $fieldModels[$field['name']];
			if($fieldModel) {
				$field['headerfield'] = $fieldModel->get('headerfield');
				$field['summaryfield'] = $fieldModel->get('summaryfield');
			}
			// if($fieldModel && $fieldModel->getFieldDataType() == 'owner') {
			// 	$currentUser = Users_Record_Model::getCurrentUserModel();
            //     $users = $currentUser->getAccessibleUsers();
            //     $usersWSId = Mobile_WS_Utils::getEntityModuleWSId('Users');
            //     foreach ($users as $id => $name) {
            //         unset($users[$id]);
            //         $users[$usersWSId.'x'.$id] = $name; 
            //     }
                
            //     $groups = $currentUser->getAccessibleGroups();
            //     $groupsWSId = Mobile_WS_Utils::getEntityModuleWSId('Groups');
            //     foreach ($groups as $id => $name) {
            //         unset($groups[$id]);
            //         $groups[$groupsWSId.'x'.$id] = $name; 
            //     }
			// 	$field['type']['picklistValues']['users'] = $users; 
			// 	$field['type']['picklistValues']['groups'] = $groups;

			// 	//Special treatment to set default mandatory owner field
			// 	if (!$field['default']) {
			// 		$field['default'] = $usersWSId.'x'.$current_user->id;
			// 	}
			// }

			if($fieldModel && $fieldModel->getFieldDataType() == 'owner') {
				$currentUser = Users_Record_Model::getCurrentUserModel();
			    $users = $currentUser->getAccessibleUsers();
			    $usersWSId = Mobile_WS_Utils::getEntityModuleWSId('Users');
			    $usersCount = 0;
			    foreach ($users as $id => $name) {
			        unset($users[$id]);
			        $users[$usersCount] = array('label'=>$name,'value'=>$usersWSId.'x'.$id,'type'=>'user'); 
			        $usersCount++;
			    }
			    
			    $groups = $currentUser->getAccessibleGroups();
			    $groupsWSId = Mobile_WS_Utils::getEntityModuleWSId('Groups');
			    $groupCount = 0;
			    foreach ($groups as $id => $name) {
			        unset($groups[$id]);
			        //$groups[$groupsWSId.'x'.$id] = $name; 
			        $groups[$groupCount] = array('label'=>$name,'value'=>$groupsWSId.'x'.$id,'type'=>'group'); 
			        $groupCount++;
			    }
			    $usersGroupMerge = array_merge(array_values($users),array_values($groups));
				$field['type']['picklistValues'] = $usersGroupMerge; 
				//$field['type']['picklistValues'] = array_values($groups);

				//Special treatment to set default mandatory owner field
				if (!$field['default']) {
					$field['default'] = $usersWSId.'x'.$current_user->id;
				}
			}
			if($fieldModel && $fieldModel->get('name') == 'salutationtype') {
				$values = $fieldModel->getPicklistValues();
				$picklistValues = array();
				foreach($values as $value => $label) {
					$picklistValues[] = array('value'=>$value, 'label'=>$label);
				}
				$field['type']['picklistValues'] = $picklistValues;
			}
			$newFields[] = $field;
		}
		$fields=null;
		$describeInfo['fields'] = $newFields;
		
		$finalDescribeData = array('describe' => $describeInfo);
		if($module == 'Contacts'){
			$leadMappingWithContactsFields = $this->getLeadMappingFields();
			$finalDescribeData['leadConvertMappingFields'] = $leadMappingWithContactsFields;
		}

		$response = new Mobile_API_Response();
		$response->setResult($finalDescribeData);
		
		return $response;
	}

	/*
	Added By:-  DivyeshChothani
	Comment:- MobileAPI Changes For Lead Convert Mapping form
	*/
	function getLeadMappingFields(){
		global $adb;
		
		$query = "SELECT (SELECT vtiger_field.fieldname FROM  vtiger_field WHERE vtiger_field.fieldid = vtiger_convertleadmapping.leadfid) AS lead_fieldname ,(SELECT vtiger_field.fieldname FROM  vtiger_field WHERE vtiger_field.fieldid = vtiger_convertleadmapping.contactfid) AS contact_fieldname FROM `vtiger_convertleadmapping` INNER JOIN vtiger_field ON vtiger_field.fieldid = vtiger_convertleadmapping.contactfid WHERE vtiger_convertleadmapping.contactfid != 0 AND vtiger_convertleadmapping.leadfid !=0 AND vtiger_field.presence IN (0,2) ORDER BY vtiger_field.sequence ASC;";
		$result = $adb->pquery($query, array());
		$leadMappingData = array();
		while ($row = $adb->fetchByAssoc($result)) {
			$lead_fieldname = $row['lead_fieldname'];
			$contact_fieldname = $row['contact_fieldname'];
			$leadMappingData[$lead_fieldname]= $contact_fieldname;
			
		}
		$leadMappingData['assigned_user_id']= 'assigned_user_id';
		
		return $leadMappingData;
	}
}