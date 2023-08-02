<?php //Script Created Date :-27-03-2020
                        
                        $Vtiger_Utils_Log = true;
                        include_once("vtlib/Vtiger/Menu.php");
                        include_once("vtlib/Vtiger/Module.php");

                        $module = Vtiger_Module::getInstance("CustomReports");
                        $block = Vtiger_Block::getInstance("LBL_BASIC_INFORMATION", $module);

                        $contactid = new Vtiger_Field();
                        $contactid->name =  "contactid";
                        $contactid->label = "Contact Name";
                        $contactid->table =  $module->basetable ;
                        $contactid->column = "contactid";
                        $contactid->columntype = "varchar(255)";
                        $contactid->uitype = 10;
                        $contactid->typeofdata = "V~O";$block->addField($contactid);$contactid->setRelatedModules(Array("Contacts"));?>