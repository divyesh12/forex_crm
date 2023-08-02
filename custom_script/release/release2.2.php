<?php
chdir (dirname(__FILE__) . '/../..');
include_once('config.php');
require_once('include/logging.php');
require_once('data/Tracker.php');
include 'include/utils/utils.php';
include_once("vtlib/Vtiger/Menu.php");
include_once("vtlib/Vtiger/Module.php");
global $adb;

$Vtiger_Utils_Log = true;

### Dashboard widget script ###
$moduleName = 'Home';
$widgetType = "DASHBOARDWIDGET";
$widgetName = "Total Statistical Data";
$link = "index.php?module=$moduleName&view=ShowWidget&name=TotalPaymentData";
$module = Vtiger_Module::getInstance($moduleName);
if ($module) {
    $module->addLink($widgetType, $widgetName, $link);
}

### Date filter for pending ib commission report ###
$module1 = Vtiger_Module::getInstance("CustomReports");
$block1 = Vtiger_Block::getInstance("LBL_BASIC_INFORMATION", $module1);

$ibcomm_dateselection = new Vtiger_Field();
$ibcomm_dateselection->name =  "ibcomm_dateselection";
$ibcomm_dateselection->label = "Commission Date";
$ibcomm_dateselection->table =  $module1->basetable ;
$ibcomm_dateselection->column = "ibcomm_dateselection";
$ibcomm_dateselection->columntype = "date";
$ibcomm_dateselection->uitype = 5;
$ibcomm_dateselection->typeofdata = "D~O";
$block1->addField($ibcomm_dateselection);

### PIP Value field in Tradecommisison module ###
$securitySymbol = Vtiger_Module::getInstance('SecuritySymbol');
$securitySymbolBlock = Vtiger_Block::getInstance('LBL_BASIC_INFORMATION', $securitySymbol);

$pipValueInstance = new Vtiger_Field();
$pipValueInstance->name = 'pip_value';//LBL_BASIC_INFORMATION,SecuritySymbol
$pipValueInstance->table = 'vtiger_securitysymbol';
$pipValueInstance->column = 'pip_value';
$pipValueInstance->label = 'PIP';
$pipValueInstance->columntype = 'decimal(8,4)';
$pipValueInstance->uitype = 1;
$pipValueInstance->typeofdata = 'V~O';
$securitySymbolBlock->addField($pipValueInstance);

### Symbol field in IB Commisison profile item module ###
$ibProfileItem = Vtiger_Module::getInstance('IBCommissionProfileItems');
$ibProfileItemBlock = Vtiger_Block::getInstance('LBL_BASIC_INFORMATION', $ibProfileItem);

$symbolInstance = new Vtiger_Field();
$symbolInstance->name = 'symbol';//LBL_BASIC_INFORMATION,IBCommissionProfileItems
$symbolInstance->table = 'vtiger_ibcommissionprofileitems';
$symbolInstance->column = 'symbol';
$symbolInstance->label = 'Symbol';
$symbolInstance->columntype = 'varchar(25)';
$symbolInstance->uitype = 33;
$symbolInstance->typeofdata = 'V~O';
$symbolInstance->quickcreate = 2;
$symbolInstance->setPicklistValues(array('AUDCAD', 'EURCAD', 'EURGBP', 'EURJPY', 'CADCHF', 'USDJPY', 'AUDCAD', 'USDCHF', 'XAUUSD'));
$ibProfileItemBlock->addField($symbolInstance);

### country of issuance and document number field in document module ###
$documentModule = Vtiger_Module::getInstance('Documents');
$documentModuleBlock = Vtiger_Block::getInstance('LBL_NOTE_INFORMATION', $documentModule);

