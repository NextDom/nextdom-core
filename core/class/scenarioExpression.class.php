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

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../core/php/core.inc.php';

use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Managers\ScenarioElementManager;
use NextDom\Managers\ScenarioSubElementManager;

class scenarioExpression {
    /*     * *************************Attributs****************************** */

    private $id;
    private $scenarioSubElement_id;
    private $type;
    private $subtype;
    private $expression;
    private $options;
    private $order;

    /*     * ***********************Méthodes statiques*************************** */

    public static function byId($_id) {
        return ScenarioExpressionManager::byId($_id);
    }

    public static function all() {
        return ScenarioExpressionManager::all();
    }

    public static function byscenarioSubElementId($_scenarioSubElementId) {
        return ScenarioExpressionManager::byScenarioSubElementId($_scenarioSubElementId);
    }

    public static function searchExpression($_expression, $_options = null, $_and = true) {
        return ScenarioExpressionManager::searchExpression($_expression, $_options, $_and);
    }

    public static function byElement($_element_id) {
        return ScenarioExpressionManager::byElement($_element_id);
    }

    public static function getExpressionOptions($_expression, $_options) {
        return ScenarioExpressionManager::getExpressionOptions($_expression, $_options);
    }

    public static function humanAction($_action) {
        return ScenarioExpressionManager::humanAction($_action);
    }

    public static function rand($_min, $_max) {
        return ScenarioExpressionManager::rand($_min, $_max);
    }

    public static function randText($_sValue) {
        return ScenarioExpressionManager::randText($_sValue);
    }

    public static function scenario($_scenario) {
        return ScenarioExpressionManager::scenario($_scenario);
    }

    public static function eqEnable($_eqLogic_id) {
        return ScenarioExpressionManager::eqEnable($_eqLogic_id);
    }

    public static function average($_cmd_id, $_period = '1 hour') {
        return ScenarioExpressionManager::average($_cmd_id, $_period);
    }

    public static function averageBetween($_cmd_id, $_startDate, $_endDate) {
        return ScenarioExpressionManager::averageBetween($_cmd_id, $_startDate, $_endDate);
    }

    public static function max($_cmd_id, $_period = '1 hour') {
        return ScenarioExpressionManager::max($_cmd_id, $_period);
    }

    public static function maxBetween($_cmd_id, $_startDate, $_endDate) {
        return ScenarioExpressionManager::maxBetween($_cmd_id, $_startDate, $_endDate);
    }

    public static function wait($_condition, $_timeout = 7200) {
        return ScenarioExpressionManager::wait($_condition, $_timeout);
    }

    public static function min($_cmd_id, $_period = '1 hour') {
        return ScenarioExpressionManager::min($_cmd_id, $_period);
    }

    public static function minBetween($_cmd_id, $_startDate, $_endDate) {
        return ScenarioExpressionManager::minBetween($_cmd_id, $_startDate, $_endDate);
    }

    public static function median() {
        return ScenarioExpressionManager::median();
    }

    public static function tendance($_cmd_id, $_period = '1 hour', $_threshold = '') {
        return ScenarioExpressionManager::tendance($_cmd_id, $_period, $_threshold);
    }

    public static function lastStateDuration($_cmd_id, $_value = null) {
        return ScenarioExpressionManager::lastStateDuration($_cmd_id, $_value);
    }

    public static function stateChanges($_cmd_id, $_value = null, $_period = '1 hour') {
        return ScenarioExpressionManager::stateChanges($_cmd_id, $_value, $_period);
    }

    public static function stateChangesBetween($_cmd_id, $_value, $_startDate, $_endDate = null) {
        return ScenarioExpressionManager::stateChangesBetween($_cmd_id, $_value, $_startDate, $_endDate);
    }

    public static function duration($_cmd_id, $_value, $_period = '1 hour') {
        return ScenarioExpressionManager::duration($_cmd_id, $_value, $_period);
    }

