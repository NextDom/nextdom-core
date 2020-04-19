<?php

require_once __DIR__ . '/../vendor/autoload.php';

$ignoredMethods = [
    'cmd' => [
        'cast' # Usage only static (in manager)
    ],
    'DB' => [
        '__construct',       # private
        'getTableName',      # private
        'getFields',         # private
        'setField',          # private
        'buildQuery',        # private
        'getField',          # private
        'getReflectionClass',# private
        'compareDatabase',
        'compareTable',
        'prepareIndexCompare',
        'compareField',
        'compareIndex',
        'buildDefinitionField',
        'buildDefinitionIndex'
    ],
    'eqLogic' => [
        'cast', # Usage only static (in manager)
        'byEqRealId',
        'getEqReal_id',
        'getEqReal',
        'setEqReal_id'
    ],
    'event' => [
        'getFileDescriptorLock', # Usage only static (in manager)
        'cleanEvent',  # Usage only static (in manager)
        'filterEvent', # Usage only static (in manager)
        'changesSince' # Usage only static (in manager)
    ],
    'nextdom' => [
        'getThemeConfig', # No usage
        'saveCustom', # Removed
        'checkValueInconfiguration', # Usage only static and private (in manager)
        'mimify' #Useless for us
    ],
    'message' => [
        'getOccurrences' # Not used
    ]
];
$error = false;
if ($argc > 2) {
    $className = $argv[1];
    if (class_exists($className)) {
        $currentArgIndex = 2;
        while ($currentArgIndex < $argc) {
            if (!method_exists($className, $argv[$currentArgIndex])) {
                if (!(isset($ignoredMethods[$className]) && in_array($argv[$currentArgIndex], $ignoredMethods[$className]))) {
                    echo "Méthode manquante dans la classe $className : " . $argv[$currentArgIndex] . "\n";
                    $error = true;
                }
            }
            ++$currentArgIndex;
        }
    } else {
        echo "La classe $className n'existe pas.\n";
    }
} else {
    $error = true;
}
if ($error) {
    exit(1);
}
echo " - $className -> OK\n";
exit(0);