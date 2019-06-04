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

    /**
     * Get the class of the repo by the name
     * @param string $name Name of the repo in jeedom format
     * @return string Name of the class
     */
    private function getRepoClassName($name): string {
        return 'Repo' . ucwords($name);
    }

    public function uploadCloud()
    {
        Utils::unautorizedInDemo();
        RepoMarket::backup_send(Utils::init('backup'));
        AjaxHelper::success();
    }

    public function restoreCloud()
    {
        Utils::unautorizedInDemo();
        $repoClassName = $this->getRepoClassName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassName . '.php')) {
            $repoClassName::backup_restore(Utils::init('backup'));
            AjaxHelper::success();
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoClassName));
    }

    public function sendReportBug()
    {
        Utils::unautorizedInDemo();
        $repoClassName = $this->getRepoClassName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassName . '.php')) {
            AjaxHelper::success($repoClassName::saveTicket(json_decode(Utils::init('ticket'), true)));
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoClassName));
    }

    public function install()
    {
        Utils::unautorizedInDemo();
        $repoClassName = $this->getRepoClassName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassName . '.php')) {
            $repo = $repoClassName::byId(Utils::init('id'));
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
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoClassName));
    }

    public function test()
    {
        $repoClassName = $this->getRepoClassName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassName . '.php')) {
            $repoClassName::test();
            AjaxHelper::success();
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoClassName));
    }

    public function remove()
    {
        unautorizedInDemo();
        $repoClassName = $this->getRepoClassName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassName . '.php')) {
            $repo = $repoClassName::byId(Utils::init('id'));
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
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoClassName));
    }

    public function save()
    {
        unautorizedInDemo();
        $repoClassName = $this->getRepoClassName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassName . '.php')) {
            $repo_ajax = json_decode(Utils::init('market'), true);
            try {
                $repo = $repoClassName::byId($repo_ajax['id']);
            } catch (\Exception $e) {
                $repo = new $repoClassName();
            }
            Utils::a2o($repo, $repo_ajax);
            $repo->save();
            AjaxHelper::success();
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoClassName));
    }

    public function getInfo()
    {
        $repoClassName = $this->getRepoClassName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassName . '.php')) {
            AjaxHelper::success($repoClassName::getInfo(Utils::init('logicalId')));
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoClassName));
    }

    public function byLogicalId()
    {
        $repoClassName = $this->getRepoClassName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassName . '.php')) {
            if (Utils::init('noExecption', 0) == 1) {
                try {
                    AjaxHelper::success(Utils::o2a($repoClassName::byLogicalIdAndType(Utils::init('logicalId'), Utils::init('type'))));
                } catch (\Exception $e) {
                    AjaxHelper::success();
                }
            } else {
                AjaxHelper::success(Utils::o2a($repoClassName::byLogicalIdAndType(Utils::init('logicalId'), Utils::init('type'))));
            }
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoClassName));
    }

    public function setRating()
    {
        unautorizedInDemo();
        $repoClassName = $this->getRepoClassName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassName . '.php')) {
            $repo = $repoClassName::byId(Utils::init('id'));
            if (!is_object($repo)) {
                throw new CoreException(__('Impossible de trouver l\'objet associé : ') . Utils::init('id'));
            }
            $repo->setRating(Utils::init('rating'));
            AjaxHelper::success();
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoClassName));
    }

    public function backupList()
    {
        $repoClassName = $this->getRepoClassName(Utils::init('repo'));
        if (file_exists(NEXTDOM_ROOT . '/src/Repo/' . $repoClassName . '.php')) {
            AjaxHelper::success($repoClassName::backup_list());
        }
        AjaxHelper::error(__('Le repo n\'existe pas : ' . $repoClassName));
    }
}