<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class LeverageHistory_LeverageChange_Action extends Vtiger_Action_Controller {
    
        function __construct() {
            $this->exposeMethod('leverageChangeManually');
        }

        public function process(Vtiger_Request $request) {
            $mode = $request->getMode();
            if(!empty($mode) && $this->isMethodExposed($mode)) {
                    $this->invokeExposedMethod($mode, $request);
                    return;
            }
        }
        
        function leverageChangeManually(Vtiger_Request $request) {
            global $adb, $log;
            ini_set('memory_limit','2048M');
            ini_set('max_execution_time', '300');
            $mycsvfile = $request->get('file_name');
            $accountNumbers = $this->readCsvFile($mycsvfile);
            $provider = ServiceProvidersManager::getActiveInstanceByProvider('MT5');
            if (empty($provider))
            {
                $message = "Service provider issue!";
                throw new Exception($message);
            }
            foreach($accountNumbers as $account => $leverage)
            {
                $changeLeverageResult = $provider->changeLeverage($account, $leverage);
                $change_leverage_code = $changeLeverageResult->Code;
                $change_leverage_messege = $changeLeverageResult->Message;
                if ($change_leverage_messege == 'Ok' && $change_leverage_code == 200)
                {
                    echo $account . '-Leverage Changed successfully <br/>';
                }
                elseif ($change_leverage_code == 201)
                {
                    echo $account . '-Leverage update issue1! <br/>';
                }
                else
                {
                    echo $account . '-Leverage update issue2! <br/>';
                }
            }
        }

        function readCsvFile($fileName = "") {
            global $root_directory;
            $accountNumbers = array();
            if(!empty($fileName))
            {
                $count = 0;
                if (($handle = fopen($root_directory.'/'.$fileName.'.csv', "r")) !== FALSE)
                {
                    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE)
                    {
                        if($count == 0){$count++; continue;}
                        $accountNumbers[$data[6]] = $data[5];
                        $count++;
                    }
                }
            }
            return $accountNumbers;
        }
	
}
