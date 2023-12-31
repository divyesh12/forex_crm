<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class CustomerPortal_DownloadFile extends CustomerPortal_API_Abstract {
	protected $translate_module = 'CustomerPortal_Client';
	function process(CustomerPortal_API_Request $request) {
		global $adb, $s3Enable;
		$current_user = $this->getActiveUser();
		$response = new CustomerPortal_API_Response();
                $portal_language = $this->getActiveCustomer()->portal_language;

		if ($current_user) {
			$parentId = $request->get('parentId');
			$recordId = $request->get('recordId');
			$module = $request->get('module');
			$parentModule = $request->get('parentModule');
			if (!CustomerPortal_Utils::isModuleActive($module)) {
				$response->setError(1404, vtranslate('CAB_MSG_MODULE_IS_DISABLED', $this->translate_module, $portal_language));
				return $response;
			}

			if (!empty($parentId)) {
				if ($parentModule === 'Faq') {
					if (!($this->isFaqPublished($parentId))) {
						throw new Exception(vtranslate('CAB_MSG_THIS_FAQ_IS_NOT_PUBLISHED', $parentModule, $portal_language), 1412);
						exit;
					}
				} else {
					if (!$this->isRecordAccessible($parentId)) {
						throw new Exception(vtranslate('CAB_MSG_MODULE_RECORD_CANNOT_BE_CREATED', $this->translate_module, $portal_language), 1412);
						exit;
					}
					$relatedRecordIds = $this->relatedRecordIds($module, CustomerPortal_Utils::getRelatedModuleLabel($module, $parentModule), $parentId);


					if (!in_array($recordId, $relatedRecordIds)) {
						throw new Exception(vtranslate('CAB_MSG_RECORD_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
						exit;
					}
				}
			} else {
				if (!$this->isRecordAccessible($recordId, $module) && $module != 'ModComments') {
					$response->setError(1404, vtranslate('CAB_MSG_RECORD_NOT_ACCESSIBLE', $this->translate_module, $portal_language));
					return $response;
				}
			}
			$idComponents = vtws_getIdComponents($recordId);
			$id = $idComponents[1];
			if ($module == 'Documents') {
				$query = "SELECT filetype FROM vtiger_notes INNER JOIN vtiger_crmentity ON vtiger_notes.notesid= vtiger_crmentity.crmid 
                          WHERE notesid =? AND vtiger_crmentity.deleted=?";
				$res = $adb->pquery($query, array($id, '0'));
				$filetype = $adb->query_result($res, 0, "filetype");
				$this->updateDownloadCount($id);

				$fileidQuery = 'SELECT attachmentsid FROM vtiger_seattachmentsrel WHERE crmid = ?';
				$fileres = $adb->pquery($fileidQuery, array($id));
				$fileid = $adb->query_result($fileres, 0, 'attachmentsid');

				$filepathQuery = 'SELECT path,name,storedname FROM vtiger_attachments WHERE attachmentsid = ?';
				$fileres = $adb->pquery($filepathQuery, array($fileid));
				$filepath = $adb->query_result($fileres, 0, 'path');
				$filename = $adb->query_result($fileres, 0, 'storedname');
				$filename = decode_html($filename);

				$saved_filename = $fileid."_".$filename;
				$filenamewithpath = $filepath.$saved_filename;
				$filesize = filesize($filenamewithpath);
				$fileDetails = array();
				$fileDetails['fileid'] = $fileid;
				$fileDetails['filename'] = $filename;
				$fileDetails['filetype'] = $filetype;
				$fileDetails['filesize'] = $filesize;
                                if($s3Enable)
                                {
                                    $fileDetails['filecontents'] = base64_encode(readBucketFile($filenamewithpath));
                                }
                                else
                                {
                                    $fileDetails['filecontents'] = base64_encode(file_get_contents($filenamewithpath));
                                }
				$response->setResult($fileDetails);
			} else if ($module == 'ModComments') {
				$attachmentId = $request->get('attachmentId');
				$modCommentsRecordModel = Vtiger_Record_Model::getInstanceById($id, $module);
				$rawAttachmentDetails = $modCommentsRecordModel->getFileDetails($attachmentId);
				//construct path for attachment and get file size and type details
				$attachmentDetails = $rawAttachmentDetails[0];
				$fileid = $attachmentDetails['attachmentsid'];
				$filename = $attachmentDetails['storedname'];
				$filepath = $attachmentDetails['path'];
				$saved_filename = $fileid."_".$filename;
				$filenamewithpath = $filepath.$saved_filename;
				$filesize = filesize($filenamewithpath);
				$filetype = $attachmentDetails['type'];

				//Construct array with all attachment details
				$fileDetails = array();
				$fileDetails['fileid'] = $fileid;
				$fileDetails['filename'] = $filename;
				$fileDetails['filetype'] = $filetype;
				$fileDetails['filesize'] = $filesize;
                                if($s3Enable)
                                {
                                    $fileDetails['filecontents'] = base64_encode(readBucketFile($filenamewithpath));
                                }
                                else
                                {
                                    $fileDetails['filecontents'] = base64_encode(file_get_contents($filenamewithpath));
                                }
				$response->setResult($fileDetails);
			} else {
				throw new Exception(vtranslate('CAB_MSG_DOWNLOAD_NOT_SUPPORTED', $module, $portal_language), 1412);				
				exit;
			}
			return $response;
		}
	}

	/**
	 * Function to update the download count of a file
	 */
	function updateDownloadCount($id) {
		global $adb, $log;
		$log->debug("Entering customer portal function updateDownloadCount");
		$updateDownloadCount = "UPDATE vtiger_notes SET filedownloadcount = filedownloadcount+1 WHERE notesid = ?";
		$countres = $adb->pquery($updateDownloadCount, array($id));
		$log->debug("Entering customer portal function updateDownloadCount");
		return true;
	}

}
