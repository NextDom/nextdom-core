<?php

require_once('core/config/common.config.php');
global $CONFIG;

shell_exec('php install/update/3.2.4.php');
shell_exec('php install/update/3.2.5.php');
shell_exec('php install/update/3.2.6.php');
shell_exec('php install/update/3.3.0.php');
shell_exec('mysql -u '.$CONFIG['db']['username'].' -p'.$CONFIG['db']['password'].' '.$CONFIG['db']['dbname'].' < install/update/3.3.0.sql');
shell_exec('mysql -u '.$CONFIG['db']['username'].' -p'.$CONFIG['db']['password'].' '.$CONFIG['db']['dbname'].' < install/update/3.3.1.sql');
shell_exec('mysql -u '.$CONFIG['db']['username'].' -p'.$CONFIG['db']['password'].' '.$CONFIG['db']['dbname'].' < install/update/3.3.2.sql');
shell_exec('mysql -u '.$CONFIG['db']['username'].' -p'.$CONFIG['db']['password'].' '.$CONFIG['db']['dbname'].' < install/update/3.3.3.sql');
shell_exec('mysql -u '.$CONFIG['db']['username'].' -p'.$CONFIG['db']['password'].' '.$CONFIG['db']['dbname'].' < install/update/nextdom.sql');

