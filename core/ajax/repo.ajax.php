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

try {
    require_once __DIR__ . '/../php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    ajax::init();

    if (init('action') == 'uploadCloud') {
        unautorizedInDemo();
        repo_market::backup_send(init('backup'));
        ajax::success();
    }

    if (init('action') == 'restoreCloud') {
        unautorizedInDemo();
        $repoName = init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            $class::backup_restore(init('backup'));
            ajax::success();
        }
        ajax::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    if (init('action') == 'sendReportBug') {
        unautorizedInDemo();
        $repoName = init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            ajax::success($class::saveTicket(json_decode(init('ticket'), true)));
        }
        ajax::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    if (init('action') == 'install') {
        unautorizedInDemo();
        $repoName = init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            $repo = $class::byId(init('id'));
            if (!is_object($repo)) {
                throw new Exception(__('Impossible de trouver l\'objet associé : ', __FILE__) . init('id'));
            }
            $update = update::byTypeAndLogicalId($repo->getType(), $repo->getLogicalId());
            if (!is_object($update)) {
                $update = new update();
            }
            $update->setSource(init('repo'));
            $update->setLogicalId($repo->getLogicalId());
            $update->setType($repo->getType());
            $update->setLocalVersion($repo->getDatetime(init('version', 'stable')));
            $update->setConfiguration('version', init('version', 'stable'));
            $update->save();
            $update->doUpdate();
            ajax::success();
        }
        ajax::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    if (init('action') == 'test') {
        $repoName = init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            $class::test();
            ajax::success();
        }
        ajax::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    if (init('action') == 'remove') {
        unautorizedInDemo();
        $repoName = init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            $repo = $class::byId(init('id'));
            if (!is_object($market)) {
                throw new Exception(__('Impossible de trouver l\'objet associé : ', __FILE__) . init('id'));
            }
            $update = update::byTypeAndLogicalId($repo->getType(), $repo->getLogicalId());
            try {
                if (is_object($update)) {
                    $update->remove();
                } else {
                    $market->remove();
                }
            } catch (Exception $e) {
                if (is_object($update)) {
                    $update->deleteObjet();
                }
            }
            ajax::success();
        }
        ajax::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    if (init('action') == 'save') {
        unautorizedInDemo();
        $repoName = init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            $repo_ajax = json_decode(init('market'), true);
            try {
                $repo = $class::byId($repo_ajax['id']);
            } catch (Exception $e) {
                $repo = new $class();
            }
            utils::a2o($repo, $repo_ajax);
            $repo->save();
            ajax::success();
        }
        ajax::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    if (init('action') == 'getInfo') {
        $repoName = init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            ajax::success($class::getInfo(init('logicalId')));
        }
        ajax::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    if (init('action') == 'byLogicalId') {
        $repoName = init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            if (init('noExecption', 0) == 1) {
                try {
                    ajax::success(utils::o2a($class::byLogicalIdAndType(init('logicalId'), init('type'))));
                } catch (Exception $e) {
                    ajax::success();
                }
            } else {
                ajax::success(utils::o2a($class::byLogicalIdAndType(init('logicalId'), init('type'))));
            }
        }
        ajax::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    if (init('action') == 'setRating') {
        unautorizedInDemo();
        $repoName = init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            $repo = $class::byId(init('id'));
            if (!is_object($repo)) {
                throw new Exception(__('Impossible de trouver l\'objet associé : ', __FILE__) . init('id'));
            }
            $repo->setRating(init('rating'));
            ajax::success();
        }
        ajax::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    if (init('action') == 'backupList') {
        $repoName = init('repo');
        if (file_exists(NEXTDOM_ROOT . '/core/repo/' . $repoName . '.repo.php')) {
            $class = 'repo_' . $repoName;
            ajax::success($class::backup_list());
        }
        ajax::error(__('Le repo n\'existe pas : ' . $repoName));
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));

    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
