<?php

include_once "modules/Vtiger/CRMEntity.php";

class IBCommissionProfile extends Vtiger_CRMEntity {

    var $table_name = "vtiger_ibcommissionprofile";
    var $table_index = "ibcommissionprofileid";
    var $customFieldTable = Array("vtiger_ibcommissionprofilecf", "ibcommissionprofileid");
    var $tab_name = Array("vtiger_crmentity", "vtiger_ibcommissionprofile", "vtiger_ibcommissionprofilecf");
    var $tab_name_index = Array("vtiger_crmentity" => "crmid", "vtiger_ibcommissionprofile" => "ibcommissionprofileid", "vtiger_ibcommissionprofilecf" => "ibcommissionprofileid");
    var $list_fields = Array("LBL_PROFILE_NAME" => Array("ibcommissionprofile", "profile_name"), "Assigned To" => Array("crmentity", "smownerid"));
    var $list_fields_name = Array("LBL_PROFILE_NAME" => "profile_name", "Assigned To" => "assigned_user_id",);
    var $list_link_field = "profile_name";
    var $search_fields = Array("LBL_PROFILE_NAME" => Array("ibcommissionprofile", "profile_name"), "Assigned To" => Array("vtiger_crmentity", "assigned_user_id"),);
    var $search_fields_name = Array("LBL_PROFILE_NAME" => "profile_name", "Assigned To" => "assigned_user_id",);
    var $popup_fields = Array("profile_name");
    var $def_basicsearch_col = "profile_name";
    var $def_detailview_recname = "profile_name";
    var $mandatory_fields = Array("profile_name", "assigned_user_id");
    var $default_order_by = "profile_name";
    var $default_sort_order = "ASC";
}

?>