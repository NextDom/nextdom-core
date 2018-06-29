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
require_once dirname(__FILE__) . '/../php/core.inc.php';

class translate {
    /*     * ***********************Methode static*************************** */

    public static function getConfig($_key, $_default = '') {
        return \NextDom\Helpers\Translate::getConfig($_key, $_default);
    }

    public static function getTranslation() {
        return \NextDom\Helpers\Translate::getTranslation();
    }

    public static function sentence($_content, $_name, $_backslash = false) {
        return \NextDom\Helpers\Translate::sentence($_content, $_name, $_backslash);
    }

    public static function exec($_content, $_name = '', $_backslash = false) {
        return \NextDom\Helpers\Translate::exec($_content, $_name, $_backslash);
    }

    public static function getPathTranslationFile($_language) {
        //TODO: PUBLIC ????
        return dirname(__FILE__) . '/../i18n/' . $_language . '.json';
    }

    public static function loadTranslation() {
        return \NextDom\Helpers\Translate::loadTranslation();
    }

    public static function saveTranslation() {
        \NextDom\Helpers\Translate::saveTranslation();
    }

    public static function getLanguage() {
        return \NextDom\Helpers\Translate::getLanguage();
    }

    public static function setLanguage($_langage) {
        \NextDom\Helpers\Translate::setLanguage($_langage);
    }

    /*     * *********************Methode d'instance************************* */
}

function __(string $_content, string $_name = '', bool $_backslash = false): string {
    return \NextDom\Helpers\Translate::sentence($_content, $_name, $_backslash);
}
