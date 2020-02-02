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
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Utils;
use NextDom\Managers\UpdateManager;
use NextDom\Model\Entity\Update;
use NextDom\Repo\RepoMarket;

/**
 * Class RepoAjax
 * @package NextDom\Ajax
 */
class RepoAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function uploadCloud()
    {
        RepoMarket::backup_send(Utils::init(AjaxParams::BACKUP));
        $this->ajax->success();
    }

    public function restoreCloud()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::initStr(AjaxParams::REPO));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData[Common::CLASS_NAME] . '.php')) {
            $repoClassData[Common::PHP_CLASS]::backup_restore(Utils::init(AjaxParams::BACKUP));
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData[Common::CLASS_NAME]));
    }

    public function sendReportBug()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::initStr(AjaxParams::REPO));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData[Common::CLASS_NAME] . '.php')) {
            $this->ajax->success($repoClassData[Common::PHP_CLASS]::saveTicket(json_decode(Utils::init('ticket'), true)));
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData[Common::CLASS_NAME]));
    }

    public function install()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::initStr(AjaxParams::REPO));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData[Common::CLASS_NAME] . '.php')) {
            $repo = $repoClassData[Common::PHP_CLASS]::byId(Utils::init(AjaxParams::ID));
            if (!is_object($repo)) {
                throw new CoreException(__('Impossible de trouver l\'objet associé : ') . Utils::init(AjaxParams::ID));
            }
            $update = UpdateManager::byTypeAndLogicalId($repo->getType(), $repo->getLogicalId());
            if (!is_object($update)) {
                $update = new Update();
            }
            $update->setSource(Utils::initStr(AjaxParams::REPO));
            $update->setLogicalId($repo->getLogicalId());
            $update->setType($repo->getType());
            $update->setLocalVersion($repo->getDatetime(Utils::init(AjaxParams::VERSION, 'stable')));
            $update->setConfiguration('version', Utils::init(AjaxParams::VERSION, 'stable'));
            $update->save();
            $update->doUpdate();
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData[Common::CLASS_NAME]));
    }

    public function test()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::initStr(AjaxParams::REPO));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData[Common::CLASS_NAME] . '.php')) {
            $repoClassData[Common::PHP_CLASS]::test();
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData[Common::CLASS_NAME]));
    }

    public function remove()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::initStr(AjaxParams::REPO));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData[Common::CLASS_NAME] . '.php')) {
            $repo = $repoClassData[Common::PHP_CLASS]::byId(Utils::init(AjaxParams::ID));
            if (!is_object($repo)) {
                throw new CoreException(__('Impossible de trouver l\'objet associé : ') . Utils::init(AjaxParams::ID));
            }
            $update = UpdateManager::byTypeAndLogicalId($repo->getType(), $repo->getLogicalId());
            try {
                if (is_object($update)) {
                    $update->remove();
                } else {
                    $repo->remove();
                }
            } catch (\Exception $e) {
                if (is_object($update)) {
                    $update->deleteObjet();
                }
            }
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData[Common::CLASS_NAME]));
    }

    public function save()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::initStr(AjaxParams::REPO));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData[Common::CLASS_NAME] . '.php')) {
            $repo_ajax = json_decode(Utils::init('market'), true);
            try {
                $repo = $repoClassData[Common::PHP_CLASS]::byId($repo_ajax['id']);
            } catch (\Exception $e) {
                $repo = new $repoClassData[Common::PHP_CLASS]();
            }
            Utils::a2o($repo, $repo_ajax);
            $repo->save();
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData[Common::CLASS_NAME]));
    }

    public function getInfo()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::initStr(AjaxParams::REPO));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData[Common::CLASS_NAME] . '.php')) {
            $this->ajax->success($repoClassData[Common::PHP_CLASS]::getInfo(Utils::init(AjaxParams::LOGICAL_ID)));
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData[Common::CLASS_NAME]));
    }

    public function byLogicalId()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::initStr(AjaxParams::REPO));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData[Common::CLASS_NAME] . '.php')) {
            if (Utils::init('noExecption', 0) == 1) {
                try {
                    $this->ajax->success(Utils::o2a($repoClassData[Common::PHP_CLASS]::byLogicalIdAndType(Utils::init(AjaxParams::LOGICAL_ID), Utils::init(AjaxParams::TYPE))));
                } catch (\Exception $e) {
                    $this->ajax->success();
                }
            } else {
                $this->ajax->success(Utils::o2a($repoClassData[Common::PHP_CLASS]::byLogicalIdAndType(Utils::init(AjaxParams::LOGICAL_ID), Utils::init(AjaxParams::TYPE))));
            }
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData[Common::CLASS_NAME]));
    }

    public function setRating()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::initStr(AjaxParams::REPO));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData[Common::CLASS_NAME] . '.php')) {
            $repo = $repoClassData[Common::PHP_CLASS]::byId(Utils::init(AjaxParams::ID));
            if (!is_object($repo)) {
                throw new CoreException(__('Impossible de trouver l\'objet associé : ') . Utils::init(AjaxParams::ID));
            }
            $repo->setRating(Utils::init('rating'));
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData[Common::CLASS_NAME]));
    }

    public function backupList()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::initStr(AjaxParams::REPO));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData[Common::CLASS_NAME] . '.php')) {
            $this->ajax->success($repoClassData[Common::PHP_CLASS]::backup_list());
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData[Common::CLASS_NAME]));
    }
}
