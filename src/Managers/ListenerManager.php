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
use NextDom\Helpers\SystemHelper;
use NextDom\Model\Entity\Listener;

require_once NEXTDOM_ROOT . '/core/class/cache.class.php';

/**
 * Class ListenerManager
 * @package NextDom\Managers
 */
class ListenerManager
{

    const CLASS_NAME = Listener::class;
    const DB_CLASS_NAME = '`listener`';

    /**
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function all()
    {
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME;
        return DBHelper::Prepare($sql, array(), DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byId($_id)
    {
        $value = array(
            'id' => $_id,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE id=:id';
        return DBHelper::Prepare($sql, $value, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_class
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byClass($_class)
    {
        $value = array(
            'class' => $_class,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE class=:class';
        return DBHelper::Prepare($sql, $value, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_class
     * @param $_function
     * @param string $_option
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byClassAndFunction($_class, $_function, $_option = '')
    {
        $value = array(
            'class' => $_class,
            'function' => $_function,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE class=:class
        AND function=:function';
        if ($_option != '') {
            $_option = json_encode($_option, JSON_UNESCAPED_UNICODE);
            $value['option'] = $_option;
            $sql .= ' AND `option`=:option';
        }
        return DBHelper::Prepare($sql, $value, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_class
     * @param $_function
     * @param string $_option
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function searchClassFunctionOption($_class, $_function, $_option = '')
    {
        $value = array(
            'class' => $_class,
            'function' => $_function,
            'option' => '%' . $_option . '%',
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE class=:class
        AND function=:function
        AND `option` LIKE :option';
        return DBHelper::Prepare($sql, $value, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_class
     * @param $_function
     * @param $_event
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byClassFunctionAndEvent($_class, $_function, $_event)
    {
        $value = array(
            'class' => $_class,
            'function' => $_function,
            'event' => $_event,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE class=:class
        AND function=:function
        AND event=:event';
        return DBHelper::Prepare($sql, $value, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_class
     * @param $_function
     * @param $_event
     * @param string $_option
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function removeByClassFunctionAndEvent($_class, $_function, $_event, $_option = '')
    {
        $value = array(
            'class' => $_class,
            'function' => $_function,
            'event' => $_event,
        );
        $sql = 'DELETE FROM ' . self::DB_CLASS_NAME . '
        WHERE class=:class
        AND function=:function
        AND event=:event';
        if ($_option != '') {
            $_option = json_encode($_option, JSON_UNESCAPED_UNICODE);
            $value['option'] = $_option;
            $sql .= ' AND `option`=:option';
        }
        DBHelper::Prepare($sql, $value, DBHelper::FETCH_TYPE_ROW);
    }

    /**
     * @param $_event
     * @param $_value
     * @param $_datetime
     * @throws \Exception
     */
    public static function check($_event, $_value, $_datetime = null)
    {
        $listeners = self::searchEvent($_event);
        if (count($listeners) > 0) {
            foreach ($listeners as $listener) {
                $listener->run(str_replace('#', '', $_event), $_value, $_datetime);
            }
        }
    }

    /**
     * @param $_event
     * @return Listener[]|null
     * @throws \Exception
     */
    public static function searchEvent($_event)
    {
        if (strpos($_event, '#') !== false) {
            $value = array(
                'event' => '%' . $_event . '%',
            );
        } else {
            $value = array(
                'event' => '%#' . $_event . '#%',
            );
        }
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE `event` LIKE :event';
        return DBHelper::Prepare($sql, $value, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param $_event
     * @throws \Exception
     */
    public static function backgroundCalculDependencyCmd($_event)
    {
        if (count(CmdManager::byValue($_event, 'info')) == 0) {
            return;
        }
        $cmd = NEXTDOM_ROOT . '/src/Api/start_listener.php';
        $cmd .= ' event_id=' . $_event;
        SystemHelper::php($cmd . ' >> /dev/null 2>&1 &');
    }
}
