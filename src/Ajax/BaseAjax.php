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

namespace NextDom\Ajax;

use NextDom\Enums\AjaxParams;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\Utils;

/**
 * Class BaseAjax
 * @package NextDom\Ajax
 */
abstract class BaseAjax
{
    /**
     * @var string Default rights for access. Must be override
     */
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    /**
     * @var string Default state of the connection needed. Must be override
     */
    protected $MUST_BE_CONNECTED = true;
    /**
     * @var string Default state of the connection needed. Must be override
     */
    protected $CHECK_AJAX_TOKEN = true;
    /**
     * @var AjaxHelper
     */
    protected $ajax = null;
    /**
     * @var array Forbidden callable methods from ajax
     */
    private $FORBIDDEN_METHODS = ['checkIfActionExists', 'process', 'checkAccessOrFail'];

    /**
     * Initialize Ajax helper
     * @throws \Exception
     */
    public function __construct()
    {
        $this->ajax = new AjaxHelper();
    }

    /**
     * Start the process
     * @throws \Exception
     */
    public function process()
    {
        try {
            $this->checkAccessOrFail($this->MUST_BE_CONNECTED, $this->NEEDED_RIGHTS);
            if ($this->CHECK_AJAX_TOKEN) {
                $this->ajax->checkToken();
            }

            // Check and call the method for the action in query
            $actionCode = Utils::init(AjaxParams::ACTION, '');
            if ($this->checkIfActionExists($actionCode)) {
                $this->$actionCode();
            } else {
                throw new CoreException(__('core.error-ajax'), 401);
            }
        } catch (\Throwable $throwable) {
            $this->ajax->error(Utils::displayException($throwable), $throwable->getCode());
        }
    }

    /**
     * Check access of the user. Fail on problem.
     *
     * @param bool $mustBeConnected True if the user must be connected
     * @param string|null $neededRights Needed rights for access
     *
     * @throws CoreException
     */
    protected function checkAccessOrFail(bool $mustBeConnected = true, $neededRights = null)
    {
        if ($mustBeConnected === true) {
            AuthentificationHelper::init();
            if ($neededRights == UserRight::ADMIN) {
                AuthentificationHelper::isConnectedAsAdminOrFail();
            } elseif ($neededRights == UserRight::USER) {
                AuthentificationHelper::isConnectedOrFail();
            } else {
                throw new CoreException(__('core.error-bad-action'), 401);
            }
        }
    }

    /**
     * Test if the action method exists
     * @param string $actionCode Code of the action from the query
     * @return bool True if the method exists
     *
     * @return bool
     */
    private function checkIfActionExists(string $actionCode): bool
    {
        // Test for forbidden parent methods
        if (!in_array($actionCode, $this->FORBIDDEN_METHODS)) {
            // Check if method exists
            $currentClassName = get_class($this);
            if (method_exists($currentClassName, $actionCode)) {
                return true;
            }
        }
        return false;
    }
}