$countryOfIssuanceInstance = new Vtiger_Field();
$countryOfIssuanceInstance->name = 'country_of_issuance';//LBL_NOTE_INFORMATION,Documents
$countryOfIssuanceInstance->table = 'vtiger_notes';
$countryOfIssuanceInstance->column = 'country_of_issuance';
$countryOfIssuanceInstance->label = 'Country of Issuance';
$countryOfIssuanceInstance->columntype = 'varchar(15)';
$countryOfIssuanceInstance->uitype = 1;
$countryOfIssuanceInstance->typeofdata = 'V~O';
$countryOfIssuanceInstance->quickcreate = 2;
$documentModuleBlock->addField($countryOfIssuanceInstance);

$docNumberInstance = new Vtiger_Field();
$docNumberInstance->name = 'document_number';//LBL_NOTE_INFORMATION,Documents
$docNumberInstance->table = 'vtiger_notes';
$docNumberInstance->column = 'document_number';
$docNumberInstance->label = 'Document Number';
$docNumberInstance->columntype = 'varchar(50)';
$docNumberInstance->uitype = 1;
$docNumberInstance->typeofdata = 'V~O';
$docNumberInstance->quickcreate = 2;
$documentModuleBlock->addField($docNumberInstance);

### KYC form status field in contact module ###
$contactModule = Vtiger_Module::getInstance('Contacts');
$contactModuleBlock = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $contactModule);

$kycFormStatusInstance = new Vtiger_Field();
$kycFormStatusInstance->name = 'kyc_form_status';//LBL_CUSTOM_INFORMATION,Contacts
$kycFormStatusInstance->table = 'vtiger_contactdetails';
$kycFormStatusInstance->column = 'kyc_form_status';
$kycFormStatusInstance->label = 'KYC Form Status';
$kycFormStatusInstance->columntype = 'varchar(25)';
$kycFormStatusInstance->uitype = 15;
$kycFormStatusInstance->typeofdata = 'V~O';
$kycFormStatusInstance->setPicklistValues(array('Pending', 'Sent for approval', 'Approved', 'Sent for edit', 'Allow for edit'));
$contactModuleBlock->addField($kycFormStatusInstance);

### Withdrawal allow field in contact module ###
$contactModuleCustomBlock = Vtiger_Block::getInstance('LBL_CONTACT_INFORMATION', $contactModule);
$withdrawalAllowInstance = new Vtiger_Field();
$withdrawalAllowInstance->name = 'withdraw_allow';//LBL_CONTACT_INFORMATION,Contacts
$withdrawalAllowInstance->table = 'vtiger_contactdetails';
$withdrawalAllowInstance->column = 'withdraw_allow';
$withdrawalAllowInstance->label = 'Withdraw Allow';
$withdrawalAllowInstance->columntype = 'tinyint(1)';
$withdrawalAllowInstance->uitype = 56;
$withdrawalAllowInstance->defaultvalue = 1;
$withdrawalAllowInstance->typeofdata = 'C~O';
$contactModuleCustomBlock->addField($withdrawalAllowInstance);

### Approved by field in payment module ###
$paymentModule = Vtiger_Module::getInstance('Payments');
$paymentModuleBlock = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $paymentModule);

$approvedUsernameInstance = new Vtiger_Field();
$approvedUsernameInstance->name = 'approved_by';//LBL_CUSTOM_INFORMATION,Payments
$approvedUsernameInstance->table = 'vtiger_payments';
$approvedUsernameInstance->column = 'approved_by';
$approvedUsernameInstance->label = 'LBL_APPROVED_BY';
$approvedUsernameInstance->columntype = 'varchar(50)';
$approvedUsernameInstance->uitype = 1;
$approvedUsernameInstance->typeofdata = 'V~O';
$paymentModuleBlock->addField($approvedUsernameInstance);

### Last login ip in contact module ###
$loginIPBlock = Vtiger_Block::getInstance('LBL_CONTACT_INFORMATION', $contactModule);

$lastLoginIpInstance = new Vtiger_Field();
$lastLoginIpInstance->name = 'login_ip_address';//LBL_CONTACT_INFORMATION,Contacts
$lastLoginIpInstance->table = 'vtiger_contactdetails';
$lastLoginIpInstance->column = 'login_ip_address';
$lastLoginIpInstance->label = 'Last Login IP';
$lastLoginIpInstance->columntype = 'varchar(25)';
$lastLoginIpInstance->uitype = 1;
$lastLoginIpInstance->typeofdata = 'V~O';
$loginIPBlock->addField($lastLoginIpInstance);

