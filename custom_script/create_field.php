<?php
chdir (dirname(__FILE__) . '/..');
//http://vtiger-crm.2324883.n4.nabble.com/UITYPE-amp-TYPEOFDATA-map-for-vtiger-5-x-td4820.html
//https://wiki.vtiger.com/index.php/UI_Types
//https://wiki.vtiger.com/index.php/TypeOfData
// Turn on debugging level
$Vtiger_Utils_Log = true;

// Include necessary classes
include_once('vtlib/Vtiger/Module.php');
// Define instances
$module = Vtiger_Module::getInstance('TradesCommission');
$block = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $module);

$brokerageCommInstance = new Vtiger_Field();
$brokerageCommInstance->name = 'brokerage_commission';//LBL_CUSTOM_INFORMATION,TradesCommission
$brokerageCommInstance->table = 'vtiger_tradescommission';
$brokerageCommInstance->column = 'brokerage_commission';
$brokerageCommInstance->label = 'Brokerage Commission';
$brokerageCommInstance->columntype = 'double';
$brokerageCommInstance->uitype = 1;
$brokerageCommInstance->typeofdata = 'V~O';
$brokerageCommInstance->summaryfield = 1;
$block->addField($brokerageCommInstance);

// $kycEditInstance = new Vtiger_Field();
// $kycEditInstance->name = 'kyc_form_edit';//LBL_CONTACT_INFORMATION,Contacts
// $kycEditInstance->table = 'vtiger_contactdetails';
// $kycEditInstance->column = 'kyc_form_edit';
// $kycEditInstance->label = 'KYC Form Edit';
// $kycEditInstance->columntype = 'tinyint(1)';
// $kycEditInstance->uitype = 56;
// $kycEditInstance->defaultvalue = 0;
// $kycEditInstance->typeofdata = 'C~O';
// $block->addField($kycEditInstance);

// $kycFormStatusInstance = new Vtiger_Field();
// $kycFormStatusInstance->name = 'kyc_form_status';//LBL_CUSTOM_INFORMATION,Contacts
// $kycFormStatusInstance->table = 'vtiger_contactdetails';
// $kycFormStatusInstance->column = 'kyc_form_status';
// $kycFormStatusInstance->label = 'KYC Form Status';
// $kycFormStatusInstance->columntype = 'varchar(25)';
// $kycFormStatusInstance->uitype = 15;
// $kycFormStatusInstance->typeofdata = 'V~O';
// $kycFormStatusInstance->setPicklistValues(array('Pending', 'Sent for approval', 'Approved', 'Sent for edit', 'Allow for edit'));
// $block->addField($kycFormStatusInstance);

// $con_firstname = new Vtiger_Field();//LBL_BASIC_INFORMATION,CustomReports
// $con_firstname->name =  "con_firstname";
// $con_firstname->label = "First Name";
// $con_firstname->table =  $module->basetable ;
// $con_firstname->column = "con_firstname";
// $con_firstname->columntype = "varchar(255)";
// $con_firstname->uitype = 1;
// $con_firstname->typeofdata = "V~O";
// $block->addField($con_firstname);

// $cont_lastname = new Vtiger_Field();
// $cont_lastname->name =  "cont_lastname";
// $cont_lastname->label = "Last Name";
// $cont_lastname->table =  $module->basetable ;
// $cont_lastname->column = "cont_lastname";
// $cont_lastname->columntype = "varchar(255)";
// $cont_lastname->uitype = 1;
// $cont_lastname->typeofdata = "V~O";
// $block->addField($cont_lastname);

// $pipValueInstance = new Vtiger_Field();
// $pipValueInstance->name = 'pip';//LBL_CUSTOM_INFORMATION,Contacts
// $pipValueInstance->table = 'vtiger_tradescommission';
// $pipValueInstance->column = 'pip';
// $pipValueInstance->label = 'PIP';
// $pipValueInstance->columntype = 'decimal(10,4)';
// $pipValueInstance->uitype = 1;
// $pipValueInstance->summaryfield = 1;
// $pipValueInstance->typeofdata = 'V~O';
// $block->addField($pipValueInstance);
// $invoice = Vtiger_Module::getInstance('Payments');
// $block = Vtiger_Block::getInstance('LBL_CUSTOM_INFORMATION', $invoice);

