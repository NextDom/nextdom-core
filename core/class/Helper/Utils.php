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
    public static function sendVarToJs($varName, $varValue) {
        echo "<script>\n";
        echo self::getVarInJs($varName, $varValue);
        echo "</script>\n";
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
            self::getVarInJs($varName, $value);
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
    private static function getVarInJs($varName, $varValue) {
        $jsVarValue = '';
        if (is_array($varValue)) {
            $jsVarValue = 'jQuery.parseJSON("' . addslashes(json_encode($varValue, JSON_UNESCAPED_UNICODE)) . '")';
        } else {
            $jsVarValue = '"' . $varValue . '"';
        }
        return "var $varName = $jsVarValue;\n";
    }
}