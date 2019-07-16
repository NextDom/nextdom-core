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
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\ObjectManager;
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
        $object = ObjectManager::byId(Utils::init('id'));
        if (!is_object($object)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $object->remove();
        AjaxHelper::success();
    }

    public function byId()
    {
        $object = ObjectManager::byId(Utils::init('id'));
        if (!is_object($object)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID ') . Utils::init('id'));
        }
        AjaxHelper::success(NextDomHelper::toHumanReadable(Utils::o2a($object)));
    }

    public function createSummaryVirtual()
    {
        ObjectManager::createSummaryToVirtual(Utils::init('key'));
        AjaxHelper::success();
    }

    public function all()
    {
        $objects = ObjectManager::buildTree();
        if (Utils::init('onlyHasEqLogic') != '') {
            $return = array();
            foreach ($objects as $object) {
                if (count($object->getEqLogic(true, false, Utils::init('onlyHasEqLogic'), null, Utils::init('searchOnchild', true))) == 0) {
                    continue;
                }
                $return[] = $object;
            }
            $objects = $return;
        }
        AjaxHelper::success(Utils::o2a($objects));
    }

    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $object_json = json_decode(Utils::init('object'), true);
        if (isset($object_json['id'])) {
            $object = ObjectManager::byId($object_json['id']);
        }
        if (!isset($object) || !is_object($object)) {
            $object = new JeeObject();
        }
        Utils::a2o($object, NextDomHelper::fromHumanReadable($object_json));
        if ($object->getName() !== '') {
            $object->save();
            AjaxHelper::success(Utils::o2a($object));
        }
        AjaxHelper::error('Le nom de l\'objet ne peut être vide');
    }

    public function getChild()
    {
        $object = ObjectManager::byId(Utils::init('id'));
        if (!is_object($object)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        $return = Utils::o2a($object->getChild());
        AjaxHelper::success($return);
    }

    public function toHtml()
    {
        if (Utils::init('id') == '' || Utils::init('id') == 'all' || is_json(Utils::init('id'))) {
            if (is_json(Utils::init('id'))) {
                $objects = json_decode(Utils::init('id'), true);
            } else {
                $objects = array();
                foreach (ObjectManager::all() as $object) {
                    if ($object->getConfiguration('hideOnDashboard', 0) == 1) {
                        continue;
                    }
                    $objects[] = $object->getId();
                }
            }
            $return = array();
            $i = 0;
            foreach ($objects as $id) {
                $html = array();
                if (Utils::init('summary') == '') {
                    $eqLogics = EqLogicManager::byObjectId($id, true, true);
                } else {
                    $object = ObjectManager::byId($id);
                    $eqLogics = $object->getEqLogicBySummary(Utils::init('summary'), true, false);
                }
                if (count($eqLogics) > 0) {
                    foreach ($eqLogics as $eqLogic) {
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
                if (Utils::init('noScenario') == '') {
                    $scenarios = ScenarioManager::byObjectId($id, false, true);
                    if (count($scenarios) > 0) {
                        /**
                         * @var Scenario $scenario
                         */
                        foreach ($scenarios as $scenario) {
                            $order = $scenario->getOrder();
                            while (isset($html[$order])) {
                                $order++;
                            }
                            $html[$order] = $scenario->toHtml(Utils::init('version'));
                        }
                    }
                }
                ksort($html);
                $return[$i . '::' . $id] = implode($html);
                $i++;
            }
            AjaxHelper::success($return);
        } else {
            $html = array();
            if (Utils::init('summary') == '') {
                $eqLogics = EqLogicManager::byObjectId(Utils::init('id'), true, true);
            } else {
                $object = ObjectManager::byId(Utils::init('id'));
                $eqLogics = $object->getEqLogicBySummary(Utils::init('summary'), true, false);
            }
            if (count($eqLogics) > 0) {
                foreach ($eqLogics as $eqLogic) {
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
            if (Utils::init('noScenario') == '') {
                $scenarios = ScenarioManager::byObjectId(Utils::init('id'), false, true);
                if (count($scenarios) > 0) {
                    /**
                     * @var Scenario $scenario
                     */
                    foreach ($scenarios as $scenario) {
                        $order = $scenario->getOrder();
                        while (isset($html[$order])) {
                            $order++;
                        }
                        $html[$order] = $scenario->toHtml(Utils::init('version'));
                    }
                }
            }
            ksort($html);
            AjaxHelper::success(implode($html));
        }
    }

    public function setOrder()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $position = 1;
        foreach (json_decode(Utils::init('objects'), true) as $id) {
            $object = ObjectManager::byId($id);
            if (is_object($object)) {
                $object->setPosition($position);
                $object->save();
                $position++;
            }
        }
        AjaxHelper::success();
    }

    public function getSummaryHtml()
    {
        if (Utils::init('ids') != '') {
            $return = array();
            foreach (json_decode(Utils::init('ids'), true) as $id => $value) {
                if ($id == 'global') {
                    $return['global'] = array(
                        'html' => ObjectManager::getGlobalHtmlSummary($value['version']),
                        'id' => 'global',
                    );
                    continue;
                }
                $object = ObjectManager::byId($id);
                if (!is_object($object)) {
                    continue;
                }
                $return[$object->getId()] = array(
                    'html' => $object->getHtmlSummary($value['version']),
                    'id' => $object->getId(),
                );
            }
            AjaxHelper::success($return);
        } else {
            $object = ObjectManager::byId(Utils::init('id'));
            if (!is_object($object)) {
                throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
            }
            $info_object = array();
            $info_object['id'] = $object->getId();
            $info_object['html'] = $object->getHtmlSummary(Utils::init('version'));
            AjaxHelper::success($info_object);
        }
    }

    public function removeImage()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $object = ObjectManager::byId(Utils::init('id'));
        if (!is_object($object)) {
            throw new CoreException(__('Vue inconnu. Vérifiez l\'ID ') . Utils::init('id'));
        }
        $object->setImage('data', '');
        $object->setImage('sha512', '');
        $object->save();
        @rrmdir(NEXTDOM_ROOT . '/core/img/object');
        AjaxHelper::success();
    }

    public function uploadImage()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $object = ObjectManager::byId(Utils::init('id'));
        if (!is_object($object)) {
            throw new CoreException(__('Objet inconnu. Vérifiez l\'ID'));
        }
        if (!isset($_FILES['file'])) {
            throw new CoreException(__('Aucun fichier trouvé. Vérifiez le paramètre PHP (post size limit)'));
        }
        $extension = strtolower(strrchr($_FILES['file']['name'], '.'));
        if (!in_array($extension, array('.jpg', '.jpeg', '.png'))) {
            throw new CoreException('Extension du fichier non valide (autorisé .jpg .jpeg .png) : ' . $extension);
        }
        if (filesize($_FILES['file']['tmp_name']) > 5000000) {
            throw new CoreException(__('Le fichier est trop gros (maximum 5Mo)'));
        }
        $files = FileSystemHelper::ls(NEXTDOM_DATA . '/data/object/', 'object' . $object->getId() . '*');
        if (count($files) > 0) {
            foreach ($files as $file) {
                unlink(NEXTDOM_DATA . '/data/object/' . $file);
            }
        }
        $object->setImage('type', str_replace('.', '', $extension));
        $object->setImage('data', base64_encode(file_get_contents($_FILES['file']['tmp_name'])));
        $object->setImage('sha512', sha512($object->getImage('data')));
        $filename = 'object' . $object->getId() . '-' . $object->getImage('sha512') . '.' . $object->getImage('type');
        $filepath = NEXTDOM_DATA . '/data/object/' . $filename;
        file_put_contents($filepath, file_get_contents($_FILES['file']['tmp_name']));
        if (!file_exists($filepath)) {
            throw new CoreException(__('Impossible de sauvegarder l\'image'));
        }
        $object->save();
        AjaxHelper::success();
    }

}