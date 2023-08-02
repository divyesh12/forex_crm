<?php

require_once "include/events/VTEventHandler.inc";

class NotificationsHandler extends VTEventHandler {

    var $labelMissingModule = array('Documents', 'DemoAccount', 'LiveAccount', 'LeverageHistory', 'HelpDesk', 'ModComments', 'Payments');
    var $specialNotificationModuleList = array("Leads", "Contacts", "Documents", "DemoAccount", "LiveAccount", "LeverageHistory", "Payments", "HelpDesk", "ModComments");
    var $cabinetNotificationURLMapping = array(
        "Contacts" => "userpage",
        "Documents" => "documents",
        "DemoAccount" => "demoaccount",
        "LiveAccount" => "liveaccount",
        "LeverageHistory" => "leverage",
        "Payments" => "payments/transactions",
        "HelpDesk" => "tickets/information/17x",
        "ModComments" => "tickets"
        );
    /**
     * @param string $eventName
     * @param Vtiger_Record_Model $entityData
     */
    public function handleEvent($eventName, $entityData) {
        global $adb,$log;
        $moduleName = $entityData->getModuleName();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $currentUserId = $currentUserModel->getId();
        if($_REQUEST['module'] != 'RecycleBin')
        {
            if ($eventName == "vtiger.entity.beforesave" && $moduleName == 'Notifications') {
                $data = $entityData->getData();
                $assignedUserId = $_REQUEST["assigned_user_id"];

                if(isset($data["notification_type"]) && $data["notification_type"] === 'Cabinet')
                {
                    /**Cabinet notification start**/
                    $processedData = $this->processData($entityData, true);
                    $entityData->set("title", $processedData['notification_message']);
                    $entityData->set("link", $processedData['notification_link']);
                    $entityData->set("notification_type", 'Cabinet');
                    $entityData->set("contact_id", $processedData['notify_contact_id']);
                    if(!isset($_REQUEST["isNotificationHandler"]))
                    {
                        $_REQUEST["isCabinetNotificationHandler"] = true;   
                    }
                    else
                    {
                        $_REQUEST["isNotificationHandler"] = true;
                    }

                    /**Cabinet notification end**/
                }
                else if(!isset($_REQUEST["isNotificationHandler"]) || $_REQUEST["isNotificationHandler"] === false || $_REQUEST["isCabinetNotificationHandler"] === true)
                {
                    $processedData = $this->processData($entityData);
                    $entityData->set("assigned_user_id", $assignedUserId);
                    if(isset($processedData['assigned_to']) && !empty($processedData['assigned_to']))
                    {
                        $entityData->set("assigned_user_id", $processedData['assigned_to']);
                    }
                    $entityData->set("contact_id", NULL);
                    $entityData->set("notification_type", 'CRM');
                    $entityData->set("title", $processedData['notification_message']);
                    $entityData->set("link", $processedData['notification_link']);
                }
            }

            if ($eventName == "vtiger.entity.aftersave.final" && $moduleName == 'Notifications') {
                if ($_REQUEST["isNotificationHandler"]) {return NULL;}
                $processedData = $this->processData($entityData);
                $notificationId = $entityData->getId();
                $assignedUserId = $_REQUEST["assigned_user_id"];

                $queryCheckGroup = $adb->pquery("SELECT groupid FROM `vtiger_groups` WHERE groupid = ?", array($assignedUserId));
                if (0 < $adb->num_rows($queryCheckGroup))
                {
                    $userRecordModel = Settings_Groups_Record_Model::getInstance($assignedUserId);
                    $listUser = $userRecordModel->getUsersList();
                    foreach ($listUser as $value)
                    {
                        $_REQUEST["isNotificationHandler"] = true;
                        $userId = $value->get("id");
                        $recordInstance = Vtiger_Record_Model::getCleanInstance("Notifications");
                        $recordInstance->set("mode", "");
                        $recordInstance->set("related_to", $processedData['related_to']);
                        $recordInstance->set("title", $processedData['notification_message']);
                        $recordInstance->set("link", $processedData['notification_link']);
                        $recordInstance->set("assigned_user_id", $userId);
                        $recordInstance->set("source", 'WORKFLOW');
                        $recordInstance->save();
                    }
                }

                if(!$_REQUEST["notAllowNotificationForCommentToAdmin"] || !isset($_REQUEST["notAllowNotificationForCommentToAdmin"]))
                {
                    $this->sendNotificationToAllAdmin($processedData);
                }

                /*Delete record for same user whom is login and record assigny*/
                $recordModel = Vtiger_Record_Model::getInstanceById($notificationId, $moduleName);
                if((0 < $adb->num_rows($queryCheckGroup)) || $_REQUEST["isNotificationDeleteHandler"])
                {
                    $recordModel->delete();
                }
            }

            if ($eventName == "vtiger.entity.aftersave" && in_array($moduleName, $this->specialNotificationModuleList)) {
                require_once 'data/VTEntityDelta.php';

                $reAssignNotificationMessage = $moduleName . ': $customer_name$ - Record is re-assigned to $currentUserId$ by $previousUserId$';

                $entityDelta = new VTEntityDelta();
                $parentRecordId = $entityData->getId();
                $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $moduleName);
                $currentOwnerId = $parentRecordModel->get('assigned_user_id');
                $assignedPrevalue = $entityDelta->getOldValue($moduleName, $parentRecordId, 'assigned_user_id');
                if(!empty($currentOwnerId) && !empty($assignedPrevalue) && ($currentOwnerId != $assignedPrevalue))
                {
                    $_REQUEST["isNotificationHandler"] = false;
                    $currentUserName = ucwords(getUserFullName($currentOwnerId));
                    $whomAssignedUserName = ucwords(getUserFullName($currentUserId));

                    $reAssignNotificationMessage = str_replace('$currentUserId$', $currentUserName, $reAssignNotificationMessage);
                    $reAssignNotificationMessage = str_replace('$previousUserId$', $whomAssignedUserName, $reAssignNotificationMessage);
                    $recordInstance = Vtiger_Record_Model::getCleanInstance("Notifications");
                    $recordInstance->set("mode", "");
                    $recordInstance->set("related_to", $parentRecordId);
                    $recordInstance->set("title", $reAssignNotificationMessage);
                    $recordInstance->set("assigned_user_id", $currentOwnerId);
                    $recordInstance->set("source", 'WORKFLOW');
                    $recordInstance->save();
                }
            }

            if ($eventName == "vtiger.entity.beforedelete" && in_array($moduleName, $this->specialNotificationModuleList)) {
            
            require_once 'data/VTEntityDelta.php';
            
            $deleteRecordNotificationMessage = $moduleName . ': $customer_name$ - Record is deleted by $currentUserId$';
            
            $entityDelta = new VTEntityDelta();
            $parentRecordId = $entityData->getId();
            $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $moduleName);
            $currentOwnerId = $parentRecordModel->get('assigned_user_id');
            
            $currentUserName = getUserFullName($currentUserId);
            $deleteRecordNotificationMessage = str_replace('$currentUserId$', $currentUserName, $deleteRecordNotificationMessage);
            $_REQUEST["isNotificationDeleteHandler"] = true;
            $recordInstance = Vtiger_Record_Model::getCleanInstance("Notifications");
            $recordInstance->set("mode", "");
            $recordInstance->set("related_to", $parentRecordId);
            $recordInstance->set("title", $deleteRecordNotificationMessage);
            $recordInstance->set("assigned_user_id", $currentOwnerId);
            $recordInstance->set("source", 'WORKFLOW');
            $recordInstance->save();
        }
        }
    }
    
    public function processData($entityData, $isCabinet = false) {
        global $adb,$log;
        $processedData = $cabinetRequest = array();
        $relatedTo = $entityData->get("related_to");
        $notificationMessage = $entityData->get("title");
        $parentModuleName = $_REQUEST['module'];

        if(empty($parentModuleName) && !empty($relatedTo))
        {
            $parentModuleName = getModuleNameFromEntityId($relatedTo);
        }
        $notificationLink = $paymentGateway = $accountNo = $fromAccountNo = $toAccountNo = '';
        $action = 'created';
        $source = 'CRM';
        $customModcommentForTicket = $isCustomerPortalRequest = false;
        
        /*cabinet request handle*/
        if(isset($_REQUEST['values']) && !empty($_REQUEST['values']))
        {
            if(is_array($_REQUEST['values']))
            {
                $cabinetRequest = $_REQUEST['values'];
            }
            else
            {
                $cabinetRequest = json_decode($_REQUEST['values'], true);
            }
        }
        
        if((isset($cabinetRequest['request_from']) && $cabinetRequest['request_from'] === 'CustomerPortal') || (isset($_REQUEST['_operation']) && !empty($_REQUEST['_operation'])))
        {
            $isCustomerPortalRequest = true;
        }
        
        if($parentModuleName === 'ModComments' || ($_REQUEST['_operation'] === 'AddComment' && $parentModuleName === 'HelpDesk'))
        {
            $relatedTo = $_REQUEST['related_to'];
            if($isCustomerPortalRequest)
            {
                $relatedTo = $cabinetRequest['related_to'];
                $relatedToArr = explode('x', $relatedTo);
                $relatedTo = $relatedToArr[1];
            }
            $parentModuleName = 'HelpDesk';
            $customModcommentForTicket = true;
            $_REQUEST["notAllowNotificationForCommentToAdmin"] = true;
        }
        
        if(isset($_REQUEST['record']) && !empty($_REQUEST['record']))
        {
            $action = 'updated';
        }
        if(!empty($relatedTo))
        {
            $parentRecordId = $relatedTo;
            $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
            if($isCabinet)
            {
                $notificationLink = $this->cabinetNotificationURLMapping[$parentModuleName];
            }
            else
            {
                $notificationLink = $parentRecordModel->getDetailViewUrl();
            }
            $parentRecordLabel = $parentRecordModel->get('label');
            $label = $parentRecordLabel;
            if(in_array($parentModuleName, $this->labelMissingModule))
            {
                if(isset($_REQUEST['contactid_display']) && !empty($_REQUEST['contactid_display']))
                {
                    $label = $_REQUEST['contactid_display'];
                    $notifyContactId = $_REQUEST['contactid'];
                }
                else if(isset($_REQUEST['contactid']) && !empty($_REQUEST['contactid']))
                {
                    $label = Vtiger_Util_Helper::getRecordName($_REQUEST['contactid']);
                    $notifyContactId = $_REQUEST['contactid'];
                }
                else if(isset($cabinetRequest['leadid']) && !empty($cabinetRequest['leadid']))
                {
                    $label = getContactNameFromEntityId($cabinetRequest['leadid']);
                }
                else if(isset($cabinetRequest['lead_id']) && !empty($cabinetRequest['lead_id']))
                {
                    $label = getContactNameFromEntityId($cabinetRequest['lead_id']);
                }
                else if(isset($_REQUEST['contact_id']) && !empty($_REQUEST['contact_id']))
                {
                    $label = Vtiger_Util_Helper::getRecordName($_REQUEST['contact_id']);
                    $notifyContactId = $_REQUEST['contact_id'];
                }
                else if(isset($cabinetRequest['contactid']) && !empty($cabinetRequest['contactid']))
                {
                    $label = getContactNameFromEntityId($cabinetRequest['contactid']);
                    $recordIds = explode('x', $cabinetRequest['contactid']);
                    $notifyContactId = isset($recordIds[1]) ? $recordIds[1] : $recordIds[0];
                }
                else if(isset($cabinetRequest['contact_id']) && !empty($cabinetRequest['contact_id']))
                {
                    $label = getContactNameFromEntityId($cabinetRequest['contact_id']);
                    $recordIds = explode('x', $cabinetRequest['contact_id']);
                    $notifyContactId = isset($recordIds[1]) ? $recordIds[1] : $recordIds[0];
                }
                else if($isCustomerPortalRequest)
                {
                    if($parentModuleName == 'HelpDesk')
                    {
                        $currentId = $_REQUEST['currentid'];
                        if($customModcommentForTicket)
                        {
                            $currentId = $relatedTo;
                        }
                        else if(isset($cabinetRequest['ticketstatus']) && !empty($cabinetRequest['ticketstatus']))
                        {
                            $recordIds = explode('x', $_REQUEST['recordId']);
                            $currentId = $recordIds[1];
                        }
                        $contactId = getContactIdForTicket($currentId);
                        if(empty($contactId))
                        {
                            $currentId = $relatedTo;
                            $contactId = getContactIdForTicket($currentId);
                        }
                        $label = Vtiger_Util_Helper::getRecordName($contactId);
                        $notifyContactId = $contactId;
                    }
                    else if($parentModuleName == 'LeverageHistory')
                    {
                        $contactId = $parentRecordModel->get('contactid');
                        $label = getContactNameFromEntityId($contactId);
                        $notifyContactId = $contactId;
                    }
                }
            }
            
            if($parentModuleName === 'Documents')
            {
                $action = $_REQUEST['record_status'];
            }
            else if($parentModuleName === 'DemoAccount')
            {
                $accountNo = $parentRecordModel->get('account_no');
            }
            else if($parentModuleName === 'LiveAccount')
            {
                $accountNo = $parentRecordModel->get('account_no');
                $action = $parentRecordModel->get('record_status');
            }
            else if($parentModuleName === 'LeverageHistory')
            {
                $accountNo = $parentRecordModel->get('account_no');
                if((isset($_REQUEST['popupReferenceModule']) && !empty($_REQUEST['popupReferenceModule']) && $_REQUEST['popupReferenceModule'] === 'LiveAccount') || $isCustomerPortalRequest)
                {
                    $liveaccountId = $parentRecordModel->get('liveaccountid');
                    $accountNo = Vtiger_Util_Helper::getRecordName($liveaccountId);
                }
                $action = $parentRecordModel->get('record_status');
            }
            else if($parentModuleName === 'HelpDesk' && $customModcommentForTicket)
            {
                $ticketAssignedUserId = $parentRecordModel->get('assigned_user_id');
                $contactId = $parentRecordModel->get('contact_id');
                $label = Vtiger_Util_Helper::getRecordName($contactId);
                $processedData['assigned_to'] = $ticketAssignedUserId;
                $notifyContactId = $contactId;
            }
            else if($parentModuleName === 'Payments')
            {
                $paymentOperation = $parentRecordModel->get('payment_operation');
                $paymentType = $parentRecordModel->get('payment_type');
                $paymentGateway = $parentRecordModel->get('payment_from');
                $accountNo = $parentRecordModel->get('payment_to');
                if($paymentOperation === 'Withdrawal')
                {
                    $accountNo = $parentRecordModel->get('payment_from');
                    $paymentGateway = $parentRecordModel->get('payment_to');
                }
                else if($paymentOperation === 'InternalTransfer')
                {
                    $fromAccountNo = $parentRecordModel->get('payment_from');
                    $toAccountNo = $parentRecordModel->get('payment_to');
                }
                $action = $parentRecordModel->get('payment_status');
                $contactId = $parentRecordModel->get('contactid');
                $label = getContactNameFromEntityId($contactId);
            }
            
            if($isCustomerPortalRequest)
            {
                if($parentModuleName == 'Contacts')
                {
                    $action = 'updated';
                }
                $source = 'Cabinet';
            }
            
            if($parentModuleName === 'HelpDesk' && $isCabinet)
            {
                $notificationLink = $notificationLink . $parentRecordModel->get('id');
            }

            $label = ucwords($label);
            $notificationMessage = str_replace('$customer_name$', $label, $notificationMessage);
            $notificationMessage = str_replace('$action$', $action, $notificationMessage);
            $notificationMessage = str_replace('$account_number$', $accountNo, $notificationMessage);
            $notificationMessage = str_replace('$payment_gateway$', $paymentGateway, $notificationMessage);
            $notificationMessage = str_replace('$from_account_number$', $fromAccountNo, $notificationMessage);
            $notificationMessage = str_replace('$to_account_number$', $toAccountNo, $notificationMessage);
            $notificationMessage = str_replace('$source$', $source, $notificationMessage);
        }
        
        if($isCustomerPortalRequest && isset($parentRecordModel) && !empty($parentRecordModel))
        {
            $cabinetAssignedUserId = $parentRecordModel->get('assigned_user_id');
            $processedData['assigned_to'] = $cabinetAssignedUserId;
            $processedData['is_cabinet_request'] = true;
        }
        if($_REQUEST["isNotificationDeleteHandler"])
        {//dont save link while record deleted
            $notificationLink = '';
        }
        $processedData['notification_message'] = $notificationMessage;
        $processedData['notification_link'] = $notificationLink;
        $processedData['related_to'] = $relatedTo;
        
        if($isCabinet)
        {
            if($parentModuleName === 'Contacts')
            {
                $notifyContactId = $processedData['related_to'];
            }
            $processedData['notify_contact_id'] = $notifyContactId;
            if($parentModuleName === 'Contacts' && $action === 'created' && $source === 'CRM')
            {
                $processedData['notify_contact_id'] = '0';
            }
        }
        return $processedData;
    }
    
    /**
     * Get all active admin users and send notifications for each admin user
     * @global global $adb
     * @global global $log
     * @param array $processedData
     */
    public function sendNotificationToAllAdmin($processedData) {
        global $adb,$log;
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $currentUserId = $currentUserModel->getId();
        $adminUsersList = Users_Record_Model::getActiveAdminUsers();
        $assignedUserId = $_REQUEST["assigned_user_id"];
        if(isset($processedData['is_cabinet_request']) && $processedData['is_cabinet_request'])
        {
            $assignedUserId = $processedData['assigned_to'];
        }
        foreach($adminUsersList as $adminUserId => $userData)
        {
//            if(($currentUserId == $adminUserId && !isset($processedData['is_cabinet_request'])) || $assignedUserId == $adminUserId)
            if($assignedUserId == $adminUserId)
            {
                continue;
            }
            else if(!isset($processedData['is_cabinet_request']))
            {
                if($currentUserId == $adminUserId && $adminUserId != 1){continue;}
            }
            $_REQUEST["isNotificationHandler"] = true;
            $recordInstance = Vtiger_Record_Model::getCleanInstance("Notifications");
            $recordInstance->set("mode", "");
            $recordInstance->set("related_to", $processedData['related_to']);
            $recordInstance->set("title", $processedData['notification_message']);
            $recordInstance->set("link", $processedData['notification_link']);
            $recordInstance->set("assigned_user_id", $adminUserId);
            $recordInstance->set("source", 'CRM');
            $recordInstance->save();
        }
    }

}

?>