// $approvedUsernameInstance = new Vtiger_Field();
// $approvedUsernameInstance->name = 'approved_by';//LBL_CUSTOM_INFORMATION,Payments
// $approvedUsernameInstance->table = 'vtiger_payments';
// $approvedUsernameInstance->column = 'approved_by';
// $approvedUsernameInstance->label = 'LBL_APPROVED_BY';
// $approvedUsernameInstance->columntype = 'varchar(50)';
// $approvedUsernameInstance->uitype = 1;
// $approvedUsernameInstance->typeofdata = 'V~O';
// $block->addField($approvedUsernameInstance);

// $withdrawalAllowInstance = new Vtiger_Field();
// $withdrawalAllowInstance->name = 'withdraw_allow';//LBL_CONTACT_INFORMATION,Contacts
// $withdrawalAllowInstance->table = 'vtiger_contactdetails';
// $withdrawalAllowInstance->column = 'withdraw_allow';
// $withdrawalAllowInstance->label = 'Withdraw Allow';
// $withdrawalAllowInstance->columntype = 'tinyint(1)';
// $withdrawalAllowInstance->uitype = 56;
// $withdrawalAllowInstance->defaultvalue = 1;
// $withdrawalAllowInstance->typeofdata = 'C~O';
// $block->addField($withdrawalAllowInstance);

// $countryOfIssuanceInstance = new Vtiger_Field();
// $countryOfIssuanceInstance->name = 'country_of_issuance';//LBL_NOTE_INFORMATION,Documents
// $countryOfIssuanceInstance->table = 'vtiger_notes';
// $countryOfIssuanceInstance->column = 'country_of_issuance';
// $countryOfIssuanceInstance->label = 'Country of Issuance';
// $countryOfIssuanceInstance->columntype = 'varchar(15)';
// $countryOfIssuanceInstance->uitype = 1;
// $countryOfIssuanceInstance->typeofdata = 'V~O';
// $countryOfIssuanceInstance->quickcreate = 2;
// $block->addField($countryOfIssuanceInstance);

// $docNumberInstance = new Vtiger_Field();
// $docNumberInstance->name = 'document_number';//LBL_NOTE_INFORMATION,Documents
// $docNumberInstance->table = 'vtiger_notes';
// $docNumberInstance->column = 'document_number';
// $docNumberInstance->label = 'Document Number';
// $docNumberInstance->columntype = 'varchar(50)';
// $docNumberInstance->uitype = 1;
// $docNumberInstance->typeofdata = 'V~O';
// $docNumberInstance->quickcreate = 2;
// $block->addField($docNumberInstance);



// $symbolInstance = new Vtiger_Field();
// $symbolInstance->name = 'symbol';//LBL_BASIC_INFORMATION,IBCommissionProfileItems
// $symbolInstance->table = 'vtiger_ibcommissionprofileitems';
// $symbolInstance->column = 'symbol';
// $symbolInstance->label = 'Symbol';
// $symbolInstance->columntype = 'varchar(25)';
// $symbolInstance->uitype = 33;
// $symbolInstance->typeofdata = 'V~O';
// $symbolInstance->quickcreate = 2;
// $symbolInstance->setPicklistValues(array('AUDCAD', 'EURCAD', 'EURGBP', 'EURJPY', 'CADCHF', 'USDJPY', 'AUDCAD', 'USDCHF', 'XAUUSD'));
// $block->addField($symbolInstance);

// $pipValueInstance = new Vtiger_Field();
// $pipValueInstance->name = 'pip_value';//LBL_BASIC_INFORMATION,SecuritySymbol
// $pipValueInstance->table = 'vtiger_securitysymbol';
// $pipValueInstance->column = 'pip_value';
// $pipValueInstance->label = 'PIP';
// $pipValueInstance->columntype = 'decimal(8,4)';
// $pipValueInstance->uitype = 1;
// $pipValueInstance->typeofdata = 'V~O';
// $block->addField($pipValueInstance);

