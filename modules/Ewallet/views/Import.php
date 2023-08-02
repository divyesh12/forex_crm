<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Ewallet_Import_View extends Vtiger_Import_View {

    function __construct() {
        parent::__construct();
    }

    /* Add By Divyesh Chothani
     * Date:- 31-12-2019
     * Comment:- Permission denied for Import Functionality 
     */

    public function requiresPermission(Vtiger_Request $request) {
        $permissions = parent::requiresPermission($request);
        $permissions[] = array();
        return $permissions;
    }

}
