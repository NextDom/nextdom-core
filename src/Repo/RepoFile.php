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

use NextDom\Enums\DateFormat;
use NextDom\Interfaces\BaseRepo;
use NextDom\Model\Entity\Update;

class RepoFile implements BaseRepo
{
    public static $_name = 'File';
    public static $_icon = 'fas fa-file-invoice';
    public static $_description = 'repo.file.description';

    public static $_scope = [
        'plugin' => true,
        'backup' => false,
        'hasConfiguration' => false,
    ];

    public static $_configuration = [
        'parameters_for_add' => [
            'path' => [
                'name' => 'repo.file.conf.path',
                'type' => 'file',
            ],
        ],
    ];

    public static function checkUpdate($_update)
    {

    }

    /**
     * @param Update $_update
     * @return array
     */
    public static function downloadObject($_update)
    {
        return ['localVersion' => date(DateFormat::FULL), 'path' => $_update->getConfiguration('path')];
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
}
