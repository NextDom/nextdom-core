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
use NextDom\Managers\JeeObjectManager;
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
        $return['widget_margin'] = ConfigManager::byKey('widget::margin');
        $return['serverDatetime'] = Utils::getmicrotime();
        if (!isConnect()) {
            $return['connected'] = false;
            $this->ajax->success($return);
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
            $resultObject = JeeObjectManager::byId($currentUser->getOptions('defaultDashboardObject'));
        }

        $return['plugins'] = array();
        foreach (PluginManager::listPlugin(true) as $plugin) {
            if ($plugin->getEventJs() == 1) {
                $return['plugins'][] = Utils::o2a($plugin);
            }
        }
        $return['custom'] = array('js' => false, 'css' => false);
        $this->ajax->success($return);
    }

    public function getDocumentationUrl()
    {
        AuthentificationHelper::isConnectedOrFail();
        $this->ajax->checkToken();
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
                $this->ajax->success($plugin->getDocumentation());
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
            $this->ajax->success('https://nextdom.github.io/core/' . ConfigManager::byKey('language', 'core', 'fr_FR') . '/' . secureXSS($page));
        }
        throw new CoreException(__('Aucune documentation trouvée'), -1234);
    }

    public function addWarnme()
    {
        AuthentificationHelper::isConnectedOrFail();
        $this->ajax->checkToken();
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
        $this->ajax->success();
    }


    public function ssh()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        $command = Utils::init('command');
        if (strpos($command, '2>&1') === false && strpos($command, '>') === false) {
            $command .= ' 2>&1';
        }
        $output = array();
        exec($command, $output);
        $this->ajax->success(implode("\n", $output));
    }

    public function db()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        if (Utils::init('command', '') !== '') {
            $this->ajax->success(DBHelper::getAll(Utils::init('command')));
        } else {
            $this->ajax->error(__('Aucune requête à exécuter'));
        }
    }

    public function dbcorrectTable()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        DBHelper::compareAndFix(json_decode(file_get_contents(NEXTDOM_ROOT . '/install/database.json'), true), Utils::init('table'));
        $this->ajax->success();

    }

    public function health()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        $this->ajax->success(NextDomHelper::health());
    }

    public function update()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        NextDomHelper::update();
        $this->ajax->success();
    }

    public function clearDate()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        $cache = CacheManager::byKey('NextDomHelper::lastDate');
        $cache->remove();
        $this->ajax->success();
    }

    public function backup()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        BackupManager::backup(true);
        $this->ajax->success();
    }

    public function restore()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        BackupManager::restore(Utils::init('backup'), true);
        $this->ajax->success();
    }

    public function removeBackup()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        BackupManager::removeBackup(Utils::init('backup'));
        $this->ajax->success();
    }

    public function listBackup()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        $this->ajax->success(BackupManager::listBackup());
    }

    public function getConfiguration()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        $this->ajax->success(NextDomHelper::getConfiguration(Utils::init('key'), Utils::init('default')));
    }

    public function resetHwKey()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        ConfigManager::save('NextDomHelper::installKey', '');
        $this->ajax->success();
    }

    public function resetHour()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        CacheManager::delete('hour');
        $this->ajax->success();
    }

    public function backupupload()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        $uploadDir = BackupManager::getBackupDirectory();
        Utils::readUploadedFile($_FILES, "file", $uploadDir, 1000, array(".gz"));
        $this->ajax->success();
    }

    public function haltSystem()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        NextDomHelper::haltSystem();
        $this->ajax->success();
    }

    public function rebootSystem()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        NextDomHelper::rebootSystem();
        $this->ajax->success();
    }

    public function forceSyncHour()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        NextDomHelper::forceSyncHour();
        $this->ajax->success();
    }

    public function getGraphData()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        $return = array('node' => array(), 'link' => array());
        $resultObject = null;
        $type = Utils::init('filter_type');
        if ($type !== '') {
            $resultObject = $type::byId(Utils::init('filter_id'));
            if (!is_object($resultObject)) {
                throw new CoreException(__('Type :') . Utils::init('filter_type') . __(' avec id : ') . Utils::init('filter_id') . __(' inconnu'));
            }
            $this->ajax->success($resultObject->getLinkData());
        } else {
            $this->ajax->error(__('Aucun filtre'));
        }
    }

    public function getTimelineEvents()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
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
        $this->ajax->success($return);
    }

    public function consistency()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        SystemHelper::consistency();
        $this->ajax->success();
    }

    public function cleanFileSystemRight()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        SystemHelper::cleanFileSystemRight();
        $this->ajax->success();
    }

    public function removeTimelineEvents()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        TimeLineHelper::removeTimelineEvent();
        $this->ajax->success();
    }

    public function getFileFolder()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        $this->ajax->success(FileSystemHelper::ls(Utils::init('path'), '*', false, array(Utils::init('type'))));
    }

    public function getFileContent()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        $filePath = Utils::init('path');
        $pathinfo = pathinfo($filePath);
        $extension = Utils::array_key_default($pathinfo, "extension", "<no-ext>");
        if (!in_array($extension, array('php', 'js', 'json', 'sql', 'ini', 'html', 'py', 'css'))) {
            throw new CoreException(__('Vous ne pouvez éditer ce type d\'extension : ' . $extension));
        }
        if (!is_writable($filePath)) {
            throw new CoreException(__('Vous n\'avez pas les droits pour éditer ce fichier.'));
        }
        $this->ajax->success(file_get_contents(Utils::init('path')));
    }

    public function setFileContent()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        $filePath = Utils::init('path');
        $pathInfo = pathinfo($filePath);
        $extension = Utils::array_key_default($pathInfo, "extension", "<no-ext>");
        if (!in_array($extension, array('php', 'js', 'json', 'sql', 'ini', 'html', 'py', 'css'))) {
            throw new CoreException(__('Vous ne pouvez éditer ce type d\'extension : ') . $extension);
        }
        if (!is_writable($filePath)) {
            throw new CoreException(__('Vous n\'avez pas les droits pour éditer ce fichier.'));
        }
        $this->ajax->success(file_put_contents($filePath, Utils::init('content')));
    }

    public function deleteFile()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        $pathinfo = pathinfo(Utils::init('path'));
        $extension = Utils::array_key_default($pathinfo, "extension", "<no-ext>");
        if (!in_array($extension, array('php', 'js', 'json', 'sql', 'ini', 'css'))) {
            throw new CoreException(__('Vous ne pouvez éditer ce type d\'extension : ' . $extension, __FILE__));
        }
        $this->ajax->success(unlink(Utils::init('path')));
    }

    public function createFile()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        $pathinfo = pathinfo(Utils::init('name'));
        $extension = Utils::array_key_default($pathinfo, "extension", "<no-ext>");
        if (!in_array($extension, array('php', 'js', 'json', 'sql', 'ini', 'css'))) {
            throw new CoreException(__('Vous ne pouvez éditer ce type d\'extension : ' . $extension, __FILE__));
        }
        touch(Utils::init('path') . Utils::init('name'));
        if (!file_exists(Utils::init('path') . Utils::init('name'))) {
            throw new CoreException(__('Impossible de créer le fichier, vérifiez les droits'));
        }
        $this->ajax->success();
    }

    public function emptyRemoveHistory()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $this->ajax->checkToken();
        unlink(NEXTDOM_DATA . '/data/remove_history.json');
        $this->ajax->success();
    }
}
