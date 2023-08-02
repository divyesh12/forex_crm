<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';

class Mobile_WS_DibortFetchPayments extends Mobile_WS_Controller {
	
        protected $allowedPaymentGateway = 'IPAY';

	function process(Mobile_API_Request $request) {
                include_once 'modules/ThirdParty/language/en_us.lang.php';
                include_once 'modules/ThirdParty/Mobile.Config.php';
		$current_user = $this->getActiveUser();
		$module = 'Payments';
		$filterId = "1";
		$page = $request->get('page','1');
		$limit = $request->get('limit','20');
		$orderBy = 'createdtime';
		$sortOrder = 'DESC';
                $isAccountValid = true;
                
		$response = new Mobile_API_Response();
                $valuesJSONString = $request->get('values');
                $values = "";
                if (!empty($valuesJSONString) && is_string($valuesJSONString)) {
                    $values = Zend_Json::decode($valuesJSONString);
                } else {
                    $values = $valuesJSONString; // Either empty or already decoded.
                }

                if (empty($values)) {
                    $response->setError(1501, "Values cannot be empty!");
                    return $response;
                }
                
                $accountFilter = true;
                if(empty($values['account_id']) || $values['account_id'] == '*')
                {
                    $values['account_id'] = '*';
                    $accountFilter = false;
                }
                
                $validationMessage = array();
                $validationError = false;
                /*Custom Validations*/
                if($accountFilter)
                {
                    $isAccountValid = isAccountFromSpecialGroup($values['account_id']);
                }
                
                if(empty($values['start_time']) || empty($values['end_time']))
                {
                    $validationError = true;
                    $validationMessage = array('code' => "LBL_DATE_FILTER_MISSING_VALIDATION", "message" => $mod_strings["LBL_DATE_FILTER_MISSING_VALIDATION"]);
                }
                else if($accountFilter && !$isAccountValid)
                {
                    $validationError = true;
                    $validationMessage = array('code' => "LBL_ACCOUNT_VALIDATION", "message" => $mod_strings["LBL_ACCOUNT_VALIDATION"]);
                }
                if($validationError)
                {
                    $response->setError($validationMessage['code'], $validationMessage['message']);
                    return $response;
                }
                /*Custom Validations*/
                
                $dateTimeFilter = $values['start_time'] . ',' . $values['end_time'];
                $accountNo = $values['account_id'];
                
                $filterConditions1 = $filterConditions2 = array();
                
                
                $filterConditions1[] = array(
                                        'columnname' => 'vtiger_crmentity:createdtime:createdtime:Payments_Created_Time:DT',
                                        'comparator' => 'bw',
                                        'value' => $dateTimeFilter,
                                        );
                
                if($accountFilter)
                {
                    $filterConditions2[] = array
                                            (
                                                'columnname' => 'vtiger_payments:payment_to:payment_to:Payments_LBL_TO:V',
                                                'comparator' => 'e',
                                                'value' => $accountNo,
                                                'column_condition' => 'OR'
                                            );
                    $filterConditions2[] = array
                                            (
                                                'columnname' => 'vtiger_payments:payment_from:payment_from:Payments_LBL_FROM:V',
                                                'comparator' => 'e',
                                                'value' => $accountNo,
                                                'column_condition' => ''
                                            );
                }
                else if($accountNo == '*')
                {
                    $filterConditions2[] = array
                                            (
                                                'columnname' => 'vtiger_payments:payment_to:payment_to:Payments_LBL_TO:V',
                                                'comparator' => 'e',
                                                'value' => $this->allowedPaymentGateway,
                                                'column_condition' => 'OR'
                                            );
                    $filterConditions2[] = array
                                            (
                                                'columnname' => 'vtiger_payments:payment_from:payment_from:Payments_LBL_FROM:V',
                                                'comparator' => 'e',
                                                'value' => $this->allowedPaymentGateway,
                                                'column_condition' => ''
                                            );
                }
                $searchParams = array(array('columns' => $filterConditions1, 'condition' => 'AND'), array('columns' => $filterConditions2));
                
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$headerFieldModels = $moduleModel->getHeaderViewFieldsList();
		
		$headerFields = array();
		$fields = array();
		$headerFieldColsMap = array();

		$nameFields = $moduleModel->getNameFields();
		if(is_string($nameFields)) {
			$nameFieldModel = $moduleModel->getField($nameFields);
			$headerFields[] = $nameFields;
			$fields = array('name'=>$nameFieldModel->get('name'), 'label'=>$nameFieldModel->get('label'), 'fieldType'=>$nameFieldModel->getFieldDataType());
		} else if(is_array($nameFields)) {
			foreach($nameFields as $nameField) {
				$nameFieldModel = $moduleModel->getField($nameField);
				$headerFields[] = $nameField;
				$fields[] = array('name'=>$nameFieldModel->get('name'), 'label'=>$nameFieldModel->get('label'), 'fieldType'=>$nameFieldModel->getFieldDataType());
			}
		}
		
		foreach($headerFieldModels as $fieldName => $fieldModel) {
			$headerFields[] = $fieldName;
			$fields[] = array('name'=>$fieldName, 'label'=>$fieldModel->get('label'), 'fieldType'=>$fieldModel->getFieldDataType());
			$headerFieldColsMap[$fieldModel->get('column')] = $fieldName;
		}

		$listViewModel = Vtiger_ListView_Model::getInstance($module, $filterId, $headerFields);
		
                
                $listViewModel->set('search_params', $searchParams);
                
                if(!empty($sortOrder)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder',$sortOrder);
		}
                
		$pagingModel = new Vtiger_Paging_Model();
		$pageLimit = $pagingModel->getPageLimit();
                if(!empty($limit))
                {
                    $pageLimit = $limit;
                }
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', $pageLimit+1);
		
		
		$listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		
		if(empty($filterId)) {
			$customView = new CustomView($module);
			$filterId = $customView->getViewId($module);
		}
		
		if($listViewEntries) {
			foreach($listViewEntries as $index => $listViewEntryModel) {
				$data = $listViewEntryModel->getRawData();
				$record = array('id'=>$listViewEntryModel->getId());
				foreach($data as $i => $value) {
					if(is_string($i)) {
						// Transform header-field (column to fieldname) in response.
						if (isset($headerFieldColsMap[$i])) {
							$i = $headerFieldColsMap[$i];
						}	
						$record[$i]= decode_html($value); 
					}
				}
				$records[] = $record;
			}
		}
		
		$moreRecords = false;
		if(count($listViewEntries) > $pageLimit) {
			$moreRecords = true;
			array_pop($records);
		}

		
                $response->setResult(array('records'=>$records, 
                                            'headers'=>$fields, 
                                            'selectedFilter'=>$filterId, 
                                            'nameFields'=>$nameFields,
                                            'moreRecords'=>$moreRecords,
                                            'orderBy'=>$orderBy,
                                            'sortOrder'=>$sortOrder,
                                            'page'=>$page));
		return $response;
	}
	
}
