<?php //Script Created Date :-27-03-2020
                        
                        $Vtiger_Utils_Log = true;
                        include_once("vtlib/Vtiger/Menu.php");
                        include_once("vtlib/Vtiger/Module.php");

                        $module = Vtiger_Module::getInstance("CustomReports");
                        $block = Vtiger_Block::getInstance("LBL_BASIC_INFORMATION", $module);

                        $child_contactid = new Vtiger_Field();
                        $child_contactid->name =  "child_contactid";
                        $child_contactid->label = "Child Contact Name";
                        $child_contactid->table =  $module->basetable ;
                        $child_contactid->column = "child_contactid";
                        $child_contactid->columntype = "varchar(255)";
                        $child_contactid->uitype = 10;
                        $child_contactid->typeofdata = "V~O";$block->addField($child_contactid);$child_contactid->setRelatedModules(Array("Contacts"));?>