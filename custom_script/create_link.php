<?php
include_once('config.php');
require_once('include/logging.php');
require_once('data/Tracker.php');
include 'include/utils/utils.php';

$moduleName = 'Notifications';
$css_widgetType = "HEADERCSS";
$css_widgetName = "Notifications";
$css_link = "layouts/v7/modules/" . $moduleName . "/resources/" . $moduleName . "CSS.css";
$js_widgetType = "HEADERSCRIPT";
$js_widgetName = "Notifications";
$js_link = "layouts/v7/modules/" . $moduleName . "/resources/" . $moduleName . "JS.js";
$module = Vtiger_Module::getInstance($moduleName);
if ($module) {
    $module->addLink($css_widgetType, $css_widgetName, $css_link);
    $module->addLink($js_widgetType, $js_widgetName, $js_link);
}