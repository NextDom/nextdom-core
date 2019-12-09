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
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PlanManager;
use NextDom\Model\Entity\Plan;
use NextDom\Model\Entity\PlanHeader;

/**
 * Class PlanAjax
 * @package NextDom\Ajax
 */
class PlanAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plans = json_decode(Utils::init('plans'), true);
        foreach ($plans as $plan_ajax) {
            @$plan = PlanManager::byId($plan_ajax['id']);
            if (!is_object($plan)) {
                $plan = new Plan();
            }
            Utils::a2o($plan, NextDomHelper::fromHumanReadable($plan_ajax));
            $plan->save();
        }
        $this->ajax->success();
    }

    public function execute()
    {
        $plan = PlanManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plan)) {
            throw new CoreException(__('Aucun plan correspondant'));
        }
        $this->ajax->success($plan->execute());
    }

    public function planHeader()
    {
        $return = [];
        /**
         * @var Plan $plan
         */
        foreach (PlanManager::byPlanHeaderId(Utils::init('planHeader_id')) as $plan) {
            $result = $plan->getHtml(Utils::init(AjaxParams::VERSION));
            if (is_array($result)) {
                $return[] = $result;
            }
        }
        $this->ajax->success($return);
    }

    public function create()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        if (Utils::init('plan', '') === '') {
            throw new CoreException(__('L\'identifiant du plan doit être fourni'));
        }
        $plan = new Plan();
        Utils::a2o($plan, json_decode(Utils::init('plan'), true));
        $plan->save();
        $this->ajax->success($plan->getHtml(Utils::init(AjaxParams::VERSION)));
    }

    public function copy()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plan = PlanManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plan)) {
            throw new CoreException(__('Aucun plan correspondant'));
        }
        $this->ajax->success($plan->copy()->getHtml(Utils::init(AjaxParams::VERSION, 'dplan')));
    }

    public function get()
    {
        $plan = PlanManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plan)) {
            throw new CoreException(__('Aucun plan correspondant'));
        }
        $this->ajax->success(NextDomHelper::toHumanReadable(Utils::o2a($plan)));
    }

    public function remove()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plan = PlanManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plan)) {
            throw new CoreException(__('Aucun plan correspondant'));
        }
        $this->ajax->success($plan->remove());
    }

    public function removePlanHeader()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $planHeader = PlanHeaderManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($planHeader)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $planHeader->remove();
        $this->ajax->success();
    }

    public function allHeader()
    {
        $planHeaders = PlanHeaderManager::all();
        $result = [];
        foreach ($planHeaders as $planHeader) {
            $info_planHeader = Utils::o2a($planHeader);
            unset($info_planHeader['image']);
            $result[] = $info_planHeader;
        }
        $this->ajax->success($result);
    }

    public function getPlanHeader()
    {
        $planHeader = PlanHeaderManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($planHeader)) {
            throw new CoreException(__('Plan header inconnu. Vérifiez l\'ID ') . Utils::init(AjaxParams::ID));
        }
        if (trim($planHeader->getConfiguration('accessCode', '')) != '' && $planHeader->getConfiguration('accessCode', '') != sha512(Utils::init('code'))) {
            throw new CoreException(__('Code d\'acces invalide'), -32005);
        }
        $result = Utils::o2a($planHeader);
        $result['image'] = $planHeader->displayImage();
        $this->ajax->success($result);
    }

    public function savePlanHeader()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
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
        $this->ajax->success(Utils::o2a($planHeader));
    }

    public function copyPlanHeader()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $planHeader = PlanHeaderManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($planHeader)) {
            throw new CoreException(__('Plan header inconnu. Vérifiez l\'ID ') . Utils::init(AjaxParams::ID));
        }
        $this->ajax->success(Utils::o2a($planHeader->copy(Utils::init('name'))));
    }

    public function removeImageHeader()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $planHeader = PlanHeaderManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($planHeader)) {
            throw new CoreException(__('Plan header inconnu. Vérifiez l\'ID ') . Utils::init(AjaxParams::ID));
        }
        $filename = 'planHeader' . $planHeader->getId() . '-' . $planHeader->getImage('sha512') . '.' . $planHeader->getImage('type');
        $planHeader->setImage('sha512', '');
        $planHeader->save();
        @unlink(NEXTDOM_DATA . '/data/custom/plans/' . $filename);
        $this->ajax->success();
    }

    public function uploadImage()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $planHeader = PlanHeaderManager::byId(Utils::init(AjaxParams::ID));
        if (!is_dir(NEXTDOM_DATA . '/data/custom/plans/')) {
            mkdir(NEXTDOM_DATA . '/data/custom/plans/');
        }
        if (!is_object($planHeader)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        if (!isset($_FILES['file'])) {
            throw new CoreException(__('Aucun fichier trouvé. Vérifiez le paramètre PHP (post size limit)'));
        }
        $extension = strtolower(strrchr($_FILES['file']['name'], '.'));
        if (!in_array($extension, ['.jpg', '.jpeg', '.png'])) {
            throw new CoreException('Extension du fichier non valide (autorisé .jpg .jpeg .png) : ' . $extension);
        }
        if (filesize($_FILES['file']['tmp_name']) > 5000000) {
            throw new CoreException(__('Le fichier est trop gros (maximum 5Mo)'));
        }
        $files = FileSystemHelper::ls(NEXTDOM_DATA . '/data/custom/plans/', 'plan' . $planHeader->getId() . '*');
        if (count($files) > 0) {
            foreach ($files as $file) {
                unlink(NEXTDOM_DATA . '/data/custom/plans/' . $file);
            }
        }
        $imgSize = getimagesize($_FILES['file']['tmp_name']);
        $fileContent = file_get_contents($_FILES['file']['tmp_name']);
        $sha512File = sha512($fileContent);
        $planHeader->setImage('type', str_replace('.', '', $extension));
        $planHeader->setImage('size', $imgSize);
        $planHeader->setImage('sha512', $sha512File);
        $planHeader->setImage('data', base64_encode($fileContent));
        $filename = 'planHeader' . $planHeader->getId() . '-' . $sha512File . '.' . $planHeader->getImage('type');
        $filepath = NEXTDOM_DATA . '/data/custom/plans/' . $filename;
        copy($_FILES['file']['tmp_name'], $filepath);
        if (!file_exists($filepath)) {
            throw new CoreException(__('Impossible de sauvegarder l\'image'));
        }
        $planHeader->setConfiguration('desktopSizeX', $imgSize[0]);
        $planHeader->setConfiguration('desktopSizeY', $imgSize[1]);
        $planHeader->save();
        $this->ajax->success();
    }

    public function uploadImagePlan()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plan = PlanManager::byId(Utils::init(AjaxParams::ID));
        if (false == is_object($plan)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $uploadDir = sprintf("%s/data/custom/plans/plan_%s", NEXTDOM_DATA, $plan->getId());
        shell_exec('rm -rf ' . $uploadDir);
        mkdir($uploadDir, 0775, true);
        $filename = Utils::readUploadedFile($_FILES, "file", $uploadDir, 5, ['.png', '.jpeg', '.jpg'], function ($file) {
            $content = file_get_contents($file['tmp_name']);
            return sha512(base64_encode($content));
        });
        $filepath = sprintf("%s/%s", $uploadDir, $filename);
        $imgSize = getimagesize($filepath);
        $plan->setDisplay('width', $imgSize[0]);
        $plan->setDisplay('height', $imgSize[1]);
        $plan->setDisplay('path', 'data/custom/plans/plan_' . $plan->getId() . '/' . $filename);
        $plan->save();
        $this->ajax->success();
    }
}
