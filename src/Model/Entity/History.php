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
use NextDom\Helpers\Utils;
use NextDom\Managers\CmdManager;
use NextDom\Managers\HistoryManager;

/**
 * History
 *
 * @ORM\Table(name="history", uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"datetime", "cmd_id"})}, indexes={@ORM\Index(name="fk_history5min_commands1_idx", columns={"cmd_id"})})
 * @ORM\Entity
 */
class History
{
    /**
     * @var string
     *
     * @ORM\Column(name="datetime", type="datetime", nullable=false)
     */
    protected $datetime;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=127, nullable=true)
     */
    protected $value;

    /**
     * @var \NextDom\Model\Entity\Cmd
     *
     * @ORM\ManyToOne(targetEntity="NextDom\Model\Entity\Cmd")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cmd_id", referencedColumnName="id")
     * })
     */
    protected $cmd_id;
    protected $_tableName = 'history';
    protected $_changed = false;

    public function save($_cmd = null, $_direct = false)
    {
        global $NEXTDOM_INTERNAL_CONFIG;
        if ($_cmd === null) {
            $cmd = $this->getCmd();
            if (!is_object($cmd)) {
                HistoryManager::emptyHistory($this->getCmd_id());
                return;
            }
        } else {
            $cmd = $_cmd;
        }
        if (empty($this->getDatetime())) {
            $this->setDatetime(date('Y-m-d H:i:s'));
        }
        if (!empty($cmd->getConfiguration('historizeRound')) && is_numeric($cmd->getConfiguration('historizeRound')) && $cmd->getConfiguration('historizeRound') >= 0 && !empty($this->getValue())) {
            $this->setValue(round($this->getValue(), $cmd->getConfiguration('historizeRound')));
        }
        if ($NEXTDOM_INTERNAL_CONFIG['cmd']['type']['info']['subtype'][$cmd->getSubType()]['isHistorized']['canBeSmooth'] && $cmd->getConfiguration('historizeMode', 'avg') != 'none' && $this->getValue() !== null && $_direct === false) {
            if ($this->getTableName() == 'history') {
                $time = strtotime($this->getDatetime());
                $time -= $time % 300;
                $this->setDatetime(date('Y-m-d H:i:s', $time));
                if ($this->getValue() === 0) {
                    $values = array(
                        'cmd_id' => $this->getCmd_id(),
                        'datetime' => date('Y-m-d H:i:00', strtotime($this->getDatetime()) + 300),
                        'value' => $this->getValue(),
                    );
                    $sql = 'REPLACE INTO history
                    SET cmd_id=:cmd_id,
                    `datetime`=:datetime,
                    value=:value';
                    DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW);
                    return;
                }
                $values = array(
                    'cmd_id' => $this->getCmd_id(),
                    'datetime' => $this->getDatetime(),
                );
                $sql = 'SELECT `value`
                FROM history
                WHERE cmd_id=:cmd_id
                AND `datetime`=:datetime';
                $result = DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW);
                if ($result !== false) {
                    switch ($cmd->getConfiguration('historizeMode', 'avg')) {
                        case 'avg':
                            $this->setValue(($result['value'] + $this->getValue()) / 2);
                            break;
                        case 'min':
                            $this->setValue(min($result['value'], $this->getValue()));
                            break;
                        case 'max':
                            $this->setValue(max($result['value'], $this->getValue()));
                            break;
                    }
                    if ($result['value'] === $this->getValue()) {
                        return;
                    }
                }
            } else {
                $this->setDatetime(date('Y-m-d H:00:00', strtotime($this->getDatetime())));
            }
        }
        $values = array(
            'cmd_id' => $this->getCmd_id(),
            'datetime' => $this->getDatetime(),
            'value' => $this->getValue(),
        );
        if (empty($values['value'])) {
            $values['value'] = null;
        }
        $sql = 'REPLACE INTO ' . $this->getTableName() . '
        SET cmd_id=:cmd_id,
        `datetime`=:datetime,
        value=:value';
        DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW);
    }

    public function remove()
    {
        DBHelper::remove($this);
    }

    public function getCmd_id()
    {
        return $this->cmd_id;
    }

    public function getCmd()
    {
        return CmdManager::byId($this->cmd_id);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }

    public function getTableName()
    {
        return $this->_tableName;
    }

    public function setTableName($_tableName)
    {
        $this->_tableName = $_tableName;
        return $this;
    }

    public function setCmd_id($cmd_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->cmd_id, $cmd_id);
        $this->cmd_id = $cmd_id;
        return $this;
    }

    public function setValue($value)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->value, $value);
        $this->value = $value;
        return $this;
    }

    public function setDatetime($datetime)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->datetime, $datetime);
        $this->datetime = $datetime;
        return $this;
    }

    public function getChanged()
    {
        return $this->_changed;
    }

    public function setChanged($_changed)
    {
        $this->_changed = $_changed;
        return $this;
    }
}
