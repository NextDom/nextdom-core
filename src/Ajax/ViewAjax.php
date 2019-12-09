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
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\FileSystemHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\ViewManager;
use NextDom\Managers\ViewZoneManager;
use NextDom\Model\Entity\View;
use NextDom\Model\Entity\ViewData;
use NextDom\Model\Entity\ViewZone;

/**
 * Class ViewAjax
 * @package NextDom\Ajax
 */
class ViewAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::USER;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function remove()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $view = ViewManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($view)) {
            throw new CoreException(__('Vue non trouvée. Vérifiez l\'iD'));
        }
        $view->remove();
        $this->ajax->success();
    }

    public function all()
    {
        $this->ajax->success(Utils::o2a(ViewManager::all()));
    }

    public function get()
    {
        if (Utils::init(AjaxParams::ID) == 'all' || is_json(Utils::init(AjaxParams::ID))) {
            $views = [];
            if (is_json(Utils::init(AjaxParams::ID))) {
                $view_ajax = json_decode(Utils::init(AjaxParams::ID), true);
                foreach ($view_ajax as $id) {
                    $views[] = ViewManager::byId($id);
                }
            } else {
                $views = ViewManager::all();
            }
            $return = [];
            foreach ($views as $view) {
                $return[$view->getId()] = $view->toAjax(Utils::init(AjaxParams::VERSION, 'dview'), Utils::init('html'));
            }
            $this->ajax->success($return);
        } else {
            $view = ViewManager::byId(Utils::init(AjaxParams::ID));
            if (!is_object($view)) {
                throw new CoreException(__('Vue non trouvée. Vérifiez l\'ID'));
            }
            $this->ajax->success($view->toAjax(Utils::init(AjaxParams::VERSION, 'dview'), Utils::init(AjaxParams::HTML)));
        }
    }

    public function save()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $view = ViewManager::byId(Utils::init('view_id'));
        if (!is_object($view)) {
            $view = new View();
        }
        $view_ajax = json_decode(Utils::init('view'), true);
        Utils::a2o($view, $view_ajax);
        $view->save();
        if (isset($view_ajax['zones']) && count($view_ajax['zones']) > 0) {
            $view->removeviewZone();
            foreach ($view_ajax['zones'] as $viewZone_info) {
                $viewZone = new ViewZone();
                $viewZone->setView_id($view->getId());
                Utils::a2o($viewZone, $viewZone_info);
                $viewZone->save();
                if (isset($viewZone_info['viewData'])) {
                    $order = 0;
                    foreach ($viewZone_info['viewData'] as $viewData_info) {
                        $viewData = new ViewData();
                        $viewData->setviewZone_id($viewZone->getId());
                        $viewData->setOrder($order);
                        Utils::a2o($viewData, NextDomHelper::fromHumanReadable($viewData_info));
                        $viewData->save();
                        $order++;
                    }
                }
            }
        }
        $this->ajax->success(Utils::o2a($view));
    }

    public function getEqLogicviewZone()
    {
        $viewZone = ViewZoneManager::byId(Utils::init('viewZone_id'));
        if (!is_object($viewZone)) {
            throw new CoreException(__('Vue non trouvée. Vérifiez l\'ID'));
        }
        $return = Utils::o2a($viewZone);
        $return['eqLogic'] = [];
        /**
         * @var ViewData $viewData
         */
        foreach ($viewZone->getViewData() as $viewData) {
            $infoViewDatat = Utils::o2a($viewData->getLinkObject());
            $infoViewDatat['html'] = $viewData->getLinkObject()->toHtml(Utils::init(AjaxParams::VERSION));
            $return['viewData'][] = $infoViewDatat;
        }
        $this->ajax->success($return);
    }

    public function setEqLogicOrder()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $eqLogics = json_decode(Utils::init('eqLogics'), true);
        $sql = '';
        foreach ($eqLogics as $eqLogic_json) {
            if (!isset($eqLogic_json['viewZone_id']) || !is_numeric($eqLogic_json['viewZone_id']) || !is_numeric($eqLogic_json['id']) || !is_numeric($eqLogic_json['order']) || (isset($eqLogic_json['object_id']) && !is_numeric($eqLogic_json['object_id']))) {
                continue;
            }
            $sql .= 'UPDATE viewData SET `order` = ' . $eqLogic_json['order'] . '  WHERE link_id = ' . $eqLogic_json['id'] . ' AND  viewZone_id = ' . $eqLogic_json['viewZone_id'] . ';';
            $eqLogic = EqLogicManager::byId($eqLogic_json['id']);
            if (!is_object($eqLogic)) {
                continue;
            }
            Utils::a2o($eqLogic, $eqLogic_json);
            $eqLogic->save(true);
        }
        if ($sql != '') {
            DBHelper::exec($sql);
        }
        $this->ajax->success();
    }

    public function setOrder()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $order = 1;
        foreach (json_decode(Utils::init('views'), true) as $id) {
            $view = ViewManager::byId($id);
            if (is_object($view)) {
                $view->setOrder($order);
                $view->save();
                $order++;
            }
        }
        $this->ajax->success();
    }

    public function removeImage()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $view = ViewManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($view)) {
            throw new CoreException(__('Vue inconnu. Vérifiez l\'ID ') . Utils::init(AjaxParams::ID));
        }
        $view->setImage('sha512', '');
        $view->save();
        @rrmdir(NEXTDOM_ROOT . '/public/img/view');
        $this->ajax->success();
    }

    public function uploadImage()
    {
        AuthentificationHelper::isConnectedAsAdminOrFail();
        $view = ViewManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($view)) {
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
        $files = FileSystemHelper::ls(NEXTDOM_DATA . '/data/view/', 'view' . $view->getId() . '*');
        if (count($files) > 0) {
            foreach ($files as $file) {
                unlink(NEXTDOM_DATA . '/data/view/' . $file);
            }
        }
        $view->setImage('type', str_replace('.', '', $extension));
        $view->setImage('sha512', sha512(file_get_contents($_FILES['file']['tmp_name'])));
        $filename = 'view' . $view->getId() . '-' . $view->getImage('sha512') . '.' . $view->getImage('type');
        $filepath = NEXTDOM_DATA . '/data/view/' . $filename;
        file_put_contents($filepath, file_get_contents($_FILES['file']['tmp_name']));
        if (!file_exists($filepath)) {
            throw new CoreException(__('Impossible de sauvegarder l\'image', __FILE__));
        }
        $view->save();
        @rrmdir(NEXTDOM_ROOT . '/public/img/view');
        $this->ajax->success();
    }
}