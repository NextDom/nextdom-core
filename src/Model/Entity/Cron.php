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

use NextDom\Enums\DateFormat;
use NextDom\Enums\LogTarget;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\CronManager;

/**
 * Cron
 *
 * @ORM\Table(name="cron", uniqueConstraints={@ORM\UniqueConstraint(name="class_function_option", columns={"class", "function", "option"})}, indexes={@ORM\Index(name="type", columns={"class"}), @ORM\Index(name="logicalId_Type", columns={"class"}), @ORM\Index(name="deamon", columns={"deamon"})})
 * @ORM\Entity
 */
class Cron implements EntityInterface
{

    /**
     * @var integer 1 if cron is enabled
     *
     * @ORM\Column(name="enable", type="integer", nullable=true)
     */
    protected $enable = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=127, nullable=true)
     */
    protected $class = '';

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="string", length=127, nullable=false)
     */
    protected $function;

    /**
     * @var string
     *
     * @ORM\Column(name="schedule", type="string", length=127, nullable=true)
     */
    protected $schedule = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="timeout", type="integer", nullable=true)
     */
    protected $timeout;

    /**
     * @var integer
     *
     * @ORM\Column(name="deamon", type="integer", nullable=true)
     */
    protected $deamon = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="deamonSleepTime", type="integer", nullable=true)
     */
    protected $deamonSleepTime;

    /**
     * @var string
     *
     * @ORM\Column(name="option", type="string", length=255, nullable=true)
     */
    protected $option;

    /**
     * @var integer
     *
     * @ORM\Column(name="once", type="integer", nullable=true)
     */
    protected $once = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var bool
     */
    protected $_changed = false;

    /**
     * Get enabled state of the cron task
     *
     * @param int $defaultValue Default value if cron task is not initialized
     *
     * @return int 1 for enabled task, 0 for disabled, or defaultValue
     */
    public function getEnable($defaultValue = 0)
    {
        if ($this->enable == '' || !is_numeric($this->enable)) {
            return $defaultValue;
        }
        return $this->enable;
    }

    /**
     * Set enabled state of the cron task
     *
     * @param int $newState 1 for enable task, 0 for disable
     *
     * @return $this
     */
    public function setEnable($newState)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->enable, $newState);
        $this->enable = $newState;
        return $this;
    }

    /**
     * Get bool enabled state
     *
     * @return bool True is task is enabled
     */
    public function isEnabled()
    {
        return $this->enable == 1;
    }

    /**
     * Get timeout of the task
     * If timeout is not configured, return default value from
     *
     * @return int Timeout
     *
     * @throws \Exception
     */
    public function getTimeout()
    {
        $timeout = $this->timeout;
        if ($timeout == 0) {
            $timeout = ConfigManager::byKey('maxExecTimeCrontask');
        }
        return $timeout;
    }

    /**
     *
     * @param $_timeout
     * @return $this
     */
    public function setTimeout($_timeout)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->timeout, $_timeout);
        $this->timeout = $_timeout;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeamon()
    {
        return $this->deamon;
    }

    /**
     *
     * @param $_deamons
     * @return $this
     */
    public function setDeamon($_deamons)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->deamon, $_deamons);
        $this->deamon = $_deamons;
        return $this;
    }

    /**
     * @return int|mixed
     * @throws \Exception
     */
    public function getDeamonSleepTime()
    {
        $deamonSleepTime = $this->deamonSleepTime;
        if ($deamonSleepTime == 0) {
            $deamonSleepTime = ConfigManager::byKey('deamonsSleepTime');
        }
        return $deamonSleepTime;
    }

    /**
     *
     * @param $_deamonSleepTime
     * @return $this
     */
    public function setDeamonSleepTime($_deamonSleepTime)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->deamonSleepTime, $_deamonSleepTime);
        $this->deamonSleepTime = $_deamonSleepTime;
        return $this;
    }

    /**
     * @param int $defaultValue
     * @return int
     */
    public function getOnce($defaultValue = 0)
    {
        if ($this->once == '' || !is_numeric($this->once)) {
            return $defaultValue;
        }
        return $this->once;
    }

    /**
     *
     * @param $_once
     * @return $this
     */
    public function setOnce($_once)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->once, $_once);
        $this->once = $_once;
        return $this;
    }

    /**
     * Get the name of the SQL table where data is stored.
     *
     * @return string
     */
    public function getTableName()
    {
        return 'cron';
    }

    /**
     * Check if cron object is valid before save
     * @throws CoreException
     */
    public function preSave()
    {
        if ($this->getFunction() == '') {
            throw new CoreException(__('La fonction ne peut pas être vide'));
        }
        if ($this->getSchedule() == '') {
            throw new CoreException(__('La programmation ne peut pas être vide : ') . print_r($this, true));
        }
        if ($this->getOption() == '' || count($this->getOption()) == 0) {
            $cron = CronManager::byClassAndFunction($this->getClass(), $this->getFunction());
            if (is_object($cron)) {
                $this->setId($cron->getId());
            }
        }
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     *
     * @param $_function
     * @return $this
     */
    public function setFunction($_function)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->function, $_function);
        $this->function = $_function;
        return $this;
    }

    /**
     * @return string
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     *
     * @param $_schedule
     * @return $this
     */
    public function setSchedule($_schedule)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->schedule, $_schedule);
        $this->schedule = $_schedule;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOption()
    {
        return json_decode($this->option, true);
    }

    /**
     *
     * @param $_option
     * @return $this
     */
    public function setOption($_option)
    {
        $_option = json_encode($_option, JSON_UNESCAPED_UNICODE);
        $this->_changed = Utils::attrChanged($this->_changed, $this->option, $_option);
        $this->option = $_option;
        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     *
     * @param $newClass
     * @return $this
     */
    public function setClass($newClass)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->class, $newClass);
        $this->class = $newClass;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set task id
     *
     * @param $_id
     * @return $this Task object
     */
    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
    }

    /**
     * Stop task after insert in database
     */
    public function postInsert()
    {
        $this->setState('stop');
        $this->setPID();
    }

    /**
     * Set task state
     *
     * @param mixed $state State of the task
     * @throws \Exception
     */
    public function setState($state)
    {
        $this->setCache('state', $state);
    }

    /**
     * Store task data in cache
     *
     * @param mixed $cacheKey
     * @param mixed $cacheValue
     * @throws \Exception
     */
    public function setCache($cacheKey, $cacheValue = null)
    {
        CacheManager::set('cronCacheAttr' . $this->getId(), Utils::setJsonAttr(CacheManager::byKey('cronCacheAttr' . $this->getId())->getValue(), $cacheKey, $cacheValue));
    }

    /**
     * Store PID in cache
     *
     * @param mixed $pid
     * @throws \Exception
     */
    public function setPID($pid = null)
    {
        $this->setCache('pid', $pid);
    }

    /**
     * Save cron object in database
     *
     * @return mixed
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        return DBHelper::save($this, false, true);
    }

    /**
     * Remove cron object from the database
     *
     * @param bool $haltBefore
     * @return mixed
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function remove($haltBefore = true)
    {
        if ($haltBefore && $this->running()) {
            $this->halt();
        }
        CacheManager::delete('cronCacheAttr' . $this->getId());
        return DBHelper::remove($this);
    }

    /**
     * Check if this cron is currently running
     *
     * @return boolean
     * @throws \Exception
     */
    public function running(): bool
    {
        if (($this->getState() == 'run' || $this->getState() == 'stoping') && $this->getPID() > 0) {
            if (posix_getsid($this->getPID()) && (!file_exists('/proc/' . $this->getPID() . '/cmdline') || strpos(@file_get_contents('/proc/' . $this->getPID() . '/cmdline'), 'cron_id=' . $this->getId()) !== false)) {
                return true;
            }
        }
        if (count(SystemHelper::ps('cron_id=' . $this->getId() . '$')) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get current state
     *
     * @return mixed Current state
     * @throws \Exception
     */
    public function getState()
    {
        return $this->getCache('state', 'stop');
    }

    /**
     * Get task data in cache
     *
     * @param string $cacheKey
     * @param string $cacheValue
     * @return mixed
     * @throws \Exception
     */
    public function getCache($cacheKey = '', $cacheValue = '')
    {
        $cache = CacheManager::byKey('cronCacheAttr' . $this->getId())->getValue();
        return Utils::getJsonAttr($cache, $cacheKey, $cacheValue);
    }

    /**
     * Get task PID
     *
     * @param mixed $defaultValue
     *
     * @return mixed Task PID
     * @throws \Exception
     */
    public function getPID($defaultValue = null)
    {
        return $this->getCache('pid', $defaultValue);
    }

    /**
     * Stop immediatly cron (this method must be only call by jeecron master)
     */
    public function halt()
    {
        if (!$this->running()) {
            $this->setState('stop');
            $this->setPID();
        } else {
            LogHelper::addInfo(LogTarget::CRON, __('Arrêt de ') . $this->getClass() . '::' . $this->getFunction() . '(), PID : ' . $this->getPID());
            if ($this->getPID() > 0) {
                SystemHelper::kill($this->getPID());
                $retry = 0;
                while ($this->running() && $retry < (ConfigManager::byKey('deamonsSleepTime') + 5)) {
                    sleep(1);
                    SystemHelper::kill($this->getPID());
                    $retry++;
                }
                $retry = 0;
                while ($this->running() && $retry < (ConfigManager::byKey('deamonsSleepTime') + 5)) {
                    sleep(1);
                    SystemHelper::kill($this->getPID());
                    $retry++;
                }
            }
            if ($this->running()) {
                SystemHelper::kill("cron_id=" . $this->getId() . "$");
                sleep(1);
                if ($this->running()) {
                    SystemHelper::kill("cron_id=" . $this->getId() . "$");
                    sleep(1);
                }
                if ($this->running()) {
                    $this->setState('error');
                    $this->setPID();
                    throw new CoreException($this->getClass() . '::' . $this->getFunction() . __('() : Impossible d\'arrêter la tâche'));
                }
            } else {
                $this->setState('stop');
                $this->setPID();
            }
        }
        return true;
    }

    /**
     * Start cron task
     */
    public function start()
    {
        if (!$this->running()) {
            $this->setState('starting');
        } else {
            $this->setState('run');
        }
    }

    /**
     * Launch cron (this method must be only call by jeeCron master)
     *
     * @param bool $noErrorReport
     * @throws CoreException
     */
    public function run($noErrorReport = false)
    {
        $cmd = NEXTDOM_ROOT . '/src/Api/start_cron.php';
        $cmd .= ' "cron_id=' . $this->getId() . '"';
        if (!$this->running()) {
            SystemHelper::php($cmd . ' >> ' . LogHelper::getPathToLog('cron_execution') . ' 2>&1 &');
        } else {
            if (!$noErrorReport) {
                $this->halt();
                if (!$this->running()) {
                    exec($cmd . ' >> ' . LogHelper::getPathToLog('cron_execution') . ' 2>&1 &');
                } else {
                    throw new CoreException(__('Impossible d\'exécuter la tâche car elle est déjà en cours d\'exécution (') . ' : ' . $cmd);
                }
            }
        }
    }

    /**
     * Refresh DB state of this cron
     *
     * @return boolean
     * @throws \Exception
     */
    public function refresh(): bool
    {
        if (($this->getState() == 'run' || $this->getState() == 'stoping') && !$this->running()) {
            $this->setState('stop');
            $this->setPID();
        }
        return true;
    }

    /**
     * Stop task
     */
    public function stop()
    {
        if ($this->running()) {
            $this->setState('stoping');
        }
    }

    /**
     * Check if it's time to launch cron
     *
     * @return boolean
     * @throws \Exception
     */
    public function isDue(): bool
    {
        //check if already sent on that minute
        $last = strtotime($this->getLastRun());
        $now = time();
        $now = ($now - $now % 60);
        $last = ($last - $last % 60);
        if ($now == $last) {
            return false;
        }
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
            $diff = abs((strtotime('now') - $prev) / 60);
            if (strtotime($this->getLastRun()) < $prev && ($diff <= ConfigManager::byKey('maxCatchAllow') || ConfigManager::byKey('maxCatchAllow') == -1)) {
                return true;
            }
        } catch (\Exception $e) {
            LogHelper::addDebug(LogTarget::CRON, 'Error on isDue : ' . $e->getMessage() . ', cron : ' . $this->getSchedule());
        }
        return false;
    }

    /**
     * Get last task run
     *
     * @return mixed Last task run
     * @throws \Exception
     */
    public function getLastRun()
    {
        return $this->getCache('lastRun');
    }

    /**
     * Get date of the next task run
     *
     * @return bool|string
     */
    public function getNextRunDate()
    {
        try {
            $cronExpression = new \Cron\CronExpression($this->getSchedule(), new \Cron\FieldFactory);
            return $cronExpression->getNextRunDate()->format(DateFormat::FULL);
        } catch (\Exception $e) {

        }
        return false;
    }

    /**
     * Get human name of cron
     * @return string
     */
    public function getName()
    {
        if ($this->getClass() != '') {
            return $this->getClass() . '::' . $this->getFunction() . '()';
        }
        return $this->getFunction() . '()';
    }

    /**
     * Get cron data in array
     *
     * @return array Cron data
     * @throws \Exception
     */
    public function toArray()
    {
        $return = Utils::o2a($this, true);
        $return['state'] = $this->getState();
        $return['lastRun'] = $this->getLastRun();
        $return['pid'] = $this->getPID();
        $return['runtime'] = $this->getCache('runtime');
        return $return;
    }

    /**
     * Set last task run
     *
     * @param mixed $lastRun Last task run
     * @throws \Exception
     */
    public function setLastRun($lastRun)
    {
        $this->setCache('lastRun', $lastRun);
    }

    /**
     * @return bool
     */
    public function getChanged()
    {
        return $this->_changed;
    }

    /**
     * @param $_changed
     * @return $this
     */
    public function setChanged($_changed)
    {
        $this->_changed = $_changed;
        return $this;
    }
}
