<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_Utils {

    protected $translate_module = 'CustomerPortal_Client';

    public static function getImageDetails($recordId, $module) {
        global $adb;
        $sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
                    INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
                        WHERE vtiger_crmentity.setype = ? and vtiger_seattachmentsrel.crmid = ?";

        $result = $adb->pquery($sql, array($module . ' Image', $recordId));

        $imageId = $adb->query_result($result, 0, 'attachmentsid');
        $imagePath = $adb->query_result($result, 0, 'path');
        $imageName = $adb->query_result($result, 0, 'name');
        $imageType = $adb->query_result($result, 0, 'type');
        $imageOriginalName = urlencode(decode_html($imageName));

        if (!empty($imageName)) {
            $imageDetails[] = array(
                'id' => $imageId,
                'orgname' => $imageOriginalName,
                'path' => $imagePath . $imageId,
                'name' => $imageName,
                'type' => $imageType,
            );
        }

        if (!isset($imageDetails)) {
            return;
        } else {
            return self::getEncodedImage($imageDetails[0]);
        }
    }

    public static function getEncodedImage($imageDetails) {
        global $root_directory;
        $image = $root_directory . '/' . $imageDetails['path'] . '_' . $imageDetails['name'];
        $image_data = file_get_contents($image);
        $encoded_image = base64_encode($image_data);
        $encodedImageData = array();
        $encodedImageData['imagetype'] = $imageDetails['type'];
        $encodedImageData['imagedata'] = $encoded_image;
        return $encodedImageData;
    }

    public static function getActiveModules() {
        $activeModules = Vtiger_Cache::get('CustomerPortal', 'activeModules'); // need to flush cache when modules updated at CRM settings

        if (empty($activeModules)) {
            global $adb;
            $sql = "SELECT vtiger_tab.name FROM vtiger_customerportal_tabs INNER JOIN vtiger_tab
                        ON vtiger_customerportal_tabs.tabid= vtiger_tab.tabid AND vtiger_tab.presence= ? WHERE vtiger_customerportal_tabs.visible = ? ";
            $sqlResult = $adb->pquery($sql, array(0, 1));

            for ($i = 0; $i < $adb->num_rows($sqlResult); $i++) {
                $activeModules[] = $adb->query_result($sqlResult, $i, 'name');
            }
            //Checking if module is active at Module Manager
            foreach ($activeModules as $index => $moduleName) {
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
                if (!$moduleModel || !$moduleModel->isActive() || Vtiger_Runtime::isRestricted('modules', $moduleName)) {
                    unset($activeModules[$index]);
                }
            }
            Vtiger_Cache::set('CustomerPortal', 'activeModules', $activeModules);
        }
        return $activeModules;
    }

    public static function isModuleActive($module) {
        $activeModules = self::getActiveModules();
        $defaultAllowedModules = array("ModComments", "History", "Contacts", "Accounts", "Payments", "Ewallet", "HelpDesk", "Document", 'LiveAccount');

        if (in_array($module, $defaultAllowedModules)) {
            return true;
        } else if (in_array($module, $activeModules) && !Vtiger_Runtime::isRestricted('modules', $module)) {
            return true;
        }
        return false;
    }

    public static function resolveRecordValues(&$record, $user = null, $ignoreUnsetFields = false) {
        $userTypeFields = array('assigned_user_id', 'creator', 'userid', 'created_user_id', 'modifiedby', 'folderid');

        if (empty($record)) {
            return $record;
        }

        $module = Vtiger_Util_Helper::detectModulenameFromRecordId($record['id']);
        $fieldnamesToResolve = Vtiger_Util_Helper::detectFieldnamesToResolve($module);
        $activeFields = self::getActiveFields($module);

        if (is_array($activeFields) && $module !== 'ModComments') {
            foreach ($fieldnamesToResolve as $key => $field) {
                if (!in_array($field, $activeFields)) {
                    unset($fieldnamesToResolve[$key]);
                }
            }
        }

        if (!empty($fieldnamesToResolve)) {
            foreach ($fieldnamesToResolve as $resolveFieldname) {

                if (isset($record[$resolveFieldname]) && !empty($record[$resolveFieldname])) {
                    $fieldvalueid = $record[$resolveFieldname];

                    if (in_array($resolveFieldname, $userTypeFields)) {
                        $fieldvalue = decode_html(trim(vtws_getName($fieldvalueid, $user)));
                    } else {
                        $fieldvalue = Vtiger_Util_Helper::fetchRecordLabelForId($fieldvalueid);
                    }
                    $record[$resolveFieldname] = array('value' => $fieldvalueid, 'label' => $fieldvalue);
                }
                else
                {
                    $record[$resolveFieldname] = null;
                }
            }
        }
        return $record;
    }

    public static function getRelatedModuleLabel($relatedModule, $parentModule = "Contacts") {
        $relatedModuleLabel = Vtiger_Cache::get('CustomerPortal', $relatedModule . ':label');

        if (empty($relatedModuleLabel)) {
            global $adb;

            if (in_array($relatedModule, array('ProjectTask', 'ProjectMilestone'))) {
                $parentModule = 'Project';
            }

            $sql = "SELECT vtiger_relatedlists.label FROM vtiger_relatedlists
                        INNER JOIN vtiger_customerportal_tabs ON vtiger_relatedlists.related_tabid =vtiger_customerportal_tabs.tabid
                        INNER JOIN vtiger_tab ON vtiger_customerportal_tabs.tabid =vtiger_tab.tabid WHERE vtiger_tab.name=? AND vtiger_relatedlists.tabid=?";
            $sqlResult = $adb->pquery($sql, array($relatedModule, getTabid($parentModule)));

            if ($adb->num_rows($sqlResult) > 0) {
                $relatedModuleLabel = $adb->query_result($sqlResult, 0, 'label');
                Vtiger_Cache::set('CustomerPortal', $relatedModule . ':label', $relatedModuleLabel);
            }
        }
        return $relatedModuleLabel;
    }

    public static function getActiveFields($module, $withPermissions = false) {
        $activeFields = Vtiger_Cache::get('CustomerPortal', 'activeFields'); // need to flush cache when fields updated at CRM settings

        if (empty($activeFields)) {
            global $adb;
            $sql = "SELECT name, fieldinfo FROM vtiger_customerportal_fields INNER JOIN vtiger_tab ON vtiger_customerportal_fields.tabid=vtiger_tab.tabid";
            $sqlResult = $adb->pquery($sql, array());
            $num_rows = $adb->num_rows($sqlResult);

            for ($i = 0; $i < $num_rows; $i++) {
                $retrievedModule = $adb->query_result($sqlResult, $i, 'name');
                $fieldInfo = $adb->query_result($sqlResult, $i, 'fieldinfo');
                $activeFields[$retrievedModule] = $fieldInfo;
            }
            Vtiger_Cache::set('CustomerPortal', 'activeFields', $activeFields);
        }

        $fieldsJSON = $activeFields[$module];
        $data = Zend_Json::decode(decode_html($fieldsJSON));
        $fields = array();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (self::isViewable($key, $module)) {
                    if ($withPermissions) {
                        $fields[$key] = $value;
                    } else {
                        $fields[] = $key;
                    }
                }
            }
        }
        return $fields;
    }

    public static function str_replace_last($search, $replace, $str) {
        if (($pos = strrpos($str, $search)) !== false) {
            $search_length = strlen($search);
            $str = substr_replace($str, $replace, $pos, $search_length);
        }
        return $str;
    }

    public static function isViewable($fieldName, $module) {
        global $db;
        $db = PearDatabase::getInstance();
        $tabidSql = "SELECT tabid from vtiger_tab WHERE name = ?";
        $tabidResult = $db->pquery($tabidSql, array($module));
        if ($db->num_rows($tabidResult)) {
            $tabId = $db->query_result($tabidResult, 0, 'tabid');
        }
        $presenceSql = "SELECT presence,displaytype FROM vtiger_field WHERE fieldname=? AND tabid = ?";
        $presenceResult = $db->pquery($presenceSql, array($fieldName, $tabId));
        $num_rows = $db->num_rows($presenceResult);
        if ($num_rows) {
            $fieldPresence = $db->query_result($presenceResult, 0, 'presence');
            $displayType = $db->query_result($presenceResult, 0, 'displaytype');
            if ($fieldPresence == 0 || $fieldPresence == 2 && $displayType !== 4) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function isReferenceType($fieldName, $describe) {
        $type = self::getFieldType($fieldName, $describe);

        if ($type === 'reference') {
            return true;
        }
        return false;
    }

    public static function isOwnerType($fieldName, $describe) {
        $type = self::getFieldType($fieldName, $describe);

        if ($type === 'owner') {
            return true;
        }
        return false;
    }

    public static function getFieldType($fieldName, $describe) {
        $fields = $describe['fields'];

        foreach ($fields as $field) {
            if ($fieldName === $field['name']) {
                return $field['type']['name'];
            }
        }
        return null;
    }

    public static function getMandatoryFields($describe) {

        $fields = $describe["fields"];
        $mandatoryFields = array();
        foreach ($fields as $field) {
            if ($field['mandatory'] == 1) {
                $mandatoryFields[$field['name']] = $field['type'];
            }
        }
        return $mandatoryFields;
    }

    public static function isModuleRecordCreatable($module) {
        global $db;
        $db = PearDatabase::getInstance();
        $recordPermissionQuery = "SELECT createrecord from vtiger_customerportal_tabs WHERE tabid=?";
        $createPermissionResult = $db->pquery($recordPermissionQuery, array(getTabid($module)));
        $createPermission = $db->query_result($createPermissionResult, 0, 'createrecord');
        return $createPermission;
    }

    public static function isModuleRecordEditable($module) {
        global $db;
        $db = PearDatabase::getInstance();
        $recordPermissionQuery = "SELECT editrecord from vtiger_customerportal_tabs WHERE tabid=?";
        $editPermissionResult = $db->pquery($recordPermissionQuery, array(getTabid($module)));
        $editPermission = $db->query_result($editPermissionResult, 0, 'editrecord');
        return $editPermission;
    }

    //Function to get all Ids when mode is set to published.

    public static function getAllRecordIds($module, $current_user) {
        $relatedIds = array();
        $sql = sprintf('SELECT id FROM %s;', $module);
        $result = vtws_query($sql, $current_user);
        foreach ($result as $resultArray) {
            $relatedIds[] = $resultArray['id'];
        }
        return $relatedIds;
    }

    public static function getPicklist($picklist_name) {
        $response = new CustomerPortal_API_Response();
        $picklist_name_arr = explode(',', $picklist_name);
        $res = array();
        if (count($picklist_name_arr) > 1) {
            for ($i = 0; count($picklist_name_arr) > $i; $i++) {
                if ($picklist_name_arr[$i] == 'language') {
                    $languageList = Vtiger_Language::getAll();
                    //$languageList = array_keys($languageList);
                    $response->addToResult($picklist_name_arr[$i], $languageList);
                } else {
                    $response->addToResult($picklist_name_arr[$i], getPickListValues($picklist_name_arr[$i], 'H2')); //H2 is for CEO, which has all per picklsit permission
                }
            }
        } else {
            if ($picklist_name == 'language') {
                $languageList = Vtiger_Language::getAll();
                $response->addToResult($picklist_name, $languageList);
            } else {
                $res = getPickListValues($picklist_name, 'H2'); //H2 is for CEO, which has all per picklsit permission
                $response->addToResult($picklist_name, $res);
            }
        }
        return $response;
    }

    public static function getEwalletBalance($customerId, $currency = '') {
        global $adb;
        $currency_query = "";
        if (!empty($currency)) {
            $currency_query = " AND vtiger_ewallet.currency = '" . $currency . "'";
        }

        $sql = "(SELECT (IFNULL(TransactionIN.total_amount, 0) - IFNULL(TransactionOUT.total_amount, 0)) as t_amount,
IFNULL(TransactionOUT.currency, TransactionIN.currency) as currency  FROM (SELECT DISTINCT sum(amount) as total_amount,  currency FROM vtiger_ewallet
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ewallet.ewalletid WHERE vtiger_ewallet.contactid = ? " . $currency_query . " AND vtiger_ewallet.transaction_type = 1 AND
vtiger_crmentity.deleted = 0 GROUP BY vtiger_ewallet.currency) as TransactionIN LEFT JOIN (SELECT DISTINCT sum(amount) as total_amount,  currency  FROM vtiger_ewallet
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ewallet.ewalletid WHERE vtiger_ewallet.contactid = ? " . $currency_query . " AND vtiger_ewallet.transaction_type = 2 AND vtiger_crmentity.deleted = 0 GROUP BY vtiger_ewallet.currency) as TransactionOUT ON TransactionIN.currency=TransactionOUT.currency)
 UNION (SELECT (IFNULL(TransactionIN.total_amount, 0) - IFNULL(TransactionOUT.total_amount, 0)) as t_amount, IFNULL(TransactionOUT.currency, TransactionIN.currency) as currency  FROM (SELECT DISTINCT sum(amount) as total_amount, currency FROM vtiger_ewallet INNER JOIN vtiger_crmentity ON
 vtiger_crmentity.crmid = vtiger_ewallet.ewalletid WHERE vtiger_ewallet.contactid = ? " . $currency_query . " AND vtiger_ewallet.transaction_type = 1
 AND vtiger_crmentity.deleted = 0 GROUP BY vtiger_ewallet.currency) as TransactionIN RIGHT JOIN (SELECT DISTINCT sum(amount) as total_amount,  currency  FROM vtiger_ewallet
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ewallet.ewalletid WHERE vtiger_ewallet.contactid = ? " . $currency_query . " AND vtiger_ewallet.transaction_type = 2 AND vtiger_crmentity.deleted = 0 GROUP BY vtiger_ewallet.currency) as TransactionOUT ON TransactionIN.currency=TransactionOUT.currency)";

        $result = $adb->pquery($sql, array($customerId, $customerId, $customerId, $customerId));
        $num_rows = $adb->num_rows($result);
        $Raw = array();
        $cur_arr = array(); //For comparting with picklist currency of live account
        if ($num_rows >= 0) {
            for ($i = 0; $i < $num_rows; $i++) {
                $total_amount = $adb->query_result($result, $i, 't_amount');
                $currency = $adb->query_result($result, $i, 'currency');
                $Raw[$i] = array('total_amount' => $total_amount, 'currency' => $currency);
                $cur_arr[$i] = $currency;
            }
        }

        //It will compare currencies which added to live account currency picklist.
        //If no transaction any currency it will add amount 0.00 and assign to array
        $r_count = count($Raw);
        $live_acc_cur = self::getPicklist('live_currency_code');
        $live_currency_code = $live_acc_cur->getResult()['live_currency_code'];

        for ($j = 0; $j < count($live_currency_code); $j++) {
            if (!in_array($live_currency_code[$j], $cur_arr)) {
                $Raw[$r_count] = array('total_amount' => '0.00', 'currency' => $live_currency_code[$j]);
                $r_count++;
            }
        }
        return $Raw;
    }

    public static function getLiveAccountsWithWalletId($contactid, $current_user, $providerType = '') {
        global $adb;
        //It will fetch the approved live account with currency
        $sql = "SELECT vtiger_liveaccount.account_no, vtiger_liveaccount.live_label_account_type, vtiger_liveaccount.live_currency_code, vtiger_liveaccount.live_metatrader_type FROM vtiger_liveaccount INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid WHERE vtiger_crmentity.deleted = 0 AND vtiger_liveaccount.contactid = ? AND vtiger_liveaccount.record_status = ?";
        $result = $adb->pquery($sql, array($contactid, 'Approved')); 
        $num_rows = $adb->num_rows($result);
        $liveaccount_data = array();
        $labelAccType = configvar('special_groups');
        $arrLiveAccountTypes = CustomerPortal_Utils::getLiveAccountTypesOfAccountNumber($labelAccType);
        for ($i = 0; $i < $num_rows; $i++) {
            $account_no = $adb->query_result($result, $i, 'account_no');
            $labelAcc = $adb->query_result($result, $i, 'live_label_account_type');
            if ($account_no != 0) {    
                if ($providerType == 'ExternalSystem' && in_array($labelAcc, $arrLiveAccountTypes)) {
                    $liveaccount_data[] = array(
                        'live_metatrader_type' => $adb->query_result($result, $i, 'live_metatrader_type'),
                        'account_no' => $account_no, 'live_currency_code' => $adb->query_result($result, $i, 'live_currency_code'),
                        'live_label_account_type' => $labelAcc
                    );
                } else if ($providerType != 'ExternalSystem' && !in_array($labelAcc, $arrLiveAccountTypes)) {
                    $liveaccount_data[] = array(
                        'live_metatrader_type' => $adb->query_result($result, $i, 'live_metatrader_type'),
                        'account_no' => $account_no, 'live_currency_code' => $adb->query_result($result, $i, 'live_currency_code'),
                        'live_label_account_type' => $labelAcc
                    );
                }
                // $liveaccount_data[$i]['live_metatrader_type'] = $adb->query_result($result, $i, 'live_metatrader_type');
                // $liveaccount_data[$i]['account_no'] = $account_no;
                // $liveaccount_data[$i]['live_currency_code'] = $adb->query_result($result, $i, 'live_currency_code');
            }
        }

        $contactId = vtws_getWebserviceEntityId('Contacts', $contactid);
        $sql = sprintf('SELECT %s FROM %s WHERE id=\'%s\';', 'contact_no', 'Contacts', $contactId);
        $result = vtws_query($sql, $current_user);
        if (!empty($result[0]) && $result[0]['contact_no'] != '') {
            $liveaccount_data[] = array(
                'live_metatrader_type' => "",
                'account_no' => $result[0]['contact_no'], 
                'live_currency_code' => ""
            );
            // $liveaccount_data[$i]['live_metatrader_type'] = "";
            // $liveaccount_data[$i]['account_no'] = $result[0]['contact_no'];
            // $liveaccount_data[$i]['live_currency_code'] = "";
        }
        return $liveaccount_data;
    }

    public static function getLiveAccountTypesOfAccountNumber($labelAccType) {
        global $adb;
        $labelAccType = explode(',', $labelAccType);
        $labelAccType = "'" . implode("','", $labelAccType) . "'";
        $sql = 'SELECT vtiger_accountmapping.`live_account_type`, vtiger_accountmapping.`live_label_account_type` FROM `vtiger_accountmapping` INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_accountmapping.accountmappingid WHERE vtiger_crmentity.deleted = 0 AND vtiger_accountmapping.`live_account_type` IN('.$labelAccType.')';
        $result = $adb->pquery($sql, array());
        $num_rows = $adb->num_rows($result);
        $liveAccTypes = array();
        if ($num_rows > 0) {
            for ($i = 0; $i < $num_rows; $i++) {
                $liveAccTypes[] = $adb->query_result($result, $i, 'live_label_account_type');
            }
        }
        return $liveAccTypes;
    }

    public static function verifyInputData($values, $provider_parameters = array(), $module = '', $portal_language) {
        require_once 'modules/ServiceProviders/ServiceProviders.php';
        global $adb;

        $currency = $values['payment_currency'];
        $values_keys = array('payment_operation', 'request_from', 'payment_type', 'payment_from', 'payment_currency', 'payment_to', 'amount');
        for ($i = 0; count($values_keys) > $i; $i++) {
            if (!array_key_exists($values_keys[$i], $values)) {
                throw new Exception(vtranslate('CAB_MSG_MISSING', $module, $portal_language) . $values_keys[$i] . vtranslate('CAB_MSG_PARAMETER_IN_VALUES', $module, $portal_language), 1416);
                exit;
            }
        }

        //Check Service Provider is active or not for meta trader related operation

        if (in_array($values['payment_type'], array('P2A', 'A2A', 'A2P'))) {
            $liveaccount_details = '';
            if ($values['payment_type'] == 'P2A') {
                $liveaccount_details = CustomerPortal_Utils::getLiveAccountFullDetails($values['payment_to'], $values['contactid']);
            }

            if ($values['payment_type'] == 'A2P') {
                $liveaccount_details = CustomerPortal_Utils::getLiveAccountFullDetails($values['payment_from'], $values['contactid']);
            }

            if ($values['payment_type'] == 'A2A') {
                $liveaccount_details = CustomerPortal_Utils::getLiveAccountFullDetails($values['payment_to'], $values['contactid']);
            }

            if ($values['payment_type'] == 'A2A') {
                $liveaccount_details = CustomerPortal_Utils::getLiveAccountFullDetails($values['payment_from'], $values['contactid']);
            }

            if (!empty($liveaccount_details)) {
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($liveaccount_details['live_metatrader_type']);
                if (empty($provider)) {
                    throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
                }
            } else {
                throw new Exception(vtranslate('CAB_MSG_LIVE_ACCOUNT_DOES_NOT_EXIST', 'LiveAccount', $portal_language), 1416);
            }
        }
        //End
        //Check transfer amount min-max
        if ($values['payment_type'] == 'A2A') {
            if ($values['amount'] < configvar('min_account_transfer') || $values['amount'] > configvar('max_account_transfer')) {
                throw new Exception(vtranslate('CAB_MSG_AMOUNT_SHOULD_BE_BETWEEN', $module, $portal_language) . configvar('min_account_transfer') . vtranslate('CAB_MSG_TO', $module, $portal_language) . configvar('max_account_transfer') . '.', 1413);
                exit;
            }
        }
        if ($values['payment_type'] == 'E2E') {
            if ($values['amount'] < configvar('min_ewallet_transfer') || $values['amount'] > configvar('max_ewallet_transfer')) {
                throw new Exception(vtranslate('CAB_MSG_AMOUNT_SHOULD_BE_BETWEEN', $module, $portal_language) . configvar('min_ewallet_transfer') . vtranslate('CAB_MSG_TO', $module, $portal_language) . configvar('max_ewallet_transfer') . '.', 1413);
                exit;
            }
        }
        //End

        if (isset($values['amount']) && !is_numeric($values['amount'])) {
            throw new Exception(vtranslate('CAB_MSG_INVALID_AMOUNT', $module, $portal_language), 1416);
            exit;
        }

        if (isset($values['payment_from']) && $values['payment_from'] != '' && empty($provider_parameters)) {
            if ($values['payment_type'] == 'A2A') {
                //From account checking
                $sql = "SELECT vtiger_liveaccount.account_no, vtiger_liveaccount.live_metatrader_type
                    FROM vtiger_liveaccount INNER JOIN vtiger_crmentity ON
                    vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid WHERE vtiger_crmentity.deleted = 0
                    AND vtiger_liveaccount.account_no = ? AND vtiger_liveaccount.contactid = ? AND vtiger_liveaccount.record_status = ?";
                $result = $adb->pquery($sql, array($values['payment_from'], $values['contactid'], 'Approved')); //
                $num_rows = $adb->num_rows($result);
                if ($num_rows > 0) {

                    //Check in MT5/MT4 side
                    $metatrader_type = $adb->query_result($result, 0, 'live_metatrader_type');
                    $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
                    if (empty($provider)) {
                        throw new Exception(vtranslate('LBL_ISSUE_AT_TRADING_PALTFORM_SIDE', $module, $portal_language), 1416);
                    }
                    $account_balance_result = $provider->getAccountInfo($values['payment_from']);
                    if ($account_balance_result->Code == 200 && $account_balance_result->Message == 'Ok') {
                        if ($account_balance_result->Data->free_margin < $values['net_amount'] && $values['payment_from'] == $account_balance_result->Data->login) {
                            $message = vtranslate('CAB_MSG_INSUFFICIENT_BAL_FOR_THIS_TRANSACION', $module, $portal_language);
                            throw new Exception($message);
                        }
                    } else {
                        throw new Exception(vtranslate($account_balance_result->Message, $module, $portal_language), 1416);
                    }
                } else {
                    throw new Exception(vtranslate('CAB_MSG_FROM_ACCOUNT_NUMBER_DOES_NOT_EXIST', $module, $portal_language), 1416);
                    exit;
                }
            } else if ($values['payment_type'] == 'E2E') {
                $sql = "SELECT vtiger_contactdetails.contact_no
                    FROM vtiger_contactdetails INNER JOIN vtiger_crmentity ON
                    vtiger_crmentity.crmid = vtiger_contactdetails.contactid WHERE vtiger_crmentity.deleted = 0
                    AND vtiger_contactdetails.contact_no = ?";
                $result = $adb->pquery($sql, array($values['wallet_id'])); //
                $num_rows = $adb->num_rows($result);
                if ($num_rows < 1) {
                    throw new Exception(vtranslate('CAB_MSG_WALLET_DOES_NOT_EXIST', $module, $portal_language), 1416);
                    exit;
                } else {
                    $wallet_bal_arr = CustomerPortal_Utils::getEwalletBalance($values['contactid']);
                    if (empty($wallet_bal_arr)) {
                        throw new Exception($currency . vtranslate('CAB_MSG_WALLET_TRANS_DOES_NOT_EXIST', $module, $portal_language), 1416);
                        exit;
                    } else {
                        $wallet_bal = 0;
                        foreach ($wallet_bal_arr as $key => $value) {
                            if ($value['currency'] == $currency) {
                                $wallet_bal = $value['total_amount'];
                            }
                        }
                        if ((float) $values['net_amount'] > (float) $wallet_bal) {
                            $message = vtranslate('LBL_INSUFFICIENT_ACCOUNT_BALANCE', $module, $portal_language);
                            throw new Exception($message, 1416);
                        }
                    }
                }
            }
        } else {
            //throw new Exception(vtranslate('LBL_ISSUE_AT_TRADING_PALTFORM_SIDE', $module), 1416);
        }

        if (!empty($provider_parameters)) {
            //Validate user input and calling parameters
            if ($values['payment_operation'] == 'Deposit') {
                if (empty(ServiceProvidersManager::getActiveInstanceByProvider($values['payment_from'])->parameters)) {
                    throw new Exception(vtranslate('CAB_MSG_INVALID_PAYMENT_NAME_OR_PAYMENT_PROVIDER_DOES_NOT_EXIST', $module, $portal_language), 1416);
                    exit;
                }
            }

            if ($values['payment_operation'] == 'Withdrawal') {
                if (empty(ServiceProvidersManager::getActiveInstanceByProvider($values['payment_to'])->parameters)) {
                    throw new Exception(vtranslate('CAB_MSG_INVALID_PAYMENT_NAME_OR_PAYMENT_PROVIDER_DOES_NOT_EXIST', $module, $portal_language), 1416);
                    exit;
                }
            }

            //Validate withdrawal and deposit min max values
            $operation_min = $provider_parameters[strtolower($values['payment_operation']) . '_min'];
            $operation_max = $provider_parameters[strtolower($values['payment_operation']) . '_max'];
            if (isset($values['amount']) && $values['amount'] < $operation_min || $values['amount'] > $operation_max) {
                throw new Exception(vtranslate('CAB_MSG_AMOUNT_SHOULD_BE_BETWEEN', $module, $portal_language) . $operation_min . vtranslate('CAB_MSG_TO', $module, $portal_language) . $operation_max . '.', 1416);
                exit;
            }
        } else {
            //throw new Exception(vtranslate('LBL_ISSUE_AT_TRADING_PALTFORM_SIDE', $module), 1416);
        }

        if (isset($values['payment_operation']) && !in_array($values['payment_operation'], array('Deposit', 'Withdrawal', 'InternalTransfer'))) {
            throw new Exception(vtranslate('CAB_MSG_INVALID_PAYMENT_OPERATION', $module, $portal_language), 1416);
            exit;
        }

        if (isset($values['payment_type']) && !in_array($values['payment_type'], array('P2A', 'P2E', 'A2P', 'E2P', 'A2A', 'E2E'))) {
            throw new Exception(vtranslate('CAB_MSG_INVALID_PAYMENT_TYPE', $module, $portal_language), 1416);
            exit;
        }

        if (isset($values['payment_from']) && $values['payment_from'] == 'Wallet' && !empty($provider_parameters) && $values['payment_type'] == 'P2A') {
            $wallet_bal_arr = CustomerPortal_Utils::getEwalletBalance($values['contactid']);
            if (empty($wallet_bal_arr)) {
                throw new Exception($currency . vtranslate('CAB_MSG_CURRENCY_WALLET_DOES_NOT_EXIST', $module, $portal_language), 1416);
                //throw new Exception($currency . " Wallet transaction does not exist", 1416);
                exit;
            } else {
                $wallet_bal = 0;
                foreach ($wallet_bal_arr as $key => $value) {
                    if ($value['currency'] == $currency) {
                        $wallet_bal = $value['total_amount'];
                    }
                }
                if ((float) $values['net_amount'] > (float) $wallet_bal) {
                    $message = vtranslate('CAB_MSG_INSUFFICIENT_BAL_FOR_THIS_TRANSACION', $module, $portal_language);
                    throw new Exception($message, 1416);
                }
            }
        } else {
            // throw new Exception(vtranslate('LBL_ISSUE_AT_TRADING_PALTFORM_SIDE', $module), 1416);
        }

        if (isset($values['payment_to']) && ($values['payment_to'] == '' || $values['payment_to'] == '0' || $values['payment_to'] == null)) {
            throw new Exception(vtranslate('CAB_MSG_INVALID_TO_PAYMENT_VALUE', $module, $portal_language), 1416);
            exit;
        } else if (isset($values['payment_to']) && $values['payment_to'] != '') {
            if (($values['payment_type'] == 'P2A' && $values['payment_from'] != 'Wallet') || $values['payment_type'] == 'A2P' || $values['payment_type'] == 'A2A') {
                //Check in Database side
                $account_no = '';

                if ($values['payment_type'] == 'A2P') {
                    $account_no = $values['payment_from'];
                } else {
                    $account_no = $values['payment_to'];
                }

                $sql = "SELECT vtiger_liveaccount.account_no, vtiger_liveaccount.live_metatrader_type
                    FROM vtiger_liveaccount INNER JOIN vtiger_crmentity ON
                    vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid WHERE vtiger_crmentity.deleted = 0
                    AND vtiger_liveaccount.account_no = ? AND vtiger_liveaccount.contactid = ? AND vtiger_liveaccount.record_status = ?";
                $result = $adb->pquery($sql, array($account_no, $values['contactid'], 'Approved')); //
                $num_rows = $adb->num_rows($result);
                if ($num_rows > 0) {
                    //Check in MT5/MT4 side
                    //                    $metatrader_type = $adb->query_result($result, 0, 'live_metatrader_type');
                    //                    $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
                    //                    $check_account_exist_result = $provider->checkAccountExist($account_no);
                    //                    if ($check_account_exist_result->Code == 200) {
                    //
                    //                    } else {
                    //                        throw new Exception($check_account_exist_result->Message, 1416);
                    //                        exit;
                    //                    }
                } else {
                    throw new Exception(vtranslate('CAB_MSG_TO_ACCOUNT_NUMBER_DOES_NOT_EXIST', $module, $portal_language), 1416);
                    exit;
                }
            } else if ($values['payment_from'] == 'Wallet' || $values['payment_type'] == 'P2E' || $values['payment_type'] == 'E2P' || $values['payment_type'] == 'E2E') {
                $sql = "SELECT vtiger_contactdetails.contact_no
                    FROM vtiger_contactdetails INNER JOIN vtiger_crmentity ON
                    vtiger_crmentity.crmid = vtiger_contactdetails.contactid WHERE vtiger_crmentity.deleted = 0
                    AND vtiger_contactdetails.contact_no = ?";

                if ($values['payment_type'] == 'E2E') {
                    $result = $adb->pquery($sql, array($values['payment_to']));
                } else {
                    $result = $adb->pquery($sql, array($values['wallet_id']));
                }

                $num_rows = $adb->num_rows($result);
                if ($num_rows < 1) {
                    throw new Exception(vtranslate('CAB_MSG_WALLET_DOES_NOT_EXIST', $module, $portal_language), 1416);
                    exit;
                }
                if ($values['payment_type'] == 'E2E' && trim(strtolower($values['payment_to'])) == trim(strtolower($values['wallet_id']))) {
                    throw new Exception(vtranslate('CAB_MSG_FROM_WALLET_AND_TO_WALLET_ID_SHOULD_NOT_BE_SAME', $module, $portal_language), 1416);
                    exit;
                }
            }
        }

        if (isset($values['request_from']) && !in_array($values['request_from'], array('CustomerPortal', 'Mobile', 'MOBILE', 'CUSTOMER PORTAL'))) {
            throw new Exception(vtranslate('CAB_MSG_INVALID_REQUEST_SOURCE', $module, $portal_language), 1416);
            exit;
        }

        if (!empty($provider_parameters)) {
            if (isset($values['payment_currency']) && !in_array($values['payment_currency'], explode(',', $provider_parameters['currencies']))) {
                throw new Exception(vtranslate('CAB_MSG_CURRENCIES_DOES_NOT_MATCH_WITH_PAYMENT_PROVIDER_CONFIGURATION', $module, $portal_language), 1416);
                exit;
            }
        } else {
            //throw new Exception(vtranslate('LBL_ISSUE_AT_TRADING_PALTFORM_SIDE', $module), 1416);
        }
        //End
    }

    //Check the balance of Live acocunt and Wallet
    public static function checkBalance($paymentType, $account_no, $net_amount, $currency, $module, $contactid, $portal_language) {
        require_once 'modules/ServiceProviders/ServiceProviders.php';
        global $adb, $log;
        if ($paymentType == 'A2P') {
            //Check in Database side
            $sql = "SELECT vtiger_liveaccount.account_no, vtiger_liveaccount.live_metatrader_type
                    FROM vtiger_liveaccount INNER JOIN vtiger_crmentity ON
                    vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid WHERE vtiger_crmentity.deleted = 0
                    AND vtiger_liveaccount.account_no = ? AND vtiger_liveaccount.contactid = ? AND vtiger_liveaccount.record_status = ?";
            $result = $adb->pquery($sql, array($account_no, $contactid, 'Approved'));
            $num_rows = $adb->num_rows($result);
            if ($num_rows > 0) {
                //Check in MT5/MT4 side
                $metatrader_type = $adb->query_result($result, 0, 'live_metatrader_type');
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($metatrader_type);
                if (empty($provider)) {
                    throw new Exception(vtranslate('LBL_SERVICE_PROVIDER_SETTING_ISSUE', $module, $portal_language), 1416);
                }
                $account_balance_result = $provider->getAccountInfo($account_no);$log->debug('$account_balance_result=');$log->debug($account_balance_result);
                if ($account_balance_result->Code == 200 && $account_balance_result->Message == 'Ok') {
                    //if ($account_balance_result->Data->free_margin < $amount && $account_no == $account_balance_result->Data->login) {
                    if ($account_balance_result->Data->free_margin < $net_amount && $account_no == $account_balance_result->Data->login) {
                        $message = vtranslate('CAB_MSG_INSUFFICIENT_BAL_FOR_THIS_TRANSACION', $module, $portal_language);
                        throw new Exception($message, 1416);
                    }
                } else {
                    $message = $account_balance_result->Message;
                    throw new Exception(vtranslate($message, $module, $portal_language), 1416);
                }
            } else {
                throw new Exception(vtranslate('CAB_MSG_LIVE_ACCOUNT_DOES_NOT_EXIST', 'LiveAccount', $portal_language), 1416);
                exit;
            }
        } else if ($paymentType == 'E2P') {
            $wallet_bal_arr = CustomerPortal_Utils::getEwalletBalance($contactid);
            if (empty($wallet_bal_arr)) {
                throw new Exception($currency . vtranslate('CAB_MSG_WALLET_TRANS_DOES_NOT_EXIST', $module, $portal_language), 1416);
                exit;
            } else {
                $wallet_bal = 0;
                foreach ($wallet_bal_arr as $key => $value) {
                    if ($value['currency'] == $currency) {
                        $wallet_bal = $value['total_amount'];
                    }
                }
                if ((float) $net_amount > (float) $wallet_bal) {
                    $message = vtranslate('CAB_MSG_INSUFFICIENT_BAL_FOR_THIS_TRANSACION', $module, $portal_language);
                    throw new Exception($message, 1416);
                }
            }
        }
    }

    /**
     * Function to setting the response message
     * @param <String> $module module based message setting
     * @param <String> $action set the message based on action under module
     * @createdBy Sandeep Thakkar
     * @date 18 March 2020
     */
    public static function setMessage($module, $action = '', $operation = '', $portal_language) {
        $message = '';
        if ($module == 'LiveAccount') {
            if (configvar('liveaccount_auto_approved')) {
                $message = vtranslate('CAB_MSG_LIVE_ACCOUNT_HAS_BEEN_CREATED', $module, $portal_language);
            }
            if (!configvar('liveaccount_auto_approved')) {
                $message = vtranslate('CAB_MSG_LIVE_ACCOUNT_HAS_BEEN_SENT', $module, $portal_language);
            }
            if ($action == 'TradingPassword') {
                $message = vtranslate('CAB_MSG_TRADING_PASS_HAS_BEEN_CHANGED', $module, $portal_language);
            }
            if ($action == 'TradingInvestorPassword') {
                $message = vtranslate('CAB_MSG_INVESTOR_PASS_HAS_BEEN_CHANGED', $module, $portal_language);
            }
        } else if ($module == 'LeverageHistory') {
            if ($action == 'Cancelled') {
                $message = vtranslate('CAB_MSG_LEVERAGE_REQ_HAS_BEEN_CANCELLED', $module, $portal_language);
            } else {
                if (configvar('leverage_auto_approved')) {
                    $message = vtranslate('CAB_MSG_LEVERAGE_HAS_BEEN_CHANGED', $module, $portal_language);
                }
                if (!configvar('leverage_auto_approved')) {
                    $message = vtranslate('CAB_MSG_LEVERAGE_REQ_HAS_BEEN_SENT', $module, $portal_language);
                }
            }
        } else if ($module == 'DemoAccount') {
            $message = vtranslate('CAB_MSG_DEMO_ACC_HAS_BEEN_CREATED', $module, $portal_language);
        } else if ($module == 'Documents') {
            $message = vtranslate('CAB_MSG_DOCU_HAS_BEEN_UPLOAD_SUCCESS', $module, $portal_language);
        } else if ($module == 'EWallet') {
            $message = vtranslate('CAB_MSG_IB_COMM_WITHDRAWAL_HAS_BEEN_DONE', $module, $portal_language);
        } else if ($module == 'Payments') {
            if ($action == 'Pending') {
                if ($operation == 'Withdrawal') {
                    $message = vtranslate('CAB_MSG_YOUR_WITH_REQUEST_HAS_BEEN_SENT', $module, $portal_language);
                } else if ($operation == 'Deposit') {
                    $message = vtranslate('CAB_MSG_DEPOSIT_REQUEST_HAS_BEEN_SENT', $module, $portal_language);
                } else if ($operation == 'InternalTransfer') {
                    $message = vtranslate('CAB_MSG_TRANSFER_REQUEST_HAS_BEEN_SENT', $module, $portal_language);
                }
            } else if ($action == 'Completed') {
                if ($operation == 'Withdrawal') {
                    $message = vtranslate('CAB_MSG_WITHDRAW_COMPLETED_SUCCESS', $module, $portal_language);
                } else if ($operation == 'InternalTransfer') {
                    $message = vtranslate('CAB_MSG_AMOUN_TRANSFER_COMPLETED_SUCCESS', $module, $portal_language);
                } else if ($operation == 'Deposit') {
                    $message = vtranslate('CAB_MSG_DEPOSIT_HAS_BEEN_ADDED', $module, $portal_language);
                } else if ($operation == 'IBCommission') {
                    $message = vtranslate('CAB_MSG_IB_COMM_WITHDRAWAL_HAS_BEEN_DONE', $module, $portal_language);
                }
            }
        }
        return $message;
    }

    public static function getEarnedIBCommission($customerId) {
        global $adb;
        //Sum of IB Commission Amount earned with Status of IB trade commission records with Status "Pending".
        $earned_commission = 0;
        $sql = "SELECT sum(commission_amount) as earned_commission FROM `tradescommission` WHERE parent_contactid = $customerId AND commission_withdraw_status = 0";
        $sqlResult = $adb->pquery($sql, array());
        if ($adb->num_rows($sqlResult) > 0) {
            $earned_commission = $adb->query_result($sqlResult, 0, 'earned_commission');
        }
        return $earned_commission;
    }

    public static function getNoOfLots($customerId) {
        global $adb;
        //Sum of volume
        $no_of_lots = 0;
        $sql = "SELECT sum(volume) as no_of_lots FROM `tradescommission` WHERE parent_contactid = $customerId AND commission_withdraw_status = 0";
        $sqlResult = $adb->pquery($sql, array());
        if ($adb->num_rows($sqlResult) > 0) {
            $no_of_lots = $adb->query_result($sqlResult, 0, 'no_of_lots');
        }
        return $no_of_lots;
    }

    public static function checkConfiguration($contactId, $current_user, $module, $values = array(), $portal_language) {
        //deposit_to_tradingaccount_without_kyc
        //ewallet_deposit_without_kyc_verification
        $is_document_verified = vtws_retrieve($contactId, $current_user)['is_document_verified'];
        if (!$is_document_verified && in_array($module, array('LiveAccount', 'DemoAccount', 'Payments', 'Contacts'))) {
//            throw new Exception(configvar('kyc_messeage'), 1416);
            //            exit;
            if ($module == 'Contacts') {
                if ($values['is_set_portal_preference'] == '1') {
                    
                } else {
//                    if (!configvar('is_enabled_ibrequest')) {
                    //                        throw new Exception(vtranslate('CAB_MSG_IB_ACCOUNT_REQUEST_DOES_NOT_ENABLED', $module, $portal_language), 1416);
                    //                        exit;
                    //                    }
                }
            }
            if ($module == 'DemoAccount') {
                if (!configvar('is_enabled_demoaccount')) {
                    throw new Exception(vtranslate('CAB_MSG_DEMO_ACCOUNT_MODULE_DOES_NOT_ENABLED', $module, $portal_language), 1416);
                    exit;
                }
            }
            if ($module == 'LiveAccount') {
                if (!$is_document_verified) {
                    
                } else {
                    if (!configvar('is_enabled_liveaccount')) {
                        throw new Exception(vtranslate('CAB_MSG_LIVE_ACCOUNT_MODULE_DOES_NOT_ENABLED', $module, $portal_language), 1416);
                        exit;
                    }
                }
            }
            if ($module == 'Payments') {
                if (in_array($values['payment_type'], array('E2E', 'E2A', 'C2E', 'A2E', 'P2A', 'A2P'))) {
                    if (in_array($values['payment_type'] == 'E2E')) {
                        if (!configvar('ewallet_to_ewallet')) {
                            throw new Exception(vtranslate('CAB_MSG_WALLET_TO_WALLET_OPERATION_DOES_NOT_ENABLED', $module, $portal_language), 1416);
                            exit;
                        }
                    }
                    if ($values['payment_from'] == 'Wallet' && $values['payment_type'] == 'P2A') {
                        if (!configvar('ewallet_module_enabled')) {
                            throw new Exception(vtranslate('CAB_MSG_WALLET_MODULE_DOES_NOT_ENABLED', $module, $portal_language), 1416);
                            exit;
                        }
                        if (!configvar('ewallet_to_tradingaccount')) {
                            throw new Exception(vtranslate('CAB_MSG_WALLET_TO_ACC_OPERATION_DOES_NOT_ENABLED', $module, $portal_language), 1416);
                            exit;
                        }
                    }
                    if ($values['payment_to'] == 'Wallet' && $values['payment_type'] == 'A2P') {
                        if (!configvar('ewallet_module_enabled')) {
                            throw new Exception(vtranslate('CAB_MSG_WALLET_MODULE_DOES_NOT_ENABLED', $module, $portal_language), 1416);
                            exit;
                        }
                        if (!configvar('tradingaccount_to_ewallet')) {
                            throw new Exception(vtranslate('CAB_MSG_ACC_TO_WALLET_OPERATION_DOES_NOT_ENABLED', $module, $portal_language), 1416);
                            exit;
                        }
                    }
                }

                if (in_array($values['payment_type'], array('P2A', 'P2E'))) {
                    if (!configvar('is_enabled_deposit')) {
                        throw new Exception(vtranslate('CAB_MSG_DEPOSIT_OPER_DOES_NOT_ENABLED', $module, $portal_language), 1416);
                        exit;
                    }
                }
                if (in_array($values['payment_type'], array('A2P', 'E2P'))) {
                    if (!configvar('is_enabled_withdraw')) {
                        throw new Exception(vtranslate('CAB_MSG_WITHDRAW_OPER_DOES_NOT_ENABLED', $module, $portal_language), 1416);
                        exit;
                    }
                }

                if (in_array($values['payment_type'], array('E2E', 'A2A'))) {
                    if (!configvar('is_enabled_internaltrasfer')) {
                        throw new Exception(vtranslate('CAB_MSG_TRANSFER_OPER_DOES_NOT_ENABLED', $module, $portal_language), 1416);
                        exit;
                    }
                }
            }
            if ($module == 'Ewallet') {
//                if (!configvar('ewallet_module_enabled')) {
                //                    throw new Exception(vtranslate('CAB_MSG_WALLET_MODULE_DOES_NOT_ENABLED', 'Payments', $portal_language), 1416);
                //                    exit;
                //                }
            }
        }
    }

    public static function getLiveAccountDetails($liveaccountid) {
        global $adb;
        $account_no = '';
        $sql = "SELECT account_no FROM `vtiger_liveaccount` WHERE liveaccountid = $liveaccountid";
        $sqlResult = $adb->pquery($sql, array());
        if ($adb->num_rows($sqlResult) > 0) {
            $account_no = $adb->query_result($sqlResult, 0, 'account_no');
        }
        return $account_no;
    }

    public static function getLiveAccountFullDetails($account_no, $contactid) {
        global $adb;
        $sql = "SELECT * FROM `vtiger_liveaccount` WHERE account_no = ? AND contactid = ?";
        $sqlResult = $adb->pquery($sql, array($account_no, $contactid));
        $row = array();
        if ($adb->num_rows($sqlResult) > 0) {
            $row['live_metatrader_type'] = $adb->query_result($sqlResult, 0, 'live_metatrader_type');
        }
        return $row;
    }

    //Using for commissoin value, commission amount
    public static function setNumberFormat($number, $decimal_point = 4) {
        return number_format($number, $decimal_point);
    }

    public static function setNumberFormatWithoutCommaSeparater($number, $decimal_point = 4) {
        return number_format($number, $decimal_point, '.', '');
    }

    /*
     * Check attachment validation
     *
     */

    public static function verifyFileInputData($FILES, $portal_language) {
        $file_type = array('JPEG', 'JPG', 'PNG', 'PDF');
        if (!in_array(strtoupper(pathinfo($FILES['file']['name'])['extension']), $file_type)) {
            throw new Exception(pathinfo($FILES['file']['name'])['extension'] . vtranslate('CAB_MSG_FILE_TYPE_DOES_NOT_ALLOWED', $this->translate_module, $portal_language), 1404);
            exit;
        } else if ($FILES['file']['size'] <= 0 || $FILES['file']['size'] > 5000000) {
            throw new Exception(vtranslate('CAB_MSG_FILE_SIZE_SHOULD_NOT_BE_GREATER_THAN_MB', $this->translate_module, $portal_language), 1404);
            exit;
        }
    }

    public static function setPaymentGatewayLogo($payment_name) {
        global $site_URL;
        return $site_URL . 'layouts/v7/modules/Settings/ServiceProviders/payments_logo/' . $payment_name . '.png';
    }

    public static function setPaymentGatewayLogoPath($payment_logo_path) {
        global $site_URL;
        return $site_URL . $payment_logo_path;
    }

    public static function getMetaTraderDonwloadLink($live_metatrader_type) {
        $provider = ServiceProvidersManager::getActiveInstanceByProvider($live_metatrader_type);
        if ($provider) {
            if (!empty($provider->parameters)) {
                //return $provider->parameters['meta_trader_windows_link'];
                return $provider->parameters;
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public static function getDepositRecipetIDs($custom_data) {
        $deposit_data = array();
        if (!empty($custom_data)) {
            $custom_data = json_decode($custom_data);
            if (is_array($custom_data)) {
                foreach ($custom_data as $key => $data) {
                    if ($data->name == 'bank_amount') {
                        $data->value = (string)$data->value;
                    }
                    $deposit_data[$key] = $data;
                }
                // $deposit_data = $custom_data;
            }
        }
        return $deposit_data;
    }

    public static function getCurrecnySupportConvertor($operation_type) {
        global $adb;
        $currencyConvertor = array();
        $sql = "SELECT vc.* FROM vtiger_currencyconverter AS vc INNER JOIN vtiger_crmentity AS vce ON vce.crmid = vc.currencyconverterid WHERE vce.deleted=0 AND `vc`.`operation_type` = ?";
        $sqlResult = $adb->pquery($sql, array($operation_type));
        for ($i = 0; $i < $adb->num_rows($sqlResult); $i++) {
            $currencyConvertor[$adb->query_result($sqlResult, $i, 'from_currency') . $adb->query_result($sqlResult, $i, 'to_currency')] = $adb->query_result($sqlResult, $i, 'conversion_rate');
        }
        return $currencyConvertor;
    }

    //Fetch the field label based on tabid and column name
    public static function getFieldLabel($module, $columnname) {
        global $adb;
        $sql = "SELECT `fieldlabel` FROM `vtiger_field` WHERE `tabid` = ? AND `columnname` = ? AND `presence` = 2";
        $tabId = getTabid($module);
        $sqlResult = $adb->pquery($sql, array($tabId, $columnname));
        if ($adb->num_rows($sqlResult) > 0) {
            return $adb->query_result($sqlResult, 0, 'fieldlabel');
        } else {
            return $columnname;
        }
    }

    //Fetch list of service provider based on provider type and parameter
    public static function getListOfServiceProviders($provider_type = '', $parameter = '') {
        $provider_data = array();
        $provider = ServiceProvidersManager::getActiveProviderInstance();
        $j = 0;
        for ($i = 0; $i < count($provider); $i++) {
            if ($provider_type != '' && $parameter != '') {
                if ($provider[$i]::PROVIDER_TYPE == $provider_type) {
                    $provider_data[$j] = $provider[$i]->parameters[$parameter];
                    $j++;
                }
            } else if ($provider_type != '') {
                if ($provider[$i]::PROVIDER_TYPE == $provider_type) {
                    $provider_data[$j] = $provider[$i]->parameters;
                    $j++;
                }
            } else {
                $provider_data[$j] = $provider[$i]->parameters;
                $j++;
            }
        }
        return $provider_data;
    }

    public static function getLiveAccounts($contactid) {
        global $adb;
        //It will fetch the approved live account with currency
        $sql = "SELECT vtiger_liveaccount.liveaccountid, vtiger_liveaccount.account_no, vtiger_liveaccount.live_currency_code, vtiger_liveaccount.live_metatrader_type
                    FROM vtiger_liveaccount INNER JOIN vtiger_crmentity ON
                    vtiger_crmentity.crmid = vtiger_liveaccount.liveaccountid WHERE vtiger_crmentity.deleted = 0
                    AND vtiger_liveaccount.contactid = ? AND vtiger_liveaccount.record_status = ?";
        $result = $adb->pquery($sql, array($contactid, 'Approved')); //
        $num_rows = $adb->num_rows($result);
        $liveaccount_data = array();
        for ($i = 0; $i < $num_rows; $i++) {
            $account_no = (integer) $adb->query_result($result, $i, 'account_no');
            if ($account_no != 0) {
                $liveaccount_data[] = array('liveaccountid' => $adb->query_result($result, $i, 'liveaccountid'), 'live_metatrader_type' => $adb->query_result($result, $i, 'live_metatrader_type'),
                    'account_no' => $account_no, 'live_currency_code' => $adb->query_result($result, $i, 'live_currency_code'));
            }
        }
        return $liveaccount_data;
    }

    public static function getSumOfEwalletInAndOut($customerId, $currency) {
        global $adb;

        $sql = "SELECT vtiger_ewallet.transaction_type,vtiger_ewallet.amount as total_amount FROM `vtiger_ewallet` INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ewallet.ewalletid WHERE vtiger_ewallet.contactid = " . $customerId . "  AND vtiger_crmentity.deleted = 0 AND vtiger_ewallet.currency = '" . $currency . "' AND vtiger_ewallet.transaction_type IN(1,2)";

        $result = $adb->pquery($sql, array());
        $num_rows = $adb->num_rows($result);
        if ($num_rows >= 0) {
            for ($i = 0; $i < $num_rows; $i++) {
                $total_amount = $adb->query_result($result, $i, 'total_amount');
                $transaction_type = $adb->query_result($result, $i, 'transaction_type');
                if ($transaction_type == 1) {
                    $Raw['depositSum'][$i] = $total_amount;
                }
                if ($transaction_type == 2) {
                    $Raw['withdrawalSum'][$i] = $total_amount;
                }
            }
        }
        $depositSum = array_sum($Raw['depositSum']);
        $withdrawalSum = array_sum($Raw['withdrawalSum']);
        return array('depositSum' => $depositSum, 'withdrawalSum' => $withdrawalSum);
    }

    public static function isCurrencyConversionSupport($currency_conversion) {
        $is_supports_currency_convertor = false;
        if ($currency_conversion == 'Yes') {
            $is_supports_currency_convertor = true;
        }
        return $is_supports_currency_convertor;
    }

    public static function verifySaveRecordInputData($values, $module, $portal_language) {
        if ($module == 'Contacts') {
            $mobile_length = 20;
            if (strlen($values['mobile']) > $mobile_length) {
                throw new Exception(vtranslate('CAB_MSG_MOBILE_NO_MAXIMUM_LENGTH', $module, $portal_language) . ' ' . $mobile_length . '.', 1416);
                exit;
            }
        }
    }

    /* Get Compnany details */

    public static function getCompanyDetails() {
        global $adb;
        $sql = "SELECT `sidebar_color` FROM `vtiger_organizationdetails`";
        $sqlResult = $adb->pquery($sql, array());
        if ($adb->num_rows($sqlResult) > 0) {
            return $adb->query_result($sqlResult, 0, 'sidebar_color');
        }
    }

    public static function getClientIp() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    public static function hasEmoji($string) {
        $emojis_regex = '/[\x{0080}-\x{02AF}\x{0300}-\x{03FF}\x{0600}-\x{06FF}\x{0C00}-\x{0C7F}\x{1DC0}-\x{1DFF}\x{1E00}-\x{1EFF}\x{2000}-\x{209F}\x{20D0}-\x{214F}\x{2190}-\x{23FF}\x{2460}-\x{25FF}\x{2600}-\x{27EF}\x{2900}-\x{29FF}\x{2B00}-\x{2BFF}\x{2C60}-\x{2C7F}\x{2E00}-\x{2E7F}\x{3000}-\x{303F}\x{A490}-\x{A4CF}\x{E000}-\x{F8FF}\x{FE00}-\x{FE0F}\x{FE30}-\x{FE4F}\x{1F000}-\x{1F02F}\x{1F0A0}-\x{1F0FF}\x{1F100}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{1F910}-\x{1F96B}\x{1F980}-\x{1F9E0}]/u';
        preg_match($emojis_regex, $string, $matches);
        return !empty($matches);
    }
    
    public static function generateOtp($n = 3)
    {
        global $otpString;
        $generator = $otpString;
        $result = "";

        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand()%(strlen($generator))), 1);
        }
        if(strlen($result) < $n)
        {
            $result = CustomerPortal_Utils::generateOtp($n);
        }
        return $result;
    }
    
    public static function saveOtp($otp = '', $contactId = '', $type = 0)
    {
        global $adb;
        $isSaved = false;
        if(!empty($otp) && !empty($contactId))
        {
            $dateTime = date('Y-m-d H:i:s');
            $sql = "INSERT INTO client_otp_mapping VALUES(?,?,?,?,?,?,?,?,?)";
            $sqlResult = $adb->pquery($sql, array(NULL,$contactId,$otp,$dateTime,0,$type,0,0,$dateTime));
            if ($sqlResult)
            {
                $isSaved = true;
            }
        }
        return $isSaved;
    }
    
    public static function isOTPSentAlready($contactId = '', $type = 0)
    {
        global $adb,$maxResendOtpAttempt,$blockTimeForResendOtpAttempt,$blockTimeForWithdResendOtpAttempt,$maxWithdResendOtpAttempt;
        $maximumResendAttempt = $maxResendOtpAttempt;
        $blockTime = $blockTimeForResendOtpAttempt;
        if($type == 1)
        {
            $maximumResendAttempt = $maxWithdResendOtpAttempt;
            $blockTime = $blockTimeForWithdResendOtpAttempt;
        }
        $isOtpSent = false;
        
        if(!empty($contactId))
        {
            $toTime = date('Y-m-d H:i:s');
            $fromTime = date('Y-m-d H:i:s', strtotime('-'.$blockTime));
            $sql = "SELECT id, otp, resend_attempt FROM client_otp_mapping "
                    . " WHERE contact_id = ? AND status = 0 AND type = ? AND createdtime BETWEEN ? AND ?";
            $sqlResult = $adb->pquery($sql, array($contactId, $type, $fromTime, $toTime));
            if ($adb->num_rows($sqlResult) > 0)
            {
                $attempt = $adb->query_result($sqlResult, 0, 'resend_attempt');
                $id = $adb->query_result($sqlResult, 0, 'id');
                if((int)$attempt >= $maximumResendAttempt)
                {
                    throw new Exception(vtranslate('CAB_ERROR_SEND_OTP_EXCEED', 'CustomerPortal_Client'), 1000);
                }
                else
                {
                    $resendAttemptUpdateSql = "UPDATE client_otp_mapping SET resend_attempt = resend_attempt+1, updatedtime = ? WHERE id = ?";
                    $resendAttemptUpdateSqlResult = $adb->pquery($resendAttemptUpdateSql, array($toTime, $id));
                    $otp = $adb->query_result($sqlResult, 0, 'otp');
                    return $otp;
                }
            }
        }
        return $isOtpSent;
    }
    
    
    public static function isOTPValid($contactId = '', $otp = '', $type = 0)
    {
        global $adb,$maxVerifyOtpAttempt,$blockTimeForVerifyOtpAttempt,$maxWithdVerifyOtpAttempt,$blockTimeForWithdVerifyOtpAttempt;
        $maximumVerifyAttempt = $maxVerifyOtpAttempt;
        $blockTime = $blockTimeForVerifyOtpAttempt;
        if($type == 1)
        {
            $maximumVerifyAttempt = $maxWithdVerifyOtpAttempt;
            $blockTime = $blockTimeForWithdVerifyOtpAttempt;
        }
        $isOtpValid = false;
        
        if(!empty($contactId) && !empty($otp))
        {
            $toTime = date('Y-m-d H:i:s');
            $fromTime = date('Y-m-d H:i:s', strtotime('-'.$blockTime));
            $sql = "SELECT id, otp, verify_attempt FROM client_otp_mapping "
                    . " WHERE contact_id = ? AND status = 0 AND type = ? AND createdtime BETWEEN ? AND ?";
            $sqlResult = $adb->pquery($sql, array($contactId, $type, $fromTime, $toTime));
            if ($adb->num_rows($sqlResult) > 0)
            {
                $attempt = $adb->query_result($sqlResult,0,'verify_attempt');
                if((int)$attempt >= $maximumVerifyAttempt)
                {
                    throw new Exception(vtranslate('CAB_ERROR_OTP_ATTEMPT_EXCEED', 'CustomerPortal_Client'), 1000);
                }
                $actualOtp = $adb->query_result($sqlResult,0,'otp');
                $id = $adb->query_result($sqlResult,0,'id');
                $attemptUpdateSql = "UPDATE client_otp_mapping SET verify_attempt = verify_attempt+1, updatedtime = ? WHERE id = ?";
                $attemptUpdateSqlResult = $adb->pquery($attemptUpdateSql, array($toTime, $id));
                if($otp == $actualOtp)
                {
                    $isOtpValid = true;
                    $updateSql = "UPDATE client_otp_mapping SET status = 1, updatedtime = ? WHERE id = ?";
                    $updateSqlResult = $adb->pquery($updateSql, array($toTime, $id));
                }
            }
        }
        return $isOtpValid;
    }
    
    public static function generateJwtToken($payload = array())
    {
        global $JWT_TOKEN_SECRET;
        $token = '';
        if(!empty($payload))
        {
            //build the headers
            $headers = ['alg'=>'HS256','typ'=>'JWT'];
            $headersEncoded = self::base64UrlEncode(json_encode($headers));

            //build the payload
            $payloadEncoded = self::base64UrlEncode(json_encode($payload));
            
            //build the signature
            $key = $JWT_TOKEN_SECRET;
            $signature = hash_hmac('sha256',"$headersEncoded.$payloadEncoded",$key,true);
            $signatureEncoded = base64_encode($signature);

            //build and return the token
            $token = "$headersEncoded.$payloadEncoded.$signatureEncoded";
        }
        return $token;
    }
    
    public static function verifyOtp($request, $customerId, $portal_language)
    {
        $module = 'Contacts';
        $otp = $request->get('otp');
        $otpTypeString = $request->get('type');
        try
        {
            if(!empty($otpTypeString))
            {
                $otpType = 0;
                if($otpTypeString == 'withdrawal')
                {
                    $otpType = 1;
                }
                if(isset($customerId) && !empty($customerId))
                {
                    if(empty($otp))
                    {
                        throw new Exception(vtranslate('CAB_ERROR_OTP_CANNOT_BLANK', $module, $portal_language), 1501);
                    }

                    /*Check for valid OTP*/
                    $isValidOtp = CustomerPortal_Utils::isOTPValid($customerId, $otp, $otpType);
                    /*Check for valid OTP*/

                    if($isValidOtp)
                    {
                        return true;
                    }
                    else
                    {
                        throw new Exception(vtranslate('CAB_ERROR_OTP_NOT_VALID', $module, $portal_language), 1501);
                    }
                }
                else
                {
                    throw new Exception(vtranslate('CAB_ERROR_CONTACTID_CANNOT_BE_BLANK', $module, $portal_language), 1501);
                }
            }
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        return false;
    }
    
    public static function sendOtp($request, $customerId, $portal_language)
    {
        $module = 'Contacts';   
        $otpTypeString = $request->get('type');
        try
        {
            if(!empty($otpTypeString))
            {
                $otpType = 0;
                if($otpTypeString == 'withdrawal')
                {
                    $otpType = 1;
                }
                /*Generate and Send OTP on email*/
                if(isset($customerId) && !empty($customerId))
                {
                    /*Check OTP already sent!*/
                    $isOtpAlreadySent = CustomerPortal_Utils::isOTPSentAlready($customerId, $otpType);
                    /*Check OTP already sent!*/

                    if(!$isOtpAlreadySent)
                    {
                        /*Generate OTP*/
                        $otp = CustomerPortal_Utils::generateOtp(4);
                        /*Generate OTP*/

                        /*Save OTP*/
                        if(!empty($otp))
                        {
                            $otpSaveResult = CustomerPortal_Utils::saveOtp($otp, $customerId, $otpType);
                        }
                        /*Save OTP*/
                    }
                    else
                    {
                        $otpSaveResult = true;
                        $otp = $isOtpAlreadySent;
                    }
                    /*Send OTP on email*/
                    if($otpSaveResult)
                    {
                        $recordModel = Vtiger_Record_Model::getInstanceById($customerId, 'Contacts');
                        $contactDetails = $recordModel->getData();

                        global $adb,$HELPDESK_SUPPORT_NAME,$HELPDESK_SUPPORT_EMAIL_ID;
                        $templName = 'Send Login OTP on email';
                        if($otpType == 1)
                        {
                            $templName = 'Send Withdrawal OTP on email';
                        }
                        
                        $templsql = "SELECT subject,body FROM vtiger_emailtemplates WHERE templatename LIKE '%$templName%'";
                        $templates = $adb->pquery($templsql);
                        $subject = $adb->query_result($templates, 0, 'subject');
                        $body = $adb->query_result($templates, 0, 'body');
                        $body = str_replace('$custom_OTP$', $otp, $body);
                        $body = str_replace('$contacts-firstname$', $contactDetails['firstname'], $body);
                        $body = str_replace('$contacts-lastname$', $contactDetails['lastname'], $body);
                        $body= decode_html(getMergedDescription($body, $customerId, 'Contacts'));
                        
                        send_mail('Contacts', $contactDetails['email'], $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $body, '', '', '', '', '', true);
                    }
                    else
                    {
                        throw new Exception(vtranslate('CAB_ERROR_OTP_NOT_SAVED', $module, $portal_language), 1501);
                    }
                    /*Send OTP on email*/
                }
                else
                {
                    throw new Exception(vtranslate('CAB_ERROR_CONTACTID_CANNOT_BE_BLANK', $module, $portal_language), 1501);
                }
            }
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        return true;
    }
    

    public static function getEwalletRecords($contactId, $filter, $limitClause) {
        global $adb;
        $sql = "SELECT vtiger_ewallet.*, vtiger_crmentity.smownerid, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime, vtiger_payments.paymentsid, vtiger_payments.comment, vtiger_payments.payment_from, vtiger_payments.payment_to FROM vtiger_ewallet INNER JOIN vtiger_payments ON vtiger_payments.transaction_id = vtiger_ewallet.transaction_id INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ewallet.ewalletid WHERE vtiger_crmentity.deleted = 0 AND vtiger_ewallet.contactid = ? AND " . $filter . ' ' . $limitClause;

        $result = $adb->pquery($sql, array($contactId));
        $num_rows = $adb->num_rows($result);
        $wallet_data = array();
        for ($i = 0; $i < $num_rows; $i++) {
            $wallet_data[$i]['contactid'] =  '12x'.$adb->query_result($result, $i, 'contactid');
            $wallet_data[$i]['amount'] = $adb->query_result($result, $i, 'amount');
            $wallet_data[$i]['ewallet_no'] = $adb->query_result($result, $i, 'ewallet_no');
            $wallet_data[$i]['currency'] = $adb->query_result($result, $i, 'currency');
            $wallet_data[$i]['transaction_type'] = $adb->query_result($result, $i, 'transaction_type');
            $wallet_data[$i]['transaction_id'] = $adb->query_result($result, $i, 'transaction_id');
            $wallet_data[$i]['createdtime'] = $adb->query_result($result, $i, 'createdtime');
            $wallet_data[$i]['modifiedtime'] = $adb->query_result($result, $i, 'modifiedtime');
            $wallet_data[$i]['assigned_user_id'] = '19x'.$adb->query_result($result, $i, 'smownerid');
            $wallet_data[$i]['id'] = '41x'.$adb->query_result($result, $i, 'ewalletid');
            $wallet_data[$i]['paymentsid'] = $adb->query_result($result, $i, 'paymentsid');
            $wallet_data[$i]['comment'] = $adb->query_result($result, $i, 'comment');
            $wallet_data[$i]['payment_from'] = $adb->query_result($result, $i, 'payment_from');
            $wallet_data[$i]['payment_to'] = $adb->query_result($result, $i, 'payment_to');
        }
        
        foreach ($wallet_data as $key => $recordValues) {
            $obj = self::resolveRecordValues($recordValues);
            $finalResult[$key] = $obj;
        }
        $finalResult = array('data' => $finalResult, 'count' => $num_rows);
        return $finalResult;
    }

    public static function getCurrencyConvertionRate($fromCurrency, $toCurrency, $operation_type = "Deposit")
    {
        global $adb;
        $conversionRate = "";
        if(!empty($fromCurrency) && !empty($toCurrency))
        {
            $sql = "SELECT vc.conversion_rate FROM vtiger_currencyconverter AS vc INNER JOIN vtiger_crmentity AS vce ON vce.crmid = vc.currencyconverterid WHERE vce.deleted=0 AND `vc`.`from_currency` = ? AND `vc`.`to_currency` = ? AND `vc`.`operation_type` = ?";
            $sqlResult = $adb->pquery($sql, array($fromCurrency, $toCurrency, $operation_type));
            $conversionRate = $adb->query_result($sqlResult, 0, 'conversion_rate');
        }
        return $conversionRate;
    }
}
