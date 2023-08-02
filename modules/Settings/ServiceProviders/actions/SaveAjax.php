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

class Settings_ServiceProviders_SaveAjax_Action extends Settings_Vtiger_Index_Action {

    public function process(Vtiger_Request $request) {
        global $adb,$log;
        $log->debug('Entering into Settings_ServiceProviders_SaveAjax_Action');
        $selectedProvider = $request->get('providertype');
        $isactive = $request->get('isactive');
        $recordId = $request->get('record');
        $provider_title = $request->get('title');
        $client_type = $request->get('client_type');
        $demo_meta_trader_ip = $request->get('demo_meta_trader_ip');
        $demo_meta_trader_user = $request->get('demo_meta_trader_user');
        $demo_meta_trader_password = $request->get('demo_meta_trader_password');

        $live_meta_trader_ip = $request->get('live_meta_trader_ip');
        $live_meta_trader_user = $request->get('live_meta_trader_user');
        $live_meta_trader_password = $request->get('live_meta_trader_password');
        $qualifiedModuleName = $request->getModule(false);
        if(empty($recordId))
        {
            $response = new Vtiger_Response();
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($provider_title);
            if(empty($client_type))
            {
                $providerResult = paymentGetwayCreationRestrictBaseOnPkg();$log->debug($providerResult);
                if(!$providerResult['success'])
                {
                    $result = vtranslate('LBL_PAYMENTGATEWAY_EXCEED_ERROR', $qualifiedModuleName);
                    $response->setError($result);
                    $response->emit();
                    exit;
                }
            }
            else
            {
                $providerResult = tradingPlatformCreationRestrictBaseOnPkg();$log->debug($providerResult);
                if(!$providerResult['success'])
                {
                    $result = vtranslate('LBL_TRADING_PLATFORM_EXCEED_ERROR', $qualifiedModuleName);
                    $response->setError($result);
                    $response->emit();
                    exit;
                }
            }
        }

        if($selectedProvider == 'MT4'){
            $meta4_trader_ios_link = $request->get('meta_trader_ios_link');
            $meta4_trader_android_link = $request->get('meta_trader_android_link');
            $meta4_trader_windows_link = $request->get('meta_trader_windows_link');
            // $query = "UPDATE `vtiger_module_configuration_editor` SET `fieldvalue`='$meta4_trader_windows_link' WHERE fieldname = 'mt4_windows_link'";
            $adb->pquery("UPDATE `vtiger_module_configuration_editor` SET `fieldvalue`='$meta4_trader_windows_link' WHERE fieldname = 'mt4_windows_link'",array());
            $adb->pquery("UPDATE `vtiger_module_configuration_editor` SET `fieldvalue`='$meta4_trader_android_link' WHERE fieldname = 'mt4_android_link'",array());
            $adb->pquery("UPDATE `vtiger_module_configuration_editor` SET `fieldvalue`='$meta4_trader_ios_link' WHERE fieldname = 'mt4_ios_link'",array());
        }else if($selectedProvider == 'MT5'){
            $meta5_trader_ios_link = $request->get('meta_trader_ios_link');
            $meta5_trader_android_link = $request->get('meta_trader_android_link');
            $meta5_trader_windows_link = $request->get('meta_trader_windows_link');

            $adb->pquery("UPDATE `vtiger_module_configuration_editor` SET `fieldvalue`='$meta5_trader_windows_link' WHERE fieldname = 'mt5_windows_link'",array());
            $adb->pquery("UPDATE `vtiger_module_configuration_editor` SET `fieldvalue`='$meta5_trader_android_link' WHERE fieldname = 'mt5_android_link'",array());
            $adb->pquery("UPDATE `vtiger_module_configuration_editor` SET `fieldvalue`='$meta5_trader_ios_link' WHERE fieldname = 'mt5_ios_link'",array());
        }else if($selectedProvider == 'Vertex'){
            $vertex_trader_ios_link = $request->get('meta_trader_ios_link');
            $vertex_trader_android_link = $request->get('meta_trader_android_link');
            $vertex_trader_windows_link = $request->get('meta_trader_windows_link');

            $adb->pquery("UPDATE `vtiger_module_configuration_editor` SET `fieldvalue`='$vertex_trader_windows_link' WHERE fieldname = 'vertex_windows_link'",array());
            $adb->pquery("UPDATE `vtiger_module_configuration_editor` SET `fieldvalue`='$vertex_trader_android_link' WHERE fieldname = 'vertex_android_link'",array());
            $adb->pquery("UPDATE `vtiger_module_configuration_editor` SET `fieldvalue`='$vertex_trader_ios_link' WHERE fieldname = 'vertex_ios_link'",array());
        }

        if ($recordId) {
            $recordModel = Settings_ServiceProviders_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
        } else {
            $recordModel = Settings_ServiceProviders_Record_Model::getCleanInstance($qualifiedModuleName);
        }

        $editableFields = $recordModel->getEditableFields();
        foreach ($editableFields as $fieldName => $fieldModel) {
            $recordModel->set($fieldName, $request->get($fieldName));
        }

        $userName = $request->get('username');
        if (isset($userName)) {
            $recordModel->set('username', $request->get('username'));
        }
        $password = $request->get('username');
        if (isset($password)) {
            $recordModel->set('password', $request->get('password'));
        }
        
        $sequenceNumber = $request->get('sequence_number');
        /*Check unique sequence validation*/
        $isSequenceExist = checkSequenceNumberExist($sequenceNumber, $recordId);
        if($isSequenceExist)
        {
            $response = new Vtiger_Response();
            $result = vtranslate('LBL_SEQUENCE_NO_ALREADY_EXIST_ERROR', $qualifiedModuleName);
            $response->setError($result);
            $response->emit();
            exit;
        }
        if (isset($sequenceNumber)) {
            $recordModel->set('sequence_number', $request->get('sequence_number'));
        }
        
        if($request->get('file'))
        {
            $request->set('file',$request->get('file'));
        }
        
        if(isset($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name']))
        {
            $uploadStatus = uploadFileOnS3($_FILES['file']);
            if($uploadStatus)
            {
                $request->set('file',$uploadStatus);
            }
        }
        
        $parameters = array();
        
        $allProviders = $recordModel->getModule()->getAllProviders();
        foreach ($allProviders as $provider) {
            if ($provider->getName() === $selectedProvider) {
                $fieldsInfo = Settings_ServiceProviders_ProviderField_Model::getInstanceByProvider($provider);
                foreach ($fieldsInfo as $fieldInfo) {
                    $recordModel->set($fieldInfo['name'], $request->get($fieldInfo['name']));
                    $parameters[$fieldInfo['name']] = $request->get($fieldInfo['name']);
                }
                $recordModel->set('parameters', Zend_Json::encode($parameters));
                break;
            }
        }

        $response = new Vtiger_Response();
        $provider = ServiceProvidersManager::getInstanceByProvider($provider_title);
        if (!empty($provider) && $isactive && $provider::PROVIDER_TYPE == 1) {
            $demoAccountServerConfigurationResult = $provider->checkMetaTraderServerConfiguration($client_type, $selectedProvider, $demo_meta_trader_ip, $demo_meta_trader_user, $demo_meta_trader_password, 'DemoAccount');

            $liveAccountServerConfigurationResult = $provider->checkMetaTraderServerConfiguration($client_type, $selectedProvider, $live_meta_trader_ip, $live_meta_trader_user, $live_meta_trader_password, 'LiveAccount');

            $create_user_code_live = $liveAccountServerConfigurationResult->Code;
            $create_user_messege_live = $liveAccountServerConfigurationResult->Message;

            $create_user_code_demo = $demoAccountServerConfigurationResult->Code;
            $create_user_messege_demo = $demoAccountServerConfigurationResult->Message;
            if ($create_user_code_live == 200 && $create_user_code_demo == 200) {
                $recordModel->save();
                $result = array('message' => vtranslate('LBL_SAVE_SERVICE_PROVIDER', $qualifiedModuleName));
                $response->setResult($result);
            } else {
                $result = vtranslate('LBL_CREDENTIALS_ERROR', $qualifiedModuleName);
                $response->setError($result);
            }
        } else {
            $recordModel->save();
            $result = array('message' => vtranslate('LBL_SAVED_SUCCESSFULLY', $qualifiedModuleName));
            $response->setResult($result);
        }

        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }

}
