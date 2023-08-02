<?php  include_once "modules/Vtiger/CRMEntity.php";
		    
		    class SecuritySymbol extends Vtiger_CRMEntity {
			var $table_name = "vtiger_securitysymbol";
			var $table_index = "securitysymbolid";
			var $customFieldTable = Array("vtiger_securitysymbolcf", "securitysymbolid");
			var $tab_name = Array("vtiger_crmentity", "vtiger_securitysymbol", "vtiger_securitysymbolcf");
			var $tab_name_index = Array("vtiger_crmentity" => "crmid","vtiger_securitysymbol" => "securitysymbolid","vtiger_securitysymbolcf" => "securitysymbolid");
			var $list_fields = Array("LBL_SYMBOL_NAME" => Array("securitysymbol", "symbol_name"),"Assigned To" => Array("crmentity", "smownerid"));
			var $list_fields_name = Array("LBL_SYMBOL_NAME" => "symbol_name","Assigned To" => "assigned_user_id",);
			var $list_link_field = "symbol_name";
			var $search_fields = Array("LBL_SYMBOL_NAME" => Array("securitysymbol", "symbol_name"),"Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
			var $search_fields_name = Array("LBL_SYMBOL_NAME" => "symbol_name","Assigned To" => "assigned_user_id",);
			var $popup_fields = Array("symbol_name");
			var $def_basicsearch_col = "symbol_name";
			var $def_detailview_recname = "symbol_name";
			var $mandatory_fields = Array("symbol_name", "assigned_user_id");
			var $default_order_by = "symbol_name";
			var $default_sort_order = "ASC";

		    } ?>