//$lastLoginIpInstance = new Vtiger_Field();
//$lastLoginIpInstance->name = 'login_ip_address';//LBL_CONTACT_INFORMATION,Contacts
//$lastLoginIpInstance->table = 'vtiger_contactdetails';
//$lastLoginIpInstance->column = 'login_ip_address';
//$lastLoginIpInstance->label = 'Last Login IP';
//$lastLoginIpInstance->columntype = 'varchar(25)';
//$lastLoginIpInstance->uitype = 1;
//$lastLoginIpInstance->typeofdata = 'V~O';
//$block->addField($lastLoginIpInstance);

//$transactionIpInstance = new Vtiger_Field();
//$transactionIpInstance->name = 'ip_address';//LBL_CUSTOM_INFORMATION,Payments
//$transactionIpInstance->table = 'vtiger_payments';
//$transactionIpInstance->column = 'ip_address';
//$transactionIpInstance->label = 'IP Address';
//$transactionIpInstance->columntype = 'varchar(25)';
//$transactionIpInstance->uitype = 1;
//$transactionIpInstance->typeofdata = 'V~O';
//$block->addField($transactionIpInstance);

//$parentAffiliateInstance = new Vtiger_Field();
//$parentAffiliateInstance->name = 'parent_affiliate_code';//LBL_LEAD_INFORMATION,Leads
//$parentAffiliateInstance->table = 'vtiger_leaddetails';
//$parentAffiliateInstance->column = 'parent_affiliate_code';
//$parentAffiliateInstance->label = 'Parent Affiliate Code';
//$parentAffiliateInstance->columntype = 'varchar(255)';
//$parentAffiliateInstance->uitype = 1;
//$parentAffiliateInstance->typeofdata = 'V~O';
//$block->addField($parentAffiliateInstance);

//$demoAccUsernameInstance = new Vtiger_Field();
//$demoAccUsernameInstance->name = 'username';//LBL_BASIC_INFORMATION,DemoAccount
//$demoAccUsernameInstance->table = 'vtiger_demoaccount';
//$demoAccUsernameInstance->column = 'username';
//$demoAccUsernameInstance->label = 'LBL_ACCOUNT_USERNAME';
//$demoAccUsernameInstance->columntype = 'varchar(50)';
//$demoAccUsernameInstance->uitype = 1;
//$demoAccUsernameInstance->typeofdata = 'V~O';
//$block->addField($demoAccUsernameInstance);

//$liveAccUsernameInstance = new Vtiger_Field();
//$liveAccUsernameInstance->name = 'username';//LBL_BASIC_INFORMATION,LiveAccount
//$liveAccUsernameInstance->table = 'vtiger_liveaccount';
//$liveAccUsernameInstance->column = 'username';
//$liveAccUsernameInstance->label = 'LBL_ACCOUNT_USERNAME';
//$liveAccUsernameInstance->columntype = 'varchar(50)';
//$liveAccUsernameInstance->uitype = 1;
//$liveAccUsernameInstance->typeofdata = 'V~O';
//$block->addField($liveAccUsernameInstance);

// $resourceReferenceContact = new Vtiger_Field();
// $resourceReferenceContact->name = 'resource_reference';//LBL_CONTACT_INFORMATION,Contacts
// $resourceReferenceContact->table = 'vtiger_contactdetails';
// $resourceReferenceContact->column = 'resource_reference';
// $resourceReferenceContact->label = 'LBL_WHERE_DID_THEY_FIND_US';
// $resourceReferenceContact->columntype = 'varchar(200)';
// $resourceReferenceContact->uitype = 15;
// $resourceReferenceContact->typeofdata = 'V~O';
// $block->addField($resourceReferenceContact);


// $resourceReference = new Vtiger_Field();
// $resourceReference->name = 'resource_reference';//LBL_LEAD_INFORMATION,Leads
// $resourceReference->table = 'vtiger_leaddetails';
// $resourceReference->column = 'resource_reference';
// $resourceReference->label = 'LBL_WHERE_DID_THEY_FIND_US';
// $resourceReference->columntype = 'varchar(200)';
// $resourceReference->uitype = 15;
// $resourceReference->typeofdata = 'V~O';
// $resourceReference->setPicklistValues(array('Social Media', 'Peer Referral', 'Google/Search Engine', 'Third-Party', 'Review', 'Other'));
// $block->addField($resourceReference);

