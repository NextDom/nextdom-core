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

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Managers\ConfigManager;

require_once __DIR__ . '/../../core/php/core.inc.php';

class RepoGitHub
{
    public static $_name = 'Github';

    public static $_scope = array(
        'plugin' => true,
        'backup' => false,
        'hasConfiguration' => true,
        'core' => true,
    );

    public static $_configuration = array(
        'parameters_for_add' => array(
            'user' => array(
                'name' => 'Utilisateur ou organisation du dépôt',
                'type' => 'input',
            ),
            'repository' => array(
                'name' => 'Nom du dépôt',
                'type' => 'input',
            ),
            'version' => array(
                'name' => 'Branche',
                'type' => 'input',
                'default' => 'master',
            ),
        ),
        'configuration' => array(
            'token' => array(
                'name' => 'Token (facultatif)',
                'type' => 'input',
            ),
            'core::user' => array(
                'name' => 'Utilisateur ou organisation du dépôt pour le core NextDom',
                'type' => 'input',
                'default' => 'nextdom',
            ),
            'core::repository' => array(
                'name' => 'Nom du dépôt pour le core NextDom',
                'type' => 'input',
                'default' => 'core',
            ),
            'core::branch' => array(
                'name' => 'Branche pour le core NextDom',
                'type' => 'input',
                'default' => 'stable',
            ),
        ),
    );

    /*     * ***********************Méthodes statiques*************************** */

    public static function checkUpdate(&$_update)
    {
        if (is_array($_update)) {
            if (count($_update) < 1) {
                return;
            }
            foreach ($_update as $update) {
                self::checkUpdate($update);
            }
            return;
        }
        $client = self::getGithubClient();
        try {
            $branch = $client->api('repo')->branches($_update->getConfiguration('user'), $_update->getConfiguration('repository'), $_update->getConfiguration('version', 'master'));
        } catch (\Exception $e) {
            $_update->setRemoteVersion('repository not found');
            $_update->setStatus('ok');
            $_update->save();
            return;
        }
        if (!isset($branch['commit']) || !isset($branch['commit']['sha'])) {
            $_update->setRemoteVersion('error');
            $_update->setStatus('ok');
            $_update->save();
            return;
        }
        $_update->setRemoteVersion($branch['commit']['sha']);
        if ($branch['commit']['sha'] != $_update->getLocalVersion()) {
            $_update->setStatus('update');
        } else {
            $_update->setStatus('ok');
        }
        $_update->save();
    }

    public static function getGithubClient()
    {
        $client = new \Github\Client(
            new \Github\HttpClient\CachedHttpClient(array('cache_dir' => NextDomHelper::getTmpFolder('github') . '/cache'))
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
        LogHelper::add('update', 'alert', __('Téléchargement de ') . $_update->getLogicalId() . '...');
        if (ConfigManager::byKey('github::token') == '') {
            $result = shell_exec('curl -s -L ' . $url . ' > ' . $tmp);
        } else {
            $result = shell_exec('curl -s -H "Authorization: token ' . ConfigManager::byKey('github::token') . '" -L ' . $url . ' > ' . $tmp);
        }
        LogHelper::add('update', 'alert', $result);

        if (!isset($branch['commit']) || !isset($branch['commit']['sha'])) {
            return array('path' => $tmp);
        }
        return array('localVersion' => $branch['commit']['sha'], 'path' => $tmp);
    }

    public static function deleteObjet($_update)
    {

    }

    public static function objectInfo($_update)
    {
        return array(
            'doc' => 'https://github.com/' . $_update->getConfiguration('user') . '/' . $_update->getConfiguration('repository') . '/blob/' . $_update->getConfiguration('version', 'master') . '/doc/' . ConfigManager::byKey('language', 'core', 'fr_FR') . '/index.asciidoc',
            'changelog' => 'https://github.com/' . $_update->getConfiguration('user') . '/' . $_update->getConfiguration('repository') . '/commits/' . $_update->getConfiguration('version', 'master'),
        );
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
