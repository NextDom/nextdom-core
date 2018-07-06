<?php
/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Helpers;

/**
 * Aiguillage de l'affichage
 *
 * @package NextDom\Helper
 */
class Router
{
    /**
     * @var string Type de vue
     */
    private $viewType;

    /**
     * Constructeur initialisant le type de vue
     *
     * @param $_viewType string Type de vue
     */
    public function __construct($_viewType)
    {
        $this->viewType = $_viewType;
    }

    /**
     * Affichage du contenu demandé
     *
     * @return bool True si une réponse a été fournie.
     */
    public function show()
    {
        $result = false;
        if ($this->viewType == 'd') {
            $this->desktopView();
            $result = true;
        }
        elseif ($this->viewType == 'm') {
            $this->mobileView();
            $result = true;
        }
        return $result;
    }

    /**
     * Affichage pour un ordinateur
     *
     * @throws \Exception
     */
    function desktopView()
    {
        if (isset($_GET['modal'])) {
            $this->showModal();
        } elseif (isset($_GET['configure'])) {
            $this->showConfiguration();
        } elseif (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            $this->ajaxGetContent();
        } else {
            \include_file('desktop', 'index', 'php', '', true);
        }
    }

    /**
     * Affichage d'un modal sur ordinateur
     *
     * @throws \Exception
     */
    private function showModal() {
        try {
            \include_file('core', 'authentification', 'php');
            \include_file('desktop', init('modal'), 'modal', \init('plugin'), true);
        } catch (\Exception $e) {
            ob_end_clean();
            echo '<div class="alert alert-danger div_alert">';
            echo \translate::exec(\displayException($e), 'desktop/' . \init('p') . '.php');
            echo '</div>';
        } catch (\Error $e) {
            ob_end_clean();
            echo '<div class="alert alert-danger div_alert">';
            echo \translate::exec(\displayException($e), 'desktop/' . \init('p') . '.php');
            echo '</div>';
        }
    }

    /**
     * Affichage de la page de configuration d'un plugin
     *
     * @throws \Exception Affichage
     */
    private function showConfiguration() {
        \include_file('core', 'authentification', 'php');
        \include_file('plugin_info', 'configuration', 'configuration', init('plugin'));
    }

    /**
     * Réponse à une requête Ajax
     *
     * @throws \Exception
     */
    private function ajaxGetContent() {
        try {
            \include_file('core', 'authentification', 'php');
            \include_file('desktop', init('p'), 'php', init('m'), true);
        } catch (\Exception $e) {
            ob_end_clean();
            echo '<div class="alert alert-danger div_alert">';
            echo \translate::exec(displayException($e), 'desktop/' . init('p') . '.php');
            echo '</div>';
        } catch (\Error $e) {
            ob_end_clean();
            echo '<div class="alert alert-danger div_alert">';
            echo \translate::exec(displayException($e), 'desktop/' . init('p') . '.php');
            echo '</div>';
        }
    }

    /**
     * Affichage de la vue mobile
     *
     * @throws \Exception
     */
    private function mobileView() {
        $filename = 'index';
        $type = 'html';
        $plugin = '';
        $modal = \init('modal', false);
        if ($modal !== false) {
            $filename = $modal;
            $type = 'modalhtml';
            $plugin = \init('plugin');
        } elseif (isset($_GET['p']) && isset($_GET['ajax'])) {
            $filename = $_GET['p'];
            $plugin = isset($_GET['m']) ? $_GET['m'] : $plugin;
        }
        \include_file('mobile', $filename, $type, $plugin);
    }
}