// $isAgreePaymentInstance = new Vtiger_Field();
// $isAgreePaymentInstance->name = 'is_agree_payment_term';//LBL_BASIC_INFORMATION,Payments
// $isAgreePaymentInstance->table = 'vtiger_payments';
// $isAgreePaymentInstance->column = 'is_agree_payment_term';
// $isAgreePaymentInstance->label = 'Is Agree';
// $isAgreePaymentInstance->columntype = 'tinyint(1)';
// $isAgreePaymentInstance->uitype = 56;
// $isAgreePaymentInstance->typeofdata = 'C~O';
// $block->addField($isAgreePaymentInstance);

//$isAgreePaymentInstance = new Vtiger_Field();
//$isAgreePaymentInstance->name = 'is_agree_payment_term';//LBL_BASIC_INFORMATION,Payments
//$isAgreePaymentInstance->table = 'vtiger_payments';
//$isAgreePaymentInstance->column = 'is_agree_payment_term';
//$isAgreePaymentInstance->label = 'Is Agree';
//$isAgreePaymentInstance->columntype = 'tinyint(1)';
//$isAgreePaymentInstance->uitype = 56;
//$isAgreePaymentInstance->typeofdata = 'C~O';
//$block->addField($isAgreePaymentInstance);

//$contactIdInstance = new Vtiger_Field();
//$contactIdInstance->name = 'min_lots_for_withdraw';//LBL_BASIC_INFORMATION,AccountMapping
//$contactIdInstance->table = 'vtiger_accountmapping';
//$contactIdInstance->column = 'min_lots_for_withdraw';
//$contactIdInstance->label = 'Minimum Withdrawal Volume';
//$contactIdInstance->columntype = 'decimal(20,8)';
//$contactIdInstance->uitype = 1;
//$contactIdInstance->typeofdata = 'V~O';
//$block->addField($contactIdInstance);

//$contactIdInstance = new Vtiger_Field();
//$contactIdInstance->name = 'reference_id';//LBL_CONTACT_INFORMATION,Contacts
//$contactIdInstance->table = 'vtiger_contactdetails';
//$contactIdInstance->column = 'reference_id';
//$contactIdInstance->label = 'Reference ID';
//$contactIdInstance->columntype = 'int(11)';
//$contactIdInstance->uitype = 1;
//$contactIdInstance->typeofdata = 'V~O';
//$block->addField($contactIdInstance);

//$contactIdInstance = new Vtiger_Field();
//$contactIdInstance->name = 'vertex_client_id';//LBL_CONTACT_INFORMATION,Contacts
//$contactIdInstance->table = 'vtiger_contactdetails';
//$contactIdInstance->column = 'vertex_client_id';
//$contactIdInstance->label = 'Vertex Client ID';
//$contactIdInstance->columntype = 'int(11)';
//$contactIdInstance->uitype = 1;
//$contactIdInstance->typeofdata = 'V~O';
//$block->addField($contactIdInstance);

//$isAgreePaymentInstance = new Vtiger_Field();
//$isAgreePaymentInstance->name = 'is_agree_payment_term';//LBL_CONTACT_INFORMATION,Contacts
//$isAgreePaymentInstance->table = 'vtiger_payments';
//$isAgreePaymentInstance->column = 'is_agree_payment_term';
//$isAgreePaymentInstance->label = 'LBL_IS_AGREE_PAYMENT_TERM';
//$isAgreePaymentInstance->columntype = 'tinyint(1)';
//$isAgreePaymentInstance->uitype = 56;
//$isAgreePaymentInstance->typeofdata = 'C~O';
//$block->addField($isAgreePaymentInstance);

