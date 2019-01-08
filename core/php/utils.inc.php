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

use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SessionHelper;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use NextDom\Helpers\Utils;

function include_file($_folder, $_filename, $_type, $_plugin = '', $translate = false)
{
    FileSystemHelper::includeFile($_folder, $_filename, $_type, $_plugin, $translate);
}

function getTemplate($_folder, $_version, $_filename, $_plugin = '')
{
    return FileSystemHelper::getTemplateFilecontent($_folder, $_version, $_filename, $_plugin);
}

function template_replace($_array, $_subject)
{
    return Utils::templateReplace($_array, $_subject);
}

function init($_name, $_default = '')
{
    return Utils::init($_name, $_default);
}

function sendVarToJS($_varName, $_value)
{
    Utils::sendVarToJs($_varName, $_value);
}

function resizeImage($contents, $width, $height)
{
    return Utils::resizeImage($contents, $width, $height);
}

function getmicrotime()
{
    return Utils::getMicrotime();
}

function redirect($_url, $_forceType = null)
{
    Utils::redirect($_url, $_forceType);
}

function convertDuration($time)
{
    return Utils::convertDuration($time);
}

function getClientIp()
{
    return NetworkHelper::getClientIp();
}

function mySqlIsHere()
{
    return Utils::connectedToDatabase();
}

function displayExeption($e)
{
    trigger_error('La fonction displayExeption devient displayException', E_USER_DEPRECATED);
    return Utils::displayException($e);
}

function displayException($e)
{
    return Utils::displayException($e);
}

