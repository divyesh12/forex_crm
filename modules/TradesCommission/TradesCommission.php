<?php  include_once "modules/Vtiger/CRMEntity.php";
		    
		    class TradesCommission extends Vtiger_CRMEntity {
			var $table_name = "vtiger_tradescommission";
			var $table_index = "tradescommissionid";
			var $customFieldTable = Array("vtiger_tradescommissioncf", "tradescommissionid");
			var $tab_name = Array("vtiger_crmentity", "vtiger_tradescommission", "vtiger_tradescommissioncf");
			var $tab_name_index = Array("vtiger_crmentity" => "crmid","vtiger_tradescommission" => "tradescommissionid","vtiger_tradescommissioncf" => "tradescommissionid");
			var $list_fields = Array("LBL_CONTACT_NAME" => Array("tradescommission", "parent_contactid"),"Assigned To" => Array("crmentity", "smownerid"));
			var $list_fields_name = Array("LBL_CONTACT_NAME" => "parent_contactid","Assigned To" => "assigned_user_id",);
			var $list_link_field = "parent_contactid";
			var $search_fields = Array("LBL_CONTACT_NAME" => Array("tradescommission", "parent_contactid"),"Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
			var $search_fields_name = Array("LBL_CONTACT_NAME" => "parent_contactid","Assigned To" => "assigned_user_id",);
			var $popup_fields = Array("parent_contactid");
			var $def_basicsearch_col = "parent_contactid";
			var $def_detailview_recname = "parent_contactid";
			var $mandatory_fields = Array("parent_contactid", "assigned_user_id");
			var $default_order_by = "parent_contactid";
			var $default_sort_order = "ASC";

		    } ?>