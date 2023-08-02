<?php //Script Created Date :-27-03-2020
                        
                        $Vtiger_Utils_Log = true;
                        include_once("vtlib/Vtiger/Menu.php");
                        include_once("vtlib/Vtiger/Module.php");

                        $module = Vtiger_Module::getInstance("CustomReports");
                        $block = Vtiger_Block::getInstance("LBL_BASIC_INFORMATION", $module);

                        $is_include_child_contactid = new Vtiger_Field();
                        $is_include_child_contactid->name =  "is_include_child_contactid";
                        $is_include_child_contactid->label = "Is Include Child Contact";
                        $is_include_child_contactid->table =  $module->basetable ;
                        $is_include_child_contactid->column = "is_include_child_contactid";
                        $is_include_child_contactid->columntype = "varchar(2)";
                        $is_include_child_contactid->uitype = 56;
                        $is_include_child_contactid->typeofdata = "C~O";$block->addField($is_include_child_contactid);?>