    public static function durationBetween($_cmd_id, $_value, $_startDate, $_endDate) {
        return ScenarioExpressionManager::durationBetween($_cmd_id, $_value, $_startDate, $_endDate);
    }

    public static function lastBetween($_cmd_id, $_startDate, $_endDate) {
        return ScenarioExpressionManager::lastBetween($_cmd_id, $_startDate, $_endDate);
    }

    public static function statistics($_cmd_id, $_calc, $_period = '1 hour') {
        return ScenarioExpressionManager::statistics($_cmd_id, $_calc, $_period);
    }

    public static function statisticsBetween($_cmd_id, $_calc, $_startDate, $_endDate) {
        return ScenarioExpressionManager::statisticsBetween($_cmd_id, $_calc, $_startDate, $_endDate);
    }

    public static function variable($_name, $_default = '') {
        return ScenarioExpressionManager::variable($_name, $_default);
    }

    public static function stateDuration($_cmd_id, $_value = null) {
        return ScenarioExpressionManager::stateDuration($_cmd_id, $_value);
    }

    public static function lastChangeStateDuration($_cmd_id, $_value) {
        return ScenarioExpressionManager::lastChangeStateDuration($_cmd_id, $_value);
    }

    public static function odd($_value) {
        return ScenarioExpressionManager::odd($_value);
    }

    public static function lastScenarioExecution($_scenario_id) {
        return ScenarioExpressionManager::lastScenarioExecution($_scenario_id);
    }

    public static function collectDate($_cmd, $_format = 'Y-m-d H:i:s') {
        return ScenarioExpressionManager::collectDate($_cmd, $_format);
    }

    public static function valueDate($_cmd_id, $_format = 'Y-m-d H:i:s') {
        return ScenarioExpressionManager::valueDate($_cmd_id, $_format);
    }

    public static function randomColor($_rangeLower, $_rangeHighter) {
        return ScenarioExpressionManager::randomColor($_rangeLower, $_rangeHighter);
    }

    public static function trigger($_name = '', &$_scenario = null) {
        return ScenarioExpressionManager::trigger($_name, $_scenario);
    }

    public static function triggerValue(&$_scenario = null) {
        return ScenarioExpressionManager::triggerValue($_scenario);
    }

    public static function round($_value, $_decimal = 0) {
        return ScenarioExpressionManager::round($_value, $_decimal);
    }

    public static function time_op($_time, $_value) {
        return ScenarioExpressionManager::time_op($_time, $_value);
    }

    public static function time_between($_time, $_start, $_end) {
        return ScenarioExpressionManager::time_between($_time, $_start, $_end);
    }

    public static function time_diff($_date1, $_date2, $_format = 'd') {
        return ScenarioExpressionManager::time_diff($_date1, $_date2, $_format);
    }

    public static function time($_value) {
        return ScenarioExpressionManager::time($_value);
    }

    public static function formatTime($_time) {
        return ScenarioExpressionManager::formatTime($_time);
    }

    public static function name($_type, $_cmd_id) {
        return ScenarioExpressionManager::name($_type, $_cmd_id);
    }

    public static function getRequestTags($_expression) {
        return ScenarioExpressionManager::getRequestTags($_expression);
    }

    public static function tag(&$_scenario = null, $_name, $_default = '') {
        return ScenarioExpressionManager::tag($_scenario, $_name, $_default);
    }

    public static function setTags($_expression, &$_scenario = null, $_quote = false, $_nbCall = 0) {
        return ScenarioExpressionManager::setTags($_expression, $_scenario, $_quote, $_nbCall);
    }

    public static function createAndExec($_type, $_cmd, $_options = null) {
        return ScenarioExpressionManager::createAndExec($_type, $_cmd, $_options);
    }

    public function checkBackground() {
        if ($this->getOptions('background', 0) == 0) {
            return;
        }
        if (in_array($this->getExpression(), array('wait', 'sleep', 'stop', 'scenario_return'))) {
            $this->setOptions('background', 0);
        }
        return;
    }

