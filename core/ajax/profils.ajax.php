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

use NextDom\Helpers\Utils;

try {
    require_once dirname(__FILE__) . '/../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect()) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));

    }

    if (init('action') == 'removeImage') {
        $uploaddir = sprintf("%s/public/img/profils", NEXTDOM_ROOT);
        $pathInfo  = pathinfo(init('image'));
        $path      = sprintf("%s/%s.%s", $uploaddir, $pathInfo['basename'], $pathInfo['extension']);
        ajax::success(unlink($path));
    }

    if (init('action') == 'imageUpload') {
        $uploadDir = sprintf("%s/public/img/profils", NEXTDOM_ROOT);
        Utils::readUploadedFile($_FILES, "images", $uploadDir, 8, array(".png", ".jpg"));
        ajax::success();
    }

    throw new Exception(__('Aucune methode correspondante à : ', __FILE__) . init('action'));

} catch (Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}
