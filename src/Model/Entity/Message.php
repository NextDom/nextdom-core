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

use NextDom\Enums\NextDomObj;
use NextDom\Helpers\DBHelper;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\EventManager;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\LogicalIdEntity;

/**
 * Message
 *
 * @ORM\Table(name="message", indexes={@ORM\Index(name="plugin_logicalID", columns={"plugin", "logicalId"})})
 * @ORM\Entity
 */
class Message extends BaseEntity
{
    const CLASS_NAME = Message::class;
    const DB_CLASS_NAME = '`message`';
    const TABLE_NAME = NextDomObj::MESSAGE;

    use LogicalIdEntity;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    protected $date;

    /**
     * @var string
     *
     * @ORM\Column(name="plugin", type="string", length=127, nullable=false)
     */
    protected $plugin;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", length=65535, nullable=true)
     */
    protected $message;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="text", length=65535, nullable=true)
     */
    protected $action;

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
     * @param bool $_writeMessage
     * @return bool|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function save($_writeMessage = true)
    {
        if ($this->getMessage() == '') {
            return null;
        }
        if ($this->getLogicalId() == '') {
            $this->setLogicalId($this->getPlugin() . '::' . ConfigManager::genKey());
            $values = [
                'message' => $this->getMessage(),
                'plugin' => $this->getPlugin(),
            ];
            $sql = 'SELECT count(*)
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE plugin = :plugin
                    AND message = :message';
            $result = DBHelper::getOne($sql, $values);
        } else {
            $values = [
                'logicalId' => $this->getLogicalId(),
                'plugin' => $this->getPlugin(),
            ];
            $sql = 'SELECT count(*)
            FROM message
            WHERE plugin=:plugin
            AND logicalId=:logicalId';
            $result = DBHelper::getOne($sql, $values);
        }
        if ($result['count(*)'] != 0) {
            return null;
        }
        EventManager::add('notify', ['title' => __('Message de ') . $this->getPlugin(), 'message' => $this->getMessage(), 'category' => 'message']);
        if ($_writeMessage) {
            DBHelper::save($this);
            $params = [
                '#plugin#' => $this->getPlugin(),
                '#message#' => $this->getMessage(),
            ];
            $actions = ConfigManager::byKey('actionOnMessage');
            if (is_array($actions) && count($actions) > 0) {
                foreach ($actions as $action) {
                    $options = [];
                    if (isset($action['options'])) {
                        $options = $action['options'];
                    }
                    foreach ($options as &$value) {
                        $value = str_replace(array_keys($params), $params, $value);
                    }
                    ScenarioExpressionManager::createAndExec('action', $action['cmd'], $options);
                }
            }
            EventManager::add('message::refreshMessageNumber');
        }
        return true;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /*     * **********************Getteur Setteur*************************** */

    /**
     * @param $_message
     * @return $this
     */
    public function setMessage($_message)
    {
        $this->updateChangeState($this->message, $_message);
        $this->message = $_message;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @param $_plugin
     * @return $this
     */
    public function setPlugin($_plugin)
    {
        $this->updateChangeState($this->plugin, $_plugin);
        $this->plugin = $_plugin;
        return $this;
    }

    public function remove()
    {
        EventManager::add('message::refreshMessageNumber');
        return parent::remove();
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
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param $_date
     * @return $this
     */
    public function setDate($_date)
    {
        $this->updateChangeState($this->date, $_date);
        $this->date = $_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param $_action
     * @return $this
     */
    public function setAction($_action)
    {
        $this->updateChangeState($this->action, $_action);
        $this->action = $_action;
        return $this;
    }
}
