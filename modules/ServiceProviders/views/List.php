<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class ServiceProviders_List_View extends Vtiger_List_View {

    public function preProcess(Vtiger_Request $request) {
        header('Location:index.php?module=ServiceProviders&parent=Settings&view=List');
    }

}
