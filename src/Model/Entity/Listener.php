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

use NextDom\Helpers\DBHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ListenerManager;

/**
 * Listener
 *
 * @ORM\Table(name="listener", indexes={@ORM\Index(name="event", columns={"event"})})
 * @ORM\Entity
 */
class Listener implements EntityInterface
{

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=127, nullable=true)
     */
    protected $class;

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="string", length=127, nullable=true)
     */
    protected $function;

    /**
     * @var string
     *
     * @ORM\Column(name="event", type="string", length=255, nullable=true)
     */
    protected $event;

    /**
     * @var string
     *
     * @ORM\Column(name="option", type="text", length=65535, nullable=true)
     */
    protected $option;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    protected $_changed = false;

    /**
     * @param $_event
     * @param $_value
     * @param null $_datetime
     * @throws \Exception
     */
    public function run($_event, $_value, $_datetime = null)
    {
        $option = array();
        if (count($this->getOption()) > 0) {
            $option = $this->getOption();
        }
        if (isset($option['background']) && $option['background'] == false) {
            $this->execute($_event, $_value, $_datetime);
        } else {
            $cmd = NEXTDOM_ROOT . '/src/Api/start_listener.php';
            $cmd .= ' listener_id=' . $this->getId() . ' event_id=' . $_event . ' "value=' . escapeshellarg($_value) . '"';
            if ($_datetime !== null) {
                $cmd .= ' "datetime=' . escapeshellarg($_datetime) . '"';
            }
            SystemHelper::php($cmd . ' >> ' . LogHelper::getPathToLog('listener_execution') . ' 2>&1 &');
        }
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getOption($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->option, $_key, $_default);
    }

    /**
     * @param $_key
     * @param string $_value
     * @return $this
     */
    public function setOption($_key, $_value = '')
    {
        $option = Utils::setJsonAttr($this->option, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->option, $option);
        $this->option = $option;
        return $this;
    }

    /**
     * @param $_event
     * @param $_value
     * @param string $_datetime
     */
    public function execute($_event, $_value, $_datetime = '')
    {
        try {
            $option = array();
            if (count($this->getOption()) > 0) {
                $option = $this->getOption();
            }
            $option['event_id'] = $_event;
            $option['value'] = $_value;
            $option['datetime'] = $_datetime;
            $option['listener_id'] = $this->getId();
            if ($this->getClass() != '') {
                $class = $this->getClass();
                $function = $this->getFunction();
                if (class_exists($class) && method_exists($class, $function)) {
                    $class::$function($option);
                } else {
                    LogHelper::add('listener', 'debug', __('[Erreur] Classe ou fonction non trouvée ') . $this->getName());
                    $this->remove();
                    return;
                }
            } else {
                $function = $this->getFunction();
                if (function_exists($function)) {
                    $function($option);
                } else {
                    LogHelper::addError('listener', __('[Erreur] Non trouvée ') . $this->getName());
                    return;
                }
            }
        } catch (\Exception $e) {
            LogHelper::add(init('plugin_id', 'plugin'), 'error', $e->getMessage());
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $_id
     * @return $this
     */
    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
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
     * @param $_class
     * @return $this
     */
    public function setClass($_class)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->class, $_class);
        $this->class = $_class;
        return $this;
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
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
    public function getName()
    {
        if ($this->getClass() != '') {
            return $this->getClass() . '::' . $this->getFunction() . '()';
        }
        return $this->getFunction() . '()';
    }

    /**
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function remove()
    {
        return DBHelper::remove($this);
    }

    public function preSave()
    {
        if ($this->getFunction() == '') {
            throw new \Exception(__('La fonction ne peut pas être vide'));
        }
    }

    /**
     * @param bool $_once
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function save($_once = false)
    {
        if ($_once) {
            ListenerManager::removeByClassFunctionAndEvent($this->getClass(), $this->getFunction(), $this->event, $this->getOption());
        }
        DBHelper::save($this);
        return true;
    }

    public function emptyEvent()
    {
        $this->event = array();
    }

    /**
     * @param $_id
     * @param string $_type
     */
    public function addEvent($_id, $_type = 'cmd')
    {
        $event = $this->getEvent();
        if (!is_array($event)) {
            $event = array();
        }
        $id = '';
        if ($_type == 'cmd') {
            $id = str_replace('#', '', $_id);
        }
        if (!in_array('#' . $id . '#', $event)) {
            $event[] = '#' . $id . '#';
        }
        $this->setEvent($event);
    }

    /**
     * @return bool|mixed|null
     */
    public function getEvent()
    {
        return Utils::isJson($this->event, array());
    }

    /**
     * @param $_event
     * @return $this
     */
    public function setEvent($_event)
    {
        $event = json_encode($_event, JSON_UNESCAPED_UNICODE);
        $this->_changed = Utils::attrChanged($this->_changed, $this->event, $_event);
        $this->event = $event;
        return $this;
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

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'listener';
    }

}
