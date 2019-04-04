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

use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\CronManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;
use NextDom\Model\Entity\Update;

class UpdateAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function nbUpdate()
    {
        AjaxHelper::success(UpdateManager::nbNeedUpdate());
    }

    public function all()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $return = array();
        /**
         * @var Update $update
         */
        foreach (UpdateManager::all(Utils::init('filter')) as $update) {
            $infos = Utils::o2a($update);
            if ($update->getType() == 'plugin') {
                try {
                    $plugin = PluginManager::byId($update->getLogicalId());
                    $infos['plugin'] = is_object($plugin) ? Utils::o2a($plugin) : array();
                } catch (\Exception $e) {

                }
            }
            $return[] = $infos;
        }
        AjaxHelper::success($return);
    }

    public function checkAllUpdate()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        UpdateManager::checkAllUpdate();
        AjaxHelper::success();
    }

    public function update()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        LogHelper::clear('update');
        $update = UpdateManager::byId(Utils::init('id'));
        if (!is_object($update)) {
            throw new CoreException(__('Aucune correspondance pour l\'ID : ' . Utils::init('id')));
        }
        try {
            if ($update->getType() != 'core') {
                LogHelper::add('update', 'alert', __("[START UPDATE]"));
            }
            $update->doUpdate();
            if ($update->getType() != 'core') {
                LogHelper::add('update', 'alert', __("Launch cron dependancy plugins"));
                try {
                    $cron = CronManager::byClassAndFunction('plugin', 'checkDeamon');
                    if (is_object($cron)) {
                        $cron->start();
                    }
                } catch (\Exception $e) {

                }
                LogHelper::add('update', 'alert', __("[END UPDATE SUCCESS]"));
            }
        } catch (\Exception $e) {
            if ($update->getType() != 'core') {
                LogHelper::add('update', 'alert', $e->getMessage());
                LogHelper::add('update', 'alert', __("[END UPDATE ERROR]"));
            }
        }
        AjaxHelper::success();
    }

    public function remove()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        UpdateManager::findNewUpdateObject();
        $update = UpdateManager::byId(Utils::init('id'));
        if (!is_object($update)) {
            $update = UpdateManager::byLogicalId(Utils::init('id'));
        }
        if (!is_object($update)) {
            throw new CoreException(__('Aucune correspondance pour l\'ID : ' . Utils::init('id')));
        }
        $update->deleteObjet();
        AjaxHelper::success();
    }

    public function checkUpdate()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $update = UpdateManager::byId(Utils::init('id'));
        if (!is_object($update)) {
            $update = UpdateManager::byLogicalId(Utils::init('id'));
        }
        if (!is_object($update)) {
            throw new CoreException(__('Aucune correspondance pour l\'ID : ' . Utils::init('id')));
        }
        $update->checkUpdate();
        AjaxHelper::success();
    }

    public function updateAll()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        NextDomHelper::update(json_decode(Utils::init('options', '{}'), true));
        AjaxHelper::success();
    }

    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $new = false;
        $update_json = json_decode(Utils::init('update'), true);
        if (isset($update_json['id'])) {
            $update = UpdateManager::byId($update_json['id']);
        }
        if (isset($update_json['logicalId'])) {
            $update = UpdateManager::byLogicalId($update_json['logicalId']);
        }
        if (!isset($update) || !is_object($update)) {
            $update = new Update();
            $new = true;
        }
        $old_update = $update;
        Utils::a2o($update, $update_json);
        $update->save();
        try {
            $update->doUpdate();
        } catch (\Exception $e) {
            if ($new) {
                throw $e;
            } else {
                $update = $old_update;
                $update->save();
            }
        }
        AjaxHelper::success(Utils::o2a($update));
    }

    public function saves()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        Utils::processJsonObject('update', Utils::init('updates'));
        AjaxHelper::success();
    }

    public function preUploadFile()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $uploaddir = '/tmp';
        if (!file_exists($uploaddir)) {
            throw new CoreException(__('Répertoire de téléversement non trouvé : ') . $uploaddir);
        }
        if (!isset($_FILES['file'])) {
            throw new CoreException(__('Aucun fichier trouvé. Vérifiez le paramètre PHP (post size limit)'));
        }
        if (filesize($_FILES['file']['tmp_name']) > 100000000) {
            throw new CoreException(__('Le fichier est trop gros (maximum 100Mo)'));
        }
        $filename = str_replace(array(' ', '(', ')'), '', $_FILES['file']['name']);
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploaddir . '/' . $filename)) {
            throw new CoreException(__('Impossible de déplacer le fichier temporaire'));
        }
        if (!file_exists($uploaddir . '/' . $filename)) {
            throw new CoreException(__('Impossible de téléverser le fichier (limite du serveur web ?)'));
        }
        AjaxHelper::success($uploaddir . '/' . $filename);
    }
}