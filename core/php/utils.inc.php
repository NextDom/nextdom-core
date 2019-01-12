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

use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DateHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SessionHelper;
use NextDom\Helpers\SystemHelper;
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
    return Utils::isJson($_string, $_default);
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

function rrmdir($dir) {
    return FileSystemHelper::rrmdir($dir);
}

function date_fr($date_en) {
    return DateHelper::dateToFr($date_en);
}

function convertDayEnToFr($_day) {
    trigger_error('La fonction convertDayEnToFr devient convertDayFromEn', E_USER_DEPRECATED);
    return DateHelper::convertDayFromEn($_day);
}

function convertDayFromEn($_day) {
    return DateHelper::convertDayFromEn($_day);
}

function create_zip($source_arr, $destination, $_excludes = array())
{
    return FileSystemHelper::createZip($source_arr, $destination, $_excludes);
}

function br2nl($string)
{
    return Utils::br2nl($string);
}

function calculPath($_path)
{
    return Utils::calculPath($_path);
}

function getDirectorySize($path)
{
    return FileSystemHelper::getDirectorySize($path);
}

function sizeFormat($size)
{
    return Utils::sizeFormat($size);
}

function netMatch($network, $ip)
{
    return NetworkHelper::netMatch($network, $ip);
}

function getNtpTime()
{
    return DateHelper::getNtpTime();
}

function cast($sourceObject, $destinationClassName)
{
    return Utils::cast($sourceObject, $destinationClassName);
}

function getIpFromString($_string)
{
    return NetworkHelper::getIpFromString($_string);
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
 */

function evaluate($_string)
{
    return Utils::evaluate($_string);
}

function secureXSS($_string)
{
    return Utils::secureXSS($_string);
}

function minify($_buffer)
{
    return Utils::minify($_buffer);
}

function sanitizeAccent($_message)
{
    return Utils::sanitizeAccent($_message);
}

function isConnect($_right = '')
{
    return AuthentificationHelper::isConnected($_right);
}

function ZipErrorMessage($code)
{
    return Utils::getZipErrorMessage($code);
}

function arg2array($_string) {
    return Utils::arg2array($_string);
}

function strToHex($string)
{
    return Utils::strToHex($string);
}

function hex2rgb($hex) {
    return Utils::hex2rgb($hex);
}

function getDominantColor($_pathimg)
{
    return Utils::getDominantColor($_pathimg);
}

function sha512($_string) {
    return Utils::sha512($_string);
}

function findCodeIcon($_icon) {
    return Utils::findCodeIcon($_icon);
}

function addGraphLink($_from, $_from_type, $_to, $_to_type, &$_data, $_level, $_drill, $_display = array('dashvalue' => '5,3', 'lengthfactor' => 0.6))
{
    return Utils::addGraphLink($_from, $_from_type, $_to, $_to_type, $_data, $_level, $_drill, $_display);
}

function getSystemMemInfo()
{
    return SystemHelper::getMemInfo();
}

function strContain($_string, $_words)
{
    return Utils::strContain($_string, $_words);
}

function makeZipSupport()
{
    return Utils::makeZipSupport();
}

function unautorizedInDemo($_user = null) {
    return Utils::unautorizedInDemo($_user);
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