    public function execute(&$scenario = null) {
        if ($scenario !== null && !$scenario->getDo()) {
            return null;
        }
        if ($this->getOptions('enable', 1) == 0) {
            return null;
        }
        $this->checkBackground();
        if ($this->getOptions('background', 0) == 1) {
            $key = 'scenarioElement' . config::genKey(10);
            while (cache::exist($key)) {
                $key = 'scenarioElement' . config::genKey(10);
            }
            cache::set($key, array('scenarioExpression' => $this, 'scenario' => $scenario), 60);
            $cmd = dirname(__FILE__) . '/../php/jeeScenarioExpression.php';
            $cmd .= ' key=' . $key;
            $this->setLog($scenario, __('Execution du lancement en arriere plan : ', __FILE__) . $key);
            system::php($cmd . ' >> /dev/null 2>&1 &');
            return;
        }
        $message = '';
        try {
            if ($this->getType() == 'element') {
                $element = ScenarioElementManager::byId($this->getExpression());
                if (is_object($element)) {
                    $this->setLog($scenario, __('Exécution d\'un bloc élément : ', __FILE__) . $this->getExpression());
                    return $element->execute($scenario);
                }
                return;
            }
            $options = $this->getOptions();
            if (isset($options['enable'])) {
                unset($options['enable']);
            }
            if (is_array($options) && $this->getExpression() != 'wait') {
                foreach ($options as $key => $value) {
                    if ($this->getExpression() == 'event' && $key == 'cmd') {
                        continue;
                    }
                    if (is_string($value)) {
                        $options[$key] = str_replace('"', '', self::setTags($value, $scenario));
                    }
                }
            }
            if ($this->getType() == 'action') {
                if ($this->getExpression() == 'icon') {
                    if ($scenario !== null) {
                        $options = $this->getOptions();
                        $this->setLog($scenario, __('Changement de l\'icone du scénario : ', __FILE__) . $options['icon']);
                        $scenario->setDisplay('icon', $options['icon']);
                        $scenario->save();
                    }
                    return;
                } elseif ($this->getExpression() == 'wait') {
                    if (!isset($options['condition'])) {
                        return;
                    }
                    $result = false;
                    $occurence = 0;
                    $limit = 7200;
                    if (isset($options['timeout'])) {
                        $timeout = nextdom::evaluateExpression($options['timeout']);
                        $limit = (is_numeric($timeout)) ? $timeout : 7200;
                    }
                    while (!$result) {
                        $expression = self::setTags($options['condition'], $scenario, true);
                        $result = evaluate($expression);
                        if ($occurence > $limit) {
                            $this->setLog($scenario, __('[Wait] Condition valide par dépassement de temps : ', __FILE__) . $expression . ' => ' . $result);
                            return;
                        }
                        $occurence++;
                        sleep(1);
                    }
                    $this->setLog($scenario, __('[Wait] Condition valide : ', __FILE__) . $expression . ' => ' . $result);
                    return;
                } elseif ($this->getExpression() == 'sleep') {
                    if (isset($options['duration'])) {
                        try {
                            $options['duration'] = floatval(evaluate($options['duration']));
                        } catch (Exception $e) {

                        } catch (Error $e) {

                        }
                        if (is_numeric($options['duration']) && $options['duration'] > 0) {
                            $this->setLog($scenario, __('Pause de ', __FILE__) . $options['duration'] . __(' seconde(s)', __FILE__));
                            if ($options['duration'] < 1) {
                                return usleep($options['duration'] * 1000000);
                            } else {
                                return sleep($options['duration']);
                            }
                        }
                    }
                    $this->setLog($scenario, __('Aucune durée trouvée pour l\'action sleep ou la durée n\'est pas valide : ', __FILE__) . $options['duration']);
                    return;
                } elseif ($this->getExpression() == 'stop') {
                    if ($scenario !== null) {
                        $this->setLog($scenario, __('Action stop', __FILE__));
                        $scenario->setDo(false);
                        return;
                    }
                    die();
                } elseif ($this->getExpression() == 'log') {
                    if ($scenario !== null) {
                        $scenario->setLog('Log : ' . $options['message']);
                    }
                    return;
                } elseif ($this->getExpression() == 'event') {
                    $cmd = cmd::byId(trim(str_replace('#', '', $options['cmd'])));
                    if (!is_object($cmd)) {
                        throw new Exception(__('Commande introuvable : ', __FILE__) . $options['cmd']);
                    }
                    $cmd->event(nextdom::evaluateExpression($options['value']));
                    return;
                } elseif ($this->getExpression() == 'message') {
                    message::add('scenario', $options['message']);
                    $this->setLog($scenario, __('Ajout du message suivant dans le centre de message : ', __FILE__) . $options['message']);
                    return;
                } elseif ($this->getExpression() == 'alert') {
                    event::add('nextdom::alert', $options);
                    $this->setLog($scenario, __('Ajout de l\'alerte : ', __FILE__) . $options['message']);
                    return;
                } elseif ($this->getExpression() == 'popup') {
                    event::add('nextdom::alertPopup', $options['message']);
                    $this->setLog($scenario, __('Affichage du popup : ', __FILE__) . $options['message']);
                    return;
                } elseif ($this->getExpression() == 'equipment' || $this->getExpression() == 'equipement') {
                    $eqLogic = eqLogic::byId(str_replace(array('#eqLogic', '#'), '', $this->getOptions('eqLogic')));
                    if (!is_object($eqLogic)) {
                        throw new Exception(__('Action sur l\'équipement impossible. Equipement introuvable - Vérifiez l\'id : ', __FILE__) . $this->getOptions('eqLogic'));
                    }
                    switch ($this->getOptions('action')) {
                        case 'show':
                            $this->setLog($scenario, __('Equipement visible : ', __FILE__) . $eqLogic->getHumanName());
                            $eqLogic->setIsVisible(1);
                            $eqLogic->save();
                            break;
                        case 'hide':
                            $this->setLog($scenario, __('Equipement masqué : ', __FILE__) . $eqLogic->getHumanName());
                            $eqLogic->setIsVisible(0);
                            $eqLogic->save();
                            break;
                        case 'deactivate':
                            $this->setLog($scenario, __('Equipement désactivé : ', __FILE__) . $eqLogic->getHumanName());
                            $eqLogic->setIsEnable(0);
                            $eqLogic->save();
                            break;
                        case 'activate':
                            $this->setLog($scenario, __('Equipement activé : ', __FILE__) . $eqLogic->getHumanName());
                            $eqLogic->setIsEnable(1);
                            $eqLogic->save();
                            break;
                    }
                    return;
                } elseif ($this->getExpression() == 'gotodesign') {
                    $this->setLog($scenario, __('Changement design : ', __FILE__) . $options['plan_id']);
                    event::add('nextdom::gotoplan', $options['plan_id']);
                    return;
                } elseif ($this->getExpression() == 'scenario') {
                    if ($scenario !== null && $this->getOptions('scenario_id') == $scenario->getId()) {
                        $actionScenario = &$scenario;
                    } else {
                        $actionScenario = scenario::byId($this->getOptions('scenario_id'));
                    }
                    if (!is_object($actionScenario)) {
                        throw new Exception(__('Action sur scénario impossible. Scénario introuvable - Vérifiez l\'id : ', __FILE__) . $this->getOptions('scenario_id'));
                    }
                    switch ($this->getOptions('action')) {
                        case 'start':
                            if ($this->getOptions('tags') != '' && !is_array($this->getOptions('tags'))) {
                                $tags = array();
                                $args = arg2array($this->getOptions('tags'));
                                foreach ($args as $key => $value) {
                                    $tags['#' . trim(trim($key), '#') . '#'] = self::setTags(trim($value), $scenario);
                                }
                                $actionScenario->setTags($tags);
                            }
                            if (is_array($this->getOptions('tags'))) {
                                $actionScenario->setTags($this->getOptions('tags'));
                            }
                            $this->setLog($scenario, __('Lancement du scénario : ', __FILE__) . $actionScenario->getName() . __(' options : ', __FILE__) . json_encode($actionScenario->getTags()));
                            if ($scenario !== null) {
                                return $actionScenario->launch('scenario', __('Lancement provoqué par le scénario  : ', __FILE__) . $scenario->getHumanName());
                            } else {
                                return $actionScenario->launch('other', __('Lancement provoqué', __FILE__));
                            }
                            break;
                        case 'startsync':
                            if ($this->getOptions('tags') != '' && !is_array($this->getOptions('tags'))) {
                                $tags = array();
                                $args = arg2array($this->getOptions('tags'));
                                foreach ($args as $key => $value) {
                                    $tags['#' . trim(trim($key), '#') . '#'] = self::setTags(trim($value), $scenario);
                                }
                                $actionScenario->setTags($tags);
                            }
                            if (is_array($this->getOptions('tags'))) {
                                $actionScenario->setTags($this->getOptions('tags'));
                            }
                            $this->setLog($scenario, __('Lancement du scénario : ', __FILE__) . $actionScenario->getName() . __(' options : ', __FILE__) . json_encode($actionScenario->getTags()));
                            if ($scenario !== null) {
                                return $actionScenario->launch('scenario', __('Lancement provoqué par le scénario  : ', __FILE__) . $scenario->getHumanName(), true);
                            } else {
                                return $actionScenario->launch('other', __('Lancement provoqué', __FILE__), true);
                            }
                            break;
                        case 'stop':
                            $this->setLog($scenario, __('Arrêt forcé du scénario : ', __FILE__) . $actionScenario->getName());
                            $actionScenario->stop();
                            break;
                        case 'deactivate':
                            $this->setLog($scenario, __('Désactivation du scénario : ', __FILE__) . $actionScenario->getName());
                            $actionScenario->setIsActive(0);
                            $actionScenario->save();
                            break;
                        case 'activate':
                            $this->setLog($scenario, __('Activation du scénario : ', __FILE__) . $actionScenario->getName());
                            $actionScenario->setIsActive(1);
                            $actionScenario->save();
                            break;
                    }
                    return;
                } elseif ($this->getExpression() == 'variable') {
                    $options['value'] = self::setTags($options['value'], $scenario);
                    try {
                        $result = evaluate($options['value']);
                        if (!is_numeric($result)) {
                            $result = $options['value'];
                        }
                    } catch (Exception $ex) {
                        $result = $options['value'];
                    } catch (Error $ex) {
                        $result = $options['value'];
                    }
                    $this->setLog($scenario, __('Affectation de la variable ', __FILE__) . $this->getOptions('name') . __(' => ', __FILE__) . $options['value'] . ' = ' . $result);
                    $dataStore = new dataStore();
                    $dataStore->setKey($this->getOptions('name'));
                    $dataStore->setValue($result);
                    $dataStore->setType('scenario');
                    $dataStore->setLink_id(-1);
                    $dataStore->save();
                    return;
                } elseif ($this->getExpression() == 'ask') {
                    $dataStore = new dataStore();
                    $dataStore->setType('scenario');
                    $dataStore->setKey($this->getOptions('variable'));
                    $dataStore->setValue('');
                    $dataStore->setLink_id(-1);
                    $dataStore->save();
                    $limit = (isset($options['timeout'])) ? $options['timeout'] : 300;
                    $options_cmd = array('title' => $options['question'], 'message' => $options['question'], 'answer' => explode(';', $options['answer']), 'timeout' => $limit, 'variable' => $this->getOptions('variable'));
                    $cmd = cmd::byId(str_replace('#', '', $this->getOptions('cmd')));
                    if (!is_object($cmd)) {
                        throw new Exception(__('Commande introuvable - Vérifiez l\'id : ', __FILE__) . $this->getOptions('cmd'));
                    }
                    $this->setLog($scenario, __('Demande ', __FILE__) . json_encode($options_cmd));
                    $cmd->setCache('ask::variable', $this->getOptions('variable'));
                    $cmd->setCache('ask::endtime', strtotime('now') + $limit);
                    $cmd->execCmd($options_cmd);
                    $occurence = 0;
                    $value = '';
                    while (true) {
                        $dataStore = dataStore::byTypeLinkIdKey('scenario', -1, $this->getOptions('variable'));
                        if (is_object($dataStore)) {
                            $value = $dataStore->getValue();
                        }
                        if ($value != '') {
                            break;
                        }
                        if ($occurence > $limit) {
                            break;
                        }
                        $occurence++;
                        sleep(1);
                    }
                    if ($value == '') {
                        $value = __('Aucune réponse', __FILE__);
                        $cmd->setCache('ask::variable', 'none');
                        $dataStore = dataStore::byTypeLinkIdKey('scenario', -1, $this->getOptions('variable'));
                        $dataStore->setValue($value);
                        $dataStore->save();
                    }
                    $this->setLog($scenario, __('Réponse ', __FILE__) . $value);
                    return;
                } elseif ($this->getExpression() == 'nextdom_poweroff') {
                    $this->setLog($scenario, __('Lancement de l\'arret de nextdom', __FILE__));
                    $scenario->persistLog();
                    nextdom::haltSystem();
                    return;
                } elseif ($this->getExpression() == 'scenario_return') {
                    $this->setLog($scenario, __('Demande de retour d\'information : ', __FILE__) . $options['message']);
                    if ($scenario->getReturn() === true) {
                        $scenario->setReturn($options['message']);
                    } else {
                        $scenario->setReturn($scenario->getReturn() . ' ' . $options['message']);
                    }
                    return;
                } elseif ($this->getExpression() == 'remove_inat') {
                    if ($scenario === null) {
                        return;
                    }
                    $this->setLog($scenario, __('Suppression des blocs DANS et A programmés du scénario ', __FILE__));
                    $crons = cron::searchClassAndFunction('scenario', 'doIn', '"scenario_id":' . $scenario->getId() . ',');
                    if (is_array($crons)) {
                        foreach ($crons as $cron) {
                            if ($cron->getState() != 'run') {
                                $cron->remove();
                            }
                        }
                    }
                    return;
                } elseif ($this->getExpression() == 'report') {
                    $cmd_parameters = array('files' => null);
                    $this->setLog($scenario, __('Génération d\'un rapport de type ', __FILE__) . $options['type']);
                    switch ($options['type']) {
                        case 'view':
                            $view = view::byId($options['view_id']);
                            if (!is_object($view)) {
                                throw new Exception(__('Vue introuvable - Vérifiez l\'id : ', __FILE__) . $options['view_id']);
                            }
                            $this->setLog($scenario, __('Génération du rapport ', __FILE__) . $view->getName());
                            $cmd_parameters['files'] = array($view->report($options['export_type'], $options));
                            $cmd_parameters['title'] = __('[' . config::byKey('name') . '] Rapport ', __FILE__) . $view->getName() . __(' du ', __FILE__) . date('Y-m-d H:i:s');
                            $cmd_parameters['message'] = __('Veuillez trouver ci-joint le rapport ', __FILE__) . $view->getName() . __(' généré le ', __FILE__) . date('Y-m-d H:i:s');
                            break;
                        case 'plan':
                            $plan = planHeader::byId($options['plan_id']);
                            if (!is_object($plan)) {
                                throw new Exception(__('Design introuvable - Vérifiez l\'id : ', __FILE__) . $options['plan_id']);
                            }
                            $this->setLog($scenario, __('Génération du rapport ', __FILE__) . $plan->getName());
                            $cmd_parameters['files'] = array($plan->report($options['export_type'], $options));
                            $cmd_parameters['title'] = __('[' . config::byKey('name') . '] Rapport ', __FILE__) . $plan->getName() . __(' du ', __FILE__) . date('Y-m-d H:i:s');
                            $cmd_parameters['message'] = __('Veuillez trouver ci-joint le rapport ', __FILE__) . $plan->getName() . __(' généré le ', __FILE__) . date('Y-m-d H:i:s');
                            break;
                        case 'plugin':
                            $plugin = plugin::byId($options['plugin_id']);
                            if (!is_object($plugin)) {
                                throw new Exception(__('Panel introuvable - Vérifiez l\'id : ', __FILE__) . $options['plugin_id']);
                            }
                            $this->setLog($scenario, __('Génération du rapport ', __FILE__) . $plugin->getName());
                            $cmd_parameters['files'] = array($plugin->report($options['export_type'], $options));
                            $cmd_parameters['title'] = __('[' . config::byKey('name') . '] Rapport ', __FILE__) . $plugin->getName() . __(' du ', __FILE__) . date('Y-m-d H:i:s');
                            $cmd_parameters['message'] = __('Veuillez trouver ci-joint le rapport ', __FILE__) . $plugin->getName() . __(' généré le ', __FILE__) . date('Y-m-d H:i:s');
                            break;
                    }
                    if ($cmd_parameters['files'] === null) {
                        throw new Exception(__('Erreur : Aucun rapport généré', __FILE__));
                    }
                    if ($this->getOptions('cmd') != '') {
                        $cmd = cmd::byId(str_replace('#', '', $this->getOptions('cmd')));
                        if (!is_object($cmd)) {
                            throw new Exception(__('Commande introuvable veuillez vérifiez l\'id : ', __FILE__) . $this->getOptions('cmd'));
                        }
                        $this->setLog($scenario, __('Envoi du rapport généré sur ', __FILE__) . $cmd->getHumanName());
                        $cmd->execCmd($cmd_parameters);
                    }
                } else {
                    $cmd = cmd::byId(str_replace('#', '', $this->getExpression()));
                    if (is_object($cmd)) {
                        if ($cmd->getSubtype() == 'slider' && isset($options['slider'])) {
                            $options['slider'] = evaluate($options['slider']);
                        }
                        if (is_array($options) && (count($options) > 1 || (isset($options['background']) && $options['background'] == 1))) {
                            $this->setLog($scenario, __('Exécution de la commande ', __FILE__) . $cmd->getHumanName() . __(" avec comme option(s) : ", __FILE__) . json_encode($options));
                        } else {
                            $this->setLog($scenario, __('Exécution de la commande ', __FILE__) . $cmd->getHumanName());
                        }
                        return $cmd->execCmd($options);
                    }
                    $this->setLog($scenario, __('[Erreur] Aucune commande trouvée pour ', __FILE__) . $this->getExpression());
                    return;
                }
            } elseif ($this->getType() == 'condition') {
                $expression = self::setTags($this->getExpression(), $scenario, true);
                $message = __('Evaluation de la condition : [', __FILE__) . $expression . '] = ';
                $result = evaluate($expression);
                if (is_bool($result)) {
                    if ($result) {
                        $message .= __('Vrai', __FILE__);
                    } else {
                        $message .= __('Faux', __FILE__);
                    }
                } else {
                    $message .= $result;
                }
                $this->setLog($scenario, $message);
                return $result;
            } elseif ($this->getType() == 'code') {
                $this->setLog($scenario, __('Exécution d\'un bloc code', __FILE__));
                return eval($this->getExpression());
            }
        } catch (Exception $e) {
            $this->setLog($scenario, $message . $e->getMessage());
        } catch (Error $e) {
            $this->setLog($scenario, $message . $e->getMessage());
        }
    }

