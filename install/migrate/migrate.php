    <?php

require_once('/var/lib/nextdom/config/common.config.php');
global $CONFIG;

$HOST=' ';

if ( 'localhost' != $CONFIG['db']['host'] ){
    $HOST=' -h '.$CONFIG['db']['host'];
}

$CMD='mysql -u '.$CONFIG['db']['username'].$HOST.' -p'.$CONFIG['db']['password'].' -f '.$CONFIG['db']['dbname'].' < '.__DIR__.'/migrate.sql > /dev/null 2>&1';
shell_exec($CMD);

require_once(__DIR__ . '/../../core/php/core.inc.php');

foreach (interactDef::all() as $interactDef) {
    $interactDef->setEnable(1);
    $interactDef->save();
}

    if ( 'localhost' != $CONFIG['db']['host'] ){
        $HOST=' -h '.$CONFIG['db']['host'];
    }

