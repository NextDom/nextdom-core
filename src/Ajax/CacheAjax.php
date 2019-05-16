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
use NextDom\Helpers\Utils;
use NextDom\Managers\CacheManager;

/**
 * Class CacheAjax
 * @package NextDom\Ajax
 */
class CacheAjax extends BaseAjax
{
    /**
     * @var string
     */
    protected $NEEDED_RIGHTS = UserRight::ADMIN;

    /**
     * @var bool
     */
    protected $MUST_BE_CONNECTED = true;

    /**
     * @var bool
     */
    protected $CHECK_AJAX_TOKEN = true;

    /**
     * Flush the cache
     *
     * @throws \NextDom\Exceptions\CoreException
     */
    public function flush()
    {
        Utils::unautorizedInDemo();
        CacheManager::flush();
        AjaxHelper::success();
    }

    /**
     * Clean the cache
     *
     * @throws \NextDom\Exceptions\CoreException
     */
    public function clean()
    {
        Utils::unautorizedInDemo();
        CacheManager::clean();
        AjaxHelper::success();
    }

    /**
     * Get cache statistics
     *
     * @throws \Exception
     */
    public function stats()
    {
        AjaxHelper::success(CacheManager::stats());
    }
}
