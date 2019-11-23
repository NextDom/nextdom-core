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

use NextDom\Enums\DateFormat;
use NextDom\Enums\LogTarget;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\UpdateStatus;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Model\Entity\Update;

/**
 * Class UpdateManager
 *
 * Manage updates
 *
 * @package NextDom\Managers
 */
class UpdateManager
{
    const REPO_CLASS_PATH = '\\NextDom\\Repo\\';
    const DB_CLASS_NAME = 'update';
    const CLASS_NAME = Update::class;

    /**
     * Check all updates
     *
     * @param string $filter Type of update
     * @param bool $findNewObjects Find if new objects are presents
     *
     * @throws \Exception
     */
    public static function checkAllUpdate($filter = '', $findNewObjects = true)
    {
        if ($findNewObjects) {
            self::findNewUpdateObject();
        }
        $updatesList = self::all($filter);
        $updatesToCheckBySource = [];
        // Arrange updates by source
        if (is_array($updatesList)) {
            foreach ($updatesList as $update) {
                if ($update->getStatus() != UpdateStatus::HOLD) {
                    $updateSource = $update->getSource();
                    if (!isset($updatesToCheckBySource[$updateSource])) {
                        $updatesToCheckBySource[$updateSource] = [];
                    }
                    $updatesToCheckBySource[$updateSource][] = $update;
                }
            }
        }

        // Check all updates
        foreach ($updatesToCheckBySource as $source => $updates) {
            if (ConfigManager::byKey($source . '::enable') == 1) {
                $repoData = self::getRepoDataFromName($source);
                if (array_key_exists('phpClass', $repoData)) {
                    $repoPhpClass = $repoData['phpClass'];
                    if (class_exists($repoPhpClass) && method_exists($repoPhpClass, 'checkUpdate')) {
                        $repoPhpClass::checkUpdate($updates);
                    }
                }
            }
        }

        // Save last update in database
        ConfigManager::save('update::lastCheck', date(DateFormat::FULL));
    }

