<?php

require_once __DIR__ . '/../../core/php/core.inc.php';
require_once(__DIR__.'/../../core/config/common.config.php');

global $CONFIG;

foreach (interactDef::all() as $interactDef) {
    $interactDef->setEnable(1);
    $interactDef->save();
}

foreach (scenarioExpression::all() as $scenarioExpression) {
    if ($scenarioExpression->getExpression() == 'equipment') {
        try {
            $scenarioExpression->setExpression('equipement');
            $scenarioExpression->save();
        } catch (Exception $e) {

        }
    }
}

$HOST=' ';

if ( 'localhost' != $CONFIG['db']['host'] ){
    $HOST=' -h '.$CONFIG['db']['host'];
}

$CMD='mysql -u '.$CONFIG['db']['username'].$HOST.' -p'.$CONFIG['db']['password'].' -f '.$CONFIG['db']['dbname'].' < '.__DIR__.'/migrate.sql';
shell_exec($CMD);