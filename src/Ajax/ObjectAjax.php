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
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\JeeObjectManager;
use NextDom\Managers\ScenarioManager;
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

    public function remove()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $resultObject = JeeObjectManager::byId(Utils::init('id'));
        if (!is_object($resultObject)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $resultObject->remove();
        $this->ajax->success();
    }

    public function byId()
    {
        $resultObject = JeeObjectManager::byId(Utils::init('id'));
        if (!is_object($resultObject)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID ') . Utils::init('id'));
        }
        $this->ajax->success(NextDomHelper::toHumanReadable(Utils::o2a($resultObject)));
    }

    public function createSummaryVirtual()
    {
        JeeObjectManager::createSummaryToVirtual(Utils::init('key'));
        $this->ajax->success();
    }

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

    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $jsonObject = json_decode(Utils::init('object'), true);
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

    public function getChild()
    {
        $resultObject = JeeObjectManager::byId(Utils::init('id'));
        if (!is_object($resultObject)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $result = Utils::o2a($resultObject->getChild());
        $this->ajax->success($result);
    }

    /**
     * Get HTML representation of the object
     *
     * @throws CoreException
     * @throws \NextDom\Exceptions\OperatingSystemException
     * @throws \ReflectionException
     */
    public function toHtml()
    {
        if (Utils::init('id') == '' || Utils::init('id') == 'all' || is_json(Utils::init('id'))) {
            if (is_json(Utils::init('id'))) {
                $objectsList = json_decode(Utils::init('id'), true);
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
                if (Utils::init('summary') == '') {
                    $eqLogics = EqLogicManager::byObjectId($id, true, true);
                } else {
                    $resultObject = JeeObjectManager::byId($id);
                    $eqLogics = $resultObject->getEqLogicBySummary(Utils::init('summary'), true, false);
                }
                if (count($eqLogics) > 0) {
                    foreach ($eqLogics as $eqLogic) {
                        if ($eqLogic === null) {
                            continue;
                        }
                        if (Utils::init('category', 'all') != 'all' && $eqLogic->getCategory(Utils::init('category')) != 1) {
                            continue;
                        }
                        if (Utils::init('tag', 'all') != 'all' && strpos($eqLogic->getTags(), Utils::init('tag')) === false) {
                            continue;
                        }
                        $order = $eqLogic->getOrder();
                        while (isset($html[$order])) {
                            $order++;
                        }
                        $html[$order] = $eqLogic->toHtml(Utils::init('version'));
                    }
                }
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
                            'icon' => $scenario->getDisplay('icon'),
                            'active' => $scenario->getIsActive()
                        ];
                    }
                }
                ksort($html);
                $result[$i . '::' . $id] = implode($html);
                $i++;
            }
            $this->ajax->success(['objectHtml' => $result, 'scenarios' => $scenariosResult]);
        } else {
            $html = [];
            if (Utils::init('summary') == '') {
                $eqLogics = EqLogicManager::byObjectId(Utils::init('id'), true, true);
            } else {
                $resultObject = JeeObjectManager::byId(Utils::init('id'));
                $eqLogics = $resultObject->getEqLogicBySummary(Utils::init('summary'), true, false);
            }
            if (count($eqLogics) > 0) {
                foreach ($eqLogics as $eqLogic) {
                    if ($eqLogic === null) {
                        continue;
                    }
                    if (Utils::init('category', 'all') != 'all' && $eqLogic->getCategory(Utils::init('category')) != 1) {
                        continue;
                    }
                    if (Utils::init('tag', 'all') != 'all' && strpos($eqLogic->getTags(), Utils::init('tag')) === false) {
                        continue;
                    }
                    $order = $eqLogic->getOrder();
                    while (isset($html[$order])) {
                        $order++;
                    }
                    $html[$order] = $eqLogic->toHtml(Utils::init('version'));
                }
            }
            $scenarios = ScenarioManager::byObjectId(Utils::init('id'), false, true);
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
            $this->ajax->success(['objectHtml' => implode($html), 'scenarios' => $scenariosResult]);
        }
    }

    public function setOrder()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $position = 1;
        foreach (json_decode(Utils::init('objects'), true) as $id) {
            $resultObject = JeeObjectManager::byId($id);
            if (is_object($resultObject)) {
                $resultObject->setPosition($position);
                $resultObject->save();
                $position++;
            }
        }
        $this->ajax->success();
    }

    public function getSummaryHtml()
    {
        if (Utils::init('ids') != '') {
            $result = [];
            foreach (json_decode(Utils::init('ids'), true) as $id => $value) {
                if ($id == 'global') {
                    $result['global'] = [
                        'html' => JeeObjectManager::getGlobalHtmlSummary($value['version']),
                        'id' => 'global',
                    ];
                    continue;
                }
                $resultObject = JeeObjectManager::byId($id);
                if (!is_object($resultObject)) {
                    continue;
                }
                $result[$resultObject->getId()] = [
                    'html' => $resultObject->getHtmlSummary($value['version']),
                    'id' => $resultObject->getId(),
                ];
            }
            $this->ajax->success($result);
        } else {
            $resultObject = JeeObjectManager::byId(Utils::init('id'));
            if (!is_object($resultObject)) {
                throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
            }
            $infoObject = [];
            $infoObject['id'] = $resultObject->getId();
            $infoObject['html'] = $resultObject->getHtmlSummary(Utils::init('version'));
            $this->ajax->success($infoObject);
        }
    }

    public function removeImage()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $resultObject = JeeObjectManager::byId(Utils::init('id'));
        if (!is_object($resultObject)) {
            throw new CoreException(__('Vue inconnu. Vérifiez l\'ID ') . Utils::init('id'));
        }
        $resultObject->setImage('data', '');
        $resultObject->setImage('sha512', '');
        $resultObject->save();
        @rrmdir(NEXTDOM_ROOT . '/core/img/object');
        $this->ajax->success();
    }

    public function uploadImage()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $resultObject = JeeObjectManager::byId(Utils::init('id'));
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
