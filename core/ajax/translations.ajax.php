<?php

use NextDom\Ajax\TranslationsAjax;

require_once (__DIR__ . '/../../src/core.php');

try {
    echo (new TranslationsAjax())->allTranslations();
} catch (Exception $e) {
    echo $e->getMessage();
}

