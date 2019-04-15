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
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\Utils;

class ProfilsAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function removeImage()
    {
        $uploaddir = NEXTDOM_ROOT . '/public/img/profils/';
        $pathInfo = pathinfo(Utils::init('image'));
        AjaxHelper::success(unlink($uploaddir . $pathInfo['basename'] . '.' . $pathInfo['extension']));
    }

    public function imageUpload()
    {
        $uploaddir = NEXTDOM_ROOT . '/public/img/profils/';
        if (!file_exists($uploaddir)) {
            throw new CoreException(__('Répertoire d\'upload non trouvé : ') . $uploaddir);
        }
        if (!isset($_FILES['images'])) {
            throw new CoreException(__('{{Aucun fichier trouvé. Vérifié parametre PHP (post size limit}}'));
        }
        $extension = strtolower(strrchr($_FILES['images']['name'], '.'));
        if (!in_array($extension, array('.png', '.jpg'))) {
            throw new CoreException('{{Seul les images sont acceptées (autorisé .jpg .png)}} : ' . $extension);
        }
        if (filesize($_FILES['images']['tmp_name']) > 1000000) {
            throw new CoreException(__('Le fichier est trop gros (maximum 8mo)'));
        }
        if (!move_uploaded_file($_FILES['images']['tmp_name'], $uploaddir . '/' . $_FILES['images']['name'])) {
            throw new CoreException(__('Impossible de déplacer le fichier temporaire'));
        }
        if (!file_exists($uploaddir . '/' . $_FILES['images']['name'])) {
            throw new CoreException(__("Impossible d'uploader le fichier (limite du serveur web ?)"));
        }

        AjaxHelper::success();
    }
}

function clean($string)
{
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}
