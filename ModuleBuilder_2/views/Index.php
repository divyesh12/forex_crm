<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_ModuleBuilder_Index_View extends Settings_Vtiger_Index_View {

      function __construct() {
            parent::__construct();
            $this->exposeMethod('showModuleCreationLayout');
      }

      public function process(Vtiger_Request $request) {
            $mode = $request->getMode();
            if ($this->isMethodExposed($mode)) {
                  $this->invokeExposedMethod($mode, $request);
            } else {
                  //by default show field layout
                  $this->showModuleCreationLayout($request);
            }
      }

      public function showModuleCreationLayout(Vtiger_Request $request) {
            global $adb, $log;
            $qualifiedModule = $request->getModule(false);
            $viewer = $this->getViewer($request);

            $module_name = $_REQUEST['module_name'];
            $parent_module = $_REQUEST['parent_module'];
            $app2tab = $parent_module;
//            if ($parent_module == 'PROJECT') {
//                  $parent_module = 'Tools';
//                  $app2tab = 'PROJECT';
//            }
            $table_name = 'vtiger_' . strtolower($module_name);
            $relatedmodule_name = $_REQUEST['relatedmodule_name'];
            $field_name = $_REQUEST['field_name'];
            $label_name = $_REQUEST['label_name'];
            $ui_type = $_REQUEST['field_ui_type'];
            $explode_ui_type = explode("_", $ui_type);
            $field_type = $explode_ui_type[0];
            $field_ui_type = $explode_ui_type[1];
            $picklist_values = $_REQUEST['picklist_values'];


            if (isset($_REQUEST['button_submit']) && $_REQUEST['button_submit'] == 'module_creation' && $module_name != '') {

                  include_once 'vtlib/Vtiger/Module.php';
                  $Vtiger_Utils_Log = true;
                  $MODULENAME = $module_name;
                  $moduleInstance = Vtiger_Module::getInstance($MODULENAME);
                  if ($moduleInstance || file_exists('modules/' . $MODULENAME)) {
                        header('Location:index.php?module=ModuleBuilder&parent=Settings&view=Index&block=6&fieldid=41&is_module_exists=1');
                  } else {
                        /* Module Creation */
                        $moduleInstance = new Vtiger_Module();
                        $moduleInstance->name = $MODULENAME;
                        $moduleInstance->parent = $parent_module;
                        $moduleInstance->save();

                        // Schema Setup
                        $moduleInstance->initTables();

                        // Field Setup
                        $block = new Vtiger_Block();
                        $block->label = 'LBL_BASIC_INFORMATION';
                        $moduleInstance->addBlock($block);

                        $blockcf = new Vtiger_Block();
                        $blockcf->label = 'LBL_CUSTOM_INFORMATION';
                        $moduleInstance->addBlock($blockcf);
                        if ($field_name != '' && $label_name != '' && $field_ui_type != '') {

                              if ($field_type == 'Text' && $field_ui_type == 1) {
                                    $typeofdata = 'V~M';
                                    $columntype = 'varchar(255)';
                              } elseif ($field_type == 'Decimal' && $field_ui_type == 7) {
                                    $typeofdata = 'NN~M';
                                    $columntype = 'decimal(13,2)';
                              } elseif ($field_type == 'Integer' && $field_ui_type == 7) {
                                    $typeofdata = 'I~M';
                                    $columntype = 'bigint(11)';
                              } elseif ($field_type == 'Percent' && $field_ui_type == 9) {
                                    $typeofdata = 'N~M~2~2';
                                    $columntype = 'decimal(13,2)';
                              } elseif ($field_type == 'Currency' && $field_ui_type == 71) {
                                    $typeofdata = 'N~M';
                                    $columntype = 'decimal(25,8)';
                              } elseif ($field_type == 'Date' && $field_ui_type == 5) {
                                    $typeofdata = 'D~M';
                                    $columntype = 'date';
                              } elseif ($field_type == 'Email' && $field_ui_type == 13) {
                                    $typeofdata = 'E~M';
                                    $columntype = 'varchar(50)';
                              } elseif ($field_type == 'Phone' && $field_ui_type == 11) {
                                    $typeofdata = 'V~M';
                                    $columntype = 'varchar(30)';
                              } elseif ($field_type == 'PickList' && $field_ui_type == 15) {
                                    if ($picklist_values != '') {
                                          $typeofdata = 'V~M';
                                          $columntype = 'varchar(255)';
                                          $explode = explode(",", $picklist_values);
                                          $picklist_value_implode = "'" . implode("','", $explode) . "'";
                                          $picklist_value_array = Array($explode);
                                    }
                              } elseif ($field_type == 'URL' && $field_ui_type == 17) {
                                    $typeofdata = 'V~M';
                                    $columntype = 'varchar(255)';
                              } elseif ($field_type == 'Checkbox' && $field_ui_type == 56) {
                                    $typeofdata = 'C~M';
                                    $columntype = 'varchar(2)';
                              } elseif ($field_type == 'TextArea' && $field_ui_type == 21) {
                                    $typeofdata = 'V~M';
                                    $columntype = 'text';
                              } elseif ($field_type == 'MultiSelectComboBox' && $field_ui_type == 33) {
                                    if ($picklist_values != '') {
                                          $typeofdata = 'V~M';
                                          $columntype = 'text';
                                          $explode = explode(",", $picklist_values);
                                          $picklist_value_implode = "'" . implode("','", $explode) . "'";
                                          $picklist_value_array = Array($explode);
                                    }
                              } elseif ($field_type == 'SkyPeID' && $field_ui_type == 85) {
                                    $typeofdata = 'V~M';
                                    $columntype = 'varchar(255)';
                              } elseif ($field_type == 'Time' && $field_ui_type == 14) {
                                    $typeofdata = 'T~M';
                                    $columntype = 'time';
                              } elseif ($field_type == 'RelationModule' && $field_ui_type == 10) {
                                    $typeofdata = 'V~M';
                                    $columntype = 'varchar(255)';
                              }

                              $field1 = new Vtiger_Field();
                              $field1->name = $field_name;
                              $field1->label = $label_name;
                              $field1->table = $module->basetable;
                              $field1->uitype = $field_ui_type;
                              $field1->column = $field1->name;
                              $field1->columntype = $columntype;
                              $field1->typeofdata = $typeofdata;
                              if ($field_type == 'RelationModule' && $field_ui_type == 10) {
                                    $field1->setRelatedModules(Array($relatedmodule_name));
                              }
                              $block->addField($field1);
                              if (($field_type == 'PickList' && $field_ui_type == 15) || ($field_type == 'MultiSelectComboBox' && $field_ui_type == 33)) {
                                    $field1->setPicklistValues($picklist_value_array[0]);
                              }
                              $moduleInstance->setEntityIdentifier($field1);
                        }


                        // Recommended common fields every Entity module should have (linked to core table)
                        $mfield1 = new Vtiger_Field();
                        $mfield1->name = 'assigned_user_id';
                        $mfield1->label = 'Assigned To';
                        $mfield1->table = 'vtiger_crmentity';
                        $mfield1->column = 'smownerid';
                        $mfield1->uitype = 53;
                        $mfield1->typeofdata = 'V~M';
                        $blockcf->addField($mfield1);

                        $mfield2 = new Vtiger_Field();
                        $mfield2->name = 'CreatedTime';
                        $mfield2->label = 'Created Time';
                        $mfield2->table = 'vtiger_crmentity';
                        $mfield2->column = 'createdtime';
                        $mfield2->uitype = 70;
                        $mfield2->typeofdata = 'DT~O';
                        $mfield2->displaytype = 2;
                        $blockcf->addField($mfield2);

                        $mfield3 = new Vtiger_Field();
                        $mfield3->name = 'ModifiedTime';
                        $mfield3->label = 'Modified Time';
                        $mfield3->table = 'vtiger_crmentity';
                        $mfield3->column = 'modifiedtime';
                        $mfield3->uitype = 70;
                        $mfield3->typeofdata = 'DT~O';
                        $mfield3->displaytype = 2;
                        $blockcf->addField($mfield3);

                        // Filter Setup
                        $filter1 = new Vtiger_Filter();
                        $filter1->name = 'All';
                        $filter1->isdefault = true;
                        $moduleInstance->addFilter($filter1);
                        $filter1->addField($field1)->addField($mfield1, 3);

                        // Sharing Access Setup
                        $moduleInstance->setDefaultSharing();

                        // Webservice Setup
                        $moduleInstance->initWebservice();

                        if ($relatedmodule_name != '' && $field_ui_type == 10) {
                              //echo $table_name =  $module->basetable;
                              $result = $adb->pquery('select fieldid from vtiger_field where tablename= ? and columnname =? ', array($table_name, $field_name));
                              if ($adb->num_rows($result) > 0) {
                                    $fieldid = $adb->query_result($result, 0, 'fieldid');
                                    $adb->query("insert into vtiger_fieldmodulerel (fieldid, module, relmodule, status, sequence) values ($fieldid, '" . $module_name . "','" . $relatedmodule_name . "' , NULL,NULL)");
                                    echo "field Relation done" . "<br>";
                              }
                        }

                        /* Module Class FIle Creation */

                        mkdir('modules/' . $MODULENAME);
                        chmod('modules/' . $MODULENAME, 0777);
                        $moduleNameLowercase = strtolower($module_name);
                        $moduleClassContent = '<?php  include_once "modules/Vtiger/CRMEntity.php";
		    
		    class ' . $module_name . ' extends Vtiger_CRMEntity {
			var $table_name = "vtiger_' . $moduleNameLowercase . '";
			var $table_index = "' . $moduleNameLowercase . 'id";
			var $customFieldTable = Array("vtiger_' . $moduleNameLowercase . 'cf", "' . $moduleNameLowercase . 'id");
			var $tab_name = Array("vtiger_crmentity", "vtiger_' . $moduleNameLowercase . '", "vtiger_' . $moduleNameLowercase . 'cf");
			var $tab_name_index = Array("vtiger_crmentity" => "crmid","vtiger_' . $moduleNameLowercase . '" => "' . $moduleNameLowercase . 'id","vtiger_' . $moduleNameLowercase . 'cf" => "' . $moduleNameLowercase . 'id");
			var $list_fields = Array("' . $label_name . '" => Array("' . $moduleNameLowercase . '", "' . $field_name . '"),"Assigned To" => Array("crmentity", "smownerid"));
			var $list_fields_name = Array("' . $label_name . '" => "' . $field_name . '","Assigned To" => "assigned_user_id",);
			var $list_link_field = "' . $field_name . '";
			var $search_fields = Array("' . $label_name . '" => Array("' . $moduleNameLowercase . '", "' . $field_name . '"),"Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
			var $search_fields_name = Array("' . $label_name . '" => "' . $field_name . '","Assigned To" => "assigned_user_id",);
			var $popup_fields = Array("' . $field_name . '");
			var $def_basicsearch_col = "' . $field_name . '";
			var $def_detailview_recname = "' . $field_name . '";
			var $mandatory_fields = Array("' . $field_name . '", "assigned_user_id");
			var $default_order_by = "' . $field_name . '";
			var $default_sort_order = "ASC";

		    } ?>';

                        $module_FileName = $module_name . '.php';
                        $moduleClassFile = fopen('modules/' . $MODULENAME . '/' . $module_FileName, "w") or die("Unable to open file!");
                        fwrite($moduleClassFile, $moduleClassContent);
                        chmod('modules/' . $MODULENAME . '/' . $module_FileName, 0777);
                        fclose($moduleClassFile);

                        //module module file creation
                        mkdir('modules/' . $MODULENAME . '/models');
                        chmod('modules/' . $MODULENAME . '/models', 0777);
                        $modelContent = '<?php class ' . $MODULENAME . '_Module_Model extends Vtiger_Module_Model {

                                                                              public function isCommentEnabled() {
                                                                                    return true;
                                                                              }

                                                                        }  ?>';
                        $modelFile = fopen('modules/' . $MODULENAME . '/models/Module.php', "w") or die("Unable to open file!");
                        fwrite($modelFile, $modelContent);
                        chmod('modules/' . $MODULENAME . '/models/Module.php', 0777);
                        fclose($modelFile);
                        //module module file creation

                        $date_time = date("Y-m-d") . " " . date("h:i:s");
                        $modulemanifest_xml_content = "<?xml version='1.0'?>
                                                <module>
                                                      <exporttime>" . $date_time . "</exporttime>
                                                      <name>" . $MODULENAME . "</name>
                                                      <label>" . $MODULENAME . "</label>
                                                      <parent>" . $parent_module . "</parent>
                                                      <version>7.0.1</version>
                                                      <dependencies>
                                                            <vtiger_version>7.0.1</vtiger_version>
                                                      </dependencies>
                                                      <tables>
                                                            <table>
                                                                  <name>vtiger_" . $moduleNameLowercase . "</name>
                                                                  <sql><![CDATA[CREATE TABLE `vtiger_" . $moduleNameLowercase . "` (
                                                  `" . $moduleNameLowercase . "id` int(11) NOT NULL ,
                                                  `" . $field_name . "` " . $columntype . " DEFAULT NULL,
                                                  PRIMARY KEY (`" . $moduleNameLowercase . "id`)
                                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8]]></sql>
                                                            </table>
                                                            <table>
                                                                  <name>vtiger_" . $moduleNameLowercase . "cf</name>
                                                                  <sql><![CDATA[CREATE TABLE `vtiger_" . $moduleNameLowercase . "cf` (
                                                  `" . $moduleNameLowercase . "id` int(11) NOT NULL,
                                                  PRIMARY KEY (`" . $moduleNameLowercase . "id`)
                                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8]]></sql>
                                                            </table>
                                                            <table>
                                                            <name>vtiger_" . $moduleNameLowercase . "_user_field</name>
                                                            <sql><![CDATA[CREATE TABLE IF NOT EXISTS `vtiger_" . $moduleNameLowercase . "_user_field` (
                                                                        `recordid` int(25) NOT NULL,
                                                                        `userid` int(25) NOT NULL,
                                                                        `starred` varchar(100) DEFAULT NULL,
                                                                        KEY `fk_" . $moduleNameLowercase . "id_vtiger_" . $moduleNameLowercase . "_user_field` (`recordid`)
                                                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8]]></sql>
                                                            </table>
                                                      </tables>
                                                      <blocks>
                                                            <block>
                                                                  <label>LBL_BASIC_INFORMATION</label>
                                                                  <fields>
                                                                        <field>
                                                                              <fieldname>" . $field_name . "</fieldname>
                                                                              <uitype>" . $field_ui_type . "</uitype>
                                                                              <columnname>" . $field_name . "</columnname>
                                                                              <tablename>vtiger_" . $moduleNameLowercase . "</tablename>
                                                                              <generatedtype>1</generatedtype>
                                                                              <fieldlabel>" . $label_name . "</fieldlabel>
                                                                              <readonly>1</readonly>
                                                                              <presence>2</presence>
                                                                              <defaultvalue></defaultvalue>
                                                                              <sequence>1</sequence>
                                                                              <maximumlength>100</maximumlength>
                                                                              <typeofdata>" . $typeofdata . "</typeofdata>
                                                                              <quickcreate>1</quickcreate>
                                                                              <quickcreatesequence></quickcreatesequence>
                                                                              <displaytype>1</displaytype>
                                                                              <info_type>BAS</info_type>
                                                                              <helpinfo><![CDATA[]]></helpinfo>
                                                                              <masseditable>0</masseditable>
                                                                              <summaryfield>0</summaryfield>
                                                                              <entityidentifier>
                                                                                    <entityidfield>" . $moduleNameLowercase . "id</entityidfield>
                                                                                    <entityidcolumn>" . $moduleNameLowercase . "id</entityidcolumn>
                                                                              </entityidentifier>";
                        if ($field_ui_type == 10) {
                              $modulemanifest_xml_content .= "<relatedmodules>
                                                                                    <relatedmodule>" . $relatedmodule_name . "</relatedmodule>
                                                                              </relatedmodules>";
                        }
                        if ($field_ui_type == 15 || $field_ui_type == 33) {
                              $modulemanifest_xml_content .= "<picklistvalues>";
                              foreach ($explode as $picklistValue) {
                                    $modulemanifest_xml_content .= "<picklistvalue>" . $picklistValue . "</picklistvalue>";
                              }

                              $modulemanifest_xml_content .= "</picklistvalues>";
                        }

                        $modulemanifest_xml_content .= "</field>
                                                                  </fields>
                                                            </block>
                                                            <block>
                                                                  <label>LBL_CUSTOM_INFORMATION</label>
                                                                  <fields>
                                                                        <field>
                                                                              <fieldname>assigned_user_id</fieldname>
                                                                              <uitype>53</uitype>
                                                                              <columnname>smownerid</columnname>
                                                                              <tablename>vtiger_crmentity</tablename>
                                                                              <generatedtype>1</generatedtype>
                                                                              <fieldlabel>Assigned To</fieldlabel>
                                                                              <readonly>1</readonly>
                                                                              <presence>2</presence>
                                                                              <defaultvalue></defaultvalue>
                                                                              <sequence>1</sequence>
                                                                              <maximumlength>100</maximumlength>
                                                                              <typeofdata>V~M</typeofdata>
                                                                              <quickcreate>0</quickcreate>
                                                                              <quickcreatesequence>0</quickcreatesequence>
                                                                              <displaytype>1</displaytype>
                                                                              <info_type>BAS</info_type>
                                                                              <helpinfo><![CDATA[]]></helpinfo>
                                                                              <masseditable>1</masseditable>
                                                                              <summaryfield>0</summaryfield>
                                                                        </field>
                                                                        <field>
                                                                              <fieldname>createdtime</fieldname>
                                                                              <uitype>70</uitype>
                                                                              <columnname>createdtime</columnname>
                                                                              <tablename>vtiger_crmentity</tablename>
                                                                              <generatedtype>1</generatedtype>
                                                                              <fieldlabel>Created Time</fieldlabel>
                                                                              <readonly>1</readonly>
                                                                              <presence>2</presence>
                                                                              <defaultvalue></defaultvalue>
                                                                              <sequence>2</sequence>
                                                                              <maximumlength>100</maximumlength>
                                                                              <typeofdata>DT~O</typeofdata>
                                                                              <quickcreate>0</quickcreate>
                                                                              <quickcreatesequence>0</quickcreatesequence>
                                                                              <displaytype>2</displaytype>
                                                                              <info_type>BAS</info_type>
                                                                              <helpinfo><![CDATA[]]></helpinfo>
                                                                              <masseditable>0</masseditable>
                                                                              <summaryfield>0</summaryfield>
                                                                        </field>
                                                                        <field>
                                                                              <fieldname>modifiedtime</fieldname>
                                                                              <uitype>70</uitype>
                                                                              <columnname>modifiedtime</columnname>
                                                                              <tablename>vtiger_crmentity</tablename>
                                                                              <generatedtype>1</generatedtype>
                                                                              <fieldlabel>Modified Time</fieldlabel>
                                                                              <readonly>1</readonly>
                                                                              <presence>2</presence>
                                                                              <defaultvalue></defaultvalue>
                                                                              <sequence>3</sequence>
                                                                              <maximumlength>100</maximumlength>
                                                                              <typeofdata>DT~O</typeofdata>
                                                                              <quickcreate>0</quickcreate>
                                                                              <quickcreatesequence>0</quickcreatesequence>
                                                                              <displaytype>2</displaytype>
                                                                              <info_type>BAS</info_type>
                                                                              <helpinfo><![CDATA[]]></helpinfo>
                                                                              <masseditable>0</masseditable>
                                                                              <summaryfield>0</summaryfield>
                                                                        </field>
                                                                  </fields>
                                                            </block>
                                                      </blocks>
                                                      <customviews>
                                                            <customview>
                                                                  <viewname>All</viewname>
                                                                  <setdefault>true</setdefault>
                                                                  <setmetrics>false</setmetrics>
                                                                  <fields>
                                                                        <field>
                                                                              <fieldname>" . $field_name . "</fieldname>
                                                                              <columnindex>1</columnindex>
                                                                        </field>
                                                                        <field>
                                                                              <fieldname>assigned_user_id</fieldname>
                                                                              <columnindex>2</columnindex>
                                                                        </field>
                                                                        <field>
                                                                              <fieldname>createdtime</fieldname>
                                                                              <columnindex>3</columnindex>
                                                                        </field>
                                                                        <field>
                                                                              <fieldname>modifiedtime</fieldname>
                                                                              <columnindex>4</columnindex>
                                                                        </field>
                                                                  </fields>
                                                            </customview>
                                                      </customviews>
                                                      <sharingaccess>
                                                            <default>public_readwritedelete</default>
                                                      </sharingaccess>
                                                      <crons>
                                                      </crons>
                                                </module>";
                        $manifest_xml_fileName = 'manifest.xml';
                        $manifest_xml_File = fopen('modules/' . $MODULENAME . '/' . $manifest_xml_fileName, "w") or die("Unable to open file!");
                        fwrite($manifest_xml_File, $modulemanifest_xml_content);
                        chmod('modules/' . $MODULENAME . '/' . $manifest_xml_fileName, 0777);
                        fclose($manifest_xml_File);

                        $moduleschema_xml_file_content = "<?xml version='1.0'?>
                                                                                    <schema>
                                                                                          <tables>
                                                                                                <table>
                                                                                                      <name>vtiger_" . $moduleNameLowercase . "</name>
                                                                                                      <sql><![CDATA[CREATE TABLE `vtiger_" . $moduleNameLowercase . "` (
                                                                                        `" . $moduleNameLowercase . "id` int(11) NOT NULL DEFAULT '0',
                                                                                          `" . $field_name . "` " . $columntype . " DEFAULT NULL,
                                                                                          PRIMARY KEY (`" . $moduleNameLowercase . "id`)
                                                                                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8]]></sql>
                                                                                                </table>
                                                                                                <table>
                                                                                                      <name>vtiger_" . $moduleNameLowercase . "cf</name>
                                                                                                      <sql><![CDATA[CREATE TABLE `vtiger_" . $moduleNameLowercase . "cf` (
                                                                                      `" . $moduleNameLowercase . "id` int(11) NOT NULL,
                                                                                      PRIMARY KEY (`" . $moduleNameLowercase . "id`)
                                                                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8]]></sql>
                                                                                                </table>
                                                                                                <table>
                                                                                                      <name>vtiger_" . $moduleNameLowercase . "_user_field</name>
                                                                                                      <sql><![CDATA[CREATE TABLE IF NOT EXISTS `vtiger_" . $moduleNameLowercase . "_user_field` (
                                                                                                      `recordid` int(25) NOT NULL,
                                                                                                      `userid` int(25) NOT NULL,
                                                                                                      `starred` varchar(100) DEFAULT NULL,
                                                                                                      KEY `fk_" . $moduleNameLowercase . "id_vtiger_" . $moduleNameLowercase . "_user_field` (`recordid`)
                                                                                                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8]]></sql>
                                                                                          </table>
                                                                                          </tables>
                                                                                    </schema>";

                        $schema_xml_fileName = 'schema.xml';
                        $schema_xml_File = fopen('modules/' . $MODULENAME . '/' . $schema_xml_fileName, "w") or die("Unable to open file!");
                        fwrite($schema_xml_File, $moduleschema_xml_file_content);
                        chmod('modules/' . $MODULENAME . '/' . $schema_xml_fileName, 0777);
                        fclose($schema_xml_File);


                        $moduleSequenceNoScritpt_Content = '<?php '
                                . '//Script Created Date :- ' . date("d-m-Y") . '
      
                                    $Vtiger_Utils_Log = true;
                                    include_once("vtlib/Vtiger/Menu.php");
                                    include_once("vtlib/Vtiger/Module.php");

                                    $module = Vtiger_Module::getInstance("' . $MODULENAME . '");
                                    $block = Vtiger_Block::getInstance("LBL_BASIC_INFORMATION", $module);

                                    $sequence_no = new Vtiger_Field();
                                    $sequence_no->name = "' . $moduleNameLowercase . '_no";
                                    $sequence_no->label = "LBL_' . $MODULENAME . '_NUMBER";
                                    $sequence_no->table = $module->basetable;
                                    $sequence_no->column = "' . $moduleNameLowercase . '_no";
                                    $sequence_no->columntype = "varchar(255)";
                                    $sequence_no->uitype = 4;
                                    $sequence_no->presence = 0;
                                    $sequence_no->typeofdata = "V~O";
                                    $sequence_no->summaryfield = 1;
                                    $block->addField($sequence_no); ?>';

                        $moduleSequenceNoScritpt_File = fopen('modules/' . $MODULENAME . '/sequence_no_script.php', "w") or die("Unable to open file!");
                        fwrite($moduleSequenceNoScritpt_File, $moduleSequenceNoScritpt_Content);
                        chmod('modules/' . $MODULENAME . '/sequence_no_script.php', 0777);
                        fclose($moduleSequenceNoScritpt_File);

                        /* Module Language File Creation */
                        $LanguageFileContent = '<?php $languageStrings = array("LBL_BASIC_INFORMATION"=>"Basic Information","SINGLE_' . $module_name . '"=>"' . $module_name . '","' . $label_name . '"=>"' . $label_name . '"); ?>';
                        $moduleLanguageFile = fopen('languages/en_us/' . $MODULENAME . '.php', "w") or die("Unable to open file!");
                        fwrite($moduleLanguageFile, $LanguageFileContent);
                        chmod('languages/en_us/' . $module_name . '.php', 0777);
                        fclose($moduleLanguageFile);

                        /* Module use_field table creation */
                        $createtbl_sql = 'CREATE TABLE IF NOT EXISTS vtiger_' . $moduleNameLowercase . '_user_field (
			recordid int(25) NOT NULL,
			userid int(25) NOT NULL,
			starred varchar(100) DEFAULT NULL,
			KEY fk_' . $moduleNameLowercase . 'id_vtiger_' . $moduleNameLowercase . '_user_field (recordid)
		      ) ENGINE=InnoDB DEFAULT CHARSET=utf8';
                        $adb->query($createtbl_sql);

                        $result1 = $adb->pquery('select * from vtiger_tab  order by tabid DESC   ', array());
                        if ($adb->num_rows($result1) > 0) {
                              $tab_id = $adb->query_result($result1, 0, 'tabid');
                        }
                        $inset_query = "INSERT INTO vtiger_app2tab (tabid, appname, sequence, visible) VALUES ($tab_id, '" . $app2tab . "', '1', '1')";
                        $adb->query($inset_query);

//                        $modtracker_tabs_qry = "INSERT INTO  `vtiger_modtracker_tabs` (`tabid` ,`visible`)VALUES ($tab_id,  '1');";
//                        $adb->query($modtracker_tabs_qry);
//


                        $relationId = $adb->getUniqueID('vtiger_relatedlists');
                        $modentity_num = $adb->getUniqueID('vtiger_modentity_num');

                        $DefaultQueryFileContent = " \n\n "
                                . "INSERT INTO `vtiger_relatedlists`(`relation_id`, `tabid`, `related_tabid`, `name`, `sequence`, `label`, `presence`, `actions`, `relationfieldid`, `source`, `relationtype`) SELECT " . $relationId . ", " . $tab_id . ", `related_tabid`, `name`, 1, `label`, `presence`, `actions`, `relationfieldid`, `source`, `relationtype` FROM `vtiger_relatedlists` WHERE `name` = 'get_comments' and label = 'ModComments' LIMIT 1;  \n\n"
                                . "UPDATE  `vtiger_relatedlists_seq` SET  `id` =" . $relationId . " WHERE 1; \n\n"
                                . "INSERT INTO  `vtiger_modtracker_tabs` (`tabid` ,`visible`)VALUES ('" . $tab_id . "',  '1'); \n\n "
                                . "INSERT INTO vtiger_modentity_num (num_id ,semodule ,prefix ,start_id ,cur_id ,active)VALUES ('" . $modentity_num . "', '" . $MODULENAME . "', '" . substr($MODULENAME, 0, 4) . "', '1', '1', '1'); \n\n"
                                . " UPDATE vtiger_modentity_num_seq SET id=" . $modentity_num . " WHERE 1; \n\n";
//                                . "UPDATE vtiger_field SET presence = '0', summaryfield = '1'  WHERE vtiger_field.columnname = '" . $moduleNameLowercase . "_no' and tabid = " . $tab_id . " and tablename = 'vtiger_" . $moduleNameLowercase . "'; \n\n";


                        $DefaultQueryFile = fopen('modules/' . $MODULENAME . '/' . $MODULENAME . '_queries.sql', "w") or die("Unable to open file!");

                        fwrite($DefaultQueryFile, $DefaultQueryFileContent);
                        chmod('modules/' . $MODULENAME . '/' . $MODULENAME . '_queries.sql', 0777);
                        fclose($DefaultQueryFile);

                        header('Location:index.php?module=ModuleBuilder&parent=Settings&view=Index&block=6&fieldid=41&module_status=1');
                  }
            }
            $viewer->view('success', $_REQUEST['module_status']);
            $viewer->view('ModuleCreation.tpl', $qualifiedModule);
      }

}
