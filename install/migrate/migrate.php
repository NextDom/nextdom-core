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

shell_exec('mysql -u '.$CONFIG['db']['username'].' -p'.$CONFIG['db']['password'].' -f '.$CONFIG['db']['dbname'].' < '.__DIR__.'/migrate.sql');

