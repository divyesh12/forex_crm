<?php //Script Created Date :-28-05-2023
                        
                        $Vtiger_Utils_Log = true;
                        include_once("vtlib/Vtiger/Menu.php");
                        include_once("vtlib/Vtiger/Module.php");

                        $module = Vtiger_Module::getInstance("CustomReports");
                        $block = Vtiger_Block::getInstance("LBL_BASIC_INFORMATION", $module);

                        $cont_lastname = new Vtiger_Field();
                        $cont_lastname->name =  "cont_lastname";
                        $cont_lastname->label = "Last Name";
                        $cont_lastname->table =  $module->basetable ;
                        $cont_lastname->column = "cont_lastname";
                        $cont_lastname->columntype = "varchar(255)";
                        $cont_lastname->uitype = 1;
                        $cont_lastname->typeofdata = "V~O";$block->addField($cont_lastname);?>