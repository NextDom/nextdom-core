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
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\Plan3dHeaderManager;
use NextDom\Managers\Plan3dManager;
use NextDom\Model\Entity\Plan3d;
use NextDom\Model\Entity\Plan3dHeader;
use ZipArchive;

/**
 * Class Plan3dAjax
 * @package NextDom\Ajax
 */
class Plan3dAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plan3ds = json_decode(Utils::init('plan3ds'), true);
        foreach ($plan3ds as $plan3d_ajax) {
            @$plan3d = Plan3dManager::byId($plan3d_ajax['id']);
            if (!is_object($plan3d)) {
                $plan3d = new plan3d();
            }
            Utils::a2o($plan3d, NextDomHelper::fromHumanReadable($plan3d_ajax));
            $plan3d->save();
        }
        $this->ajax->success();
    }

    public function plan3dHeader()
    {
        $return = [];
        /**
         * @var Plan3d $plan3d
         */
        foreach (Plan3dManager::byPlan3dHeaderId(Utils::init('plan3dHeader_id')) as $plan3d) {
            $info = Utils::o2a($plan3d);
            $info['additionalData'] = $plan3d->additionalData();
            $return[] = $info;
        }
        $this->ajax->success($return);
    }

    public function create()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        if (Utils::init('plan3d', '') === '') {
            throw new CoreException(__('L\'identifiant du plan doit être fourni'));
        }
        $plan3d = new Plan3d();
        Utils::a2o($plan3d, json_decode(Utils::init('plan3d'), true));
        $plan3d->save();
        $this->ajax->success($plan3d->getHtml(Utils::init(AjaxParams::VERSION)));
    }

    public function get()
    {
        $plan3d = Plan3dManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plan3d)) {
            throw new CoreException(__('Aucun plan3d correspondant'));
        }
        $return = NextDomHelper::toHumanReadable(Utils::o2a($plan3d));
        $return['additionalData'] = $plan3d->additionalData();
        $this->ajax->success($return);
    }

    public function byName()
    {
        $plan3d = Plan3dManager::byName3dHeaderId(Utils::init(AjaxParams::NAME), Utils::init('plan3dHeader_id'));
        if (!is_object($plan3d)) {
            $this->ajax->success();
        }
        $this->ajax->success($plan3d->getHtml());
    }

    public function remove()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plan3d = Plan3dManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plan3d)) {
            throw new CoreException(__('Aucun plan3d correspondant'));
        }
        $this->ajax->success($plan3d->remove());
    }

    public function removeplan3dHeader()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plan3dHeader = Plan3dHeaderManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plan3dHeader)) {
            throw new CoreException(__('Objet inconnu verifiez l\'id'));
        }
        $plan3dHeader->remove();
        $this->ajax->success();
    }

    public function allHeader()
    {
        $plan3dHeaders = Plan3dHeaderManager::all();
        $return = [];
        foreach ($plan3dHeaders as $plan3dHeader) {
            $info_plan3dHeader = Utils::o2a($plan3dHeader);
            unset($info_plan3dHeader['image']);
            $return[] = $info_plan3dHeader;
        }
        $this->ajax->success($return);
    }

    public function getplan3dHeader()
    {
        $plan3dHeader = Plan3dHeaderManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plan3dHeader)) {
            throw new CoreException(__('plan3d header inconnu verifiez l\'id : ') . Utils::init(AjaxParams::ID));
        }
        if (trim($plan3dHeader->getConfiguration('accessCode', '')) != '' && $plan3dHeader->getConfiguration('accessCode', '') != sha512(Utils::init('code'))) {
            throw new CoreException(__('Code d\'acces invalide'), -32005);
        }
        $return = Utils::o2a($plan3dHeader);
        $this->ajax->success($return);
    }

    public function saveplan3dHeader()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plan3dHeader_ajax = json_decode(Utils::init('plan3dHeader'), true);
        $plan3dHeader = null;
        if (isset($plan3dHeader_ajax['id'])) {
            $plan3dHeader = Plan3dHeaderManager::byId($plan3dHeader_ajax['id']);
        }
        if (!is_object($plan3dHeader)) {
            $plan3dHeader = new Plan3dHeader();
        }
        Utils::a2o($plan3dHeader, $plan3dHeader_ajax);
        $plan3dHeader->save();
        $this->ajax->success(Utils::o2a($plan3dHeader));
    }

    public function uploadModel()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $uploadDir = '/tmp';
        $plan3dHeader = Plan3dHeaderManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plan3dHeader)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }

        $filename = Utils::readUploadedFile($_FILES, "file", $uploadDir, 150, [".zip"]);

        if ($plan3dHeader->getConfiguration('path') == '') {
            $path = sprintf("%s/data/3d/%s/", NEXTDOM_DATA, ConfigManager::genKey());
            $plan3dHeader->setConfiguration('path', $path);
        }

        $cibDir = NEXTDOM_ROOT . '/' . $plan3dHeader->getConfiguration('path');
        $zip = new ZipArchive;
        $filepath = sprintf("%s/%s", $uploadDir, $filename);
        $res = $zip->open($filepath);

        if ($res === TRUE) {
            if (!$zip->extractTo($cibDir . '/')) {
                throw new CoreException(__('Impossible de décompresser les fichiers : '));
            }
            $zip->close();
            unlink($filename);
        } else {
            throw new CoreException(__('Impossible de décompresser l\'archive zip : ') . $filename . ' => ' . ZipErrorMessage($res));
        }
        $objfile = FileSystemHelper::ls($cibDir, '*.obj', false, ['files']);
        if (count($objfile) != 1) {
            throw new CoreException(__('Il faut 1 seul et unique fichier .obj'));
        }
        $plan3dHeader->setConfiguration('objfile', $objfile[0]);
        $mtlfile = FileSystemHelper::ls($cibDir, '*.mtl', false, ['files']);
        if (count($mtlfile) == 1) {
            $plan3dHeader->setConfiguration('mtlfile', $mtlfile[0]);
        }
        $plan3dHeader->save();
        $this->ajax->success();
    }
}
