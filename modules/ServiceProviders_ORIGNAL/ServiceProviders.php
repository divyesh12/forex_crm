<?php  include_once "modules/Vtiger/CRMEntity.php";
		    
		    class ServiceProviders extends Vtiger_CRMEntity {
			var $table_name = "vtiger_serviceproviders";
			var $table_index = "serviceprovidersid";
			var $customFieldTable = Array("vtiger_serviceproviderscf", "serviceprovidersid");
			var $tab_name = Array("vtiger_crmentity", "vtiger_serviceproviders", "vtiger_serviceproviderscf");
			var $tab_name_index = Array("vtiger_crmentity" => "crmid","vtiger_serviceproviders" => "serviceprovidersid","vtiger_serviceproviderscf" => "serviceprovidersid");
			var $list_fields = Array("LBL_SERVICE_PROVIDER_NAME" => Array("serviceproviders", "service_provider_name"),"Assigned To" => Array("crmentity", "smownerid"));
			var $list_fields_name = Array("LBL_SERVICE_PROVIDER_NAME" => "service_provider_name","Assigned To" => "assigned_user_id",);
			var $list_link_field = "service_provider_name";
			var $search_fields = Array("LBL_SERVICE_PROVIDER_NAME" => Array("serviceproviders", "service_provider_name"),"Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
			var $search_fields_name = Array("LBL_SERVICE_PROVIDER_NAME" => "service_provider_name","Assigned To" => "assigned_user_id",);
			var $popup_fields = Array("service_provider_name");
			var $def_basicsearch_col = "service_provider_name";
			var $def_detailview_recname = "service_provider_name";
			var $mandatory_fields = Array("service_provider_name", "assigned_user_id");
			var $default_order_by = "service_provider_name";
			var $default_sort_order = "ASC";

		    } ?>