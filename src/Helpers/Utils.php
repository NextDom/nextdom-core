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

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Helpers;

use NextDom\Exceptions\CoreException;

class Utils
{
    /**
     * Ajouter une variable Javascript au code HTML
     *
     * @param string $varName Nom de la variable dans le code HTML
     * @param mixed $varValue Valeur de la variable
     */
    public static function sendVarToJs(string $varName, $varValue)
    {
        echo self::getVarToJs($varName, $varValue);
    }

    public static function getVarToJs(string $varName, $varValue)
    {
        return "<script>" . self::getVarInJs($varName, $varValue) .  "</script>\n";
    }

    /**
     * Ajouter une liste de variables Javascript au HTML
     *
     * TODO: Merger les 2 avecs un test
     * @param array $listOfVarsWithValues Liste des variables 'nom' => 'valeur'
     */
    public static function sendVarsToJS(array $listOfVarsWithValues)
    {
        echo self::getVarsToJS($listOfVarsWithValues);
    }

    /**
     * Get HTML code of list a javascript variables.
     *
     * @param array $listOfVarsWithValues
     * @return string
     */
    public static function getVarsToJS(array $listOfVarsWithValues)
    {
        $result = "<script>\n";
        foreach ($listOfVarsWithValues as $varName => $value) {
            $result .= self::getVarInJs($varName, $value) . "\n";
        }
        $result .= "</script>\n";
        return $result;
    }

    /**
     * Prépare la déclaration d'une variable au format Javascript
     *
     * @param string $varName Nom de la variable
     * @param mixed $varValue Valeur
     *
     * @return string Déclaration javascript
     */
    private static function getVarInJs(string $varName, $varValue): string
    {
        $jsVarValue = '';
        if (is_array($varValue)) {
            $jsVarValue = 'jQuery.parseJSON("' . addslashes(json_encode($varValue, JSON_UNESCAPED_UNICODE)) . '")';
        } else {
            $jsVarValue = '"' . $varValue . '"';
        }
        return "var $varName = $jsVarValue;";
    }