//$isAgreeInstance = new Vtiger_Field();
//$isAgreeInstance->name = 'is_agree';//LBL_CONTACT_INFORMATION,Contacts
//$isAgreeInstance->table = 'vtiger_contactdetails';
//$isAgreeInstance->column = 'is_agree';
//$isAgreeInstance->label = 'Is Agree';
//$isAgreeInstance->columntype = 'tinyint(1)';
//$isAgreeInstance->uitype = 56;
//$isAgreeInstance->typeofdata = 'C~O';
//$block->addField($isAgreeInstance);
//
//$agreeIpInstance = new Vtiger_Field();
//$agreeIpInstance->name = 'agree_ip';//LBL_CONTACT_INFORMATION,Contacts
//$agreeIpInstance->table = 'vtiger_contactdetails';
//$agreeIpInstance->column = 'agree_ip';
//$agreeIpInstance->label = 'Agree IP';
//$agreeIpInstance->columntype = 'varchar(25)';
//$agreeIpInstance->uitype = 1;
//$agreeIpInstance->typeofdata = 'V~O';
//$block->addField($agreeIpInstance);


//$operationTypeInstance = new Vtiger_Field();
//$operationTypeInstance->name = 'operation_type';//LBL_BASIC_INFORMATION,CurrencyConverter
//$operationTypeInstance->table = 'vtiger_customreports';
//$operationTypeInstance->column = 'operation_type';
//$operationTypeInstance->label = 'Operation Type';
//$operationTypeInstance->columntype = 'varchar(15)';
//$operationTypeInstance->uitype = 15;
//$operationTypeInstance->typeofdata = 'V~O';
//$operationTypeInstance->setPicklistValues(array('Deposit', 'Withdrawal'));
//$block->addField($operationTypeInstance);

//$commissionTypeInstance = new Vtiger_Field();
//$commissionTypeInstance->name = 'custom_commission_type';//LBL_BASIC_INFORMATION,CustomReports
//$commissionTypeInstance->table = 'vtiger_currencyconverter';
//$commissionTypeInstance->column = 'custom_commission_type';
//$commissionTypeInstance->label = 'Commission Type';
//$commissionTypeInstance->columntype = 'varchar(15)';
//$commissionTypeInstance->uitype = 15;
//$commissionTypeInstance->typeofdata = 'V~O';
//$commissionTypeInstance->setPicklistValues(array('Trade', 'Deposit', 'Master IB'));
//$block->addField($commissionTypeInstance);

//$serverTypeInstance = new Vtiger_Field();
//$serverTypeInstance->name = 'server_type';//LBL_BASIC_INFORMATION,CustomReports
//$serverTypeInstance->table = 'vtiger_customreports';
//$serverTypeInstance->column = 'server_type';
//$serverTypeInstance->label = 'Server Type';
//$serverTypeInstance->columntype = 'varchar(15)';
//$serverTypeInstance->uitype = 15;
//$serverTypeInstance->typeofdata = 'V~O';
//$serverTypeInstance->setPicklistValues(array('MT4', 'MT5'));
//$block->addField($serverTypeInstance);

//$notificationTypeInstance = new Vtiger_Field();
//$notificationTypeInstance->name = 'notification_type';//LBL_NOTIFICATIONS_INFORMATION,Notifications
//$notificationTypeInstance->table = 'vtiger_notifications';
//$notificationTypeInstance->column = 'notification_type';
//$notificationTypeInstance->label = 'notification_type';
//$notificationTypeInstance->columntype = 'varchar(15)';
//$notificationTypeInstance->uitype = 15;
//$notificationTypeInstance->typeofdata = 'V~M';
//$notificationTypeInstance->defaultvalue = 'CRM';
//$notificationTypeInstance->setPicklistValues(array('CRM', 'Cabinet'));
//$block->addField($notificationTypeInstance);

//$contactIdInstance = new Vtiger_Field();
//$contactIdInstance->name = 'contact_id';//LBL_NOTIFICATIONS_INFORMATION,Notifications
//$contactIdInstance->table = 'vtiger_notifications';
//$contactIdInstance->column = 'contact_id';
//$contactIdInstance->label = 'Contact ID';
//$contactIdInstance->columntype = 'int(11)';
//$contactIdInstance->uitype = 1;
//$contactIdInstance->typeofdata = 'V~O';
//$block->addField($contactIdInstance);

