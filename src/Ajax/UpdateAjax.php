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

namespace NextDom\Ajax;

use NextDom\Enums\AjaxParams;
use NextDom\Enums\Common;
use NextDom\Enums\LogTarget;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CronManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;
use NextDom\Model\Entity\Update;

/**
 * Class UpdateAjax
 * @package NextDom\Ajax
 */
class UpdateAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function nbUpdate()
    {
        $this->ajax->success(UpdateManager::nbNeedUpdate());
    }

    public function all()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $result = [];
        /**
         * @var Update $update
         */
        foreach (UpdateManager::all(Utils::init(AjaxParams::FILTER)) as $update) {
            $infos = Utils::o2a($update);
            if ($update->getType() == 'plugin') {
                try {
                    $plugin = PluginManager::byId($update->getLogicalId());
                    if (is_object($plugin)) {
                        $infos['plugin'] = Utils::o2a($plugin);
                        $infos['plugin']['icon'] = $plugin->getPathImgIcon();
                    } else {
                        $infos['plugin'] = [];
                    }
                } catch (\Exception $e) {

                }
            }
            $result[] = $infos;
        }
        $this->ajax->success($result);
    }

    public function checkAllUpdate()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        UpdateManager::checkAllUpdate();
        $this->ajax->success();
    }

    public function update()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        LogHelper::clear(LogTarget::UPDATE);
        $update = UpdateManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($update)) {
            throw new CoreException(__('Aucune correspondance pour l\'ID : ' . Utils::init(AjaxParams::ID)));
        }
        try {
            if ($update->getType() != 'core') {
                LogHelper::addAlert(LogTarget::UPDATE, __("[START UPDATE]"));
            }
            $update->doUpdate();
            if ($update->getType() != 'core') {
                LogHelper::addAlert(LogTarget::UPDATE, __("Launch cron dependancy plugins"));
                try {
                    $cron = CronManager::byClassAndFunction('plugin', 'checkDeamon');
                    if (is_object($cron)) {
                        $cron->start();
                    }
                } catch (\Exception $e) {

                }
                LogHelper::addAlert(LogTarget::UPDATE, __("[END UPDATE SUCCESS]"));
                LogHelper::addAlert(LogTarget::UPDATE, __("Refresh with F5 key to discover news"));
            }
        } catch (\Exception $e) {
            if ($update->getType() != 'core') {
                LogHelper::addAlert(LogTarget::UPDATE, $e->getMessage());
                LogHelper::addAlert(LogTarget::UPDATE, __("[END UPDATE ERROR]"));
            }
        }
        $this->ajax->success();
    }

    public function remove()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        UpdateManager::findNewUpdateObject();
        $update = UpdateManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($update)) {
            $update = UpdateManager::byLogicalId(Utils::init(AjaxParams::ID));
        }
        if (!is_object($update)) {
            throw new CoreException(__('Aucune correspondance pour l\'ID : ' . Utils::init(AjaxParams::ID)));
        }
        $update->deleteObjet();
        $this->ajax->success();
    }

    public function checkUpdate()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $update = UpdateManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($update)) {
            $update = UpdateManager::byLogicalId(Utils::init(AjaxParams::ID));
        }
        if (!is_object($update)) {
            throw new CoreException(__('Aucune correspondance pour l\'ID : ' . Utils::init(AjaxParams::ID)));
        }
        $update->checkUpdate();
        $this->ajax->success();
    }

    public function updateAll()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        NextDomHelper::update(json_decode(Utils::init('options', '{}'), true));
        $this->ajax->success();
    }

    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $isNewUpdate = false;
        $backupUpdate = null;

        $updateDataJson = json_decode(Utils::init(AjaxParams::UPDATE), true);
        if (isset($updateDataJson[Common::ID])) {
            $targetUpdate = UpdateManager::byId($updateDataJson[Common::ID]);
        } elseif (isset($updateDataJson['logicalId'])) {
            $targetUpdate = UpdateManager::byLogicalId($updateDataJson[Common::LOGICAL_ID]);
        }
        if (!isset($targetUpdate) || !is_object($targetUpdate)) {
            $targetUpdate = new Update();
            $isNewUpdate = true;
        } else {
            $backupUpdate = $targetUpdate;
        }

        Utils::a2o($targetUpdate, $updateDataJson);
        $targetUpdate->save();
        try {
            $targetUpdate->doUpdate();
        } catch (\Exception $e) {
            if ($isNewUpdate) {
                throw $e;
            } else {
                $targetUpdate = $backupUpdate;
                $targetUpdate->save();
            }
        }
        $this->ajax->success(Utils::o2a($targetUpdate));
    }

    public function saves()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::processJsonObject(NextDomObj::UPDATE, Utils::init(AjaxParams::UPDATES));
        $this->ajax->success();
    }

    public function preUploadFile()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $uploadDir = '/tmp';
        $filename = Utils::readUploadedFile($_FILES, "file", $uploadDir, 100, [], function ($file) {
            $remove = [" ", "(", ")"];
            return str_replace($remove, "", $file["name"]);
        });
        $filepath = sprintf("%s/%s", $uploadDir, $filename);
        $this->ajax->success($filepath);
    }
}
