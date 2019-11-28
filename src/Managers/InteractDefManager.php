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
use NextDom\Model\Entity\InteractDef;

require_once NEXTDOM_ROOT . '/core/class/cache.class.php';

/**
 * Class InteractDefManager
 * @package NextDom\Managers
 */
class InteractDefManager
{
    const CLASS_NAME = InteractDef::class;
    const DB_CLASS_NAME = '`interactDef`';

    /**
     * @param $_id
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function byId($_id)
    {
        $values = [
            'id' => $_id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return DBHelper::getOneObject($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param null $_group
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function listGroup($_group = null)
    {
        $values = [];
        $sql = 'SELECT DISTINCT(`group`)
                FROM ' . self::DB_CLASS_NAME;
        if ($_group !== null) {
            $values['group'] = '%' . $_group . '%';
            $sql .= ' WHERE `group` LIKE :group';
        }
        $sql .= ' ORDER BY `group`';
        return DBHelper::getAll($sql, $values);
    }

    /**
     * @param $_text
     * @return array
     */
    public static function generateTextVariant($_text)
    {
        $return = [];
        preg_match_all("/(\[.*?\])/", $_text, $words);
        if (count($words[1]) == 0) {
            $return[] = $_text;
        } else {
            $math = $words[1][0];
            $words = str_replace('[', '', $math);
            $words = str_replace(']', '', $words);
            $words = explode('|', $words);
            $textBefore = substr($_text, 0, strpos($_text, $math));
            foreach (self::generateTextVariant(substr($_text, strpos($_text, $math) + strlen($math))) as $remainsText) {
                foreach ($words as $word) {
                    $return[] = $textBefore . $word . $remainsText;
                }
            }
        }
        return $return;
    }

