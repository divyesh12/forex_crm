<?php

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

$clientRequestValues = $_POST;
if (get_magic_quotes_gpc()) {
    $clientRequestValues = stripslashes_recursive($clientRequestValues);
}

$clientRequestValuesRaw = array();
$response = CustomerPortal_API_EntryPoint::process(new CustomerPortal_API_Request($clientRequestValues, $clientRequestValuesRaw));

if ($response !== false) {
    header("Content-type:text/json;charset=UTF-8"); //Added by Sandeep Thakkar 06-04-2020
    echo $response->emitJSON();
}

