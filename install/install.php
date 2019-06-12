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

namespace NextDom;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\ScriptHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UpdateManager;
use NextDom\Model\Entity\Update;
use NextDom\Model\Entity\User;

require_once __DIR__ . "/../src/core.php";

ScriptHelper::cliOrCrash();

set_time_limit(1800);

echo "[START INSTALL]\n";
$starttime = strtotime('now');
ScriptHelper::parseArgumentsToGET();

try {
    if (count(SystemHelper::ps('install/install.php', 'sudo')) > 1) {
        echo "Une mise à jour/installation est déjà en cours. Vous devez attendre qu'elle soit finie avant d'en relancer une\n";
        print_r(SystemHelper::ps('install/install.php', 'sudo'));
        echo "[END INSTALL]\n";
        die();
    }
    echo "****Install nextdom from " . NextDomHelper::getNextdomVersion() . " (" . date('Y-m-d H:i:s') . ")****\n";
    /*         * ***************************INSTALLATION************************** */
    if (version_compare(PHP_VERSION, '5.6.0', '<')) {
        throw new CoreException('NextDom nécessite PHP 5.6 ou plus (actuellement : ' . PHP_VERSION . ')');
    }
    echo "\nInstallation de NextDom " . NextDomHelper::getNextdomVersion() . "\n";
    $sql = file_get_contents(__DIR__ . '/install.sql');
    echo "Installation de la base de données...";
    DBHelper::Prepare($sql, array(), DBHelper::FETCH_TYPE_ROW);
    $nextDomUpdate = UpdateManager::byTypeAndLogicalId('core', 'nextdom');
    if (!is_object($nextDomUpdate)) {
        $nextDomUpdate = new Update();
        $nextDomUpdate->setType('core');
        $nextDomUpdate->setLogicalId('nextdom');
        $nextDomUpdate->setRemoteVersion('');
    }
    if (is_dir(NEXTDOM_ROOT . '/.git') && system('git version') !== '') {
        $gitHash = system('git rev-parse HEAD');
        $gitBranch = system('git rev-parse --abbrev-ref HEAD');
        $nextDomUpdate->setLocalVersion($gitHash);
        $nextDomUpdate->setConfiguration('user', 'NextDom');
        $nextDomUpdate->setConfiguration('repository', 'nextdom-core');
        $nextDomUpdate->setConfiguration('version', $gitBranch);
        $nextDomUpdate->setSource('github');
    } else {
        $version = "dpkg -s nextdom | grep '^Version:'";
        $nextDomUpdate->setLocalVersion($version);
        $nextDomUpdate->setSource('deb');
    }
    $nextDomUpdate->save();
    echo "OK\n";
    echo "Post installation...\n";
    ConfigManager::save('api', ConfigManager::genKey());
    require_once __DIR__ . '/consistency.php';

    try {
        echo "Ajout de l'utilisateur (admin,admin)\n";
        $user = new User();
        $user->setLogin('admin');
        $user->setPassword(sha512('admin'));
        $user->setProfils('admin');
        $user->save();
        ConfigManager::save('log::level', 400);
        echo "OK\n";
    } catch (\Throwable $t) {
        echo "OK : Utilisateur deja present\n";
    }
    ConfigManager::save('version', NextDomHelper::getNextdomVersion());
    UpdateManager::checkAllUpdate();
} catch (\Throwable $e) {
    echo 'Erreur durant l\'installation : ' . $e->getMessage();
    echo 'Détails : ' . print_r($e->getTrace(), true);
    echo "[END INSTALL ERROR]\n";
    throw $e;
}

echo "Temps d'installation : " . (strtotime('now') - $starttime) . "s\n";
echo "[END INSTALL SUCCESS]\n";
