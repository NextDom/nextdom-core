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

class UpdateManager
{
    const DB_CLASS_NAME = 'update';
    const CLASS_NAME    = 'update';

    /**
     * Check all updates
     * @param string $filter
     * @param bool $findNewObjects
     */
    public static function checkAllUpdate($filter = '', $findNewObjects = true)
    {
        $findCore = false;
        if ($findNewObjects) {
            self::findNewUpdateObject();
        }
        $updatesList = self::all($filter);
        $updates_sources = array();
        if (is_array($updatesList)) {
            foreach ($updatesList as $update) {
                if ($update->getType() == 'core') {
                    if ($findCore) {
                        $update->remove();
                        continue;
                    }
                    $findCore = true;
                    $update->setType('core')
                        ->setLogicalId('nextdom')
                        ->setSource(\config::byKey('core::repo::provider'))
                        ->setLocalVersion(\nextdom::version());
                    $update->save();
                    $update->checkUpdate();
                } else {
                    if ($update->getStatus() != 'hold') {
                        if (!isset($updates_sources[$update->getSource()])) {
                            $updates_sources[$update->getSource()] = array();
                        }
                        $updates_sources[$update->getSource()][] = $update;
                    }
                }
            }
        }
        if (!$findCore && ($filter == '' || $filter == 'core')) {
            $update = (new \update())
                ->setType('core')
                ->setLogicalId('nextdom')
                ->setSource(\config::byKey('core::repo::provider'))
                ->setLocalVersion(\nextdom::version());
            $update->save();
            $update->checkUpdate();
        }
        foreach ($updates_sources as $source => $updates) {
            $class = 'repo_' . $source;
            if (class_exists($class) && method_exists($class, 'checkUpdate') && \config::byKey($source . '::enable') == 1) {
                $class::checkUpdate($updates);
            }
        }
        \config::save('update::lastCheck', date('Y-m-d H:i:s'));
    }

    /**
     * List of rest (Source of downloads)
     * @return array
     */
    public static function listRepo(): array
    {
        $result = array();
        foreach (\ls(NEXTDOM_ROOT . '/core/repo', '*.repo.php') as $repoFile) {
            if (substr_count($repoFile, '.') != 2) {
                continue;
            }

            $class = 'repo_' . str_replace('.repo.php', '', $repoFile);
            $result[str_replace('.repo.php', '', $repoFile)] = array(
                'name'          => $class::$_name,
                'class'         => $class,
                'configuration' => $class::$_configuration,
                'scope'         => $class::$_scope,
            );
            $result[str_replace('.repo.php', '', $repoFile)]['enable'] = \config::byKey(str_replace('.repo.php', '', $repoFile) . '::enable');
        }
        return $result;
    }

    /**
     * Get a repo by its identifier
     * @param $id Repo identifier
     * @return array
     */
    public static function repoById($id)
    {
        $class = 'repo_' . $id;
        $return = array(
            'name'          => $class::$_name,
            'class'         => $class,
            'configuration' => $class::$_configuration,
            'scope'         => $class::$_scope,
        );
        $return['enable'] = \config::byKey($id . '::enable');
        return $return;
    }

    /**
     * Update all items
     * @param string $filter
     * @return bool
     */
    public static function updateAll(string $filter = '')
    {
        //TODO: Il n'a pas l'air de servir à grand chose ce test
        if ($filter == 'core') {
            foreach (self::byType($filter) as $update) {
                $update->doUpdate();
            }
        } else {
            $error = false;
            if ($filter == '') {
                $updates = self::all();
            } else {
                $updates = self::byType($filter);
            }
            if (is_array($updates)) {
                foreach ($updates as $update) {
                    if ($update->getStatus() != 'hold' && $update->getStatus() == 'update' && $update->getType() != 'core') {
                        try {
                            $update->doUpdate();
                        } catch (\Exception $e) {
                            \log::add('update', 'update', $e->getMessage());
                            $error = true;
                        } catch (\Error $e) {
                            \log::add('update', 'update', $e->getMessage());
                            $error = true;
                        }
                    }
                }
            }
            return $error;
        }
    }

    /**
     * Get information about an update from its username
     * @param $id ID of the update
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function byId($id)
    {
        $values = array(
            'id' => $id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE id=:id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Get updates from their status
     * @param $status
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function byStatus($status)
    {
        $values = array(
            'status' => $status,
        );
        $sql = 'SELECT ' . \DB::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE status=:status';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Get the bets from its logical identifier
     * @param $logicalId
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function byLogicalId($logicalId)
    {
        $values = array(
            'logicalId' => $logicalId,
        );
        $sql = 'SELECT ' . \DB::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE logicalId=:logicalId';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Obtenir les mises à jour à partir de leur type
     *
     * @param $type
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function byType($type)
    {
        $values = array(
            'type' => $type,
        );
        $sql = 'SELECT ' . \DB::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE type=:type';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Get updates from their type and logicalId
     *
     * @param $type
     * @param $logicalId
     * @return array|mixed|null
     * @throws \Exception
     */
    public static function byTypeAndLogicalId($type, $logicalId)
    {
        $values = array(
            'logicalId' => $logicalId,
            'type'      => $type,
        );
        $sql = 'SELECT ' . \DB::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE logicalId=:logicalId
                AND type=:type';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Get all the updates.
     * @param string $filter
     * @return array|null List of all objects
     * @throws \Exception
     */
    public static function all($filter = '')
    {
        $values = array();
        $sql = 'SELECT ' . \DB::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '` ';
        if ($filter != '') {
            $values['type'] = $filter;
            $sql .= 'WHERE `type`=:type ';
        }
        $sql .= 'ORDER BY FIELD( `status`, "update","ok","depreciated") ASC,FIELD( `type`,"plugin","core") DESC, `name` ASC';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Get the number of pending updates
     * @return mixed
     * @throws \Exception
     */
    public static function nbNeedUpdate()
    {
        $sql = 'SELECT count(*)
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE `status`="update"';
        $result = \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ROW);
        return $result['count(*)'];
    }

    /**
     * Search new updates
     * @throws \Exception
     */
    public static function findNewUpdateObject()
    {
        foreach (PluginManager::listPlugin() as $plugin) {
            $pluginId = $plugin->getId();
            $update = self::byTypeAndLogicalId('plugin', $pluginId);
            if (!is_object($update)) {
                $update = (new \update())
                    ->setLogicalId($pluginId)
                    ->setType('plugin')
                    ->setLocalVersion(date('Y-m-d H:i:s'));
                $update->save();
            }
            $find = array();
            if (method_exists($pluginId, 'listMarketObject')) {
                foreach ($pluginId::listMarketObject() as $logical_id) {
                    $find[$logical_id] = true;
                    $update = self::byTypeAndLogicalId($pluginId, $logical_id);
                    if (!is_object($update)) {
                        $update = (new \update())
                            ->setLogicalId($logical_id)
                            ->setType($pluginId)
                            ->setLocalVersion(date('Y-m-d H:i:s'));
                        $update->save();
                    }
                }
                foreach (self::byType($pluginId) as $update) {
                    if (!isset($find[$update->getLogicalId()])) {
                        $update->remove();
                    }
                }
            } else {
                $values = array(
                    'type' => $pluginId,
                );
                $sql = 'DELETE FROM `' . self::DB_CLASS_NAME . '`
                        WHERE type=:type';
                \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW);
            }
        }
    }

    /**
     * Liste des mises à jour du core.
     *
     * @return array
     */
    public static function listCoreUpdate()
    {
        return \ls(NEXTDOM_ROOT . '/install/update', '*');
    }
}