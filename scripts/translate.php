<?php

if (count($argv) == 1) {
    echo "Utilisation : ".$argv[0]." FILE_TO_TRANSLATE\n";
    die();
}

require_once __DIR__ . '/../vendor/autoload.php';

define('NEXTDOM_ROOT', realpath(__DIR__ . '/..'));
define('NEXTDOM_DATA', '/tmp/');

// Avoid scripts warning during tests
if (!is_dir(NEXTDOM_ROOT . '/plugins')) {
    mkdir(NEXTDOM_ROOT . '/plugins');
}
\NextDom\Helpers\TranslateHelper::setLanguage('fr_FR');

$target = $argv[1];
if (file_exists($target)) {
    $content = file_get_contents($target);
    $translatedContent = \NextDom\Helpers\TranslateHelper::exec($content);
    file_put_contents($target, $translatedContent);
}
