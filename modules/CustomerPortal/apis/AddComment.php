<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

include_once dirname(__FILE__) . '/SaveRecord.php';

class CustomerPortal_AddComment extends CustomerPortal_SaveRecord {

    protected $translate_module = 'CustomerPortal_Client';

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        global $adb;
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $valuesJSONString = $request->get('values');
            $element = null;

            if (!empty($valuesJSONString) && is_string($valuesJSONString)) {
                $element = Zend_Json::decode($valuesJSONString);
            } else {
                $element = $valuesJSONString; // Either empty or already decoded.
            }

            $element['assigned_user_id'] = vtws_getWebserviceEntityId('Users', $current_user->id);
            $parentId = $request->get('parentId');

            $relatedRecordId = $element['related_to'];
            $relatedModule = VtigerWebserviceObject::fromId($adb, $relatedRecordId)->getEntityName();

            if (!CustomerPortal_Utils::isModuleActive($relatedModule)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }

            if (!empty($parentId)) {
                if (!$this->isRecordAccessible($parentId)) {
                    throw new Exception(vtranslate('CAB_MSG_PARENT_RECORD_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                    exit;
                }
                $relatedRecordIds = $this->relatedRecordIds($relatedModule, CustomerPortal_Utils::getRelatedModuleLabel($relatedModule), $parentId);

                if (!in_array($relatedRecordId, $relatedRecordIds)) {
                    throw new Exception(vtranslate('CAB_MSG_RECORD_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                    exit;
                }
            } else {
                //If module is Faq by pass this check as we Faq's are not related to Contacts module.
                if ($relatedModule == 'Faq') {
                    if (!($this->isFaqPublished($relatedRecordId))) {
                        throw new Exception(vtranslate('CAB_MSG_THIS_FAQ_IS_NOT_PUBLISHED', $relatedModule, $portal_language), 1412);
                        exit;
                    }
                } else if (!$this->isRecordAccessible($relatedRecordId)) {
                    throw new Exception(vtranslate('CAB_MSG_RECORD_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                    exit;
                }
            }
            // Always set the customer to Portal user when comment is added from portal 
            $customerId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
            $element['customer'] = $customerId;
            $element['from_portal'] = true;
            $element['commentcontent'] = nl2br($element['commentcontent']);
            //comment_added_from_portal added to check workflow condition "is added from portal" for comments.
            //Cannot use from_portal as Mailroom also sets to TRUE.
            $element['comment_added_from_portal'] = true;
            $result = vtws_create('ModComments', $element, $current_user);
            if (isset($result['id']) && !empty($result['id'])) {
                /* Added by hitesh: that is used to attach document with comment */
                list($commentTabId, $recordId) = explode('x', $result['id']);
                if (count($_FILES)) {
                    $_FILES = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
                    $module = 'ModComments';
                    //Added by sandeep for alloed particular file type only 18-02-2020
                    CustomerPortal_Utils::verifyFileInputData($_FILES, $portal_language);
                    //End
                    $focus = CRMEntity::getInstance($module);
                    $attachmentId = $focus->uploadAndSaveFile($recordId, $module, $_FILES['file']);

                    /* Update filename field to download attachment cabinet side */
                    $adb->pquery('UPDATE vtiger_modcomments SET filename=? WHERE modcommentsid=?', array($attachmentId, $recordId));
                    $attachments[0]['filename'] = decode_html($_FILES['file']['name']);
                    $attachments[0]['attachmentid'] = $attachmentId;
                    $result['attachments'] = $attachments;
                }
            }
            $result = CustomerPortal_Utils::resolveRecordValues($result, $current_user);
            $response->setResult($result);
            return $response;
        }
    }

}
