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

class Payments_Detail_View extends Vtiger_Detail_View {

    /**
     * Function to get Ajax is enabled or not
     * @param Vtiger_Record_Model record model
     * @return <boolean> true/false
     */
    public function isAjaxEnabled($recordModel) {
        return false;
    }

    /** @creator: Divyesh Chothani
     * @comment: return provider field html 
     * @date: 17-10-2019
     * */
    public function showModuleDetailView(Vtiger_Request $request) {
        global $root_directory;
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $modelData = $recordModel->getData();
        $parameters = $modelData['custom_data'];
        $payment_operation = $modelData['payment_operation'];
        $payment_type = $modelData['payment_type'];
        $json_parameters = Zend_Json::decode(decode_html($parameters));

//        $downloadFileURL = Payments_Record_Model::getDownloadFileURL(10393);
//        echo "<pre>";
//        print_r($json_parameters);
//        exit;

        if ($payment_operation == 'Deposit' && ($payment_type == 'P2A' || $payment_type == 'P2E')) {
            $payment_provider = $modelData['payment_from'];
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_provider);
            if (!empty($provider)) {
                $getRequiredParams = $provider->getDepositFormParams();
            }
        } elseif ($payment_operation == 'Withdrawal' && ($payment_type == 'A2P' || $payment_type == 'E2P' )) {
            $payment_provider = $modelData['payment_to'];
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($payment_provider);
            if (!empty($provider)) {
                $getRequiredParams = $provider->getWithdrawFormParams();
            }
        }

        $html = '<div class="blockData"><table class="table detailview-table no-border"><tbody><tr>' . vtranslate('LBL_NO_PAYMENT_DETAILS', $moduleName) . '</tr></tbody></table></div>';
        if (!empty($provider) && !empty($getRequiredParams)) {
            $html = '<div class="blockData"><table class="table detailview-table no-border"><tbody><tr>';
            $COUNTER = 0;
            foreach ($json_parameters as $key => $value) {
                if ($COUNTER == 2) {
                    $html .= '</tr><tr>';
                    $COUNTER = 1;
                } else {
                    $COUNTER = $COUNTER + 1;
                }
                if ($value['type'] == 'file') {
                    $documentid = end(explode("x", $value['value']));
                    $html .= '<td  class="fieldLabel textOverflowEllipsis" id="Payments_detailView_fieldLabel_' . $value['name'] . '"><span class="muted">' . vtranslate($value['label'], $moduleName) . '</span></td>';

                    if ($documentid) {
                        $html .= '<td class="fieldValue " id="Payments_detailView_fieldValue_' . $value['name'] . '"><span class="value" data-field-type="documentsFileUpload"><a name="viewfile" href="javascript:void(0)" data-filelocationtype="I" data-filename="' . $downloadFileData['document_name'] . '" onclick="Vtiger_Header_Js.previewFile(event,' . $documentid . ')"><i title="View File" class="fa fa-picture-o alignMiddle"></i>&nbsp;' . vtranslate('LBL_VIEW_FILE', $moduleName) . '</a></span></td>';
                    }
                } else {
                    $html .= '<td  class="fieldLabel textOverflowEllipsis" id="Payments_detailView_fieldLabel_' . $value['name'] . '"><span class="muted">' . vtranslate($value['label'], $moduleName) . '</span></td>
                  <td class="fieldValue " id="Payments_detailView_fieldValue_' . $value['name'] . '"><span class="value" data-field-type="string">' . $value['value'] . '</span></td>';
                }

                if ($COUNTER == 1) {
                    $html .= '<td class="fieldLabel "></td><td class=""></td>';
                }
            }
            $html .= '</tr></tbody></table></div>';
        }
        // Getting model to reuse it in parent 
        if (!$this->record) {
            $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
        }
        $recordModel = $this->record->getRecord();
        $viewer = $this->getViewer($request);
        $viewer->assign('PROVIDER_FORM_HTML', $html);
        return parent::showModuleDetailView($request);
    }

}
