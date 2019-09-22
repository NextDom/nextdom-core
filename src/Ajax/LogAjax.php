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

use NextDom\Enums\UserRight;
use NextDom\Helpers\AjaxHelper;
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
        LogHelper::clear(Utils::init('log'));
        AjaxHelper::success();
    }

    public function remove()
    {
        LogHelper::remove(Utils::init('log'));
        AjaxHelper::success();
    }

    public function list()
    {
        AjaxHelper::success(LogHelper::liste());
    }

    public function removeAll()
    {
        LogHelper::removeAll();
        AjaxHelper::success();
    }

    public function get()
    {
        AjaxHelper::success(LogHelper::get(Utils::init('log'), Utils::init('start', 0), Utils::init('nbLine', 99999)));
    }
}