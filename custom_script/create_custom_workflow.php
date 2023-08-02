<?php
chdir (dirname(__FILE__) . '/..');
require_once 'include/utils/utils.php';
require 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
$emm = new VTEntityMethodManager($adb);

//$emm->addEntityMethod("Module Name","Label", "Path to file" , "Method Name" );
$emm->addEntityMethod("HelpDesk", "DocumentApprovedOnCloseTicket", "modules/HelpDesk/handlers/DocumentApprovedOnCloseTicket.php", "HelpDesk_DocumentApprovedOnCloseTicket");
echo 'done';