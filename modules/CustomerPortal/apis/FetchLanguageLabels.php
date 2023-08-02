<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class CustomerPortal_FetchLanguageLabels extends CustomerPortal_API_Abstract {

    function process(CustomerPortal_API_Request $request) {
        $current_user = $this->getActiveUser();
        $response = new CustomerPortal_API_Response();
        $language = $this->getActiveCustomer()->portal_language;
        if (empty($language))
            $language = 'en_us';

        if ($current_user) {
            $module_arr = array('LeverageHistory', 'ContactChannels', 'DemoAccount', 'LiveAccount', 'ModComments', 'Payments', 'HelpDesk', 'Documents', 'Contacts', 'CustomerPortal_Client', 'Faq', 'Ewallet');
            sort($module_arr);
            foreach ($module_arr as $key => $value) {
                include_once('languages/' . $language . '/' . $value . '.php');
                $response->addToResult($value, $languageStrings);
            }
            return $response;
        }
    }

}
