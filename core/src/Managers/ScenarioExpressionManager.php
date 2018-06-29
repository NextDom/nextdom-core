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

namespace NextDom\Managers;

class ScenarioExpressionManager
{
    const DB_CLASS_NAME = 'scenarioExpression';
    const CLASS_NAME = 'scenarioExpression';
    const WAIT_LIMIT = 7200;

    /**
     * Obtenir une expression de scénario à partir de son identifiant
     *
     * @param mixed $id Identifiant
     *
     * @return array|mixed|null
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        $values = array('id' => $id);
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Obtenir toutes les expressions de scénario
     *
     * @return array|mixed|null
     *
     * @throws \Exception
     */
    public static function all()
    {
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME;
        return \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }


    /**
     * Obtenir le sous élément d'un scénario à partir de son identifiant
     *
     * TODO: Remplacable par ScenarioSubElementManager:byId si j'ai bien compris
     *
     * @param $_scenarioSubElementId
     *
     * @return array|mixed|null
     *
     * @throws \Exception
     */
    public static function byScenarioSubElementId($_scenarioSubElementId)
    {
        $values = array('scenarioSubElement_id' => $_scenarioSubElementId);
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE scenarioSubElement_id = :scenarioSubElement_id
                ORDER BY `order`';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Rechercher une expression d'après son nom et/ou avec une option
     *
     * @param string $expression Expression recherchée
     * @param string $options Option recherchée
     * @param bool $and True si l'expression et l'option sont un critère, sinon l'expression ou l'option
     *
     * @return array|mixed|null
     *
     * @throws \Exception
     */
    public static function searchExpression($expression, $options = null, $and = true)
    {
        $values = array('expression' => '%' . $expression . '%');
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE expression LIKE :expression ';
        if ($options !== null) {
            $values['options'] = '%' . $options . '%';
            if ($and) {
                $sql .= 'AND options LIKE :options';
            } else {
                $sql .= 'OR options LIKE :options';
            }
        }
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Recherche une expression de type "element" ou l'expression est égale à l'identifiant de l'élément TODO ????
     *
     * @param $elementId
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function byElement($elementId)
    {
        $values = array('expression' => $elementId);
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE expression = :expression
        AND `type` = "element"';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * TODO ????
     * TODO Revoir la génération des UID
     *
     * @param $expression
     * @param $options
     *
     * @return array
     */
    public static function getExpressionOptions($expression, $options)
    {
        $replace = array(
            '#uid#' => 'exp' . mt_rand(),
        );
        $return = array('html' => '');
        $cmd = \cmd::byId(str_replace('#', '', \cmd::humanReadableToCmd($expression)));
        if (is_object($cmd)) {
            $return['html'] = trim($cmd->toHtml('scenario', $options));
            return $return;
        }
        $return['template'] = getTemplate('core', 'scenario', $expression . '.default');
        if (is_json($options)) {
            $options = json_decode($options, true);
        }
        if (is_array($options) && count($options) > 0) {
            foreach ($options as $key => $value) {
                $replace['#' . $key . '#'] = str_replace('"', '&quot;', $value);
            }
        }
        if (!isset($replace['#id#'])) {
            $replace['#id#'] = rand();
        }
        $return['html'] = template_replace(\cmd::cmdToHumanReadable($replace), $return['template']);
        preg_match_all("/#[a-zA-Z_]*#/", $return['template'], $matches);
        foreach ($matches[0] as $value) {
            if (!isset($replace[$value])) {
                $replace[$value] = '';
            }
        }
        $return['html'] = \translate::exec(template_replace($replace, $return['html']), 'core/template/scenario/' . $expression . '.default');
        return $return;
    }

    /**
     * TODO ????
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
            $result .= __('Scénario : ', __FILE__) . $name . ' <i class="fa fa-arrow-right"></i> ' . $action;
        } elseif ($baseAction['cmd'] == 'variable') {
            $name = $baseAction['options']['name'];
            $value = $baseAction['options']['value'];
            $result .= __('Variable : ', __FILE__) . $name . ' <i class="fa fa-arrow-right"></i> ' . $value;
        } elseif (is_object(\cmd::byId(str_replace('#', '', $baseAction['cmd'])))) {
            $cmd = \cmd::byId(str_replace('#', '', $baseAction['cmd']));
            $eqLogic = $cmd->getEqLogic();
            $result .= $eqLogic->getHumanName(true) . ' ' . $cmd->getName();
        }
        return trim($result);
    }

    /**
     * Obtenir un nombre aléatoire
     *
     * @param $minValue
     * @param $maxValue
     *
     * @return int
     */
    public static function rand($minValue, $maxValue): int
    {
        return rand($minValue, $maxValue);
    }

    /**
     * TODO ???
     * TODO Result n'est jamais utilisé, le bloc Try peut normalement être supprimé
     * @param $_sValue
     *
     * @return array|mixed
     */
    public static function randText($_sValue)
    {
        $_sValue = self::setTags($_sValue);
        $_aValue = explode(";", $_sValue);
        try {
            $result = evaluate($_aValue);
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
     * Obtenir un scénario à partir de son expression
     * TODO: Format ???
     *
     * @param $scenarioExpression
     * @return int TODO -1, -2, -3 ????
     * @throws \Exception
     */
    public static function scenario($scenarioExpression)
    {
        $id = str_replace(array('scenario', '#'), '', trim($scenarioExpression));
        $scenario = ScenarioManager::byId($id);
        if (!is_object($scenario)) {
            return -2;
        }
        $state = $scenario->getState();
        if ($scenario->getIsActive() == 0) {
            return -1;
        }
        switch ($state) {
            case 'stop':
                return 0;
            case 'in progress':
                return 1;
        }
        return -3;
    }

    /**
     * Active un objet eqLogic
     * TODO: -2 en -1 ?
     * @param mixed $eqLogicId Identifiant du l'objet
     *
     * @return int 0 Si l'objet n'est pas activé, 1 si l'objet est activé, -2 si l'objet n'existe pas
     */
    public static function eqEnable($eqLogicId)
    {
        $id = str_replace(array('eqLogic', '#'), '', trim($eqLogicId));
        $eqLogic = \eqLogic::byId($id);
        if (!is_object($eqLogic)) {
            return -2;
        }
        return $eqLogic->getIsEnable();
    }

    /**
     * TODO: Fait une moyenne de quelque chose
     * TODO: Mettre en place la gestion du nombre de paramètres variables
     *
     * @param mixed $cmdId Identifiant de la commande
     * @param string $period Période sur laquelle la moyenne doit être calculée
     *
     * @return float|int|string
     */
    public static function average($cmdId, $period = '1 hour')
    {
        $args = func_get_args();
        if (count($args) > 2 || strpos($period, '#') !== false || is_numeric($period)) {
            $values = array();
            foreach ($args as $arg) {
                if (is_numeric($arg)) {
                    $values[] = $arg;
                } else {
                    $value = \cmd::cmdToValue($arg);
                    if (is_numeric($value)) {
                        $values[] = $value;
                    } else {
                        try {
                            $values[] = evaluate($value);
                        } catch (\Exception $ex) {

                        } catch (\Error $ex) {

                        }
                    }
                }
            }
            return array_sum($values) / count($values);
        } else {
            $cmd = \cmd::byId(trim(str_replace('#', '', $cmdId)));
            if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
                return '';
            }
            if (str_word_count($period) == 1 && is_numeric(trim($period)[0])) {
                $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . $period));
            } else {
                $startHist = date('Y-m-d H:i:s', strtotime($period));
                if ($startHist == date('Y-m-d H:i:s', strtotime(0))) {
                    return '';
                }
            }
            $historyStatistique = $cmd->getStatistique($startHist, date('Y-m-d H:i:s'));
            if (!isset($historyStatistique['avg']) || $historyStatistique['avg'] == '') {
                return $cmd->execCmd();
            }
            return round($historyStatistique['avg'], 1);
        }
    }

    /**
     * TODO: Calcule une moyenne de quelque chose entre deux dates
     *
     * @param $cmdId
     * @param $startDate
     * @param $endDate
     *
     * @return float|string
     */
    public static function averageBetween($cmdId, $startDate, $endDate)
    {
        $cmd = \cmd::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $startDate = date('Y-m-d H:i:s', strtotime(self::setTags($startDate)));
        $endDate = date('Y-m-d H:i:s', strtotime(self::setTags($endDate)));
        $historyStatistique = $cmd->getStatistique($startDate, $endDate);
        if (!isset($historyStatistique['avg'])) {
            return '';
        }
        return round($historyStatistique['avg'], 1);
    }

    /**
     * Obtenir la valeur maximum sur une période TODO: de quelque chose
     *
     * @param $cmdId
     * @param string $period
     * @return float|mixed|string
     */
    public static function max($cmdId, $period = '1 hour')
    {
        $args = func_get_args();
        if (count($args) > 2 || strpos($period, '#') !== false || is_numeric($period)) {
            $values = array();
            foreach ($args as $arg) {
                if (is_numeric($arg)) {
                    $values[] = $arg;
                } else {
                    $value = \cmd::cmdToValue($arg);
                    if (is_numeric($value)) {
                        $values[] = $value;
                    } else {
                        try {
                            $values[] = evaluate($value);
                        } catch (\Exception $ex) {

                        } catch (\Error $ex) {

                        }
                    }
                }
            }
            return max($values);
        } else {
            $cmd = \cmd::byId(trim(str_replace('#', '', $cmdId)));
            if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
                return '';
            }
            if (str_word_count($period) == 1 && is_numeric(trim($period)[0])) {
                $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . $period));
            } else {
                $startHist = date('Y-m-d H:i:s', strtotime($period));
                if ($startHist == date('Y-m-d H:i:s', strtotime(0))) {
                    return '';
                }
            }
            $historyStatistique = $cmd->getStatistique($startHist, date('Y-m-d H:i:s'));
            if (!isset($historyStatistique['max']) || $historyStatistique['max'] == '') {
                return $cmd->execCmd();
            }
            return round($historyStatistique['max'], 1);
        }
    }

    /**
     * Obtenir la valeur maximum entre deux dates TODO: de quelque chose
     *
     * @param $cmdId
     * @param $startDate
     * @param $endDate
     * @return float|string
     */
    public static function maxBetween($cmdId, $startDate, $endDate)
    {
        $cmd = \cmd::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $startDate = date('Y-m-d H:i:s', strtotime(self::setTags($startDate)));
        $endDate = date('Y-m-d H:i:s', strtotime(self::setTags($endDate)));
        // TODO ligne à virer, voir si ça lance quelque chose avant
        $historyStatistique = $cmd->getStatistique($startDate, $endDate);
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
        $timeout = \nextdom::evaluateExpression($waitTimeout);
        // Si le timeout
        $limit = (is_numeric($timeout)) ? $timeout : self::WAIT_LIMIT;
        while ($result !== true) {
            $result = \nextdom::evaluateExpression($condition);
            if ($occurence > $limit) {
                return 0;
            }
            $occurence++;
            sleep(1);
        }
        return 1;
    }

    /**
     * Obtenir la valeur minimum sur une période TODO: ??? Toujours sur quoi ?
     * @param $cmdId
     * @param string $period
     * @return float|mixed|string
     */
    public static function min($cmdId, $period = '1 hour')
    {
        $args = func_get_args();
        if (count($args) > 2 || strpos($period, '#') !== false || is_numeric($period)) {
            $values = array();
            foreach ($args as $arg) {
                if (is_numeric($arg)) {
                    $values[] = $arg;
                } else {
                    $value = \cmd::cmdToValue($arg);
                    if (is_numeric($value)) {
                        $values[] = $value;
                    } else {
                        try {
                            $values[] = evaluate($value);
                        } catch (\Exception $ex) {

                        } catch (\Error $ex) {

                        }
                    }
                }
            }
            return min($values);
        } else {
            $cmd = \cmd::byId(trim(str_replace('#', '', $cmdId)));
            if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
                return '';
            }
            if (str_word_count($period) == 1 && is_numeric(trim($period)[0])) {
                $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . $period));
            } else {
                $startHist = date('Y-m-d H:i:s', strtotime($period));
                if ($startHist == date('Y-m-d H:i:s', strtotime(0))) {
                    return '';
                }
            }
            $historyStatistique = $cmd->getStatistique($startHist, date('Y-m-d H:i:s'));
            if (!isset($historyStatistique['min']) || $historyStatistique['min'] == '') {
                return $cmd->execCmd();
            }
            return round($historyStatistique['min'], 1);
        }
    }

    /**
     * Obtenir la valeur minimum entre deux dates TODO: De quoi ?
     *
     * @param $cmdId
     * @param $startDate
     * @param $endDate
     * @return float|string
     */
    public static function minBetween($cmdId, $startDate, $endDate)
    {
        $cmd = \cmd::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $startDate = date('Y-m-d H:i:s', strtotime(self::setTags($startDate)));
        $endDate = date('Y-m-d H:i:s', strtotime(self::setTags($endDate)));
        $historyStatistique = $cmd->getStatistique($startDate, $endDate);
        if (!isset($historyStatistique['min'])) {
            return '';
        }
        return round($historyStatistique['min'], 1);
    }

    /**
     * Obtenir une valeur médiane TODO: De quoi ?
     *
     * @return int|mixed
     */
    public static function median()
    {
        $args = func_get_args();
        $values = array();
        foreach ($args as $arg) {
            if (is_numeric($arg)) {
                $values[] = $arg;
            } else {
                $value = \cmd::cmdToValue($arg);
                if (is_numeric($value)) {
                    $values[] = $value;
                } else {
                    try {
                        $values[] = evaluate($value);
                    } catch (\Exception $ex) {

                    } catch (\Error $ex) {

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
     * Renvoie une tendance TODO de ?
     *
     * @param $cmdId
     * @param string $period
     * @param string $threshold
     * @return int|string
     */
    public static function tendance($cmdId, $period = '1 hour', $threshold = '')
    {
        $cmd = \cmd::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd)) {
            return '';
        }
        if ($cmd->getIsHistorized() == 0) {
            return '';
        }
        $endTime = date('Y-m-d H:i:s');
        if (str_word_count($period) == 1 && is_numeric(trim($period)[0])) {
            $startTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . $period));
        } else {
            $startTime = date('Y-m-d H:i:s', strtotime($period));
            if ($startTime == date('Y-m-d H:i:s', strtotime(0))) {
                return '';
            }
        }
        $tendance = $cmd->getTendance($startTime, $endTime);
        if ($threshold != '') {
            $maxThreshold = $threshold;
            $minThreshold = -$threshold;
        } else {
            $maxThreshold = \config::byKey('historyCalculTendanceThresholddMax');
            $minThreshold = \config::byKey('historyCalculTendanceThresholddMin');
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
     * TODO: j'en sais rien, durée pendant laquelle il a conserver son état sans doute
     * 
     * @param $cmdId
     * @param null $value
     * @return false|int
     * @throws \Exception
     */
    public static function lastStateDuration($cmdId, $value = null)
    {
        return \history::lastStateDuration(str_replace('#', '', $cmdId), $value);
    }

    /**
     * TODO Changement d'état, ou de pantalon, ou de slip
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
            $cmd = \cmd::byId(str_replace('#', '', \cmd::humanReadableToCmd($cmdId)));
        } else {
            $cmd = \cmd::byId(str_replace('#', '', $cmdId));
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
        return \history::stateChanges($cmd_id, $value, date('Y-m-d H:i:s', strtotime('-' . $period)), date('Y-m-d H:i:s'));
    }

    /**
     * Changement entre deux dates //TODO: Woohoo
     *
     * @param $cmdId
     * @param $value
     * @param $startDate
     * @param null $_endDate
     * @return array|string
     * @throws \Exception
     */
    public static function stateChangesBetween($cmdId, $value, $startDate, $_endDate = null)
    {
        if (!is_numeric(str_replace('#', '', $cmdId))) {
            $cmd = \cmd::byId(str_replace('#', '', \cmd::humanReadableToCmd($cmdId)));
        } else {
            $cmd = \cmd::byId(str_replace('#', '', $cmdId));
        }
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $cmd_id = $cmd->getId();

        $args = func_num_args();
        if ($args == 3) {
            $_endDate = func_get_arg(2);
            $startDate = func_get_arg(1);
            $value = null;
        }
        $startDate = date('Y-m-d H:i:s', strtotime(self::setTags($startDate)));
        $_endDate = date('Y-m-d H:i:s', strtotime(self::setTags($_endDate)));

        return \history::stateChanges($cmd_id, $value, $startDate, $_endDate);
    }

    public static function duration($cmdId, $_value, $_period = '1 hour')
    {
        $cmd_id = str_replace('#', '', $cmdId);
        if (!is_numeric($cmd_id)) {
            $cmd_id = \cmd::byId(str_replace('#', '', \cmd::humanReadableToCmd($cmdId)));
        }
        $cmd = \cmd::byId($cmd_id);
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }

        if (str_word_count($_period) == 1 && is_numeric(trim($_period)[0])) {
            $_startDate = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . $_period));
        } else {
            $_startDate = date('Y-m-d H:i:s', strtotime($_period));
            if ($_startDate == date('Y-m-d H:i:s', strtotime(0))) {
                return '';
            }
        }
        $_endDate = date('Y-m-d H:i:s');
        $_value = str_replace(',', '.', $_value);
        $_decimal = strlen(substr(strrchr($_value, "."), 1));

        $histories = $cmd->getHistory();

        if (count($histories) == 0) {
            return '';
        }

        $duration = 0;
        $lastDuration = strtotime($histories[0]->getDatetime());
        $lastValue = $histories[0]->getValue();

        foreach ($histories as $history) {
            if ($history->getDatetime() >= $_startDate) {
                if ($history->getDatetime() <= $_endDate) {
                    if ($lastValue == $_value) {
                        $duration = $duration + (strtotime($history->getDatetime()) - $lastDuration);
                    }
                } else {
                    if ($lastValue == $_value) {
                        $duration = $duration + (strtotime($_endDate) - $lastDuration);
                    }
                    break;
                }
                $lastDuration = strtotime($history->getDatetime());
            } else {
                $lastDuration = strtotime($_startDate);
            }
            $lastValue = round($history->getValue(), $_decimal);
        }
        if ($lastValue == $_value && $lastDuration <= strtotime($_endDate)) {
            $duration = $duration + (strtotime($_endDate) - $lastDuration);
        }
        return floor($duration / 60);
    }

    public static function durationBetween($cmdId, $_value, $_startDate, $_endDate)
    {
        if (!is_numeric(str_replace('#', '', $cmdId))) {
            $cmd = \cmd::byId(str_replace('#', '', \cmd::humanReadableToCmd($cmdId)));
        } else {
            $cmd = \cmd::byId(str_replace('#', '', $cmdId));
        }
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }

        $_startDate = date('Y-m-d H:i:s', strtotime(self::setTags($_startDate)));
        $_endDate = date('Y-m-d H:i:s', strtotime(self::setTags($_endDate)));
        $_value = str_replace(',', '.', $_value);
        $_decimal = strlen(substr(strrchr($_value, "."), 1));

        $histories = $cmd->getHistory();

        $duration = 0;
        $lastDuration = strtotime($histories[0]->getDatetime());
        $lastValue = $histories[0]->getValue();

        foreach ($histories as $history) {
            if ($history->getDatetime() >= $_startDate) {
                if ($history->getDatetime() <= $_endDate) {
                    if ($lastValue == $_value) {
                        $duration = $duration + (strtotime($history->getDatetime()) - $lastDuration);
                    }
                } else {
                    if ($lastValue == $_value) {
                        $duration = $duration + (strtotime($_endDate) - $lastDuration);
                    }
                    break;
                }
                $lastDuration = strtotime($history->getDatetime());
            } else {
                $lastDuration = strtotime($_startDate);
            }
            $lastValue = round($history->getValue(), $_decimal);
        }
        if ($lastValue == $_value && $lastDuration <= strtotime($_endDate)) {
            $duration = $duration + (strtotime($_endDate) - $lastDuration);
        }
        return floor($duration / 60);
    }

    public static function lastBetween($cmdId, $_startDate, $_endDate)
    {
        $cmd = \cmd::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $_startDate = date('Y-m-d H:i:s', strtotime(self::setTags($_startDate)));
        $_endDate = date('Y-m-d H:i:s', strtotime(self::setTags($_endDate)));
        $historyStatistique = $cmd->getStatistique($_startDate, $_endDate);
        return round($historyStatistique['last'], 1);
    }

    public static function statistics($cmdId, $_calc, $_period = '1 hour')
    {

        $cmd = \cmd::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        if (str_word_count($_period) == 1 && is_numeric(trim($_period)[0])) {
            $startHist = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -' . $_period));
        } else {
            $startHist = date('Y-m-d H:i:s', strtotime($_period));
            if ($startHist == date('Y-m-d H:i:s', strtotime(0))) {
                return '';
            }
        }
        $_calc = str_replace(' ', '', $_calc);
        $historyStatistique = $cmd->getStatistique($startHist, date('Y-m-d H:i:s'));
        if ($historyStatistique['min'] == '') {
            return $cmd->execCmd();
        }
        return $historyStatistique[$_calc];
    }

    public static function statisticsBetween($cmdId, $_calc, $_startDate, $_endDate)
    {
        $cmd = \cmd::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }
        $_calc = str_replace(' ', '', $_calc);
        $_startDate = date('Y-m-d H:i:s', strtotime(self::setTags($_startDate)));
        $_endDate = date('Y-m-d H:i:s', strtotime(self::setTags($_endDate)));
        $historyStatistique = $cmd->getStatistique(self::setTags($_startDate), self::setTags($_endDate));
        return $historyStatistique[$_calc];
    }

    public static function variable($_name, $_default = '')
    {
        $_name = trim(trim(trim($_name), '"'));
        $dataStore = \dataStore::byTypeLinkIdKey('scenario', -1, trim($_name));
        if (is_object($dataStore)) {
            $value = $dataStore->getValue($_default);
            return $value;
        }
        return $_default;
    }

    public static function stateDuration($cmdId, $_value = null)
    {
        return \history::stateDuration(str_replace('#', '', $cmdId), $_value);
    }

    public static function lastChangeStateDuration($cmdId, $_value)
    {
        return \history::lastChangeStateDuration(str_replace('#', '', $cmdId), $_value);
    }

    public static function odd($_value)
    {
        $_value = intval(evaluate(self::setTags($_value)));
        return ($_value % 2) ? 1 : 0;
    }

    public static function lastScenarioExecution($_scenario_id)
    {
        $scenario = ScenarioManager::byId(str_replace(array('#scenario', '#'), '', $_scenario_id));
        if (!is_object($scenario)) {
            return 0;
        }
        return strtotime('now') - strtotime($scenario->getLastLaunch());
    }

    public static function collectDate($_cmd, $_format = 'Y-m-d H:i:s')
    {
        $cmd = \cmd::byId(trim(str_replace('#', '', $_cmd)));
        if (!is_object($cmd)) {
            return -1;
        }
        if ($cmd->getType() != 'info') {
            return -2;
        }
        $cmd->execCmd();
        return date($_format, strtotime($cmd->getCollectDate()));
    }

    public static function valueDate($cmdId, $_format = 'Y-m-d H:i:s')
    {
        $cmd = \cmd::byId(trim(str_replace('#', '', $cmdId)));
        if (!is_object($cmd)) {
            return '';
        }
        $cmd->execCmd();
        return date($_format, strtotime($cmd->getValueDate()));
    }

    public static function randomColor($_rangeLower, $_rangeHighter)
    {
        $value = rand($_rangeLower, $_rangeHighter);
        $color_range = 85;
        $color = new stdClass();
        $color->red = $_rangeLower;
        $color->green = $_rangeLower;
        $color->blue = $_rangeLower;
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

    public static function trigger($_name = '', &$_scenario = null)
    {
        if ($_scenario !== null) {
            if (trim($_name) == '') {
                return $_scenario->getRealTrigger();
            }
            if ($_name == $_scenario->getRealTrigger()) {
                return 1;
            }
        }
        return 0;
    }

    public static function triggerValue(&$_scenario = null)
    {
        if ($_scenario !== null) {
            $cmd = \cmd::byId(str_replace('#', '', $_scenario->getRealTrigger()));
            if (is_object($cmd)) {
                return $cmd->execCmd();
            }
        }
        return false;
    }

    public static function round($_value, $_decimal = 0)
    {
        $_value = self::setTags($_value);
        try {
            $result = evaluate($_value);
            if (is_string($result)) {
                $result = $_value;
            }
        } catch (\Exception $e) {
            $result = $_value;
        }
        if ($_decimal == 0) {
            return ceil(floatval(str_replace(',', '.', $result)));
        } else {
            return round(floatval(str_replace(',', '.', $result)), $_decimal);
        }
    }

    public static function time_op($_time, $_value)
    {
        $_time = self::setTags($_time);
        $_value = self::setTags($_value);
        $_time = ltrim($_time, 0);
        switch (strlen($_time)) {
            case 1:
                $date = \DateTime::createFromFormat('Gi', '000' . intval(trim($_time)));
                break;
            case 2:
                $date = \DateTime::createFromFormat('Gi', '00' . intval(trim($_time)));
                break;
            case 3:
                $date = \DateTime::createFromFormat('Gi', '0' . intval(trim($_time)));
                break;
            default:
                $date = \DateTime::createFromFormat('Gi', intval(trim($_time)));
                break;
        }
        if ($date === false) {
            return -1;
        }
        if ($_value > 0) {
            $date->add(new \DateInterval('PT' . abs($_value) . 'M'));
        } else {
            $date->sub(new \DateInterval('PT' . abs($_value) . 'M'));
        }
        return $date->format('Gi');
    }

    public static function time_between($_time, $_start, $_end)
    {
        $_time = self::setTags($_time);
        $_start = self::setTags($_start);
        $_end = self::setTags($_end);
        if ($_start < $_end) {
            $result = (($_time >= $_start) && ($_time < $_end)) ? 1 : 0;
        } else {
            $result = (($_time >= $_start) || ($_time < $_end)) ? 1 : 0;
        }
        return $result;
    }

    public static function time_diff($_date1, $_date2, $_format = 'd')
    {
        $date1 = new \DateTime($_date1);
        $date2 = new \DateTime($_date2);
        $interval = $date1->diff($date2);
        if ($_format == 's') {
            return $interval->format('%s') + 60 * $interval->format('%m') + 3600 * $interval->format('%h') + 86400 * $interval->format('%a');
        }
        if ($_format == 'm') {
            return $interval->format('%i') + 60 * $interval->format('%h') + 1440 * $interval->format('%a');
        }
        if ($_format == 'h') {
            return $interval->format('%h') + 24 * $interval->format('%a');
        }
        return $interval->format('%a');
    }

    public static function time($_value)
    {
        $_value = self::setTags($_value);
        try {
            $result = evaluate($_value);
            if (is_string($result)) {
                $result = $_value;
            }
        } catch (\Exception $e) {
            $result = $_value;
        }
        if ($result < 0) {
            return -1;
        }
        if (($result % 100) > 59) {
            if (strpos($_value, '-') !== false) {
                $result -= 40;
            } else {
                $result += 40;
            }

        }
        return $result;
    }

    public static function formatTime($_time)
    {
        $_time = self::setTags($_time);
        if (strlen($_time) > 3) {
            return substr($_time, 0, 2) . 'h' . substr($_time, 2, 2);
        } elseif (strlen($_time) > 2) {
            return substr($_time, 0, 1) . 'h' . substr($_time, 1, 2);
        } elseif (strlen($_time) > 1) {
            return '00h' . substr($_time, 0, 2);
        } else {
            return '00h0' . substr($_time, 0, 1);
        }
    }

    public static function name($_type, $cmdId)
    {
        $cmd = \cmd::byId(str_replace('#', '', $cmdId));
        if (!is_object($cmd)) {
            $cmd = \cmd::byId(trim(str_replace('#', '', \cmd::humanReadableToCmd('#' . str_replace('#', '', $cmdId) . '#'))));
        }
        if (!is_object($cmd)) {
            return __('Commande non trouvée', __FILE__);
        }
        switch ($_type) {
            case 'cmd':
                return $cmd->getName();
            case 'eqLogic':
                return $cmd->getEqLogic()->getName();
            case 'object':
                $object = $cmd->getEqLogic()->getObject();
                if (!is_object($object)) {
                    return __('Aucun', __FILE__);
                }
                return $object->getName();
        }
        return __('Type inconnu', __FILE__);
    }

    public static function getRequestTags($_expression)
    {
        $return = array();
        preg_match_all("/#([a-zA-Z0-9]*)#/", $_expression, $matches);
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
                    $return['#sjour#'] = '"' . date_fr(date('l')) . '"';
                    break;
                case '#smois#':
                    $return['#smois#'] = '"' . date_fr(date('F')) . '"';
                    break;
                case '#njour#':
                    $return['#njour#'] = (int)date('w');
                    break;
                case '#nextdom_name#':
                    $return['#nextdom_name#'] = '"' . \config::byKey('name') . '"';
                    break;
                case '#hostname#':
                    $return['#hostname#'] = '"' . gethostname() . '"';
                    break;
                case '#IP#':
                    $return['#IP#'] = '"' . \network::getNetworkAccess('internal', 'ip', '', false) . '"';
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

    public static function tag(&$_scenario = null, $_name, $_default = '')
    {
        if ($_scenario === null) {
            return '"' . $_default . '"';
        }
        $tags = $_scenario->getTags();
        if (isset($tags['#' . $_name . '#'])) {
            return $tags['#' . $_name . '#'];
        }
        return '"' . $_default . '"';
    }

    public static function setTags($_expression, &$_scenario = null, $_quote = false, $_nbCall = 0)
    {
        if (file_exists(dirname(__FILE__) . '/../../data/php/user.function.class.php')) {
            require_once dirname(__FILE__) . '/../../data/php/user.function.class.php';
        }
        if ($_nbCall > 10) {
            return $_expression;
        }
        $replace1 = self::getRequestTags($_expression);
        if ($_scenario !== null && count($_scenario->getTags()) > 0) {
            $replace1 = array_merge($replace1, $_scenario->getTags());
        }

        if (is_object($_scenario)) {
            $cmd = \cmd::byId(str_replace('#', '', $_scenario->getRealTrigger()));
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
        $replace2 = array();
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
                    $result = str_replace($match[2], $arguments, $_expression);
                    while (substr_count($result, '(') > substr_count($result, ')')) {
                        $result .= ')';
                    }
                    $result = self::setTags($result, $_scenario, $_quote, $_nbCall++);
                    return \cmd::cmdToValue(str_replace(array_keys($replace1), array_values($replace1), $result), $_quote);
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
                } else if (class_exists('userFunction') && method_exists('userFunction', $function)) {
                    $replace2[$replace_string] = call_user_func_array('userFunction' . "::" . $function, $arguments);
                } else {
                    if (function_exists($function)) {
                        foreach ($arguments as &$argument) {
                            $argument = trim(evaluate(self::setTags($argument, $_scenario, $_quote)));
                        }
                        $replace2[$replace_string] = call_user_func_array($function, $arguments);
                    }
                }
                if ($_quote && isset($replace2[$replace_string]) && (strpos($replace2[$replace_string], ' ') !== false || preg_match("/[a-zA-Z#]/", $replace2[$replace_string]) || $replace2[$replace_string] === '')) {
                    $replace2[$replace_string] = '"' . trim($replace2[$replace_string], '"') . '"';
                }
            }
        }
        $return = \cmd::cmdToValue(str_replace(array_keys($replace1), array_values($replace1), str_replace(array_keys($replace2), array_values($replace2), $_expression)), $_quote);
        return $return;
    }

    public static function createAndExec($_type, $_cmd, $_options = null)
    {
        $scenarioExpression = new self();
        $scenarioExpression->setType($_type);
        $scenarioExpression->setExpression($_cmd);
        if (is_array($_options)) {
            foreach ($_options as $key => $value) {
                $scenarioExpression->setOptions($key, $value);
            }
        }
        return $scenarioExpression->execute();
    }
}