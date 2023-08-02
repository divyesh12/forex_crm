<?php //Script Created Date :-27-03-2020
                        
                        $Vtiger_Utils_Log = true;
                        include_once("vtlib/Vtiger/Menu.php");
                        include_once("vtlib/Vtiger/Module.php");

                        $module = Vtiger_Module::getInstance("CustomReports");
                        $block = Vtiger_Block::getInstance("LBL_BASIC_INFORMATION", $module);

                        $is_hide_zero_commission = new Vtiger_Field();
                        $is_hide_zero_commission->name =  "is_hide_zero_commission";
                        $is_hide_zero_commission->label = "Hide Zero Commission";
                        $is_hide_zero_commission->table =  $module->basetable ;
                        $is_hide_zero_commission->column = "is_hide_zero_commission";
                        $is_hide_zero_commission->columntype = "varchar(2)";
                        $is_hide_zero_commission->uitype = 56;
                        $is_hide_zero_commission->typeofdata = "C~O";$block->addField($is_hide_zero_commission);?>