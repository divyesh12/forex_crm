<?php

include_once "modules/Vtiger/CRMEntity.php";

class DemoAccount extends Vtiger_CRMEntity {

      var $table_name = "vtiger_demoaccount";
      var $table_index = "demoaccountid";
      var $customFieldTable = Array("vtiger_demoaccountcf", "demoaccountid");
      var $tab_name = Array("vtiger_crmentity", "vtiger_demoaccount", "vtiger_demoaccountcf");
      var $tab_name_index = Array("vtiger_crmentity" => "crmid", "vtiger_demoaccount" => "demoaccountid", "vtiger_demoaccountcf" => "demoaccountid");
      var $list_fields = Array("LBL_ACCOUNT_NO" => Array("demoaccount", "account_no"), "Assigned To" => Array("crmentity", "smownerid"));
      var $list_fields_name = Array("LBL_ACCOUNT_NO" => "account_no", "Assigned To" => "assigned_user_id",);
      var $list_link_field = "account_no";
      var $search_fields = Array("LBL_ACCOUNT_NO" => Array("demoaccount", "account_no"), "Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
      var $search_fields_name = Array("LBL_ACCOUNT_NO" => "account_no", "Assigned To" => "assigned_user_id",);
      var $popup_fields = Array("account_no");
      var $def_basicsearch_col = "account_no";
      var $def_detailview_recname = "account_no";
      var $mandatory_fields = Array("account_no", "assigned_user_id");
      var $default_order_by = "account_no";
      var $default_sort_order = "ASC";

}

?>