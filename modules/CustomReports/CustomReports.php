<?php  include_once "modules/Vtiger/CRMEntity.php";
		    
		    class CustomReports extends Vtiger_CRMEntity {
			var $table_name = "vtiger_customreports";
			var $table_index = "customreportsid";
			var $customFieldTable = Array("vtiger_customreportscf", "customreportsid");
			var $tab_name = Array("vtiger_crmentity", "vtiger_customreports", "vtiger_customreportscf");
			var $tab_name_index = Array("vtiger_crmentity" => "crmid","vtiger_customreports" => "customreportsid","vtiger_customreportscf" => "customreportsid");
			var $list_fields = Array("LiveAccount Name" => Array("customreports", "liveaccountid"),"Assigned To" => Array("crmentity", "smownerid"));
			var $list_fields_name = Array("LiveAccount Name" => "liveaccountid","Assigned To" => "assigned_user_id",);
			var $list_link_field = "liveaccountid";
			var $search_fields = Array("LiveAccount Name" => Array("customreports", "liveaccountid"),"Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
			var $search_fields_name = Array("LiveAccount Name" => "liveaccountid","Assigned To" => "assigned_user_id",);
			var $popup_fields = Array("liveaccountid");
			var $def_basicsearch_col = "liveaccountid";
			var $def_detailview_recname = "liveaccountid";
			var $mandatory_fields = Array("liveaccountid", "assigned_user_id");
			var $default_order_by = "liveaccountid";
			var $default_sort_order = "ASC";

		    } ?>