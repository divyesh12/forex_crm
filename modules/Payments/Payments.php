<?php

include_once "modules/Vtiger/CRMEntity.php";

class Payments extends Vtiger_CRMEntity {

    var $table_name = "vtiger_payments";
    var $table_index = "paymentsid";
    var $customFieldTable = Array("vtiger_paymentscf", "paymentsid");
    var $tab_name = Array("vtiger_crmentity", "vtiger_payments", "vtiger_paymentscf");
    var $tab_name_index = Array("vtiger_crmentity" => "crmid", "vtiger_payments" => "paymentsid", "vtiger_paymentscf" => "paymentsid");
    var $list_fields = Array("LBL_PAYMENT_OPERATION" => Array("payments", "payment_operation"), "Assigned To" => Array("crmentity", "smownerid"));
    var $list_fields_name = Array("LBL_PAYMENT_OPERATION" => "payment_operation", "Assigned To" => "assigned_user_id",);
    var $list_link_field = "payment_operation";
    var $search_fields = Array("LBL_PAYMENT_OPERATION" => Array("payments", "payment_operation"), "Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
    var $search_fields_name = Array("LBL_PAYMENT_OPERATION" => "payment_operation", "Assigned To" => "assigned_user_id",);
    var $popup_fields = Array("payment_operation");
    var $def_basicsearch_col = "payment_operation";
    var $def_detailview_recname = "payment_operation";
    var $mandatory_fields = Array("payment_operation", "assigned_user_id");
    var $default_order_by = "payment_operation";
    var $default_sort_order = "ASC";

//    public function save_module($module) {
//        global $adb;
////        echo "UPDATE `vtiger_modtracker_basic` SET `changedon`='" . $date_time . "'  WHERE crmid =" . $recordId;
////        exit;
//        $query = "UPDATE `vtiger_modtracker_basic` SET `changedon`='" . date("Y-m-d h:i:s") . "'  WHERE crmid =" . $this->id;
//        //exit;
//        $adb->pquery($query, array());
//        //Payments_Record_Model::updateChangedon(date("Y-m-d h:i:s"), $this->id);
//    }

}

?>