<?php

if (count($argv) == 1) {
    echo "Utilisation : ".$argv[0]." FILE_TO_TRANSLATE\n";
    die();
}

require (__DIR__.'/../vendor/autoload.php');

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

$translator = new Translator('fr_FR', null, __DIR__.'/../var/i10n');
$translator->addLoader('yaml', new YamlFileLoader());
$translator->addResource('yaml', '../translations/fr_FR.yml', 'fr_FR');

$target = $argv[1];
if (file_exists($target)) {
    $content = file_get_contents($target);
    preg_match_all("/{{(.*?)}}/s", $content, $matches);
    $translationArray = [];
    foreach ($matches[1] as $toTranslate) {
        $translation = $translator->trans($toTranslate);
        if ($translation == '') {
            $translation = $toTranslate;
        }
        $translationArray['{{'.$toTranslate.'}}'] = $translation;
    }
    file_put_contents($target, str_replace(array_keys($translationArray), $translationArray, $content));
}
