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

use NextDom\Enums\Common;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Managers\Parents\BaseManager;
use NextDom\Managers\Parents\CommonManager;
use NextDom\Model\Entity\Listener;

require_once NEXTDOM_ROOT . '/core/class/cache.class.php';

/**
 * Class ListenerManager
 * @package NextDom\Managers
 */
class ListenerManager extends BaseManager
{
    use CommonManager;

    const CLASS_NAME = Listener::class;
    const DB_CLASS_NAME = '`listener`';

    /**
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function all()
    {
        return static::getAll();
    }

    /**
     * @param $_class
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byClass($_class)
    {
        return static::getMultipleByClauses([Common::CLASS => $_class]);
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
        $clauses = [
            Common::CLASS_CODE => $_class,
            Common::FUNCTION => $_function,
        ];
        if ($_option != '') {
            $_option = json_encode($_option, JSON_UNESCAPED_UNICODE);
            $clauses[Common::OPTION] = $_option;
        }
        return static::getOneByClauses($clauses);
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
        $value = [
            Common::CLASS_CODE => $_class,
            Common::FUNCTION => $_function,
            Common::OPTION => '%' . $_option . '%',
        ];
        $sql = static::getBaseSQL() . '
                WHERE `class` = :class
                AND `function` = :function
                AND `option` LIKE :option';
        return DBHelper::getAllObjects($sql, $value, self::CLASS_NAME);
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
        return static::getMultipleByClauses([
            Common::CLASS_CODE => $_class,
            Common::FUNCTION => $_function,
            Common::EVENT => $_event
        ]);
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
        $value = [
            Common::CLASS_CODE => $_class,
            Common::FUNCTION => $_function,
            Common::EVENT => $_event,
        ];
        $sql = 'DELETE FROM ' . self::DB_CLASS_NAME . '
                WHERE `class` = :class
                AND `function` = :function
                AND `event` = :event';
        if ($_option != '') {
            $_option = json_encode($_option, JSON_UNESCAPED_UNICODE);
            $value[Common::OPTION] = $_option;
            $sql .= ' AND `option`=:option';
        }
        DBHelper::exec($sql, $value);
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
            $clauses = [Common::EVENT => '%' . $_event . '%'];
        } else {
            $clauses = [Common::EVENT => '%#' . $_event . '#%'];
        }
        return static::searchMultipleByClauses($clauses);
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
