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
use NextDom\Helpers\Utils;
use NextDom\Managers\NoteManager;
use NextDom\Model\Entity\Note;

/**
 * Class NoteAjax
 * @package NextDom\Ajax
 */
class NoteAjax extends BaseAjax
{
    protected $NEEDED_RIGHTS = UserRight::ADMIN;
    protected $MUST_BE_CONNECTED = true;
    protected $CHECK_AJAX_TOKEN = true;

    public function all()
    {
        $this->ajax->success(Utils::o2a(NoteManager::all()));
    }

    public function byId()
    {
        $this->ajax->success(Utils::o2a(NoteManager::byId(Utils::init(AjaxParams::ID))));
    }

    public function save()
    {
        $noteData = json_decode(Utils::init('note'), true);
        if (empty($noteData['name'])) {
            $this->ajax->error(__('entity.note.name-cannot-be-empty'));
        } else {
            if (isset($noteData['id'])) {
                $note = NoteManager::byId($noteData['id']);
            } else {
                $note = new Note();
            }
            Utils::a2o($note, $noteData);
            $note->save();
            $this->ajax->success(Utils::o2a($note));
        }
    }

    public function remove()
    {
        $note = NoteManager::byId(Utils::init(AjaxParams::ID));
        if (!is_object($note)) {
            throw new CoreException(__('Note inconnue. Vérifiez l\'ID'));
        }
        $note->remove();
        $this->ajax->success();
    }

}
