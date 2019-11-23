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
use NextDom\Helpers\LogHelper;
use NextDom\Helpers\Utils;

/**
 * Class LogAjax
 * @package NextDom\Ajax
 */
class LogAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function clear()
    {
        LogHelper::clear(Utils::init(AjaxParams::LOG));
        $this->ajax->success();
    }

    public function remove()
    {
        LogHelper::remove(Utils::init(AjaxParams::LOG));
        $this->ajax->success();
    }

    public function list()
    {
        $this->ajax->success(LogHelper::getLogFileList());
    }

    public function removeAll()
    {
        LogHelper::removeAll();
        $this->ajax->success();
    }

    public function get()
    {
        $this->ajax->success(LogHelper::get(Utils::init(AjaxParams::LOG), Utils::init(AjaxParams::START, 0), Utils::init('nbLine', 99999)));
    }
}