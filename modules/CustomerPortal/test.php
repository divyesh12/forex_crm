<?php

#include_once 'include.inc';
#include_once './include/request_helper.php';

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

include_once 'include.inc';

/** Take care of stripping the slashes */
function stripslashes_recursive($value) {
    $value = is_array($value) ? array_map('stripslashes_recursive', $value) : stripslashes($value);
    return $value;
}


$data = array(
    '_operation' => 'FetchLanguageLabels'
);
$clientRequestValues = $data;
//$clientRequestValues = $_POST;
if (get_magic_quotes_gpc()) {
    $clientRequestValues = stripslashes_recursive($clientRequestValues);
}

$clientRequestValuesRaw = array();
CustomerPortal_API_EntryPoint::process(new CustomerPortal_API_Request($clientRequestValues, $clientRequestValuesRaw));


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

