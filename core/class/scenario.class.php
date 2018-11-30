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

/*
Chemin d'un scénario
scenario->launch(trigger, message, forceSyncMode);
 - Test si le scénario est activé
 - Si mode syncmode
   - scenario->execute(trigger, message)
   Sinon
     - Fait un truc avec les tags
     - lancement en mode asynchrone avec jeeScenario en ligne de commande scenarioId, trigger, message

scenario->execute(trigger, message)
 - Fait un truc avec les tags
 - Test si le scenario est actif
 - Vérifie la date !!!! Peut amener un délai de 3s
 - Récupère la commande du trigger
 - Fait des trucs et des bidules avec une histoire de timeline
 - Fait des trucs encore plus bizarres
 - Boucle sur les éléments
   - Appel récursif à cette commande !!!! et recheck tout le merdier
   - Break si $this->getDo() sur l'élément
 - Fait encore un truc bizarre avec le PID
 */


use NextDom\Managers\ScenarioManager;
use NextDom\Managers\ScenarioElementManager;
use NextDom\Managers\EqLogicManager;

/* * ***************************Includes********************************* */
require_once NEXTDOM_ROOT . '/core/php/core.inc.php';

class scenario
{
    /*     * *************************Attributs****************************** */

    private $id;
    private $name;
    private $isActive = 1;
    private $group = '';
    private $mode;
    private $schedule;
    private $scenarioElement;
    private $trigger;
    private $_log;
    private $timeout = 0;
    private $object_id = null;
    private $isVisible = 1;
    private $display;
    private $description;
    private $configuration;
    private $type = 'expert';
    private static $_templateArray;
    private $_elements = array();
    private $_changeState = false;
    private $_realTrigger = '';
    private $_return = true;
    private $_tags = array();
    private $_do = true;

    /*     * ***********************Méthodes statiques*************************** */

    /**
     * Renvoie un objet scenario
     * @param int $_id id du scenario voulu
     * @return scenario object scenario
     */
    public static function byId($_id)
    {
        return ScenarioManager::byId($_id);
    }

    public static function byString($_string)
    {
        return ScenarioManager::byString($_string, __('La commande n\'a pas pu être trouvée : ', __FILE__));
    }

    /**
     * Renvoie tous les objets scenario
     * @return [] scenario object scenario
     */
    public static function all($_group = '', $_type = null)
    {
        return ScenarioManager::all($_group, $_type);
    }

    /**
     *
     * @return type
     */
    public static function schedule()
    {
        return ScenarioManager::schedule();
    }

    /**
     *
     * @param type $_group
     * @return type
     */
    public static function listGroup($_group = null)
    {
        return ScenarioManager::listGroup($_group);
    }

    /**
     *
     * @param type $_cmd_id
     * @return type
     */
    public static function byTrigger($_cmd_id, $_onlyEnable = true)
    {
        return ScenarioManager::byTrigger($_cmd_id, $_onlyEnable);
    }

    /**
     *
     * @param type $_element_id
     * @return type
     */
    public static function byElement($_element_id)
    {
        return ScenarioManager::byElement($_element_id);
    }

    /**
     *
     * @param type $_object_id
     * @param type $_onlyEnable
     * @param type $_onlyVisible
     * @return type
     */
    public static function byObjectId($_object_id, $_onlyEnable = true, $_onlyVisible = false)
    {
        return ScenarioManager::byObjectId($_object_id, $_onlyEnable, $_onlyVisible);
    }

    /**
     *
     * @param type $_event
     * @param type $_forceSyncMode
     * @return boolean
     */
    public static function check($_event = null, $_forceSyncMode = false)
    {
        return ScenarioManager::check($_event, $_forceSyncMode, __FILE__);
    }

    public static function control()
    {
        ScenarioManager::control(__FILE__);
    }

    /**
     *
     * @param array $_options
     * @return type
     */
    public static function doIn($_options)
    {
        ScenarioManager::doIn($_options, __FILE__);
    }

    /**
     *
     */
    public static function cleanTable()
    {
        ScenarioManager::cleanTable();
    }

    /**
     *
     */
    public static function consystencyCheck($_needsReturn = false)
    {
        ScenarioManager::consystencyCheck($_needsReturn, __FILE__);
    }

    /**
     * @name byObjectNameGroupNameScenarioName()
     * @param object $_object_name
     * @param type $_group_name
     * @param type $_scenario_name
     * @return type
     */
    public static function byObjectNameGroupNameScenarioName($_object_name, $_group_name, $_scenario_name)
    {
        ScenarioManager::byObjectNameGroupNameScenarioName($_object_name, $_group_name, $_scenario_name);
    }

    /**
     * @name toHumanReadable()
     * @param object $_input
     * @return string
     */
    public static function toHumanReadable($_input)
    {
        return ScenarioManager::toHumanReadable($_input);
    }

    /**
     *
     * @param type $_input
     * @return type
     */
    public static function fromHumanReadable($_input)
    {
        return ScenarioManager::fromHumanReadable($_input);
    }

    /**
     *
     * @param type $searchs
     * @return type
     */
    public static function searchByUse($searchs)
    {
        return ScenarioManager::searchByUse($searchs);
    }

    /**
     *
     * @param type $_template
     * @return type
     */
    public static function getTemplate($_template = '')
    {
        return ScenarioManager::getTemplate($_template);
    }