    /**
     * @param $_query
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function searchByQuery($_query)
    {
        $values = [
            'query' => '%' . $_query . '%',
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE query LIKE :query';
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    public static function regenerateInteract()
    {
        foreach (self::all() as $interactDef) {
            $interactDef->save();
        }
    }

    /**
     * @param string $_group
     * @return InteractDef[]|null
     * @throws \Exception
     */
    public static function all($_group = '')
    {
        $values = [];
        if ($_group === '') {
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    ORDER BY name, query';
        } else if ($_group === null) {
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE (`group` IS NULL OR `group` = "")
                    ORDER BY name, query';
        } else {
            $values['group'] = $_group;
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE `group` = :group
                    ORDER BY name, query';
        }
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param $_def
     * @param $_query
     * @return array
     */
    public static function getTagFromQuery($_def, $_query)
    {
        $_def = self::sanitizeQuery(trim($_def));
        $_query = self::sanitizeQuery(trim($_query));
        $options = [];
        $regexp = preg_quote(strtolower($_def));
        preg_match_all("/#(.*?)#/", $_def, $tags);
        if (count($tags[1]) > 0) {
            foreach ($tags[1] as $match) {
                $regexp = str_replace(preg_quote('#' . $match . '#'), '(.*?)', $regexp);
            }
            preg_match_all("/" . $regexp . "$/", strtolower($_query), $matches, PREG_SET_ORDER);
            if (isset($matches[0])) {
                $countTags = count($tags[1]);
                for ($i = 0; $i < $countTags; $i++) {
                    if (isset($matches[0][$i + 1])) {
                        $options['#' . $tags[1][$i] . '#'] = $matches[0][$i + 1];
                    }
                }
            }
        }
        foreach ($tags[1] as $match) {
            if (!isset($options['#' . $match . '#'])) {
                $options['#' . $match . '#'] = '';
            }
        }
        return $options;
    }

    /**
     * @param $_query
     * @return mixed|null|string|string[]
     */
    public static function sanitizeQuery($_query)
    {
        $_query = str_replace(["\'"], ["'"], $_query);
        $_query = preg_replace('/\s+/', ' ', $_query);
        $_query = ucfirst(strtolower($_query));
        $_query = strtolower(Utils::sanitizeAccent($_query));
        return $_query;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function deadCmd()
    {
        $return = [];
        foreach (self::all() as $interact) {
            if (is_string($interact->getActions('cmd')) && $interact->getActions('cmd') != '') {
                preg_match_all("/#([0-9]*)#/", $interact->getActions('cmd'), $matches);
                foreach ($matches[1] as $cmd_id) {
                    if (is_numeric($cmd_id)) {
                        if (!CmdManager::byId(str_replace('#', '', $cmd_id))) {
                            $return[] = ['detail' => 'Interaction ' . $interact->getName() . ' du groupe ' . $interact->getGroup(), 'help' => 'Action', 'who' => '#' . $cmd_id . '#'];
                        }
                    }
                }
            }
            if (is_string($interact->getReply()) && $interact->getReply() != '') {
                preg_match_all("/#([0-9]*)#/", $interact->getReply(), $matches);
                foreach ($matches[1] as $cmd_id) {
                    if (is_numeric($cmd_id)) {
                        if (!CmdManager::byId(str_replace('#', '', $cmd_id))) {
                            $return[] = ['detail' => 'Interaction ' . $interact->getName() . ' du groupe ' . $interact->getGroup(), 'help' => 'Réponse', 'who' => '#' . $cmd_id . '#'];
                        }
                    }
                }
            }
        }
        return $return;
    }

    /**
     * @return array|mixed|null
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function cleanInteract()
    {
        $list_id = [];
        foreach (self::all() as $interactDef) {
            $list_id[$interactDef->getId()] = $interactDef->getId();
        }
        if (count($list_id) > 0) {
            $sql = 'DELETE FROM ' . InteractQueryManager::DB_CLASS_NAME . ' WHERE interactDef_id NOT IN (' . implode(',', $list_id) . ')';
            return DBHelper::getOne($sql);
        }
        return null;
    }

    /**
     * @param $searchPattern
     * @return array
     * @throws \Exception
     */
    public static function searchByUse($searchPattern)
    {
        $return = [];
        $interactDefs = self::searchByActionsOrReply($searchPattern);
        $interactQueries = InteractQueryManager::searchActions($searchPattern);
        foreach ($interactQueries as $interactQuery) {
            $interactDefs[] = $interactQuery->getInteractDef();
        }
        foreach ($interactDefs as $interactDef) {
            if (!isset($return[$interactDef->getId()])) {
                $return[$interactDef->getId()] = $interactDef;
            }
        }
        return $return;
    }

    /**
     * @param string $searchPattern
     * @return InteractDef[]|null
     * @throws \Exception
     */
    private static function searchByActionsOrReply($searchPattern)
    {
        if (!is_array($searchPattern)) {
            $values = [
                'search' => '%' . $searchPattern . '%',
            ];
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE actions LIKE :search
                        OR reply LIKE :search';
        } else {
            $values = [
                'search' => '%' . $searchPattern[0] . '%',
            ];
            $sql = 'SELECT ' . DBHelper::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE actions LIKE :search
                        OR reply LIKE :search';
            for ($i = 1; $i < count($searchPattern); $i++) {
                $values['search' . $i] = '%' . $searchPattern[$i] . '%';
                $sql .= ' OR actions LIKE :search' . $i . '
                          OR reply LIKE :search' . $i;
            }
        }
        return DBHelper::getAllObjects($sql, $values, self::CLASS_NAME);
    }

    /**
     * @param $_text
     * @param $_synonymes
     * @param int $_deep
     * @return array
     */
    public static function generateSynonymeVariante($_text, $_synonymes, $_deep = 0)
    {
        $return = [];
        if (count($_synonymes) == 0) {
            return $return;
        }
        if ($_deep > 10) {
            return $return;
        }
        $_deep++;
        foreach ($_synonymes as $replace => $values) {
            foreach ($values as $value) {
                $result = @preg_replace('/\b' . $replace . '\b/iu', $value, $_text);
                if ($result != $_text) {
                    $synonymes = $_synonymes;
                    unset($synonymes[$replace]);
                    $return = array_merge($return, self::generateSynonymeVariante($result, $synonymes, $_deep));
                    $return[] = $result;
                }
            }
        }
        return $return;
    }
}
