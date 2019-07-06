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

namespace NextDom\Controller\Modals;

use NextDom\Helpers\Render;
use NextDom\Managers\UserManager;
use PragmaRX\Google2FA\Google2FA;

/**
 * Class TwoFactorAuthentification
 * @package NextDom\Controller\Modals
 */
class TwoFactorAuthentification extends BaseAbstractModal
{
    /**
     * Render view configure modal
     *
     * @return string
     * @throws \Exception
     */
    public static function get(): string
    {
        $google2fa = new Google2FA();
        @session_start();
        UserManager::getStoredUser()->refresh();
        if (UserManager::getStoredUser()->getOptions('twoFactorAuthentificationSecret') == '' || UserManager::getStoredUser()->getOptions('twoFactorAuthentification', 0) == 0) {
            UserManager::getStoredUser()->setOptions('twoFactorAuthentificationSecret', $google2fa->generateSecretKey());
            UserManager::getStoredUser()->save();
        }
        @session_write_close();
        $google2faUrl = $google2fa->getQRCodeGoogleUrl(
            'NextDom',
            UserManager::getStoredUser()->getLogin(),
            UserManager::getStoredUser()->getOptions('twoFactorAuthentificationSecret')
        );

        $pageData = [];
        $pageData['google2FaUrl'] = $google2faUrl;
        $pageData['userTwoFactorSecret'] = UserManager::getStoredUser()->getOptions('twoFactorAuthentificationSecret');

        return Render::getInstance()->get('/modals/twoFactor.authentification.html.twig', $pageData);
    }
}
