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

use NextDom\Managers\InteractQueryManager;

require_once __DIR__ . '/../../core/php/core.inc.php';

class interactQuery {
    /*     * *************************Attributs****************************** */

    private $id;
    private $interactDef_id;
    private $query;
    private $actions;
    private $_changed = false;

    /*     * ***********************Méthodes statiques*************************** */

    public static function byId($_id) {
        return InteractqueryManager::byId($_id);
    }

    public static function byQuery($_query, $_interactDef_id = null) {
        return InteractQueryManager::byQuery($_query, $_interactDef_id);
    }

    public static function byInteractDefId($_interactDef_id) {
        return InteractQueryManager::byInteractDefId($_interactDef_id);
    }

    /**
     * @param $_action
     * @return \interactQuery[]
     * @throws Exception
     */
    public static function searchActions($_action) {
        return InteractQueryManager::searchActions($_action);
    }

    public static function all() {
        return InteractQueryManager::all();
    }

    public static function removeByInteractDefId($_interactDef_id) {
        return InteractQueryManager::removeByInteractDefId($_interactDef_id);
    }

    public static function recognize($_query) {
        return InteractQueryManager::recognize($_query);
    }

    public static function getQuerySynonym($_query, $_for) {
        return InteractQueryManager::getQuerySynonym($_query, $_for);
    }

    public static function findInQuery($_type, $_query, $_data = null) {
        return InteractQueryManager::findInQuery($_type, $_query, $_data);
    }

    public static function cmp_objectName($a, $b) {
        return InteractQueryManager::cmp_objectName($a, $b);
    }

    public static function autoInteract($_query, $_parameters = array()) {
        return InteractQueryManager::autoInteract($_query, $_parameters);
    }

    public static function autoInteractWordFind($_string, $_word) {
        return InteractQueryManager::autoInteractWordFind($_string, $_word);
    }

    public static function pluginReply($_query, $_parameters = array()) {
        return InteractQueryManager::pluginReply($_query, $_parameters);
    }

    public static function warnMe($_query, $_parameters = array()) {
        return InteractQueryManager::warnMe($_query, $_parameters);
    }

    public static function warnMeExecute($_options) {
        InteractQueryManager::warnMeExecute($_options);
    }

    public static function tryToReply($_query, $_parameters = array()) {
        return InteractQueryManager::tryToReply($_query, $_parameters);
    }

    public static function addLastInteract($_lastCmd, $_identifier = 'unknown') {
        InteractQueryManager::addLastInteract($_lastCmd, $_identifier);
    }

    public static function contextualReply($_query, $_parameters = array(), $_lastCmd = null) {
        return InteractQueryManager::contextualReply($_query, $_parameters, $_lastCmd);
    }

    public static function brainReply($_query, $_parameters) {
        return InteractQueryManager::brainReply($_query, $_parameters);
    }

    public static function dontUnderstand($_parameters) {
        return InteractQueryManager::dontUnderstand($_parameters);
    }

    public static function replyOk() {
        return InteractQueryManager::replyOk();
    }

    public static function doIn($_params) {
        InteractQueryManager::doIn($_params);
    }

    /*     * *********************Méthodes d'instance************************* */

    public function save() {
        if ($this->getQuery() == '') {
            throw new Exception(__('La commande vocale ne peut pas être vide', __FILE__));
        }
        if ($this->getInteractDef_id() == '') {
            throw new Exception(__('InteractDef_id ne peut pas être vide', __FILE__));
        }
        DB::save($this);
        return $this;
    }

    public function remove() {
        return DB::remove($this);
    }

