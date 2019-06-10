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
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Helpers\TimeLineHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\BackupManager;
use NextDom\Managers\CacheManager;
use NextDom\Managers\CmdManager;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\ObjectManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\UserManager;
use NextDom\Managers\ViewManager;
use NextDom\Model\Entity\Listener;

/**
 * Class NextDomAjax
 * @package NextDom\Ajax
 */
class NextDomAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::NOTHING;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = false;

    public function getInfoApplication()
    {
        $return = array();
        $return['product_name'] = ConfigManager::byKey('product_name');
        $return['product_icon'] = ConfigManager::byKey('product_icon');
        $return['product_image'] = ConfigManager::byKey('product_image');
        $return['widget_margin'] = ConfigManager::byKey('widget::margin');
        $return['serverDatetime'] = Utils::getmicrotime();
        if (!isConnect()) {
            $return['connected'] = false;
            AjaxHelper::success($return);
        }

        $return['user_id'] = UserManager::getStoredUser()->getId();
        $return['nextdom_token'] = AjaxHelper::getToken();
        @session_start();
        $currentUser = UserManager::getStoredUser();
        $currentUser->refresh();
        @session_write_close();

        $return['userProfils'] = $currentUser->getOptions();
        if ($currentUser->getOptions('defaultDesktopView') != '') {
            $view = ViewManager::byId($currentUser->getOptions('defaultDesktopView'));
        }
        if ($currentUser->getOptions('defaultDashboardObject') != '') {
            $object = ObjectManager::byId($currentUser->getOptions('defaultDashboardObject'));
        }

        $return['plugins'] = array();
        foreach (PluginManager::listPlugin(true) as $plugin) {
            if ($plugin->getEventJs() == 1) {
                $return['plugins'][] = Utils::o2a($plugin);
            }
        }
        $return['custom'] = array('js' => false, 'css' => false);
        AjaxHelper::success($return);
    }

    public function getDocumentationUrl()
    {
        AuthentificationHelper::isConnectedOrFail();
        AjaxHelper::init(true);
        $pluginId = Utils::init('plugin');
        $plugin = null;
        if ($pluginId != '' || $pluginId == 'false') {
            try {
                $plugin = PluginManager::byId($pluginId);
            } catch (\Exception $e) {

            }
        }
        if (isset($plugin) && is_object($plugin)) {
            if ($plugin->getDocumentation() != '') {
                AjaxHelper::success($plugin->getDocumentation());
            }
        } else {
            $page = Utils::init('page');
            if (Utils::init('page') == 'scenarioAssist') {
                $page = 'scenario';
            } else if (Utils::init('page') == 'view_edit') {
                $page = 'view';
            } else if (Utils::init('page') == 'plan') {
                $page = 'design';
            } else if (Utils::init('page') == 'plan3d') {
                $page = 'design3d';
            }
            AjaxHelper::success('https://nextdom.github.io/core/' . ConfigManager::byKey('language', 'core', 'fr_FR') . '/' . secureXSS($page));
        }
        throw new CoreException(__('Aucune documentation trouvée'), -1234);
    }

    public function addWarnme()
    {
        AuthentificationHelper::isConnectedOrFail();
        AjaxHelper::init(true);
        $cmd = CmdManager::byId(Utils::init('cmd_id'));
        if (!is_object($cmd)) {
            throw new CoreException(__('Commande non trouvée : ') . Utils::init('cmd_id'));
        }
        $listener = new Listener();
        $listener->setClass('interactQuery');
        $listener->setFunction('warnMeExecute');
        $listener->addEvent($cmd->getId());
        $options = array(
            'type' => 'cmd',
            'cmd_id' => $cmd->getId(),
            'name' => $cmd->getHumanName(),
            'test' => Utils::init('test'),
            'reply_cmd' => Utils::init('reply_cmd', UserManager::getStoredUser()->getOptions('notification::cmd')),
        );
        $listener->setOption($options);
        $listener->save(true);
        AjaxHelper::success();
    }


    public function ssh()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init(true);
        Utils::unautorizedInDemo();
        $command = Utils::init('command');
        if (strpos($command, '2>&1') === false && strpos($command, '>') === false) {
            $command .= ' 2>&1';
        }
        $output = array();
        exec($command, $output);
        AjaxHelper::success(implode("\n", $output));
    }

    public function db()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        if (Utils::init('command', '') !== '') {
            AjaxHelper::success(DBHelper::prepare(Utils::init('command'), array(), DBHelper::FETCH_TYPE_ALL));
        } else {
            AjaxHelper::error(__('Aucune requête à exécuter'));
        }
    }

    public function dbcorrectTable()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        DBHelper::compareAndFix(json_decode(file_get_contents(NEXTDOM_ROOT . '/install/database.json'), true), Utils::init('table'));
        AjaxHelper::success();

    }

    public function health()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init(true);
        AjaxHelper::success(NextDomHelper::health());
    }

    public function update()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        NextDomHelper::update();
        AjaxHelper::success();
    }

    public function clearDate()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init(true);
        $cache = CacheManager::byKey('NextDomHelper::lastDate');
        $cache->remove();
        AjaxHelper::success();
    }

    public function backup()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        BackupManager::backup(true);
        AjaxHelper::success();
    }

    public function restore()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        BackupManager::restore(Utils::init('backup'), true);
        AjaxHelper::success();
    }

    public function removeBackup()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        BackupManager::removeBackup(Utils::init('backup'));
        AjaxHelper::success();
    }

    public function listBackup()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init(true);
        AjaxHelper::success(BackupManager::listBackup());
    }

    public function getConfiguration()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init(true);
        AjaxHelper::success(NextDomHelper::getConfiguration(Utils::init('key'), Utils::init('default')));
    }

    public function resetHwKey()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        ConfigManager::save('NextDomHelper::installKey', '');
        AjaxHelper::success();
    }

    public function resetHour()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init(true);
        CacheManager::delete('hour');
        AjaxHelper::success();
    }

    public function backupupload()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        $uploadDir = BackupManager::getBackupDirectory();
        Utils::readUploadedFile($_FILES, "file", $uploadDir, 1000, array(".gz"));
        AjaxHelper::success();
    }

    public function haltSystem()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        NextDomHelper::haltSystem();
        AjaxHelper::success();
    }

    public function rebootSystem()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        NextDomHelper::rebootSystem();
        AjaxHelper::success();
    }

    public function forceSyncHour()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        NextDomHelper::forceSyncHour();
        AjaxHelper::success();
    }

    public function saveCustom()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        $customType = Utils::init('type');
        if ($customType != 'js' && $customType != 'css') {
            throw new CoreException(__('La version ne peut être que js ou css'));
        }
        $customDir = sprintf("%s/custom/desktop/", NEXTDOM_DATA);
        $customPath = sprintf("%s/custom.%s", $customDir, $customType);
        file_put_contents($customPath, Utils::init('content'));
        AjaxHelper::success();
    }

    public function getGraphData()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init(true);
        $return = array('node' => array(), 'link' => array());
        $object = null;
        $type = Utils::init('filter_type');
        if ($type !== '') {
            $object = $type::byId(Utils::init('filter_id'));
            if (!is_object($object)) {
                throw new CoreException(__('Type :') . Utils::init('filter_type') . __(' avec id : ') . Utils::init('filter_id') . __(' inconnu'));
            }
            AjaxHelper::success($object->getLinkData());
        } else {
            AjaxHelper::error(__('Aucun filtre'));
        }
    }

    public function getTimelineEvents()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        AjaxHelper::init(true);
        $return = array();
        $events = TimeLineHelper::getTimelineEvent();
        foreach ($events as $event) {
            $info = null;
            switch ($event['type']) {
                case 'cmd':
                    $info = CmdManager::timelineDisplay($event);
                    break;
                case 'scenario':
                    $info = ScenarioManager::timelineDisplay($event);
                    break;
            }
            if ($info != null) {
                $return[] = $info;
            }
        }
        AjaxHelper::success($return);
    }

    public function consistency()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        SystemHelper::consistency();
        AjaxHelper::success();
    }

    public function cleanFileSystemRight()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        SystemHelper::cleanFileSystemRight();
        AjaxHelper::success();
    }

    public function removeTimelineEvents()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        TimeLineHelper::removeTimelineEvent();
        AjaxHelper::success();
    }

    public function getFileFolder()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        AjaxHelper::success(FileSystemHelper::ls(Utils::init('path'), '*', false, array(Utils::init('type'))));
    }

    public function getFileContent()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        $filePath = Utils::init('path');
        $pathinfo  = pathinfo($filePath);
        $extension = Utils::array_key_default($pathinfo, "extension", "<no-ext>");
        if (!in_array($extension, array('php', 'js', 'json', 'sql', 'ini','html','py','css'))) {
            throw new CoreException(__('Vous ne pouvez éditer ce type d\'extension : ' . $extension));
        }
        if (!is_writable($filePath)) {
            throw new CoreException(__('Vous n\'avez pas les droits pour éditer ce fichier.'));
        }
        AjaxHelper::success(file_get_contents(Utils::init('path')));
    }

    public function setFileContent()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        $filePath = Utils::init('path');
        $pathInfo = pathinfo($filePath);
        $extension = Utils::array_key_default($pathInfo, "extension", "<no-ext>");
        if (!in_array($extension, array('php', 'js', 'json', 'sql', 'ini','html','py','css'))) {
            throw new CoreException(__('Vous ne pouvez éditer ce type d\'extension : ') . $extension);
        }
        if (!is_writable($filePath)) {
            throw new CoreException(__('Vous n\'avez pas les droits pour éditer ce fichier.'));
        }
        AjaxHelper::success(file_put_contents($filePath, Utils::init('content')));
    }

    public function deleteFile()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        $pathinfo = pathinfo(Utils::init('path'));
        $extension = Utils::array_key_default($pathinfo, "extension", "<no-ext>");
        if (!in_array($extension, array('php', 'js', 'json', 'sql', 'ini', 'css'))) {
            throw new CoreException(__('Vous ne pouvez éditer ce type d\'extension : ' . $extension, __FILE__));
        }
        AjaxHelper::success(unlink(Utils::init('path')));
    }

    public function createFile()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        $pathinfo = pathinfo(Utils::init('name'));
        $extension = Utils::array_key_default($pathinfo, "extension", "<no-ext>");
        if (!in_array($extension, array('php', 'js', 'json', 'sql', 'ini', 'css'))) {
            throw new CoreException(__('Vous ne pouvez éditer ce type d\'extension : ' . $extension, __FILE__));
        }
        touch(Utils::init('path') . Utils::init('name'));
        if (!file_exists(Utils::init('path') . Utils::init('name'))) {
            throw new CoreException(__('Impossible de créer le fichier, vérifiez les droits'));
        }
        AjaxHelper::success();
    }

    public function emptyRemoveHistory()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        AjaxHelper::init(true);
        unlink(NEXTDOM_DATA . '/data/remove_history.json');
        AjaxHelper::success();
    }
}
