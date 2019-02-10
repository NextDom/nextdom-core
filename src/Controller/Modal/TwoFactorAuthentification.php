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

namespace NextDom\Controller\Modal;

use NextDom\Helpers\Render;
use NextDom\Managers\ConfigManager;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthentification extends BaseAbstractModal
{
    /**
     * Render view configure modal
     *
     * @param Render $render Render engine
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function get(Render $render): string
    {
        $google2fa = new Google2FA();
        @session_start();
        $_SESSION['user']->refresh();
        if ($_SESSION['user']->getOptions('twoFactorAuthentificationSecret') == '' || $_SESSION['user']->getOptions('twoFactorAuthentification', 0) == 0) {
            $_SESSION['user']->setOptions('twoFactorAuthentificationSecret', $google2fa->generateSecretKey());
            $_SESSION['user']->save();
        }
        @session_write_close();
        $google2faUrl = $google2fa->getQRCodeGoogleUrl(
            'NextDom',
            $_SESSION['user']->getLogin(),
            $_SESSION['user']->getOptions('twoFactorAuthentificationSecret')
        );

        $pageData = [];
        $pageData['google2FaUrl'] = $google2faUrl;
        $pageData['productName'] = ConfigManager::byKey('product_name');
        $pageData['userTwoFactorSecret'] = $_SESSION['user']->getOptions('twoFactorAuthentificationSecret');

        return $render->get('/modals/twoFactor.authentification.html.twig', $pageData);
    }
}
