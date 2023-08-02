<?php  include_once "modules/Vtiger/CRMEntity.php";
		    
		    class CurrencyConverter extends Vtiger_CRMEntity {
			var $table_name = "vtiger_currencyconverter";
			var $table_index = "currencyconverterid";
			var $customFieldTable = Array("vtiger_currencyconvertercf", "currencyconverterid");
			var $tab_name = Array("vtiger_crmentity", "vtiger_currencyconverter", "vtiger_currencyconvertercf");
			var $tab_name_index = Array("vtiger_crmentity" => "crmid","vtiger_currencyconverter" => "currencyconverterid","vtiger_currencyconvertercf" => "currencyconverterid");
			var $list_fields = Array("LBL_FROM_CURRENCY" => Array("currencyconverter", "from_currency"),"Assigned To" => Array("crmentity", "smownerid"));
			var $list_fields_name = Array("LBL_FROM_CURRENCY" => "from_currency","Assigned To" => "assigned_user_id",);
			var $list_link_field = "from_currency";
			var $search_fields = Array("LBL_FROM_CURRENCY" => Array("currencyconverter", "from_currency"),"Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
			var $search_fields_name = Array("LBL_FROM_CURRENCY" => "from_currency","Assigned To" => "assigned_user_id",);
			var $popup_fields = Array("from_currency");
			var $def_basicsearch_col = "from_currency";
			var $def_detailview_recname = "from_currency";
			var $mandatory_fields = Array("from_currency", "assigned_user_id");
			var $default_order_by = "from_currency";
			var $default_sort_order = "ASC";

		    } ?>