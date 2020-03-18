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

use NextDom\Enums\Common;
use NextDom\Enums\DateFormat;
use NextDom\Enums\LogTarget;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\SQLField;
use NextDom\Enums\UpdateStatus;
use NextDom\Enums\UpdateType;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Managers\Parents\BaseManager;
use NextDom\Managers\Parents\CommonManager;
use NextDom\Model\Entity\Update;

/**
 * Class UpdateManager
 *
 * Manage updates
 *
 * @package NextDom\Managers
 */
class UpdateManager extends BaseManager
{
    use CommonManager;
    const REPO_CLASS_PATH = '\\NextDom\\Repo\\';
    const DB_CLASS_NAME = '`update`';
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
                if (array_key_exists(Common::PHP_CLASS, $repoData)) {
                    $repoPhpClass = $repoData[Common::PHP_CLASS];
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
                    Common::TYPE => $pluginId,
                ];
                $sql = 'DELETE FROM ' . self::DB_CLASS_NAME . '
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
        return static::getOneByClauses(['logicalId' => $logicalId, Common::TYPE => $type]);
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
        return static::getMultipleByClauses([Common::TYPE => $type]);
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
        $sql = static::getBaseSQL() . ' ';
        if ($filter != '') {
            $params[Common::TYPE] = $filter;
            $sql .= 'WHERE `type` = :type ';
        }
        $sql .= 'ORDER BY FIELD(`status`, "update", "ok", "depreciated") ASC, FIELD(`type`, "plugin", "core") DESC, `name` ASC';
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
            if (ucfirst($repoData[Common::NAME]) == ucfirst($name)) {
                return [
                    Common::CLASS_NAME => str_replace(self::REPO_CLASS_PATH, '', $repoData[Common::CLASS_CODE]),
                    Common::PHP_CLASS => $repoData[Common::CLASS_CODE]
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
                    Common::NAME => $fullNameClass::$_name,
                    Common::CLASS_CODE => $fullNameClass,
                    Common::CONFIGURATION => $fullNameClass::$_configuration,
                    Common::SCOPE => $fullNameClass::$_scope,
                    Common::DESCRIPTION => $fullNameClass::$_description,
                    Common::ICON => $fullNameClass::$_icon
                ];
                $result[$repoCode][Common::ENABLE] = ConfigManager::byKey($repoCode . '::enable');
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
        $phpClass = $repoClassData[Common::PHP_CLASS];
        $result = [
            Common::NAME => $phpClass::$_name,
            Common::CLASS_CODE => $repoClassData[Common::CLASS_NAME],
            Common::CONFIGURATION => $phpClass::$_configuration,
            Common::SCOPE => $phpClass::$_scope,
            Common::DESCRIPTION => $phpClass::$_description,
            Common::ICON => $phpClass::$_icon
        ];
        $result[Common::ENABLE] = ConfigManager::byKey($id . '::enable');
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
        return static::getMultipleByClauses([Common::STATUS => $status]);
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
        return static::getOneByClauses([Common::LOGICAL_ID => $logicalId]);
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
            Common::STATUS => 'update',
            Common::CONFIGURATION => '%"doNotUpdate":"1"%'
        ];
        if (is_array($filter)) {
            $likeParams = DBHelper::getInParamFromArray($filter);
            if ($params) {
                $sql = 'SELECT `type`, SUM(CASE WHEN status = :status AND configuration NOT LIKE :configuration THEN 1 ELSE 0 END) AS count
                        FROM ' . self::DB_CLASS_NAME . '
                        WHERE `type` IN ' . $likeParams . '
                        GROUP BY `type`';
                $result = DBHelper::getAll($sql, $params);
                $sql = 'SELECT SUM(CASE WHEN status = :status AND configuration NOT LIKE :configuration THEN 1 ELSE 0 END) AS count
                        FROM ' . self::DB_CLASS_NAME . '
                        WHERE `type` NOT IN ' . $likeParams . '
                        GROUP BY `type`';
                $othersCount = DBHelper::getOne($sql, $params);
                array_push($result, ['type' => 'others', 'count' => intval($othersCount['count'])]);
                return $result;
            }
        }
        else {
            $sql = 'SELECT count(*)
                    FROM ' . self::DB_CLASS_NAME . '
                    WHERE `status` = :status
                    AND `configuration` NOT LIKE :configuration';
            if ($filter != '') {
                $params[Common::TYPE] = $filter;
                $sql .= ' AND `type` = :type';
            }
            $result = DBHelper::getOne($sql, $params);
            return $result[SQLField::COUNT];
        }
        return false;
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
