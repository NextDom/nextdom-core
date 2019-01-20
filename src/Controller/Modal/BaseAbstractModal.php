<?php

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 *
 * @Support <https://www.nextdom.org>
 * @Email   <admin@nextdom.org>
 * @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
 */

namespace NextDom\Controller\Modal;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Helpers\Utils;
use NextDom\Managers\UpdateManager;

abstract class BaseAbstractModal
{
    public function __construct()
    {
        Status::initConnectState();
    }
    
    public abstract function get(Render $render): string;

    /**
     * Show repo modal from code
     *
     * @param string $type Modal type
     *
     * @return false|string
     * @throws CoreException If repo is disabled
     */
    public function showRepoModal($type)
    {
        $repoId = Utils::init('repo', 'market');
        $repo = UpdateManager::repoById($repoId);
        if ($repo['enable'] == 0) {
            throw new CoreException(__('Le dépôt est inactif : ') . $repoId);
        }
        ob_start();
        $repoDisplayFile = NEXTDOM_ROOT . '/core/repo/' . $repoId . '.display.repo.php';
        if (file_exists($repoDisplayFile)) {
            \include_file('core', $repoId . '.' . $type, 'repo', '', true);
        }
        return ob_get_clean();
    }
}
