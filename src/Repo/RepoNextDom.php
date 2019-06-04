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

class RepoNextDom
{
    /*     * *************************Attributs****************************** */

    public static $_name = 'NextDom Market';

    public static $_scope = array(
        'plugin' => true,
        'backup' => false,
        'hasConfiguration' => true,
        'proxy' => true,
        'sendPlugin' => false,
        'hasStore' => true,
        'hasScenarioStore' => false,
        'test' => false,
    );

    public static $_configuration = array(
        'configuration' => array(
            'nextdom_stable' => array(
                'name' => 'NextDom Stable',
                'type' => 'checkbox',
            ),
            'nextdom_draft' => array(
                'name' => 'NextDom Draft',
                'type' => 'checkbox',
            ),
            'show_sources_filters' => array(
                'name' => 'Afficher les filtres des sources',
                'type' => 'checkbox',
            ),
        )
    );

    /*     * ***********************Méthodes statiques*************************** */

    public static function checkUpdate(&$_update)
    {
        // Jamais appelé, Passe par le repo GitHub
    }

    public static function getInfo($_logicalId, $_version = 'stable')
    {

    }
}