    public function save() {
        $this->checkBackground();
        DB::save($this);
    }

    public function remove() {
        DB::remove($this);
    }

    public function getAllId() {
        $return = array(
            'element' => array(),
            'subelement' => array(),
            'expression' => array($this->getId()),
        );
        $result = array(
            'element' => array(),
            'subelement' => array(),
            'expression' => array(),
        );
        if ($this->getType() == 'element') {
            $element = ScenarioElementManager::byId($this->getExpression());
            if (is_object($element)) {
                $result = $element->getAllId();
            }
        }
        $return['element'] = array_merge($return['element'], $result['element']);
        $return['subelement'] = array_merge($return['subelement'], $result['subelement']);
        $return['expression'] = array_merge($return['expression'], $result['expression']);
        return $return;
    }

    public function copy($_scenarioSubElement_id) {
        $expressionCopy = clone $this;
        $expressionCopy->setId('');
        $expressionCopy->setScenarioSubElement_id($_scenarioSubElement_id);
        $expressionCopy->save();
        if ($expressionCopy->getType() == 'element') {
            $element = ScenarioElementManager::byId($expressionCopy->getExpression());
            if (is_object($element)) {
                $expressionCopy->setExpression($element->copy());
                $expressionCopy->save();
            }
        }
        return $expressionCopy->getId();
    }

