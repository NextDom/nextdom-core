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

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Managers;

use NextDom\Model\Entity\Plan3d;

class Plan3dManager
{
    const CLASS_NAME = Plan3d::class;
    const DB_CLASS_NAME = '`plan3d`';

    public static function byId($_id)
    {
        $values = array(
            'id' => $_id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE id=:id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function byPlan3dHeaderId($_plan3dHeader_id)
    {
        $values = array(
            'plan3dHeader_id' => $_plan3dHeader_id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE plan3dHeader_id=:plan3dHeader_id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function byLinkTypeLinkId($_link_type, $_link_id)
    {
        $values = array(
            'link_type' => $_link_type,
            'link_id' => $_link_id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE link_type=:link_type
        AND link_id=:link_id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function byName3dHeaderId($_name, $_plan3dHeader_id)
    {
        $values = array(
            'name' => $_name,
            'plan3dHeader_id' => $_plan3dHeader_id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE name=:name
        AND plan3dHeader_id=:plan3dHeader_id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function byLinkTypeLinkId3dHeaderId($_link_type, $_link_id, $_plan3dHeader_id)
    {
        $values = array(
            'link_type' => $_link_type,
            'link_id' => $_link_id,
            'plan3dHeader_id' => $_plan3dHeader_id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE link_type=:link_type
        AND link_id=:link_id
        AND plan3dHeader_id=:plan3dHeader_id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function removeByLinkTypeLinkId3dHeaderId($_link_type, $_link_id, $_plan3dHeader_id)
    {
        $values = array(
            'link_type' => $_link_type,
            'link_id' => $_link_id,
            'plan3dHeader_id' => $_plan3dHeader_id,
        );
        $sql = 'DELETE FROM ' . self::DB_CLASS_NAME . '
        WHERE link_type=:link_type
        AND link_id=:link_id
        AND plan3dHeader_id=:plan3dHeader_id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function all()
    {
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME;
        return \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function searchByDisplay($_search)
    {
        $value = array(
            'search' => '%' . $_search . '%',
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE display LIKE :search';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function searchByConfiguration($_search, $_not = '')
    {
        $value = array(
            'search' => '%' . $_search . '%',
            'not' => $_not,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
        FROM ' . self::DB_CLASS_NAME . '
        WHERE configuration LIKE :search
        AND link_type !=:not';
        return \DB::Prepare($sql, $value, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }
}
