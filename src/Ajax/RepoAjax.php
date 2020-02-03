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

use mysql_xdevapi\Exception;
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
        $repoCode = Utils::initStr(AjaxParams::REPO);
        $repoPhpClass = $this->checkAndGetRepoClassName($repoCode);
        if ($repoPhpClass) {
            $repoPhpClass::backup_restore(Utils::init(AjaxParams::BACKUP));
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ') . $repoCode);
    }

    public function sendReportBug()
    {
        $repoCode = Utils::initStr(AjaxParams::REPO);
        $repoPhpClass = $this->checkAndGetRepoClassName($repoCode);
        if ($repoPhpClass) {
            $this->ajax->success($repoPhpClass::saveTicket(json_decode(Utils::init('ticket'), true)));
        }
        $this->ajax->error(__('Le repo n\'existe pas : ') . $repoCode);
    }

    public function install()
    {
        $repoCode = Utils::initStr(AjaxParams::REPO);
        $repoPhpClass = $this->checkAndGetRepoClassName($repoCode);
        if ($repoPhpClass) {
            $repo = $repoPhpClass::byId(Utils::init(AjaxParams::ID));
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
        $this->ajax->error(__('Le repo n\'existe pas : ') . $repoCode);
    }

    public function test()
    {
        $repoCode = Utils::initStr(AjaxParams::REPO);
        $repoPhpClass = $this->checkAndGetRepoClassName($repoCode);
        if ($repoPhpClass) {
            if (method_exists($repoPhpClass, 'test')) {
                $repoPhpClass::test();
                $this->ajax->success();
            } else {
                $this->ajax->error(__('Aucune fonctionnalité de test pour le repo : ') . $repoPhpClass);
            }
        }
        $this->ajax->error(__('Le repo n\'existe pas : ') . $repoCode);
    }

    public function remove()
    {
        $repoCode = Utils::initStr(AjaxParams::REPO);
        $repoPhpClass = $this->checkAndGetRepoClassName($repoCode);
        if ($repoPhpClass) {
            $repo = $repoPhpClass::byId(Utils::init(AjaxParams::ID));
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
        $this->ajax->error(__('Le repo n\'existe pas : ') . $repoCode);
    }

    public function save()
    {
        $repoCode = Utils::initStr(AjaxParams::REPO);
        $repoPhpClass = $this->checkAndGetRepoClassName($repoCode);
        if ($repoPhpClass) {
            $repo_ajax = json_decode(Utils::init('market'), true);
            try {
                $repo = $repoPhpClass::byId($repo_ajax['id']);
            } catch (\Exception $e) {
                $repo = new $repoPhpClass();
            }
            Utils::a2o($repo, $repo_ajax);
            $repo->save();
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ') . $repoCode);
    }

    public function getInfo()
    {
        $repoCode = Utils::initStr(AjaxParams::REPO);
        $repoPhpClass = $this->checkAndGetRepoClassName($repoCode);
        if ($repoPhpClass) {
            if (method_exists($repoPhpClass, 'getInfo')) {
                $this->ajax->success($repoPhpClass::getInfo(Utils::init(AjaxParams::LOGICAL_ID)));
            } else {
                $this->ajax->error(__('Aucune fonctionnalité getInfo pour le repo : ') . $repoPhpClass);
            }
        }
        $this->ajax->error(__('Le repo n\'existe pas : ') . $repoCode);
    }

    public function byLogicalId()
    {
        $repoCode = Utils::initStr(AjaxParams::REPO);
        $repoPhpClass = $this->checkAndGetRepoClassName($repoCode);
        if ($repoPhpClass) {
            if (Utils::init('noExecption', 0) == 1) {
                try {
                    $this->ajax->success(Utils::o2a($repoPhpClass::byLogicalIdAndType(Utils::init(AjaxParams::LOGICAL_ID), Utils::init(AjaxParams::TYPE))));
                } catch (\Exception $e) {
                    $this->ajax->success();
                }
            } else {
                $this->ajax->success(Utils::o2a($repoPhpClass::byLogicalIdAndType(Utils::init(AjaxParams::LOGICAL_ID), Utils::init(AjaxParams::TYPE))));
            }
        }
        $this->ajax->error(__('Le repo n\'existe pas : ') . $repoCode);
    }

    public function setRating()
    {
        $repoCode = Utils::initStr(AjaxParams::REPO);
        $repoPhpClass = $this->checkAndGetRepoClassName($repoCode);
        if ($repoPhpClass) {
            $repo = $repoPhpClass::byId(Utils::init(AjaxParams::ID));
            if (!is_object($repo)) {
                throw new CoreException(__('Impossible de trouver l\'objet associé : ') . Utils::init(AjaxParams::ID));
            }
            $repo->setRating(Utils::init('rating'));
            $this->ajax->success();
        }
        $this->ajax->error(__('Le repo n\'existe pas : ') . $repoCode);
    }

    public function backupList()
    {
        $repoCode = Utils::initStr(AjaxParams::REPO);
        $repoPhpClass = $this->checkAndGetRepoClassName($repoCode);
        if ($repoPhpClass) {
            if (method_exists($repoPhpClass, 'backup_list')) {
                try {
                    $this->ajax->success($repoPhpClass::backup_list());
                } catch (\Throwable $t) {
                    var_dump($t->getMessage());
                    var_dump($t->getTraceAsString());
                }
            }
            $this->ajax->error(__('Aucune fonctionnalité de backup_list pour le repo : ') . $repoPhpClass);
        }
        $this->ajax->error(__('Le repo n\'existe pas : ') . $repoCode);
    }

    public function checkAndGetRepoClassName($repoCode)
    {
        $repoPath = NEXTDOM_ROOT . '/src/Repo/';
        $repoClassData = UpdateManager::getRepoDataFromName($repoCode);
        if (array_has($repoClassData, Common::CLASS_NAME) && file_exists($repoPath . $repoClassData[Common::CLASS_NAME] . '.php')) {
            return $repoClassData[Common::PHP_CLASS];
        }
        return false;
    }
}
