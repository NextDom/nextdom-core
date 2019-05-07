<?php

/*
* This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
* Copyright (c) 2018 NextDom.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 2.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace NextDom\Rest;

use NextDom\Managers\EventManager;

/**
 * Class ChangesRest
 *
 * @package NextDom\Rest
 */
class ChangesRest
{
    /**
     * Get change since a datetime
     *
     * @param int $lastUpdate Get updates at this time
     *
     * @return array List of events
     *
     * @throws \Exception
     */
    public static function get(int $lastUpdate)
    {
        sleep(3);
        return json_decode('{"datetime":1557440034.2021,"result":[{"datetime":1557440034.2021,"name":"cmd::update","option":{"cmd_id":"60","value":73.5,"display_value":73.5,"valueDate":"2019-05-10 00:13:54","collectDate":"2019-05-10 00:13:54","alertLevel":"none"}}]}', true);
        return EventManager::changes($lastUpdate, 59);
    }

}