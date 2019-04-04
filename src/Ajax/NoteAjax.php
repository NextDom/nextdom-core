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
use NextDom\Managers\NoteManager;
use NextDom\Model\Entity\Note;

class NoteAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function all()
    {
        AjaxHelper::success(Utils::o2a(NoteManager::all()));
    }

    public function byId()
    {
        AjaxHelper::success(Utils::o2a(NoteManager::byId(Utils::init('id'))));
    }

    public function save()
    {
        $note_json = json_decode(Utils::init('note'), true);
        if (isset($note_json['id'])) {
            $note = NoteManager::byId($note_json['id']);
        }
        if (!isset($note) || !is_object($note)) {
            $note = new Note();
        }
        Utils::a2o($note, $note_json);
        $note->save();
        AjaxHelper::success(Utils::o2a($note));
    }

    public function remove()
    {
        $note = NoteManager::byId(Utils::init('id'));
        if (!is_object($note)) {
            throw new CoreException(__('Note inconnue. Vérifiez l\'ID'));
        }
        $note->remove();
        AjaxHelper::success();
    }

}