    public function emptyOptions() {
        $this->options = '';
    }

    public function export() {
        $return = '';
        if ($this->getType() == 'element') {
            $element = ScenarioElementManager::byId($this->getExpression());
            if (is_object($element)) {
                $exports = explode("\n", $element->export());
                foreach ($exports as $export) {
                    $return .= "    " . $export . "\n";
                }
            }
            return rtrim($return);
        }
        $options = $this->getOptions();
        if ($this->getType() == 'action') {
            if ($this->getExpression() == 'icon') {
                return '';
            } elseif ($this->getExpression() == 'sleep') {
                return '(sleep) Pause de  : ' . $options['duration'];
            } elseif ($this->getExpression() == 'stop') {
                return '(stop) Arret du scenario';
            } elseif ($this->getExpression() == 'scenario') {
                $actionScenario = scenario::byId($this->getOptions('scenario_id'));
                if (is_object($actionScenario)) {
                    return '(scenario) ' . $this->getOptions('action') . ' de ' . $actionScenario->getHumanName();
                }
            } elseif ($this->getExpression() == 'variable') {
                return '(variable) Affectation de la variable : ' . $this->getOptions('name') . ' à ' . $this->getOptions('value');
            } else {
                $return = nextdom::toHumanReadable($this->getExpression());
                if (is_array($options) && count($options) != 0) {
                    $return .= ' - Options : ' . json_encode(nextdom::toHumanReadable($options));
                }
                return $return;
            }
        } elseif ($this->getType() == 'condition') {
            return nextdom::toHumanReadable($this->getExpression());
        }
        if ($this->getType() == 'code') {

        }
    }

/*     * **********************Getteur Setteur*************************** */

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function getScenarioSubElement_id() {
        return $this->scenarioSubElement_id;
    }

    public function getSubElement() {
        return ScenarioSubElementManager::byId($this->getScenarioSubElement_id());
    }

    public function setScenarioSubElement_id($scenarioSubElement_id) {
        $this->scenarioSubElement_id = $scenarioSubElement_id;
        return $this;
    }

    public function getSubtype() {
        return $this->subtype;
    }

    public function setSubtype($subtype) {
        $this->subtype = $subtype;
        return $this;
    }

    public function getExpression() {
        return $this->expression;
    }

    public function setExpression($expression) {
        $this->expression = nextdom::fromHumanReadable($expression);
        return $this;
    }

    public function getOptions($_key = '', $_default = '') {
        return utils::getJsonAttr($this->options, $_key, $_default);
    }

    public function setOptions($_key, $_value) {
        $this->options = utils::setJsonAttr($this->options, $_key, nextdom::fromHumanReadable($_value));
        return $this;
    }

    public function getOrder() {
        return $this->order;
    }

    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }

    public function setLog(&$_scenario, $log) {
        if ($_scenario !== null && is_object($_scenario)) {
            $_scenario->setLog($log);
        }
    }

}
