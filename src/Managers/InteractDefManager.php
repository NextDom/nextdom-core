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

use NextDom\Helpers\Utils;

require_once NEXTDOM_ROOT.'/core/class/cache.class.php';

class InteractDefManager {
    const CLASS_NAME = 'interactDef';
    const DB_CLASS_NAME = '`interactDef`';

    public static function byId($_id) {
        $values = array(
            'id' => $_id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE id = :id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * @param string $_group
     * @return \interactDef[]|null
     * @throws \Exception
     */
    public static function all($_group = '') {
        $values = array();
        if ($_group === '') {
            $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    ORDER BY name, query';
        } else if ($_group === null) {
            $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE (`group` IS NULL OR `group` = "")
                    ORDER BY name, query';
        } else {
            $values['group'] = $_group;
            $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE `group` = :group
                    ORDER BY name, query';
        }
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function listGroup($_group = null) {
        $values = array();
        $sql = 'SELECT DISTINCT(`group`)
                FROM ' . self::DB_CLASS_NAME;
        if ($_group !== null) {
            $values['group'] = '%' . $_group . '%';
            $sql .= ' WHERE `group` LIKE :group';
        }
        $sql .= ' ORDER BY `group`';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL);
    }

    public static function generateTextVariant($_text) {
        $return = array();
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

    public static function searchByQuery($_query) {
        $values = array(
            'query' => '%' . $_query . '%',
        );
        $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                FROM ' . self::DB_CLASS_NAME . '
                WHERE query LIKE :query';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function regenerateInteract() {
        foreach (self::all() as $interactDef) {
            $interactDef->save();
        }
    }

    public static function getTagFromQuery($_def, $_query) {
        $_def = self::sanitizeQuery(trim($_def));
        $_query = self::sanitizeQuery(trim($_query));
        $options = array();
        $regexp = preg_quote(strtolower($_def));
        preg_match_all("/#(.*?)#/", $_def, $tags);
        if (count($tags[1]) > 0) {
            foreach ($tags[1] as $match) {
                $regexp = str_replace('#' . $match . '#', '(.*?)', $regexp);
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

    public static function sanitizeQuery($_query) {
        $_query = str_replace(array("\'"), array("'"), $_query);
        $_query = preg_replace('/\s+/', ' ', $_query);
        $_query = ucfirst(strtolower($_query));
        $_query = strtolower(Utils::sanitizeAccent($_query));
        return $_query;
    }

    public static function deadCmd() {
        $return = array();
        foreach (self::all() as $interact) {
            if (is_string($interact->getActions('cmd')) && $interact->getActions('cmd') != '') {
                preg_match_all("/#([0-9]*)#/", $interact->getActions('cmd'), $matches);
                foreach ($matches[1] as $cmd_id) {
                    if (is_numeric($cmd_id)) {
                        if (!CmdManager::byId(str_replace('#', '', $cmd_id))) {
                            $return[] = array('detail' => 'Interaction ' . $interact->getName() . ' du groupe ' . $interact->getGroup(), 'help' => 'Action', 'who' => '#' . $cmd_id . '#');
                        }
                    }
                }
            }
            if (is_string($interact->getReply()) && $interact->getReply() != '') {
                preg_match_all("/#([0-9]*)#/", $interact->getReply(), $matches);
                foreach ($matches[1] as $cmd_id) {
                    if (is_numeric($cmd_id)) {
                        if (!CmdManager::byId(str_replace('#', '', $cmd_id))) {
                            $return[] = array('detail' => 'Interaction ' . $interact->getName() . ' du groupe ' . $interact->getGroup(), 'help' => 'Réponse', 'who' => '#' . $cmd_id . '#');
                        }
                    }
                }
            }
        }
        return $return;
    }

    public static function cleanInteract() {
        $list_id = array();
        foreach (self::all() as $interactDef) {
            $list_id[$interactDef->getId()] = $interactDef->getId();
        }
        if (count($list_id) > 0) {
            $sql = 'DELETE FROM ' . InteractQueryManager::DB_CLASS_NAME . ' WHERE interactDef_id NOT IN (' . implode(',', $list_id) . ')';
            return \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ROW);
        }
        return null;
    }

    /**
     * @param string $searchPattern
     * @return \interactDef[]|null
     * @throws \Exception
     */
    private static function searchByActionsOrReply($searchPattern) {
        if (!is_array($searchPattern)) {
            $values = array(
                'search' => '%' . $searchPattern . '%',
            );
            $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE actions LIKE :search
                        OR reply LIKE :search';
        } else {
            $values = array(
                'search' => '%' . $searchPattern[0] . '%',
            );
            $sql = 'SELECT ' . \DB::buildField(self::CLASS_NAME) . '
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE actions LIKE :search
                        OR reply LIKE :search';
            for ($i = 1; $i < count($searchPattern); $i++) {
                $values['search' . $i] = '%' . $searchPattern[$i] . '%';
                $sql .= ' OR actions LIKE :search' . $i . '
                          OR reply LIKE :search' . $i;
            }
        }
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    public static function searchByUse($searchPattern) {
        $return = array();
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
}
