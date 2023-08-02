<?php

include_once "modules/Vtiger/CRMEntity.php";

class LiveAccount extends Vtiger_CRMEntity {

    var $table_name = "vtiger_liveaccount";
    var $table_index = "liveaccountid";
    var $customFieldTable = Array("vtiger_liveaccountcf", "liveaccountid");
    var $tab_name = Array("vtiger_crmentity", "vtiger_liveaccount", "vtiger_liveaccountcf");
    var $tab_name_index = Array("vtiger_crmentity" => "crmid", "vtiger_liveaccount" => "liveaccountid", "vtiger_liveaccountcf" => "liveaccountid");
    var $list_fields = Array("LBL_ACCOUNT_NO" => Array("liveaccount", "account_no"), "Assigned To" => Array("crmentity", "smownerid"));
    var $list_fields_name = Array("LBL_ACCOUNT_NO" => "account_no", "Assigned To" => "assigned_user_id",);
    var $list_link_field = "account_no";
    var $search_fields = Array("LBL_ACCOUNT_NO" => Array("liveaccount", "account_no"), "Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
    var $search_fields_name = Array("LBL_ACCOUNT_NO" => "account_no", "Assigned To" => "assigned_user_id",);
    var $popup_fields = Array("account_no");
    var $def_basicsearch_col = "account_no";
    var $def_detailview_recname = "account_no";
    var $mandatory_fields = Array("account_no", "assigned_user_id");
    var $default_order_by = "account_no";
    var $default_sort_order = "ASC";

    function save_module($module) {
        global $adb;
        $email_label_account_type = end(explode("_", $this->column_fields['live_label_account_type']));
        $update_query = "UPDATE vtiger_liveaccount SET cabinet_label_account_type=? WHERE liveaccountid=?";
        $update_params = array($email_label_account_type, $this->id);
        $adb->pquery($update_query, $update_params);
    }

}

?>