    public static function getArrayToJQueryJson($varToTransform) {
        return 'jQuery.parseJSON("' . addslashes(json_encode($varToTransform, JSON_UNESCAPED_UNICODE)) . '")';
    }
    /**
     * Rediriger vers un autre url
     *
     * @param string $url URL cible
     * @param null $forceType Forcage si 'JS' TODO: ???
     */
    public static function redirect(string $url, $forceType = null)
    {
        if ($forceType == 'JS' || headers_sent() || isset($_GET['ajax'])) {
            echo '<script type="text/javascript">window.location.href="' . $url . '"</script>';
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
    public static function isConnect(string $rights = ''): bool
    {
        $rightsKey        = 'isConnect::' . $rights;
        $isSetSessionUser = isset($_SESSION['user']);
        $result           = false;

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
     * @param string $name Nom de la variable
     * @param mixed $default Valeur par défaut
     *
     * @return mixed Valeur de la variable
     */
    public static function init(string $name, $default = '')
    {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }
        if (isset($_REQUEST[$name])) {
            return $_REQUEST[$name];
        }
        return $default;
    }

    /**
     * Transforme une expression lisible en une expression analysable
     *
     * @param string $expression Expression lisible
     *
     * @return string Expression transformée
     */
    public static function transformExpressionForEvaluation(string $expression): string
    {

        $result = $expression;
        $replaceMap = [
            '=='  => '==',
            '='   => '==',
            '>='  => '>=',
            '<='  => '<=',
            '<==' => '<=',
            '>==' => '>=',
            '===' => '==',
            '!==' => '!=',
            '!='  => '!=',
            'OR'  => '||',
            'OU'  => '||',
            'or'  => '||',
            'ou'  => '||',
            '||'  => '||',
            'AND' => '&&',
            'ET'  => '&&',
            'and' => '&&',
            'et'  => '&&',
            '&&'  => '&&',
            '<'   => '<',
            '>'   => '>',
            '/'   => '/',
            '*'   => '*',
            '+'   => '+',
            '-'   => '-',
            ''    => ''
        ];
        preg_match_all('/(\w+|\d+|\.\d+|".*?"|\'.*?\'|\#.*?\#|\(|\))[ ]*([!*+&|\\-\\/>=<]+|and|or|ou|et)*[ ]*/i', $expression, $pregOutput);
        if (count($pregOutput) > 2) {
            $result = '';
            $exprIndex = 0;
            foreach ($pregOutput[1] as $expr) {
                $result .= $expr . $replaceMap[$pregOutput[2][$exprIndex++]];
            }
        }
        return $result;
    }

    public static function templateReplace($_array, $_subject)
    {
        return str_replace(array_keys($_array), array_values($_array), $_subject);
    }

    public static function resizeImage($contents, $width, $height)
    {
// Cacul des nouvelles dimensions
        $width_orig = imagesx($contents);
        $height_orig = imagesy($contents);
        $ratio_orig = $width_orig / $height_orig;
        $test = $width / $height > $ratio_orig;
        $dest_width = $test ? ceil($height * $ratio_orig) : $width;
        $dest_height = $test ? $height : ceil($width / $ratio_orig);

        $dest_image = imagecreatetruecolor($width, $height);
        $wh = imagecolorallocate($dest_image, 0xFF, 0xFF, 0xFF);
        imagefill($dest_image, 0, 0, $wh);

        $offcet_x = ($width - $dest_width) / 2;
        $offcet_y = ($height - $dest_height) / 2;
        if ($dest_image && $contents) {
            if (!imagecopyresampled($dest_image, $contents, $offcet_x, $offcet_y, 0, 0, $dest_width, $dest_height, $width_orig, $height_orig)) {
                error_log("Error image copy resampled");
                return false;
            }
        }
// start buffering
        ob_start();
        imagejpeg($dest_image);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    public static function getMicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public static function convertDuration($time)
    {
        $result = '';
        $unities = array('j' => 86400, 'h' => 3600, 'min' => 60);
        foreach ($unities as $unity => $value) {
            if ($time >= $value || $result != '') {
                $result .= floor($time / $value) . $unity . ' ';
                $time %= $value;
            }
        }

        $result .= $time . 's';
        return $result;
    }

    /**
     * @return bool
     */
    public static function connectedToDatabase()
    {
        require_once NEXTDOM_ROOT . '/core/class/DB.class.php';
        return is_object(\DB::getConnection());
    }

    /**
     * @param CoreException $e
     * @return string
     */
    public static function displayException($e)
    {
        $message = '<span id="span_errorMessage">' . $e->getMessage() . '</span>';
        if (DEBUG) {
            $message .= '<a class="pull-right bt_errorShowTrace cursor">' . __('Show traces') . '</a>';
            $message .= '<br/><pre class="pre_errorTrace" style="display : none;">' . print_r($e->getTrace(), true) . '</pre>';
        }
        return $message;
    }

    public static function isSha1($_string = '')
    {
        if ($_string == '') {
            return false;
        }
        return preg_match('/^[0-9a-f]{40}$/i', $_string);
    }

    public static function isSha512($_string = '')
    {
        if ($_string == '') {
            return false;
        }
        return preg_match('/^[0-9a-f]{128}$/i', $_string);
    }

    public static function cleanPath($path)
    {
        $out = array();
        foreach (explode('/', $path) as $i => $fold) {
            if ($fold == '' || $fold == '.') {
                continue;
            }

            if ($fold == '..' && $i > 0 && end($out) != '..') {
                array_pop($out);
            } else {
                $out[] = $fold;
            }

        }
        return ($path{0} == '/' ? '/' : '') . join('/', $out);
    }

    public static function getRootPath()
    {
        return NEXTDOM_ROOT;
    }

    /**
     * got from https://github.com/zendframework/zend-stdlib/issues/58
     *
     * @param $pattern
     * @param $flags
     * @return array|false
     */
    public static function polyfillGlobBrace($pattern, $flags)
    {
        static $next_brace_sub;
        if (!$next_brace_sub) {
            // Find the end of the sub-pattern in a brace expression.
            $next_brace_sub = function ($pattern, $current) {
                $length = strlen($pattern);
                $depth = 0;

                while ($current < $length) {
                    if ('\\' === $pattern[$current]) {
                        if (++$current === $length) {
                            break;
                        }
                        $current++;
                    } else {
                        if (('}' === $pattern[$current] && $depth-- === 0) || (',' === $pattern[$current] && 0 === $depth)) {
                            break;
                        } elseif ('{' === $pattern[$current++]) {
                            $depth++;
                        }
                    }
                }

                return $current < $length ? $current : null;
            };
        }

        $length = strlen($pattern);

        // Find first opening brace.
        for ($begin = 0; $begin < $length; $begin++) {
            if ('\\' === $pattern[$begin]) {
                $begin++;
            } elseif ('{' === $pattern[$begin]) {
                break;
            }
        }

        // Find comma or matching closing brace.
        if (null === ($next = $next_brace_sub($pattern, $begin + 1))) {
            return glob($pattern, $flags);
        }

        $rest = $next;

        // Point `$rest` to matching closing brace.
        while ('}' !== $pattern[$rest]) {
            if (null === ($rest = $next_brace_sub($pattern, $rest + 1))) {
                return glob($pattern, $flags);
            }
        }

        $paths = array();
        $p = $begin + 1;

        // For each comma-separated subpattern.
        do {
            $subpattern = substr($pattern, 0, $begin)
                . substr($pattern, $p, $next - $p)
                . substr($pattern, $rest + 1);

            if (($result = self::polyfillGlobBrace($subpattern, $flags))) {
                $paths = array_merge($paths, $result);
            }

            if ('}' === $pattern[$next]) {
                break;
            }

            $p = $next + 1;
            $next = $next_brace_sub($pattern, $p);
        } while (null !== $next);

        return array_values(array_unique($paths));
    }

    public static function globBrace($pattern, $flags = 0)
    {
        if (defined("GLOB_BRACE")) {
            return glob($pattern, $flags + GLOB_BRACE);
        } else {
            return self::polyfillGlobBrace($pattern, $flags);
        }
    }

    public static function removeCR($_string)
    {
        return trim(str_replace(array("\n", "\r\n", "\r", "\n\r"), '', $_string));
    }
}
