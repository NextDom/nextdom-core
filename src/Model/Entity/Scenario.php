<?php
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

namespace NextDom\Model\Entity;

use NextDom\Enums\CacheKey;
use NextDom\Enums\Common;
use NextDom\Enums\DateFormat;
use NextDom\Enums\LogTarget;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\ScenarioCache;
use NextDom\Enums\ScenarioConf;
use NextDom\Enums\ScenarioState;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Helpers\TimeLineHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\CronManager;
use NextDom\Managers\DataStoreManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\EventManager;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\ScenarioElementManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\UserManager;
use NextDom\Managers\ViewDataManager;
use NextDom\Managers\ViewManager;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\ConfigurationEntity;
use NextDom\Model\Entity\Parents\DisplayEntity;
use NextDom\Model\Entity\Parents\IsActiveEntity;
use NextDom\Model\Entity\Parents\IsVisibleEntity;
use NextDom\Model\Entity\Parents\NameEntity;
use NextDom\Model\Entity\Parents\OrderEntity;
use NextDom\Model\Entity\Parents\RefreshEntity;
use NextDom\Model\Entity\Parents\TypeEntity;

/**
 * Scenario
 *
 * ORM\Table(name="scenario", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"group", "object_id", "name"})}, indexes={@ORM\Index(name="group", columns={"group"}), @ORM\Index(name="fk_scenario_object1_idx", columns={"object_id"}), @ORM\Index(name="trigger", columns={"trigger"}), @ORM\Index(name="mode", columns={"mode"}), @ORM\Index(name="modeTriger", columns={"mode", "trigger"})})
 * ORM\Entity
 */
class Scenario extends BaseEntity
{
    const TABLE_NAME = NextDomObj::SCENARIO;

    use ConfigurationEntity, DisplayEntity, IsVisibleEntity, IsActiveEntity, NameEntity, OrderEntity, RefreshEntity, TypeEntity;

    /**
     * @var array
     */
    protected static $_templateArray;
    /**
     * @var string
     *
     * @ORM\Column(name="group", type="string", length=127, nullable=true)
     */
    protected $group = '';
    /**
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length=127, nullable=true)
     */
    protected $mode;
    /**
     * @var string
     *
     * @ORM\Column(name="schedule", type="text", length=65535, nullable=true)
     */
    protected $schedule;
    /**
     * @var string
     *
     * @ORM\Column(name="scenarioElement", type="text", length=65535, nullable=true)
     */
    protected $scenarioElement;
    /**
     * @var mixed
     *
     * @ORM\Column(name="trigger", type="string", length=255, nullable=true)
     */
    protected $trigger;
    /**
     * @var integer
     *
     * @ORM\Column(name="timeout", type="integer", nullable=true)
     */
    protected $timeout = 0;
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    protected $description;

    /**
     * @var int
     *
     * ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Object")
     * ORM\JoinColumns({
     *   ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     */
    protected $object_id;
    protected $_elements = [];
    protected $_changeState = false;
    protected $_realTrigger = '';
    protected $_return = true;
    protected $_tags = [];
    protected $_do = true;
    protected $_log;

    public function __construct()
    {
        if ($this->order === null) {
            $this->order = 9999;
        }
    }

