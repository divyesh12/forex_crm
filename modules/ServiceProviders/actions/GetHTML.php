<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * @creator: Divyesh Chothani
 * @comment:  return provider form html
 * @date: 17-10-2019
 * */
class ServiceProviders_GetHTML_Action extends Vtiger_BasicAjax_Action {

    function __construct() {
        parent::__construct();
    }

    function process(Vtiger_Request $request) {
        global $root_directory;

        $module = $request->get('module');
        $provider = $request->get('provider');
        $fileName = $provider . '.php';
        $filePath = $root_directory . '/modules/' . $module . '/providers/' . $fileName;
        $filePath = str_replace('//', '/', $filePath);

        if (file_exists($filePath)) {
            $providerClassName = $module . '_' . $provider . '_Provider';
            $handler = new $providerClassName();
            $getRequiredParams = $handler->getRequiredParams();
            $getHtml = ServiceProviders_Module_Model::getHtml($getRequiredParams);
            $result = array(1, $getHtml);
        } else {
            $getHtml = NULL;
            $result = array(0, $getHtml);
        }
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

}
