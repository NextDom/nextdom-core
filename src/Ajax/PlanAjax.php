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
use NextDom\Enums\Common;
use NextDom\Enums\NextDomFolder;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\PlanHeaderManager;
use NextDom\Managers\PlanManager;
use NextDom\Model\DataClass\UploadedImage;
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
        $planHeader->setImage('data', '');
        $planHeader->save();
        @unlink(NEXTDOM_DATA . '/data/custom/plans/' . $filename);
        $this->ajax->success();
    }

    /**
     * Get data of uploaded file
     *
     * @return UploadedImage
     * @throws CoreException
     */
    private function getUploadedImageData()
    {
        $uploadedImageData = new UploadedImage();
        if (!isset($_FILES['file'])) {
            throw new CoreException(__('Aucun fichier trouvé. Vérifiez le paramètre PHP (post size limit)'));
        }
        $extension = strtolower(strrchr($_FILES['file']['name'], '.'));
        $uploadedImageData->setType(substr($extension, 1));
        if (!in_array($extension, ['.jpg', '.jpeg', '.png'])) {
            throw new CoreException('Extension du fichier non valide (autorisé .jpg .jpeg .png) : ' . $extension);
        }
        if (filesize($_FILES['file']['tmp_name']) > 5000000) {
            throw new CoreException(__('Le fichier est trop gros (maximum 5Mo)'));
        }
        $uploadedImageData->setSize(getimagesize($_FILES['file']['tmp_name']));
        $fileContent = file_get_contents($_FILES['file']['tmp_name']);
        $uploadedImageData->setHash(Utils::sha512($fileContent));
        $uploadedImageData->setPath($_FILES['file']['tmp_name']);
        $uploadedImageData->setData(base64_encode($fileContent));
        return $uploadedImageData;
    }

    /**
     * Check file path and move file
     *
     * @param $uploadFile
     * @param $targetPath
     *
     * @throws CoreException
     */
    private function checkAndMoveUploadImage($uploadFile, $targetPath)
    {
        // Check $targetPath don't go up
        if (preg_match('/.*(\.\.\/)|(\/\.\.).*/', $targetPath) !== 0) {
            throw new CoreException(__('Le répertoire de destination n\'est pas valide'));
        }
        if (!move_uploaded_file($uploadFile, $targetPath)) {
            throw new CoreException(__('Impossible de sauvegarder l\'image'));
        }
    }

    /**
     * Upload background picture on plan
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function uploadImage()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $planHeader = PlanHeaderManager::byId(Utils::init(AjaxParams::ID));
        if (!is_dir(NextDomFolder::PLAN_IMAGE)) {
            mkdir(NextDomFolder::PLAN_IMAGE, 0755, true);
        }
        if (!is_object($planHeader)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $uploadedImageData = $this->getUploadedImageData();
        PlanHeaderManager::cleanPlanImageFolder($planHeader->getId());
        $planHeader->setImage('type', $uploadedImageData->getType());
        $planHeader->setImage('size', $uploadedImageData->getSize());
        $planHeader->setImage('sha512', $uploadedImageData->getHash());
        $planHeader->setImage('data', $uploadedImageData->getData());
        $destFilename = NextDomObj::PLAN_HEADER . $planHeader->getId() . '-' . $uploadedImageData->getHash() . '.' . $uploadedImageData->getType();
        $this->checkAndMoveUploadImage($uploadedImageData->getPath(), NextDomFolder::PLAN_IMAGE . $destFilename);
        $planHeader->setConfiguration('desktopSizeX', $uploadedImageData->getSizeX());
        $planHeader->setConfiguration('desktopSizeY', $uploadedImageData->getSizeY());
        $planHeader->save();
        $this->ajax->success();
    }

    /**
     * Upload image for static picture on plan
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function uploadImagePlan()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $plan = PlanManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($plan)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $uploadedImageData = $this->getUploadedImageData();
        $destPath = NextDomFolder::PLAN_IMAGE . 'plan_' . $plan->getId();
        shell_exec('rm -rf ' . $destPath);
        mkdir($destPath, 0775, true);
        $destFilename = sha512($uploadedImageData->getData()) . '.' . $uploadedImageData->getType();
        $this->checkAndMoveUploadImage($uploadedImageData->getPath(), $destPath . '/' . $destFilename);
        $plan->setDisplay('width', $uploadedImageData->getSizeX());
        $plan->setDisplay('height', $uploadedImageData->getSizeY());
        $plan->setDisplay('path', 'data/custom/plans/plan_' . $plan->getId() . '/' . $destFilename);
        $plan->save();
        $this->ajax->success();
    }
}
