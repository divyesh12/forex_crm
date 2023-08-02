<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class IBCommissionProfile_DeleteAjax_Action extends Vtiger_Delete_Action {

	public function process(Vtiger_Request $request) {
            global $adb;
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$ibProfileId = $request->get('ib_profile_id');
                $childContactsTobeUpdate = array();
                $adb->startTransaction();
                
                /*Update profile*/
                if(!empty($recordId) && !empty($ibProfileId))
                {
                    /*get contactid of child profile and parent profile match*/
                    $childProfileSql = 'SELECT contactid FROM vtiger_contactdetails'
                            . ' INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid'
                            . ' WHERE (child_ibcommissionprofileid = ? || parent_ibcommissionprofileid = ?) AND vtiger_crmentity.deleted = 0';
                    $childProfileSqlResult = $adb->pquery($childProfileSql, array($recordId, $recordId));
                    
                    $numRowsForChild = $adb->num_rows($childProfileSqlResult);
                    if ($numRowsForChild > 0)
                    {
                        for ($i = 0; $i < $numRowsForChild; $i++)
                        {
                            $childContacts = array();
                            $contactId = $adb->query_result($childProfileSqlResult, $i, 'contactid');
                            $childContacts = fetchChildContactRecordIds($contactId);
                            $childContactsTobeUpdate[] = $contactId;
                            $childContactsTobeUpdate = array_merge($childContactsTobeUpdate,$childContacts);
                        }
                    }
                    
                    $childProfileUpdateSql = 'UPDATE vtiger_contactdetails'
                            . ' INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid'
                            . ' SET child_ibcommissionprofileid = ?'
                            . ' WHERE child_ibcommissionprofileid = ? AND vtiger_crmentity.deleted = 0';
                    $childProfileUpdateResult = $adb->pquery($childProfileUpdateSql, array($ibProfileId, $recordId));

                    $parentProfileUpdatesql = 'UPDATE vtiger_contactdetails'
                            . ' INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid'
                            . ' SET parent_ibcommissionprofileid = ?'
                            . ' WHERE parent_ibcommissionprofileid = ? AND vtiger_crmentity.deleted = 0';
                    $parentProfileUpdateResult = $adb->pquery($parentProfileUpdatesql, array($ibProfileId, $recordId));
                }
                
                $childContactsTobeUpdate = array_unique($childContactsTobeUpdate);
                /*Update hierarchy*/
                foreach($childContactsTobeUpdate as $contactid)
                {
                    $updateResult = updateHierarchy($contactid);
                    if(!$updateResult)
                    {
                        throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
                    }
                }
                
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$recordModel->delete();
		$cvId = $request->get('viewname');
		deleteRecordFromDetailViewNavigationRecords($recordId, $cvId, $moduleName);
                
                $error = $adb->hasFailedTransaction();
                $adb->completeTransaction();
                if($error)
                {
                    throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
		}
                
		$response = new Vtiger_Response();
		$response->setResult(array('viewname'=>$cvId, 'module'=>$moduleName));
		$response->emit();
	}
}