//$contactIdInstance = new Vtiger_Field();
//$contactIdInstance->name = 'contact_id';//LBL_NOTIFICATIONS_INFORMATION,Notifications
//$contactIdInstance->table = 'vtiger_notifications';
//$contactIdInstance->column = 'contact_id';
//$contactIdInstance->label = 'Contact ID';
//$contactIdInstance->columntype = 'int(11)';
//$contactIdInstance->uitype = 1;
//$contactIdInstance->typeofdata = 'V~O';
//$block->addField($contactIdInstance);

//$campaignActivitySubjectInstance = new Vtiger_Field();
//$campaignActivitySubjectInstance->name = 'campaign_activity_template';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$campaignActivitySubjectInstance->table = 'vtiger_campaignactivity';
//$campaignActivitySubjectInstance->column = 'campaign_activity_template';
//$campaignActivitySubjectInstance->label = 'Campaign Activity Template';
//$campaignActivitySubjectInstance->columntype = 'text';
//$campaignActivitySubjectInstance->uitype = 19;
//$campaignActivitySubjectInstance->typeofdata = 'V~O';
//$block->addField($campaignActivitySubjectInstance);

//$campaignActivitySubjectInstance = new Vtiger_Field();
//$campaignActivitySubjectInstance->name = 'campaign_activity_subject';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$campaignActivitySubjectInstance->table = 'vtiger_campaignactivity';
//$campaignActivitySubjectInstance->column = 'campaign_activity_subject';
//$campaignActivitySubjectInstance->label = 'Campaign Activity Subject';
//$campaignActivitySubjectInstance->columntype = 'varchar(100)';
//$campaignActivitySubjectInstance->uitype = 1;
//$campaignActivitySubjectInstance->typeofdata = 'V~O';
//$block->addField($campaignActivitySubjectInstance);

//$campaignActivityModuleInstance = new Vtiger_Field();
//$campaignActivityModuleInstance->name = 'campaign_activity_module';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$campaignActivityModuleInstance->table = 'vtiger_campaignactivity';
//$campaignActivityModuleInstance->column = 'campaign_activity_module';
//$campaignActivityModuleInstance->label = 'Campaign Activity Module';
//$campaignActivityModuleInstance->columntype = 'varchar(10)';
//$campaignActivityModuleInstance->uitype = 1;
//$campaignActivityModuleInstance->typeofdata = 'V~O';
//$block->addField($campaignActivityModuleInstance);


//$campaignIdInstance = new Vtiger_Field();
//$campaignIdInstance->name = 'campaign_id';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$campaignIdInstance->table = 'vtiger_campaignactivity';
//$campaignIdInstance->column = 'campaign_id';
//$campaignIdInstance->label = 'Campaign ID';
//$campaignIdInstance->columntype = 'int(11)';
//$campaignIdInstance->uitype = 1;
//$campaignIdInstance->typeofdata = 'V~O';
//$block->addField($campaignIdInstance);

//$schAnnualDateInstance = new Vtiger_Field();
//$schAnnualDateInstance->name = 'schannualdates';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$schAnnualDateInstance->table = 'vtiger_campaignactivity';
//$schAnnualDateInstance->column = 'schannualdates';
//$schAnnualDateInstance->label = 'Schedule Annual Date';
//$schAnnualDateInstance->columntype = 'varchar(100)';
//$schAnnualDateInstance->uitype = 1;
//$schAnnualDateInstance->typeofdata = 'V~O';
//$block->addField($schAnnualDateInstance);

//$nextTriggerInstance = new Vtiger_Field();
//$nextTriggerInstance->name = 'nexttrigger_time';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$nextTriggerInstance->table = 'vtiger_campaignactivity';
//$nextTriggerInstance->column = 'nexttrigger_time';
//$nextTriggerInstance->label = 'Next Tigger Time';
//$nextTriggerInstance->columntype = 'datetime';
//$nextTriggerInstance->uitype = 1;
//$nextTriggerInstance->typeofdata = 'V~O';
//$block->addField($nextTriggerInstance);

