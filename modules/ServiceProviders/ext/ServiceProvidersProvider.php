<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class ServiceProvidersProvider {

    //change by divyesh 18-11-2019
    static function getInstance($providername) {
        if (!empty($providername)) {
            $providername = trim($providername);

            $filepath = dirname(__FILE__) . "/../providers/{$providername}.php";
            //checkFileAccessForInclusion($filepath);
            if (file_exists($filepath)) {

                $className = "ServiceProviders_" . $providername . "_Provider";
                if (!class_exists($className)) {
                    include_once $filepath;
                }
                return new $className();
            } else {
                return false;
            }
        }
        return false;
    }

    /* end */

    static function listAll() {
        $providers = array();
        if ($handle = opendir(dirname(__FILE__) . '/../providers')) {
            while (false !== ($file = readdir($handle))) {
                if (!in_array($file, array('.', '..', '.svn', 'CVS'))) {
                    if (preg_match("/(.*)\.php$/", $file, $matches)) {
                        $providers[] = $matches[1];
                    }
                }
            }
        }
        return $providers;
    }

}

?>