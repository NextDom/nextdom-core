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
use NextDom\Helpers\Utils;

/**
 * Class ProfilsAjax
 * @package NextDom\Ajax
 */
class ProfilsAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function removeImage()
    {
        $uploaddir = sprintf("%s/public/img/profils", NEXTDOM_ROOT);
        $pathInfo = pathinfo(Utils::init(AjaxParams::IMAGE));
        $extension = Utils::array_key_default($pathInfo, "extension", "<no-ext>");
        $path = sprintf("%s/%s.%s", $uploaddir, $pathInfo['basename'], $extension);
        $this->ajax->success(unlink($path));
    }

    public function imageUpload()
    {
        $uploadDir = sprintf("%s/public/img/profils", NEXTDOM_ROOT);
        Utils::readUploadedFile($_FILES, "images", $uploadDir, 8, ['.png', '.jpg', '.jpeg']);
        $this->ajax->success();
    }
}

