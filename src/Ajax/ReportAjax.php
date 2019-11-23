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

namespace NextDom\Ajax;

use NextDom\Enums\AjaxParams;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\Utils;

/**
 * Class ReportAjax
 * @package NextDom\Ajax
 */
class ReportAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function list()
    {
        $return = [];
        $path = NEXTDOM_DATA . '/data/report/' . Utils::init('type') . '/' . Utils::init(AjaxParams::ID) . '/';
        foreach (FileSystemHelper::ls($path, '*') as $value) {
            $return[$value] = ['name' => $value];
        }
        $this->ajax->success($return);
    }

    public function get()
    {
        $path = NEXTDOM_DATA . '/data/report/' . Utils::init('type') . '/' . Utils::init(AjaxParams::ID) . '/' . Utils::init('report');
        $return = pathinfo($path);
        $return['path'] = $path;
        $return['type'] = Utils::init('type');
        $return['id'] = Utils::init(AjaxParams::ID);
        $this->ajax->success($return);
    }

    public function remove()
    {
        $path = NEXTDOM_DATA . '/data/report/' . Utils::init('type') . '/' . Utils::init(AjaxParams::ID) . '/' . Utils::init('report');
        if (file_exists($path)) {
            unlink($path);
        }
        if (file_exists($path)) {
            throw new CoreException(__('Impossible de supprimer : ') . $path);
        }
        $this->ajax->success();
    }

    public function removeAll()
    {
        $path = NEXTDOM_DATA . '/data/report/' . Utils::init('type') . '/' . Utils::init(AjaxParams::ID) . '/';
        foreach (FileSystemHelper::ls($path, '*') as $value) {
            unlink($path . $value);
        }
        $this->ajax->success();
    }
}