function is_json($_string, $_default = null)
{
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

function is_sha1($_string = '')
{
    return Utils::isSha1($_string);
}

function is_sha512($_string = '')
{
    return Utils::isSha512($_string);
}

function cleanPath($path)
{
    return Utils::cleanPath($path);
}

function getRootPath()
{
    return Utils::getRootPath();
}

function hadFileRight($_allowPath, $_path)
{
    return FileSystemHelper::hadFileRight($_allowPath, $_path);
}

/**
 * Obtenir la version de NextDom installée
 *
 * TODO: Supprimer l'argument ?
 *
 * @param string $_name Nom de ?
 * @return string Version de NextDom
 */
function getVersion($_name)
{
    return NextDomHelper::getJeedomVersion();
}

function polyfill_glob_brace($pattern, $flags)
{
    return Utils::polyfillGlobBrace($pattern, $flags);
}

function glob_brace($pattern, $flags = 0)
{
    return Utils::globBrace($pattern, $flags);
}

function ls($folder = "", $pattern = "*", $recursivly = false, $options = array('files', 'folders'))
{
    return FileSystemHelper::ls($folder, $pattern, $recursivly, $options);
}

function removeCR($_string)
{
    return Utils::removeCR($_string);
}

function rcopy($src, $dst, $_emptyDest = true, $_exclude = array(), $_noError = false, $_params = array())
{
    return FileSystemHelper::rcopy($src, $dst, $_emptyDest, $_exclude, $_noError, $_params);
}

function rmove($src, $dst, $_emptyDest = true, $_exclude = array(), $_noError = false, $_params = array()) {
    return FileSystemHelper::rmove($src, $dst, $_emptyDest, $_exclude, $_noError, $_params);
}

// removes files and non-empty directories
function rrmdir($dir) {
    return FileSystemHelper::rrmdir($dir);
}

function date_fr($date_en) {
    if (config::byKey('language', 'core', 'fr_FR') == 'en_US') {
        return $date_en;
    }
    $texte_long_en = array(
        "Monday", "Tuesday", "Wednesday", "Thursday",
        "Friday", "Saturday", "Sunday", "January",
        "February", "March", "April", "May",
        "June", "July", "August", "September",
        "October", "November", "December",
    );
    $texte_short_en = array(
        "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun",
        "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul",
        "Aug", "Sep", "Oct", "Nov", "Dec",
    );

    switch (config::byKey('language', 'core', 'fr_FR')) {
        case 'fr_FR':
            $texte_long = array(
                "Lundi", "Mardi", "Mercredi", "Jeudi",
                "Vendredi", "Samedi", "Dimanche", "Janvier",
                "Février", "Mars", "Avril", "Mai",
                "Juin", "Juillet", "Août", "Septembre",
                "Octobre", "Novembre", "Décembre",
            );
            $texte_short = array(
                "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim",
                "Jan", "Fev", "Mar", "Avr", "Mai", "Jui",
                "Jui", "Aou;", "Sep", "Oct", "Nov", "Dec",
            );
            break;
        case 'de_DE':
            $texte_long = array(
                "Montag", "Dienstag", "Mittwoch", "Donnerstag",
                "Freitag", "Samstag", "Sonntag", "Januar",
                "Februar", "März", "April", "May",
                "Juni", "July", "August", "September",
                "October", "November", "December",
            );

            $texte_short = array(
                "Mon", "Die", "Mit", "Thu", "Don", "Sam", "Son",
                "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul",
                "Aug", "Sep", "Oct", "Nov", "Dec",
            );
            break;
        default:
            return $date_en;
            break;
    }
    return str_replace($texte_short_en, $texte_short, str_replace($texte_long_en, $texte_long, $date_en));
}

function convertDayEnToFr($_day) {
    trigger_error('La fonction convertDayEnToFr devient convertDayFromEn', E_USER_DEPRECATED);
    return convertDayFromEn($_day);
}

function convertDayFromEn($_day) {
    $result = $_day;
    $daysMapping = array(
        'fr_FR' => array(
            'Monday' => 'Lundi', 'Mon' => 'Lundi',
            'monday' => 'lundi', 'mon' => 'lundi',
            'Tuesday' => 'Mardi', 'Tue' => 'Mardi',
            'tuesday' => 'mardi', 'tue' => 'mardi',
            'Wednesday' => 'Mercredi', 'Wed' => 'Mercredi',
            'wednesday' => 'mercredi', 'wed' => 'mercredi',
            'Thursday' => 'Jeudi', 'Thu' => 'Jeudi',
            'thursday' => 'jeudi', 'thu' => 'jeudi',
            'Friday' => 'Vendredi', 'Fri' => 'Vendredi',
            'friday' => 'vendredi', 'fri' => 'vendredi',
            'Saturday' => 'Samedi', 'Sat' => 'Samedi',
            'saturday' => 'samedi', 'sat' => 'samedi',
            'Sunday' => 'Dimanche', 'Sun' => 'Dimanche',
            'sunday' => 'dimanche', 'sun' => 'dimanche',
        ),
        'de_DE' => array(
            'Monday' => 'Montag', 'Mon' => 'Montag',
            'monday' => 'montag', 'mon' => 'montag',
            'Tuesday' => 'Dienstag', 'Tue' => 'Dienstag',
            'tuesday' => 'dienstag', 'tue' => 'dienstag',
            'Wednesday' => 'Mittwoch', 'Wed' => 'Mittwoch',
            'wednesday' => 'mittwoch', 'wed' => 'mittwoch',
            'Thursday' => 'Donnerstag', 'Thu' => 'Donnerstag',
            'thursday' => 'donnerstag', 'thu' => 'donnerstag',
            'Friday' => 'Freitag', 'Fri' => 'Freitag',
            'friday' => 'freitag', 'fri' => 'freitag',
            'Saturday' => 'Samstag', 'Sat' => 'Samstag',
            'saturday' => 'samstag', 'sat' => 'samstag',
            'Sunday' => 'Sonntag', 'Sun' => 'Sonntag',
            'sunday' => 'sonntag', 'sun' => 'sonntag',
        ),
    );
    $language = config::byKey('language', 'core', 'fr_FR');
    if (array_key_exists($language, $daysMapping)) {
        $daysArray = $daysMapping[$language];
        if (array_key_exists($_day, $daysArray)) {
            $result = $daysArray[$_day];
        }
    }

    return $result;
}

function create_zip($source_arr, $destination, $_excludes = array())
{
    if (is_string($source_arr)) {
        $source_arr = array($source_arr);
    }
    if (!extension_loaded('zip')) {
        throw new Exception('Extension php ZIP non chargée');
    }
    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        throw new Exception('Impossible de créer l\'archive ZIP dans le dossier de destination : ' . $destination);
    }
    foreach ($source_arr as $source) {
        if (!file_exists($source)) {
            continue;
        }
        $source = str_replace('\\', '/', realpath($source));
        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file) {
                if (strpos($file, $source) === false) {
                    continue;
                }
                if ($file == $source . '/.' || $file == $source . '/..' || in_array(basename($file), $_excludes) || in_array(realpath($file), $_excludes)) {
                    continue;
                }
                foreach ($_excludes as $exclude) {
                    if (strpos($file, trim('/' . $exclude . '/', '/')) !== false) {
                        continue (2);
                    }
                }
                $file = str_replace('\\', '/', realpath($file));
                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }
    }
    return $zip->close();
}

function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

function calculPath($_path)
{
    if (strpos($_path, '/') !== 0) {
        return dirname(__FILE__) . '/../../' . $_path;
    }
    return $_path;
}

