<?php

use NextDom\Ajax\TranslationsAjax;

require_once (__DIR__ . '/../../src/core.php');

  echo (new TranslationsAjax())->sendTranslations();
