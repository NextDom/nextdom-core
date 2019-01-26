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

use NextDom\Helpers\LogHelper;

require_once __DIR__ . '/../../core/php/core.inc.php';

class log {
    const DEFAULT_MAX_LINE = 200;

    public static function getConfig($_key, $_default = '') {
        return LogHelper::getConfig($_key, $_default);
    }

    public static function getLogger($_log) {
        return LogHelper::getLogger($_log);
    }

    public static function getLogLevel($_log) {
        return LogHelper::getLogLevel($_log);
    }

    public static function convertLogLevel($_level = 100) {
        return LogHelper::convertLogLevel($_level);
    }

    /**
     * Ajoute un message dans les log et fait en sorte qu'il n'y
     * ai jamais plus de 1000 lignes
     * @param string $_type type du message à mettre dans les log
     * @param string $_message message à mettre dans les logs
     */
    public static function add($_log, $_type, $_message, $_logicalId = '') {
        LogHelper::add($_log, $_type, $_message, $_logicalId);
    }

    public static function chunk($_log = '') {
        LogHelper::chunk($_log);
    }

    public static function chunkLog($_path) {
        LogHelper::chunkLog($_path);
    }

    public static function getPathToLog($_log = 'core') {
        return LogHelper::getPathToLog($_log);
    }

    /**
     * Autorisation de vide le fichier de log
     */
    public static function authorizeClearLog($_log, $_subPath = '') {
        return LogHelper::authorizeClearLog($_log, $_subPath);
    }

    /**
     * Vide le fichier de log
     */
    public static function clear($_log) {
        return LogHelper::clear($_log);
    }

    /**
     * Vide le fichier de log
     */
    public static function remove($_log) {
        return LogHelper::remove($_log);
    }

    public static function removeAll() {
        return LogHelper::removeAll();
    }

    public static function get($_log = 'core', $_begin, $_nbLines) {
        return LogHelper::get($_log, $_begin, $_nbLines);
    }

    public static function liste($_filtre = null) {
        return LogHelper::liste($_filtre);
    }

    /**
     * Fixe le niveau de rapport d'erreurs PHP
     * @param int $log_level
     * @since 2.1.4
     * @author KwiZeR <kwizer@kw12er.com>
     */
    public static function define_error_reporting($log_level) {
        LogHelper::define_error_reporting($log_level);
    }

    public static function exception($e) {
        return LogHelper::exception($e);
    }

    /*     * *********************Methode d'instance************************* */

    /*     * **********************Getteur Setteur*************************** */
}
