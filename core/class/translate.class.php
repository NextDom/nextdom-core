<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */

use NextDom\Helpers\TranslateHelper;

require_once dirname(__FILE__) . '/../php/core.inc.php';

class translate
{
    /*     * ***********************Methode static*************************** */

    public static function getConfig($_key, $_default = '')
    {
        return TranslateHelper::getConfig($_key, $_default);
    }

    public static function getTranslation()
    {
        return TranslateHelper::getTranslation();
    }

    public static function sentence($_content, $_name, $_backslash = false)
    {
        return TranslateHelper::sentence($_content, $_name, $_backslash);
    }

    public static function exec($_content, $_name = '', $_backslash = false)
    {
        return TranslateHelper::exec($_content, $_name, $_backslash);
    }

    public static function getPluginFromName($_name)
    {
        return TranslateHelper::getPluginFromName($_name);
    }

    public static function getPathTranslationFile($_language)
    {
        return NEXTDOM_ROOT . '/i18n/' . $_language . '.json';
    }

    public static function loadTranslation()
    {
        return TranslateHelper::loadTranslation();
    }

    public static function saveTranslation()
    {
        TranslateHelper::saveTranslation();
    }

    public static function getLanguage()
    {
        return TranslateHelper::getLanguage();
    }

    public static function setLanguage($_langage)
    {
        TranslateHelper::setLanguage($_langage);
    }
}

if (!function_exists('__')) {
    function __(string $_content, string $_name = '', bool $_backslash = false): string
    {
        return TranslateHelper::sentence($_content, $_name, $_backslash);
    }
}
