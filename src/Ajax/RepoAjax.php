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
        Utils::unautorizedInDemo();
        RepoMarket::backup_send(Utils::init('backup'));
        AjaxHelper::success();
    }

    public function restoreCloud()
    {
        Utils::unautorizedInDemo();
        $repoName = Utils::init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            $class::backup_restore(Utils::init('backup'));
            AjaxHelper::success();
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    public function sendReportBug()
    {
        Utils::unautorizedInDemo();
        $repoName = Utils::init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            AjaxHelper::success($class::saveTicket(json_decode(Utils::init('ticket'), true)));
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    public function install()
    {
        Utils::unautorizedInDemo();
        $repoName = Utils::init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            $repo = $class::byId(Utils::init('id'));
            if (!is_object($repo)) {
                throw new CoreException(__('Impossible de trouver l\'objet associé : ', __FILE__) . Utils::init('id'));
            }
            $update = UpdateManager::byTypeAndLogicalId($repo->getType(), $repo->getLogicalId());
            if (!is_object($update)) {
                $update = new update();
            }
            $update->setSource(Utils::init('repo'));
            $update->setLogicalId($repo->getLogicalId());
            $update->setType($repo->getType());
            $update->setLocalVersion($repo->getDatetime(Utils::init('version', 'stable')));
            $update->setConfiguration('version', Utils::init('version', 'stable'));
            $update->save();
            $update->doUpdate();
            AjaxHelper::success();
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    public function test()
    {
        $repoName = Utils::init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            $class::test();
            AjaxHelper::success();
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    public function remove()
    {
        unautorizedInDemo();
        $repoName = Utils::init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            $repo = $class::byId(Utils::init('id'));
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
            AjaxHelper::success();
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    public function save()
    {
        unautorizedInDemo();
        $repoName = Utils::init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            $repo_ajax = json_decode(Utils::init('market'), true);
            try {
                $repo = $class::byId($repo_ajax['id']);
            } catch (\Exception $e) {
                $repo = new $class();
            }
            Utils::a2o($repo, $repo_ajax);
            $repo->save();
            AjaxHelper::success();
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    public function getInfo()
    {
        $repoName = Utils::init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            AjaxHelper::success($class::getInfo(Utils::init('logicalId')));
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    public function byLogicalId()
    {
        $repoName = Utils::init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            if (Utils::init('noExecption', 0) == 1) {
                try {
                    AjaxHelper::success(Utils::o2a($class::byLogicalIdAndType(Utils::init('logicalId'), Utils::init('type'))));
                } catch (\Exception $e) {
                    AjaxHelper::success();
                }
            } else {
                AjaxHelper::success(Utils::o2a($class::byLogicalIdAndType(Utils::init('logicalId'), Utils::init('type'))));
            }
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    public function setRating()
    {
        unautorizedInDemo();
        $repoName = Utils::init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            $repo = $class::byId(Utils::init('id'));
            if (!is_object($repo)) {
                throw new CoreException(__('Impossible de trouver l\'objet associé : ', __FILE__) . Utils::init('id'));
            }
            $repo->setRating(Utils::init('rating'));
            AjaxHelper::success();
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    public function backupList()
    {
        $repoName = Utils::init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            AjaxHelper::success($class::backup_list());
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoName));
    }
}