<?php  include_once "modules/Vtiger/CRMEntity.php";
		    
		    class Ewallet extends Vtiger_CRMEntity {
			var $table_name = "vtiger_ewallet";
			var $table_index = "ewalletid";
			var $customFieldTable = Array("vtiger_ewalletcf", "ewalletid");
			var $tab_name = Array("vtiger_crmentity", "vtiger_ewallet", "vtiger_ewalletcf");
			var $tab_name_index = Array("vtiger_crmentity" => "crmid","vtiger_ewallet" => "ewalletid","vtiger_ewalletcf" => "ewalletid");
			var $list_fields = Array("LBL_CONTACT_NAME" => Array("ewallet", "contactid"),"Assigned To" => Array("crmentity", "smownerid"));
			var $list_fields_name = Array("LBL_CONTACT_NAME" => "contactid","Assigned To" => "assigned_user_id",);
			var $list_link_field = "contactid";
			var $search_fields = Array("LBL_CONTACT_NAME" => Array("ewallet", "contactid"),"Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
			var $search_fields_name = Array("LBL_CONTACT_NAME" => "contactid","Assigned To" => "assigned_user_id",);
			var $popup_fields = Array("contactid");
			var $def_basicsearch_col = "contactid";
			var $def_detailview_recname = "contactid";
			var $mandatory_fields = Array("contactid", "assigned_user_id");
			var $default_order_by = "contactid";
			var $default_sort_order = "ASC";

		    } ?>