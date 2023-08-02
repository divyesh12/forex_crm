<?php  include_once "modules/Vtiger/CRMEntity.php";
		    
		    class LeverageHistory extends Vtiger_CRMEntity {
			var $table_name = "vtiger_leveragehistory";
			var $table_index = "leveragehistoryid";
			var $customFieldTable = Array("vtiger_leveragehistorycf", "leveragehistoryid");
			var $tab_name = Array("vtiger_crmentity", "vtiger_leveragehistory", "vtiger_leveragehistorycf");
			var $tab_name_index = Array("vtiger_crmentity" => "crmid","vtiger_leveragehistory" => "leveragehistoryid","vtiger_leveragehistorycf" => "leveragehistoryid");
			var $list_fields = Array("LBL_ACCOUNT_NO" => Array("leveragehistory", "liveaccountid"),"Assigned To" => Array("crmentity", "smownerid"));
			var $list_fields_name = Array("LBL_ACCOUNT_NO" => "liveaccountid","Assigned To" => "assigned_user_id",);
			var $list_link_field = "liveaccountid";
			var $search_fields = Array("LBL_ACCOUNT_NO" => Array("leveragehistory", "liveaccountid"),"Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
			var $search_fields_name = Array("LBL_ACCOUNT_NO" => "liveaccountid","Assigned To" => "assigned_user_id",);
			var $popup_fields = Array("liveaccountid");
			var $def_basicsearch_col = "liveaccountid";
			var $def_detailview_recname = "liveaccountid";
			var $mandatory_fields = Array("liveaccountid", "assigned_user_id");
			var $default_order_by = "liveaccountid";
			var $default_sort_order = "ASC";

		    } ?>