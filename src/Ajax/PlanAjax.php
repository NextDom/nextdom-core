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
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PlanManager;
use NextDom\Model\Entity\Plan;
use NextDom\Model\Entity\PlanHeader;

class PlanAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $plans = json_decode(Utils::init('plans'), true);
        foreach ($plans as $plan_ajax) {
            @$plan = PlanManager::byId($plan_ajax['id']);
            if (!is_object($plan)) {
                $plan = new Plan();
            }
            Utils::a2o($plan, NextDomHelper::fromHumanReadable($plan_ajax));
            $plan->save();
        }
        AjaxHelper::success();
    }

    public function execute()
    {
        $plan = PlanManager::byId(Utils::init('id'));
        if (!is_object($plan)) {
            throw new CoreException(__('Aucun plan correspondant'));
        }
        AjaxHelper::success($plan->execute());
    }

    public function planHeader()
    {
        $return = array();
        /**
         * @var Plan $plan
         */
        foreach (PlanManager::byPlanHeaderId(Utils::init('planHeader_id')) as $plan) {
            $result = $plan->getHtml(Utils::init('version'));
            if (is_array($result)) {
                $return[] = $result;
            }
        }
        AjaxHelper::success($return);
    }

    public function create()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        if (Utils::init('plan', '') === '') {
            throw new CoreException(__('L\'identifiant du plan doit être fourni', __FILE__));
        }
        $plan = new Plan();
        Utils::a2o($plan, json_decode(Utils::init('plan'), true));
        $plan->save();
        AjaxHelper::success($plan->getHtml(Utils::init('version')));
    }

    public function copy()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $plan = PlanManager::byId(Utils::init('id'));
        if (!is_object($plan)) {
            throw new CoreException(__('Aucun plan correspondant'));
        }
        AjaxHelper::success($plan->copy()->getHtml(Utils::init('version', 'dplan')));
    }

    public function get()
    {
        $plan = PlanManager::byId(Utils::init('id'));
        if (!is_object($plan)) {
            throw new CoreException(__('Aucun plan correspondant'));
        }
        AjaxHelper::success(NextDomHelper::toHumanReadable(Utils::o2a($plan)));
    }

    public function remove()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $plan = PlanManager::byId(Utils::init('id'));
        if (!is_object($plan)) {
            throw new CoreException(__('Aucun plan correspondant'));
        }
        AjaxHelper::success($plan->remove());
    }

    public function removePlanHeader()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $planHeader = PlanHeaderManager::byId(Utils::init('id'));
        if (!is_object($planHeader)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $planHeader->remove();
        AjaxHelper::success();
    }

    public function allHeader()
    {
        $planHeaders = PlanHeaderManager::all();
        $return = array();
        foreach ($planHeaders as $planHeader) {
            $info_planHeader = Utils::o2a($planHeader);
            unset($info_planHeader['image']);
            $return[] = $info_planHeader;
        }
        AjaxHelper::success($return);
    }

    public function getPlanHeader()
    {
        $planHeader = PlanHeaderManager::byId(Utils::init('id'));
        if (!is_object($planHeader)) {
            throw new CoreException(__('Plan header inconnu. Vérifiez l\'ID ') . Utils::init('id'));
        }
        if (trim($planHeader->getConfiguration('accessCode', '')) != '' && $planHeader->getConfiguration('accessCode', '') != sha512(Utils::init('code'))) {
            throw new CoreException(__('Code d\'acces invalide'), -32005);
        }
        $return = Utils::o2a($planHeader);
        $return['image'] = $planHeader->displayImage();
        AjaxHelper::success($return);
    }

    public function savePlanHeader()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $planHeader_ajax = json_decode(Utils::init('planHeader'), true);
        $planHeader = null;
        if (isset($planHeader_ajax['id'])) {
            $planHeader = PlanHeaderManager::byId($planHeader_ajax['id']);
        }
        if (!is_object($planHeader)) {
            $planHeader = new PlanHeader();
        }
        Utils::a2o($planHeader, $planHeader_ajax);
        $planHeader->save();
        AjaxHelper::success(Utils::o2a($planHeader));
    }

    public function copyPlanHeader()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $planHeader = PlanHeaderManager::byId(Utils::init('id'));
        if (!is_object($planHeader)) {
            throw new CoreException(__('Plan header inconnu. Vérifiez l\'ID ') . Utils::init('id'));
        }
        AjaxHelper::success(Utils::o2a($planHeader->copy(Utils::init('name'))));
    }

    public function removeImageHeader()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $planHeader = PlanHeaderManager::byId(Utils::init('id'));
        if (!is_object($planHeader)) {
            throw new CoreException(__('Plan header inconnu. Vérifiez l\'ID ') . Utils::init('id'));
        }
        $filename = 'planHeader' . $planHeader->getId() . '-' . $planHeader->getImage('sha512') . '.' . $planHeader->getImage('type');
        $planHeader->setImage('sha512', '');
        $planHeader->save();
        @unlink(NEXTDOM_DATA . '/data/plan/' . $filename);
        AjaxHelper::success();
    }

    public function uploadImage()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $planHeader = PlanHeaderManager::byId(Utils::init('id'));
        if (!is_object($planHeader)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        if (!isset($_FILES['file'])) {
            throw new CoreException(__('Aucun fichier trouvé. Vérifiez le paramètre PHP (post size limit)'));
        }
        $extension = strtolower(strrchr($_FILES['file']['name'], '.'));
        if (!in_array($extension, array('.jpg', '.png'))) {
            throw new CoreException('Extension du fichier non valide (autorisé .jpg .png) : ' . $extension);
        }
        if (filesize($_FILES['file']['tmp_name']) > 5000000) {
            throw new CoreException(__('Le fichier est trop gros (maximum 5Mo)'));
        }
        $files = FileSystemHelper::ls(NEXTDOM_DATA . '/data/plan/', 'plan' . $planHeader->getId() . '*');
        if (count($files) > 0) {
            foreach ($files as $file) {
                unlink(NEXTDOM_DATA . '/data/plan/' . $file);
            }
        }
        $img_size = getimagesize($_FILES['file']['tmp_name']);
        $planHeader->setImage('type', str_replace('.', '', $extension));
        $planHeader->setImage('size', $img_size);
        $planHeader->setImage('sha512', sha512($planHeader->getImage('data')));
        $filename = 'planHeader' . $planHeader->getId() . '-' . $planHeader->getImage('sha512') . '.' . $planHeader->getImage('type');
        $filepath = NEXTDOM_DATA . '/data/plan/' . $filename;
        file_put_contents($filepath, file_get_contents($_FILES['file']['tmp_name']));
        if (!file_exists($filepath)) {
            throw new CoreException(__('Impossible de sauvegarder l\'image', __FILE__));
        }
        $planHeader->setConfiguration('desktopSizeX', $img_size[0]);
        $planHeader->setConfiguration('desktopSizeY', $img_size[1]);
        $planHeader->save();
        AjaxHelper::success();
    }

    public function uploadImagePlan()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        Utils::unautorizedInDemo();
        $plan = PlanManager::byId(Utils::init('id'));
        if (false == is_object($plan)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $uploadDir = sprintf("%s/public/img/plan_%s", NEXTDOM_ROOT, $plan->getId());
        shell_exec('rm -rf ' . $uploadDir);
        mkdir($uploadDir, 0775, true);
        $filename = Utils::readUploadedFile($_FILES, "file", $uploadDir, 5, array(".png", ".jpg"), function ($file) {
            $content = file_get_contents($file['tmp_name']);
            return sha512(base64_encode($content));
        });
        $filepath = sprintf("%s/%s", $uploadDir, $filename);
        $img_size = getimagesize($filepath);
        $plan->setDisplay('width', $img_size[0]);
        $plan->setDisplay('height', $img_size[1]);
        $plan->setDisplay('path', 'public/img/plan_' . $plan->getId() . '/' . $name);
        $plan->save();
        AjaxHelper::success();
    }
}
