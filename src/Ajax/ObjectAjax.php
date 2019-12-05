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
use NextDom\Enums\EqLogicViewType;
use NextDom\Enums\NextDomObj;
use NextDom\Enums\UserRight;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\ScenarioManager;
use NextDom\Model\Entity\EqLogic;
use NextDom\Model\Entity\JeeObject;
use NextDom\Model\Entity\Scenario;

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
        if (isset($jsonObject['id'])) {
            $resultObject = JeeObjectManager::byId($jsonObject['id']);
        }
        if (!isset($resultObject) || !is_object($resultObject)) {
            $resultObject = new JeeObject();
        }
        Utils::a2o($resultObject, NextDomHelper::fromHumanReadable($jsonObject));
        if ($resultObject->getName() !== '') {
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
        if ($objectId == '' || $objectId == 'all' || is_json($objectId)) {
            if (is_json($objectId)) {
                $objectsList = json_decode($objectId, true);
            } else {
                $objectsList = [];
                foreach (JeeObjectManager::all() as $resultObject) {
                    if ($resultObject->getConfiguration('hideOnDashboard', 0) == 1) {
                        continue;
                    }
                    $objectsList[] = $resultObject->getId();
                }
            }
            $result = [];
            $scenariosResult = [];
            $i = 0;
            foreach ($objectsList as $id) {
                $html = [];
                if (Utils::init(AjaxParams::SUMMARY) == '') {
                    $eqLogics = EqLogicManager::byObjectId($id, true, true);
                } else {
                    $resultObject = JeeObjectManager::byId($id);
                    $eqLogics = $resultObject->getEqLogicBySummary(Utils::init(AjaxParams::SUMMARY), true, false);
                }
                $this->toHtmlEqLogics($html, $eqLogics);
                $scenarios = ScenarioManager::byObjectId($id, false, true);
                if (count($scenarios) > 0) {
                    $scenariosResult[$i . '::' . $id] = [];
                    /**
                     * @var Scenario $scenario
                     */
                    foreach ($scenarios as $scenario) {
                        $scenariosResult[$i . '::' . $id][] = [
                            'id' => $scenario->getId(),
                            'state' => $scenario->getState(),
                            'name' => $scenario->getName(),
                            'icon' => $scenario->getDisplay(Common::ICON),
                            'active' => $scenario->getIsActive()
                        ];
                    }
                }
                ksort($html);
                $result[$i . '::' . $id] = implode($html);
                $i++;
            }
            $this->ajax->success([Common::OBJECT_HTML => $result, NextDomObj::SCENARIOS => $scenariosResult]);
        } else {
            $objectId = intval($objectId);
            $html = [];
            if (Utils::init(AjaxParams::SUMMARY) == '') {
                $eqLogics = EqLogicManager::byObjectId($objectId, true, true);
            } else {
                $resultObject = JeeObjectManager::byId($objectId);
                $eqLogics = $resultObject->getEqLogicBySummary($objectId, true, false);
            }
            $this->toHtmlEqLogics($html, $eqLogics);
            $scenarios = ScenarioManager::byObjectId($objectId, false, true);
            $scenariosResult = [];
            if (count($scenarios) > 0) {
                /**
                 * @var Scenario $scenario
                 */
                foreach ($scenarios as $scenario) {
                    $scenariosResult[] = [
                        'id' => $scenario->getId(),
                        'state' => $scenario->getState(),
                        'name' => $scenario->getName(),
                        'icon' => $scenario->getDisplay('icon'),
                        'active' => $scenario->getIsActive()
                    ];
                }
            }
            ksort($html);
            $this->ajax->success([Common::OBJECT_HTML => implode($html), NextDomObj::SCENARIOS => $scenariosResult]);
        }
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
                        Common::ID => Common::GLOBAL
                    ];
                    continue;
                }
                $resultObject = JeeObjectManager::byId($id);
                if (!is_object($resultObject)) {
                    continue;
                }
                $result[$resultObject->getId()] = [
                    Common::HTML => $resultObject->getHtmlSummary($value[Common::VERSION]),
                    Common::ID => $resultObject->getId()
                ];
            }
            $this->ajax->success($result);
        } else {
            $resultObject = JeeObjectManager::byId(Utils::initInt(AjaxParams::ID));
            if (!is_object($resultObject)) {
                throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
            }
            $infoObject = [];
            $infoObject[Common::ID] = $resultObject->getId();
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
        $resultObject->setImage('data', '');
        $resultObject->setImage('sha512', '');
        $resultObject->save();
        @rrmdir(NEXTDOM_ROOT . '/core/img/object');
        $this->ajax->success();
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
        if (!is_object($resultObject)) {
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
        $files = FileSystemHelper::ls(NEXTDOM_DATA . '/data/object/', 'object' . $resultObject->getId() . '*');
        if (count($files) > 0) {
            foreach ($files as $file) {
                unlink(NEXTDOM_DATA . '/data/object/' . $file);
            }
        }
        $resultObject->setImage('type', str_replace('.', '', $extension));
        $resultObject->setImage('data', base64_encode(file_get_contents($_FILES['file']['tmp_name'])));
        $resultObject->setImage('sha512', sha512($resultObject->getImage('data')));
        $filename = 'object' . $resultObject->getId() . '-' . $resultObject->getImage('sha512') . '.' . $resultObject->getImage('type');
        $dir = NEXTDOM_DATA . '/data/custom/object/';
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        $filepath = $dir . $filename;
        file_put_contents($filepath, file_get_contents($_FILES['file']['tmp_name']));
        if (!file_exists($filepath)) {
            throw new CoreException(__('Impossible de sauvegarder l\'image'));
        }
        $resultObject->save();
        $this->ajax->success();
    }

}
