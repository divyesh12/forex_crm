<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_ModuleBuilder_AddField_View extends Settings_Vtiger_Index_View {

      function __construct() {
            parent::__construct();
            $this->exposeMethod('showAddFieldLayout');
      }

      public function process(Vtiger_Request $request) {
            $mode = $request->getMode();
            if ($this->isMethodExposed($mode)) {
                  $this->invokeExposedMethod($mode, $request);
            } else {
                  //by default show field layout
                  $this->showAddFieldLayout($request);
            }
      }

      public function showAddFieldLayout(Vtiger_Request $request) {
            global $adb, $log;
            $supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();
            $supportedModulesList = array_flip($supportedModulesList);
            $qualifiedModule = $request->getModule(false);
            $viewer = $this->getViewer($request);

            $module_name = $_REQUEST['module_name'];
            $block_name = $_REQUEST['block_name'];
            $field_name = $_REQUEST['field_name'];
            $label_name = $_REQUEST['label_name'];
            $ui_type = $_REQUEST['field_ui_type'];
            $explode_ui_type = explode("_", $ui_type);
            $field_type = $explode_ui_type[0];
            $field_ui_type = $explode_ui_type[1];
            $picklist_values = $_REQUEST['picklist_values'];
            $relatedmodule_name = $_REQUEST['relatedmodule_name'];

            $module = Vtiger_Module::getInstance($_REQUEST["module_name"]);
            $modul_table_name = $module->basetable;

            $result = $adb->pquery('select * from vtiger_field where tablename= ? and columnname =? ', array($modul_table_name, $field_name));
            $no_of_record = $adb->num_rows($result);


            if (isset($_REQUEST['button_submit']) && $_REQUEST['button_submit'] == 'save_field' && $module_name != '' && $block_name != '' && $field_name != '' && $label_name != '' && $field_ui_type != '') {

                  if ($no_of_record > 0) {
                        header('Location:index.php?module=ModuleBuilder&parent=Settings&view=AddField&block=6&fieldid=42&field_exists=1');
                  } else {

                        /* Filed Creation */

                        if ($field_type == 'Text' && $field_ui_type == 1) {
                              $typeofdata = 'V~O';
                              $columntype = 'varchar(255)';
                        } elseif ($field_type == 'Decimal' && $field_ui_type == 7) {
                              $typeofdata = 'NN~O';
                              $columntype = 'decimal(13,2)';
                        } elseif ($field_type == 'Integer' && $field_ui_type == 7) {
                              $typeofdata = 'I~O';
                              $columntype = 'bigint(11)';
                        } elseif ($field_type == 'Percent' && $field_ui_type == 9) {
                              $typeofdata = 'N~O~2~2';
                              $columntype = 'decimal(7,3)';
                        } elseif ($field_type == 'Currency' && $field_ui_type == 71) {
                              $typeofdata = 'N~O';
                              $columntype = 'decimal(25,8)';
                        } elseif ($field_type == 'Date' && $field_ui_type == 5) {
                              $typeofdata = 'D~O';
                              $columntype = 'date';
                        } elseif ($field_type == 'Email' && $field_ui_type == 13) {
                              $typeofdata = 'E~O';
                              $columntype = 'varchar(50)';
                        } elseif ($field_type == 'Phone' && $field_ui_type == 11) {
                              $typeofdata = 'V~O';
                              $columntype = 'varchar(30)';
                        } elseif ($field_type == 'PickList' && $field_ui_type == 15) {
                              if ($picklist_values != '') {
                                    $typeofdata = 'V~O';
                                    $columntype = 'varchar(255)';
                                    $explode = explode(",", $picklist_values);
                                    $picklist_value_implode = "'" . implode("','", $explode) . "'";
                                    $picklist_value_array = Array($explode);
                              }
                        } elseif ($field_type == 'URL' && $field_ui_type == 17) {
                              $typeofdata = 'V~O';
                              $columntype = 'varchar(255)';
                        } elseif ($field_type == 'Checkbox' && $field_ui_type == 56) {
                              $typeofdata = 'C~O';
                              $columntype = 'varchar(2)';
                        } elseif ($field_type == 'TextArea' && $field_ui_type == 21) {
                              $typeofdata = 'V~O';
                              $columntype = 'text';
                        } elseif ($field_type == 'MultiSelectComboBox' && $field_ui_type == 33) {
                              if ($picklist_values != '') {
                                    $typeofdata = 'V~O';
                                    $columntype = 'text';
                                    $explode = explode(",", $picklist_values);
                                    $picklist_value_implode = "'" . implode("','", $explode) . "'";
                                    $picklist_value_array = Array($explode);
                              }
                        } elseif ($field_type == 'SkyPeID' && $field_ui_type == 85) {
                              $typeofdata = 'V~O';
                              $columntype = 'varchar(255)';
                        } elseif ($field_type == 'Time' && $field_ui_type == 14) {
                              $typeofdata = 'T~O';
                              $columntype = 'time';
                        } elseif ($field_type == 'RelationModule' && $field_ui_type == 10) {
                              $typeofdata = 'V~O';
                              $columntype = 'varchar(255)';
                        }

                        $Vtiger_Utils_Log = true;
                        include_once('vtlib/Vtiger/Menu.php');
                        include_once('vtlib/Vtiger/Module.php');

                        $module = Vtiger_Module::getInstance($module_name);
                        $block = Vtiger_Block::getInstance($block_name, $module);

                        $field1 = new Vtiger_Field();
                        $field1->name = $field_name;
                        $field1->label = $label_name;
                        $field1->table = $module->basetable;
                        $field1->column = $field1->name;
                        $field1->columntype = $columntype;
                        $field1->uitype = $field_ui_type;
                        $field1->typeofdata = $typeofdata;
                        if ($field_type == 'RelationModule' && $field_ui_type == 10) {
                              $field1->setRelatedModules(Array($relatedmodule_name));
                        }
                        $block->addField($field1);
                        if (($field_type == 'MultiSelectComboBox' && $field_ui_type == 33) || ($field_type == 'PickList' && $field_ui_type == 15)) {
                              $field1->setPicklistValues($picklist_value_array[0]);
                        }

                        if ($relatedmodule_name != '' && isset($_REQUEST['relatedmodule_name']) && $field_ui_type == 10) {
                              $table_name = $module->basetable;
                              $result = $adb->pquery('select fieldid from vtiger_field where tablename= ? and columnname =? ', array($table_name, $field_name));
                              if ($adb->num_rows($result) > 0) {
                                    $fieldid = $adb->query_result($result, 0, 'fieldid');
                              }
                              $adb->query("insert into vtiger_fieldmodulerel (fieldid, module, relmodule, status, sequence) values ($fieldid, '" . $module_name . "','" . $relatedmodule_name . "' , NULL,NULL)");
                        }

                        /* Module Language file creation */
                        $filepath = 'languages/en_us/' . $module_name . '.php';
                        include $filepath;
                        $formData = array($label_name => $label_name);
                        $languageStrings = array_merge($languageStrings, $formData);
                        $tab = '    ';
                        $newContent = '<?php' . PHP_EOL . '$languageStrings = array(' . PHP_EOL;
                        foreach ($languageStrings as $key => $value) {
                              $newContent .= $tab . "'$key' => '" . addslashes($value) . "'," . PHP_EOL;
                        }
                        $newContent .= ');' . PHP_EOL;
                        file_put_contents($filepath, $newContent);

                        $date = date("d-m-Y");
                        mkdir('modules/' . $module_name . '/' . $module_name . '_Scripts');
                        $ScriptFileContent = '<?php //Script Created Date :-' . $date . '
                        
                        $Vtiger_Utils_Log = true;
                        include_once("vtlib/Vtiger/Menu.php");
                        include_once("vtlib/Vtiger/Module.php");

                        $module = Vtiger_Module::getInstance("' . $module_name . '");
                        $block = Vtiger_Block::getInstance("' . $block_name . '", $module);

                        $' . $field_name . ' = new Vtiger_Field();
                        $' . $field_name . '->name =  "' . $field_name . '";
                        $' . $field_name . '->label = "' . $label_name . '";
                        $' . $field_name . '->table =  $module->basetable ;
                        $' . $field_name . '->column = "' . $field1->name . '";
                        $' . $field_name . '->columntype = "' . $columntype . '";
                        $' . $field_name . '->uitype = ' . $field_ui_type . ';
                        $' . $field_name . '->typeofdata = "' . $typeofdata . '";';
                        if ($field_type == 'RelationModule' && $field_ui_type == 10) {
                              $ScriptFileContent .= '$block->addField($' . $field_name . ');';
                              $ScriptFileContent .= '$' . $field_name . '->setRelatedModules(Array("' . $relatedmodule_name . '"));';
                        } elseif (($field_type == 'MultiSelectComboBox' && $field_ui_type == 33) || ($field_type == 'PickList' && $field_ui_type == 15)) {
                              $ScriptFileContent .= '$block->addField($' . $field_name . ');';
                              $ScriptFileContent .= '$' . $field_name . '->setPicklistValues(Array(' . $picklist_value_implode . '));';
                        } else {
                              $ScriptFileContent .= '$block->addField($' . $field_name . ');';
                        }
                        $ScriptFileContent .= '?>';
                        $ScriptFile = fopen('modules/' . $module_name . '/' . $module_name . '_Scripts/' . $module_name . '_' . $field_name . '_script.php', "w") or die("Unable to open file!");
                        fwrite($ScriptFile, $ScriptFileContent);
                        chmod('modules/' . $module_name . '/' . $module_name . '_Scripts/' . $module_name . '_' . $field_name . '_script.php', 0777);
                        chmod('modules/' . $module_name . '/' . $module_name . '_Scripts', 0777);
                        fclose($ScriptFile);

                        header('Location:index.php?module=ModuleBuilder&parent=Settings&view=AddField&block=6&fieldid=42&add_field=1');
                  }
            }
            $viewer->assign('SUPPORTED_MODULES', $supportedModulesList);
            $viewer->view('AddField.tpl', $qualifiedModule);
      }

}
