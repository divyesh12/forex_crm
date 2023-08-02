<?php  include_once "modules/Vtiger/CRMEntity.php";
		    
		    class AccountMapping extends Vtiger_CRMEntity {
			var $table_name = "vtiger_accountmapping";
			var $table_index = "accountmappingid";
			var $customFieldTable = Array("vtiger_accountmappingcf", "accountmappingid");
			var $tab_name = Array("vtiger_crmentity", "vtiger_accountmapping", "vtiger_accountmappingcf");
			var $tab_name_index = Array("vtiger_crmentity" => "crmid","vtiger_accountmapping" => "accountmappingid","vtiger_accountmappingcf" => "accountmappingid");
			var $list_fields = Array("LBL_ACCOUNT_START_RANGE" => Array("accountmapping", "start_range"),"Assigned To" => Array("crmentity", "smownerid"));
			var $list_fields_name = Array("LBL_ACCOUNT_START_RANGE" => "start_range","Assigned To" => "assigned_user_id",);
			var $list_link_field = "start_range";
			var $search_fields = Array("LBL_ACCOUNT_START_RANGE" => Array("accountmapping", "start_range"),"Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
			var $search_fields_name = Array("LBL_ACCOUNT_START_RANGE" => "start_range","Assigned To" => "assigned_user_id",);
			var $popup_fields = Array("start_range");
			var $def_basicsearch_col = "start_range";
			var $def_detailview_recname = "start_range";
			var $mandatory_fields = Array("start_range", "assigned_user_id");
			var $default_order_by = "start_range";
			var $default_sort_order = "ASC";

		    } ?>