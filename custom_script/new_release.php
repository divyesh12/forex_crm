<?php

include_once('config.php');
require_once('include/logging.php');
require_once('data/Tracker.php');
include 'include/utils/utils.php';

/*module permission file create*/
fopen('tabdata.php', 'w+');
chmod('tabdata.php', 0664);
create_tab_data_file();

fopen('parent_tabdata.php', 'w+');
chmod('parent_tabdata.php', 0664);
create_parenttab_data_file();

/*Cabinet logo folder add*/
$path = 'test/logo/cabinet';
if(!file_exists($path))
{
    mkdir($path);
    chmod($path, 0775);
}

/*user privileges files for admin user*/
require_once('modules/Users/CreateUserPrivilegeFile.php');
createUserPrivilegesfile(1);
createUserSharingPrivilegesfile(1);
chmod('user_privileges/user_privileges_1.php', 0664);
chmod('user_privileges/sharing_privileges_1.php', 0664);

/*user privileges files for front form api user*/
createUserPrivilegesfile(5);
createUserSharingPrivilegesfile(5);
chmod('user_privileges/user_privileges_5.php', 0664);
chmod('user_privileges/sharing_privileges_5.php', 0664);

echo 'New release changes done';