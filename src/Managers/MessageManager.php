<?php
/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

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

namespace NextDom\Managers;

use NextDom\Helpers\DBHelper;
use NextDom\Helpers\Utils;
use NextDom\Model\Entity\Message;

require_once NEXTDOM_ROOT . '/core/class/cache.class.php';

/**
 * Class MessageManager
 * @package NextDom\Managers
 */
class MessageManager
{
    /**
     *
     */
    const CLASS_NAME = Message::class;
    /**
     *
     */
    const DB_CLASS_NAME = '`message`';

    /**
     *
     * @param string $_type
     * @param string $_message
     * @param string $_action
     * @param mixed $_logicalId
     * @param bool $_writeMessage
     */
    public static function add($_type, $_message, $_action = '', $_logicalId = '', $_writeMessage = true)
    {
        $message = (new message())
            ->setPlugin(Utils::secureXSS($_type))
            ->setMessage(Utils::secureXSS($_message))
            ->setAction(Utils::secureXSS($_action))
            ->setDate(date('Y-m-d H:i:s'))
            ->setLogicalId(Utils::secureXSS($_logicalId));
        $message->save($_writeMessage);
    }

    /**
     * @param string $_plugin
     * @param string $_logicalId
     * @param bool $_search
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function removeAll($_plugin = '', $_logicalId = '', $_search = false)
    {
        $values = array();
        $sql = 'DELETE FROM ' . self::DB_CLASS_NAME;
        if ($_plugin != '') {
            $values['plugin'] = $_plugin;
            $sql .= ' WHERE plugin=:plugin';
            if ($_logicalId != '') {
                if ($_search) {
                    $values['logicalId'] = '%' . $_logicalId . '%';
                    $sql .= ' AND logicalId LIKE :logicalId';
                } else {
                    $values['logicalId'] = $_logicalId;
                    $sql .= ' AND logicalId=:logicalId';
                }
            }
        }
        DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW);
        EventManager::add('message::refreshMessageNumber');
        return true;
    }

    /**
     * @return mixed
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function nbMessage()
    {
        $sql = 'SELECT count(*)
        FROM ' . self::DB_CLASS_NAME;
        $count = DBHelper::Prepare($sql, array(), DBHelper::FETCH_TYPE_ROW);
        return $count['count(*)'];
    }

    /**
     * @param $_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byId($_id)
    {
        $values = array(
            'id' => $_id,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE id=:id';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_plugin
     * @param $_logicalId
     * @return Message[]|null
     * @throws \Exception
     */
    public static function byPluginLogicalId($_plugin, $_logicalId)
    {
        $values = array(
            'logicalId' => $_logicalId,
            'plugin' => $_plugin,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE logicalId=:logicalId
        AND plugin=:plugin';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_plugin
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byPlugin($_plugin)
    {
        $values = array(
            'plugin' => $_plugin,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE plugin=:plugin
        ORDER BY date DESC';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function listPlugin()
    {
        $sql = 'SELECT DISTINCT(plugin)
        FROM ' . self::DB_CLASS_NAME;
        return DBHelper::Prepare($sql, array(), DBHelper::FETCH_TYPE_ALL);
    }

    /**
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function all()
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        ORDER BY date DESC
        LIMIT 500';
        return DBHelper::Prepare($sql, array(), DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }
}
