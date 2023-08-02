<?php //Script Created Date :- 05-02-2020
      
                                    $Vtiger_Utils_Log = true;
                                    include_once("vtlib/Vtiger/Menu.php");
                                    include_once("vtlib/Vtiger/Module.php");

                                    $module = Vtiger_Module::getInstance("SecuritySymbol");
                                    $block = Vtiger_Block::getInstance("LBL_BASIC_INFORMATION", $module);

                                    $sequence_no = new Vtiger_Field();
                                    $sequence_no->name = "securitysymbol_no";
                                    $sequence_no->label = "LBL_SecuritySymbol_NUMBER";
                                    $sequence_no->table = $module->basetable;
                                    $sequence_no->column = "securitysymbol_no";
                                    $sequence_no->columntype = "varchar(255)";
                                    $sequence_no->uitype = 4;
                                    $sequence_no->presence = 0;
                                    $sequence_no->typeofdata = "V~O";
                                    $sequence_no->summaryfield = 1;
                                    $block->addField($sequence_no); ?>