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

/* * ***************************Includes********************************* */

namespace NextDom\Repo;

use NextDom\Com\ComShell;
use NextDom\Enums\DateFormat;
use NextDom\Enums\LogTarget;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\SystemHelper;
use NextDom\Interfaces\BaseRepo;
use NextDom\Managers\ConfigManager;

class RepoUrl implements BaseRepo
{
    /*     * *************************Attributs****************************** */

    public static $_name = 'URL';
    public static $_icon = 'fas fa-at';
    public static $_description = 'repo.url.description';

    public static $_scope = [
        'plugin' => true,
        'backup' => false,
        'hasConfiguration' => true,
        'core' => true,
    ];

    public static $_configuration = [
        'parameters_for_add' => [
            'url' => [
                'name' => 'repo.url.conf.zip',
                'type' => 'input',
            ],
        ],
        'configuration' => [
            'core::url' => [
                'name' => 'repo.url.conf.core.url',
                'type' => 'input',
            ],
            'core::version' => [
                'name' => 'repo.url.conf.core.version',
                'type' => 'input',
            ],
        ],
    ];

    /*     * ***********************Méthodes statiques*************************** */

    public static function checkUpdate($_update)
    {

    }

    public static function downloadObject($_update)
    {
        $tmp_dir = NextDomHelper::getTmpFolder('url');
        $tmp = $tmp_dir . '/' . $_update->getLogicalId() . '.zip';
        if (file_exists($tmp)) {
            unlink($tmp);
        }
        if (!is_writable($tmp_dir)) {
            exec(SystemHelper::getCmdSudo() . 'chmod 777 -R ' . $tmp);
        }
        if (!is_writable($tmp_dir)) {
            throw new CoreException(__('Impossible d\'écrire dans le répertoire : ', __FILE__) . $tmp . __('. Exécuter la commande suivante en SSH : sudo chmod 777 -R ', __FILE__) . $tmp_dir);
        }
        $result = exec('wget --no-check-certificate --progress=dot --dot=mega ' . $_update->getConfiguration('url') . ' -O ' . $tmp);
        LogHelper::addAlert(LogTarget::UPDATE, $result);
        return ['path' => $tmp, 'localVersion' => date(DateFormat::FULL)];
    }

    public static function deleteObjet($_update)
    {

    }

    public static function objectInfo($_update)
    {
        return [
            'doc' => '',
            'changelog' => '',
        ];
    }

    public static function downloadCore($_path)
    {
        exec('wget --no-check-certificate --progress=dot --dot=mega ' . ConfigManager::byKey('url::core::url') . ' -O ' . $_path);
        return;
    }

    public static function versionCore()
    {
        if (ConfigManager::byKey('url::core::version') == '') {
            return null;
        }
        try {
            if (file_exists(NextDomHelper::getTmpFolder('url') . '/version')) {
                ComShell::execute(SystemHelper::getCmdSudo() . 'rm /tmp/nextdom_version');
            }
            exec('wget --no-check-certificate --progress=dot --dot=mega ' . ConfigManager::byKey('url::core::version') . ' -O /tmp/nextdom_version');
            if (!file_exists(NextDomHelper::getTmpFolder('url') . '/version')) {
                return null;
            }
            $version = trim(file_get_contents(NextDomHelper::getTmpFolder('url') . '/version'));
            \com_shell::execute(SystemHelper::getCmdSudo() . 'rm ' . NextDomHelper::getTmpFolder('url') . '/version');
            return $version;
        } catch (\Exception $e) {

        }
        return null;
    }

    /*     * *********************Methode d'instance************************* */

    /*     * **********************Getteur Setteur*************************** */

}
