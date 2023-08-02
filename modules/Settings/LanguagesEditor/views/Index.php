<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_LanguagesEditor_Index_View extends Settings_Vtiger_Index_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('showModuleCreationLayout');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->getMode();
        if ($this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        } else {
            $this->showModuleCreationLayout($request);
        }
    }

    public function showModuleCreationLayout(Vtiger_Request $request) {
        global $adb, $log, $current_user;

        $current_language = $current_user->language;
        $languageList = Settings_LanguagesEditor_Module_Model::getLanguages();
        $languageFolderPath = scandir('languages/en_us');
        $filesListResult = array();
        foreach ($languageFolderPath as $moduleFileName) {
            $fileExtension = explode(".", $moduleFileName);
            if ($fileExtension[1] == 'php') {
                $filesListResult[$fileExtension[0]] = $moduleFileName;
            }
        }

        $qualifiedModule = $request->getModule(false);
        $viewer = $this->getViewer($request);
        $view = $request->get('modeview');

        if (isset($_REQUEST['button_submit']) && $_REQUEST['button_submit'] == 'saveFormData') {
            $languageFolderName = $_REQUEST['languageFolderName'];
            $current_language = $languageFolderName;
            $languageFileName = $_REQUEST['languageFileName'];
            $filePath = 'languages/' . $languageFolderName . '/' . $languageFileName . '.php';
            if (file_exists($filePath)) {
                include $filePath;
                $viewer->assign('LANGUAGE_STRING', $languageStrings);
                $viewer->assign('JS_LANGUAGE_STRING', $jsLanguageStrings);
            } else {
                echo 'This ' . $filePath . ' file not avaible';
                exit;
            }
        }
        /* Field Label Start */
        if (isset($_REQUEST['SubmitFieldLabel']) && $_REQUEST['SubmitFieldLabel'] == 'saveFieldLabel') {
            unset($_REQUEST['module'], $_REQUEST['parent'], $_REQUEST['view'], $_REQUEST['modeview'], $_REQUEST['button_submit'], $_REQUEST['__vtrftk'], $_REQUEST['SubmitFieldLabel']);

            $languageFolderName = $_REQUEST['languageFolderName'];
            $current_language = $languageFolderName;
            $languageFileName = $_REQUEST['languageFileName'];
            $filepath = 'languages/' . $languageFolderName . '/' . $languageFileName . '.php';
            include $filepath;
            $tab = '  ';
            $fieldLabelContent = '<?php ' . PHP_EOL . '
/*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************/' . PHP_EOL;
            $fieldLabelContent .= '  ' . PHP_EOL . ' $languageStrings = array(' . PHP_EOL;
            foreach ($_REQUEST as $fieldLabel => $fieldValue) {
                if ($fieldLabel != 'languageFolderName' && $fieldLabel != 'languageFileName') {
                    $fieldLabelContent .= $tab . "'$fieldLabel' => '" . addslashes($fieldValue) . "'," . PHP_EOL;
                }
            }
            $fieldLabelContent .= '); ' . PHP_EOL;

            $fieldLabelContent .= PHP_EOL . ' $jsLanguageStrings = array(' . PHP_EOL;
            if (!empty($jsLanguageStrings)) {
                foreach ($jsLanguageStrings as $jqueryLabel => $jqueryValue) {
                    $fieldLabelContent .= $tab . "'$jqueryLabel' => '" . addslashes($jqueryValue) . "'," . PHP_EOL;
                }
            }
            $fieldLabelContent .= ');' . PHP_EOL . '?>';
            file_put_contents($filepath, $fieldLabelContent);
            header('Location:index.php?module=LanguagesEditor&parent=Settings&view=Index&modeview=Detail&languageFolderName=' . $languageFolderName . '&languageFileName=' . $languageFileName . '&button_submit=saveFormData');
        }
        /* Field Label End */


        /* Jquery Label Start */
        if (isset($_REQUEST['SubmitJqueryLabel']) && $_REQUEST['SubmitJqueryLabel'] == 'saveJqueryLabel') {
            unset($_REQUEST['module'], $_REQUEST['parent'], $_REQUEST['view'], $_REQUEST['modeview'], $_REQUEST['button_submit'], $_REQUEST['__vtrftk'], $_REQUEST['SubmitJqueryLabel']);

            $languageFolderName = $_REQUEST['languageFolderName'];
            $current_language = $languageFolderName;
            $languageFileName = $_REQUEST['languageFileName'];
            $filepath = 'languages/' . $languageFolderName . '/' . $languageFileName . '.php';
            include $filepath;
            $tab = '  ';
            $fieldLabelContent = '<?php ' . PHP_EOL . '
/*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************/' . PHP_EOL;
            $fieldLabelContent .= '  ' . PHP_EOL . ' $languageStrings = array(' . PHP_EOL;
            foreach ($languageStrings as $fieldLabel => $fieldValue) {
                $fieldLabelContent .= $tab . "'$fieldLabel' => '" . addslashes($fieldValue) . "'," . PHP_EOL;
            }
            $fieldLabelContent .= '); ' . PHP_EOL;

            $fieldLabelContent .= PHP_EOL . ' $jsLanguageStrings = array(' . PHP_EOL;
            if (!empty($_REQUEST)) {
                foreach ($_REQUEST as $jqueryLabel => $jqueryValue) {
                    if ($jqueryLabel != 'languageFolderName' && $jqueryLabel != 'languageFileName') {
                        $fieldLabelContent .= $tab . "'$jqueryLabel' => '" . addslashes($jqueryValue) . "'," . PHP_EOL;
                    }
                }
            }
            $fieldLabelContent .= ');' . PHP_EOL . '?>';

            file_put_contents($filepath, $fieldLabelContent);
            header('Location:index.php?module=LanguagesEditor&parent=Settings&view=Index&modeview=Detail&languageFolderName=' . $languageFolderName . '&languageFileName=' . $languageFileName . '&button_submit=saveFormData');
        }
        //exit;
        $viewer->assign('FORM_HTML', $getFieldHTML);
        $viewer->assign('MODEVIEW', $view);
        $viewer->assign('LANGUAGES', $languageList);
        $viewer->assign('FILE_LIST', $filesListResult);
        $viewer->assign('CURRENT_LANGUAGE', $current_language);
        $viewer->assign('SELECTED_LANG_FOLDER_NAME', $_REQUEST['languageFolderName']);
        $viewer->assign('SELECTED_LANG_FILE_NAME', $_REQUEST['languageFileName']);
        $viewer->view('Index.tpl', $qualifiedModule);
    }

}