function getDirectorySize($path)
{
    $totalsize = 0;
    if ($handle = opendir($path)) {
        while (false !== ($file = readdir($handle))) {
            $nextpath = $path . '/' . $file;
            if ($file != '.' && $file != '..' && !is_link($nextpath)) {
                if (is_dir($nextpath)) {
                    $totalsize += getDirectorySize($nextpath);
                } elseif (is_file($nextpath)) {
                    $totalsize += filesize($nextpath);
                }
            }
        }
        closedir($handle);
    }
    return $totalsize;
}

function sizeFormat($size)
{
    $mod = 1024;
    $units = explode(' ', 'B KB MB GB TB PB');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
    return round($size, 2) . ' ' . $units[$i];
}

/**
 * @param string $network
 * @param string $ip
 *
 * @return bool
 */
function netMatch($network, $ip)
{

    $ip = trim($ip);
    if ($ip == trim($network)) {
        return true;
    }
    $network = str_replace(' ', '', $network);
    if (strpos($network, '*') !== false) {
        if (strpos($network, '/') !== false) {
            $asParts = explode('/', $network);
            if ($asParts[0]) {
                $network = $asParts[0];
            } else {
                $network = null;
            }
        }
        $nCount = substr_count($network, '*');
        $network = str_replace('*', '0', $network);
        if ($nCount == 1) {
            $network .= '/24';
        } elseif ($nCount == 2) {
            $network .= '/16';
        } elseif ($nCount == 3) {
            $network .= '/8';
        } elseif ($nCount > 3) {
            return true; // if *.*.*.*, then all, so matched
        }
    }

    $d = strpos($network, '-');
    if ($d === false) {
        if (strpos($network, '/') === false) {
            if ($ip == $network) {
                return true;
            }
            return false;
        }
        $ip_arr = explode('/', $network);
        if (!preg_match("@\d*\.\d*\.\d*\.\d*@", $ip_arr[0], $matches)) {
            $ip_arr[0] .= ".0"; // Alternate form 194.1.4/24
        }
        $network_long = ip2long($ip_arr[0]);
        $x = ip2long($ip_arr[1]);
        $mask = long2ip($x) == $ip_arr[1] ? $x : (0xffffffff << (32 - $ip_arr[1]));
        $ip_long = ip2long($ip);
        return ($ip_long & $mask) == ($network_long & $mask);
    } else {

        $from = trim(ip2long(substr($network, 0, $d)));
        $to = trim(ip2long(substr($network, $d + 1)));
        $ip = ip2long($ip);
        return ($ip >= $from && $ip <= $to);
    }
    return false;
}

function getNtpTime()
{
    $time_servers = array(
        'ntp2.emn.fr',
        'time-a.timefreq.bldrdoc.gov',
        'utcnist.colorado.edu',
        'time.nist.gov',
        'ntp.pads.ufrj.br',
    );
    $time_adjustment = 0;
    foreach ($time_servers as $time_server) {
        $fp = fsockopen($time_server, 37, $errno, $errstr, 1);
        if ($fp) {
            $data = NULL;
            while (!feof($fp)) {
                $data .= fgets($fp, 128);
            }
            fclose($fp);
            if (strlen($data) == 4) {
                $NTPtime = ord($data{0}) * pow(256, 3) + ord($data{1}) * pow(256, 2) + ord($data{2}) * 256 + ord($data{3});
                $TimeFrom1990 = $NTPtime - 2840140800;
                $TimeNow = $TimeFrom1990 + 631152000;
                return date("m/d/Y H:i:s", $TimeNow + $time_adjustment);
            }
        }
    }
    return false;
}

function cast($sourceObject, $destination)
{
    $obj_in = serialize($sourceObject);
    return unserialize('O:' . strlen($destination) . ':"' . $destination . '":' . substr($obj_in, $obj_in[2] + 7));
}

function getIpFromString($_string)
{
    $result = parse_url($_string);
    if (isset($result['host'])) {
        $_string = $result['host'];
    } else {
        $_string = str_replace(array('https://', 'http://'), '', $_string);
        if (strpos($_string, '/') !== false) {
            $_string = substr($_string, 0, strpos($_string, '/'));
        }
        if (strpos($_string, ':') !== false) {
            $_string = substr($_string, 0, strpos($_string, ':'));
        }
    }
    if (!filter_var($_string, FILTER_VALIDATE_IP)) {
        $_string = gethostbyname($_string);
    }
    return $_string;
}

