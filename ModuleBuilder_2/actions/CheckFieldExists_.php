<?php

$root_diretory_path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
include $root_diretory_path . '/config.inc.php';

$con = mysqli_connect($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name']);
if (mysqli_connect_errno()) {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
      exit;
}

if (isset($_REQUEST["module_name"]) && !empty($_REQUEST["module_name"])) {

      $query = "SELECT * FROM vtiger_tab where name='" . $_REQUEST["module_name"] . "' ";
      $result = mysqli_query($con, $query);
      $row = mysqli_fetch_array($result);
      $tabid = $row['tabid'];

      $block_qry = "SELECT * FROM `vtiger_blocks` where tabid ='" . $tabid . "'  ";
      $block_result = mysqli_query($con, $block_qry);
      $numrows = mysqli_num_rows($block_result);

      if ($numrows > 0) {
            $html = '<option value="">Select an Option</option>';
            while ($block_row = mysqli_fetch_array($block_result)) {
                  $html .= '<option value="' . $block_row['blocklabel'] . '">' . $block_row['blocklabel'] . '</option>';
            }
      } else {
            $html .= '<option value="">block not available</option>';
      }
      echo $html;
      exit;
}
?>