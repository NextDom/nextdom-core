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

namespace NextDom\Repo;

use NextDom\Enums\LogTarget;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Interfaces\BaseRepo;
use NextDom\Managers\ConfigManager;
use NextDom\Model\Entity\Update;

require_once __DIR__ . '/../../core/php/core.inc.php';

class RepoGitHub implements BaseRepo
{
    public static $_name = 'Github';
    public static $_icon = 'fab fa-github';
    public static $_description = 'repo.github.description';

    public static $_scope = [
        'plugin' => true,
        'backup' => false,
        'hasConfiguration' => true,
        'core' => true,
    ];

    public static $_configuration = [
        'parameters_for_add' => [
            'user' => [
                'name' => 'repo.github.conf.user',
                'type' => 'input',
            ],
            'repository' => [
                'name' => 'repo.github.conf.repo',
                'type' => 'input',
            ],
            'version' => [
                'name' => 'repo.github.conf.branch',
                'type' => 'input',
                'default' => 'master',
            ],
        ],
        'configuration' => [
            'token' => [
                'name' => 'repo.github.conf.token',
                'type' => 'input',
            ],
            'core::user' => [
                'name' => 'repo.github.conf.core.user',
                'type' => 'input',
                'default' => 'nextdom',
            ],
            'core::repository' => [
                'name' => 'repo.github.conf.core.repo.name',
                'type' => 'input',
                'default' => 'nextdom-core',
                'placeholder' => 'repo.github.conf.core.repo.placeholder',
            ],
            'core::branch' => [
                'name' => 'repo.github.conf.core.branch.name',
                'type' => 'input',
                'default' => 'master',
                'placeholder' => 'repo.github.conf.core.branch.placeholder',
            ],
        ],
    ];

    /*     * ***********************Méthodes statiques*************************** */

    /**
     * @param Update $targetUpdate
     * @throws CoreException
     * @throws \ReflectionException
     */
    public static function checkUpdate(&$targetUpdate)
    {
        if (is_array($targetUpdate)) {
            if (count($targetUpdate) < 1) {
                return;
            }
            foreach ($targetUpdate as $update) {
                self::checkUpdate($update);
            }
            return;
        }
        $client = self::getGithubClient();
        // Check if core data is correct and change type or repository if necessary
        if ($targetUpdate->getType() === 'core') {
            exec('cd ' . NEXTDOM_ROOT . ' && git rev-parse --abbrev-ref HEAD 2> /dev/null', $currentBranch);
            if (is_array($currentBranch) && count($currentBranch) > 0) {
                $targetUpdate->setConfiguration('version', $currentBranch[0]);
                $targetUpdate->save();
            } elseif (!is_dir(NEXTDOM_ROOT . '/.git')) {
                $targetUpdate->setSource('apt');
                $targetUpdate->save();
                RepoApt::checkUpdate($targetUpdate);
                return;
            }
        }
        try {
            $branch = $client->api('repo')->branches($targetUpdate->getConfiguration('user'), $targetUpdate->getConfiguration('repository'), $targetUpdate->getConfiguration('version', 'master'));
        } catch (\Exception $e) {
            $targetUpdate->setRemoteVersion('repository not found');
            $targetUpdate->setStatus('ok');
            $targetUpdate->save();
            return;
        }
        if (!isset($branch['commit']) || !isset($branch['commit']['sha'])) {
            $targetUpdate->setRemoteVersion('error');
            $targetUpdate->setStatus('ok');
            $targetUpdate->save();
            return;
        }
        $targetUpdate->setRemoteVersion($branch['commit']['sha']);
        // Read local version
        exec('cd ' . NEXTDOM_ROOT . ' && git rev-parse HEAD 2> /dev/null', $localVersion);
        if (is_array($localVersion) && count($localVersion) > 0) {
            $targetUpdate->setLocalVersion($localVersion[0]);
        }
        // Compare
        if ($branch['commit']['sha'] != $targetUpdate->getLocalVersion()) {
            $targetUpdate->setStatus('update');
        } else {
            $targetUpdate->setStatus('ok');
        }
        $targetUpdate->save();
    }

    public static function getGithubClient()
    {
        $client = new \Github\Client(
            new \Github\HttpClient\CachedHttpClient(['cache_dir' => NextDomHelper::getTmpFolder('github') . '/cache'])
        );
        if (ConfigManager::byKey('github::token') != '') {
            $client->authenticate(ConfigManager::byKey('github::token'), '', \Github\Client::AUTH_URL_TOKEN);
        }
        return $client;
    }

