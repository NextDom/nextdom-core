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

use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Model\Entity\Update;

/**
 * Class UpdateManager
 * @package NextDom\Managers
 */
class UpdateManager
{
    const DB_CLASS_NAME = 'update';
    const CLASS_NAME = Update::class;

    /**
     * Check all updates
     * @param string $filter
     * @param bool $findNewObjects
     * @throws \Exception
     */
    public static function checkAllUpdate($filter = '', $findNewObjects = true)
    {
        if ($findNewObjects) {
            self::findNewUpdateObject();
        }
        $updatesList = self::all($filter);
        $updates_sources = array();
        if (is_array($updatesList)) {
            foreach ($updatesList as $update) {
                if ($update->getStatus() != 'hold') {
                    if (!isset($updates_sources[$update->getSource()])) {
                        $updates_sources[$update->getSource()] = array();
                    }
                    $updates_sources[$update->getSource()][] = $update;
                }
            }
        }
        foreach ($updates_sources as $source => $updates) {
            $class = 'Repo' . $source;
            if (class_exists($class) && method_exists($class, 'checkUpdate') && ConfigManager::byKey($source . '::enable') == 1) {
                $class::checkUpdate($updates);
            }
        }
        ConfigManager::save('update::lastCheck', date('Y-m-d H:i:s'));
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
                $update = (new Update())
                    ->setLogicalId($pluginId)
                    ->setType('plugin')
                    ->setLocalVersion(date('Y-m-d H:i:s'));
                $update->save();
            }
            $find = array();
            if (method_exists($pluginId, 'listMarketObject')) {
                $pluginIdListMarketObject = $pluginId::listMarketObject();
                foreach ($pluginIdListMarketObject as $logical_id) {
                    $find[$logical_id] = true;
                    $update = self::byTypeAndLogicalId($pluginId, $logical_id);
                    if (!is_object($update)) {
                        $update = (new Update())
                            ->setLogicalId($logical_id)
                            ->setType($pluginId)
                            ->setLocalVersion(date('Y-m-d H:i:s'));
                        $update->save();
                    }
                }
                $byTypePluginId = self::byType($pluginId);
                foreach ($byTypePluginId as $update) {
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
                DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW);
            }
        }
    }

    /**
     * Get updates from their type and logicalId
     *
     * @param $type
     * @param $logicalId
     * @return Update|null
     * @throws \Exception
     */
    public static function byTypeAndLogicalId($type, $logicalId)
    {
        $values = array(
            'logicalId' => $logicalId,
            'type' => $type,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE logicalId=:logicalId
                AND type=:type';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Obtenir les mises à jour à partir de leur type
     *
     * @param $type
     * @return Update[]|null
     * @throws \Exception
     */
    public static function byType($type)
    {
        $values = array(
            'type' => $type,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE type=:type';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
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
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '` ';
        if ($filter != '') {
            $values['type'] = $filter;
            $sql .= 'WHERE `type`=:type ';
        }
        $sql .= 'ORDER BY FIELD( `status`, "update","ok","depreciated") ASC,FIELD( `type`,"plugin","core") DESC, `name` ASC';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * List of repositories
     *
     * @return array Repositories data
     *
     * @throws \Exception
     */
    public static function listRepo(): array
    {
        $result = [];
        // Temp hack for specific repo render file
        $repoRenderFiles = ['RepoMarketApi.php', 'RepoMarketDisplay.php', 'RepoMarketList.php', 'RepoMarketSend.php'];
        foreach (FileSystemHelper::ls(NEXTDOM_ROOT . '/src/Repo/', '*.php') as $repoFile) {
            if (!in_array($repoFile, $repoRenderFiles)) {
                $className = str_replace('.php', '', $repoFile);
                $repoCode = strtolower(str_replace('Repo', '', $className));
                $fullNameClass = '\\NextDom\\Repo\\' . $className;
                if (class_exists($fullNameClass)) {
                    $result[$repoCode] = array(
                        'name' => $fullNameClass::$_name,
                        'class' => $fullNameClass,
                        'configuration' => $fullNameClass::$_configuration,
                        'scope' => $fullNameClass::$_scope,
                    );
                    $result[$repoCode]['enable'] = ConfigManager::byKey($repoCode . '::enable');
                }
            }
        }
        return $result;
    }

    /**
     * Get the class of the repo by the name
     * @param string $name Name of the repo in jeedom format
     * @return array Associative array
     */
    public static function getRepoDataFromName($name): array
    {
        $name = str_replace('repo_', '', $name);
        $className = 'Repo' . ucwords($name);
        return [
            'className' => $className,
            'phpClass' => '\\NextDom\\Repo\\' . $className
        ];
    }

    /**
     * Get a repo by its identifier
     * @param string $id Repo identifier
     * @return array
     * @throws \Exception
     */
    public static function repoById($id)
    {
        $repoClassData = self::getRepoDataFromName($id);
        $phpClass = $repoClassData['phpClass'];
        $return = array(
            'name' => $phpClass::$_name,
            'class' => $repoClassData['className'],
            'configuration' => $phpClass::$_configuration,
            'scope' => $phpClass::$_scope,
        );
        $return['enable'] = ConfigManager::byKey($id . '::enable');
        return $return;
    }

    /**
     * Update all items
     * @param string $filter
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Throwable
     */
    public static function updateAll(string $filter = '')
    {
        //TODO: Il n'a pas l'air de servir à grand chose ce test
        $error = false;
        if ($filter == 'core') {
            foreach (self::byType($filter) as $update) {
                $update->doUpdate();
            }
        } else {
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
                            LogHelper::add('update', 'update', $e->getMessage());
                            $error = true;
                        }
                    }
                }
            }
        }
        return $error;
    }

    /**
     * Get information about an update from its username
     * @param string $id ID of the update
     * @return Update|null
     * @throws \Exception
     */
    public static function byId($id)
    {
        $values = array(
            'id' => $id,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE id=:id';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Get updates from their status
     * @param $status
     * @return Update[]
     * @throws \Exception
     */
    public static function byStatus($status)
    {
        $values = array(
            'status' => $status,
        );
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE status=:status';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
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
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE logicalId=:logicalId';
        return DBHelper::Prepare($sql, $values, DBHelper::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Get the number of pending updates
     * @return mixed
     * @throws \Exception
     */
    public static function nbNeedUpdate($filter = '')
    {
        $values = array();
        $values['status'] = 'update';
        $sql = 'SELECT count(*)
               FROM `' . self::DB_CLASS_NAME . '`
               WHERE status=:status';
        if ($filter != '') {
            $values['type'] = $filter;
            $sql .= ' AND type=:type';
        }

        $result = \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW);
        return $result['count(*)'];
    }

    /**
     * Liste des mises à jour du core.
     *
     * @return array
     */
    public static function listCoreUpdate()
    {
        return FileSystemHelper::ls(NEXTDOM_ROOT . '/install/update', '*');
    }
}
