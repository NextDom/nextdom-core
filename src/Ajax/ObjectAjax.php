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
use NextDom\Enums\NextDomFolder;
use NextDom\Enums\Common;
use NextDom\Enums\EqLogicViewType;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Model\Entity\EqLogic;
use NextDom\Model\Entity\JeeObject;
use NextDom\Model\DataClass\UploadedImage;

/**
 * Class ObjectAjax
 * @package NextDom\Ajax
 */
class ObjectAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    /**
     * Remove object
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function remove()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $resultObject = JeeObjectManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($resultObject)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $resultObject->remove();
        $this->ajax->success();
    }

    /**
     * Get object by his Id
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function byId()
    {
        $resultObject = JeeObjectManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($resultObject)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID ') . Utils::initInt(AjaxParams::ID));
        }
        $this->ajax->success(NextDomHelper::toHumanReadable(Utils::o2a($resultObject)));
    }

    public function createSummaryVirtual()
    {
        JeeObjectManager::createSummaryToVirtual(Utils::init('key'));
        $this->ajax->success();
    }

    /**
     * Get all objects
     *
     * @throws \ReflectionException
     */
    public function all()
    {
        $resultObjects = JeeObjectManager::buildTree();
        if (Utils::init('onlyHasEqLogic') != '') {
            $result = [];
            foreach ($resultObjects as $resultObject) {
                if (count($resultObject->getEqLogic(true, false, Utils::init('onlyHasEqLogic'), null, Utils::init('searchOnchild', true))) == 0) {
                    continue;
                }
                $result[] = $resultObject;
            }
            $resultObjects = $result;
        }
        $this->ajax->success(Utils::o2a($resultObjects));
    }

    /**
     * Save object passed in json format
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $jsonObject = json_decode(Utils::init(AjaxParams::OBJECT), true);
        if (isset($jsonObject[AjaxParams::ID])) {
            $resultObject = JeeObjectManager::byId($jsonObject[AjaxParams::ID]);
        }
        if (!isset($resultObject) || !is_object($resultObject)) {
            $resultObject = new JeeObject();
        }
        Utils::a2o($resultObject, NextDomHelper::fromHumanReadable($jsonObject));
        if (!empty($resultObject->getName())) {
            $resultObject->save();
            $this->ajax->success(Utils::o2a($resultObject));
        }
        $this->ajax->error('Le nom de l\'objet ne peut être vide');
    }

    /**
     * Get child objects
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function getChild()
    {
        $resultObject = JeeObjectManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($resultObject)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $result = Utils::o2a($resultObject->getChild());
        $this->ajax->success($result);
    }

    /**
     * Get HTML representation of the object
     *
     * @throws \Exception
     */
    public function toHtml()
    {
        $objectId = Utils::init(AjaxParams::ID);
        if ($objectId == '' || $objectId == 'all' || Utils::isJson($objectId)) {
            if (Utils::isJson($objectId)) {
                $objectsList = json_decode($objectId, true);
            } else {
                $objectsList = [];
                if (Utils::init(AjaxParams::SUMMARY) == '') {
                    foreach (JeeObjectManager::buildTree(null, true) as $objectId) {
                        if ($objectId->getConfiguration('hideOnDashboard', 0) == 1) {
                            continue;
                        }
                        $objects[] = $objectId->getId();
                    }
                } else {
                    foreach (JeeObjectManager::all() as $objectId) {
                        $objects[] = $objectId->getId();
                    }
                }
            }
            $result = [];
            $scenariosResult = [];
            $i = 0;
            foreach ($objectsList as $objectId) {
                $objectId = intval($objectId);
                $htmlObject = $this->renderObjectAsHtml($objectId);
                $scenariosResult = array_merge($scenariosResult, $this->renderScenariosAsHtml($objectId));
                $result[$i . '::' . $objectId] = implode($htmlObject);
                $i++;
            }
            $this->ajax->success([Common::OBJECT_HTML => $result, NextDomObj::SCENARIOS => $scenariosResult]);
        } else {
            $objectId = intval($objectId);
            $htmlObject = $this->renderObjectAsHtml($objectId);
            $scenariosObject = $this->renderScenariosAsHtml($objectId);
            $this->ajax->success([Common::OBJECT_HTML => implode($htmlObject), NextDomObj::SCENARIOS => $scenariosObject]);
        }
    }

    private function renderObjectAsHtml($objectId)
    {
        $result = [];
        if (Utils::init(AjaxParams::SUMMARY) == '') {
            $eqLogics = EqLogicManager::byObjectId($objectId, true, true);
        } else {
            $resultObject = JeeObjectManager::byId($objectId);
            $eqLogics = $resultObject->getEqLogicBySummary($objectId, true, false);
        }
        $this->toHtmlEqLogics($result, $eqLogics);
        ksort($result);
        return $result;
    }

    private function renderScenariosAsHtml($objectId)
    {
        $scenarios = ScenarioManager::byObjectId($objectId, false, true);
        $result = [];
        if (count($scenarios) > 0) {
            /** @var Scenario $scenario */
            foreach ($scenarios as $scenario) {
                $result[] = [
                    'scenario_id' => $scenario->getId(),
                    'state' => $scenario->getState(),
                    'name' => $scenario->getName(),
                    'icon' => $scenario->getDisplay('icon'),
                    'active' => $scenario->getIsActive()
                ];
            }
        }
        return $result;
    }

    /**
     * Test if eqLogic must be ignored
     *
     * @param EqLogic $eqLogic EqLogic to test
     * @param string $category Filter by category
     * @param string $tag Filter by tag
     *
     * @return bool True if eqLogic must be ignored
     */
    private function toHtmlIgnoreEqLogics($eqLogic, $category, $tag)
    {
        if ($eqLogic === null ||
            ($category != Common::ALL && $eqLogic->getCategory($category) != 1) ||
            ($tag != Common::ALL && strpos($eqLogic->getTags(), $tag) === false)) {
            return true;
        }
        return false;
    }

    /**
     * Get HTML render of eqLogics
     *
     * @param array $html Render buffer
     * @param Eqlogic[] $eqLogics List of eqLogics to render
     *
     * @throws CoreException
     * @throws \NextDom\Exceptions\OperatingSystemException
     * @throws \ReflectionException
     */
    private function toHtmlEqLogics(&$html, $eqLogics)
    {
        if (count($eqLogics) > 0) {
            $category = Utils::init(Common::CATEGORY, Common::ALL);
            $tag = Utils::init(Common::TAG, Common::ALL);
            $version = Utils::init(AjaxParams::VERSION, EqLogicViewType::DASHBOARD);
            foreach ($eqLogics as $eqLogic) {
                if ($this->toHtmlIgnoreEqLogics($eqLogic, $category, $tag)) {
                    continue;
                }
                $order = $eqLogic->getOrder();
                while (isset($html[$order])) {
                    $order++;
                }
                $html[$order] = $eqLogic->toHtml($version);
            }
        }
    }

    public function setOrder()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $position = 1;
        foreach (json_decode(Utils::init(AjaxParams::OBJECTS), true) as $id) {
            $resultObject = JeeObjectManager::byId($id);
            if (is_object($resultObject)) {
                $resultObject->setPosition($position);
                $resultObject->save();
                $position++;
            }
        }
        $this->ajax->success();
    }

    /**
     * Get HTML of the summary
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function getSummaryHtml()
    {
        if (Utils::init(Utils::init(AjaxParams::IDS)) != '') {
            $result = [];
            foreach (json_decode(Utils::init(AjaxParams::IDS), true) as $id => $value) {
                if ($id == Common::GLOBAL) {
                    $result[Common::GLOBAL] = [
                        Common::HTML => JeeObjectManager::getGlobalHtmlSummary($value[Common::VERSION]),
                        AjaxParams::ID => Common::GLOBAL
                    ];
                    continue;
                }
                $resultObject = JeeObjectManager::byId($id);
                if (!is_object($resultObject)) {
                    continue;
                }
                $result[$resultObject->getId()] = [
                    Common::HTML => $resultObject->getHtmlSummary($value[Common::VERSION]),
                    AjaxParams::ID => $resultObject->getId()
                ];
            }
            $this->ajax->success($result);
        } else {
            $resultObject = JeeObjectManager::byId(Utils::initInt(AjaxParams::ID));
            if (!is_object($resultObject)) {
                throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
            }
            $infoObject = [];
            $infoObject[AjaxParams::ID] = $resultObject->getId();
            $infoObject[Common::HTML] = $resultObject->getHtmlSummary(Utils::init(AjaxParams::VERSION));
            $this->ajax->success($infoObject);
        }
    }

    /**
     * Remove image linked to an object
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function removeImage()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $resultObject = JeeObjectManager::byId(Utils::initInt(AjaxParams::ID));
        if (!is_object($resultObject)) {
            throw new CoreException(__('Vue inconnu. Vérifiez l\'ID ') . Utils::initInt(AjaxParams::ID));
        }
        $resultObject->setImage('sha512', '');
        $resultObject->save();
        @unlink(NEXTDOM_DATA . '/' . $resultObject->getImgLink());
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
     * Link an image to the object
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function uploadImage()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $resultObject = JeeObjectManager::byId(Utils::initInt(AjaxParams::ID));

        if (!is_dir(NextDomFolder::PLAN_OBJECT)) {
            mkdir(NextDomFolder::PLAN_OBJECT, 0755, true);
        }
        if (!is_object($resultObject)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $uploadedImageData = $this->getUploadedImageData();
        JeeObjectManager::cleanPlanImageFolder($resultObject->getId());
        $resultObject->setImage('type', $uploadedImageData->getType());
        $resultObject->setImage('size', $uploadedImageData->getSize());
        $resultObject->setImage('sha512', $uploadedImageData->getHash());
        $destFilename = NextDomObj::PLAN_OBJECT . $resultObject->getId() . '-' . $uploadedImageData->getHash() . '.' . $uploadedImageData->getType();
        $this->checkAndMoveUploadImage($uploadedImageData->getPath(), NextDomFolder::PLAN_OBJECT . $destFilename);
        $resultObject->setConfiguration('desktopSizeX', $uploadedImageData->getSizeX());
        $resultObject->setConfiguration('desktopSizeY', $uploadedImageData->getSizeY());
        $resultObject->save();
        $this->ajax->success();
    }

}
