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

class CustomerPortal_FetchConfiguration extends CustomerPortal_API_Abstract {

    function process(CustomerPortal_API_Request $request) {
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $contactId = $this->getActiveCustomer()->id;
        
        if ($current_user) {
            $provider = ServiceProvidersManager::getActiveProviderInstance();
            $tradingPlatform = [];
            $tradingLeverage = [];
            foreach ($provider AS $key=>$value) {
                if (isset($value->parameters['client_type'])) {
                    $tradingPlatform[$key]['platform_type'] = $value->parameters['providertype'];
                    $tradingPlatform[$key]['platform_label'] = $value->parameters['title'];
                    $tradingLeverage[$key]['platform_type'] = $value->parameters['providertype'];
                    $tradingLeverage[$key]['leverage_enable'] = ($value->parameters['leverage_enable'] == "" || $value->parameters['leverage_enable'] == "No") ? false : true;
                }
            }
            /*Get referral link */
            $referral_affiliate_link = "";
            $contactWebId = vtws_getWebserviceEntityId('Contacts', $contactId);
            $contact = vtws_retrieve($contactWebId, $current_user);
            if($contact['record_status'] == "Approved")
            {
                $referral_affiliate_link = configvar('liveacc_referral_url');
                $affiliate_code = $contact['affiliate_code'];
                if (strpos($referral_affiliate_link, '?')) {
                    $referral_affiliate_link = $referral_affiliate_link . '&ref=' . $affiliate_code;
                } else {
                    $referral_affiliate_link = $referral_affiliate_link . '?ref=' . $affiliate_code;
                }
            }
            /*Get referral link */

            $tradingPlatform = array_values($tradingPlatform);
            $tradingLeverage = array_values($tradingLeverage);
            $contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
            $response->addToResult('config', configvar());
            $response->addToResult('is_document_verified', vtws_retrieve($contactId, $current_user)['is_document_verified']);
            $response->addToResult('trading_platform', $tradingPlatform);
            $response->addToResult('trading_leverage', $tradingLeverage);
            $response->addToResult('referral_affiliate_link', $referral_affiliate_link);
            return $response;
        }
    }

}
