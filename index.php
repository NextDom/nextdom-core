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

require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/core/php/utils.inc.php');

use NextDom\Helper\Status;

try {
    // Test si l'installation doit être lancée
    if (!file_exists(dirname(__FILE__) . '/core/config/common.config.php')) {
        header("location: install/setup.php");
    }

    if (!isset($_GET['v'])) {
        $getParams = 'd';
        if (\NextDom\ClientHelper::isMobile()) {
            $getParams = 'm';
        }
        foreach ($_GET AS $var => $value) {
            $getParams .= '&' . $var . '=' . $value;
        }
        $url = 'index.php?v=' . trim($getParams, '&');
        redirect($url);
        die();
    }
    // Configuration des variables d'état
    require_once __DIR__ . "/core/php/core.inc.php";
    Status::initRescueModeState();
    $vParam = init('v', '');
    if ($vParam == 'd') {
        if (isset($_GET['modal'])) {
            try {
                include_file('core', 'authentification', 'php');
                include_file('desktop', init('modal'), 'modal', init('plugin'));
            } catch (Exception $e) {
                ob_end_clean();
                echo '<div class="alert alert-danger div_alert">';
                echo translate::exec(displayException($e), 'desktop/' . init('p') . '.php');
                echo '</div>';
            } catch (Error $e) {
                ob_end_clean();
                echo '<div class="alert alert-danger div_alert">';
                echo translate::exec(displayException($e), 'desktop/' . init('p') . '.php');
                echo '</div>';
            }
        } elseif (isset($_GET['configure'])) {
            include_file('core', 'authentification', 'php');
            include_file('plugin_info', 'configuration', 'configuration', init('plugin'));
        } elseif (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            try {
                include_file('core', 'authentification', 'php');
                include_file('desktop', init('p'), 'php', init('m'));
            } catch (Exception $e) {
                ob_end_clean();
                echo '<div class="alert alert-danger div_alert">';
                echo translate::exec(displayException($e), 'desktop/' . init('p') . '.php');
                echo '</div>';
            } catch (Error $e) {
                ob_end_clean();
                echo '<div class="alert alert-danger div_alert">';
                echo translate::exec(displayException($e), 'desktop/' . init('p') . '.php');
                echo '</div>';
            }
        } else {
            include_file('desktop', 'index', 'php');
        }
    } elseif ($vParam == 'm') {
        $_fn = 'index';
        $_type = 'html';
        $_plugin = '';
        if (isset($_GET['modal'])) {
            $_fn = init('modal');
            $_type = 'modalhtml';
            $_plugin = init('plugin');
        } elseif (isset($_GET['p']) && isset($_GET['ajax'])) {
            $_fn = $_GET['p'];
            $_plugin = isset($_GET['m']) ? $_GET['m'] : $_plugin;
        }
        include_file('mobile', $_fn, $_type, $_plugin);
    } else {
        echo "Erreur : veuillez contacter l'administrateur";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