$tradeCommModule = Vtiger_Module::getInstance('TradesCommission');
$tradeCommBlock = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $tradeCommModule);

$pipValueInstance = new Vtiger_Field();
$pipValueInstance->name = 'pip';//LBL_CUSTOM_INFORMATION,Contacts
$pipValueInstance->table = 'vtiger_tradescommission';
$pipValueInstance->column = 'pip';
$pipValueInstance->label = 'PIP';
$pipValueInstance->columntype = 'decimal(10,4)';
$pipValueInstance->uitype = 1;
$pipValueInstance->summaryfield = 1;
$pipValueInstance->typeofdata = 'V~O';
$tradeCommBlock->addField($pipValueInstance);

### Document approve auto custom workflow ###
require 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
$emm = new VTEntityMethodManager($adb);
$emm->addEntityMethod("Payments", "DocumentApprovedOnApproveOfStatus", "modules/Payments/handlers/DocumentApprovedOnApproveOfStatus.php", "Payments_DocumentApprovedOnApproveOfStatus");


$pammLoginSql = "INSERT INTO `vtiger_module_configuration_editor` (`id`, `tabid`, `module`, `fieldlabel`, `fieldsuggestion`, `fieldname`, `fieldtype`, `fieldvalue`, `sequence`, `presence`) VALUES (NULL, '78', 'Social Trading', 'LBL_PAMM_LOGIN_LIINK', 'INFO_PAMM_LOGIN_LIINK', 'pamm_login_link', 'textbox~O', '', '5', '0');";
$adb->pquery($pammLoginSql);

$pammRegisterSql = "INSERT INTO `vtiger_module_configuration_editor` (`id`, `tabid`, `module`, `fieldlabel`, `fieldsuggestion`, `fieldname`, `fieldtype`, `fieldvalue`, `sequence`, `presence`) VALUES (NULL, '78', 'Social Trading', 'LBL_PAMM_REGISTER_LIINK', 'INFO_PAMM_REGISTER_LIINK', 'pamm_register_link', 'textbox~O', '', '6', '0');";
$adb->pquery($pammRegisterSql);

// $appActiveSql = "INSERT INTO `vtiger_module_configuration_editor` (`id`, `tabid`, `module`, `fieldlabel`, `fieldsuggestion`, `fieldname`, `fieldtype`, `fieldvalue`, `sequence`, `presence`) 
// VALUES (NULL, '73', 'Mobile', 'LBL_IS_CRM_MOB_ACTIVE', 'INFO_IS_CRM_MOB_ACTIVE', 'is_crm_app_active', 'picklist', 'false', '6', '0');";
// $adb->pquery($appActiveSql);

$mt4Link1 = "INSERT INTO vtiger_module_configuration_editor (id, tabid, module, fieldlabel, fieldsuggestion, fieldname, fieldtype, fieldvalue, sequence, presence) VALUES (NULL, '79', 'Quick Links', 'MT4 Windows Link', '', 'mt4_windows_link', 'textbox~O', '', '1', '0');";
$adb->pquery($mt4Link1);
$mt4Link2 = "INSERT INTO vtiger_module_configuration_editor (id, tabid, module, fieldlabel, fieldsuggestion, fieldname, fieldtype, fieldvalue, sequence, presence) VALUES (NULL, '79', 'Quick Links', 'MT4 iOs Link', '', 'mt4_ios_link', 'textbox~O', '', '1', '0');";
$adb->pquery($mt4Link2);
$mt4Link3 = "INSERT INTO vtiger_module_configuration_editor (id, tabid, module, fieldlabel, fieldsuggestion, fieldname, fieldtype, fieldvalue, sequence, presence) VALUES (NULL, '79', 'Quick Links', 'MT4 Android Link', '', 'mt4_android_link', 'textbox~O', '', '1', '0');";
$adb->pquery($mt4Link3);