    public function executeAndReply($_parameters) {
        if (isset($_parameters['reply_cmd'])) {
            unset($_parameters['reply_cmd']);
        }
        $interactDef = interactDef::byId($this->getInteractDef_id());
        if (!is_object($interactDef)) {
            return __('Inconsistance de la base de données', __FILE__);
        }
        if (isset($_parameters['profile']) && trim($interactDef->getPerson()) != '') {
            $person = strtolower($interactDef->getPerson());
            $person = explode('|', $person);
            if (!in_array($_parameters['profile'], $person)) {
                return __('Vous n\'êtes pas autorisé à exécuter cette action', __FILE__);
            }
        }
        $reply = $interactDef->selectReply();
        $replace = array('#query#' => $this->getQuery());
        foreach ($_parameters as $key => $value) {
            $replace['#' . $key . '#'] = $value;
        }
        $tags = null;
        if (isset($_parameters['dictation'])) {
            $tags = interactDef::getTagFromQuery($this->getQuery(), $_parameters['dictation']);
            $replace['#dictation#'] = $_parameters['dictation'];
        }
        if (is_array($tags)) {
            $replace = array_merge($replace, $tags);
        }
        $executeDate = null;

        if (isset($replace['#duration#'])) {
            $dateConvert = array(
                'heure' => 'hour',
                'mois' => 'month',
                'semaine' => 'week',
                'année' => 'year',
            );
            $replace['#duration#'] = str_replace(array_keys($dateConvert), $dateConvert, $replace['#duration#']);
            $executeDate = strtotime('+' . $replace['#duration#']);
        }
        if (isset($replace['#time#'])) {
            $time = str_replace(array('h'), array(':'), $replace['#time#']);
            if (strlen($time) == 2) {
                $time .= ':00';
            } else if (strlen($time) == 3) {
                $time .= '00';
            }
            $executeDate = strtotime($time);
            if ($executeDate < strtotime('now')) {
                $executeDate += 3600;
            }
        }
        if ($executeDate !== null && !isset($_parameters['execNow'])) {
            if (date('Y', $executeDate) < 2000) {
                return __('Erreur : impossible de calculer la date de programmation', __FILE__);
            }
            if ($executeDate < (strtotime('now') + 60)) {
                $executeDate = strtotime('now') + 60;
            }
            $crons = cron::searchClassAndFunction('interactQuery', 'doIn', '"interactQuery_id":' . $this->getId());
            if (is_array($crons)) {
                foreach ($crons as $cron) {
                    if ($cron->getState() != 'run') {
                        $cron->remove();
                    }
                }
            }
            $cron = new cron();
            $cron->setClass('interactQuery');
            $cron->setFunction('doIn');
            $cron->setOption(array_merge(array('interactQuery_id' => intval($this->getId())), $_parameters));
            $cron->setLastRun(date('Y-m-d H:i:s'));
            $cron->setOnce(1);
            $cron->setSchedule(cron::convertDateToCron($executeDate));
            $cron->save();
            $replace['#valeur#'] = date('Y-m-d H:i:s', $executeDate);
            $result = scenarioExpression::setTags(str_replace(array_keys($replace), $replace, $reply));
            return $result;
        }
        $replace['#valeur#'] = '';
        $colors = array_change_key_case(config::byKey('convertColor'));
        if (is_array($this->getActions('cmd'))) {
            foreach ($this->getActions('cmd') as $action) {
                try {
                    $options = array();
                    if (isset($action['options'])) {
                        $options = $action['options'];
                    }
                    if ($tags !== null) {
                        foreach ($options as &$option) {
                            $option = str_replace(array_keys($replace), $replace, $option);
                        }
                        if (isset($options['color']) && isset($colors[strtolower($options['color'])])) {
                            $options['color'] = $colors[strtolower($options['color'])];
                        }
                    }
                    $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
                    if (is_object($cmd)) {
                        $replace['#unite#'] = $cmd->getUnite();
                        $replace['#commande#'] = $cmd->getName();
                        $replace['#objet#'] = '';
                        $replace['#equipement#'] = '';
                        $eqLogic = $cmd->getEqLogicId();
                        if (is_object($eqLogic)) {
                            $replace['#equipement#'] = $eqLogic->getName();
                            $object = $eqLogic->getObject();
                            if (is_object($object)) {
                                $replace['#objet#'] = $object->getName();
                            }
                        }
                    }
                    $tags = array();
                    if (isset($options['tags'])) {
                        $options['tags'] = arg2array($options['tags']);
                        foreach ($options['tags'] as $key => $value) {
                            $tags['#' . trim(trim($key), '#') . '#'] = scenarioExpression::setTags(trim($value));
                        }
                    }
                    $options['tags'] = array_merge($replace, $tags);
                    $return = scenarioExpression::createAndExec('action', $action['cmd'], $options);
                    if (trim($return) !== '' && trim($return) !== null) {
                        $replace['#valeur#'] .= ' ' . $return;
                    }
                } catch (Exception $e) {
                    log::add('interact', 'error', __('Erreur lors de l\'exécution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
                }
            }
        }
        if ($interactDef->getOptions('waitBeforeReply') != '' && $interactDef->getOptions('waitBeforeReply') != 0 && is_numeric($interactDef->getOptions('waitBeforeReply'))) {
            sleep($interactDef->getOptions('waitBeforeReply'));
        }
        $reply = nextdom::evaluateExpression($reply);
        $replace['#valeur#'] = trim($replace['#valeur#']);
        $replace['#profile#'] = isset($_parameters['profile']) ? $_parameters['profile'] : '';
        if ($interactDef->getOptions('convertBinary') != '') {
            $convertBinary = explode('|', $interactDef->getOptions('convertBinary'));
            if (is_array($convertBinary) && count($convertBinary) == 2) {
                $replace['1'] = $convertBinary[1];
                $replace['0'] = $convertBinary[0];
            }
        }
        foreach ($replace as $key => $value) {
            if (is_array($value)) {
                unset($replace[$key]);
            }
        }
        if ($replace['#valeur#'] == '') {
            $replace['#valeur#'] = __('aucune valeur', __FILE__);
        }
        $replace['"'] = '';
        return str_replace(array_keys($replace), $replace, $reply);
    }

    /**
     * @return \interactDef
     */
    public function getInteractDef() {
        return interactDef::byId($this->interactDef_id);
    }

    /*     * **********************Getteur Setteur*************************** */

    public function getInteractDef_id() {
        return $this->interactDef_id;
    }

    public function setInteractDef_id($_interactDef_id) {
        $this->_changed = utils::attrChanged($this->_changed,$this->interactDef_id,$_interactDef_id);
        $this->interactDef_id = $_interactDef_id;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($_id) {
        $this->_changed = utils::attrChanged($this->_changed,$this->id,$_id);
        $this->id = $_id;
        return $this;
    }

    public function getQuery() {
        return $this->query;
    }

    public function setQuery($_query) {
        $this->_changed = utils::attrChanged($this->_changed,$this->query,$_query);
        $this->query = $_query;
        return $this;
    }

    public function getActions($_key = '', $_default = '') {
        return utils::getJsonAttr($this->actions, $_key, $_default);
    }

    public function setActions($_key, $_value) {
        $actions = utils::setJsonAttr($this->actions, $_key, $_value);
        $this->_changed = utils::attrChanged($this->_changed,$this->actions,$actions);
        $this->actions = $actions;
        return $this;
    }

    public function replaceForContextual($_replace, $_by, $_in) {
        Interactquery::replaceForContextual($_replace, $_by, $_in);
    }

    public function getChanged() {
        return $this->_changed;
    }

    public function setChanged($_changed) {
        $this->_changed = $_changed;
        return $this;
    }
}
