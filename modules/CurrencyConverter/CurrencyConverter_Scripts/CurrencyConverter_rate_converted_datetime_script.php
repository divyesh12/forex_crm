<?php //Script Created Date :-07-10-2022
                        
                        $Vtiger_Utils_Log = true;
                        include_once("vtlib/Vtiger/Menu.php");
                        include_once("vtlib/Vtiger/Module.php");

                        $module = Vtiger_Module::getInstance("CurrencyConverter");
                        $block = Vtiger_Block::getInstance("LBL_BASIC_INFORMATION", $module);

                        $rate_converted_datetime = new Vtiger_Field();
                        $rate_converted_datetime->name =  "rate_converted_datetime";
                        $rate_converted_datetime->label = "Rate Converted Date Time";
                        $rate_converted_datetime->table =  $module->basetable ;
                        $rate_converted_datetime->column = "rate_converted_datetime";
                        $rate_converted_datetime->columntype = "date";
                        $rate_converted_datetime->uitype = 5;
                        $rate_converted_datetime->typeofdata = "D~O";$block->addField($rate_converted_datetime);?>