$mt5Link1 = "INSERT INTO vtiger_module_configuration_editor (id, tabid, module, fieldlabel, fieldsuggestion, fieldname, fieldtype, fieldvalue, sequence, presence) VALUES (NULL, '79', 'Quick Links', 'MT5 Windows Link', '', 'mt5_windows_link', 'textbox~O', '', '1', '0');";
$adb->pquery($mt5Link1);
$mt5Link2 = "INSERT INTO vtiger_module_configuration_editor (id, tabid, module, fieldlabel, fieldsuggestion, fieldname, fieldtype, fieldvalue, sequence, presence) VALUES (NULL, '79', 'Quick Links', 'MT5 iOs Link', '', 'mt5_ios_link', 'textbox~O', '', '1', '0');";
$adb->pquery($mt5Link2);
$mt5Link3 = "INSERT INTO vtiger_module_configuration_editor (id, tabid, module, fieldlabel, fieldsuggestion, fieldname, fieldtype, fieldvalue, sequence, presence) VALUES (NULL, '79', 'Quick Links', 'MT5 Android Link', '', 'mt5_android_link', 'textbox~O', '', '1', '0');";
$adb->pquery($mt5Link3);

$vertexLink1 = "INSERT INTO vtiger_module_configuration_editor (id, tabid, module, fieldlabel, fieldsuggestion, fieldname, fieldtype, fieldvalue, sequence, presence) VALUES (NULL, '79', 'Quick Links', 'Vertex Windows Link', '', 'vertex_windows_link', 'textbox~O', '', '1', '0');";
$adb->pquery($vertexLink1);
$vertexLink2 = "INSERT INTO vtiger_module_configuration_editor (id, tabid, module, fieldlabel, fieldsuggestion, fieldname, fieldtype, fieldvalue, sequence, presence) VALUES (NULL, '79', 'Quick Links', 'Vertex iOs Link', '', 'vertex_ios_link', 'textbox~O', '', '1', '0');";
$adb->pquery($vertexLink2);
$vertexLink3 = "INSERT INTO vtiger_module_configuration_editor (id, tabid, module, fieldlabel, fieldsuggestion, fieldname, fieldtype, fieldvalue, sequence, presence) VALUES (NULL, '79', 'Quick Links', 'Vertex Android Link', '', 'vertex_android_link', 'textbox~O', '', '1', '0');";
$adb->pquery($vertexLink3);

$commQuery = "ALTER TABLE `tradescommission` ADD `pip` decimal(10,4) NOT NULL DEFAULT '0' AFTER `commission_value`;";
$adb->pquery($commQuery);

$kycModuleQuery = "INSERT INTO `vtiger_module_configuration_editor` (`id`, `tabid`, `module`, `fieldlabel`, `fieldsuggestion`, `fieldname`, `fieldtype`, `fieldvalue`, `sequence`, `presence`) 
VALUES (NULL, '71', 'KYC', 'LBL_IS_KYC_QUESTIONNARIE_ENABLE', 'INFO_IS_KYC_QUESTIONNARIE_ENABLE', 'is_kyc_questionnarie_enable', 'picklist', 'false', '8', '0');";
$adb->pquery($kycModuleQuery);

$withdAllowQuery = "UPDATE vtiger_contactdetails SET withdraw_allow = 1;";
$adb->pquery($withdAllowQuery);

$withdAllowQuery1 = "UPDATE `vtiger_field` SET `summaryfield` = '1' WHERE `tabid` = '53' AND (`columnname` = 'transaction_id' OR `columnname` = 'createdtime' OR `columnname` = 'modifiedtime');";
$adb->pquery($withdAllowQuery1);

$withdAllowQuery2 = "UPDATE `vtiger_field` SET `headerfield` = '0' WHERE `tabid` = '53' AND `columnname` = 'order_id';";
$adb->pquery($withdAllowQuery2);

echo 'CRM: Database updated as per Release 2.2 changes <br/>';