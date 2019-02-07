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

use NextDom\Helpers\SystemHelper;
use NextDom\Model\Entity\Listener;

require_once NEXTDOM_ROOT . '/core/class/cache.class.php';

class ListenerManager
{

    const CLASS_NAME = Listener::class;
    const DB_CLASS_NAME = '`listener`';

    public static function all()
    {
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::CLASS_NAME;
        return \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function byId($_id)
    {
        $value = array(
            'id' => $_id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::CLASS_NAME . '
        WHERE id=:id';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function byClass($_class)
    {
        $value = array(
            'class' => $_class,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::CLASS_NAME . '
        WHERE class=:class';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function byClassAndFunction($_class, $_function, $_option = '')
    {
        $value = array(
            'class' => $_class,
            'function' => $_function,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::CLASS_NAME . '
        WHERE class=:class
        AND function=:function';
        if ($_option != '') {
            $_option = json_encode($_option, JSON_UNESCAPED_UNICODE);
            $value['option'] = $_option;
            $sql .= ' AND `option`=:option';
        }
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function searchClassFunctionOption($_class, $_function, $_option = '')
    {
        $value = array(
            'class' => $_class,
            'function' => $_function,
            'option' => '%' . $_option . '%',
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::CLASS_NAME . '
        WHERE class=:class
        AND function=:function
        AND `option` LIKE :option';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function byClassFunctionAndEvent($_class, $_function, $_event)
    {
        $value = array(
            'class' => $_class,
            'function' => $_function,
            'event' => $_event,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::CLASS_NAME . '
        WHERE class=:class
        AND function=:function
        AND event=:event';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function removeByClassFunctionAndEvent($_class, $_function, $_event, $_option = '')
    {
        $value = array(
            'class' => $_class,
            'function' => $_function,
            'event' => $_event,
        );
        $sql = 'DELETE FROM ' . self::CLASS_NAME . '
        WHERE class=:class
        AND function=:function
        AND event=:event';
        if ($_option != '') {
            $_option = json_encode($_option, JSON_UNESCAPED_UNICODE);
            $value['option'] = $_option;
            $sql .= ' AND `option`=:option';
        }
        \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ROW);
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
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::CLASS_NAME . '
        WHERE `event` LIKE :event';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function check($_event, $_value, $_datetime)
    {
        $listeners = self::searchEvent($_event);
        if (count($listeners) > 0) {
            foreach ($listeners as $listener) {
                $listener->run(str_replace('#', '', $_event), $_value, $_datetime);
            }
        }
    }

    public static function backgroundCalculDependencyCmd($_event)
    {
        if (count(CmdManager::byValue($_event, 'info')) == 0) {
            return;
        }
        $cmd = __DIR__ . '/../php/jeeListener.php';
        $cmd .= ' event_id=' . $_event;
        SystemHelper::php($cmd . ' >> /dev/null 2>&1 &');
    }
}
