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
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Managers;

use NextDom\Enums\CmdType;
use NextDom\Enums\DateFormat;
use NextDom\Enums\ScenarioState;
use NextDom\Helpers\DateHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NetworkHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\TranslateHelper;
use NextDom\Helpers\Utils;
use NextDom\Model\Entity\Cmd;
use NextDom\Model\Entity\Scenario;
use NextDom\Model\Entity\ScenarioExpression;

/**
 * Class ScenarioExpressionManager
 * @package NextDom\Managers
 */
class ScenarioExpressionManager
{
    const DB_CLASS_NAME = 'scenarioExpression';
    const CLASS_NAME = ScenarioExpression::class;
    const WAIT_LIMIT = 7200;

    /**
     * Get expression from his id
     *
     * @param mixed $id Identifiant
     *
     * @return ScenarioExpression|null
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        $params = ['id' => $id];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return DBHelper::getOneObject($sql, $params, self::CLASS_NAME);
    }

    /**
     * Get all scenario expressions
     *
     * @return ScenarioExpression|null
     *
     * @throws \Exception
     */
    public static function all()
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME;
        return DBHelper::getAllObjects($sql, [], self::CLASS_NAME);
    }


    /**
     * Get the sub-element of a scenario from its identifier
     *
     * @param int|string $scenarioSubElementId Scenario sub element ID
     *
     * @return ScenarioExpression|null
     *
     * @throws \Exception
     */
    public static function byScenarioSubElementId($scenarioSubElementId)
    {
        $params = ['scenarioSubElement_id' => $scenarioSubElementId];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE scenarioSubElement_id = :scenarioSubElement_id
                ORDER BY `order`';
        return DBHelper::getAllObjects($sql, $params, self::CLASS_NAME);
    }

    /**
     * Search an expression by name and/or option
     *
     * @param string $expression Expression searched
     * @param string $options Option searched
     * @param bool $and True if the expression and the option are a criterion, if not the expression or the option
     *
     * @return ScenarioExpression[]|null
     *
     * @throws \Exception
     */
    public static function searchExpression($expression, $options = null, $and = true)
    {
        $params = ['expression' => '%' . $expression . '%'];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE expression LIKE :expression ';
        if ($options !== null) {
            $params['options'] = '%' . $options . '%';
            if ($and) {
                $sql .= 'AND options LIKE :options';
            } else {
                $sql .= 'OR options LIKE :options';
            }
        }
        return DBHelper::getAllObjects($sql, $params, self::CLASS_NAME);
    }

    /**
     * Searches for an expression on scenario expression of type "element"
     *
     * @param $elementId
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function byElement($elementId)
    {
        $params = ['expression' => $elementId];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE expression = :expression
                AND `type` = "element"';
        return DBHelper::getOneObject($sql, $params, self::CLASS_NAME);
    }

    /**
     * @TODO ????
     * @TODO Revoir la génération des UID
     *
     * @param $expression
     * @param $options
     *
     * @return array
     * @throws \Exception
     */
    public static function getExpressionOptions($expression, $options)
    {
        $replace = [
            '#uid#' => 'exp' . mt_rand()
        ];
        $result = ['html' => ''];
        $cmd = CmdManager::byId(str_replace('#', '', CmdManager::humanReadableToCmd($expression)));
        if (is_object($cmd)) {
            $result['html'] = trim($cmd->toHtml('scenario', $options));
            return $result;
        }
        $result['template'] = FileSystemHelper::getCoreTemplateFileContent('scenario', $expression . '.default');
        $options = Utils::isJson($options, $options);
        if (is_array($options) && count($options) > 0) {
            foreach ($options as $key => $value) {
                $replace['#' . $key . '#'] = str_replace('"', '&quot;', $value);
            }
        }
        if (!isset($replace['#id#'])) {
            $replace['#id#'] = mt_rand();
        }
        $result['html'] = Utils::templateReplace(CmdManager::cmdToHumanReadable($replace), $result['template']);
        preg_match_all("/#[a-zA-Z_]*#/", $result['template'], $matches);
        foreach ($matches[0] as $value) {
            if (!isset($replace[$value])) {
                $replace[$value] = '';
            }
        }
        $result['html'] = TranslateHelper::exec(Utils::templateReplace($replace, $result['html']), 'core/template/scenario/' . $expression . '.default');
        return $result;
    }

    /**
     * @TODO ????
     *
     * @param $baseAction
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function humanAction($baseAction)
    {
        $result = '';
        if ($baseAction['cmd'] == 'scenario') {
            $scenario = ScenarioManager::byId($baseAction['options']['scenario_id']);
            if (!is_object($scenario)) {
                $name = 'scenario ' . $baseAction['options']['scenario_id'];
            } else {
                $name = $scenario->getName();
            }
            $action = $baseAction['options']['action'];
            $result .= __('Scénario : ') . $name . ' <i class="fa fa-arrow-right"></i> ' . $action;
        } elseif ($baseAction['cmd'] == 'variable') {
            $name = $baseAction['options']['name'];
            $value = $baseAction['options']['value'];
            $result .= __('Variable : ') . $name . ' <i class="fa fa-arrow-right"></i> ' . $value;
        } elseif (is_object(CmdManager::byId(str_replace('#', '', $baseAction['cmd'])))) {
            $cmd = CmdManager::byId(str_replace('#', '', $baseAction['cmd']));
            $eqLogic = $cmd->getEqLogicId();
            $result .= $eqLogic->getHumanName(true) . ' ' . $cmd->getName();
        }
        return trim($result);
    }

    /**
     * Get a random number
     *
     * @param $minValue
     * @param $maxValue
     *
     * @return int
     */
    public static function rand(int $minValue, int $maxValue): int
    {
        return mt_rand($minValue, $maxValue);
    }

    /**
     * @TODO ???
     * @TODO Result n'est jamais utilisé, le bloc Try peut normalement être supprimé
     * @param $_sValue
     *
     * @return array|mixed
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function randText($_sValue)
    {
        $_sValue = self::setTags($_sValue);
        $_aValue = explode(";", $_sValue);
        try {
            $result = Utils::evaluate($_aValue);
            if (is_string($result)) {
                $result = $_aValue;
            }
        } catch (\Exception $e) {
            $result = $_aValue;
        }
        if (is_array($_aValue)) {
            $nbr = mt_rand(0, count($_aValue) - 1);
            return $_aValue[$nbr];
        } else {
            return $_aValue;
        }
    }

    /**
     * @TODO Faut bien les définir les tags
     *
     * @param $_expression
     * @param Scenario $_scenario
     * @param bool $_quote
     * @param int $_nbCall
     * @return mixed
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function setTags($_expression, &$_scenario = null, $_quote = false, $_nbCall = 0)
    {
        if ($_nbCall > 10) {
            return $_expression;
        }
        $replace1 = self::getRequestTags($_expression);
        if ($_scenario !== null && count($_scenario->getTags()) > 0) {
            $replace1 = array_merge($replace1, $_scenario->getTags());
        }
        if (is_object($_scenario)) {
            $cmd = CmdManager::byId(str_replace('#', '', $_scenario->getRealTrigger()));
            if (is_object($cmd)) {
                $replace1['#trigger#'] = $cmd->getHumanName();
                $replace1['#trigger_value#'] = $cmd->execCmd();
            } else {
                $replace1['#trigger#'] = $_scenario->getRealTrigger();
            }
        }
        if ($_quote) {
            foreach ($replace1 as &$value) {
                if (strpos($value, ' ') !== false || preg_match("/[a-zA-Z]/", $value) || $value === '') {
                    $value = '"' . trim($value, '"') . '"';
                }
            }
        }
        $replace2 = [];
        if (!is_string($_expression)) {
            return $_expression;
        }
        preg_match_all("/([a-zA-Z][a-zA-Z_]*?)\((.*?)\)/", $_expression, $matches, PREG_SET_ORDER);
        if (is_array($matches)) {
            foreach ($matches as $match) {
                $function = $match[1];
                $replace_string = $match[0];
                if (substr_count($match[2], '(') != substr_count($match[2], ')')) {
                    $pos = strpos($_expression, $match[2]) + strlen($match[2]);
                    while (substr_count($match[2], '(') > substr_count($match[2], ')')) {
                        $match[2] .= $_expression[$pos];
                        $pos++;
                        if ($pos > strlen($_expression)) {
                            break;
                        }
                    }
                    $arguments = self::setTags($match[2], $_scenario, $_quote, $_nbCall++);
                    while ($arguments[0] == '(' && $arguments[strlen($arguments) - 1] == ')') {
                        $arguments = substr($arguments, 1, -1);
                    }
                    $result = str_replace($match[2], $arguments, $_expression);
                    while (substr_count($result, '(') > substr_count($result, ')')) {
                        $result .= ')';
                    }
                    $result = self::setTags($result, $_scenario, $_quote, $_nbCall++);
                    return CmdManager::cmdToValue(str_replace(array_keys($replace1), array_values($replace1), $result), $_quote);
                } else {
                    $arguments = explode(',', $match[2]);
                }
                if (method_exists(__CLASS__, $function)) {
                    if ($function == 'trigger') {
                        if (!isset($arguments[0])) {
                            $arguments[0] = '';
                        }
                        $replace2[$replace_string] = self::trigger($arguments[0], $_scenario);
                    } elseif ($function == 'triggerValue') {
                        $replace2[$replace_string] = self::triggerValue($_scenario);
                    } elseif ($function == 'tag') {
                        if (!isset($arguments[0])) {
                            $arguments[0] = '';
                        }
                        if (!isset($arguments[1])) {
                            $arguments[1] = '';
                        }
                        $replace2[$replace_string] = self::tag($_scenario, $arguments[0], $arguments[1]);
                    } else {
                        $replace2[$replace_string] = call_user_func_array(__CLASS__ . "::" . $function, $arguments);
                    }
                } else {
                    if (function_exists($function)) {
                        foreach ($arguments as &$argument) {
                            $argument = trim(Utils::evaluate(self::setTags($argument, $_scenario, $_quote)));
                        }
                        $replace2[$replace_string] = call_user_func_array($function, $arguments);
                    }
                }
                if ($_quote && isset($replace2[$replace_string]) && (strpos($replace2[$replace_string], ' ') !== false || preg_match("/[a-zA-Z#]/", $replace2[$replace_string]) || $replace2[$replace_string] === '')) {
                    $replace2[$replace_string] = '"' . trim($replace2[$replace_string], '"') . '"';
                }
            }
        }
        $return = CmdManager::cmdToValue(str_replace(array_keys($replace1), array_values($replace1), str_replace(array_keys($replace2), array_values($replace2), $_expression)), $_quote);
        return $return;
    }

    /**
     * @TODO: Je demande des tags
     *
     * @param $expression
     * @return array
     * @throws \Exception
     */
    public static function getRequestTags($expression)
    {
        $return = [];
        preg_match_all("/#([a-zA-Z0-9]*)#/", $expression, $matches);
        if (count($matches) == 0) {
            return $return;
        }
        $matches = array_unique($matches[0]);
        foreach ($matches as $tag) {
            switch ($tag) {
                case '#seconde#':
                    $return['#seconde#'] = (int)date('s');
                    break;
                case '#heure#':
                    $return['#heure#'] = (int)date('G');
                    break;
                case '#heure12#':
                    $return['#heure12#'] = (int)date('g');
                    break;
                case '#minute#':
                    $return['#minute#'] = (int)date('i');
                    break;
                case '#jour#':
                    $return['#jour#'] = (int)date('d');
                    break;
                case '#mois#':
                    $return['#mois#'] = (int)date('m');
                    break;
                case '#annee#':
                    $return['#annee#'] = (int)date('Y');
                    break;
                case '#time#':
                    $return['#time#'] = date('Gi');
                    break;
                case '#timestamp#':
                    $return['#timestamp#'] = time();
                    break;
                case '#date#':
                    $return['#date#'] = date('md');
                    break;
                case '#semaine#':
                    $return['#semaine#'] = date('W');
                    break;
                case '#sjour#':
                    $return['#sjour#'] = '"' . DateHelper::dateToFr(date('l')) . '"';
                    break;
                case '#smois#':
                    $return['#smois#'] = '"' . DateHelper::dateToFr(date('F')) . '"';
                    break;
                case '#njour#':
                    $return['#njour#'] = (int)date('w');
                    break;
                case '#nextdom_name#':
                    $return['#nextdom_name#'] = '"' . ConfigManager::byKey('name') . '"';
                    break;
                case '#hostname#':
                    $return['#hostname#'] = '"' . gethostname() . '"';
                    break;
                case '#IP#':
                    $return['#IP#'] = '"' . NetworkHelper::getNetworkAccess('internal', 'ip', '', false) . '"';
                    break;
                case '#trigger#':
                    $return['#trigger#'] = '';
                    break;
                case '#trigger_value#':
                    $return['#trigger_value#'] = '';
                    break;
            }
        }
        return $return;
    }

    /**
     * @TODO ????
     * @ il semble judicieu de rajouter l'interface SenarioInterface à $senario, elle est prete, faut se servir...
     * @param string $name
     * @param Scenario $scenario
     * @return int
     */
    public static function trigger($name = '', &$scenario = null)
    {
        if ($scenario !== null) {
            if (trim($name) == '') {
                return $scenario->getRealTrigger();
            }
            if ($name == $scenario->getRealTrigger()) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * @TODO ????
     *
     * @param Scenario $scenario
     * @return mixed
     * @throws \Exception
     */
    public static function triggerValue(&$scenario = null)
    {
        if ($scenario !== null) {
            $cmd = CmdManager::byId(str_replace('#', '', $scenario->getRealTrigger()));
            if (is_object($cmd)) {
                return $cmd->execCmd();
            }
        }
        return false;
    }

    /**
     * @TODO: Un tag
     *
     * @param Scenario|null $scenario
     * @param $name
     * @param string $default
     * @return string
     */
    public static function tag(&$scenario = null, $name, $default = '')
    {
        if ($scenario === null) {
            return '"' . $default . '"';
        }
        $tags = $scenario->getTags();
        if (isset($tags['#' . $name . '#'])) {
            return $tags['#' . $name . '#'];
        }
        return '"' . $default . '"';
    }

    /**
     * Get a scenario from its expression
     * @TODO: Format ???
     *
     * @param $scenarioExpression
     * @return int @TODO -1, -2, -3 ????
     * @throws \Exception
     */
    public static function scenario(string $scenarioExpression)
    {
        $id = str_replace(['scenario', '#'], '', trim($scenarioExpression));
        $scenario = ScenarioManager::byId($id);
        if (!is_object($scenario)) {
            return -2;
        }
        $state = $scenario->getState();
        if ($scenario->getIsActive() == 0) {
            return -1;
        }
        switch ($state) {
            case ScenarioState::STOP:
                return 0;
            case ScenarioState::IN_PROGRESS:
                return 1;
        }
        return -3;
    }

    /**
     * Enables an eqLogic object
     * @TODO: -2 en -1 ?
     * @param mixed $eqLogicId Identifiant du l'objet
     *
     * @return int 0 If the object is not activated, 1 if the object is activated, -2 if the object does not exist
     * @throws \Exception
     */
    public static function eqEnable($eqLogicId)
    {
        $id = str_replace(['eqLogic', '#'], '', trim($eqLogicId));
        $eqLogic = EqLogicManager::byId($id);
        if (!is_object($eqLogic)) {
            return -2;
        }
        return $eqLogic->getIsEnable();
    }

    /**
     * @TODO: Fait une moyenne de quelque chose
     * @TODO: Mettre en place la gestion du nombre de paramètres variables
     *
     * @param mixed $cmdId Identifiant de la commande
     * @param string $period Période sur laquelle la moyenne doit être calculée
     *
     * @return float|int|string
     * @throws \Exception
     */
    public static function average($cmdId, $period = '1 hour')
    {
        $args = func_get_args();
        if (count($args) > 2 || strpos($period, '#') !== false || is_numeric($period)) {
            $values = [];
            foreach ($args as $arg) {
                if (is_numeric($arg)) {
                    $values[] = $arg;
                } else {
                    $value = CmdManager::cmdToValue($arg);
                    if (is_numeric($value)) {
                        $values[] = $value;
                    } else {
                        try {
                            $values[] = Utils::evaluate($value);
                        } catch (\Throwable $ex) {

                        }
                    }
                }
            }
            return array_sum($values) / count($values);
        } else {
            $cmd = CmdManager::byId(trim(str_replace('#', '', $cmdId)));
            if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
                return '';
            }
            if (str_word_count($period) == 1 && is_numeric(trim($period)[0])) {
                $startHist = date(DateFormat::FULL, strtotime(date(DateFormat::FULL) . ' -' . $period));
            } else {
                $startHist = date(DateFormat::FULL, strtotime($period));
                if ($startHist == date(DateFormat::FULL, strtotime(0))) {
                    return '';
                }
            }
            $historyStatistic = $cmd->getStatistique($startHist, date(DateFormat::FULL));
            if (!isset($historyStatistic['avg']) || $historyStatistic['avg'] == '') {
                return $cmd->execCmd();
            }
            return round($historyStatistic['avg'], 1);
        }
    }

    /**
     * @TODO: Calcule une moyenne de quelque chose entre deux dates
     *
     * @param $cmdId
     * @param $startDate
     * @param $endDate
     *
     * @return float|string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function averageBetween($cmdId, $startDate, $endDate)
    {
        $cmd = CmdManager::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $startDate = date(DateFormat::FULL, strtotime(self::setTags($startDate)));
        $endDate = date(DateFormat::FULL, strtotime(self::setTags($endDate)));
        $historyStatistic = $cmd->getStatistique($startDate, $endDate);
        if (!isset($historyStatistic['avg'])) {
            return '';
        }
        return round($historyStatistic['avg'], 1);
    }

    /**
     * Obtenir un dégradé de couleur
     *
     * @param $_from_color Couleur de départ
     * @param $_to_color Couleur de fin
     * @param $_min
     * @param $_max
     * @param $_value
     * @return mixed
     */
    public static function color_gradient($_from_color, $_to_color, $_min, $_max, $_value)
    {
        if (!is_numeric($_value)) {
            $value = round(NextDomHelper::evaluateExpression($_value));
        } else {
            $value = round($_value);
        }
        $graduations = $_max - $_min - 1;
        $value -= $_min + 1;
        $startcol = str_replace('#', '', $_from_color);
        $endcol = str_replace('#', '', $_to_color);
        $RedOrigin = hexdec(substr($startcol, 1, 2));
        $GrnOrigin = hexdec(substr($startcol, 3, 2));
        $BluOrigin = hexdec(substr($startcol, 5, 2));
        if ($graduations >= 2) {
            $GradientSizeRed = (hexdec(substr($endcol, 1, 2)) - $RedOrigin) / $graduations;
            $GradientSizeGrn = (hexdec(substr($endcol, 3, 2)) - $GrnOrigin) / $graduations;
            $GradientSizeBlu = (hexdec(substr($endcol, 5, 2)) - $BluOrigin) / $graduations;
            for ($i = 0; $i <= $graduations; $i++) {
                $RetVal[$i] = strtoupper("#" . str_pad(dechex($RedOrigin + ($GradientSizeRed * $i)), 2, '0', STR_PAD_LEFT) .
                    str_pad(dechex($GrnOrigin + ($GradientSizeGrn * $i)), 2, '0', STR_PAD_LEFT) .
                    str_pad(dechex($BluOrigin + ($GradientSizeBlu * $i)), 2, '0', STR_PAD_LEFT));
            }
        } elseif ($graduations == 1) {
            $RetVal[] = $_from_color;
            $RetVal[] = $_to_color;
        } else {
            $RetVal[] = $_from_color;
        }
        if (isset($RetVal[$value])) {
            return $RetVal[$value];
        }
        if ($_value <= $_min) {
            return $RetVal[0];
        }
        return $RetVal[count($RetVal) - 1];
    }

    /**
     * Obtenir la valeur maximum sur une période @TODO: de quelque chose
     *
     * @param $cmdId
     * @param string $period
     * @return float|mixed|string
     * @throws \Exception
     */
    public static function max($cmdId, $period = '1 hour')
    {
        $args = func_get_args();
        if (count($args) > 2 || strpos($period, '#') !== false || is_numeric($period)) {
            $values = [];
            foreach ($args as $arg) {
                if (is_numeric($arg)) {
                    $values[] = $arg;
                } else {
                    $value = CmdManager::cmdToValue($arg);
                    if (is_numeric($value)) {
                        $values[] = $value;
                    } else {
                        try {
                            $values[] = Utils::evaluate($value);
                        } catch (\Throwable $ex) {

                        }
                    }
                }
            }
            return max($values);
        } else {
            $cmd = CmdManager::byId(trim(str_replace('#', '', $cmdId)));
            if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
                return '';
            }
            if (str_word_count($period) == 1 && is_numeric(trim($period)[0])) {
                $startHist = date(DateFormat::FULL, strtotime(date(DateFormat::FULL) . ' -' . $period));
            } else {
                $startHist = date(DateFormat::FULL, strtotime($period));
                if ($startHist == date(DateFormat::FULL, strtotime(0))) {
                    return '';
                }
            }
            $historyStatistique = $cmd->getStatistique($startHist, date(DateFormat::FULL));
            if (!isset($historyStatistique['max']) || $historyStatistique['max'] == '') {
                return $cmd->execCmd();
            }
            return round($historyStatistique['max'], 1);
        }
    }

    /**
     * Obtenir la valeur maximum entre deux dates @TODO: de quelque chose
     *
     * @param $cmdId
     * @param $startDate
     * @param $endDate
     * @return float|string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function maxBetween($cmdId, $startDate, $endDate)
    {
        $cmd = CmdManager::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $startDate = date(DateFormat::FULL, strtotime(self::setTags($startDate)));
        $endDate = date(DateFormat::FULL, strtotime(self::setTags($endDate)));
        $historyStatistique = $cmd->getStatistique(self::setTags($startDate), self::setTags($endDate));
        if (!isset($historyStatistique['max'])) {
            return '';
        }
        return round($historyStatistique['max'], 1);
    }

    /**
     * Attend que la condition soit vraie.
     *
     * @param mixed $condition Condition à tester
     * @param int $waitTimeout Durée limite de l'attente (7200s par défaut)
     *
     * @return int
     */
    public static function wait($condition, $waitTimeout = self::WAIT_LIMIT)
    {
        $result = false;
        $occurence = 0;
        // Si le timeout est une expression, évalue sa valeur
        $timeout = NextDomHelper::evaluateExpression($waitTimeout);
        // Si le timeout
        $limit = (is_numeric($timeout)) ? $timeout : self::WAIT_LIMIT;
        while ($result !== true) {
            $result = NextDomHelper::evaluateExpression($condition);
            if ($occurence > $limit) {
                return 0;
            }
            $occurence++;
            sleep(1);
        }
        return 1;
    }

    /**
     * Obtenir la valeur minimum sur une période @TODO: ??? Toujours sur quoi ?
     * @param $cmdId
     * @param string $period
     * @return float|mixed|string
     * @throws \Exception
     */
    public static function min($cmdId, $period = '1 hour')
    {
        $args = func_get_args();
        if (count($args) > 2 || strpos($period, '#') !== false || is_numeric($period)) {
            $values = [];
            foreach ($args as $arg) {
                if (is_numeric($arg)) {
                    $values[] = $arg;
                } else {
                    $value = CmdManager::cmdToValue($arg);
                    if (is_numeric($value)) {
                        $values[] = $value;
                    } else {
                        try {
                            $values[] = Utils::evaluate($value);
                        } catch (\Throwable $ex) {

                        }
                    }
                }
            }
            return min($values);
        } else {
            $cmd = CmdManager::byId(trim(str_replace('#', '', $cmdId)));
            if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
                return '';
            }
            if (str_word_count($period) == 1 && is_numeric(trim($period)[0])) {
                $startHist = date(DateFormat::FULL, strtotime(date(DateFormat::FULL) . ' -' . $period));
            } else {
                $startHist = date(DateFormat::FULL, strtotime($period));
                if ($startHist == date(DateFormat::FULL, strtotime(0))) {
                    return '';
                }
            }
            $historyStatistique = $cmd->getStatistique($startHist, date(DateFormat::FULL));
            if (!isset($historyStatistique['min']) || $historyStatistique['min'] == '') {
                return $cmd->execCmd();
            }
            return round($historyStatistique['min'], 1);
        }
    }

    /**
     * Obtenir la valeur minimum entre deux dates @TODO: De quoi ?
     *
     * @param $cmdId
     * @param $startDate
     * @param $endDate
     * @return float|string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function minBetween($cmdId, $startDate, $endDate)
    {
        $cmd = CmdManager::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $startDate = date(DateFormat::FULL, strtotime(self::setTags($startDate)));
        $endDate = date(DateFormat::FULL, strtotime(self::setTags($endDate)));
        $historyStatistique = $cmd->getStatistique($startDate, $endDate);
        if (!isset($historyStatistique['min'])) {
            return '';
        }
        return round($historyStatistique['min'], 1);
    }

    /**
     * Obtenir une valeur médiane @TODO: De quoi ?
     *
     * @return int|mixed
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function median()
    {
        $args = func_get_args();
        $values = [];
        foreach ($args as $arg) {
            if (is_numeric($arg)) {
                $values[] = $arg;
            } else {
                $value = CmdManager::cmdToValue($arg);
                if (is_numeric($value)) {
                    $values[] = $value;
                } else {
                    try {
                        $values[] = Utils::evaluate($value);
                    } catch (\Throwable $ex) {

                    }
                }
            }
        }
        if (count($values) < 1) {
            return 0;
        }
        if (count($values) == 1) {
            return $values[0];
        }
        sort($values);
        return $values[round(count($values) / 2) - 1];
    }

    /**
     * Renvoie une tendance @TODO de ?
     *
     * @param $cmdId
     * @param string $period
     * @param string $threshold
     * @return int|string
     * @throws \Exception
     */
    public static function tendance($cmdId, $period = '1 hour', $threshold = '')
    {
        $cmd = CmdManager::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd)) {
            return '';
        }
        if ($cmd->getIsHistorized() == 0) {
            return '';
        }
        $endTime = date(DateFormat::FULL);
        if (str_word_count($period) == 1 && is_numeric(trim($period)[0])) {
            $startTime = date(DateFormat::FULL, strtotime(date(DateFormat::FULL) . ' -' . $period));
        } else {
            $startTime = date(DateFormat::FULL, strtotime($period));
            if ($startTime == date(DateFormat::FULL, strtotime(0))) {
                return '';
            }
        }
        $tendance = $cmd->getTendance($startTime, $endTime);
        if ($threshold != '') {
            $maxThreshold = $threshold;
            $minThreshold = -$threshold;
        } else {
            $maxThreshold = ConfigManager::byKey('historyCalculTendanceThresholddMax');
            $minThreshold = ConfigManager::byKey('historyCalculTendanceThresholddMin');
        }
        if ($tendance > $maxThreshold) {
            return 1;
        }
        if ($tendance < $minThreshold) {
            return -1;
        }
        return 0;
    }

    /**
     * @TODO: j'en sais rien, durée pendant laquelle il a conserver son état sans doute
     *
     * @param $cmdId
     * @param null $value
     * @return false|int
     * @throws \Exception
     */
    public static function lastStateDuration($cmdId, $value = null)
    {
        return HistoryManager::lastStateDuration(str_replace('#', '', $cmdId), $value);
    }

    /**
     * @TODO Changement d'état, ou de pantalon, ou de slip
     *
     * @param $cmdId
     * @param null $value
     * @param string $period
     * @return array|string
     * @throws \Exception
     */
    public static function stateChanges($cmdId, $value = null, $period = '1 hour')
    {
        if (!is_numeric(str_replace('#', '', $cmdId))) {
            $cmd = CmdManager::byId(str_replace('#', '', CmdManager::humanReadableToCmd($cmdId)));
        } else {
            $cmd = CmdManager::byId(str_replace('#', '', $cmdId));
        }
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $cmd_id = $cmd->getId();

        $args = func_num_args();
        if ($args == 2) {
            if (is_numeric(func_get_arg(1))) {
                $value = func_get_arg(1);
            } else {
                $period = func_get_arg(1);
                $value = null;
            }
        }
        return HistoryManager::stateChanges($cmd_id, $value, date(DateFormat::FULL, strtotime('-' . $period)), date(DateFormat::FULL));
    }

    /**
     * Changement entre deux dates //@TODO: Woohoo
     *
     * @param $cmdId
     * @param $value
     * @param $startDate
     * @param null $endDate
     * @return array|string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function stateChangesBetween($cmdId, $value, $startDate, $endDate = null)
    {
        if (!is_numeric(str_replace('#', '', $cmdId))) {
            $cmd = CmdManager::byId(str_replace('#', '', CmdManager::humanReadableToCmd($cmdId)));
        } else {
            $cmd = CmdManager::byId(str_replace('#', '', $cmdId));
        }
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $cmd_id = $cmd->getId();

        if (func_num_args() == 3) {
            $endDate = func_get_arg(2);
            $startDate = func_get_arg(1);
            $value = null;
        }
        $startDate = date(DateFormat::FULL, strtotime(self::setTags($startDate)));
        $endDate = date(DateFormat::FULL, strtotime(self::setTags($endDate)));

        return HistoryManager::stateChanges($cmd_id, $value, $startDate, $endDate);
    }

    /**
     * Get the duration since the command has this value
     *
     * @param $cmdId
     * @param $value
     * @param string $period
     * @return float|string
     * @throws \Exception
     */
    public static function duration($cmdId, $value, $period = '1 hour')
    {
        $cmd_id = str_replace('#', '', $cmdId);
        if (!is_numeric($cmd_id)) {
            $cmd_id = CmdManager::byId(str_replace('#', '', CmdManager::humanReadableToCmd($cmdId)));
        }
        $cmd = CmdManager::byId($cmd_id);
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }

        if (str_word_count($period) == 1 && is_numeric(trim($period)[0])) {
            $startDate = date(DateFormat::FULL, strtotime(date(DateFormat::FULL) . ' -' . $period));
        } else {
            $startDate = date(DateFormat::FULL, strtotime($period));
            if ($startDate == date(DateFormat::FULL, strtotime(0))) {
                return '';
            }
        }
        $endDate = date(DateFormat::FULL);

        return self::getCmdValueDuration($cmd, $startDate, $endDate, $value);
    }

    /**
     * @param Cmd $cmd
     * @param string $startDate
     * @param string $endDate
     * @param mixed $value
     * @return float|string
     * @throws \Exception
     */
    private static function getCmdValueDuration($cmd, $startDate, $endDate, $value)
    {
        $value = str_replace(',', '.', $value);
        $histories = $cmd->getHistory();
        $nbDecimals = strlen(substr(strrchr($value, "."), 1));

        if (count($histories) == 0) {
            return '';
        }

        $duration = 0;
        $lastDuration = strtotime($histories[0]->getDatetime());
        $lastValue = $histories[0]->getValue();

        foreach ($histories as $history) {
            if ($history->getDatetime() >= $startDate) {
                if ($history->getDatetime() <= $endDate) {
                    if ($lastValue == $value) {
                        $duration = $duration + (strtotime($history->getDatetime()) - $lastDuration);
                    }
                } else {
                    if ($lastValue == $value) {
                        $duration = $duration + (strtotime($endDate) - $lastDuration);
                    }
                    break;
                }
                $lastDuration = strtotime($history->getDatetime());
            } else {
                $lastDuration = strtotime($startDate);
            }
            $lastValue = round($history->getValue(), $nbDecimals);
        }
        if ($lastValue == $value && $lastDuration <= strtotime($endDate)) {
            $duration = $duration + (strtotime($endDate) - $lastDuration);
        }
        return floor($duration / 60);
    }

    /**
     * Get the duration between the command has this value
     *
     * @param $cmdId
     * @param $value
     * @param $startDate
     * @param $endDate
     * @return float|string
     * @throws \Exception
     */
    public static function durationBetween($cmdId, $value, $startDate, $endDate)
    {
        if (!is_numeric(str_replace('#', '', $cmdId))) {
            $cmd = CmdManager::byId(str_replace('#', '', CmdManager::humanReadableToCmd($cmdId)));
        } else {
            $cmd = CmdManager::byId(str_replace('#', '', $cmdId));
        }
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }

        $startDate = date(DateFormat::FULL, strtotime(self::setTags($startDate)));
        $endDate = date(DateFormat::FULL, strtotime(self::setTags($endDate)));

        return self::getCmdValueDuration($cmd, $startDate, $endDate, $value);
    }

    /**
     * @TODO: Dernier entre deux dates ???
     *
     * @param $cmdId
     * @param $startDate
     * @param $endDate
     * @return float|string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function lastBetween($cmdId, $startDate, $endDate)
    {
        $cmd = CmdManager::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $startDate = date(DateFormat::FULL, strtotime(self::setTags($startDate)));
        $endDate = date(DateFormat::FULL, strtotime(self::setTags($endDate)));
        $historyStatistic = $cmd->getStatistique($startDate, $endDate);
        if (!$historyStatistic['last']) {
            return '';
        }
        return round($historyStatistic['last'], 1);
    }

    /**
     * @TODO: Statistiques de quelque chose
     *
     * @param $cmdId
     * @param $calc
     * @param string $period
     * @return string
     * @throws \Exception
     */
    public static function statistics($cmdId, $calc, $period = '1 hour')
    {

        $cmd = CmdManager::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        if (str_word_count($period) == 1 && is_numeric(trim($period)[0])) {
            $startHist = date(DateFormat::FULL, strtotime(date(DateFormat::FULL) . ' -' . $period));
        } else {
            $startHist = date(DateFormat::FULL, strtotime($period));
            if ($startHist == date(DateFormat::FULL, strtotime(0))) {
                return '';
            }
        }
        $calc = str_replace(' ', '', $calc);
        $historyStatistique = $cmd->getStatistique($startHist, date(DateFormat::FULL));
        if ($historyStatistique['min'] == '') {
            return $cmd->execCmd();
        }
        return $historyStatistique[$calc];
    }

    /**
     * @TODO: Statistiques de quelque chose entre deux dates
     *
     * @param $cmdId
     * @param $calc
     * @param $startDate
     * @param $endDate
     * @return string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function statisticsBetween($cmdId, $calc, $startDate, $endDate)
    {
        $cmd = CmdManager::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $calc = str_replace(' ', '', $calc);
        $startDate = date(DateFormat::FULL, strtotime(self::setTags($startDate)));
        $endDate = date(DateFormat::FULL, strtotime(self::setTags($endDate)));
        $historyStatistique = $cmd->getStatistique(self::setTags($startDate), self::setTags($endDate));
        return $historyStatistique[$calc];
    }

    /**
     * Obtenir la valeur d'une variable
     *
     * @param $name
     * @param string $defaultValue Valeur par défaut
     * @return string
     * @throws \Exception
     */
    public static function variable($name, $defaultValue = '')
    {
        // @TODO: Yolo sur les trims
        $name = trim(trim(trim($name), '"'));
        $dataStore = DataStoreManager::byTypeLinkIdKey('scenario', -1, trim($name));
        if (is_object($dataStore)) {
            $value = $dataStore->getValue($defaultValue);
            return $value;
        }
        return $defaultValue;
    }

    /**
     * Obtenir la durée d'un état
     *
     * @param $cmdId
     * @param null $value
     * @return false|int
     * @throws \Exception
     */
    public static function stateDuration($cmdId, $value = null)
    {
        return HistoryManager::stateDuration(str_replace('#', '', $cmdId), $value);
    }

    /**
     * @TODO: Dernier changement de la durée de ???
     *
     * @param $cmdId
     * @param $value
     * @return false|int
     * @throws \Exception
     */
    public static function lastChangeStateDuration($cmdId, $value)
    {
        return HistoryManager::lastChangeStateDuration(str_replace('#', '', $cmdId), $value);
    }

    /**
     * Tester si une valeur est paire
     * @TODO: Changer en binaire le résultat
     *
     * @param mixed $value
     *
     * @return int 1 si $value est pair, sinon 0
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function odd($value): int
    {
        $value = intval(Utils::evaluate(self::setTags($value)));
        if ($value % 2) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Obtenir l'interval de temps depuis lequel le scénario s'est exécuté
     *
     * @param string $scenarioId Identifiant du scénario
     * @return false|int
     * @throws \Exception
     */
    public static function lastScenarioExecution($scenarioId)
    {
        $scenario = ScenarioManager::byId(str_replace(['#scenario', '#'], '', $scenarioId));
        if (!is_object($scenario)) {
            return 0;
        }
        return strtotime('now') - strtotime($scenario->getLastLaunch());
    }

    /**
     * @TODO: Collecter une date
     *
     * @param $cmdId
     * @param string $format
     * @return false|int|string
     * @throws \Exception
     */
    public static function collectDate($cmdId, $format = DateFormat::FULL)
    {
        $cmdObj = CmdManager::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmdObj)) {
            return -1;
        }
        if (!$cmdObj->isType(CmdType::INFO)) {
            return -2;
        }
        $cmdObj->execCmd();
        return date($format, strtotime($cmdObj->getCollectDate()));
    }

    /**
     * @TODO: Valeur d'une date
     *
     * @param $cmdId
     * @param string $format
     * @return false|string
     * @throws \Exception
     */
    public static function valueDate($cmdId, $format = DateFormat::FULL)
    {
        $cmd = CmdManager::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd)) {
            return '';
        }
        $cmd->execCmd();
        return date($format, strtotime($cmd->getValueDate()));
    }

    /**
     * @param $_eqLogic_id
     * @param string $_format
     * @return false|int|string
     * @throws \Exception
     */
    /**
     * @param $_eqLogic_id
     * @param string $_format
     * @return false|int|string
     * @throws \Exception
     */
    /**
     * @param $_eqLogic_id
     * @param string $_format
     * @return false|int|string
     * @throws \Exception
     */
    public static function lastCommunication($_eqLogic_id, $_format = DateFormat::FULL)
    {
        $eqLogic = EqLogicManager::byId(trim(str_replace(['#', '#eqLogic', 'eqLogic'], '', EqLogicManager::fromHumanReadable('#' . str_replace('#', '', $_eqLogic_id) . '#'))));
        if (!is_object($eqLogic)) {
            return -1;
        }
        return date($_format, strtotime($eqLogic->getStatus('lastCommunication', date(DateFormat::FULL))));
    }

    /**
     * @param $_cmd_id
     * @return mixed|string
     * @throws \NextDom\Exceptions\CoreException
     */
    /**
     * @param $_cmd_id
     * @return mixed|string
     * @throws \NextDom\Exceptions\CoreException
     */
    /**
     * @param $_cmd_id
     * @return mixed|string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function value($_cmd_id)
    {
        $cmd = CmdManager::byId(trim(str_replace('#', '', CmdManager::humanReadableToCmd('#' . str_replace('#', '', $_cmd_id) . '#'))));
        if (!is_object($cmd)) {
            return '';
        }
        return $cmd->execCmd();
    }

    /**
     * Obtenir une couleur aléatoire
     *
     * @param $rangeLower
     * @param $rangeHighter
     * @return string
     */
    public static function randomColor($rangeLower, $rangeHighter)
    {
        $value = mt_rand($rangeLower, $rangeHighter);
        $color_range = 85;
        $color = new \stdClass();
        $color->red = $rangeLower;
        $color->green = $rangeLower;
        $color->blue = $rangeLower;
        if ($value < $color_range * 1) {
            $color->red += $color_range - $value;
            $color->green += $value;
        } elseif ($value < $color_range * 2) {
            $color->green += $color_range - $value;
            $color->blue += $value;
        } elseif ($value < $color_range * 3) {
            $color->blue += $color_range - $value;
            $color->red += $value;
        }
        $color->red = ($color->red < 0) ? dechex(0) : dechex(round($color->red));
        $color->blue = ($color->blue < 0) ? dechex(0) : dechex(round($color->blue));
        $color->green = ($color->green < 0) ? dechex(0) : dechex(round($color->green));
        $color->red = (strlen($color->red) == 1) ? '0' . $color->red : $color->red;
        $color->green = (strlen($color->green) == 1) ? '0' . $color->green : $color->green;
        $color->blue = (strlen($color->blue) == 1) ? '0' . $color->blue : $color->blue;
        return '#' . $color->red . $color->green . $color->blue;
    }

    /**
     * Arrondir une valeur
     *
     * @param mixed $value Valeur à arrondir
     * @param int $decimal Nombre de décimales
     *
     * @return float Valeur arrondie.
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function round($value, $decimal = 0)
    {
        $value = self::setTags($value);
        try {
            $result = Utils::evaluate($value);
            if (is_string($result)) {
                $result = $value;
            }
        } catch (\Exception $e) {
            $result = $value;
        }
        if ($decimal == 0) {
            return ceil(floatval(str_replace(',', '.', $result)));
        } else {
            return round(floatval(str_replace(',', '.', $result)), $decimal);
        }
    }

    /**
     * @TODO:? ???
     *
     * @param $time
     * @param $value
     * @return int|string
     * @throws \Exception
     */
    public static function time_op($time, $value)
    {
        $time = self::setTags($time);
        $value = self::setTags($value);
        $time = ltrim($time, 0);
        switch (strlen($time)) {
            case 1:
                $date = \DateTime::createFromFormat('Gi', '000' . intval(trim($time)));
                break;
            case 2:
                $date = \DateTime::createFromFormat('Gi', '00' . intval(trim($time)));
                break;
            case 3:
                $date = \DateTime::createFromFormat('Gi', '0' . intval(trim($time)));
                break;
            default:
                $date = \DateTime::createFromFormat('Gi', intval(trim($time)));
                break;
        }
        if ($date === false) {
            return -1;
        }
        if ($value > 0) {
            $date->add(new \DateInterval('PT' . abs($value) . 'M'));
        } else {
            $date->sub(new \DateInterval('PT' . abs($value) . 'M'));
        }
        return $date->format('Gi');
    }

    /**
     * Tester si une date se trouve dans un interval
     *
     * @param $time
     * @param $startInverval
     * @param $endInterval
     *
     * @return int @TODO: 0, 1
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function time_between($time, $startInverval, $endInterval)
    {
        $time = self::setTags($time);
        $startInverval = self::setTags($startInverval);
        $endInterval = self::setTags($endInterval);
        if ($startInverval < $endInterval) {
            $result = (($time >= $startInverval) && ($time < $endInterval)) ? 1 : 0;
        } else {
            $result = (($time >= $startInverval) || ($time < $endInterval)) ? 1 : 0;
        }
        return $result;
    }

    /**
     * Obtenir l'interval entre deux dates
     *
     * @param string $date1Str Première date au format texte
     * @param string $date2Str Seconde date au format texte
     * @param string $intervalFormat Format de l'interval (s : secondes, m : minutes, h : heures, d : jours)
     *
     * @return float|int|string
     */
    public static function time_diff($date1Str, $date2Str, $intervalFormat = 'd')
    {
        $date1 = new \DateTime($date1Str);
        $date2 = new \DateTime($date2Str);
        $interval = $date1->diff($date2);
        if ($intervalFormat == 's') {
            return intval($interval->format('%s')) + 60 * intval($interval->format('%i')) + 3600 * intval($interval->format('%h')) + 86400 * intval($interval->format('%a'));
        }
        if ($intervalFormat == 'm') {
            return intval($interval->format('%i')) + 60 * intval($interval->format('%h')) + 1440 * intval($interval->format('%a'));
        }
        if ($intervalFormat == 'h') {
            return intval($interval->format('%h')) + 24 * intval($interval->format('%a'));
        }
        return $interval->format('%a');
    }

    /** @noinspection PhpOptionalBeforeRequiredParametersInspection */

    /**
     * @TODO: L'heure mais ça à l'air plus compliqué que ça
     *
     * @param $value
     * @return int|mixed|string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function time($value)
    {
        $value = self::setTags($value);
        try {
            $result = Utils::evaluate($value);
            if (is_string($result)) {
                $result = $value;
            }
        } catch (\Exception $e) {
            $result = $value;
        }
        if ($result < 0) {
            return -1;
        }
        if (($result % 100) > 59) {
            if (strpos($value, '-') !== false) {
                $result -= 40;
            } else {
                $result += 40;
            }

        }
        return $result;
    }

    /**
     * @TODO: Formate l'heure
     *
     * @param $time
     * @return string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function formatTime($time)
    {
        $time = self::setTags($time);
        if (strlen($time) > 3) {
            return substr($time, 0, 2) . 'h' . substr($time, 2, 2);
        } elseif (strlen($time) > 2) {
            return substr($time, 0, 1) . 'h' . substr($time, 1, 2);
        } elseif (strlen($time) > 1) {
            return '00h' . substr($time, 0, 2);
        } else {
            return '00h0' . substr($time, 0, 1);
        }
    }

    /**
     * @TODO: My name is Bond, James Bond
     *
     * @param $type
     * @param $cmdId
     * @return string
     * @throws \Exception
     */
    public static function name($type, $cmdId)
    {
        $cmd = CmdManager::byId(str_replace('#', '', $cmdId));
        if (!is_object($cmd)) {
            $cmd = CmdManager::byId(trim(str_replace('#', '', CmdManager::humanReadableToCmd('#' . str_replace('#', '', $cmdId) . '#'))));
        }
        if (!is_object($cmd)) {
            return __('Commande non trouvée');
        }
        switch ($type) {
            case 'cmd':
                return $cmd->getName();
            case 'eqLogic':
                return $cmd->getEqLogicId()->getName();
            case 'object':
                $linkedObject = $cmd->getEqLogicId()->getObject();
                if (!is_object($linkedObject)) {
                    return __('Aucun');
                }
                return $linkedObject->getName();
        }
        return __('Type inconnu');
    }

    /**
     * @TODO: Créé et exécute un truc
     *
     * @param $type
     * @param $cmd
     * @param null $options
     * @return mixed
     * @throws \Exception
     */
    public static function createAndExec($type, $cmd, $options = null)
    {
        $scenarioExpression = new ScenarioExpression();
        $scenarioExpression->setType($type);
        $scenarioExpression->setExpression($cmd);
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $scenarioExpression->setOptions($key, $value);
            }
        }
        return $scenarioExpression->execute();
    }
}
