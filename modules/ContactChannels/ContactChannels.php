<?php  include_once "modules/Vtiger/CRMEntity.php";
		    
		    class ContactChannels extends Vtiger_CRMEntity {
			var $table_name = "vtiger_contactchannels";
			var $table_index = "contactchannelsid";
			var $customFieldTable = Array("vtiger_contactchannelscf", "contactchannelsid");
			var $tab_name = Array("vtiger_crmentity", "vtiger_contactchannels", "vtiger_contactchannelscf");
			var $tab_name_index = Array("vtiger_crmentity" => "crmid","vtiger_contactchannels" => "contactchannelsid","vtiger_contactchannelscf" => "contactchannelsid");
			var $list_fields = Array("Channel" => Array("contactchannels", "channel"),"Assigned To" => Array("crmentity", "smownerid"));
			var $list_fields_name = Array("Channel" => "channel","Assigned To" => "assigned_user_id",);
			var $list_link_field = "channel";
			var $search_fields = Array("Channel" => Array("contactchannels", "channel"),"Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
			var $search_fields_name = Array("Channel" => "channel","Assigned To" => "assigned_user_id",);
			var $popup_fields = Array("channel");
			var $def_basicsearch_col = "channel";
			var $def_detailview_recname = "channel";
			var $mandatory_fields = Array("channel", "assigned_user_id");
			var $default_order_by = "channel";
			var $default_sort_order = "ASC";

		    } ?>