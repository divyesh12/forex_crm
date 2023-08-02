<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class TradesCommission_calculateCommission_Action extends Vtiger_Action_Controller {
    
        function __construct() {
            $this->exposeMethod('calculateManual');
        }

        public function process(Vtiger_Request $request) {
            $mode = $request->getMode();
            if(!empty($mode) && $this->isMethodExposed($mode)) {
                    $this->invokeExposedMethod($mode, $request);
                    return;
            }
        }
        
        function calculateManual(Vtiger_Request $request) {
            global $adb, $log;
            $manualDate = $request->get('manualDate');
            $result = false;
            if(!empty($manualDate))
            {
                $formatedDate = date('Y-m-d', strtotime($manualDate));
                if($formatedDate === '1970-01-01')
                {
                    echo 'Please provide proper date format. ex."dd-mm-yyyy"';exit;
                }
                $commissionData = getTrades('manual', $formatedDate);
                $result = saveTradeCalculation($commissionData);
            }
            if($result)
            {
                echo 'Commission calculate successfully..';exit;
            }
            echo 'No pending trade commission found.';exit;
        }
	
}
