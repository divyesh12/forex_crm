<?php //Script Created Date :-31-05-2023
                        
                        $Vtiger_Utils_Log = true;
                        include_once("vtlib/Vtiger/Menu.php");
                        include_once("vtlib/Vtiger/Module.php");

                        $module = Vtiger_Module::getInstance("CustomReports");
                        $block = Vtiger_Block::getInstance("LBL_BASIC_INFORMATION", $module);

                        $ibcomm_dateselection = new Vtiger_Field();
                        $ibcomm_dateselection->name =  "ibcomm_dateselection";
                        $ibcomm_dateselection->label = "Commission Date";
                        $ibcomm_dateselection->table =  $module->basetable ;
                        $ibcomm_dateselection->column = "ibcomm_dateselection";
                        $ibcomm_dateselection->columntype = "date";
                        $ibcomm_dateselection->uitype = 5;
                        $ibcomm_dateselection->typeofdata = "D~O";$block->addField($ibcomm_dateselection);?>