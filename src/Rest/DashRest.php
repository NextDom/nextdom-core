<?php

/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace NextDom\Rest;

use Exception;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\DashManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Model\Entity\Dash;
use NextDom\Model\Entity\JeeObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DashRest
 *
 * @package NextDom\Rest
 */
class DashRest
{
    /**
     * Get dash by id
     * @param int $dashId
     * @return array
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public static function get(int $dashId)
    {
        $dash = DashManager::byId($dashId);
        return Utils::o2a($dash);
    }

    /**
     * @param Request $request
     * @return bool
     * @throws CoreException
     */
    public static function save(Request $request)
    {
        $id = $request->request->get('id');
        $name = $request->request->get('name');
        $data = $request->request->get('data');

        if ($name === '' || $data === '') {
            throw new CoreException(__('Invalid data'));
        }
        $dash = DashManager::byId($id);
        if (!is_object($dash)) {
            $dash = new Dash();
        }
        $dash->setName($name);
        $dash->setData($data);
        $dash->save();
        return true;
    }

    /**
     * TODO: Changer en DELETE
     * @param int $dashId
     * @return bool
     * @throws CoreException
     */
    public static function delete(int $dashId)
    {
        /** @var Dash $dash */
        $dash = DashManager::byId($dashId);
        if (is_object($dash)) {
            $dash->remove();
            return true;
        }
        return false;
    }

    /**
     * @param string $path
     * @return array
     */
    public static function pictures($path = '')
    {
        $path = Utils::sanitizeString($path);
        return FileSystemHelper::ls(NEXTDOM_DATA . '/data/pictures/' . $path, '*', true);
    }
}