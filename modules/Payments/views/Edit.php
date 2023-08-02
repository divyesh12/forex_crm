<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
require_once('modules/ServiceProviders/ServiceProviders.php');

class Payments_Edit_View extends Vtiger_Edit_View {

    public function preProcess(Vtiger_Request $request, $display = true) {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);

        if ($recordId) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $recordData = $recordModel->getData();

            if (($recordData['payment_status'] == "Completed") || ($recordData['payment_status'] == "Rejected") || ($recordData['payment_status'] == "Cancelled")) {
                $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED') . ' because of ' . $moduleName . ' Status are ' . $recordData['payment_status'];
                throw new AppException($permission_denied_messege);
            }

//            if ($recordData['payment_operation'] == "Deposit") {
//                if ($recordData['payment_type'] == "P2A") {
//                    if (($recordData['payment_status'] == "Completed" && $recordData['payment_process'] == "Finish") || ($recordData['payment_status'] == "Failed" && $recordData['payment_process'] == "PSP") || ($recordData['payment_status'] == "Rejected" && $recordData['payment_process'] == "PSP")) {
//                        $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED') . ' because of ' . $moduleName . ' Status are ' . $recordData['payment_status'];
//                        throw new AppException($permission_denied_messege);
//                    }
//                } else if ($recordData['payment_type'] == "P2E") {
//                    if (($recordData['payment_status'] == "Completed" && $recordData['payment_process'] == "Finish") || ($recordData['payment_status'] == "Failed" && $recordData['payment_process'] == "PSP") || ($recordData['payment_status'] == "Rejected" && $recordData['payment_process'] == "PSP")) {
//                        $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED') . ' because of ' . $moduleName . ' Status are ' . $recordData['payment_status'];
//                        throw new AppException($permission_denied_messege);
//                    }
//                }
//            } else if ($recordData['payment_operation'] == "Withdrawal") {
//                if ($recordData['payment_type'] == "A2P") {
//                    if (($recordData['payment_status'] == "Completed" && $recordData['payment_process'] == "Finish") || ($recordData['payment_status'] == "Rejected" && $recordData['payment_process'] == "Account")) {
//                        $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED') . ' because of ' . $moduleName . ' Status are ' . $recordData['payment_status'];
//                        throw new AppException($permission_denied_messege);
//                    }
//                } else if ($recordData['payment_type'] == "E2P") {
//                    if (($recordData['payment_status'] == "Completed" && $recordData['payment_process'] == "Finish") || ($recordData['payment_status'] == "Rejected" && $recordData['payment_process'] == "Wallet")) {
//                        $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED') . ' because of ' . $moduleName . ' Status are ' . $recordData['payment_status'];
//                        throw new AppException($permission_denied_messege);
//                    }
//                }
//            } else if ($recordData['payment_operation'] == "InternalTransfer") {
//                if ($recordData['payment_type'] == "E2E") {
//                    if (($recordData['payment_status'] == "Completed" && $recordData['payment_process'] == "Finish") || ($recordData['payment_status'] == "Rejected" && $recordData['payment_process'] == "Wallet Withdrawal")) {
//                        $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED') . ' because of ' . $moduleName . ' Status are ' . $recordData['payment_status'];
//                        throw new AppException($permission_denied_messege);
//                    }
//                } else if ($recordData['payment_type'] == "A2A") {
//                    if (($recordData['payment_status'] == "Completed" && $recordData['payment_process'] == "Finish") || ($recordData['payment_status'] == "Rejected" && $recordData['payment_process'] == "Account Withdrawal")) {
//                        $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED') . ' because of ' . $moduleName . ' Status are ' . $recordData['payment_status'];
//                        throw new AppException($permission_denied_messege);
//                    }
//                }
//            }
        }
        $hidden_html = getHiddenHTML();
        $hidden_field_values = $hidden_html[$moduleName];
        $viewer->assign('INPUT_HIDDEN_DATA', $hidden_field_values);
        parent::preProcess($request, $display);
    }

    /**
     * @add by:- Reena Hingol
     * @Date:- 26-11-2019
     * @comment:-Add restriction for Contacts related Payments module while click on Edit pencil icon */
    public function process(Vtiger_Request $request, $display = true) {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        if ($recordId) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $recordData = $recordModel->getData();
            if (($recordData['payment_status'] == "Completed") || ($recordData['payment_status'] == "Rejected")) {
                $permission_denied_messege = vtranslate('LBL_PERMISSION_DENIED') . ' because of ' . $moduleName . ' Status are ' . $recordData['payment_status'];
                throw new AppException($permission_denied_messege);
            }

            $parameters = $recordData['custom_data'];
            $payment_operation = $recordData['payment_operation'];
            $payment_type = $recordData['payment_type'];
            $json_parameters = Zend_Json::decode(decode_html($parameters));
            if ($payment_operation == 'Deposit' && ($payment_type == 'P2A' || $payment_type == 'P2E')) {
                $payment_provider = $recordData['payment_from'];
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_provider);
                if (!empty($provider)) {
                    $getRequiredParams = $provider->getDepositFormParams();
                }
            } elseif ($payment_operation == 'Withdrawal' && ($payment_type == 'A2P' || $payment_type == 'E2P' )) {
                $payment_provider = $recordData['payment_to'];
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_provider);
                if (!empty($provider)) {
                    $getRequiredParams = $provider->getWithdrawFormParams();
                }
            }

            $html = '<tr>' . vtranslate('LBL_NO_PAYMENT_DETAILS', $moduleName) . '</tr>';
            if (!empty($provider) && !empty($getRequiredParams)) {
                $html = '<tr class="payment_provider_fields">';
                $COUNTER = 0;
                foreach ($json_parameters as $key => $value) {
                    if ($COUNTER == 2) {
                        $html .= '</tr><tr class="payment_provider_fields">';
                        $COUNTER = 1;
                    } else {
                        $COUNTER = $COUNTER + 1;
                    }
                    if ($value['type'] == 'file') {
                        $documentid = end(explode("x", $value['value']));
                        $html .= '<td  class="fieldLabel  ' . $value['name'] . '" id="fieldLabel_' . $value['name'] . '">' . vtranslate($value['label'], $moduleName) . '</td>';
                        if ($documentid) {
                            //$downloadFileData = Payments_Record_Model::getDownloadFileURL($documentid);
                            $html .= '<td class="fieldValue ' . $value['name'] . '" id="fieldValue_' . $value['name'] . '"><span class="value" data-field-type="documentsFileUpload"><a name="viewfile" href="javascript:void(0)" data-filelocationtype="I" data-filename="' . $downloadFileData['document_name'] . '" onclick="Vtiger_Header_Js.previewFile(event,' . $documentid . ')"><i title="View File" class="fa fa-picture-o alignMiddle"></i>&nbsp;' . vtranslate('LBL_VIEW_FILE', $moduleName) . '</a></span></td>';
                        }
                    } else {
                        $html .= '<td  class="fieldLabel  ' . $value['name'] . '" id="fieldLabel_' . $value['name'] . '">' . vtranslate($value['label'], $moduleName) . '</td><td class="fieldValue ' . $value['name'] . '" id="fieldValue_' . $value['name'] . '">' . $value['value'] . '</td>';
                    }
                    if ($COUNTER == 1) {
                        $html .= '<td class="fieldLabel "></td><td class=""></td>';
                    }
                }
                $html .= '</tr>';
            }
            // Getting model to reuse it in parent 
            $viewer = $this->getViewer($request);
            $viewer->assign('PROVIDER_FORM_HTML', $html);
        }
        parent::process($request, $display);
    }

    /**
     * Function to get the list of Script models to be included
     * @param Vtiger_Request $request
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $moduleEditFile = 'modules.' . $moduleName . '.resources.Edit';
        $jsFileNames = array(
            'modules.' . $moduleName . '.resources.validation',
        );
        $jsFileNames[] = $moduleEditFile;
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }

    /* End */
}
