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

namespace NextDom\Helper;

class Utils
{
    /**
     * Ajouter une variable Javascript au code HTML
     *
     * @param string $_varName Nom de la variable dans le code HTML
     * @param mixed $_value Valeur de la variable
     */
    public static function sendVarToJs($varName, $varValue)
    {
        echo "<script>" .
            self::getVarInJs($varName, $varValue) .
            "</script>\n";
    }

    /**
     * Ajouter une liste de variables Javascript au HTML
     *
     * @param array $listOfVarsWithValues Liste des variables 'nom' => 'valeur'
     */
    public static function sendVarsToJS(array $listOfVarsWithValues)
    {
        echo "<script>\n";
        foreach ($listOfVarsWithValues as $varName => $value) {
            echo self::getVarInJs($varName, $value)."\n";
        }
        echo "</script>\n";
    }

    /**
     * Prépare la déclaration d'une variable au format Javascript
     *
     * @param string $varName Nom de la variable
     * @param mixed $varValue Valeur
     *
     * @return string Déclaration javascript
     */
    private static function getVarInJs($varName, $varValue)
    {
        $jsVarValue = '';
        if (is_array($varValue)) {
            $jsVarValue = 'jQuery.parseJSON("' . addslashes(json_encode($varValue, JSON_UNESCAPED_UNICODE)) . '")';
        } else {
            $jsVarValue = '"' . $varValue . '"';
        }
        return "var $varName = $jsVarValue;";
    }

    /**
     * Rediriger vers un autre url
     *
     * @param $url URL cible
     * @param null $forceType Forcage si 'JS' TODO: ???
     */
    public static function redirect($url, $forceType = null)
    {
        if ($forceType == 'JS' || headers_sent() || isset($_GET['ajax'])) {
            echo '<script type="text/javascript">window.location.href="'.$url.'"</script>';
        } else {
            exit(header("Location: $url"));
        }
    }

    /**
     * Test si l'utilisateur est connecté avec certains droits
     *
     * @param string $rights Droits à tester (admin)
     *
     * @return boolean True si l'utilisateur est connecté avec les droits demandés
     */
    public static function isConnect($rights = '')
    {
        $rightsKey = 'isConnect::' . $rights;
        $isSetSessionUser = isset($_SESSION['user']);
        $result = false;

        if ($isSetSessionUser && isset($GLOBALS[$rightsKey]) && $GLOBALS[$rightsKey]) {
            $result = $GLOBALS[$rightsKey];
        } else {
            if (session_status() == PHP_SESSION_DISABLED || !$isSetSessionUser) {
                $result = false;
            } elseif ($isSetSessionUser && is_object($_SESSION['user']) && $_SESSION['user']->is_Connected()) {
                if ($rights !== '') {
                    if ($_SESSION['user']->getProfils() == $rights) {
                        $result = true;
                    }
                } else {
                    $result = true;
                }
            }
            $GLOBALS[$rightsKey] = $result;
        }
        return $result;
    }

    /**
     * Obtenir une variable passée en paramètre
     *
     * @param string $_name Nom de la variable
     * @param mixed $_default Valeur par défaut
     *
     * @return mixed Valeur de la variable
     */
    public static function init($_name, $_default = '')
    {
        if (isset($_GET[$_name])) {
            return $_GET[$_name];
        }
        if (isset($_POST[$_name])) {
            return $_POST[$_name];
        }
        if (isset($_REQUEST[$_name])) {
            return $_REQUEST[$_name];
        }
        return $_default;
    }

    /**
     * Obtenir le contenu d'un fichier template
     *
     * @param string $folder Répertoire dans lequel se trouve le fichier de template
     * @param string $version Version du template
     * @param string $filename Nom du fichier
     * @param string $pluginId Identifiant du plugin
     *
     * @return string Contenu du fichier ou une chaine vide.
     */
    public static function getTemplateFilecontent($folder, $version, $filename, $pluginId = '') {
        $result = '';
        $filePath = NEXTDOM_ROOT . '/plugins/' . $pluginId . '/core/template/' . $version . '/' . $filename . '.html';
        if ($pluginId == '') {
            $filePath = NEXTDOM_ROOT . '/' . $folder . '/template/' . $version . '/' . $filename . '.html';
        }
        if (file_exists($filePath)) {
            $result = file_get_contents($filePath);
        }
        return $result;
    }
}