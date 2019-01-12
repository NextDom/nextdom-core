<?php

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 *
 * @Support <https://www.nextdom.org>
 * @Email   <admin@nextdom.org>
 * @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
 */

namespace NextDom\Controller\Modale;

use NextDom\Managers\JeeObjectManager;
use NextDom\Helpers\Utils;
use NextDom\Helpers\Render;
use NextDom\Helpers\Status;
use NextDom\Exceptions\CoreException;

class ObjectConfigure extends BaseAbstractModale
{

    public function __construct()
    {
        parent::__construct();
        Status::isConnectedOrFail();
    }

    /**
     * Render object configure modal
     *
     * @param Render $render Render engine
     *
     * @throws CoreException
     */
    public function get(Render $render)
    {

        $objectId = Utils::init('object_id');
        $object   = JeeObjectManager::byId($objectId);
        if (!is_object($object)) {
            throw new CoreException(__('Objet non trouvé : ') . $objectId);
        }
        Utils::sendVarToJS('objectInfo', \utils::o2a($object));

        return $render->get('/modals/object.configure.html.twig');
    }

}