    /**
     *
     * @param mixed $event
     * @return boolean
     */
    public function testTrigger($event): bool
    {
        foreach ($this->getTrigger() as $triggerTotest) {
            $triggerTotest = str_replace(['#variable(', ')#'], ['variable(', ')'], $triggerTotest);
            if ($triggerTotest == $event || (strpos($triggerTotest, $event) !== false && NextDomHelper::evaluateExpression($triggerTotest))) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @return mixed
     */
    public function getTrigger()
    {
        return Utils::isJson($this->trigger, [$this->trigger]);
    }

    /**
     *
     * @param $_trigger
     * @return $this
     * @throws \Exception
     */
    public function setTrigger($_trigger)
    {
        if (is_array($_trigger)) {
            $_trigger = json_encode($_trigger, JSON_UNESCAPED_UNICODE);
        }
        $_trigger = CmdManager::humanReadableToCmd($_trigger);
        $this->updateChangeState($this->trigger, $_trigger);
        $this->trigger = $_trigger;
        return $this;
    }

    /**
     * Lance un scénario
     *
     * @param string $trigger
     * @param string $message
     * @param bool $forceSyncMode Force synchronous mode
     * @return boolean
     * @throws \Exception
     */
    public function launch($trigger = '', $message = '', $forceSyncMode = false)
    {
        // Test if scenarios are enabled and if this scenario is activated
        if (!$this->isActive() || !ScenarioManager::isEnabled()) {
            return false;
        }
        $state = $this->getState();
        $now = strtotime('now');
        if ($state == ScenarioState::STARTING) {
            //Scénario bloqué en starting (Exemple de cause : trop de connexions à MySql, la connexion est refusée, le scénario plante)
            if ($now - $this->getCache(ScenarioCache::STARTING_TIME) > 5) {
                LogHelper::addError(LogTarget::SCENARIO, __('La dernière exécution du scénario ne s\'est pas lancée. Vérifiez le log scenario_execution, ainsi que le log du scénario') . " \"" . $this->getName() . "\".");
                $this->setLog(__('La dernière exécution du scénario ne s\'est pas lancée. Vérifiez le log scenario_execution pour l\'exécution à ') . date(DateFormat::FULL, $this->getCache(ScenarioCache::STARTING_TIME)) . ".");
                $this->persistLog();
            }
            //Retarde le lancement du scénario si une autre instance est déjà en cours de démarrage
            if (($this->getCache(ScenarioCache::STARTING_TIME) + 2) > $now) {
                $i = 0;
                while ($state == ScenarioState::STARTING) {
                    sleep(1);
                    $state = $this->getState();
                    $i++;
                    if ($i > 10) {
                        break;
                    }
                }
                if ($state == ScenarioState::STARTING) {
                    LogHelper::addError(LogTarget::SCENARIO, __('Trop d\'appel simultané du scénario, il ne peut-être exécuté une nouvelle fois. Il est conseillé de réduire les appels au scénario') . " \"" . $this->getName() . "\".");
                    $this->setLog(__('Trop d\'appel simultané du scénario, il ne peut-être exécuté une nouvelle fois. Il est conseillé de réduire les appels à ce scénario') . ".");
                    $this->persistLog();
                    return false;
                }
            }
        }
        if ($state == ScenarioState::IN_PROGRESS && $this->getConfiguration('allowMultiInstance', 0) == 0) {
            return false;
        }
        $this->setCache([ScenarioCache::STARTING_TIME => $now, ScenarioCache::STATE => ScenarioState::STARTING]);
        // Test execution mode
        if ($this->getConfiguration(ScenarioConf::SYNC_MODE) == 1 || $forceSyncMode) {
            $this->setLog(__('Lancement du scénario en mode synchrone'));
            return $this->execute($trigger, $message);
        } else {
            if (count($this->getTags()) != '') {
                $this->setCache(ScenarioCache::TAGS, $this->getTags());
            }
            $cmd = NEXTDOM_ROOT . '/src/Api/start_scenario.php ';
            $cmd .= ' scenario_id=' . $this->getId();
            $cmd .= ' trigger=' . escapeshellarg($trigger);
            $cmd .= ' "message=' . escapeshellarg(Utils::sanitizeAccent($message)) . '"';
            $cmd .= ' >> ' . LogHelper::getPathToLog('scenario_execution') . ' 2>&1 &';
            SystemHelper::php($cmd);
        }
        return true;
    }

    /**
     *
     * @return mixed
     * @throws \Exception
     */
    public function getState()
    {
        return $this->getCache(ScenarioCache::STATE);
    }

    /**
     * Get data from cache
     *
     * Data are stored in scenarioCacheAttr + Scenario_ID
     *
     * @param string $key Key find
     * @param mixed $defaultValue Default value returned if key is not found
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getCache($key = '', $defaultValue = '')
    {
        $scenarioCacheAttr = CacheManager::byKey(CacheKey::SCENARIO_CACHE_ATTR . $this->getId())->getValue();
        return Utils::getJsonAttr($scenarioCacheAttr, $key, $defaultValue);
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        if ($name != $this->getName()) {
            $this->_changeState = true;
            $this->_changed = true;
        }
        $this->name = $name;
        return $this;
    }

    /**
     *
     * @param mixed $_partial
     * @return bool|null
     */
    public function persistLog($_partial = false)
    {
        if ($this->getConfiguration(ScenarioConf::LOG_MODE, Common::DEFAULT) == 'none') {
            return null;
        }
        $path = NEXTDOM_LOG . '/scenarioLog';
        if (!file_exists($path)) {
            mkdir($path);
        }
        $path .= '/scenario' . $this->getId() . '.log';
        if ($_partial) {
            file_put_contents($path, $this->getLog(), FILE_APPEND);
        } else {
            file_put_contents($path, "------------------------------------\n" . $this->getLog(), FILE_APPEND);
        }
        return true;
    }

    /**
     *
     * @return mixed
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
        $this->_log .= '[' . date(DateFormat::FULL) . '][SCENARIO] ' . $log . "\n";
        if ($this->getConfiguration(ScenarioConf::LOG_MODE, 'default') == 'realtime') {
            $this->persistLog(true);
            $this->_log = '';
        }
    }

    /**
     * Store data in cache
     *
     * Data are stored in scenarioCacheAttr + Scenario_ID
     *
     * @param string|array $key Key to store
     * @param mixed $valueToStore Value to store
     *
     * @throws \Exception
     */
    public function setCache($key, $valueToStore = null)
    {
        CacheManager::set(CacheKey::SCENARIO_CACHE_ATTR . $this->getId(), Utils::setJsonAttr(CacheManager::byKey(CacheKey::SCENARIO_CACHE_ATTR . $this->getId())->getValue(), $key, $valueToStore));
    }

    /**
     * Execute the scenario
     *
     * @param string $trigger
     * @param string $message
     * @return mixed
     * @throws \Exception
     */
    public function execute($trigger = '', $message = '')
    {
        $tags = $this->getCache(ScenarioCache::TAGS);
        if ($tags != '') {
            $this->setTags($tags);
            $this->setCache(ScenarioCache::TAGS, '');
        }
        if (!$this->isActive()) {
            $this->setLog(__('Impossible d\'exécuter le scénario : ') . $this->getHumanName() . __(' sur : ') . $message . __(' car il est désactivé'));
            $this->persistLog();
            return null;
        }
        if ($this->getConfiguration(ScenarioConf::TIME_DEPENDENCY, 0) == 1 && !NextDomHelper::isDateOk()) {
            $this->setLog(__('Lancement du scénario : ') . $this->getHumanName() . __(' annulé car il utilise une condition de type temporelle et que la date système n\'est pas OK'));
            $this->persistLog();
            return null;
        }

        $cmd = CmdManager::byId(str_replace('#', '', $trigger));
        if (is_object($cmd)) {
            LogHelper::addInfo(LogTarget::EVENT, __('Exécution du scénario ') . $this->getHumanName() . __(' déclenché par : ') . $cmd->getHumanName());
            if ($this->getConfiguration(ScenarioConf::TIMELINE_ENABLE)) {
                TimeLineHelper::addTimelineEvent([Common::TYPE => NextDomObj::SCENARIO, Common::ID => $this->getId(), Common::NAME => $this->getHumanName(true), Common::DATETIME => date(DateFormat::FULL), Common::TRIGGER => $cmd->getHumanName(true)]);
            }
        } else {
            LogHelper::addInfo(LogTarget::EVENT, __('Exécution du scénario ') . $this->getHumanName() . __(' déclenché par : ') . $trigger);
            if ($this->getConfiguration(ScenarioConf::TIMELINE_ENABLE)) {
                TimeLineHelper::addTimelineEvent([Common::TYPE => NextDomObj::SCENARIO, Common::ID => $this->getId(), Common::NAME => $this->getHumanName(true), Common::DATETIME => date(DateFormat::FULL), Common::TRIGGER => $trigger == Common::SCHEDULE ? 'programmation' : $trigger]);
            }
        }
        if (count($this->getTags()) == 0) {
            $this->setLog('Start : ' . trim($message, "'") . '.');
        } else {
            $this->setLog('Start : ' . trim($message, "'") . '. Tags : ' . json_encode($this->getTags()));
        }
        $this->setLastLaunch(date(DateFormat::FULL));
        $this->setState(ScenarioState::IN_PROGRESS);
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
        $this->setLog(__('Fin correcte du scénario'));
        $this->persistLog();
        return $this->getReturn();
    }

    /**
     *
     * @param bool $fullInfo
     * @param bool $withoutGroup
     * @param bool $withTag
     * @param bool $htmlRender
     * @param bool $withoutScenarioName
     * @param bool $showObjectName
     * @return string
     * @throws \Exception
     */
    public function getHumanName($fullInfo = false, $withoutGroup = false, $withTag = false, $htmlRender = false, $withoutScenarioName = false, $showObjectName = true)
    {
        $noneText = __('Aucun');
        $result = '';
        if ($showObjectName && is_numeric($this->getObject_id()) && is_object($this->getObject())) {
            $linkedObject = $this->getObject();
            if ($withTag) {
                if ($linkedObject->getDisplay('tagColor') != '') {
                    $result .= '<span class="label label-config" style="background-color:' . $linkedObject->getDisplay('tagColor') . ' !important;color:' . $linkedObject->getDisplay('tagTextColor', 'white') . ' !important">' . $linkedObject->getName() . '</span>';
                } else {
                    $result .= '<span class="label label-primary label-sticker">' . $linkedObject->getName() . '</span>';
                }
            } else {
                $result .= '[' . $linkedObject->getName() . ']';
            }
        } else {
            if ($fullInfo) {
                if ($withTag) {
                    $result .= '<span class="label label-default label-sticker">' . __('Aucun') . '</span>';
                } else {
                    $result .= '[' . $noneText . ']';
                }
            }
        }
        if (!$withoutGroup) {
            if ($this->getGroup() != '') {
                $result .= '[' . $this->getGroup() . ']';
            } else {
                if ($fullInfo) {
                    $result .= '[' . $noneText . ']';
                }
            }
        }
        if ($htmlRender) {
            $result .= '<p class="title">';
        }
        if (!$withoutScenarioName) {
            if ($withTag) {
                $result .= $this->getName();
            } else {
                $result .= '[' . $this->getName() . ']';
            }
        }
        if ($htmlRender) {
            $result .= '</p>';
        }
        return $result;
    }

    /**
     *
     * @param mixed $default
     * @return mixed
     */
    public function getObject_id($default = null)
    {
        if ($this->object_id == '' || !is_numeric($this->object_id)) {
            return $default;
        }
        return $this->object_id;
    }

    /**
     *
     * @param mixed $object_id
     * @return $this
     */
    public function setObject_id($object_id = null)
    {
        if ($object_id != $this->getObject_id()) {
            $this->_changeState = true;
            $this->_changed = true;
        }
        $this->object_id = (!is_numeric($object_id)) ? null : $object_id;
        return $this;
    }

    /**
     *
     * @return JeeObject
     * @throws \Exception
     */
    public function getObject()
    {
        return JeeObjectManager::byId($this->object_id);
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     *
     * @param mixed $group
     * @return $this
     */
    public function setGroup($group)
    {
        if ($group != $this->getGroup()) {
            $this->_changeState = true;
            $this->_changed = true;
        }
        $this->group = $group;
        return $this;
    }

    /**
     *
     * @return array
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
     * @param mixed $lastLaunch
     * @throws \Exception
     */
    public function setLastLaunch($lastLaunch)
    {
        $this->setCache(ScenarioCache::LAST_LAUNCH, $lastLaunch);
    }

    /**
     *
     * @param mixed $state
     * @throws \Exception
     */
    public function setState($state)
    {
        if ($this->getCache(ScenarioCache::STATE) != $state) {
            $this->emptyCacheWidget();
            EventManager::add('scenario::update', ['scenario_id' => $this->getId(), Common::STATE => $state, ScenarioCache::LAST_LAUNCH => $this->getLastLaunch()]);
        }
        $this->setCache(ScenarioCache::STATE, $state);
    }

    /**
     *
     */
    public function emptyCacheWidget()
    {
        $mc = CacheManager::byKey('scenarioHtmldashboard' . $this->getId());
        $mc->remove();
        $mc = CacheManager::byKey('scenarioHtmlmobile' . $this->getId());
        $mc->remove();
        $mc = CacheManager::byKey('scenarioHtmlmview' . $this->getId());
        $mc->remove();
        $mc = CacheManager::byKey('scenarioHtmldview' . $this->getId());
        $mc->remove();
    }

    /**
     *
     * @return mixed
     * @throws \Exception
     */
    public function getLastLaunch()
    {
        return $this->getCache(ScenarioCache::LAST_LAUNCH);
    }

    /**
     *
     * @param mixed $pid
     * @throws \Exception
     */
    public function setPID($pid = '')
    {
        $this->setCache('pid', $pid);
    }

    /**
     *
     * @return ScenarioElement[]
     * @throws \Exception
     */
    public function getElement()
    {
        if (count($this->_elements) > 0) {
            return $this->_elements;
        }
        $result = [];
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
        }
        elseif ($elements != '') {
            $element = ScenarioElementManager::byId($elementId);
            if (is_object($element)) {
                $result[] = $element;
                $this->_elements = $result;
            }
        }
        return $result;
    }

    /**
     *
     * @return mixed
     */
    public function getScenarioElement()
    {
        return Utils::isJson($this->scenarioElement, $this->scenarioElement);
    }

    /**
     *
     * @param $_scenarioElement
     * @return $this
     */
    public function setScenarioElement($_scenarioElement)
    {
        if (is_array($_scenarioElement)) {
            $_scenarioElement = json_encode($_scenarioElement, JSON_UNESCAPED_UNICODE);
        }
        $this->updateChangeState($this->scenarioElement, $_scenarioElement);
        $this->scenarioElement = $_scenarioElement;
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
     * Set the state of the scenario.
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
     * @return string
     */
    public function getReturn()
    {
        return $this->_return;
    }

    /**
     *
     * @param mixed $_return
     * @return $this
     */
    public function setReturn($_return)
    {
        $this->_return = $_return;
        return $this;
    }

    /**
     *
     * @param mixed $name
     * @return Scenario
     * @throws \Exception
     */
    public function copy($name)
    {
        $scenarioCopy = clone $this;
        $scenarioCopy->setName($name);
        $scenarioCopy->setId('');
        $scenario_element_list = [];
        foreach ($this->getElement() as $element) {
            $scenario_element_list[] = $element->copy();
        }
        $scenarioCopy->setScenarioElement($scenario_element_list);
        $scenarioCopy->setLog('');
        $scenarioCopy->save();
        if (file_exists(NEXTDOM_LOG . '/scenarioLog/scenario' . $scenarioCopy->getId() . '.log')) {
            unlink(NEXTDOM_LOG . '/scenarioLog/scenario' . $scenarioCopy->getId() . '.log');
        }
        return $scenarioCopy;
    }

    /**
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        if ($this->getLastLaunch() == '' && ($this->getMode() == Common::SCHEDULE || $this->getMode() == 'all')) {
            $calculateScheduleDate = $this->calculateScheduleDate();
            $this->setLastLaunch($calculateScheduleDate['prevDate']);
        }
        DBHelper::save($this);
        $this->emptyCacheWidget();
        if ($this->_changeState) {
            $this->_changeState = false;
            EventManager::add('scenario::update', ['scenario_id' => $this->getId(), 'isActive' => $this->getIsActive(), Common::STATE => $this->getState(), ScenarioCache::LAST_LAUNCH => $this->getLastLaunch()]);
        }
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param $_mode
     * @return $this
     */
    public function setMode($_mode)
    {
        $this->updateChangeState($this->mode, $_mode);
        $this->mode = $_mode;
        return $this;
    }

    /**
     * @return array
     */
    public function calculateScheduleDate()
    {
        $calculatedDate = ['prevDate' => '', 'nextDate' => ''];
        if (is_array($this->getSchedule())) {
            $calculatedDate_tmp = ['prevDate' => '', 'nextDate' => ''];
            foreach ($this->getSchedule() as $schedule) {
                try {
                    $c = new \Cron\CronExpression($schedule, new \Cron\FieldFactory);
                    $calculatedDate_tmp['prevDate'] = $c->getPreviousRunDate()->format(DateFormat::FULL);
                    $calculatedDate_tmp['nextDate'] = $c->getNextRunDate()->format(DateFormat::FULL);
                } catch (\Exception $exc) {

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
                $c = new \Cron\CronExpression($this->getSchedule(), new \Cron\FieldFactory);
                $calculatedDate['prevDate'] = $c->getPreviousRunDate()->format(DateFormat::FULL);
                $calculatedDate['nextDate'] = $c->getNextRunDate()->format(DateFormat::FULL);
            } catch (\Exception $exc) {

            }
        }
        return $calculatedDate;
    }

    /**
     * @return bool|mixed|null
     */
    public function getSchedule()
    {
        return Utils::isJson($this->schedule, $this->schedule);
    }

    /**
     * @param $_schedule
     * @return $this
     */
    public function setSchedule($_schedule)
    {
        if (is_array($_schedule)) {
            $_schedule = json_encode($_schedule, JSON_UNESCAPED_UNICODE);
        }
        $this->updateChangeState($this->schedule, $_schedule);
        $this->schedule = $_schedule;
        return $this;
    }

    /**
     *
     * @param mixed $_version
     * @return string
     * @throws \Exception
     */
    public function toHtml($_version)
    {
        if (!$this->hasRight('r')) {
            return '';
        }
        $mc = CacheManager::byKey('scenarioHtml' . $_version . $this->getId());
        if ($mc->getValue() != '') {
            return $mc->getValue();
        }

        $version = NextDomHelper::versionAlias($_version);
        $replace = [
            '#id#' => $this->getId(),
            '#state#' => $this->getState(),
            '#isActive#' => $this->getIsActive(),
            '#name#' => ($this->getDisplay(Common::NAME) != '') ? $this->getDisplay(Common::NAME) : $this->getHumanName(),
            '#shortname#' => ($this->getDisplay(Common::NAME) != '') ? $this->getDisplay(Common::NAME) : $this->getName(),
            '#treename#' => $this->getHumanName(false, false, false, false, true),
            '#icon#' => $this->getIcon(),
            '#lastLaunch#' => $this->getLastLaunch(),
            '#scenarioLink#' => $this->getLinkToConfiguration(),
            '#version#' => $_version,
            '#height#' => $this->getDisplay('height', 'auto'),
            '#width#' => $this->getDisplay('width', 'auto')
        ];
        if (!isset(self::$_templateArray)) {
            self::$_templateArray = [];
        }
        if (!isset(self::$_templateArray[$version])) {
            self::$_templateArray[$version] = FileSystemHelper::getCoreTemplateFileContent($version, NextDomObj::SCENARIO, '');
        }
        $html = Utils::templateReplace($replace, self::$_templateArray[$version]);
        CacheManager::set('scenarioHtml' . $version . $this->getId(), $html);
        return $html;
    }

    /**
     *
     * @param mixed $_right
     * @param User|null $_user
     *
     * @return boolean
     */
    public function hasRight($_right, $_user = null)
    {
        if ($_user !== null) {
            if ($_user->getProfils() == 'admin' || $_user->getProfils() == 'user') {
                return true;
            }
            if (strpos($_user->getRights(NextDomObj::SCENARIO . $this->getId()), $_right) !== false) {
                return true;
            }
            return false;
        }
        if (!AuthentificationHelper::isConnected()) {
            return false;
        }
        if (AuthentificationHelper::isConnectedAsAdmin() || AuthentificationHelper::isConnectedWithRights('user')) {
            return true;
        }
        if (strpos(UserManager::getStoredUser()->getRights(NextDomObj::SCENARIO . $this->getId()), $_right) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Get scenario icon
     *
     * @param bool $onlyClass
     * @return string
     * @throws \Exception
     */
    public function getIcon($onlyClass = false)
    {
        if ($this->isActive()) {
            switch ($this->getState()) {
                case ScenarioState::STARTING:
                    $cssClass = 'fas fa-hourglass-start';
                    break;
                case ScenarioState::IN_PROGRESS:
                    $cssClass = 'fas fa-spinner fa-spin';
                    break;
                case ScenarioState::ERROR:
                    $cssClass = 'fas fa-exclamation-triangle';
                    break;
                default:
                    // User custom icon
                    if (strpos($this->getDisplay('icon'), '<i') === 0) {
                        // Icon stored with HTML
                        if ($onlyClass) {
                            $cssClass = trim(str_replace(['<i', 'class=', '"', '/>', '></i>'], '', $this->getDisplay('icon')));
                        } else {
                            return $this->getDisplay('icon');
                        }
                    } else {
                        $cssClass = 'fas fa-check';
                    }
                    break;
            }
        } else {
            $cssClass = 'fas fa-times';
        }

        if ($onlyClass) {
            return $cssClass;
        } else {
            return '<i class="' . $cssClass . '"></i>';
        }
    }

    /**
     *
     * @return string
     */
    public function getLinkToConfiguration(): string
    {
        return 'index.php?v=d&p=scenario&id=' . $this->getId();
    }

    /**
     *
     * @throws CoreException
     */
    public function preSave()
    {
        if ($this->getTimeout() == '' || !is_numeric($this->getTimeout())) {
            $this->setTimeout(0);
        }
        if ($this->getName() == '') {
            throw new CoreException(__('Le nom du scénario ne peut pas être vide.'));
        }
        if (($this->getMode() == Common::SCHEDULE || $this->getMode() == 'all') && $this->getSchedule() == '') {
            throw new CoreException(__('Le scénario est de type programmé mais la programmation est vide'));
        }
        if ($this->getConfiguration('has_return', 0) == 1) {
            $this->setConfiguration(ScenarioConf::SYNC_MODE, 1);
        }
        if ($this->getConfiguration(ScenarioConf::LOG_MODE) == '') {
            $this->setConfiguration(ScenarioConf::LOG_MODE, 'default');
        }
    }

    /**
     *
     * @param mixed $_default
     * @return mixed
     */
    public function getTimeout($_default = 0)
    {
        if ($this->timeout == '' || !is_numeric($this->timeout)) {
            return $_default;
        }
        return $this->timeout;
    }

    /**
     *
     * @param $_timeout
     * @return $this
     */
    public function setTimeout($_timeout)
    {
        if ($_timeout === '' || is_nan(intval($_timeout)) || $_timeout < 1) {
            $_timeout = 0;
        }
        $this->updateChangeState($this->timeout, $_timeout);
        $this->timeout = $_timeout;
        return $this;
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
     * @return mixed
     * @throws \Exception
     */
    public function remove()
    {
        ViewDataManager::removeByTypeLinkId(NextDomObj::SCENARIO, $this->getId());
        DataStoreManager::removeByTypeLinkId(NextDomObj::SCENARIO, $this->getId());
        foreach ($this->getElement() as $element) {
            $element->remove();
        }
        $this->emptyCacheWidget();
        if (file_exists(NEXTDOM_LOG . '/scenarioLog/scenario' . $this->getId() . '.log')) {
            unlink(NEXTDOM_LOG . '/scenarioLog/scenario' . $this->getId() . '.log');
        }
        CacheManager::delete(CacheKey::SCENARIO_CACHE_ATTR . $this->getId());
        return parent::remove();
    }

    /**
     *
     * @param mixed $_key
     * @param mixed $_private
     * @return boolean
     * @throws \Exception
     */
    public function removeData($_key, $_private = false)
    {
        if ($_private) {
            $dataStore = DataStoreManager::byTypeLinkIdKey(NextDomObj::SCENARIO, $this->getId(), $_key);
        } else {
            $dataStore = DataStoreManager::byTypeLinkIdKey(NextDomObj::SCENARIO, -1, $_key);
        }
        if (is_object($dataStore)) {
            return $dataStore->remove();
        }
        return true;
    }

    /**
     *
     * @param mixed $_key
     * @param mixed $_value
     * @param bool $_private
     * @return boolean
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function setData($_key, $_value, $_private = false)
    {
        $dataStore = new DataStore();
        $dataStore->setType(NextDomObj::SCENARIO);
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

    /**
     * @param $key
     * @param bool $protected
     * @param string $default
     * @return string
     * @throws \Exception
     */
    public function getData($key, $protected = false, $default = '')
    {
        if ($protected !== false) {
            $dataStore = DataStoreManager::byTypeLinkIdKey(NextDomObj::SCENARIO, $this->getId(), $key);
        } else {
            $dataStore = DataStoreManager::byTypeLinkIdKey(NextDomObj::SCENARIO, -1, $key);
        }
        if (is_object($dataStore)) {
            return $dataStore->getValue($default);
        }
        return $default;
    }

    /**
     *
     * @return boolean
     * @throws \Exception
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
                    $c = new \Cron\CronExpression($schedule, new \Cron\FieldFactory);
                    try {
                        if ($c->isDue()) {
                            return true;
                        }
                    } catch (\Exception $e) {

                    }
                    try {
                        $prev = $c->getPreviousRunDate()->getTimestamp();
                    } catch (\Exception $e) {
                        continue;
                    }
                    $lastCheck = strtotime($this->getLastLaunch());
                    $diff = abs((strtotime('now') - $prev) / 60);
                    if ($lastCheck <= $prev && $diff <= ConfigManager::byKey('maxCatchAllow') || ConfigManager::byKey('maxCatchAllow') == -1) {
                        return true;
                    }
                } catch (\Exception $e) {

                }
            }
        } else {
            try {
                $c = new \Cron\CronExpression($this->getSchedule(), new \Cron\FieldFactory);
                try {
                    if ($c->isDue()) {
                        return true;
                    }
                } catch (\Exception $e) {

                }
                try {
                    $prev = $c->getPreviousRunDate()->getTimestamp();
                } catch (\Exception $e) {
                    return false;
                }
                $lastCheck = strtotime($this->getLastLaunch());
                $diff = abs((strtotime('now') - $prev) / 60);
                if ($lastCheck <= $prev && $diff <= ConfigManager::byKey('maxCatchAllow') || ConfigManager::byKey('maxCatchAllow') == -1) {
                    return true;
                }
            } catch (\Exception $exc) {

            }
        }
        return false;
    }

    /**
     *
     * @return boolean
     * @throws \Exception
     */
    public function stop()
    {
        $crons = CronManager::searchClassAndFunction(NextDomObj::SCENARIO, 'doIn', '"scenario_id":' . $this->getId());
        if (is_array($crons)) {
            foreach ($crons as $cron) {
                if ($cron->getState() == 'run') {
                    try {
                        $cron->halt();
                        $cron->remove();
                    } catch (\Exception $e) {
                        LogHelper::addInfo(LogTarget::SCENARIO, __('Can not stop subtask : ') . print_r($cron->getOption(), true));
                    }
                }
            }
        }
        if ($this->running()) {
            if ($this->getPID() > 0) {
                SystemHelper::kill($this->getPID());
                $retry = 0;
                while ($this->running() && $retry < 10) {
                    sleep(1);
                    SystemHelper::kill($this->getPID());
                    $retry++;
                }
            }

            if ($this->running()) {
                SystemHelper::kill("scenario_id=" . $this->getId() . ' ');
                sleep(1);
                if ($this->running()) {
                    SystemHelper::kill("scenario_id=" . $this->getId() . ' ');
                    sleep(1);
                }
            }
            if ($this->running()) {
                throw new CoreException(__('Impossible d\'arrêter le scénario : ') . $this->getHumanName() . __('. PID : ') . $this->getPID());
            }
        }
        $this->setState('stop');
        return true;
    }

    /**
     *
     * @return boolean
     * @throws \Exception
     */
    public function running()
    {
        if (intval($this->getPID()) > 0 && posix_getsid(intval($this->getPID())) && (!file_exists('/proc/' . $this->getPID() . '/cmdline') || strpos(file_get_contents('/proc/' . $this->getPID() . '/cmdline'), 'scenario_id=' . $this->getId()) !== false)) {
            return true;
        }
        if (count(SystemHelper::ps('scenario_id=' . $this->getId() . ' ', [getmypid()])) > 0) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return string
     * @throws \Exception
     */
    public function getPID()
    {
        return $this->getCache('pid');
    }

    /**
     *
     * @return mixed
     * @throws \Exception
     */
    public function toArray()
    {
        $return = Utils::o2a($this, true);
        $cache = $this->getCache([ScenarioCache::STATE, ScenarioCache::LAST_LAUNCH]);
        // TODO: Pourquoi ce test a-t-il dû être rajouté ?
        if (isset($cache[ScenarioCache::STATE])) {
            $return[ScenarioCache::STATE] = $cache[ScenarioCache::STATE];
        } else {
            $return[ScenarioCache::STATE] = '';
        }
        if (isset($cache[ScenarioCache::LAST_LAUNCH])) {
            $return[ScenarioCache::LAST_LAUNCH] = $cache[ScenarioCache::LAST_LAUNCH];
        } else {
            $return[ScenarioCache::LAST_LAUNCH] = '';
        }
        return $return;
    }

    /**
     *
     * @param mixed $_data
     * @param mixed $_level
     * @param mixed $_drill
     * @return string
     * @throws \Exception
     */
    public function getLinkData(&$_data = ['node' => [], 'link' => []], $_level = 0, $_drill = null)
    {
        if ($_drill === null) {
            $_drill = ConfigManager::byKey('graphlink::scenario::drill');
        }
        if (isset($_data['node'][NextDomObj::SCENARIO . $this->getId()])) {
            return null;
        }
        if (!$this->isActive() && $_level > 0) {
            return $_data;
        }
        $_level++;
        if ($_level > $_drill) {
            return $_data;
        }

        $_data['node'][NextDomObj::SCENARIO . $this->getId()] = [
            Common::ID => NextDomObj::SCENARIO . $this->getId(),
            Common::NAME => $this->getName(),
            'fontweight' => ($_level == 1) ? 'bold' : 'normal',
            'shape' => 'rect',
            'width' => 40,
            'height' => 40,
            'color' => 'green',
            'image' => '/public/img/NextDom_Scenario.png',
            'title' => $this->getHumanName(),
            'url' => 'index.php?v=d&p=scenario&id=' . $this->getId(),
        ];
        $use = $this->getUse();
        $usedBy = $this->getUsedBy();
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $this->getObject(), 'object', $_data, $_level + 1, $_drill, ['dashvalue' => '1,0', 'lengthfactor' => 0.6]);
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $use['cmd'], 'cmd', $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $use[NextDomObj::SCENARIO], NextDomObj::SCENARIO, $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $use['eqLogic'], 'eqLogic', $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $use['dataStore'], 'dataStore', $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $use['view'], 'view', $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $use['plan'], 'plan', $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $usedBy['cmd'], 'cmd', $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $usedBy[NextDomObj::SCENARIO], NextDomObj::SCENARIO, $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $usedBy['eqLogic'], 'eqLogic', $_data, $_level, $_drill);
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $usedBy['interactDef'], 'interactDef', $_data, $_level, $_drill, ['dashvalue' => '2,6', 'lengthfactor' => 0.6]);
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $usedBy['plan'], 'plan', $_data, $_level, $_drill, ['dashvalue' => '2,6', 'lengthfactor' => 0.6]);
        Utils::addGraphLink($this, NextDomObj::SCENARIO, $usedBy['view'], 'view', $_data, $_level, $_drill, ['dashvalue' => '2,6', 'lengthfactor' => 0.6]);
        return $_data;
    }

    /**
     *
     * @return mixed
     * @throws \Exception
     */
    public function getUse()
    {
        $json = NextDomHelper::fromHumanReadable(json_encode($this->export('array')));
        return NextDomHelper::getTypeUse($json);
    }

    /**
     *
     * @param mixed $_mode
     * @return mixed
     * @throws \Exception
     */
    public function export($_mode = 'text')
    {
        $return = null;
        if ($_mode == 'text') {
            $return = '';
            $return .= '- Nom du scénario : ' . $this->getName() . "\n";
            if (is_numeric($this->getObject_id())) {
                $return .= '- Objet parent : ' . $this->getObject()->getName() . "\n";
            }
            $return .= '- Mode du scénario : ' . $this->getMode() . "\n";
            $schedules = $this->getSchedule();
            if ($this->getMode() == Common::SCHEDULE || $this->getMode() == 'all') {
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
                    $return .= '    - Evènement : ' . NextDomHelper::toHumanReadable($trigger) . "\n";
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
            $return = Utils::o2a($this);
            $return[Common::TRIGGER] = NextDomHelper::toHumanReadable($return[Common::TRIGGER]);
            $return['elements'] = [];
            foreach ($this->getElement() as $element) {
                $return['elements'][] = $element->getAjaxElement('array');
            }
            if (isset($return['id'])) {
                unset($return['id']);
            }
            if (isset($return[ScenarioCache::LAST_LAUNCH])) {
                unset($return[ScenarioCache::LAST_LAUNCH]);
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
            if (isset($return['_changeState'])) {
                unset($return['_changeState']);
            }
            if (isset($return['_realTrigger'])) {
                unset($return['_realTrigger']);
            }
            if (isset($return['_elements'])) {
                unset($return['_elements']);
            }
        }
        return $return;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $_description
     * @return $this
     */
    public function setDescription($_description)
    {
        $this->updateChangeState($this->description, $_description);
        $this->description = $_description;
        return $this;
    }

    /**
     *
     * @param bool $_array
     * @return mixed
     * @throws \Exception
     */
    public function getUsedBy($_array = false)
    {
        $return = [NextDomObj::CMD => [], NextDomObj::EQLOGIC => [], NextDomObj::SCENARIO => [], NextDomObj::PLAN => [], NextDomObj::VIEW => []];
        $return[NextDomObj::EQLOGIC] = EqLogicManager::searchConfiguration(['#scenario' . $this->getId() . '#', '"scenario_id":"' . $this->getId()]);
        $return[NextDomObj::INTERACT_DEF] = InteractDefManager::searchByUse(['#scenario' . $this->getId() . '#', '"scenario_id":"' . $this->getId()]);
        // @TODO: scenario_id, pas de guillemet ouvrant, à vérifier
        $return[NextDomObj::SCENARIO] = ScenarioManager::searchByUse([
            ['action' => NextDomObj::SCENARIO, 'option' => $this->getId(), 'and' => true],
            ['action' => '#scenario' . $this->getId() . '#'],
        ]);
        $return[NextDomObj::VIEW] = ViewManager::searchByUse(NextDomObj::SCENARIO, $this->getId());
        $return[NextDomObj::PLAN] = PlanHeaderManager::searchByUse(NextDomObj::SCENARIO, $this->getId());
        if ($_array) {
            foreach ($return as &$value) {
                $value = Utils::o2a($value);
            }
        }
        return $return;
    }

    public function clearLog()
    {
        $this->_log = '';
    }

    public function resetRepeatIfStatus()
    {
        foreach ($this->getElement() as $element) {
            $element->resetRepeatIfStatus();
        }
    }

    /**
     *
     * @return mixed
     */
    public function getRealTrigger()
    {
        return $this->_realTrigger;
    }

    /**
     *
     * @param mixed $_realTrigger
     * @return $this
     */
    public function setRealTrigger($_realTrigger)
    {
        $this->_realTrigger = $_realTrigger;
        return $this;
    }
}