/**
 * Evaluate Jeedom
 * @param $_string
 * @return mixed
function evaluate($_string) {
	if (!isset($GLOBALS['ExpressionLanguage'])) {
		$GLOBALS['ExpressionLanguage'] = new ExpressionLanguage();
	}
	if (strpos($_string, '"') !== false || strpos($_string, '\'') !== false) {
		$regex = "/(?:(?:\"(?:\\\\\"|[^\"])+\")|(?:'(?:\\\'|[^'])+'))/is";
		$r = preg_match_all($regex, $_string, $matches);
		$c = count($matches[0]);
		for ($i = 0; $i < $c; $i++) {
			$_string = str_replace($matches[0][$i], '--preparsed' . $i . '--', $_string);
		}
	} else {
		$c = 0;
	}
	$expr = str_ireplace(array(' et ', ' and ', ' ou ', ' or '), array(' && ', ' && ', ' || ', ' || '), $_string);
	$expr = str_replace('==', '=', $expr);
	$expr = str_replace('=', '==', $expr);
	$expr = str_replace('<==', '<=', $expr);
	$expr = str_replace('>==', '>=', $expr);
	$expr = str_replace('!==', '!=', $expr);
	$expr = str_replace('!===', '!==', $expr);
	$expr = str_replace('====', '===', $expr);
	if ($c > 0) {
		for ($i = 0; $i < $c; $i++) {
			$expr = str_replace('--preparsed' . $i . '--', $matches[0][$i], $expr);
		}
	}
	try {
		return $GLOBALS['ExpressionLanguage']->evaluate($expr);
	} catch (Exception $e) {
		//log::add('expression', 'debug', '[Parser 1] Expression : ' . $_string . ' tranformé en ' . $expr . ' => ' . $e->getMessage());
	}
	try {
		$expr = str_replace('""', '"', $expr);
		return $GLOBALS['ExpressionLanguage']->evaluate($expr);
	} catch (Exception $e) {
		//log::add('expression', 'debug', '[Parser 2] Expression : ' . $_string . ' tranformé en ' . $expr . ' => ' . $e->getMessage());
	}
	if ($c > 0) {
		for ($i = 0; $i < $c; $i++) {
			$_string = str_replace('--preparsed' . $i . '--', $matches[0][$i], $_string);
		}
	}
	return $_string;
}
/**
 * Evaluate Jeedom old
function evaluate_old($_string) {
	if (!isset($GLOBALS['ExpressionLanguage'])) {
		$GLOBALS['ExpressionLanguage'] = new ExpressionLanguage();
	}
	$expr = str_replace(array(' et ', ' ET ', ' AND ', ' and ', ' ou ', ' OR ', ' or ', ' OU '), array(' && ', ' && ', ' && ', ' && ', ' || ', ' || ', ' || ', ' || '), $_string);
	$expr = str_replace('==', '=', $expr);
	$expr = str_replace('=', '==', $expr);
	$expr = str_replace('<==', '<=', $expr);
	$expr = str_replace('>==', '>=', $expr);
	$expr = str_replace('!==', '!=', $expr);
	$expr = str_replace('!===', '!==', $expr);
	$expr = str_replace('====', '===', $expr);
	try {
		return $GLOBALS['ExpressionLanguage']->evaluate($expr);
	} catch (Exception $e) {
		//log::add('expression', 'debug', '[Parser 1] Expression : ' . $_string . ' tranformé en ' . $expr . ' => ' . $e->getMessage());
	}
	try {
		$expr = str_replace('""', '"', $expr);
		return $GLOBALS['ExpressionLanguage']->evaluate($expr);
	} catch (Exception $e) {
		//log::add('expression', 'debug', '[Parser 2] Expression : ' . $_string . ' tranformé en ' . $expr . ' => ' . $e->getMessage());
	}
	return $_string;
}

/**
 * TODO: Stocker la version évaluée
 *
 * @param $_string
 * @return string
 */
function evaluate($_string)
{
    if (!isset($GLOBALS['ExpressionLanguage'])) {
        $GLOBALS['ExpressionLanguage'] = new ExpressionLanguage();
    }
    
    $expr = Utils::transformExpressionForEvaluation($_string);

    try {
        return $GLOBALS['ExpressionLanguage']->evaluate($expr);
    } catch (Exception $e) {
        //log::add('expression', 'debug', '[Parser 1] Expression : ' . $_string . ' tranformé en ' . $expr . ' => ' . $e->getMessage());
    }
    try {
        $expr = str_replace('""', '"', $expr);
        return $GLOBALS['ExpressionLanguage']->evaluate($expr);
    } catch (Exception $e) {
        //log::add('expression', 'debug', '[Parser 2] Expression : ' . $_string . ' tranformé en ' . $expr . ' => ' . $e->getMessage());
    }
    return $_string;
}

