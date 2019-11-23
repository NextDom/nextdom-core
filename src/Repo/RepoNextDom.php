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

use NextDom\Interfaces\BaseRepo;

class RepoNextDom implements BaseRepo
{
    /*     * *************************Attributs****************************** */

    public static $_name = 'NextDom Market';
    public static $_icon = 'fas fa-store';
    public static $_description = 'repo.nextdom.description';

    public static $_scope = [
        'plugin' => true,
        'backup' => false,
        'hasConfiguration' => true,
        'proxy' => true,
        'sendPlugin' => false,
        'hasStore' => true,
        'hasScenarioStore' => false,
        'test' => false,
    ];

    public static $_configuration = [
        'configuration' => [
            'nextdom_stable' => [
                'name' => 'repo.nextdom.conf.stable',
                'type' => 'checkbox',
            ],
            'nextdom_draft' => [
                'name' => 'repo.nextdom.conf.draft',
                'type' => 'checkbox',
            ],
            'show_sources_filters' => [
                'name' => 'repo.nextdom.conf.filters',
                'type' => 'checkbox',
            ],
        ]
    ];

    /*     * ***********************Méthodes statiques*************************** */

    public static function checkUpdate(&$_update)
    {
        // Jamais appelé, Passe par le repo GitHub
    }

    public static function getInfo($_logicalId, $_version = 'stable')
    {

    }
}