//$schTimeInstance = new Vtiger_Field();
//$schTimeInstance->name = 'schtime';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$schTimeInstance->table = 'vtiger_campaignactivity';
//$schTimeInstance->column = 'schtime';
//$schTimeInstance->label = 'Schedule Time';
//$schTimeInstance->columntype = 'varchar(50)';
//$schTimeInstance->uitype = 1;
//$schTimeInstance->typeofdata = 'V~O';
//$block->addField($schTimeInstance);

//$schDayOFWeekInstance = new Vtiger_Field();
//$schDayOFWeekInstance->name = 'schdayofweek';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$schDayOFWeekInstance->table = 'vtiger_campaignactivity';
//$schDayOFWeekInstance->column = 'schdayofweek';
//$schDayOFWeekInstance->label = 'Schedule Day of Week';
//$schDayOFWeekInstance->columntype = 'varchar(100)';
//$schDayOFWeekInstance->uitype = 1;
//$schDayOFWeekInstance->typeofdata = 'V~O';
//$block->addField($schDayOFWeekInstance);

//$schMonthInstance = new Vtiger_Field();
//$schMonthInstance->name = 'schdayofmonth';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$schMonthInstance->table = 'vtiger_campaignactivity';
//$schMonthInstance->column = 'schdayofmonth';
//$schMonthInstance->label = 'Schedule Month';
//$schMonthInstance->columntype = 'varchar(100)';
//$schMonthInstance->uitype = 1;
//$schMonthInstance->typeofdata = 'V~O';
//$block->addField($schMonthInstance);

//$schTypeInstance = new Vtiger_Field();
//$schTypeInstance->name = 'schtypeid';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$schTypeInstance->table = 'vtiger_campaignactivity';
//$schTypeInstance->column = 'schtypeid';
//$schTypeInstance->label = 'Schedule Type';
//$schTypeInstance->columntype = 'int(10)';
//$schTypeInstance->uitype = 1;
//$schTypeInstance->typeofdata = 'V~O';
//$block->addField($schTypeInstance);

//$descriptionInstance = new Vtiger_Field();
//$descriptionInstance->name = 'description';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$descriptionInstance->table = 'vtiger_campaignactivity';
//$descriptionInstance->column = 'description';
//$descriptionInstance->label = 'Description';
//$descriptionInstance->columntype = 'text';
//$descriptionInstance->uitype = 19;
//$descriptionInstance->typeofdata = 'V~O';
//$block->addField($descriptionInstance);

//$statusInstance = new Vtiger_Field();
//$statusInstance->name = 'activity_type';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$statusInstance->table = 'vtiger_campaignactivity';
//$statusInstance->column = 'activity_type';
//$statusInstance->label = 'Type';
//$statusInstance->columntype = 'varchar(15)';
//$statusInstance->uitype = 15;
//$statusInstance->typeofdata = 'V~M';
//$statusInstance->setPicklistValues(array('Email', 'SMS', 'Notification'));
//$block->addField($statusInstance);

//$statusInstance = new Vtiger_Field();
//$statusInstance->name = 'campaign_activity_status';//LBL_CAMPAIGNACTIVITY_INFORMATION,CampaignActivity
//$statusInstance->table = 'vtiger_campaignactivity';
//$statusInstance->column = 'campaign_activity_status';
//$statusInstance->label = 'Status';
//$statusInstance->columntype = 'varchar(15)';
//$statusInstance->uitype = 15;
//$statusInstance->typeofdata = 'V~M';
//$statusInstance->defaultvalue = 'Active';
//$statusInstance->setPicklistValues(array('Active', 'Inactive', 'Not Started', 'In Progress', 'Completed'));
//$block->addField($statusInstance);

//$commTypeInstance = new Vtiger_Field();
//$commTypeInstance->name = 'status';//LBL_NOTIFICATIONS_INFORMATION,Notifications
//$commTypeInstance->table = 'vtiger_notifications';
//$commTypeInstance->column = 'status';
//$commTypeInstance->label = 'Status';
//$commTypeInstance->columntype = 'int(1)';
//$commTypeInstance->uitype = 7;
//$commTypeInstance->typeofdata = 'I~O';
//$block->addField($commTypeInstance);

