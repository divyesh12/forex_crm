<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_SaveRecord extends CustomerPortal_FetchRecord {

    protected $recordValues = false;
    protected $mode = 'edit';

    protected function isNewRecordRequest(CustomerPortal_API_Request $request) {
        $recordid = $request->get('recordId');
        return (preg_match("/([0-9]+)x0/", $recordid));
    }

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        global $current_user;
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $module = $request->get('module');

            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }

            if (in_array($module, array('LeverageHistory', 'ContactChannels', 'DemoAccount', 'LiveAccount', 'ModComments', 'Payments', 'HelpDesk', 'Documents', 'Assets', 'Quotes', 'Contacts', 'Accounts', 'Notifications'))) {
                $recordId = $request->get('recordId');
                if (!empty($recordId)) {
                    //Stop edit record if edit is disabled
                    if (!CustomerPortal_Utils::isModuleRecordEditable($module)) {
                        throw new Exception(vtranslate('CAB_MSG_RECORD_CANNOT_BE_EDITED', $this->translate_module), 1412);
                        exit;
                    }
                } else {
                    if (!CustomerPortal_Utils::isModuleRecordCreatable($module)) {
                        throw new Exception(vtranslate('CAB_MSG_MODULE_RECORD_CANNOT_BE_CREATED', $this->translate_module, $portal_language), 1412);
                        exit;
                    }
                }
                $valuesJSONString = $request->get('values', '', false);
                $values = "";

                if (!empty($valuesJSONString) && is_string($valuesJSONString)) {
                    $values = Zend_Json::decode($valuesJSONString);
                } else {
                    $values = $valuesJSONString; // Either empty or already decoded.
                }
                /* added by hitesh to resolve related contact list issue in sop283 */
                $DocUploadByIB = $request->get('doc_upload_by_ib');
                /* added by hitesh to resolve related contact list issue in sop283 */

                //Check configuration added by sandeep 20-02-2020
                $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
                CustomerPortal_Utils::checkConfiguration($contactId, $current_user, $module, $values, $portal_language);
                //End
                //Avoiding fetching fields from customerportal_fields for Accounts and Contacts
                if ($module !== 'Contacts' && $module !== 'Accounts') {
                    //get active fieids with read , write permissions 
                    $activeFields = CustomerPortal_Utils::getActiveFields($module, true);
                    $editableFields = array();
                    $editableFields[] = 'request_from';

                    foreach ($activeFields as $key => $value) {
                        if ($value == 1)
                            $editableFields[] = $key;
                    }
                    if ($module == 'HelpDesk') {
                        $editableFields[] = 'serviceid';
                        $editableFields[] = 'ticketstatus';
                        $editableFields[] = 'ticketpriorities';
                    }
                    if ($module == 'Quotes') {
                        $editableFields[] = 'quotestage';
                    }

                    if (!empty($values)) {
                        foreach ($values as $key => $value) {
                            if (!in_array($key, $editableFields)) {
                                throw new Exception(vtranslate('CAB_MSG_SPECIFIED_NOT_EDITABLE', $this->translate_module, $portal_language), 1412);
                                exit;
                            }
                        }
                    }
                }

                try {
                    if (vtws_recordExists($recordId)) {
                        // Retrieve or Initalize
                        if (!empty($recordId) && !$this->isNewRecordRequest($request)) {
                            $this->recordValues = vtws_retrieve($recordId, $current_user);
                        } else {
                            $this->recordValues = array();
                            // set assigned user to default assignee

                            if ($module == 'HelpDesk')
                                $this->recordValues['assigned_user_id'] = CustomerPortal_Settings_Utils::getDefaultAssignee();
                            else
                                $this->recordValues['assigned_user_id'] = '19x' . getRecordOwnerId($this->getActiveCustomer()->id)['Users']; //contact's assignee will assign to sub module record
                        }

                        // Set the modified values
                        if (!empty($values)) {
                            foreach ($values as $name => $value) {
                                $this->recordValues[$name] = $value;
                            }
                        }
                        // set contact , Organization for Helpdesk record
                        if ($module == 'HelpDesk') {
                            $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
                            $this->recordValues['contact_id'] = $contactId;
                            $this->recordValues['from_portal'] = true;
                            $accountId = $this->getParent($contactId);
                            if (!empty($accountId))
                                $this->recordValues['parent_id'] = $accountId;
                        }

                        if ($module == 'Documents' && count($_FILES)) {
                            //Added by sandeep for alloed particular file type only 18-02-2020
                            CustomerPortal_Utils::verifyFileInputData($_FILES, $portal_language);
                            //End

                            $file = $_FILES['file'];
                            //$this->recordValues['notes_title'] = $request->get('filename');
                            $this->recordValues['notes_title'] = $values['notes_title'];
                            $this->recordValues['filelocationtype'] = 'I'; // location type is internal
                            $this->recordValues['filestatus'] = '1'; //status always active
                            $this->recordValues['filename'] = $file['name'];
                            $this->recordValues['filetype'] = $file['type'];
                            $this->recordValues['filesize'] = $file['size'];
                        }

                        // Setting missing mandatory fields for record.
                        $describe = vtws_describe($module, $current_user);
                        $mandatoryFields = CustomerPortal_Utils:: getMandatoryFields($describe);
                        foreach ($mandatoryFields as $fieldName => $type) {
                            if (!isset($this->recordValues[$fieldName])) {
                                if ($type['name'] == 'reference') {
                                    $crmId = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $module);
                                    $wsId = vtws_getWebserviceEntityId($type['refersTo'][0], $crmId);
                                    $this->recordValues[$fieldName] = $wsId;
                                } else {
                                    $this->recordValues[$fieldName] = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $module);
                                }
                            }
                        }
                        // Update or Create
                        if (isset($this->recordValues['id'])) {
                            if ($module == 'Contacts' || $module == 'Accounts') {
                                //Set IP address of client who will accept the term and condition from cabinet or mobile app side                                                                 
                                if($values['is_agree'] == '1'){
                                    if ($this->recordValues['agree_ip'] == '') {
                                        $this->recordValues['agree_ip'] = CustomerPortal_Utils::getClientIp();
                                    }    
                                }
                                //End
                                //check validation before save for contacts added by Sandeep Thakkar 12-05-2021
                                CustomerPortal_Utils::verifySaveRecordInputData($this->recordValues, $module, $portal_language);
                                //End							
                                $updatedStatus = vtws_update($this->recordValues, $current_user);
                                if ($updatedStatus['id'] == $recordId) {
                                    $response = new CustomerPortal_API_Response();

                                    //It is for setting the IB status message. Added by Sandeep Thakkar 10-06-2010
                                    if ($module == 'Contacts' && $this->recordValues['record_status'] == 'Pending') {
                                        $updatedStatus['ib_message'] = vtranslate('CAB_MSG_YOUR_IB_REQUEST_HAS_BEEN_PROCESSED', $module, $portal_language);
                                    }
                                    //End
                                    $response->setResult($updatedStatus);
                                } else {
                                    $response->setError("CAB_MSG_RECORD_DOES_NOT_EXIST", vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', $this->translate_module, $portal_language));
                                }
                                return $response;
                            }
                            foreach ($mandatoryFields as $fieldName => $type) {
                                if (!isset($this->recordValues[$fieldName]) || empty($this->recordValues[$fieldName])) {
                                    if ($type['name'] !== 'reference') {
                                        $this->recordValues[$fieldName] = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $module);
                                    }
                                }
                            }
                            $this->recordValues = vtws_update($this->recordValues, $current_user);
                        } else {
                            $this->mode = 'create';
                            //Setting source to customer portal
                            $this->recordValues['source'] = $current_user->column_fields['source']; //'CUSTOMER PORTAL';
                            $this->recordValues = vtws_create($module, $this->recordValues, $current_user);
                        }
                        if ($module == 'LeverageHistory') {
                            $request->set('action', $this->recordValues['record_status']);
                        }

                        // Update the record id
                        $request->set('recordId', $this->recordValues['id']);
                        $idComponents = explode('x', $this->recordValues['id']);
                        $recordId = $idComponents[1];

                        //Adding relation to Service Contracts

                        if ($module == 'HelpDesk' && !empty($values['serviceid'])) {
                            $contact = new Contacts();
                            $serviceId = $values['serviceid'];
                            $ids = explode('x', $serviceId);
                            $crmId = explode('x', $this->recordValues['id']);
                            $contact->save_related_module('HelpDesk', $crmId[1], 'ServiceContracts', array($ids[1]));
                        }

                        if ($module == 'Documents') {
                            $contact = new Contacts();
                            $activeCustomer = $this->getActiveCustomer()->id;
                            if ($DocUploadByIB && isset($values['contactid']) && !empty($values['contactid'])) {
                                list($wsContactId, $subibContactId) = explode('x', $values['contactid']);
                                $activeCustomer = $subibContactId;
                            }

                            $contact->save_related_module('Contacts', $activeCustomer, 'Documents', array($recordId));

                            //relate Document with a Ticket OR Project
                            $parentId = $request->get('parentId');

                            if (!empty($parentId) && $this->isRecordAccessible($parentId)) {
                                $focus = CRMEntity::getInstance('Documents');
                                $parentIdComponents = explode('x', $parentId);
                                $focus->insertintonotesrel($parentIdComponents[1], $recordId);
                            }
                        }

                        if (count($_FILES)) {
                            $_FILES = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);


                            //Added by sandeep for alloed particular file type only 18-02-2020
                            CustomerPortal_Utils::verifyFileInputData($_FILES, $portal_language);
                            //End
                            $attachmentType = $request->get('attachmentType');
                            $focus = CRMEntity::getInstance($module);
                            $focus->uploadAndSaveFile($recordId, $module, $_FILES['file'], $attachmentType);
                        }

                        // Gather response with full details                        
                        $response = parent::process($request);
                    } else {
                        $response->setError("CAB_MSG_RECORD_DOES_NOT_EXIST", vtranslate('CAB_MSG_RECORD_DOES_NOT_EXIST', $this->translate_module, $portal_language));
                    }
                } catch (Exception $e) {
                    $response->setError($e->getCode(), $e->getMessage());
                }
            } else {
                $response->setError(1404, vtranslate('CAB_MSG_SAVE_OPER_NOT_SUPPORTS_THIS_MODULE', $this->translate_module, $portal_language));
            }
            return $response;
        }
    }

}
