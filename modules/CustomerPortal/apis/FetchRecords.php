<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

require_once('modules/ServiceProviders/ServiceProviders.php');

class CustomerPortal_FetchRecords extends CustomerPortal_API_Abstract {

    protected $translate_module = 'CustomerPortal_Client';

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $portal_language = $this->getActiveCustomer()->portal_language;

        if ($current_user) {
            $customerId = $this->getActiveCustomer()->id;
            $contactWebserviceId = vtws_getWebserviceEntityId('Contacts', $customerId);
            $accountId = $this->getParent($contactWebserviceId);
            $mode = $request->get('mode');
            $module = $request->get('module');
            $moduleLabel = $request->get('moduleLabel');
            $fieldsArray = $request->get('fields');
            $orderBy = $request->get('orderBy');
            $order = $request->get('order');
            $ibContactId = $request->get('ib_contact_id');
            $filter = htmlspecialchars_decode($request->get('filter'));
            $activeFields = CustomerPortal_Utils::getActiveFields($module);
            $subOperation =  $request->get('sub_operation');

            //Custom paramter for listing based on document type
            $document_type = $request->get('document_type'); // If empty then it will give all docs list                        
            //End
            //Custom paramter for count the data or not for export to excel
            $is_count = $request->get('is_count');
            //End
            //Check configuration added by sandeep 20-02-2020
            $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
            CustomerPortal_Utils::checkConfiguration($contactId, $current_user, $module, array(), $portal_language);
            //End
            if (empty($orderBy)) {
                $orderBy = 'modifiedtime';
            } else {
                if (!in_array($orderBy, $activeFields)) {
                    throw new Exception(vtranslate('CAB_MSG_SORT_BY', $this->translate_module, $portal_language) . $orderBy . vtranslate('CAB_MSG_NOT_ALLOWED', $this->translate_module, $portal_language), 1412);
                    exit;
                }
            }

            if (empty($order)) {
                $order = 'DESC';
            } else {
                if (!in_array(strtoupper($order), array("DESC", "ASC"))) {
                    throw new Exception(vtranslate('CAB_MSG_INVALID_SORTING_ORDER', $this->translate_module, $portal_language), 1412);
                    exit;
                }
            }
            $fieldsArray = Zend_Json::decode($fieldsArray);
            $groupConditionsBy = $request->get('groupConditions');
            $page = $request->get('page');
            if (empty($page))
                $page = 0;

            $pageLimit = $request->get('pageLimit');

            if (empty($pageLimit))
                $pageLimit = CustomerPortal_Config::$DEFAULT_PAGE_LIMIT;

            if (empty($groupConditionsBy))
                $groupConditionsBy = 'AND';

            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception(vtranslate('CAB_MSG_MODULE_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                exit;
            }

            if (empty($mode)) {
                $mode = CustomerPortal_Settings_Utils::getDefaultMode($module);
            }
            $count = null;

            if ($fieldsArray !== null) {
                foreach ($fieldsArray as $key => $value) {
                    if (!in_array($key, $activeFields)) {
                        throw new Exception($key . vtranslate('CAB_MSG_IS_NOT_ACCESSIBLE', $this->translate_module, $portal_language), 1412);
                        exit;
                    }
                }
            }
            $fields = implode(',', $activeFields);

            if ($module == 'Faq') {
                if (!empty($fieldsArray)) {
                    $countSql = "SELECT COUNT(*) FROM Faq WHERE faqstatus='Published' AND ";
                    $sql = sprintf('SELECT %s FROM Faq WHERE faqstatus=\'Published\' AND ', $fields);

                    foreach ($fieldsArray as $key => $value) {
                        $countSql .= $key . '=\'' . $value . "' " . $groupConditionsBy . " ";
                        $sql .= $key . '=\'' . $value . "' " . $groupConditionsBy . " ";
                    }
                    $countSql = CustomerPortal_Utils::str_replace_last($groupConditionsBy, ';', $countSql);
                    $sql = CustomerPortal_Utils::str_replace_last($groupConditionsBy, '', $sql);
                } else if (!empty($filter)) {
                    $sql = sprintf('SELECT %s FROM %s WHERE %s AND %s', $fields, $module, "faqstatus='Published'", $filter);
                    $countSql = sprintf('SELECT count(*) FROM %s WHERE %s AND %s;', $module, "faqstatus='Published'", $filter);
                } else {
                    $countSql = "SELECT COUNT(*) FROM Faq WHERE faqstatus='Published';";
                    $sql = sprintf('SELECT %s FROM Faq WHERE faqstatus=\'Published\'', $fields);
                }

                if (isset($is_count) && $is_count == '0') {
                    //if count 0 then it will skip the count query execution
                } else {
                    $countResult = vtws_query($countSql, $current_user);
                    $count = $countResult[0]['count'];
                }


                $sql = sprintf('%s ORDER BY %s %s LIMIT %s,%s ;', $sql, $orderBy, $order, ($page * $pageLimit), $pageLimit);
                $result = vtws_query($sql, $current_user);
            } else if ($module == 'Contacts') {
                $result = vtws_query(sprintf("SELECT %s FROM %s WHERE id='%s';", $fields, $module, $contactWebserviceId), $current_user);
            } else if ($module == 'Accounts') {
                if (!empty($accountId))
                    $result = vtws_query(sprintf("SELECT %s FROM %s WHERE id='%s';", $fields, $module, $accountId), $current_user);
            } else {
                $relatedId = null;
                $defaultMode = CustomerPortal_Settings_Utils::getDefaultMode($module);
                
                /*Leverage hide condition */
                if ($module == 'LiveAccount')
                {
                    $metaTypeFieldName = "live_metatrader_type";
                }
                else if($module == 'DemoAccount')
                {
                    $metaTypeFieldName = "metatrader_type";
                }

                if($subOperation === 'leverage_remove')
                {
                    $providerTitleList = getLeverageHideProviderTitle();
                    if(!empty($providerTitleList))
                    {
                        if (!empty($filter))
                        {
                            foreach($providerTitleList as $key => $providerTitle) {
                                $filter .= " AND $metaTypeFieldName != '$providerTitle'";
                            }
                        }
                        else
                        {
                            foreach($providerTitleList as $key => $providerTitle) {
                                $filter .= "$metaTypeFieldName != '$providerTitle'";
                                if($providerTitle != end($providerTitleList)) {
                                    $filter .= " AND ";
                                }
                            }
                        }
                    }
                }
                
                /*Leverage hide condition */

                if (!empty($fieldsArray)) {
                    $countSql = sprintf('SELECT count(*) FROM %s WHERE ', $module);
                    $sql = sprintf('SELECT %s FROM %s WHERE ', $fields, $module);



                    foreach ($fieldsArray as $key => $value) {
                        $countSql .= $key . '=\'' . $value . "' " . $groupConditionsBy . " ";
                        $sql .= $key . '=\'' . $value . "' " . $groupConditionsBy . " ";
                    }

                    $countSql = CustomerPortal_Utils::str_replace_last($groupConditionsBy, '', $countSql);
                    $sql = CustomerPortal_Utils::str_replace_last($groupConditionsBy, '', $sql);
                } else if (!empty($filter)) {
                    $sql = sprintf('SELECT %s FROM %s WHERE %s ', $fields, $module, $filter);
                    $countSql = sprintf('SELECT count(*) FROM %s WHERE %s ', $module, $filter);
                } else {
                    $countSql = sprintf('SELECT count(*) FROM %s', $module);
                    $sql = sprintf('SELECT %s FROM %s', $fields, $module);
                }


                if ($mode == 'mine') {
                    $relatedId = $contactWebserviceId;
                    if (isset($is_count) && $is_count == '0') {
                        //if count 0 then it will skip the count query execution
                    } else {
                        $countResult = vtws_query_related($countSql, $relatedId, $moduleLabel, $current_user);
                        $count = $countResult[0]['count'];
                    }

                    $limitClause = sprintf('ORDER BY %s %s LIMIT %s,%s', $orderBy, $order, ($page * $pageLimit), $pageLimit);
                    $result = vtws_query_related($sql, $relatedId, $moduleLabel, $current_user, $limitClause);
                } else if ($mode == 'all') {
                    if (in_array($module, array('Products', 'Services'))) {
                        $countSql = sprintf('SELECT count(*) FROM %s;', $module);
                        $sql = sprintf('SELECT %s FROM %s', $fields, $module);
                        $limitClause = sprintf('ORDER BY %s %s LIMIT %s,%s;', $orderBy, $order, ($page * $pageLimit), $pageLimit);
                        $sql = $sql . ' ' . $limitClause;
                        $result = vtws_query($sql, $current_user);
                        if (isset($is_count) && $is_count == '0') {
                            //if count 0 then it will skip the count query execution
                        } else {
                            $countResult = vtws_query($countSql, $current_user);
                            $count = $countResult[0]['count'];
                        }
                    } else {
                        if (!empty($accountId)) {
                            if ($defaultMode == 'all')
                                $relatedId = $accountId;
                            else
                                $relatedId = $contactWebserviceId;
                        }
                        else if (!empty($ibContactId)) {
                            $relatedId = $ibContactId;
                        } else {
                            $relatedId = $contactWebserviceId;
                        }
                        //Custom paramter for listing based on document type
                        if ($module == 'Documents') {
                            $countSql = $countSql . " WHERE document_type='" . $document_type . "'";
                        }
                        //End
                        if (isset($is_count) && $is_count == '0') {
                            //if count 0 then it will skip the count query execution
                        } else {
                            $countResult = vtws_query_related($countSql, $relatedId, $moduleLabel, $current_user);
                            $count = $countResult[0]['count'];
                        }
                        $limitClause = sprintf('ORDER BY %s %s LIMIT %s,%s', $orderBy, $order, ($page * $pageLimit), $pageLimit);

                        //Custom paramter for listing based on document type
                        if ($module == 'Documents') {
                            $sql = $sql . " WHERE document_type='" . $document_type . "'";
                        }
                        //End
                        $result = vtws_query_related($sql, $relatedId, $moduleLabel, $current_user, $limitClause);
                    }
                }
            }

//            foreach ($result as $key => $recordValues) {
//                $result[$key] = CustomerPortal_Utils::resolveRecordValues($recordValues);
//            }
            $response_type = $request->get('response_type');
            //     $resultArr = array();
            $finalResult = array();
            foreach ($result as $key => $recordValues) {
                $obj = CustomerPortal_Utils::resolveRecordValues($recordValues);
                //$result[$key] = CustomerPortal_Utils::resolveRecordValues($recordValues);
                if ($module == 'LeverageHistory') {
                    $idComponents = vtws_getIdComponents($obj['liveaccountid']['value']);
                    $liveaccountid = $idComponents[1];
                    $obj['account_no'] = CustomerPortal_Utils::getLiveAccountDetails($liveaccountid);
                    // $contactIdComponents = vtws_getIdComponents($obj['contactid']['value']);
                    // $conId = $contactIdComponents[1];                    
                    // $metatrader_type = CustomerPortal_Utils::getLiveAccountFullDetails($obj['account_no'], $conId);
                    // $obj['live_metatrader_type'] = $metatrader_type['live_metatrader_type'];
                }
                else if ($module == 'DemoAccount') {
                    $demoAccountProviderType = getProviderType($result[$key]['metatrader_type']);
                    $obj['demo_account_provider_type'] = $demoAccountProviderType;
                }
                else if ($module == 'LiveAccount') {
                    $provider = ServiceProvidersManager::getActiveInstanceByProvider($result[$key]['live_metatrader_type']);
//                    $obj['donwload_link'] = CustomerPortal_Utils::getMetaTraderDonwloadLink($result[$key]['live_metatrader_type']);
                    //Web Meta Trader Link
                    $obj['donwload_link'] = CustomerPortal_Utils::getMetaTraderDonwloadLink($result[$key]['live_metatrader_type'])['meta_trader_windows_link'];

                    //Android Meta Trader Link
                    $obj['donwload_link_android'] = CustomerPortal_Utils::getMetaTraderDonwloadLink($result[$key]['live_metatrader_type'])['meta_trader_android_link'];

                    //iOS Meta Trader Link
                    $obj['donwload_link_ios'] = CustomerPortal_Utils::getMetaTraderDonwloadLink($result[$key]['live_metatrader_type'])['meta_trader_ios_link'];


                    /* Added By Atik Malek : Logic for Remove ( _ ) from acount type in live account list */
                    $liveLabelAccType = $obj['live_label_account_type'];
                    $accTypeVal = '';
                    if (strpos($liveLabelAccType, '_') !== false) {
                        $accTypeVal = explode("_", $liveLabelAccType);
                        $obj['live_label_account_type'] = end($accTypeVal);
                    }
                    /* End */
                    $obj['investor_pass_enable'] = true;
                    if (isset($provider->parameters['investor_pass_enable']) && strtolower($provider->parameters['investor_pass_enable']) == 'no') {
                        $obj['investor_pass_enable'] = false; 
                    }
                    $liveAccountProviderType = getProviderType($result[$key]['live_metatrader_type']);
                    $obj['live_account_provider_type'] = $liveAccountProviderType;
                }
                else if ($module == 'Payments') {

                    if ($obj['payment_operation'] == 'InternalTransfer') {
                        $obj['payment_operation'] = 'Transfer';
                    }

                    if ($obj['payment_operation'] == 'IBCommission') {
                        $obj['payment_operation'] = 'IB Commission';
                        $obj['payment_from'] = 'IB Commission';
                    }
                    $obj['failure_reason'] = vtranslate($obj['failure_reason'], $module, $portal_language);
                    $obj['custom_data'] = CustomerPortal_Utils::getDepositRecipetIDs($obj['custom_data']);
                }
                else if ($module == 'Documents') {
                    if ($obj['document_expriry_date'] === '0000-00-00') {
                        $obj['document_expriry_date'] = '';
                    }
                }

                $finalResult[$key] = $obj;
            }
//            if ($subOperation == 'LeverageHistory') {
//                foreach($finalResult as $key => $value) {
//                    if($value['live_metatrader_type'] == 'Vertex') {
//                        unset($finalResult[$key]);
//                    }
//                }
//                $finalResult = array_values($finalResult);
//            }

            if ($subOperation == 'LeverageHistory') {
                $providerTitleList = getLeverageHideProviderTitle();
                foreach($finalResult as $key => $value) {
                    if(in_array($value['live_metatrader_type'], $providerTitleList)) {
                        unset($finalResult[$key]);
                    }
                }
                $finalResult = array_values($finalResult);
            }

            if (!empty($response_type) && $response_type == 'List') {
                //Note: In future we need to use this cabinet too, as of now using Mobile App
                $response->addToResult('records', $finalResult);
            } else {
                $response->setResult($finalResult);
            }
            $response->addToResult('count', $count);

            return $response;
        }
    }

}
