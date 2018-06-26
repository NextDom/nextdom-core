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

/* This file is part of NextDom.
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
    const CLASS_NAME = 'update';

    /**
     * Vérifier l'ensemble des mises à jour
     *
     * @param string $filter
     * @param bool $findNewObjets
     */
    public static function checkAllUpdate($filter = '', $findNewObjets = true)
    {
        $findCore = false;
        if ($findNewObjets) {
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
                    $update->setType('core');
                    $update->setLogicalId('nextdom');
                    $update->setSource(\config::byKey('core::repo::provider'));
                    $update->setLocalVersion(\nextdom::version());
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
            $update = new \update();
            $update->setType('core');
            $update->setLogicalId('nextdom');
            $update->setSource(\config::byKey('core::repo::provider'));
            $update->setLocalVersion(\nextdom::version());
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
     * Liste les repos (Source de téléchargements)
     *
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
                'name' => $class::$_name,
                'class' => $class,
                'configuration' => $class::$_configuration,
                'scope' => $class::$_scope,
            );
            $result[str_replace('.repo.php', '', $repoFile)]['enable'] = \config::byKey(str_replace('.repo.php', '', $repoFile) . '::enable');
        }
        return $result;
    }

    /**
     * Obtenir un repo par son identifiant
     *
     * @param $id Identifiant du repo
     *
     * @return array
     */
    public static function repoById($id)
    {
        $class = 'repo_' . $id;
        $return = array(
            'name' => $class::$_name,
            'class' => $class,
            'configuration' => $class::$_configuration,
            'scope' => $class::$_scope,
        );
        $return['enable'] = \config::byKey($id . '::enable');
        return $return;
    }

    /**
     * Met à jour tous les éléments
     *
     * @param string $filter
     *
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
                        } catch (Exception $e) {
                            log::add('update', 'update', $e->getMessage());
                            $error = true;
                        } catch (Error $e) {
                            log::add('update', 'update', $e->getMessage());
                            $error = true;
                        }
                    }
                }
            }
            return $error;
        }
    }

    /**
     * Obtenir des informations sur une mise à jour à partir de son identifiant
     *
     * @param $id Identifiant de la mise à jour
     *
     * @return array|mixed|null
     *
     * @throws \Exception
     */
    public static function byId($id)
    {
        $values = array(
            'id' => $id,
        );
        $sql = 'SELECT ' . \DB::buildField(self::DB_CLASS_NAME) . '
                FROM `update`
                WHERE id=:id';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Obtenir les mises à jour à partir de leurs status
     *
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
                FROM `update`
                WHERE status=:status';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Obtenir les mises à partir de son identifiant logique
     *
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
                FROM `update`
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
                FROM `update`
                WHERE type=:type';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Obtenir les mises à jour à partir de leur type et leur identifiant logique
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
            'type' => $type,
        );
        $sql = 'SELECT ' . \DB::buildField(self::DB_CLASS_NAME) . '
                FROM `update`
                WHERE logicalId=:logicalId
                AND type=:type';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ROW, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Obtenir toutes les mises à jour.
     *
     * @param string $filter
     *
     * @return array|null Liste de tous les objets
     *
     * @throws \Exception
     */
    public static function all($filter = '')
    {
        $values = array();
        $sql = 'SELECT ' . \DB::buildField(self::DB_CLASS_NAME) . '
                FROM `update` ';
        if ($filter != '') {
            $values['type'] = $filter;
            $sql .= 'WHERE `type`=:type ';
        }
        $sql .= 'ORDER BY FIELD( `status`, "update","ok","depreciated") ASC,FIELD( `type`,"plugin","core") DESC, `name` ASC';
        return \DB::Prepare($sql, $values, \DB::FETCH_TYPE_ALL, \PDO::FETCH_CLASS, self::CLASS_NAME);
    }

    /**
     * Obtenir le nombre de mises à jour en attente
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public static function nbNeedUpdate()
    {
        $sql = 'SELECT count(*)
                FROM `update`
                WHERE `status`="update"';
        $result = \DB::Prepare($sql, array(), \DB::FETCH_TYPE_ROW);
        return $result['count(*)'];
    }

    /**
     * Recherche les nouvelles mises à jour
     *
     * @throws \Exception
     */
    public static function findNewUpdateObject()
    {
        foreach (PluginManager::listPlugin() as $plugin) {
            $pluginId = $plugin->getId();
            $update = self::byTypeAndLogicalId('plugin', $pluginId);
            if (!is_object($update)) {
                $update = new \update();
                $update->setLogicalId($pluginId);
                $update->setType('plugin');
                $update->setLocalVersion(date('Y-m-d H:i:s'));
                $update->save();
            }
            $find = array();
            if (method_exists($pluginId, 'listMarketObject')) {
                foreach ($pluginId::listMarketObject() as $logical_id) {
                    $find[$logical_id] = true;
                    $update = self::byTypeAndLogicalId($pluginId, $logical_id);
                    if (!is_object($update)) {
                        $update = new \update();
                        $update->setLogicalId($logical_id);
                        $update->setType($pluginId);
                        $update->setLocalVersion(date('Y-m-d H:i:s'));
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
                $sql = 'DELETE FROM `update`
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