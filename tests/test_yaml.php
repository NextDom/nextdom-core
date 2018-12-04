<?php

define('TRANSLATIONS_PATH', realpath(__DIR__ . '/../translations'));
define('CACHE_PATH', realpath(__DIR__ . '/../var/i18n'));

if (count($argv) == 3) {
    echo "Utilisation : ".$argv[0]." LOCALE STR_TO_TEST STR_RESULT\n";
    die();
}

define('LOCALE', $argv[1]);

if (!file_exists(TRANSLATIONS_PATH . '/' . $argv[1] . '.yml')) {
    echo "File not found " . TRANSLATIONS_PATH . '/' . $argv[1] . '.yml' . "\n";
    exit(1);
}

shell_exec('rm -fr '.CACHE_PATH.'/*');

require (__DIR__ . '/../vendor/autoload.php');

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

$translator = new Translator(LOCALE, null, CACHE_PATH);
$translator->addLoader('yaml', new YamlFileLoader());
$translator->addResource('yaml', TRANSLATIONS_PATH . '/' . LOCALE . '.yml', LOCALE);

$result = $translator->trans($argv[2]);

if (strcmp($result, $argv[3]) !== 0) {
    echo "Bad translation : ".$result." != ".$argv[3]."\n";
    exit(1);
}

if (count(glob(CACHE_PATH . '/catalogue.fr_FR.*')) == 0) {
    echo "Cache file not generated.\n";
    exit(1);
}

echo "Translation test success\n";
exit(0);