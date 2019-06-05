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

use NextDom\Helpers\SystemHelper;
use NextDom\Model\Entity\Update;

/**
 * Class RepoApt
 */
class RepoApt
{
    public static $_name = 'Apt';

    public static $_scope = array(
        'plugin' => false,
        'backup' => false,
        'hasConfiguration' => false,
        'core' => true,
    );

    public static $_configuration = [];

    /**
     * @param Update $targetUpdate
     * @return bool
     * @throws \Exception
     */
    public static function checkUpdate(&$targetUpdate)
    {
        if (is_array($targetUpdate)) {
            if (count($targetUpdate) < 1) {
                return false;
            }
            foreach ($targetUpdate as $update) {
                self::checkUpdate($update);
            }
            return true;
        }
        if ($targetUpdate->getType() === 'core' && $targetUpdate->getLogicalId() === 'nextdom') {
            exec(SystemHelper::getCmdSudo() . 'apt-get update');
            exec(SystemHelper::getCmdSudo() . "apt-cache policy nextdom | grep Installed | sed 's/Installed: \\(.*\\)/\\1/g'", $currentVersion);
            exec(SystemHelper::getCmdSudo() . "apt-cache policy nextdom | grep Candidate | sed 's/Candidate: \\(.*\\)/\\1/g'", $newVersion);
            if (count($currentVersion) > 0 && count($newVersion) > 0) {
                $currentVersion = trim($currentVersion[0]);
                $newVersion = trim($newVersion[0]);
                if ($currentVersion !== $newVersion && $currentVersion !== '(none)') {
                    $targetUpdate->setRemoteVersion($newVersion);
                    $targetUpdate->setStatus('update');
                    $targetUpdate->save();
                }
            }
        }
        return true;
    }
}
