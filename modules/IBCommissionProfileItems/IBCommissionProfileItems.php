<?php  include_once "modules/Vtiger/CRMEntity.php";
		    
		    class IBCommissionProfileItems extends Vtiger_CRMEntity {
			var $table_name = "vtiger_ibcommissionprofileitems";
			var $table_index = "ibcommissionprofileitemsid";
			var $customFieldTable = Array("vtiger_ibcommissionprofileitemscf", "ibcommissionprofileitemsid");
			var $tab_name = Array("vtiger_crmentity", "vtiger_ibcommissionprofileitems", "vtiger_ibcommissionprofileitemscf");
			var $tab_name_index = Array("vtiger_crmentity" => "crmid","vtiger_ibcommissionprofileitems" => "ibcommissionprofileitemsid","vtiger_ibcommissionprofileitemscf" => "ibcommissionprofileitemsid");
			var $list_fields = Array("LBL_COMMISSION_VALUE" => Array("ibcommissionprofileitems", "ib_commission_value"),"Assigned To" => Array("crmentity", "smownerid"));
			var $list_fields_name = Array("LBL_COMMISSION_VALUE" => "ib_commission_value","Assigned To" => "assigned_user_id",);
			var $list_link_field = "ib_commission_value";
			var $search_fields = Array("LBL_COMMISSION_VALUE" => Array("ibcommissionprofileitems", "ib_commission_value"),"Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
			var $search_fields_name = Array("LBL_COMMISSION_VALUE" => "ib_commission_value","Assigned To" => "assigned_user_id",);
			var $popup_fields = Array("ib_commission_value");
			var $def_basicsearch_col = "ib_commission_value";
			var $def_detailview_recname = "ib_commission_value";
			var $mandatory_fields = Array("ib_commission_value", "assigned_user_id");
			var $default_order_by = "ib_commission_value";
			var $default_sort_order = "ASC";

		    } ?>