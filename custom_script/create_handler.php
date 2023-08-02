<?php
include_once('config.php');
require_once('include/logging.php');
require_once('data/Tracker.php');
include 'include/utils/utils.php';

global $adb;
$moduleName = 'Campaigns';
$em = new VTEventsManager($adb);
$em->registerHandler("vtiger.entity.aftersave", "modules/" . $moduleName . "/handlers/" . $moduleName . "AfterSaveHandler.php", (string) $moduleName . "AfterSaveHandler");