//$commTypeInstance = new Vtiger_Field();
//$commTypeInstance->name = 'link';//LBL_NOTIFICATIONS_INFORMATION,Notifications
//$commTypeInstance->table = 'vtiger_notifications';
//$commTypeInstance->column = 'link';
//$commTypeInstance->label = 'Link';
//$commTypeInstance->columntype = 'varchar(255)';
//$commTypeInstance->uitype = 1;
//$commTypeInstance->typeofdata = 'V~O';
//$block->addField($commTypeInstance);


//$commTypeInstance = new Vtiger_Field();
//$commTypeInstance->name = 'related_to';//LBL_NOTIFICATIONS_INFORMATION,Notifications
//$commTypeInstance->table = 'vtiger_notifications';
//$commTypeInstance->column = 'related_to';
//$commTypeInstance->label = 'Related To';
//$commTypeInstance->columntype = 'int(11)';
//$commTypeInstance->uitype = 10;
//$commTypeInstance->typeofdata = 'V~O';
//$block->addField($commTypeInstance);

//$commTypeInstance = new Vtiger_Field();
//$commTypeInstance->name = 'type';//LBL_CUSTOM_INFORMATION,TradesCommission
//$commTypeInstance->table = 'vtiger_tradescommission';
//$commTypeInstance->column = 'type';
//$commTypeInstance->label = 'Type';
//$commTypeInstance->columntype = 'varchar(10)';
//$commTypeInstance->uitype = 1;
//$commTypeInstance->typeofdata = 'V~O';
//$commTypeInstance->summaryfield = 1;
//$block->addField($commTypeInstance);

//$distMaxCommInstance = new Vtiger_Field();
//$distMaxCommInstance->name = 'is_dist_max_comm';//LBL_CUSTOM_INFORMATION,Contacts
//$distMaxCommInstance->table = 'vtiger_contactdetails';
//$distMaxCommInstance->column = 'is_dist_max_comm';
//$distMaxCommInstance->label = 'Distribute Max Comm.';
//$distMaxCommInstance->columntype = 'varchar(5)';
//$distMaxCommInstance->uitype = 15;
//$distMaxCommInstance->typeofdata = 'V~O';
//$distMaxCommInstance->setPicklistValues(array('Yes', 'No'));
//$block->addField($distMaxCommInstance);

//$amountPerLotInstance = new Vtiger_Field();
//$amountPerLotInstance->name = 'comm_amount_per_lot';//LBL_CUSTOM_INFORMATION,Contacts
//$amountPerLotInstance->table = 'vtiger_contactdetails';
//$amountPerLotInstance->column = 'comm_amount_per_lot';
//$amountPerLotInstance->label = 'Max IB Comm. Amount per lot';
//$amountPerLotInstance->columntype = 'decimal(25,2)';
//$amountPerLotInstance->uitype = 71;
//$amountPerLotInstance->typeofdata = 'N~O';
//$block->addField($amountPerLotInstance);

//$ibNoteFieldInstance = new Vtiger_Field();
//$ibNoteFieldInstance->name = 'ib_note';//LBL_CUSTOM_INFORMATION,Contacts
//$ibNoteFieldInstance->table = 'vtiger_contactdetails';
//$ibNoteFieldInstance->column = 'ib_note';
//$ibNoteFieldInstance->label = 'IB Note';
//$ibNoteFieldInstance->columntype = 'text';
//$ibNoteFieldInstance->uitype = 19;
//$ibNoteFieldInstance->typeofdata = 'V~O';
//$block->addField($ibNoteFieldInstance);

//$fieldInstance1 = new Vtiger_Field();
//$fieldInstance1->name = 'ib_note';//LBL_CUSTOM_INFORMATION,Contacts
//$fieldInstance1->table = 'vtiger_activity';
//$fieldInstance1->column = 'ib_note';
//$fieldInstance1->label = 'IB Note';
//$fieldInstance1->columntype = 'varchar(100)';
//$fieldInstance1->uitype = 1;
//$fieldInstance1->typeofdata = 'V~O~LE~100';
//$block->addField($fieldInstance1);