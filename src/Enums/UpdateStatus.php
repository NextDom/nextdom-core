<?php
/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Enums;

/**
 * Class UpdateStatus
 *
 * Status of update in database
 *
 * @package NextDom\Enums
 */
class UpdateStatus extends Enum
{
    /**
     * @var string Do not update
     */
    const HOLD = 'hold';
    /**
     * @var string Need update
     */
    const UPDATE = 'update';
    /**
     * @var string Updated
     */
    const OK = 'ok';
    /**
     * @var string Depreciated plugins
     */
    const DEPRECIATED = 'depreciated';
}
