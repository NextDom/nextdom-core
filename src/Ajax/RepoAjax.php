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
        RepoMarket::backup_send(Utils::init('backup'));
        $this->ajax->success();
    }

    public function restoreCloud()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData['className'] . '.php')) {
            $repoClassData['phpClass']::backup_restore(Utils::init('backup'));
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData['className']));
    }

    public function sendReportBug()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData['className'] . '.php')) {
            $this->ajax->success($repoClassData['phpClass']::saveTicket(json_decode(Utils::init('ticket'), true)));
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData['className']));
    }

    public function install()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData['className'] . '.php')) {
            $repo = $repoClassData['phpClass']::byId(Utils::init('id'));
            if (!is_object($repo)) {
                throw new CoreException(__('Impossible de trouver l\'objet associé : ', __FILE__) . Utils::init('id'));
            }
            $update = UpdateManager::byTypeAndLogicalId($repo->getType(), $repo->getLogicalId());
            if (!is_object($update)) {
                $update = new Update();
            }
            $update->setSource(Utils::init('repo'));
            $update->setLogicalId($repo->getLogicalId());
            $update->setType($repo->getType());
            $update->setLocalVersion($repo->getDatetime(Utils::init('version', 'stable')));
            $update->setConfiguration('version', Utils::init('version', 'stable'));
            $update->save();
            $update->doUpdate();
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData['className']));
    }

    public function test()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData['className'] . '.php')) {
            $repoClassData['phpClass']::test();
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData['className']));
    }

    public function remove()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData['className'] . '.php')) {
            $repo = $repoClassData['phpClass']::byId(Utils::init('id'));
            if (!is_object($repo)) {
                throw new CoreException(__('Impossible de trouver l\'objet associé : ', __FILE__) . Utils::init('id'));
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
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData['className']));
    }

    public function save()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData['className'] . '.php')) {
            $repo_ajax = json_decode(Utils::init('market'), true);
            try {
                $repo = $repoClassData['phpClass']::byId($repo_ajax['id']);
            } catch (\Exception $e) {
                $repo = new $repoClassData['phpClass']();
            }
            Utils::a2o($repo, $repo_ajax);
            $repo->save();
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData['className']));
    }

    public function getInfo()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData['className'] . '.php')) {
            $this->ajax->success($repoClassData['phpClass']::getInfo(Utils::init('logicalId')));
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData['className']));
    }

    public function byLogicalId()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData['className'] . '.php')) {
            if (Utils::init('noExecption', 0) == 1) {
                try {
                    $this->ajax->success(Utils::o2a($repoClassData['phpClass']::byLogicalIdAndType(Utils::init('logicalId'), Utils::init('type'))));
                } catch (\Exception $e) {
                    $this->ajax->success();
                }
            } else {
                $this->ajax->success(Utils::o2a($repoClassData['phpClass']::byLogicalIdAndType(Utils::init('logicalId'), Utils::init('type'))));
            }
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData['className']));
    }

    public function setRating()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData['className'] . '.php')) {
            $repo = $repoClassData['phpClass']::byId(Utils::init('id'));
            if (!is_object($repo)) {
                throw new CoreException(__('Impossible de trouver l\'objet associé : ') . Utils::init('id'));
            }
            $repo->setRating(Utils::init('rating'));
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData['className']));
    }

    public function backupList()
    {
        $repoClassData = UpdateManager::getRepoDataFromName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassData['className'] . '.php')) {
            $this->ajax->success($repoClassData['phpClass']::backup_list());
        }
        $this->ajax->error(__('Le repo n\'existe pas : ' . $repoClassData['className']));
    }
}