/**
 * @param string $_string
 *
 * @return string
 */
function secureXSS($_string)
{
    if ($_string === null) {
        return null;
    }
    return str_replace('&amp;', '&', htmlspecialchars(strip_tags($_string), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function minify($_buffer)
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

function sanitizeAccent($_message)
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

function isConnect($_right = '')
{
    return Utils::isConnect($_right);
}

function ZipErrorMessage($code)
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

function arg2array($_string) {
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

function strToHex($string)
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

function hex2rgb($hex) {
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

function getDominantColor($_pathimg)
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

function sha512($_string) {
    return hash('sha512', $_string);
}

function findCodeIcon($_icon) {
    $icon = trim(str_replace(array('fa ', 'icon ', '></i>', '<i', 'class="', '"'), '', trim($_icon)));
    $re = '/.' . $icon . ':.*\n.*content:.*"(.*?)";/m';

    $css = file_get_contents(__DIR__ . '/../../vendor/node_modules/font-awesome/css/font-awesome.css');
    preg_match($re, $css, $matches);
    if (isset($matches[1])) {
        return array('icon' => trim($matches[1], '\\'), 'fontfamily' => 'FontAwesome');
    }

    foreach (ls(__DIR__ . '/../css/icon', '*') as $dir) {
        if (is_dir(__DIR__ . '/../css/icon/' . $dir) && file_exists(__DIR__ . '/../css/icon/' . $dir . '/style.css')) {
            $css = file_get_contents(__DIR__ . '/../css/icon/' . $dir . '/style.css');
            preg_match($re, $css, $matches);
            if (isset($matches[1])) {
                return array('icon' => trim($matches[1], '\\'), 'fontfamily' => trim($dir, '/'));
            }
        }
    }
    return array('icon' => '', 'fontfamily' => '');
}

function addGraphLink($_from, $_from_type, $_to, $_to_type, &$_data, $_level, $_drill, $_display = array('dashvalue' => '5,3', 'lengthfactor' => 0.6))
{
    if (is_array($_to) && count($_to) == 0) {
        return;
    }
    if (!is_array($_to)) {
        if (!is_object($_to)) {
            return;
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

function getSystemMemInfo()
{
    $data = explode("\n", file_get_contents("/proc/meminfo"));
    $meminfo = array();
    foreach ($data as $line) {
        $info = explode(":", $line);
        if (count($info) != 2) {
            continue;
        }
        $value = explode(' ', trim($info[1]));
        $meminfo[$info[0]] = trim($value[0]);
    }
    return $meminfo;
}

function strContain($_string, $_words)
{
    foreach ($_words as $word) {
        if (strpos($_string, $word) !== false) {
            return true;
        }
    }
    return false;
}

function makeZipSupport()
{
    $nextdom_folder = dirname(__FILE__) . '/../..';
    $folder = '/tmp/nextdom_support';
    $outputfile = $nextdom_folder . '/support/nextdom_support_' . date('Y-m-d_His') . '.tar.gz';
    if (file_exists($folder)) {
        rrmdir($folder);
    }
    mkdir($folder);
    system('cd ' . $nextdom_folder . '/log;cp -R * "' . $folder . '" > /dev/null;cp -R .[^.]* "' . $folder . '" > /dev/null');
    system('sudo dmesg >> ' . $folder . '/dmesg');
    system('sudo cp /var/log/messages "' . $folder . '/" > /dev/null');
    system('sudo chmod 777 -R "' . $folder . '" > /dev/null');
    system('cd ' . $folder . ';tar cfz "' . $outputfile . '" * > /dev/null;chmod 777 ' . $outputfile);
    rrmdir($folder);
    return realpath($outputfile);
}

function unautorizedInDemo($_user = null) {
    if ($_user === null) {
        if (!isset($_SESSION) || !isset($_SESSION['user'])) {
            return;
        }
        $_user = $_SESSION['user'];
    }
    if (!is_object($_user)) {
        return;
    }
    if ($_user->getLogin() == 'demo') {
        throw new Exception(__('Cette action n\'est pas autorisée en mode démo', __FILE__));
    }
}

function decodeSessionData($_data)
{
    return SessionHelper::decodeSessionData($_data);
}

function listSession() {
    return SessionHelper::getSessionsList();
}

function deleteSession($_id)
{
    SessionHelper::deleteSession($_id);
}