    /**
     * Find if new items are presents (installed manually)
     *
     * @throws \Exception
     */
    public static function findNewUpdateObject()
    {
        // Look for plugins
        foreach (PluginManager::listPlugin() as $plugin) {
            $pluginId = $plugin->getId();
            $update = self::byTypeAndLogicalId(NextDomObj::PLUGIN, $pluginId);
            // Add update data if plugin not exists
            if (!is_object($update)) {
                $update = (new Update())
                    ->setLogicalId($pluginId)
                    ->setType(NextDomObj::PLUGIN)
                    ->setLocalVersion(date(DateFormat::FULL));
                $update->save();
            }
            $find = [];
            // Check for plugin with market
            if (method_exists($pluginId, 'listMarketObject')) {
                $pluginIdListMarketObject = $pluginId::listMarketObject();
                // Check all object from this market
                foreach ($pluginIdListMarketObject as $logicalId) {
                    $find[$logicalId] = true;
                    $update = self::byTypeAndLogicalId($pluginId, $logicalId);
                    // Add update if not exists
                    if (!is_object($update)) {
                        $update = (new Update())
                            ->setLogicalId($logicalId)
                            ->setType($pluginId)
                            ->setLocalVersion(date(DateFormat::FULL));
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
                // Remove all update if plugin is removed
                $params = [
                    'type' => $pluginId,
                ];
                $sql = 'DELETE FROM `' . self::DB_CLASS_NAME . '`
                        WHERE `type` = :type';
                DBHelper::exec($sql, $params);
            }
        }
    }

    /**
     * Get updates from their type and logicalId
     *
     * @param $type
     * @param $logicalId
     *
     * @return Update|null
     *
     * @throws \Exception
     */
    public static function byTypeAndLogicalId($type, $logicalId)
    {
        $params = [
            'logicalId' => $logicalId,
            'type' => $type,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE logicalId = :logicalId
                AND `type` = :type';
        return DBHelper::getOneObject($sql, $params, self::CLASS_NAME);
    }

    /**
     * Get updates by type
     *
     * @param $type
     *
     * @return Update[]|null
     *
     * @throws \Exception
     */
    public static function byType($type)
    {
        $params = [
            'type' => $type,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE `type` = :type';
        return DBHelper::getAllObjects($sql, $params, self::CLASS_NAME);
    }

    /**
     * Get all the updates.
     *
     * @param string $filter
     *
     * @return Update[]|null List of all objects
     *
     * @throws \Exception
     */
    public static function all($filter = '')
    {
        $params = [];
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '` ';
        if ($filter != '') {
            $params['type'] = $filter;
            $sql .= 'WHERE `type` = :type ';
        }
        $sql .= 'ORDER BY FIELD( `status`, "update", "ok", "depreciated") ASC, FIELD( `type`, "plugin","core") DESC, `name` ASC';
        return DBHelper::getAllObjects($sql, $params, self::CLASS_NAME);
    }

    /**
     * Get the class of the repo by the name
     *
     * @param string $name Name of the repo in jeedom format
     *
     * @return array Associative array
     *
     * @throws \Exception
     */
    public static function getRepoDataFromName($name): array
    {
        $repoList = self::listRepo();
        foreach ($repoList as $repoData) {
            if (ucfirst($repoData['name']) == ucfirst($name)) {
                return [
                    'className' => str_replace(self::REPO_CLASS_PATH, '', $repoData['class']),
                    'phpClass' => $repoData['class']
                ];
            }
        }
        return [];
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
        foreach (FileSystemHelper::ls(NEXTDOM_ROOT . '/src/Repo/', '*.php') as $repoFile) {
            $repoClassName = str_replace('.php', '', $repoFile);
            $fullNameClass = self::REPO_CLASS_PATH . $repoClassName;
            if (class_exists($fullNameClass) && is_subclass_of($fullNameClass, '\\NextDom\\Interfaces\\BaseRepo')) {
                $repoCode = strtolower(str_replace('Repo', '', $repoClassName));
                $result[$repoCode] = [
                    'name' => $fullNameClass::$_name,
                    'class' => $fullNameClass,
                    'configuration' => $fullNameClass::$_configuration,
                    'scope' => $fullNameClass::$_scope,
                    'description' => $fullNameClass::$_description,
                    'icon' => $fullNameClass::$_icon
                ];
                $result[$repoCode]['enable'] = ConfigManager::byKey($repoCode . '::enable');
            }
        }
        return $result;
    }

    /**
     * Get a repo by its identifier
     *
     * @param string $id Repo identifier
     *
     * @return array Repo data
     *
     * @throws \Exception
     */
    public static function repoById($id)
    {
        $repoClassData = self::getRepoDataFromName($id);
        $phpClass = $repoClassData['phpClass'];
        $result = [
            'name' => $phpClass::$_name,
            'class' => $repoClassData['className'],
            'configuration' => $phpClass::$_configuration,
            'scope' => $phpClass::$_scope,
            'description' => $phpClass::$_description,
            'icon' => $phpClass::$_icon
        ];
        $result['enable'] = ConfigManager::byKey($id . '::enable');
        return $result;
    }

    /**
     * Update all items
     *
     * @param string $filter Type of updates
     *
     * @return bool True if all update pass
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \Throwable
     */
    public static function updateAll(string $filter = '')
    {
        $error = false;
        if ($filter == 'core') {
            foreach (self::byType($filter) as $update) {
                $update->doUpdate();
            }
        } else {
            if ($filter == '') {
                $updatesList = self::all();
            } else {
                $updatesList = self::byType($filter);
            }
            if (is_array($updatesList)) {
                foreach ($updatesList as $update) {
                    if ($update->getStatus() != UpdateStatus::HOLD && $update->getStatus() == UpdateStatus::UPDATE && $update->getType() != 'core') {
                        try {
                            $update->doUpdate();
                        } catch (\Exception $e) {
                            LogHelper::addUpdate(LogTarget::UPDATE, $e->getMessage());
                            $error = true;
                        }
                    }
                }
            }
        }
        return $error;
    }

    /**
     * Get update by his id
     *
     * @param string $id ID of the update
     *
     * @return Update|null Update object
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        $params = [
            'id' => $id,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE id = :id';
        return DBHelper::getOneObject($sql, $params, self::CLASS_NAME);
    }

    /**
     * Get updates by their status
     *
     * @param string $status Status of the update (@see UpdateStatus)
     *
     * @return Update[] List of updates of the required status
     *
     * @throws \Exception
     */
    public static function byStatus($status)
    {
        $params = [
            'status' => $status,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE status = :status';
        return DBHelper::getAllObjects($sql, $params, self::CLASS_NAME);
    }

    /**
     * Get the bets from its logical identifier
     *
     * @param string $logicalId Logical Id of the update (plugin id)
     *
     * @return Update[]|null List of updates
     *
     * @throws \Exception
     */
    public static function byLogicalId($logicalId)
    {
        $params = [
            'logicalId' => $logicalId,
        ];
        $sql = 'SELECT ' . DBHelper::buildField(self::DB_CLASS_NAME) . '
                FROM `' . self::DB_CLASS_NAME . '`
                WHERE logicalId = :logicalId';
        return DBHelper::getOneObject($sql, $params, self::CLASS_NAME);
    }

    /**
     * Get the number of pending updates
     *
     * @param string $filter Type filter
     *
     * @return int Count of pending updates
     *
     * @throws \NextDom\Exceptions\CoreException
     */
    public static function nbNeedUpdate($filter = '')
    {
        $params = [
            'status' => 'update',
            'configuration' => '%"doNotUpdate":"1"%'
        ];
        $sql = 'SELECT count(*)
               FROM `' . self::DB_CLASS_NAME . '`
               WHERE `status` = :status
               AND `configuration` NOT LIKE :configuration';
        if ($filter != '') {
            $params['type'] = $filter;
            $sql .= ' AND `type` = :type';
        }

        $result = DBHelper::getOne($sql, $params);
        return $result['count(*)'];
    }

    /**
     * List core updates
     *
     * @return array List of updates
     */
    public static function listCoreUpdate()
    {
        return FileSystemHelper::ls(NEXTDOM_ROOT . '/install/update', '*');
    }
}
