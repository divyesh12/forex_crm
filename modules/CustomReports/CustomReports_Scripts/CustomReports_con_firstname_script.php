<?php //Script Created Date :-28-05-2023
                        
                        $Vtiger_Utils_Log = true;
                        include_once("vtlib/Vtiger/Menu.php");
                        include_once("vtlib/Vtiger/Module.php");

                        $module = Vtiger_Module::getInstance("CustomReports");
                        $block = Vtiger_Block::getInstance("LBL_BASIC_INFORMATION", $module);

                        $con_firstname = new Vtiger_Field();
                        $con_firstname->name =  "con_firstname";
                        $con_firstname->label = "First Name";
                        $con_firstname->table =  $module->basetable ;
                        $con_firstname->column = "con_firstname";
                        $con_firstname->columntype = "varchar(255)";
                        $con_firstname->uitype = 1;
                        $con_firstname->typeofdata = "V~O";$block->addField($con_firstname);?>