    public static function downloadObject($_update)
    {
        $client = self::getGithubClient();
        try {
            $branch = $client->api('repo')->branches($_update->getConfiguration('user'), $_update->getConfiguration('repository'), $_update->getConfiguration('version', 'master'));
        } catch (\Exception $e) {
            throw new CoreException(__('Dépot github non trouvé : ') . $_update->getConfiguration('user') . '/' . $_update->getConfiguration('repository') . '/' . $_update->getConfiguration('version', 'master'));
        }
        $tmp_dir = NextDomHelper::getTmpFolder('github');
        $tmp = $tmp_dir . '/' . $_update->getLogicalId() . '.zip';
        if (file_exists($tmp)) {
            unlink($tmp);
        }
        if (!is_writable($tmp_dir)) {
            exec(SystemHelper::getCmdSudo() . 'chmod 777 -R ' . $tmp);
        }
        if (!is_writable($tmp_dir)) {
            throw new CoreException(__('Impossible d\'écrire dans le répertoire : ') . $tmp . __('. Exécuter la commande suivante en SSH : sudo chmod 777 -R ') . $tmp_dir);
        }

        $url = 'https://api.github.com/repos/' . $_update->getConfiguration('user') . '/' . $_update->getConfiguration('repository') . '/zipball/' . $_update->getConfiguration('version', 'master');
        LogHelper::addAlert(LogTarget::UPDATE, __('Téléchargement de ') . $_update->getLogicalId() . '...');
        if (ConfigManager::byKey('github::token') == '') {
            $result = shell_exec('curl -s -L ' . $url . ' > ' . $tmp);
        } else {
            $result = shell_exec('curl -s -H "Authorization: token ' . ConfigManager::byKey('github::token') . '" -L ' . $url . ' > ' . $tmp);
        }
        LogHelper::addAlert(LogTarget::UPDATE, $result);

        if (!isset($branch['commit']) || !isset($branch['commit']['sha'])) {
            return ['path' => $tmp];
        }
        return ['localVersion' => $branch['commit']['sha'], 'path' => $tmp];
    }

    public static function deleteObjet($_update)
    {

    }

    public static function objectInfo($_update)
    {
        return [
            'doc' => 'https://github.com/' . $_update->getConfiguration('user') . '/' . $_update->getConfiguration('repository') . '/blob/' . $_update->getConfiguration('version', 'master') . '/doc/' . ConfigManager::byKey('language', 'core', 'fr_FR') . '/index.asciidoc',
            'changelog' => 'https://github.com/' . $_update->getConfiguration('user') . '/' . $_update->getConfiguration('repository') . '/commits/' . $_update->getConfiguration('version', 'master'),
        ];
    }

    public static function downloadCore($_path)
    {
        $client = self::getGithubClient();
        try {
            $client->api('repo')->branches(ConfigManager::byKey('github::core::user', 'core', 'nextdom'), ConfigManager::byKey('github::core::repository', 'core', 'core'), ConfigManager::byKey('github::core::branch', 'core', 'stable'));
        } catch (\Exception $e) {
            throw new CoreException(__('Dépot github non trouvé : ') . ConfigManager::byKey('github::core::user', 'core', 'nextdom') . '/' . ConfigManager::byKey('github::core::repository', 'core', 'core') . '/' . ConfigManager::byKey('github::core::branch', 'core', 'stable'));
        }
        $url = 'https://api.github.com/repos/' . ConfigManager::byKey('github::core::user', 'core', 'nextdom') . '/' . ConfigManager::byKey('github::core::repository', 'core', 'core') . '/zipball/' . ConfigManager::byKey('github::core::branch', 'core', 'stable');
        echo __('Téléchargement de ') . $url . '...';
        if (ConfigManager::byKey('github::token') == '') {
            echo shell_exec('curl -s -L ' . $url . ' > ' . $_path);
        } else {
            echo shell_exec('curl -s -H "Authorization: token ' . ConfigManager::byKey('github::token') . '" -L ' . $url . ' > ' . $_path);
        }
        return;
    }

    public static function versionCore()
    {
        try {
            $client = self::getGithubClient();
            $fileContent = $client->api('repo')->contents()->download(ConfigManager::byKey('github::core::user', 'core', 'nextdom'), ConfigManager::byKey('github::core::repository', 'core', 'core'), 'core/config/version', ConfigManager::byKey('github::core::branch', 'core', 'stable'));
            return trim($fileContent);
        } catch (\Throwable $e) {

        }
        return null;
    }

    /*     * *********************Methode d'instance************************* */

    /*     * **********************Getteur Setteur*************************** */

}