    public static function shareOnMarket(&$market)
    {
        return ScenarioManager::shareOnMarket($market, __FILE);
    }

    /**
     *
     * @param type $market
     * @param type $_path
     * @throws Exception
     */
    public static function getFromMarket(&$market, $_path)
    {
        ScenarioManager::getFromMarket($market, $_path, __FILE__);
    }

    public static function removeFromMarket(&$market)
    {
        trigger_error('This method is deprecated', E_USER_DEPRECATED);
    }

    public static function listMarketObject()
    {
        return ScenarioManager::listMarketObject();
    }

    public static function timelineDisplay($_event)
    {
        return ScenarioManager::timelineDisplay($_event);
    }

    /*     * *********************Méthodes d'instance************************* */

    /**
     *
     * @param type $_event
     * @return boolean
     */
    public function testTrigger($_event)
    {
        foreach ($this->getTrigger() as $trigger) {
            $trigger = str_replace(array('#variable(', ')#'), array('variable(', ')'), $trigger);
            if ($trigger == $_event) {
                return true;
            } elseif (strpos($trigger, $_event) !== false && nextdom::evaluateExpression($trigger)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Lance un scénario
     *
     * @param type $trigger
     * @param type $message
     * @param bool $forceSyncMode Force synchronous mode
     * @return boolean
     */
    public function launch($trigger = '', $message = '', bool $forceSyncMode = false)
    {
        // Test if scenarios are enabled and if this scenario is activated
        if (config::byKey('enableScenario') != 1 || $this->getIsActive() != 1) {
            return false;
        }
        // Test execution mode
        if ($this->getConfiguration('syncmode') == 1 || $forceSyncMode) {
            $this->setLog(__('Lancement du scénario en mode synchrone'));
            return $this->execute($trigger, $message);
        } else {
            if (count($this->getTags()) != '') {
                $this->setCache('tags', $this->getTags());
            }
            $cmd = __DIR__ . '/../../core/php/jeeScenario.php ';
            $cmd .= ' scenario_id=' . $this->getId();
            $cmd .= ' trigger=' . escapeshellarg($trigger);
            $cmd .= ' "message=' . escapeshellarg(sanitizeAccent($message)) . '"';
            $cmd .= ' >> ' . log::getPathToLog('scenario_execution') . ' 2>&1 &';
            system::php($cmd);
        }
        return true;
    }

    /**
     * Execute the scenario
     *
     * @param type $trigger
     * @param type $message
     * @return type
     */
    public function execute($trigger = '', $message = '')
    {
		$tags = $this->getCache('tags');
		if ($tags != '') {
            $this->setTags($tags);
            $this->setCache('tags', '');
        }
        if ($this->getIsActive() != 1) {
            $this->setLog(__('Impossible d\'exécuter le scénario : ', __FILE__) . $this->getHumanName() . __(' sur : ', __FILE__) . $message . __(' car il est désactivé', __FILE__));
            $this->persistLog();
            return;
        }
        if ($this->getConfiguration('timeDependency', 0) == 1 && !nextdom::isDateOk()) {
            $this->setLog(__('Lancement du scénario : ', __FILE__) . $this->getHumanName() . __(' annulé car il utilise une condition de type temporelle et que la date système n\'est pas OK', __FILE__));
            $this->persistLog();
            return;
        }

        $cmd = cmd::byId(str_replace('#', '', $trigger));
        if (is_object($cmd)) {
            log::add('event', 'info', __('Exécution du scénario ', __FILE__) . $this->getHumanName() . __(' déclenché par : ', __FILE__) . $cmd->getHumanName());
            if ($this->getConfiguration('timeline::enable')) {
                nextdom::addTimelineEvent(array('type' => 'scenario', 'id' => $this->getId(), 'name' => $this->getHumanName(true), 'datetime' => date('Y-m-d H:i:s'), 'trigger' => $cmd->getHumanName(true)));
            }
        } else {
            log::add('event', 'info', __('Exécution du scénario ', __FILE__) . $this->getHumanName() . __(' déclenché par : ', __FILE__) . $trigger);
            if ($this->getConfiguration('timeline::enable')) {
                nextdom::addTimelineEvent(array('type' => 'scenario', 'id' => $this->getId(), 'name' => $this->getHumanName(true), 'datetime' => date('Y-m-d H:i:s'), 'trigger' => $trigger == 'schedule' ? 'programmation' : $trigger));
            }
        }
        if (count($this->getTags()) == 0) {
            $this->setLog('Start : ' . trim($message, "'") . '.');
        } else {
            $this->setLog('Start : ' . trim($message, "'") . '. Tags : ' . json_encode($this->getTags()));
        }
        $this->setLastLaunch(date('Y-m-d H:i:s'));
        $this->setState('in progress');
        $this->setPID(getmypid());
        $this->setRealTrigger($trigger);
        foreach ($this->getElement() as $element) {
            if (!$this->getDo()) {
                break;
            }
            $element->execute($this);
        }
        $this->setState('stop');
        $this->setPID();
        $this->setLog(__('Fin correcte du scénario', __FILE__));
        $this->persistLog();
        return $this->getReturn();
    }

    /**
     *
     * @param type $_name
     * @return \scenario
     */
    public function copy($_name)
    {
        $scenarioCopy = clone $this;
        $scenarioCopy->setName($_name);
        $scenarioCopy->setId('');
        $scenario_element_list = array();
        foreach ($this->getElement() as $element) {
            $scenario_element_list[] = $element->copy();
        }
        $scenarioCopy->setScenarioElement($scenario_element_list);
        $scenarioCopy->setLog('');
        $scenarioCopy->save();
        if (file_exists('/var/log/nextdom/scenarioLog/scenario' . $scenarioCopy->getId() . '.log')) {
            unlink('/var/log/nextdom/scenarioLog/scenario' . $scenarioCopy->getId() . '.log');
        }
        return $scenarioCopy;
    }

    /**
     *
     * @param type $_version
     * @return string
     */
    public function toHtml($_version)
    {
        if (!$this->hasRight('r')) {
            return '';
        }
        $mc = cache::byKey('scenarioHtml' . $_version . $this->getId());
        if ($mc->getValue() != '') {
            return $mc->getValue();
        }

        $version = nextdom::versionAlias($_version);
        $replace = array(
            '#id#' => $this->getId(),
            '#state#' => $this->getState(),
            '#isActive#' => $this->getIsActive(),
            '#name#' => ($this->getDisplay('name') != '') ? $this->getDisplay('name') : $this->getHumanName(),
            '#shortname#' => ($this->getDisplay('name') != '') ? $this->getDisplay('name') : $this->getName(),
            '#treename#' => $this->getHumanName(false, false, false, false, true),
            '#icon#' => $this->getIcon(),
            '#lastLaunch#' => $this->getLastLaunch(),
            '#lastLaunch#' => $this->getLastLaunch(),
            '#scenarioLink#' => $this->getLinkToConfiguration(),
            '#version#' => $_version,
        );
        if (!isset(self::$_templateArray)) {
            self::$_templateArray = array();
        }
        if (!isset(self::$_templateArray[$version])) {
            self::$_templateArray[$version] = getTemplate('core', $version, 'scenario');
        }
        $html = template_replace($replace, self::$_templateArray[$version]);
        cache::set('scenarioHtml' . $version . $this->getId(), $html);
        return $html;
    }

    /**
     *
     */
    public function emptyCacheWidget()
    {
        $mc = cache::byKey('scenarioHtmldashboard' . $this->getId());
        $mc->remove();
        $mc = cache::byKey('scenarioHtmlmobile' . $this->getId());
        $mc->remove();
        $mc = cache::byKey('scenarioHtmlmview' . $this->getId());
        $mc->remove();
        $mc = cache::byKey('scenarioHtmldview' . $this->getId());
        $mc->remove();
    }

    /**
     *
     * @param type $_only_class
     * @return string
     */
    public function getIcon($_only_class = false)
    {
        if ($_only_class) {
            if ($this->getIsActive() == 1) {
                switch ($this->getState()) {
                    case 'in progress':
                        return 'fas fa-spinner fa-spin';
                    case 'error':
                        return 'fas fa-exclamation-triangle';
                    default:
                        if (strpos($this->getDisplay('icon'), '<i') === 0) {
                            return str_replace(array('<i', 'class=', '"', '/>'), '', $this->getDisplay('icon'));
                        }
                        return 'fas fa-check';
                }
            } else {
                return 'fas fa-times';
            }
        } else {
            if ($this->getIsActive() == 1) {
                switch ($this->getState()) {
                    case 'in progress':
                        return '<i class="fas fa-spinner fa-spin"></i>';
                    case 'error':
                        return '<i class="fas fa-exclamation-triangle"></i>';
                    default:
                        if (strpos($this->getDisplay('icon'), '<i') === 0) {
                            return $this->getDisplay('icon');
                        }
                        return '<i class="fas fa-check"></i>';
                }
            } else {
                return '<i class="fas fa-times"></i>';
            }
        }
    }

    /**
     *
     * @return type
     */
    public function getLinkToConfiguration()
    {
        return 'index.php?v=d&p=scenario&id=' . $this->getId();
    }

    /**
     *
     * @throws Exception
     */
    public function preSave()
    {
        if ($this->getTimeout() == '' || !is_numeric($this->getTimeout())) {
            $this->setTimeout(0);
        }
        if ($this->getName() == '') {
            throw new Exception('Le nom du scénario ne peut pas être vide.');
        }
        if (($this->getMode() == 'schedule' || $this->getMode() == 'all') && $this->getSchedule() == '') {
            throw new Exception(__('Le scénario est de type programmé mais la programmation est vide', __FILE__));
        }
        if ($this->getConfiguration('has_return', 0) == 1) {
            $this->setConfiguration('syncmode', 1);
        }
        if ($this->getConfiguration('logmode') == '') {
            $this->setConfiguration('logmode', 'default');
        }
    }

    /**
     *
     */
    public function postInsert()
    {
        $this->setState('stop');
        $this->setPID();
    }

    /**
     *
     */
    public function save()
    {
        if ($this->getLastLaunch() == '' && ($this->getMode() == 'schedule' || $this->getMode() == 'all')) {
            $calculateScheduleDate = $this->calculateScheduleDate();
            $this->setLastLaunch($calculateScheduleDate['prevDate']);
        }
        DB::save($this);
        $this->emptyCacheWidget();
        if ($this->_changeState) {
            $this->_changeState = false;
            event::add('scenario::update', array('scenario_id' => $this->getId(), 'isActive' => $this->getIsActive(), 'state' => $this->getState(), 'lastLaunch' => $this->getLastLaunch()));
        }
    }

    /**
     *
     */
    public function refresh()
    {
        DB::refresh($this);
    }

    /**
     *
     * @return type
     */
    public function remove()
    {
        viewData::removeByTypeLinkId('scenario', $this->getId());
        dataStore::removeByTypeLinkId('scenario', $this->getId());
        foreach ($this->getElement() as $element) {
            $element->remove();
        }
        $this->emptyCacheWidget();
        if (file_exists('/var/log/nextdom/scenarioLog/scenario' . $this->getId() . '.log')) {
            unlink('/var/log/nextdom/scenarioLog/scenario' . $this->getId() . '.log');
        }
        cache::delete('scenarioCacheAttr' . $this->getId());
        return DB::remove($this);
    }

    /**
     *
     * @param type $_key
     * @param type $_private
     * @return boolean
     */
    public function removeData($_key, $_private = false)
    {
        if ($_private) {
            $dataStore = dataStore::byTypeLinkIdKey('scenario', $this->getId(), $_key);
        } else {
            $dataStore = dataStore::byTypeLinkIdKey('scenario', -1, $_key);
        }
        if (is_object($dataStore)) {
            return $dataStore->remove();
        }
        return true;
    }

    /**
     *
     * @param type $_key
     * @param type $_value
     * @param bool $_private
     * @return boolean
     */
    public function setData($_key, $_value, $_private = false)
    {
        $dataStore = new dataStore();
        $dataStore->setType('scenario');
        $dataStore->setKey($_key);
        $dataStore->setValue($_value);
        if ($_private) {
            $dataStore->setLink_id($this->getId());
        } else {
            $dataStore->setLink_id(-1);
        }
        $dataStore->save();
        return true;
    }

    public function getData($_key, $_private = false, $_default = '')
    {
        if ($_private) {
            $dataStore = dataStore::byTypeLinkIdKey('scenario', $this->getId(), $_key);
        } else {
            $dataStore = dataStore::byTypeLinkIdKey('scenario', -1, $_key);
        }
        if (is_object($dataStore)) {
            return $dataStore->getValue($_default);
        }
        return $_default;
    }

    /**
     *
     * @return type
     */
    public function calculateScheduleDate()
    {
        $calculatedDate = array('prevDate' => '', 'nextDate' => '');
        if (is_array($this->getSchedule())) {
            $calculatedDate_tmp = array('prevDate' => '', 'nextDate' => '');
            foreach ($this->getSchedule() as $schedule) {
                try {
                    $c = new Cron\CronExpression($schedule, new Cron\FieldFactory);
                    $calculatedDate_tmp['prevDate'] = $c->getPreviousRunDate()->format('Y-m-d H:i:s');
                    $calculatedDate_tmp['nextDate'] = $c->getNextRunDate()->format('Y-m-d H:i:s');
                } catch (Exception $exc) {

                } catch (Error $exc) {

                }
                if ($calculatedDate['prevDate'] == '' || strtotime($calculatedDate['prevDate']) < strtotime($calculatedDate_tmp['prevDate'])) {
                    $calculatedDate['prevDate'] = $calculatedDate_tmp['prevDate'];
                }
                if ($calculatedDate['nextDate'] == '' || strtotime($calculatedDate['nextDate']) > strtotime($calculatedDate_tmp['nextDate'])) {
                    $calculatedDate['nextDate'] = $calculatedDate_tmp['nextDate'];
                }
            }
        } else {
            try {
                $c = new Cron\CronExpression($this->getSchedule(), new Cron\FieldFactory);
                $calculatedDate['prevDate'] = $c->getPreviousRunDate()->format('Y-m-d H:i:s');
                $calculatedDate['nextDate'] = $c->getNextRunDate()->format('Y-m-d H:i:s');
            } catch (Exception $exc) {

            } catch (Error $exc) {

            }
        }
        return $calculatedDate;
    }

    /**
     *
     * @return boolean
     */
    public function isDue()
    {
        $last = strtotime($this->getLastLaunch());
        $now = time();
        $now = ($now - $now % 60);
        $last = ($last - $last % 60);
        if ($now == $last) {
            return false;
        }
        if (is_array($this->getSchedule())) {
            foreach ($this->getSchedule() as $schedule) {
                try {
                    $c = new Cron\CronExpression($schedule, new Cron\FieldFactory);
                    try {
                        if ($c->isDue()) {
                            return true;
                        }
                    } catch (Exception $e) {

                    } catch (Error $e) {

                    }
                    try {
                        $prev = $c->getPreviousRunDate()->getTimestamp();
                    } catch (Exception $e) {
                        continue;
                    } catch (Error $e) {
                        continue;
                    }
                    $lastCheck = strtotime($this->getLastLaunch());
                    $diff = abs((strtotime('now') - $prev) / 60);
                    if ($lastCheck <= $prev && $diff <= config::byKey('maxCatchAllow') || config::byKey('maxCatchAllow') == -1) {
                        return true;
                    }
                } catch (Exception $e) {

                } catch (Error $e) {

                }
            }
        } else {
            try {
                $c = new Cron\CronExpression($this->getSchedule(), new Cron\FieldFactory);
                try {
                    if ($c->isDue()) {
                        return true;
                    }
                } catch (Exception $e) {

                } catch (Error $e) {

                }
                try {
                    $prev = $c->getPreviousRunDate()->getTimestamp();
                } catch (Exception $e) {
                    return false;
                } catch (Error $e) {
                    return false;
                }
                $lastCheck = strtotime($this->getLastLaunch());
                $diff = abs((strtotime('now') - $prev) / 60);
                if ($lastCheck <= $prev && $diff <= config::byKey('maxCatchAllow') || config::byKey('maxCatchAllow') == -1) {
                    return true;
                }
            } catch (Exception $exc) {

            } catch (Error $exc) {

            }
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function running()
    {
        if (intval($this->getPID()) > 0 && posix_getsid(intval($this->getPID())) && (!file_exists('/proc/' . $this->getPID() . '/cmdline') || strpos(file_get_contents('/proc/' . $this->getPID() . '/cmdline'), 'scenario_id=' . $this->getId()) !== false)) {
            return true;
        }
        if (count(system::ps('scenario_id=' . $this->getId() . ' ', array(getmypid()))) > 0) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return boolean
     * @throws Exception
     */
    public function stop()
    {
        $crons = cron::searchClassAndFunction('scenario', 'doIn', '"scenario_id":' . $this->getId());
        if (is_array($crons)) {
            foreach ($crons as $cron) {
                if ($cron->getState() == 'run') {
                    try {
                        $cron->halt();
                        $cron->remove();
                    } catch (Exception $e) {
                        log::add('scenario', 'info', __('Can not stop subtask : ') . print_r($cron->getOption(), true));
                    }
                }
            }
        }
        if ($this->running()) {
            if ($this->getPID() > 0) {
                system::kill($this->getPID());
                $retry = 0;
                while ($this->running() && $retry < 10) {
                    sleep(1);
                    system::kill($this->getPID());
                    $retry++;
                }
            }

            if ($this->running()) {
                system::kill("scenario_id=" . $this->getId() . ' ');
                sleep(1);
                if ($this->running()) {
                    system::kill("scenario_id=" . $this->getId() . ' ');
                    sleep(1);
                }
            }
            if ($this->running()) {
                throw new Exception(__('Impossible d\'arrêter le scénario : ', __FILE__) . $this->getHumanName() . __('. PID : ', __FILE__) . $this->getPID());
            }
        }
        $this->setState('stop');
        return true;
    }

    /**
     *
     * @return mixed
     */
    public function getElement()
    {
        if (count($this->_elements) > 0) {
            return $this->_elements;
        }
        $result = array();
        $elements = $this->getScenarioElement();
        $elementId = -1;
        if (is_array($elements)) {
            foreach ($this->getScenarioElement() as $elementId) {
                $element = ScenarioElementManager::byId($elementId);
                if (is_object($element)) {
                    $result[] = $element;
                }
            }
            $this->_elements = $result;
            return $result;
        }
        if ($elements != '') {
            $element = ScenarioElementManager::byId($elementId);
            if (is_object($element)) {
                $result[] = $element;
                $this->_elements = $result;
                return $result;
            }
        }
        return array();
    }

    /**
     *
     * @param type $_mode
     * @return type
     */
    public function export($_mode = 'text')
    {
        if ($_mode == 'text') {
            $return = '';
            $return .= '- Nom du scénario : ' . $this->getName() . "\n";
            if (is_numeric($this->getObject_id())) {
                $return .= '- Objet parent : ' . $this->getObject()->getName() . "\n";
            }
            $return .= '- Mode du scénario : ' . $this->getMode() . "\n";
            $schedules = $this->getSchedule();
            if ($this->getMode() == 'schedule' || $this->getMode() == 'all') {
                if (is_array($schedules)) {
                    foreach ($schedules as $schedule) {
                        $return .= '    - Programmation : ' . $schedule . "\n";
                    }
                } else {
                    if ($schedules != '') {
                        $return .= '    - Programmation : ' . $schedules . "\n";
                    }
                }
            }
            if ($this->getMode() == 'provoke' || $this->getMode() == 'all') {
                foreach ($this->getTrigger() as $trigger) {
                    $return .= '    - Evènement : ' . nextdom::toHumanReadable($trigger) . "\n";
                }
            }
            $return .= "\n";
            $return .= $this->getDescription();
            $return .= "\n\n";
            foreach ($this->getElement() as $element) {
                $exports = explode("\n", $element->export());
                foreach ($exports as $export) {
                    $return .= "    " . $export . "\n";
                }
            }
        }
        if ($_mode == 'array') {
            $return = utils::o2a($this);
            $return['trigger'] = nextdom::toHumanReadable($return['trigger']);
            $return['elements'] = array();
            foreach ($this->getElement() as $element) {
                $return['elements'][] = $element->getAjaxElement('array');
            }
            if (isset($return['id'])) {
                unset($return['id']);
            }
            if (isset($return['lastLaunch'])) {
                unset($return['lastLaunch']);
            }
            if (isset($return['log'])) {
                unset($return['log']);
            }
            if (isset($return['hlogs'])) {
                unset($return['hlogs']);
            }
            if (isset($return['object_id'])) {
                unset($return['object_id']);
            }
            if (isset($return['pid'])) {
                unset($return['pid']);
            }
            if (isset($return['scenarioElement'])) {
                unset($return['scenarioElement']);
            }
            if (isset($return['_templateArray'])) {
                unset($return['_templateArray']);
            }
            if (isset($return['_templateArray'])) {
                unset($return['_templateArray']);
            }
            if (isset($return['_changeState'])) {
                unset($return['_changeState']);
            }
            if (isset($return['_realTrigger'])) {
                unset($return['_realTrigger']);
            }
            if (isset($return['_templateArray'])) {
                unset($return['_templateArray']);
            }
            if (isset($return['_elements'])) {
                unset($return['_elements']);
            }
        }
        return $return;
    }

    /**
     *
     * @return object
     */
    public function getObject()
    {
        return object::byId($this->object_id);
    }

    /**
     *
     * @param type $_complete
     * @param type $_noGroup
     * @param type $_tag
     * @param type $_prettify
     * @param type $_withoutScenarioName
     * @return string
     */
    public function getHumanName($_complete = false, $_noGroup = false, $_tag = false, $_prettify = false, $_withoutScenarioName = false)
    {
        $name = '';
        if (is_numeric($this->getObject_id()) && is_object($this->getObject())) {
            $object = $this->getObject();
            if ($_tag) {
                if ($object->getDisplay('tagColor') != '') {
                    $name .= '<span class="label" style="text-shadow : none;background-color:' . $object->getDisplay('tagColor') . ' !important;color:' . $object->getDisplay('tagTextColor', 'white') . ' !important">' . $object->getName() . '</span>';
                } else {
                    $name .= '<span class="label label-primary" style="text-shadow : none;">' . $object->getName() . '</span>';
                }
            } else {
                $name .= '[' . $object->getName() . ']';
            }
        } else {
            if ($_complete) {
                if ($_tag) {
                    $name .= '<span class="label label-default" style="text-shadow : none;">' . __('Aucun', __FILE__) . '</span>';
                } else {
                    $name .= '[' . __('Aucun', __FILE__) . ']';
                }
            }
        }
        if (!$_noGroup) {
            if ($this->getGroup() != '') {
                $name .= '[' . $this->getGroup() . ']';
            } else {
                if ($_complete) {
                    $name .= '[' . __('Aucun', __FILE__) . ']';
                }
            }
        }
        if ($_prettify) {
            $name .= '<br/><strong>';
        }
        if (!$_withoutScenarioName) {
            if ($_tag) {
                $name .= ' ' . $this->getName();
            } else {
                $name .= '[' . $this->getName() . ']';
            }
        }
        if ($_prettify) {
            $name .= '</strong>';
        }
        return $name;
    }

    /**
     *
     * @param type $_right
     * @return boolean
     */
    public function hasRight($_right, $_user = null)
    {
        if ($_user !== null) {
            if ($_user->getProfils() == 'admin' || $_user->getProfils() == 'user') {
                return true;
            }
            if (strpos($_user->getRights('scenario' . $this->getId()), $_right) !== false) {
                return true;
            }
            return false;
        }
        if (!isConnect()) {
            return false;
        }
        if (isConnect('admin') || isConnect('user')) {
            return true;
        }
        if (strpos($_SESSION['user']->getRights('scenario' . $this->getId()), $_right) !== false) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param type $_partial
     * @return type
     */
    public function persistLog($_partial = false)
    {
        if ($this->getConfiguration('logmode', 'default') == 'none') {
            return;
        }
        $path = '/var/log/nextdom/scenarioLog';
        if (!file_exists($path)) {
            mkdir($path);
        }
        $path .= '/scenario' . $this->getId() . '.log';
        if ($_partial) {
            file_put_contents($path, $this->getLog(), FILE_APPEND);
        } else {
            file_put_contents($path, "------------------------------------\n" . $this->getLog(), FILE_APPEND);
        }
    }

    /**
     *
     * @return type
     */
    public function toArray()
    {
        $return = utils::o2a($this, true);
		$cache = $this->getCache(array('state', 'lastLaunch'));
		$return['state'] = $cache['state'];
        $return['lastLaunch'] = $cache['lastLaunch'];
        return $return;
    }

    /**
     *
     * @param type $_data
     * @param type $_level
     * @param type $_drill
     * @return string
     */
    public function getLinkData(&$_data = array('node' => array(), 'link' => array()), $_level = 0, $_drill = null)
    {
        if ($_drill === null) {
            $_drill = config::byKey('graphlink::scenario::drill');
        }
        if (isset($_data['node']['scenario' . $this->getId()])) {
            return;
        }
        if ($this->getIsActive() == 0 && $_level > 0) {
            return $_data;
        }
        $_level++;
        if ($_level > $_drill) {
            return $_data;
        }

        $_data['node']['scenario' . $this->getId()] = array(
            'id' => 'scenario' . $this->getId(),
            'name' => $this->getName(),
            'fontweight' => ($_level == 1) ? 'bold' : 'normal',
            'shape' => 'rect',
            'width' => 40,
            'height' => 40,
            'color' => 'green',
            'image' => '/public/img/NextDom_Scenario.png',
            'title' => $this->getHumanName(),
            'url' => 'index.php?v=d&p=scenario&id=' . $this->getId(),
        );
        $use = $this->getUse();
        $usedBy = $this->getUsedBy();
        addGraphLink($this, 'scenario', $this->getObject(), 'object', $_data, $_level + 1, $_drill, array('dashvalue' => '1,0', 'lengthfactor' => 0.6));
        addGraphLink($this, 'scenario', $use['cmd'], 'cmd', $_data, $_level, $_drill);
        addGraphLink($this, 'scenario', $use['scenario'], 'scenario', $_data, $_level, $_drill);
        addGraphLink($this, 'scenario', $use['eqLogic'], 'eqLogic', $_data, $_level, $_drill);
        addGraphLink($this, 'scenario', $use['dataStore'], 'dataStore', $_data, $_level, $_drill);
        addGraphLink($this, 'scenario', $use['view'], 'view', $_data, $_level, $_drill);
        addGraphLink($this, 'scenario', $use['plan'], 'plan', $_data, $_level, $_drill);
        addGraphLink($this, 'scenario', $usedBy['cmd'], 'cmd', $_data, $_level, $_drill);
        addGraphLink($this, 'scenario', $usedBy['scenario'], 'scenario', $_data, $_level, $_drill);
        addGraphLink($this, 'scenario', $usedBy['eqLogic'], 'eqLogic', $_data, $_level, $_drill);
        addGraphLink($this, 'scenario', $usedBy['interactDef'], 'interactDef', $_data, $_level, $_drill, array('dashvalue' => '2,6', 'lengthfactor' => 0.6));
        addGraphLink($this, 'scenario', $usedBy['plan'], 'plan', $_data, $_level, $_drill, array('dashvalue' => '2,6', 'lengthfactor' => 0.6));
        addGraphLink($this, 'scenario', $usedBy['view'], 'view', $_data, $_level, $_drill, array('dashvalue' => '2,6', 'lengthfactor' => 0.6));
        return $_data;
    }

    /**
     *
     * @return type
     */
    public function getUse()
    {
        $json = nextdom::fromHumanReadable(json_encode($this->export('array')));
        return nextdom::getTypeUse($json);
    }

    /**
     *
     * @param array $_array
     * @return type
     */
    public function getUsedBy($_array = false)
    {
        $return = array('cmd' => array(), 'eqLogic' => array(), 'scenario' => array(), 'plan' => array(), 'view' => array());
        $return['cmd'] = cmd::searchConfiguration('#scenario' . $this->getId() . '#');
        $return['eqLogic'] = EqLogicManager::searchConfiguration(array('#scenario' . $this->getId() . '#', '"scenario_id":"' . $this->getId()));
        $return['interactDef'] = interactDef::searchByUse(array('#scenario' . $this->getId() . '#', '"scenario_id":"' . $this->getId()));
        $return['scenario'] = scenario::searchByUse(array(
            array('action' => 'scenario', 'option' => $this->getId(), 'and' => true),
            array('action' => '#scenario' . $this->getId() . '#'),
        ));
        $return['view'] = view::searchByUse('scenario', $this->getId());
        $return['plan'] = planHeader::searchByUse('scenario', $this->getId());
        if ($_array) {
            foreach ($return as &$value) {
                $value = utils::o2a($value);
            }
        }
        return $return;
    }

    public function clearLog()
    {
        $this->_log = '';
    }

    public function resetRepeatIfStatus() {
        foreach ($this->getElement() as $element) {
            $element->resetRepeatIfStatus();
        }
    }

    /*     * **********************Getteur Setteur*************************** */
    /**
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return type
     */
    public function getState()
    {
        return $this->getCache('state');
    }

    /**
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     *
     * @return type
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     *
     * @return type
     */
    public function getLastLaunch()
    {
        return $this->getCache('lastLaunch');
    }

    /**
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     *
     * @param type $name
     * @return $this
     */
    public function setName($name)
    {
        if ($name != $this->getName()) {
            $this->_changeState = true;
        }
        $this->name = $name;
        return $this;
    }

    /**
     *
     * @param int $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        if ($isActive != $this->getIsActive()) {
            $this->_changeState = true;
        }
        $this->isActive = $isActive;
        return $this;
    }

    /**
     *
     * @param type $group
     * @return $this
     */
    public function setGroup($group)
    {
        if ($group != $this->getGroup()) {
            $this->_changeState = true;
        }
        $this->group = $group;
        return $this;
    }

    /**
     *
     * @param type $state
     */
    public function setState($state)
    {
        if ($this->getCache('state') != $state) {
            $this->emptyCacheWidget();
            event::add('scenario::update', array('scenario_id' => $this->getId(), 'state' => $state, 'lastLaunch' => $this->getLastLaunch()));
        }
        $this->setCache('state', $state);
    }

    /**
     *
     * @param type $lastLaunch
     */
    public function setLastLaunch($lastLaunch)
    {
        $this->setCache('lastLaunch', $lastLaunch);
    }

    /**
     *
     * @return type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param type $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     *
     * @return type
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     *
     * @param type $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     *
     * @return string/object
     */
    public function getSchedule()
    {
        return is_json($this->schedule, $this->schedule);
    }

    /**
     *
     * @param type $schedule
     * @return $this
     */
    public function setSchedule($schedule)
    {
        if (is_array($schedule)) {
            $schedule = json_encode($schedule, JSON_UNESCAPED_UNICODE);
        }
        $this->schedule = $schedule;
        return $this;
    }

    /**
     *
     * @return type
     */
    public function getPID()
    {
        return $this->getCache('pid');
    }

    /**
     *
     * @param type $pid
     */
    public function setPID($pid = '')
    {
        $this->setCache('pid', $pid);
    }

    /**
     *
     * @return type
     */
    public function getScenarioElement()
    {
        return is_json($this->scenarioElement, $this->scenarioElement);
    }

    /**
     *
     * @param type $scenarioElement
     * @return $this
     */
    public function setScenarioElement($scenarioElement)
    {
        if (is_array($scenarioElement)) {
            $scenarioElement = json_encode($scenarioElement, JSON_UNESCAPED_UNICODE);
        }
        $this->scenarioElement = $scenarioElement;
        return $this;
    }

    /**
     *
     * @return type
     */
    public function getTrigger()
    {
        return is_json($this->trigger, array($this->trigger));
    }

    /**
     *
     * @param type $trigger
     * @return $this
     */
    public function setTrigger($trigger)
    {
        if (is_array($trigger)) {
            $trigger = json_encode($trigger, JSON_UNESCAPED_UNICODE);
        }
        $this->trigger = cmd::humanReadableToCmd($trigger);
        return $this;
    }

    /**
     *
     * @return type
     */
    public function getLog()
    {
        return $this->_log;
    }

    /**
     *
     * @param string $log
     */
    public function setLog($log)
    {
        $this->_log .= '[' . date('Y-m-d H:i:s') . '][SCENARIO] ' . $log . "\n";
        if ($this->getConfiguration('logmode', 'default') == 'realtime') {
            $this->persistLog(true);
            $this->_log = '';
        }
    }

    /**
     *
     * @param type $_default
     * @return type
     */
    public function getTimeout($_default = null)
    {
        if ($this->timeout == '' || !is_numeric($this->timeout)) {
            return $_default;
        }
        return $this->timeout;
    }

    /**
     *
     * @param string $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        if ($timeout == '' || is_nan(intval($timeout)) || $timeout < 1) {
            $timeout = '';
        }
        $this->timeout = $timeout;
        return $this;
    }

    /**
     *
     * @param type $_default
     * @return type
     */
    public function getObject_id($_default = null)
    {
        if ($this->object_id == '' || !is_numeric($this->object_id)) {
            return $_default;
        }
        return $this->object_id;
    }

    /**
     *
     * @param type $_default
     * @return type
     */
    public function getIsVisible($_default = 0)
    {
        if ($this->isVisible == '' || !is_numeric($this->isVisible)) {
            return $_default;
        }
        return $this->isVisible;
    }

    /**
     *
     * @param type $object_id
     * @return $this
     */
    public function setObject_id($object_id = null)
    {
        if ($object_id != $this->getObject_id()) {
            $this->_changeState = true;
        }
        $this->object_id = (!is_numeric($object_id)) ? null : $object_id;
        return $this;
    }

    /**
     *
     * @param type $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;
        return $this;
    }

    /**
     *
     * @param type $_key
     * @param type $_default
     * @return type
     */
    public function getDisplay($_key = '', $_default = '')
    {
        return utils::getJsonAttr($this->display, $_key, $_default);
    }

    /**
     *
     * @param type $_key
     * @param type $_value
     * @return $this
     */
    public function setDisplay($_key, $_value)
    {
        $this->display = utils::setJsonAttr($this->display, $_key, $_value);
        return $this;
    }

    /**
     *
     * @return type
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     *
     * @param type $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     *
     * @param type $_key
     * @param type $_default
     * @return type
     */
    public function getConfiguration($_key = '', $_default = '')
    {
        return utils::getJsonAttr($this->configuration, $_key, $_default);
    }

    /**
     *
     * @param type $_key
     * @param type $_value
     * @return $this
     */
    public function setConfiguration($_key, $_value)
    {
        $this->configuration = utils::setJsonAttr($this->configuration, $_key, $_value);
        return $this;
    }

    /**
     *
     * @return type
     */
    public function getRealTrigger()
    {
        return $this->_realTrigger;
    }

    /**
     *
     * @param type $_realTrigger
     * @return $this
     */
    public function setRealTrigger($_realTrigger)
    {
        $this->_realTrigger = $_realTrigger;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getReturn()
    {
        return $this->_return;
    }

    /**
     *
     * @param type $_return
     * @return $this
     */
    public function setReturn($_return)
    {
        $this->_return = $_return;
        return $this;
    }

    /**
     *
     * @return type
     */
    public function getTags()
    {
        return $this->_tags;
    }

    /**
     *
     * @param array $_tags
     * @return $this
     */
    public function setTags($_tags)
    {
        $this->_tags = $_tags;
        return $this;
    }

    /**
     *
     * @return bool
     */
    public function getDo()
    {
        return $this->_do;
    }

    /**
     *
     * @param bool $_do
     * @return $this
     */
    public function setDo($_do)
    {
        $this->_do = $_do;
        return $this;
    }

    /**
     *
     * @param string $_key
     * @param mixed $_default
     * @return mixed
     */
    public function getCache($_key = '', $_default = '')
    {
        return utils::getJsonAttr(cache::byKey('scenarioCacheAttr' . $this->getId())->getValue(), $_key, $_default);
    }

    /**
     *
     * @param string $_key
     * @param mixed $_value
     */
    public function setCache($_key, $_value = null)
    {
        cache::set('scenarioCacheAttr' . $this->getId(), utils::setJsonAttr(cache::byKey('scenarioCacheAttr' . $this->getId())->getValue(), $_key, $_value));
    }

}
