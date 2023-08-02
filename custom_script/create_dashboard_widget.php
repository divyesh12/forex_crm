<?php
include_once('config.php');
require_once('include/logging.php');
require_once('data/Tracker.php');
include 'include/utils/utils.php';

$moduleName = 'Home';
$widgetType = "DASHBOARDWIDGET";
$widgetName = "TOTAL_PAYMENT_DATA";
$link = "index.php?module=$moduleName&view=ShowWidget&name=TotalPaymentData";
$module = Vtiger_Module::getInstance($moduleName);
if ($module) {
    $module->addLink($widgetType, $widgetName, $link);
}