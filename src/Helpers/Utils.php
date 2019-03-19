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
use NextDom\Managers\UserManager;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class Utils
{
    private static $properties = array();

    /**
     * Add javascript variable in HTML code
     *
     * @param string $varName Name of javascript variable
     * @param mixed $varValue Value of the javascript variable
     */
    public static function sendVarToJs(string $varName, $varValue)
    {
        echo "<script>" . self::getVarInJs($varName, $varValue) . "</script>\n";
    }

    /**
     * Add list of javascript variables in HTML code
     *
     * @param array $listOfVarsWithValues Variables associative array 'name' => 'value'
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
     * Convert variable in javascript format
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

    /**
     * Encode at JSON format for javascript
     * @param mixed $varToTransform Variable to transform
     * @return string Encoded string for javascript
     */
    public static function getArrayToJQueryJson($varToTransform): string
    {
        return 'jQuery.parseJSON("' . addslashes(json_encode($varToTransform, JSON_UNESCAPED_UNICODE)) . '")';
    }

    /**
     * Redirect to target url
     *
     * @param string $url Target url
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
    public static function transformExpressionForEvaluation($expression)
    {

        $result = $expression;
        $replaceMap = [
            '==' => '==',
            '=' => '==',
            '>=' => '>=',
            '<=' => '<=',
            '<==' => '<=',
            '>==' => '>=',
            '===' => '==',
            '!==' => '!=',
            '!=' => '!=',
            'OR' => '||',
            'OU' => '||',
            'or' => '||',
            'ou' => '||',
            '||' => '||',
            'AND' => '&&',
            'ET' => '&&',
            'and' => '&&',
            'et' => '&&',
            '&&' => '&&',
            '<' => '<',
            '>' => '>',
            '/' => '/',
            '*' => '*',
            '+' => '+',
            '-' => '-',
            '' => ''
        ];
        preg_match_all('/(\w+|-?(?:\d+\\.\d+|\\.?\d+)|".*?"|\'.*?\'|\#.*?\#|\(|,|\)|!) *([!*+&|\\-\\/>=<]+|and|or|ou|et)* */i', $expression, $pregOutput);
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
     * @param CoreException|\Exception $e
     * @return string
     * @throws \Exception
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

    public static function isJson($_string, $_default = null)
    {
        if ($_string === null) {
            return $_default;
        }
        if ($_default !== null) {
            if (!is_string($_string)) {
                return $_default;
            }
            $return = json_decode($_string, true, 512, JSON_BIGINT_AS_STRING);
            if (!is_array($return)) {
                return $_default;
            }
            return $return;
        }
        return ((is_string($_string) && is_array(json_decode($_string, true, 512, JSON_BIGINT_AS_STRING)))) ? true : false;
    }

    /**
     * @param $string
     * @return null|string|string[]
     */
    public static function br2nl($string)
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    }

    public static function calculPath($_path)
    {
        if (strpos($_path, '/') !== 0) {
            return NEXTDOM_ROOT . '/' . $_path;
        }
        return $_path;
    }

    public static function sizeFormat($size)
    {
        $mod = 1024;
        $units = explode(' ', 'B KB MB GB TB PB');
        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Convert object of type to another
     *
     * @param mixed $sourceObject Source object
     * @param string $destinationClassName Destination class name
     *
     * @return mixed Object of destinationClassName type
     */
    public static function cast($sourceObject, $destinationClassName)
    {
        $sourceClassName = get_class($sourceObject);
        $sourceSerializedPrefix = 'O:' . strlen($sourceClassName) . ':"' . $sourceClassName . '"';
        $destinationSerializedPrefix = 'O:' . strlen($destinationClassName) . ':"' . $destinationClassName . '"';
        $serializedObject = serialize($sourceObject);
        return unserialize(str_replace($sourceSerializedPrefix, $destinationSerializedPrefix, $serializedObject));
    }

    /**
     * TODO: Stocker la version évaluée
     *
     * @param $_string
     * @return string
     */
    public static function evaluate($_string)
    {
        if (!isset($GLOBALS['ExpressionLanguage'])) {
            $GLOBALS['ExpressionLanguage'] = new ExpressionLanguage();
        }

        $expr = Utils::transformExpressionForEvaluation($_string);

        try {
            return $GLOBALS['ExpressionLanguage']->evaluate($expr);
        } catch (\Exception $e) {
            //log::add('expression', 'debug', '[Parser 1] Expression : ' . $_string . ' tranformé en ' . $expr . ' => ' . $e->getMessage());
        }
        try {
            $expr = str_replace('""', '"', $expr);
            return $GLOBALS['ExpressionLanguage']->evaluate($expr);
        } catch (\Exception $e) {
            //log::add('expression', 'debug', '[Parser 2] Expression : ' . $_string . ' tranformé en ' . $expr . ' => ' . $e->getMessage());
        }
        return $_string;
    }

    /**
     * @param string $_string
     *
     * @return string
     */
    public static function secureXSS($_string)
    {
        if ($_string === null) {
            return null;
        }
        return str_replace('&amp;', '&', htmlspecialchars(strip_tags($_string), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    public static function minify($_buffer)
    {
        $search = array(
            '/\>[^\S ]+/s', // strip whitespaces after tags, except space
            '/[^\S ]+\</s', // strip whitespaces before tags, except space
            '/(\s)+/s', // shorten multiple whitespace sequences
        );
        $replace = array(
            '>',
            '<',
            '\\1',
        );
        return preg_replace($search, $replace, $_buffer);
    }

    public static function sanitizeAccent($_message)
    {
        $caracteres = array(
            'À' => 'a', 'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', '@' => 'a',
            'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', '€' => 'e',
            'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o',
            'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'µ' => 'u',
            'Œ' => 'oe', 'œ' => 'oe',
            '$' => 's');
        return preg_replace('#[^A-Za-z0-9 \n\.\'=\*:]+\#\)\(#', '', strtr($_message, $caracteres));
    }

    public static function getZipErrorMessage($code)
    {
        switch ($code) {
            case 0:
                return 'No error';

            case 1:
                return 'Multi-disk zip archives not supported';

            case 2:
                return 'Renaming temporary file failed';

            case 3:
                return 'Closing zip archive failed';

            case 4:
                return 'Seek error';

            case 5:
                return 'Read error';

            case 6:
                return 'Write error';

            case 7:
                return 'CRC error';

            case 8:
                return 'Containing zip archive was closed';

            case 9:
                return 'No such file';

            case 10:
                return 'File already exists';

            case 11:
                return 'Can\'t open file';

            case 12:
                return 'Failure to create temporary file';

            case 13:
                return 'Zlib error';

            case 14:
                return 'Malloc failure';

            case 15:
                return 'Entry has been changed';

            case 16:
                return 'Compression method not supported';

            case 17:
                return 'Premature EOF';

            case 18:
                return 'Invalid argument';

            case 19:
                return 'Not a zip archive';

            case 20:
                return 'Internal error';

            case 21:
                return 'Zip archive inconsistent';

            case 22:
                return 'Can\'t remove file';

            case 23:
                return 'Entry has been deleted';

            default:
                return 'An unknown error has occurred(' . intval($code) . ')';
        }
    }

    public static function arg2array($_string)
    {
        $return = array();
        $re = '/[\/-]?(([a-zA-Z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ_#]+)(?:[=:]("[^"]+"|[^\s"]+))?)(?:\s+|$)/';
        preg_match_all($re, $_string, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match) {
            if (count($match) != 4) {
                continue;
            }
            $return[$match[2]] = $match[3];
        }
        return $return;
    }

    public static function strToHex($string)
    {
        $hex = '';
        $calculateStrLen = strlen($string);
        for ($i = 0; $i < $calculateStrLen; $i++) {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0' . $hexCode, -2);
        }
        return strToUpper($hex);
    }

    public static function hexToRgb($hex)
    {
        $hex = str_replace("#", "", $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return array($r, $g, $b);
    }

    public static function getDominantColor($_pathimg)
    {
        $rTotal = 0;
        $gTotal = 0;
        $bTotal = 0;
        $total = 0;
        $i = imagecreatefromjpeg($_pathimg);
        $imagesX = imagesx($i);
        for ($x = 0; $x < $imagesX; $x++) {
            $imagesY = imagesy($i);
            for ($y = 0; $y < $imagesY; $y++) {
                $rgb = imagecolorat($i, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $rTotal += $r;
                $gTotal += $g;
                $bTotal += $b;
                $total++;
            }
        }
        return '#' . sprintf('%02x', round($rTotal / $total)) . sprintf('%02x', round($gTotal / $total)) . sprintf('%02x', round($bTotal / $total));
    }

    public static function sha512($_string)
    {
        return hash('sha512', $_string);
    }

    public static function findCodeIcon($_icon)
    {
        $icon = trim(str_replace(array('fa ', 'icon ', '></i>', '<i', 'class="', '"'), '', trim($_icon)));
        $re = '/.' . $icon . ':.*\n.*content:.*"(.*?)";/m';

        $css = file_get_contents(NEXTDOM_ROOT . '/vendor/node_modules/font-awesome/css/font-awesome.css');
        preg_match($re, $css, $matches);
        if (isset($matches[1])) {
            return array('icon' => trim($matches[1], '\\'), 'fontfamily' => 'FontAwesome');
        }

        foreach (ls(NEXTDOM_ROOT . '/public/icon', '*') as $dir) {
            if (is_dir(NEXTDOM_ROOT . '/public/icon/' . $dir) && file_exists(NEXTDOM_ROOT . '/public/icon/' . $dir . '/style.css')) {
                $css = file_get_contents(NEXTDOM_ROOT . '/public/icon/' . $dir . '/style.css');
                preg_match($re, $css, $matches);
                if (isset($matches[1])) {
                    return array('icon' => trim($matches[1], '\\'), 'fontfamily' => trim($dir, '/'));
                }
            }
        }
        return array('icon' => '', 'fontfamily' => '');
    }

    public static function addGraphLink($_from, $_from_type, $_to, $_to_type, &$_data, $_level, $_drill, $_display = array('dashvalue' => '5,3', 'lengthfactor' => 0.6))
    {
        if (is_array($_to) && count($_to) == 0) {
            return null;
        }
        if (!is_array($_to)) {
            if (!is_object($_to)) {
                return null;
            }
            $_to = array($_to);
        }
        foreach ($_to as $to) {
            $to->getLinkData($_data, $_level, $_drill);
            if (isset($_data['link'][$_to_type . $to->getId() . '-' . $_from_type . $_from->getId()])) {
                continue;
            }
            if (isset($_data['link'][$_from_type . $_from->getId() . '-' . $_to_type . $to->getId()])) {
                continue;
            }
            $_data['link'][$_to_type . $to->getId() . '-' . $_from_type . $_from->getId()] = array(
                'from' => $_to_type . $to->getId(),
                'to' => $_from_type . $_from->getId(),
            );
            $_data['link'][$_to_type . $to->getId() . '-' . $_from_type . $_from->getId()] = array_merge($_data['link'][$_to_type . $to->getId() . '-' . $_from_type . $_from->getId()], $_display);
        }
        return $_data;
    }

    public static function strContain($_string, $_words)
    {
        foreach ($_words as $word) {
            if (strpos($_string, $word) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function makeZipSupport()
    {
        $folder = '/tmp/nextdom_support';
        $outputfile = NEXTDOM_ROOT . '/support/nextdom_support_' . date('Y-m-d_His') . '.tar.gz';
        if (file_exists($folder)) {
            FileSystemHelper::rrmdir($folder);
        }
        mkdir($folder);
        system('cd /var/log/nextdom;cp -R * "' . $folder . '" > /dev/null;cp -R .[^.]* "' . $folder . '" > /dev/null');
        system('sudo dmesg >> ' . $folder . '/dmesg');
        system('sudo cp /var/log/messages "' . $folder . '/" > /dev/null');
        system('sudo chmod 777 -R "' . $folder . '" > /dev/null');
        system('cd ' . $folder . ';tar cfz "' . $outputfile . '" * > /dev/null;chmod 777 ' . $outputfile);
        FileSystemHelper::rrmdir($folder);
        return realpath($outputfile);
    }

    public static function unautorizedInDemo($_user = null)
    {
        if ($_user === null) {
            if (!isset($_SESSION) || !UserManager::getStoredUser() !== null) {
                return null;
            }
            $_user = UserManager::getStoredUser();
        }
        if (!is_object($_user)) {
            return;
        }
        if ($_user->getLogin() == 'demo') {
            throw new CoreException(__('Cette action n\'est pas autorisée en mode démo'));
        }
    }

    public static function o2a($_object, $_noToArray = false)
    {
        if (is_array($_object)) {
            $return = array();
            foreach ($_object as $object) {
                $return[] = self::o2a($object);
            }
            return $return;
        }
        $array = array();
        if (!is_object($_object)) {
            return $array;
        }
        if (!$_noToArray && method_exists($_object, 'toArray')) {
            return $_object->toArray();
        }
        $class = get_class($_object);
        if (!isset(self::$properties[$class])) {
            self::$properties[$class] = (new \ReflectionClass($class))->getProperties();
        }
        foreach (self::$properties[$class] as $property) {
            $name = $property->getName();
            if ('_' !== $name[0]) {
                $method = 'get' . ucfirst($name);
                if (method_exists($_object, $method)) {
                    $value = $_object->$method();
                } else {
                    $property->setAccessible(true);
                    $value = $property->getValue($_object);
                    $property->setAccessible(false);
                }
                $array[$name] = ($value === null) ? null : Utils::isJson($value, $value);
            }
        }
        return $array;
    }

    public static function a2o(&$_object, $_data)
    {
        if (is_array($_data)) {
            foreach ($_data as $key => $value) {
                $method = 'set' . ucfirst($key);
                if (method_exists($_object, $method)) {
                    $function = new \ReflectionMethod($_object, $method);
                    $value = Utils::isJson($value, $value);
                    if (is_array($value)) {
                        if ($function->getNumberOfRequiredParameters() == 2) {
                            foreach ($value as $arrayKey => $arrayValue) {
                                if (is_array($arrayValue)) {
                                    if ($function->getNumberOfRequiredParameters() == 3) {
                                        foreach ($arrayValue as $arrayArraykey => $arrayArrayvalue) {
                                            $_object->$method($arrayKey, $arrayArraykey, $arrayArrayvalue);
                                        }
                                        continue;
                                    }
                                }
                                $_object->$method($arrayKey, $arrayValue);
                            }
                        } else {
                            $_object->$method(json_encode($value, JSON_UNESCAPED_UNICODE));
                        }
                    } else {
                        if ($function->getNumberOfRequiredParameters() < 2) {
                            $_object->$method($value);
                        }
                    }
                }
            }
        }
    }

    public static function processJsonObject($_class, $_ajaxList, $_dbList = null)
    {
        if (!is_array($_ajaxList)) {
            if (Utils::isJson($_ajaxList)) {
                $_ajaxList = json_decode($_ajaxList, true);
            } else {
                throw new CoreException('Invalid json : ' . print_r($_ajaxList, true));
            }
        }
        if (!is_array($_dbList)) {
            if (!class_exists($_class)) {
                throw new CoreException('Invalid class : ' . $_class);
            }
            $_dbList = $_class::all();
        }

        $enableList = array();
        foreach ($_ajaxList as $ajaxObject) {
            $object = $_class::byId($ajaxObject['id']);
            if (!is_object($object)) {
                $object = new $_class();
            }
            self::a2o($object, $ajaxObject);
            $object->save();
            $enableList[$object->getId()] = true;
        }
        foreach ($_dbList as $dbObject) {
            if (!isset($enableList[$dbObject->getId()])) {
                $dbObject->remove();
            }
        }
    }

    /**
     * @param string|array $_attr
     * @param string|array $_key
     * @param null $_value
     * @return array|bool|mixed|null
     */
    public static function setJsonAttr($_attr, $_key, $_value = null)
    {
        if ($_value === null && !is_array($_key)) {
            if (!is_array($_attr)) {
                $_attr = Utils::isJson($_attr, array());
            }
            unset($_attr[$_key]);
        } else {
            if (!is_array($_attr)) {
                $_attr = Utils::isJson($_attr, array());
            }
            if (is_array($_key)) {
                $_attr = array_merge($_attr, $_key);
            } else {
                $_attr[$_key] = $_value;
            }
        }
        return $_attr;
    }

    public static function getJsonAttr(&$_attr, $_key = '', $_default = '')
    {
        if (is_array($_attr)) {
            if ($_key == '') {
                return $_attr;
            }
        } else {
            if ($_key == '') {
                return self::isJson($_attr, array());
            }
            if ($_attr === '') {
                if (is_array($_key)) {
                    foreach ($_key as $key) {
                        $return[$key] = $_default;
                    }
                    return $return;
                }
                return $_default;
            }
            $_attr = json_decode($_attr, true);
        }
        if (is_array($_key)) {
            $return = array();
            foreach ($_key as $key) {
                $return[$key] = (isset($_attr[$key]) && $_attr[$key] !== '') ? $_attr[$key] : $_default;
            }
            return $return;
        }
        return (isset($_attr[$_key]) && $_attr[$_key] !== '') ? $_attr[$_key] : $_default;
    }

    public static function attrChanged($_changed, $_old, $_new)
    {
        if ($_changed) {
            return true;
        }
        if (is_array($_old)) {
            $_old = json_encode($_old);
        }
        if (is_array($_new)) {
            $_new = json_encode($_new);
        }
        return ($_old != $_new);
    }

}
